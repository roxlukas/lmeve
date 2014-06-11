<?php
$POLLER_VERSION="16";
set_time_limit(880); //poller can work for up to 15 minutes 
//(minus 20 seconds so the next cron cycle can work correctly), afterwards it should die
$mypath=str_replace('\\','/',dirname(__FILE__));
$mylog=$mypath."/../var/poller.txt";
$httplog=$mypath."/../var/http_errors.txt";
$mylock=$mypath."/../var/poller.lock";
$mycache=$mypath."/../var";
$mytmp=$mypath."/../tmp";
$MAX_ERRORS=10; //ignore first x errors

$FEED_BLOCKED="This feed is blocked due to previous errors.";

date_default_timezone_set('Europe/Warsaw');
set_include_path("$mypath/../include");
include_once("log.php");
include_once("db.php");

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function critical($what,$error) {
	global $mylog,$mylock;
	$message="[CRITICAL] $what: $error\n";
	loguj($mylog,$message);
	die($message);	
}

function warning($what,$error) {
	global $mylog;
	$message="[WARNING] $what: $error\n";
	echo($message);
	loguj($mylog,$message);	
}

function inform($what,$error) {
	global $mylog;
	$message="[INFORM] $what: $error\n";
	echo($message);
	loguj($mylog,$message);	
}

function apiCheckStatus($keyID,$fileName) {
//checks if there are ANY entries in apistatus table
	if (db_count("SELECT * FROM `apistatus` WHERE keyID='$keyID' AND fileName='$fileName';")>0) {
		return true;  //returns true when there are enes
	} else {
		return false; //returns false if there have been no entries
	}
}

function apiCheckWarnings($keyID,$fileName) {
//checks if there are TEMPORARY ERROR entries in apistatus table
	if (db_count("SELECT * FROM `apistatus` WHERE errorCode>=500 AND keyID='$keyID' AND fileName='$fileName';")>0) {
		return true;  //returns true when there are temporary errors
	} else {
		return false; //returns false if there have been no temporary errors
	}
}

function apiCheckErrors($keyID,$fileName) {
//checks if there are UNRECOVERABLE ERROR entries in apistatus table
    global $MAX_ERRORS;
	if (db_count("SELECT * FROM `apistatus` WHERE errorCode>0 AND errorCode<500 AND errorCount >= $MAX_ERRORS AND keyID='$keyID' AND fileName='$fileName';")>0) {
		return true;  //returns true when there are unrecoverable errors
	} else {
		return false; //returns false if there have been no unrecoverable errors
	}
}

function apiSaveWarning($keyID,$error,$fileName) {
	$attrs=$error->attributes();
	$errorCode=$attrs->code;
	$errorMessage=$error;
	if (!apiCheckStatus($keyID,$fileName)) {
		db_uquery("INSERT INTO `apistatus` VALUES (DEFAULT,'$keyID','$fileName',NOW(),$errorCode,1,'$errorMessage');");
	} else {
                if ($errorCode > 0) {
                    $errcount='errorCount+1';
                } else {
                    $errcount='0';
                }
		db_uquery("UPDATE `apistatus` SET date=NOW(), errorCode=$errorCode, errorCount=$errcount, errorMessage='$errorMessage' WHERE keyID='$keyID' AND fileName='$fileName';");
	}
	if ($errorCode > 0)	warning($fileName,"ERROR $errorCode: $errorMessage");
}

function apiSaveOK($keyID,$fileName) {
	if (!apiCheckStatus($keyID,$fileName)) {
		db_uquery("INSERT INTO `apistatus` VALUES (DEFAULT,'$keyID','$fileName',NOW(),0,0,'OK');");
	} else {
		db_uquery("UPDATE `apistatus` SET date=NOW(), errorCode=0, errorCount=0, errorMessage='OK' WHERE keyID='$keyID' AND fileName='$fileName';");
	}
}

function lock_check($lock) {
	if (file_exists($lock)) {
		return true;
	} else {
		return false;	
	}
}

function lock_set($lock) {
	if (!lock_check($lock)) {
		touch($lock);
		return true;
	} else {
		return false;
	}
}

function lock_unset($lock) {
	if (lock_check($lock)) {
		unlink($lock);
		return true;
	} else {
		return false;
	}
} 

function cache_file($url, $cache, $interval) { //DEPRECATED
	//if a file got polled @ 12:00:20 and next poller time is 12:15:01, a 15 minute cache timer will not be satisfied
	//thus we cut 20 seconds
	if ($interval>20) $interval=$interval-20;
	
	if (file_exists($cache) && (filemtime($cache)>(time() - $interval ))) {
   		$data = file_get_contents($cache);
	} else {
   		$data = file_get_contents($url);
   		if ($data===false) {
			//http errors
   		} else {
   		   	file_put_contents($cache, $data, LOCK_EX);
   		}
	}
}

function get_xml_contents($url, $cache, $interval) {
	//if a file got polled @ 12:00:20 and next poller time is 12:15:01, a 15 minute cache timer will not be satisfied
	//thus we cut 20 seconds
	if ($interval>20) $interval=$interval-20;
	global $httplog;
	if (file_exists($cache) && (filemtime($cache)>(time() - $interval ))) {
   		$data = file_get_contents($cache);
		$xml_data=new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><eveapi version="2">  <currentTime></currentTime><error code="0">Cached</error><cachedUntil></cachedUntil></eveapi>');
	} else {
                 $ctx = stream_context_create(array(
                        'http' => array (
                            'ignore_errors' => TRUE
                         )
                    ));
                $data=file_get_contents($url, FALSE, $ctx); 
   		//$data = file_get_contents($url);
   		//if ($data === false) {
                if (!empty($http_response_header)) {
                    $http_parse=explode(' ',$http_response_header[0]);
                    $http_code=$http_parse[1];
                    if ($http_code!=200) {
                            //http errors
                            if (empty($http_code)) $http_code=500;
                            $xml_data=new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><eveapi version="2">  <currentTime></currentTime><error code="'.$http_code.'">HTTP ERROR! Return code: '.$http_response_header[0].'</error><cachedUntil></cachedUntil></eveapi>');
                            //additional logging!!
                            loguj($httplog,"\r\nREQUEST URI:\r\n$url\r\nHTTP RESPONSE:\r\n${http_response_header[0]}\r\n-------- HTTP RESPONSE BELOW THIS LINE --------\r\n$data\r\n------------- END OF HTTP RESPONSE ------------\r\n");
                    } else {
                            file_put_contents($cache, $data, LOCK_EX);
                            $xml_data = simplexml_load_file( $cache );
                            if ($xml_data === false) {
                                    //parser errors
                                    $xml_data=new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><eveapi version="2">  <currentTime></currentTime><error code="500">XML Parser error </error><cachedUntil></cachedUntil></eveapi>');
                            }
                    }
                } else {
                    //network problem?
                    $xml_data=new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><eveapi version="2">  <currentTime></currentTime><error code="500">NETWORK PROBLEM!</error><cachedUntil></cachedUntil></eveapi>');
                    loguj($httplog,"\r\nREQUEST URI:\r\n$url\r\nNETWORK PROBLEM!\r\n");
                }
   	}
	return $xml_data;
}
/*
PHP Warning:  file_get_contents(https://api.eveonline.com/account/APIKeyInfo.xml.aspx?keyID=1141058&vCode=zd6cxEv98sMKyxOTwRiqkh3fYRGlYiDPBBEGVNM5vcwGTWSn6jPLb5KVuTEkWpPL): 
 * failed to open stream: php_network_getaddresses: getaddrinfo failed: No address associated with hostname in /home/lukas/lmeve/bin/poller.php on line 157
PHP Notice:  Undefined variable: http_response_header in /home/lukas/lmeve/bin/poller.php on line 160
PHP Notice:  Undefined offset: 1 in /home/lukas/lmeve/bin/poller.php on line 161
PHP Notice:  Undefined variable: http_response_header in /home/lukas/lmeve/bin/poller.php on line 164
PHP Notice:  Undefined variable: http_response_header in /home/lukas/lmeve/bin/poller.php on line 166
<br><table class="error"><tr><td>Error in query: UPDATE `apistatus` SET date=NOW(), errorCode=, errorCount=0, errorMessage='HTTP ERROR! Return code: ' WHERE keyID='1141058' AND fileName='APIKeyInfo.xml';<br />MySQL reply: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ' errorCount=0, errorMessage='HTTP ERROR! Return code: ' WHERE keyID='1141058' AN' at line 1</td></tr></table><input type="button" value="&lt; Back" onclick="history.back();">
 */

function load_apikeys_from_file() { //DEPRECATED
	$handle=fopen($myconfig,"r");
	if ($handle) {
		while (($buffer = fgets($handle, 1024)) !== false) {
			$api_line=explode("\t",$buffer);
			$api_keys[$i]['keyID']=$api_line[0];
			$api_keys[$i]['vCode']=$api_line[1];
		}
		if (!feof($handle)) {
			critical("Main","Error while reading configuration");
		}
		fclose($handle);
	}
	return $api_keys;
}

function ins_string($string) {
	$out="'".addslashes($string)."'";
	return $out;
}

function load_apikeys_from_db() {
	$api_keys=db_asocquery("SELECT keyID,vCode FROM cfgapikeys;");
	return $api_keys;
}

function insertAssets($rowset,$parentID,$locationID,$corporationID) { //$parent=0 - root node
		foreach ($rowset as $row) {
			$attrs=$row->attributes();

			if ($parentID==0)	{ 				//if root node
				$locID=$attrs->locationID;  //then take locationID of the row
				$parID=0;					//set parentID=0
			} else {
				$locID=$locationID; 		//otherwise take locationID of the parent
				$parID=$parentID;			//and set parentID as ID of the parent
			}
			
			if (isset($attrs->rawQuantity)) {
				$rawQuantity=$attrs->rawQuantity;
			} else {
				$rawQuantity='NULL';
			}
			
			$sql="INSERT INTO `apiassets` VALUES(".
			$attrs->itemID.",".
			$parID.",".
			$locID.",".
			$attrs->typeID.",".
			$attrs->quantity.",".
			$attrs->flag.",".
			$attrs->singleton.",".
			$rawQuantity.",".
			$corporationID.
			");";
			db_uquery($sql);
			//echo($attrs->itemID.",".$locID."\r\n");
			if (isset($row->rowset)) {
				//echo("HAS CONTENTS!\r\n");
				insertAssets($row->rowset->row,$attrs->itemID,$locID,$corporationID);
			}
		}
	}

/*************************************************************************************************/

//PROGRAM BEGINNING, START TIME (for performance measurements)
$time_start = microtime_float();

//TRY TO CREATE LOCK FILE OR EXIT
//if (!lock_set($mylock)) critical("Main","Lock file already exists.");
if (!lock_set($mylock)) warning("Main","Lock file already exists.");

//LOAD API KEYS
//$api_keys=load_apikeys_from_file(); //LOAD FROM FILE
$api_keys=load_apikeys_from_db(); //LOAD FROM DB

//MAIN PROGRAM LOOP - POLL DATA FOR EACH KEY/CODE PAIR
foreach ($api_keys as $api_key) {
	
	$keyid=$api_key['keyID'];
	$vcode=$api_key['vCode'];
	
	inform("Main","Polling keyID $keyid...");
	
	if (!apiCheckErrors($keyid,"APIKeyInfo.xml")) {
		$aki=get_xml_contents("https://api.eveonline.com/account/APIKeyInfo.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/APIKeyInfo_$keyid.xml",0*60);
		if (isset($aki->error)) {
			apiSaveWarning($keyid,$aki->error,"APIKeyInfo.xml");
			continue;
		} else {
			$row=$aki->result->key->rowset->row->attributes();
			$sql="INSERT IGNORE INTO apicorps VALUES (".$row->corporationID.",".
			ins_string($row->corporationName).",".
			$row->characterID.",".
			ins_string($row->characterName).",".
			$keyid.
			");";
			db_uquery($sql);
			$corporationID=$row->corporationID;
			apiSaveOK($keyid,"APIKeyInfo.xml");
		}
	} else {
		warning("APIKeyInfo.xml",$FEED_BLOCKED);
		continue;
	}
	
	//POLL CORP MEMBER NAMES
	if (!apiCheckErrors($keyid,"MemberTracking.xml")) {
		$mtr=get_xml_contents("https://api.eveonline.com/corp/MemberTracking.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/MemberTracking_$keyid.xml",60*60);
		if (isset($mtr->error)) {
			apiSaveWarning($keyid,$mtr->error,"MemberTracking.xml");
		} else {
			db_uquery("DELETE FROM apicorpmembers WHERE corporationID=$corporationID;");
			$rows=$mtr->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();
				$sql="INSERT IGNORE INTO apicorpmembers VALUES (".
				$attrs->characterID.",".
				ins_string($attrs->name).",".
				ins_string($attrs->startDateTime).",".
				$attrs->baseID.",".
				ins_string($attrs->base).",".
				ins_string($attrs->title).",".
				$corporationID.
				");";
				db_uquery($sql);
			}
			apiSaveOK($keyid,"MemberTracking.xml");
		}
	} else {
		warning("MemberTracking.xml",$FEED_BLOCKED);
	}
	
	//POLL INDUSTRY JOBS
	if (!apiCheckErrors($keyid,"IndustryJobs.xml")) {
		$ijl=get_xml_contents("https://api.eveonline.com/corp/IndustryJobs.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/IndustryJobs_$keyid.xml",15*60);
		if (isset($ijl->error)) {
			apiSaveWarning($keyid,$ijl->error,"IndustryJobs.xml");
		} else {
			$rows=$ijl->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();
				$sql="INSERT INTO apiindustryjobs VALUES (".
				$attrs->jobID.",".
				$attrs->assemblyLineID.",".
				$attrs->containerID.",".
				$attrs->installedItemID.",".
				$attrs->installedItemLocationID.",".
				$attrs->installedItemQuantity.",".
				$attrs->installedItemProductivityLevel.",".
				$attrs->installedItemMaterialLevel.",".
				$attrs->installedItemLicensedProductionRunsRemaining.",".
				$attrs->outputLocationID.",".
				$attrs->installerID.",".
				$attrs->runs.",".
				$attrs->licensedProductionRuns.",".
				$attrs->installedInSolarSystemID.",".
				$attrs->containerLocationID.",".
				$attrs->materialMultiplier.",".
				$attrs->charMaterialMultiplier.",".
				$attrs->timeMultiplier.",".
				$attrs->charTimeMultiplier.",".
				$attrs->installedItemTypeID.",".
				$attrs->outputTypeID.",".
				$attrs->containerTypeID.",".
				$attrs->installedItemCopy.",".
				$attrs->completed.",".
				$attrs->completedSuccessfully.",".
				$attrs->installedItemFlag.",".
				$attrs->outputFlag.",".
				$attrs->activityID.",".
				$attrs->completedStatus.",".
				ins_string($attrs->installTime).",".
				ins_string($attrs->beginProductionTime).",".
				ins_string($attrs->endProductionTime).",".
				ins_string($attrs->pauseProductionTime).",".
				$corporationID.
				") ON DUPLICATE KEY UPDATE".
				" completed=".$attrs->completed.
				",completedSuccessfully=".$attrs->completedSuccessfully.
				",completedStatus=".$attrs->completedStatus;
				db_uquery($sql);
			}
			apiSaveOK($keyid,"IndustryJobs.xml");
		}
	} else {
		warning("IndustryJobs.xml",$FEED_BLOCKED);
	}
	
	//CORP SHEET: https://api.eveonline.com/corp/CorporationSheet.xml.aspx
	//Parameters	 userID, apiKey, characterID OR corporationID
	//Cache Time (minutes)	 360
	if (!apiCheckErrors($keyid,"CorporationSheet.xml")) {
		$csh=get_xml_contents("https://api.eveonline.com/corp/CorporationSheet.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/CorporationSheet_$keyid.xml",360*60);
		if (isset($csh->error)) {
			apiSaveWarning($keyid,$csh->error,"CorporationSheet.xml");
		} else {
			db_uquery("DELETE FROM apicorpsheet WHERE corporationID=$corporationID;");
			$row=$csh->result;
			$sql="INSERT IGNORE INTO apicorpsheet VALUES (".
			$row->corporationID.",".
			ins_string($row->corporationName).",".
			ins_string($row->ticker).",".
			$row->ceoID.",".
			ins_string($row->ceoName).",".
			$row->stationID.",".
			ins_string($row->stationName).",".
			ins_string($row->description).",".
			ins_string($row->url).",".
			$row->allianceID.",".
			$row->taxRate.",".
			$row->memberCount.",".
			$row->memberLimit.",".
			$row->shares.",".
			$row->logo->graphicID.",".
			$row->logo->shape1.",".
			$row->logo->shape2.",".
			$row->logo->shape3.",".
			$row->logo->color1.",".
			$row->logo->color2.",".
			$row->logo->color3.
			");";
			db_uquery($sql);
			
			$rowsets=$row->rowset;
			foreach ($rowsets as $rowset) {
				$attrs=$rowset->attributes();
				foreach($rowset->row as $row) {
					switch ($attrs->name) {
						case 'divisions':
							$rowattrs=$row->attributes();
							$accountKey=$rowattrs->accountKey;
							$description=$rowattrs->description;
							db_uquery("DELETE FROM apidivisions WHERE corporationID=$corporationID AND accountKey=$accountKey;");
							$sql="INSERT IGNORE INTO apidivisions VALUES (".
							$corporationID.",".
							$accountKey.",".
							ins_string($description).
							");";
							db_uquery($sql);
						break;
						case 'walletDivisions':
							$rowattrs=$row->attributes();
							$accountKey=$rowattrs->accountKey;
							$description=$rowattrs->description;
							db_uquery("DELETE FROM apiwalletdivisions WHERE corporationID=$corporationID AND accountKey=$accountKey;");
							$sql="INSERT IGNORE INTO apiwalletdivisions VALUES (".
							$corporationID.",".
							$accountKey.",".
							ins_string($description).
							");";
							db_uquery($sql);
						break;
						default:
					}
				}
			}
			apiSaveOK($keyid,"CorporationSheet.xml");
		}
	} else {
		warning("CorporationSheet.xml",$FEED_BLOCKED);
	}
	
	//WALLET JOURNAL: 	https://api.eveonline.com/corp/WalletJournal.xml.aspx
	//Parameters	 userID, apiKey, characterID, (rowCount)
	//Cache Time (minutes)	 15
	$MAXROWS=256; //maximum number of rows from Journal 50-2560
	$accountKeys=db_asocquery("SELECT accountKey FROM apicorps acs JOIN apiwalletdivisions awd ON acs.corporationID=awd.corporationID WHERE keyID=$keyid;");
	if (sizeof($accountKeys) > 0) {
		foreach($accountKeys as $acct) {
			$accountKey=$acct['accountKey'];
			if (!apiCheckErrors($keyid,"WalletJournal_$accountKey.xml")) {
				$wlj=get_xml_contents("https://api.eveonline.com/corp/WalletJournal.xml.aspx?keyID=${keyid}&vCode=${vcode}&accountKey=$accountKey&rowCount=${MAXROWS}","${mycache}/WalletJournal_${keyid}_${accountKey}.xml",15*60);
				if (isset($wlj->error)) {
					apiSaveWarning($keyid,$wlj->error,"WalletJournal_$accountKey.xml");
				} else {
					$rows=$wlj->result->rowset->row;
					foreach ($rows as $row) {
						$attrs=$row->attributes();
						$sql="INSERT IGNORE INTO apiwalletjournal VALUES (".
						ins_string($attrs->date).",".
						$attrs->refID.",".
						$attrs->refTypeID.",".
						ins_string($attrs->ownerName1).",".
						$attrs->ownerID1.",".
						ins_string($attrs->ownerName2).",".
						$attrs->ownerID2.",".
						ins_string($attrs->argName1).",".
						$attrs->argID1.",".
						$attrs->amount.",".
						$attrs->balance.",".
						ins_string($attrs->reason).",".
						$corporationID.",".
						$accountKey.
						");";
						db_uquery($sql);
					}
					apiSaveOK($keyid,"WalletJournal_$accountKey.xml");
				}
			} else {
				warning("WalletJournal_$accountKey.xml",$FEED_BLOCKED);
			}
		}
	}
	//WALLET TRANSACTIONS: https://api.eveonline.com/corp/WalletTransactions.xml.aspx
	//Parameters	 userID, apiKey, accountKey --characterID-- 
	//Cache Time (minutes)	 15
	$accountKeys=db_asocquery("SELECT accountKey FROM apicorps acs JOIN apiwalletdivisions awd ON acs.corporationID=awd.corporationID WHERE keyID=$keyid;");
	if (sizeof($accountKeys) > 0) {
		foreach($accountKeys as $acct) {
			$accountKey=$acct['accountKey'];
			if (!apiCheckErrors($keyid,"WalletTransactions_$accountKey.xml")) {
				$wlt=get_xml_contents("https://api.eveonline.com/corp/WalletTransactions.xml.aspx?keyID=${keyid}&vCode=${vcode}&accountKey=$accountKey","${mycache}/WalletTransactions_${keyid}_${accountKey}.xml",15*60);
				if (isset($wlt->error)) {
					apiSaveWarning($keyid,$wlt->error,"WalletTransactions_$accountKey.xml");
				} else {
					$rows=$wlt->result->rowset->row;
					foreach ($rows as $row) {
						$attrs=$row->attributes();
						$sql="INSERT IGNORE INTO apiwallettransactions VALUES (".
						ins_string($attrs->transactionDateTime).",".
						$attrs->transactionID.",".
						$attrs->quantity.",".
						ins_string($attrs->typeName).",".
						$attrs->typeID.",".
						$attrs->price.",".
						$attrs->clientID.",".
						ins_string($attrs->clientName).",".
						$attrs->characterID.",".
						ins_string($attrs->characterName).",".
						$attrs->stationID.",".
						ins_string($attrs->stationName).",".
						ins_string($attrs->transactionType).",".
						ins_string($attrs->transactionFor).",".
						$attrs->journalTransactionID.",".
						$accountKey.",".
						$corporationID.
						");";
						db_uquery($sql);
					}
					apiSaveOK($keyid,"WalletTransactions_$accountKey.xml");
				}
			} else {
				warning("WalletTransactions_$accountKey.xml",$FEED_BLOCKED);
			}	
		}
	}

	//MARKET ORDERS: https://api.eveonline.com/corp/MarketOrders.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	if (!apiCheckErrors($keyid,"MarketOrders.xml")) {
		$mao=get_xml_contents("https://api.eveonline.com/corp/MarketOrders.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/MarketOrders$keyid.xml",60*60);
		if (isset($mao->error)) {
			apiSaveWarning($keyid,$mao->error,"MarketOrders.xml");
		} else {
			db_uquery("DELETE FROM `apimarketorders` WHERE corporationID=$corporationID;");
			$rows=$mao->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();			
				$sql="INSERT IGNORE INTO `apimarketorders` VALUES(".
				$attrs->orderID.",".
				$attrs->charID.",".
				$attrs->stationID.",".
				$attrs->volEntered.",".
				$attrs->volRemaining.",".
				$attrs->minVolume.",".
				$attrs->orderState.",".
				$attrs->typeID.",".
				$attrs->range.",".
				$attrs->accountKey.",".
				$attrs->duration.",".
				$attrs->escrow.",".
				$attrs->price.",".
				$attrs->bid.",".
				ins_string($attrs->issued).",".
				$corporationID.
				");";
				db_uquery($sql);
			}
			apiSaveOK($keyid,"MarketOrders.xml");
		}
	} else {
		warning("MarketOrders.xml",$FEED_BLOCKED);
	}
	
	//POS LIST: https://api.eveonline.com/corp/StarbaseList.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 360
	if (!apiCheckErrors($keyid,"StarbaseList.xml")) {
		$psl=get_xml_contents("https://api.eveonline.com/corp/StarbaseList.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/StarbaseList_$keyid.xml",15*60);
		if (isset($psl->error)) {
			apiSaveWarning($keyid,$psl->error,"StarbaseList.xml");
		} else {
                        //clear the poS list
                        db_uquery("DELETE FROM `apistarbaselist` WHERE `corporationID`=$corporationID;");
			$rows=$psl->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();		
				$sql="INSERT INTO `apistarbaselist` VALUES(".
				$attrs->itemID.",".
				$attrs->typeID.",".
				$attrs->locationID.",".
				$attrs->moonID.",".
				$attrs->state.",".
				ins_string($attrs->stateTimestamp).",".
				ins_string($attrs->onlineTimestamp).",".
				$attrs->standingOwnerID.",".
				$corporationID.
				");"; /* ON DUPLICATE KEY UPDATE 
                                state=".$attrs->state.",".
				"stateTimestamp=".ins_string($attrs->stateTimestamp).",".
				"onlineTimestamp=".ins_string($attrs->onlineTimestamp).";"; */
				db_uquery($sql);
			}
			apiSaveOK($keyid,"StarbaseList.xml");
		}
	} else {
		warning("StarbaseList.xml",$FEED_BLOCKED);
	}
	
	//POCOS LIST: https://api.eveonline.com/corp/CustomsOffices.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	//<rowset name="pocos" key="itemID" columns="itemID,solarSystemID,solarSystemName,reinforceHour,allowAlliance,allowStandings,standingLevel,taxRateAlliance,taxRateCorp,taxRateStandingHigh,taxRateStandingGood,taxRateStandingNeutral,taxRateStandingBad,taxRateStandingHorrible" />
	if (!apiCheckErrors($keyid,"CustomsOffices.xml")) {
		$ppl=get_xml_contents("https://api.eveonline.com/corp/CustomsOffices.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/CustomsOffices_$keyid.xml",60*60);
		if (isset($ppl->error)) {
			apiSaveWarning($keyid,$ppl->error,"CustomsOffices.xml");
		} else {
                        //clear the poco list
                        db_uquery("DELETE FROM `apipocolist` WHERE `corporationID`=$corporationID;");
                        //update the poco list
			$rows=$ppl->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();		
				$sql="INSERT INTO `apipocolist` VALUES(".
				$attrs->itemID.",".
				$attrs->solarSystemID.",".
				ins_string($attrs->solarSystemName).",".
				$attrs->reinforceHour.",".
				$attrs->allowAlliance.",".
				$attrs->allowStandings.",".
				$attrs->standingLevel.",".
				$attrs->taxRateAlliance.",".
				$attrs->taxRateCorp.",".
				$attrs->taxRateStandingHigh.",".
				$attrs->taxRateStandingGood.",".
				$attrs->taxRateStandingNeutral.",".
				$attrs->taxRateStandingBad.",".
				$attrs->taxRateStandingHorrible.",".
				$corporationID.
				");"; /* ON DUPLICATE KEY UPDATE 
                 reinforceHour=".$attrs->reinforceHour.",".
				"allowAlliance=".$attrs->allowAlliance.",".
				"allowStandings=".$attrs->allowStandings.",".
				"standingLevel=".$attrs->standingLevel.",".
				"taxRateAlliance=".$attrs->taxRateAlliance.",".
				"taxRateCorp=".$attrs->taxRateCorp.",".
				"taxRateStandingHigh=".$attrs->taxRateStandingHigh.",".
				"taxRateStandingGood=".$attrs->taxRateStandingGood.",".
				"taxRateStandingNeutral=".$attrs->taxRateStandingNeutral.",".
				"taxRateStandingBad=".$attrs->taxRateStandingBad.",".
				"taxRateStandingHorrible=".$attrs->taxRateStandingHorrible.
				";";*/
				db_uquery($sql);
			}
			apiSaveOK($keyid,"CustomsOffices.xml");
		}
	} else {
		warning("CustomsOffices.xml",$FEED_BLOCKED);
	}
	
	/************************************************ DONE DOWN TO THIS LINE ****************************************************/
	
	//POS DETAILS: https://api.eveonline.com/corp/StarbaseDetail.xml.aspx
	//Parameters	 keyID, vCode, itemID
	//Cache Time (minutes)	 1380
	//Needs specific ID from list! Need to get POS list first.
	/*$psd=get_xml_contents("https://api.eveonline.com/corp/StarbaseDetail.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/StarbaseDetail_$keyid.xml",1380*60);
	if (isset($psd->error)) {
		warning("StarbaseDetail.xml",$psd->error);
	}*/
	
	//Base URL	https://api.eveonline.com/corp/AccountBalance.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	if (!apiCheckErrors($keyid,"AccountBalance.xml")) {
		$dat=get_xml_contents("https://api.eveonline.com/corp/AccountBalance.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/AccountBalance_$keyid.xml",60*60);
		if (isset($dat->error)) {
			apiSaveWarning($keyid,$dat->error,"AccountBalance.xml");
		} else {
			db_uquery("DELETE FROM `apiaccountbalance` WHERE corporationID=$corporationID;");
			$rows=$dat->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();			
				$sql="INSERT INTO `apiaccountbalance` VALUES(".
				$attrs->accountID.",".
				$attrs->accountKey.",".
				$attrs->balance.",".
				$corporationID.
				");";
				db_uquery($sql);
			}
			apiSaveOK($keyid,"AccountBalance.xml");
		}
	} else {
		warning("AccountBalance.xml",$FEED_BLOCKED);
	}
	
	//Base URL	https://api.eveonline.com/corp/ContactList.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 1380
	if (!apiCheckErrors($keyid,"ContactList.xml")) {
		$dat=get_xml_contents("https://api.eveonline.com/corp/ContactList.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/ContactList_$keyid.xml",1380*60);
		if (isset($dat->error)) {
			apiSaveWarning($keyid,$dat->error,"ContactList.xml");
		} else {
			db_uquery("DELETE FROM `apicontactlist` WHERE corporationID=$corporationID;");
			$rows=$dat->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();			
				$sql="INSERT INTO `apicontactlist` VALUES(".
				$attrs->contactID.",".
				ins_string($attrs->contactName).",".
				$attrs->standing.",".
				$corporationID.
				");";
				db_uquery($sql);
			}
			apiSaveOK($keyid,"ContactList.xml");
		}
	} else {
		warning("ContactList.xml",$FEED_BLOCKED);
	}
	
	//Base URL	https://api.eveonline.com/corp/Contracts.xml.aspx
	//Parameters	 userID, apiKey
	//Cache Time (minutes)	 15
	if (!apiCheckErrors($keyid,"Contracts.xml")) {
		$dat=get_xml_contents("https://api.eveonline.com/corp/Contracts.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/Contracts_$keyid.xml",15*60);
		if (isset($dat->error)) {
			apiSaveWarning($keyid,$dat->error,"Contracts.xml");
		} else {
			//db_uquery("DELETE FROM `apicontracts` WHERE corporationID=$corporationID;");
			$rows=$dat->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();			
				$sql="INSERT IGNORE INTO `apicontracts` VALUES(".
				$attrs->contractID.",".
				$attrs->issuerID.",".
				$attrs->issuerCorpID.",".
				$attrs->assigneeID.",".
				$attrs->acceptorID.",".
				$attrs->startStationID.",".
				$attrs->endStationID.",".
				ins_string($attrs->type).",".
				ins_string($attrs->status).",".
				ins_string($attrs->title).",".
				$attrs->forCorp.",".
				ins_string($attrs->availability).",".
				ins_string($attrs->dateIssued).",".
				ins_string($attrs->dateExpired).",".
				ins_string($attrs->dateAccepted).",".
				$attrs->numDays.",".
				ins_string($attrs->dateCompleted).",".
				$attrs->price.",".
				$attrs->reward.",".
				$attrs->collateral.",".
				$attrs->buyout.",".
				$attrs->volume.",".
				$corporationID.
				") ON DUPLICATE KEY UPDATE ".
				"dateExpired=".ins_string($attrs->dateExpired).",".
				"dateAccepted=".ins_string($attrs->dateAccepted).",".
				"dateCompleted=".ins_string($attrs->dateCompleted).",".
				"acceptorID=".$attrs->acceptorID.",".
				"status=".ins_string($attrs->status).";";
				db_uquery($sql);
			}
			apiSaveOK($keyid,"Contracts.xml");
		}
	} else {
		warning("Contracts.xml",$FEED_BLOCKED);
	}
	
	//Contractitems.
	//First: max_value = SELECT MAX(`contractID`) FROM `apicontractitems`;
	//If null, then = 0
	//Then: SELECT `contractID` FROM apicontracts WHERE `contractID` > max_value;
	//for each contract - fetch items, inert to DB
	//maybe it will work without php checking? i.e. max_value as sub query?
	//it works!! if there is a not-null value. A record with all zeros will do just fine.
	//ContractItems: https://api.eveonline.com/corp/ContractItems.xml.aspx
	//Parameters	 userID, apiKey, contractID 
	//Cache Time (minutes)	 15
	$contracts=db_asocquery("SELECT `contractID` FROM apicontracts WHERE `contractID` > (SELECT MAX(`contractID`) FROM `apicontractitems`) AND `type`!='Courier';");
	if (sizeof($contracts) > 0) {
		foreach($contracts as $con) {
			$contractID=$con['contractID'];
			if (!apiCheckErrors($keyid,"ContractItems.xml")) {
				$cit=get_xml_contents("https://api.eveonline.com/corp/ContractItems.xml.aspx?keyID=${keyid}&vCode=${vcode}&contractID=$contractID","${mycache}/ContractItems_${keyid}.xml",0);
				if (isset($cit->error)) {
					apiSaveWarning($keyid,$cit->error,"ContractItems.xml");
				} else {
					$rows=$cit->result->rowset->row;
					foreach ($rows as $row) {
						$attrs=$row->attributes();
						$sql="INSERT IGNORE INTO apicontractitems VALUES (".
						$contractID.",".
						$attrs->recordID.",".
						$attrs->typeID.",".
						$attrs->quantity.",".
						$attrs->singleton.",".
						$attrs->included.",".
						$corporationID.
						");";
						db_uquery($sql);
					}
					apiSaveOK($keyid,"ContractItems.xml");
				}
			} else {
				warning("ContractItems.xml",$FEED_BLOCKED);
			}	
		}
	}
	
	
	//Base URL	https://api.eveonline.com/corp/ContainerLog.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 Modified Short Cache
	if (!apiCheckErrors($keyid,"ContainerLog.xml")) {
		$dat=get_xml_contents("https://api.eveonline.com/corp/ContainerLog.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/ContainerLog$keyid.xml",180*60);
		if (isset($dat->error)) {
			apiSaveWarning($keyid,$dat->error,"ContainerLog.xml");
		} else {
			db_uquery("DELETE FROM `apicontainerlog` WHERE corporationID=$corporationID;");
			$rows=$dat->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();			
				$sql="INSERT INTO `apicontainerlog` VALUES(".
				ins_string($attrs->logTime).",".
				$attrs->itemID.",".
				$attrs->itemTypeID.",".
				$attrs->actorID.",".
				ins_string($attrs->actorName).",".
				$attrs->flag.",".
				$attrs->locationID.",".
				ins_string($attrs->action).",".
				ins_string($attrs->passwordType).",".
				ins_string($attrs->typeID).",".
				$attrs->quantity.",".
				ins_string($attrs->oldConfiguration).",".
				ins_string($attrs->newConfiguration).",".
				$corporationID.
				");";
				db_uquery($sql);
			}
			apiSaveOK($keyid,"ContainerLog.xml");
		}
	} else {
		warning("ContainerLog.xml",$FEED_BLOCKED);
	}
	
	//Base URL	https://api.eveonline.com/corp/FacWarStats.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	if (!apiCheckErrors($keyid,"FacWarStats.xml")) {
		$dat=get_xml_contents("https://api.eveonline.com/corp/FacWarStats.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/FacWarStats$keyid.xml",60*60);
		if (isset($dat->error)) {
			apiSaveWarning($keyid,$dat->error,"FacWarStats.xml");
		} else {
			db_uquery("DELETE FROM `apifacwarstats` WHERE corporationID=$corporationID;");
			$rows=$dat->result->rowset->row;
			foreach ($rows as $row) {
				//$attrs=$row->attributes();			
				$sql="INSERT INTO `apifacwarstats` VALUES(".
				$row->factionID.",".
				ins_string($row->factionID).",".
				ins_string($row->enlisted).",".
				$row->pilots.",".
				$row->killsYesterday.",".
				$row->killsLastWeek.",".
				$row->killsTotal.",".
				$row->victoryPointsYesterday.",".
				$row->victoryPointsLastWeek.",".
				$row->victoryPointsTotal.",".
				$corporationID.
				");";
				db_uquery($sql);
			}
			apiSaveOK($keyid,"FacWarStats.xml");
		}
	} else {
		warning("FacWarStats.xml",$FEED_BLOCKED);
	}
	
	//ASSET LIST: https://api.eveonline.com/corp/AssetList.xml.aspx
	//Parameters	 keyID, vCode, [characterID]
	//Cache Time (minutes)	 360
	
	if (!apiCheckErrors($keyid,"AssetList.xml")) {
		$dat=get_xml_contents("https://api.eveonline.com/corp/AssetList.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/AssetList_$keyid.xml",360*60);
		if (isset($dat->error)) {
			apiSaveWarning($keyid,$dat->error,"AssetList.xml");
		} else {
			inform("Main","Polling assets, this may take a while...");
			db_uquery("DELETE FROM `apiassets` WHERE corporationID=$corporationID;");
			insertAssets($dat->result->rowset->row,0,0,$corporationID);
			apiSaveOK($keyid,"AssetList.xml");
		}
	} else {
		warning("AssetList.xml",$FEED_BLOCKED);
	}
	
	//Base URL	https://api.eveonline.com/corp/Locations.xml.aspx
	//Parameters	 userID, apiKey, ids
	//Cache Time (minutes)	 1440
	//This feed REQUIRES a list of IDS, for example from Poco List
	
	$result=db_asocquery("SELECT DISTINCT `itemID` FROM `apipocolist` WHERE `corporationID`=$corporationID;");
	$ids="";
	foreach ($result as $row) {
		$ids="${row['itemID']},$ids";
	}
	//cut the last comma
	$ids = substr_replace($ids ,"",-1);
	//if list of IDs isnt empty, ask EVE API for names
	
        if (count($result)>0) {
            if (!apiCheckErrors($keyid,"Locations.xml")) {
                    $url="https://api.eveonline.com/corp/Locations.xml.aspx?keyID=${keyid}&vCode=${vcode}&ids=${ids}";
                    inform("Locations.xml", $url);
                    $dat=get_xml_contents($url,"${mycache}/Locations$keyid.xml",1440*60);
                    if (isset($dat->error)) {
                            apiSaveWarning($keyid,$dat->error,"Locations.xml");
                    } else {
                            db_uquery("DELETE FROM `apilocations` WHERE corporationID=$corporationID;");
                            $rows=$dat->result->rowset->row;
                            foreach ($rows as $row) {
                                    $attrs=$row->attributes();			
                                    $sql="INSERT INTO `apilocations` VALUES(".
                                    $attrs->itemID.",".
                                    ins_string($attrs->itemName).",".
                                    $attrs->x.",".
                                    $attrs->y.",".
                                    $attrs->z.",".
                                    $corporationID.
                                    ");";
                                    db_uquery($sql);
                            }
                            apiSaveOK($keyid,"Locations.xml");
                    }
            } else {
                    warning("Locations.xml",$FEED_BLOCKED);
            }
        }
	
	//Base URL	https://api.eveonline.com/corp/Medals.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	https://api.eveonline.com/corp/MemberMedals.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	https://api.eveonline.com/corp/MemberSecurity.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	https://api.eveonline.com/corp/MemberSecurityLog.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	https://api.eveonline.com/corp/Standings.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	https://api.eveonline.com/corp/OutpostList.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 1380
	
	//Base URL	https://api.eveonline.com/corp/OutpostServiceDetail.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 1380
	
	//Base URL	https://api.eveonline.com/corp/Shareholders.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	https://api.eveonline.com/corp/Titles.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//KILLBOARD: https://api.eveonline.com/corp/KillLog.xml.aspx
	//Parameters	 userID, apiKey, beforeKillID, characterID
	//Cache Time (minutes)	 60
        if (!apiCheckErrors($keyid,"KillLog.xml")) {
		$klg=get_xml_contents("https://api.eveonline.com/corp/KillLog.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/KillLog_$keyid.xml",60*60);
                if (isset($klg->error)) {
                      apiSaveWarning($keyid,$klg->error,"KillLog.xml");
                }
	} else {
		warning("KillLog.xml",$FEED_BLOCKED);
	}
	 //else {

		
	/*//*********************************************** NEW API PARSE BLOCK
	if (!apiCheckErrors($keyid,"EXPORT.xml")) {
		$dat=get_xml_contents("https://api.eveonline.com/corp/EXPORT.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/EXPORT_$keyid.xml",15*60);
		if (isset($dat->error)) {
			apiSaveWarning($keyid,$dat->error,"EXPORT.xml");
		} else {
			$rows=$dat->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();			
				$sql="";
				db_uquery($sql);
			}
			apiSaveOK($keyid,"EXPORT.xml");
		}
	} else {
		warning("EXPORT.xml",$FEED_BLOCKED);
	}
	//*********************************************** END API PARSE BLOCK*/
	
	
	/*
	//POLL CORP SIMPLE MEMBER NAMES
	$result=db_asocquery("SELECT DISTINCT aij.`installerID` FROM `apiindustryjobs` aij
LEFT JOIN `apicorpmembers` acm ON acm.installerID=aij.installerID
WHERE acm.`installerID` IS NULL;");
	$unknownIDs="";
	foreach ($result as $row) {
		$unknownIDs="${row['installerID']},$unknownIDs";
	}
	//cut the last comma
	$unknownIDs = substr_replace($unknownIDs ,"",-1);
	//if list of IDs isnt empty, ask EVE API for names
	if (!empty($unknownIDs)) {
		$ecn=get_xml_contents("https://api.eveonline.com/eve/CharacterName.xml.aspx?IDs=${unknownIDs}","${mycache}/CharacterName_$keyid.xml",5*60);
		if (isset($ecn->error)) {
			warning("CharacterName.xml",$ecn->error);
		} else {
			$rows=$ecn->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();
				$sql="INSERT INTO apicorpmembers VALUES (".$attrs->characterID.",'".addslashes($attrs->name)."');";
				db_uquery($sql);
			}
		}
	}
	*/
} //END MAIN LOOP

inform("Main","Polling global feeds...");

//Base URL	https://api.eveonline.com/eve/ConquerableStationList.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 1 (1440)
if (!apiCheckErrors($keyid,"ConquerableStationList.xml")) {
	$dat=get_xml_contents("https://api.eveonline.com/eve/ConquerableStationList.xml.aspx","${mycache}/ConquerableStationList.xml",1440*60);
	if (isset($dat->error)) {
		apiSaveWarning(0,$dat->error,"ConquerableStationList.xml");
	} else {
		db_uquery("DELETE FROM apiconquerablestationslist WHERE true;");
		$rows=$dat->result->rowset->row;
		foreach ($rows as $row) {
			$attrs=$row->attributes();			
			$sql="INSERT INTO apiconquerablestationslist VALUES(".
			$attrs->stationID.",".
			ins_string($attrs->stationName).",".
			$attrs->stationTypeID.",".
			$attrs->solarSystemID.",".
			$attrs->corporationID.",".
			ins_string($attrs->corporationName).
			");";
			db_uquery($sql);
		}
		apiSaveOK(0,"ConquerableStationList.xml");
	}
} else {
	warning("ConquerableStationList.xml",$FEED_BLOCKED);
}

//Base URL	https://api.eveonline.com/eve/AllianceList.xml.aspx
//Parameters	 version
//Cache Time (minutes)	 1 (1440)

//Base URL	https://api.eveonline.com/eve/CertificateTree.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 1 (1440)

//Base URL	https://api.eveonline.com/eve/ErrorList.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60 (1440)
if (!apiCheckErrors(0,"ErrorList.xml")) {
	$dat=get_xml_contents("https://api.eveonline.com/eve/ErrorList.xml.aspx","${mycache}/ErrorList.xml",1440*60);
	if (isset($dat->error)) {
		apiSaveWarning(0,$dat->error,"ErrorList.xml");
	} else {
		db_uquery("DELETE FROM apierrorlist WHERE true;");
		$rows=$dat->result->rowset->row;
		foreach ($rows as $row) {
			$attrs=$row->attributes();			
			$sql="INSERT INTO apierrorlist VALUES(".
			$attrs->errorCode.",".
			ins_string($attrs->errorText).
			");";
			db_uquery($sql);
		}
		apiSaveOK(0,"ErrorList.xml");
	}
} else {
	warning("ErrorList.xml",$FEED_BLOCKED);
}

//Base URL	https://api.eveonline.com/eve/FacWarStats.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60

//Base URL	https://api.eveonline.com/eve/FacWarTopStats.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60

//Base URL	https://api.eveonline.com/eve/RefTypes.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 1440
if (!apiCheckErrors(0,"RefTypes.xml")) {
	$dat=get_xml_contents("https://api.eveonline.com/eve/RefTypes.xml.aspx","${mycache}/RefTypes.xml",1440*60);
	if (isset($dat->error)) {
		apiSaveWarning(0,$dat->error,"RefTypes.xml");
	} else {
		db_uquery("DELETE FROM apireftypes WHERE true;");
		$rows=$dat->result->rowset->row;
		foreach ($rows as $row) {
			$attrs=$row->attributes();			
			$sql="INSERT INTO apireftypes VALUES(".
			$attrs->refTypeID.",".
			ins_string($attrs->refTypeName).
			");";
			db_uquery($sql);
		}
		apiSaveOK(0,"RefTypes.xml");
	}
} else {
	warning("RefTypes.xml",$FEED_BLOCKED);
}

//Base URL	https://api.eveonline.com/eve/SkillTree.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 1440

//Base URL	https://api.eveonline.com/server/ServerStatus.xml.aspx/
//Parameters	 none
//Cache Time (minutes)	 3

//Base URL	https://api.eveonline.com/map/Jumps.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60

//Base URL	https://api.eveonline.com/map/Kills.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60

//Base URL	https://api.eveonline.com/map/FacWarSystems.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60 (1440)

inform("Main","Polling eve-central.com feeds...");

//Base URL	http://api.eve-central.com/api/marketstat
//Parameters	 typeID, usesystem=30000142
//Cache Time (minutes)	 60
$MAXTYPES=30;
$amountTypes=db_query("SELECT COUNT(*) FROM cfgmarket;");
$amountTypes=$amountTypes[0][0];
for ($i=0; $i < ceil($amountTypes / $MAXTYPES); $i++) {
	//inform("Main","Getting data for TypeIDs... ".$i*$MAXTYPES." of $amountTypes");
	$TYPES='';
	$configuredTypes=db_asocquery("SELECT * FROM cfgmarket LIMIT ".$i*$MAXTYPES.",${MAXTYPES};");
	foreach ($configuredTypes as $type) {
		$TYPES=$TYPES."&typeid=".$type['typeID'];
	}
	//echo("DEBUG: ".$TYPES."\r\n");
	if (!apiCheckErrors(0,"eve-central.com")) {
		$dat=get_xml_contents("http://api.eve-central.com/api/marketstat?usesystem=30000142${TYPES}","${mycache}/marketstat_$i.xml",60*60);
		if (isset($dat->error)) {
			apiSaveWarning(0,$dat->error,"eve-central.com/marketstat.xml");
		} else {
			$rows=$dat->marketstat->type;
			foreach ($rows as $row) {
				$attrs=$row->attributes();
				db_uquery("DELETE FROM apiprices WHERE typeID=".$attrs->id.";");
				//echo("DEBUG: typeID=".$attrs->id."\r\n");
				$buy="INSERT INTO apiprices VALUES(".
				$attrs->id.",".
				$row->buy->volume.",".
				$row->buy->avg.",".
				$row->buy->max.",".
				$row->buy->min.",".
				$row->buy->stddev.",".
				$row->buy->median.",".
				$row->buy->percentile.
				",'buy');";
				$sell="INSERT INTO apiprices VALUES(".
				$attrs->id.",".
				$row->sell->volume.",".
				$row->sell->avg.",".
				$row->sell->max.",".
				$row->sell->min.",".
				$row->sell->stddev.",".
				$row->sell->median.",".
				$row->sell->percentile.
				",'sell');";
				db_uquery($buy);
				db_uquery($sell);
			}
			apiSaveOK(0,"eve-central.com/marketstat.xml");
			//be gentle to eve-central.com, wait before asking for another batch.
			sleep(2);
		}
	} else {
		warning("eve-central.com/marketstat.xml",$FEED_BLOCKED);
	}
}

//REMOVE LOCK FILE
lock_unset($mylock);

//CALCULATE TIME
$time_end = microtime_float();
$time = $time_end - $time_start;
//2013-03-28 15:20:40
$timestamp=date("Y-m-d H:i:s");
db_uquery("INSERT INTO `apipollerstats` VALUES (DEFAULT, '$timestamp', $time);");
inform("Main","Success! Import took $time seconds.");
?>
