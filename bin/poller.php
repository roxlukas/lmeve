<?php
$POLLER_VERSION="27";
$POLLER_MAX_TIME=900;
set_time_limit($POLLER_MAX_TIME-20); //poller can work for up to 15 minutes 
//(minus 20 seconds so the next cron cycle can work correctly), afterwards it should die
$mypath=str_replace('\\','/',dirname(__FILE__));
$mylog=$mypath."/../var/poller.txt";
$httplog=$mypath."/../var/http_errors.txt";
$mylock=$mypath."/../var/poller.lock";
$mycache=$mypath."/../var";
$mytmp=$mypath."/../tmp";
$MAX_ERRORS=10; //ignore first x errors

/**
 * API Server configuration
 * 
 * You can either use Tranquility URLs, or Singularity's:
 * 
 * NOTE: No trailing slash!!
 * 
 * TRANQ:
 * $API_BASEURL="https://api.eveonline.com"; 
 * $CREST_BASEURL="http://public-crest.eveonline.com";
 * 
 * SISI:
 * Get TEST API Keys here: https://community.testeveonline.com/support/api-key 
 * $API_BASEURL="https://api.testeveonline.com"; 
 * $CREST_BASEURL="http://public-crest-sisi.testeveonline.com";
 */

include_once("$mypath/../config/config.php"); //API URLs are now in config.php

if (!isset($API_BASEURL)) {
    echo('WARNING: $API_BASEURL isn\'t set in config.php. Using default XML API URL https://api.eveonline.com');
    $API_BASEURL="https://api.eveonline.com"; 
}
if (!isset($CREST_BASEURL)) {
    echo('WARNING: $CREST_BASEURL isn\'t set in config.php. Using default CREST API URL https://crest-tq.eveonline.com');
    $CREST_BASEURL="https://crest-tq.eveonline.com"; 
}    
    
$USER_AGENT="LMeve/1.0 API Poller Version/$POLLER_VERSION";

$FEED_BLOCKED="This feed is blocked due to previous errors.";
$FEED_URL_PROBLEM="Can't get CREST url from CREST root.";

date_default_timezone_set(@date_default_timezone_get());
//set_include_path("$mypath/../include");
include_once("$mypath/../include/log.php");
include_once("$mypath/../include/db.php");
include_once("$mypath/../include/configuration.php");
include_once("$mypath/../include/killboard.php");

include_once('libpoller.php');       

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
		$aki=get_xml_contents("$API_BASEURL/account/APIKeyInfo.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/APIKeyInfo_$keyid.xml",0*60);
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
		$mtr=get_xml_contents("$API_BASEURL/corp/MemberTracking.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/MemberTracking_$keyid.xml",60*60);
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
        
	/* CRIUS CHANGES
            Added /corp/Facilities.xml.aspx (cache: 1 hour)
            Returns a list of all outpost and POS industrial facilities your corporation owns.

            Added /corp/IndustryJobsHistory.xml.aspx (cache: 6 hours)
            Returns a list of running and completed jobs for your corporation, up to 90 days or 10000 rows.

            Added /char/IndustryJobsHistory.xml.aspx (cache: 6 hours)
            Returns a list of running and completed jobs for your character, up to 90 days or 10000 rows.

            Modified /corp/IndustryJobs.xml.aspx (cache: 15 minutes)
            Returns a list of running jobs for your corporation, up to 90 days or 10000 rows.

            Modified /char/IndustryJobs.xml.aspx (cache: 15 minutes)
            Returns a list of running jobs for your character, up to 90 days or 10000 rows.
        */
        
	//POLL INDUSTRY JOBS
        /*
         * corp_IndustryJobs.xml
         * 
         * jobID,installerID,installerName,facilityID,solarSystemID,solarSystemName,stationID,
         * activityID,blueprintID,blueprintTypeID,blueprintTypeName,blueprintLocationID,
         * outputLocationID,runs,cost,teamID,licensedRuns,probability,productTypeID,productTypeName,
         * status,timeInSeconds,startDate,endDate,pauseDate,completedDate,completedCharacterID
         * 
	 * <row jobID="226177178" installerID="170029085" installerName="Raksha Delam" facilityID="1015061849537" solarSystemID="30002797" solarSystemName="Kaunokka" stationID="1015061829404" activityID="3" blueprintID="1002679594531" blueprintTypeID="2047" blueprintTypeName="Damage Control I Blueprint" blueprintLocationID="1015061849537" outputLocationID="1015061849537" runs="10" cost="122795.0000" teamID="0" licensedRuns="200" probability="1" productTypeID="0" productTypeName="" status="1" timeInSeconds="374400" startDate="6/29/2014 4:08:46 AM" endDate="7/3/2014 12:08:46 PM" pauseDate="1/1/0001 12:00:00 AM" completedDate="1/1/0001 12:00:00 AM" completedCharacterID="0"/>
         * 
         * 
         * 
         * corp_IndustryJobsHistory.xml
         * 
         * jobID,installerID,installerName,facilityID,solarSystemID,solarSystemName,stationID,
         * activityID,blueprintID,blueprintTypeID,blueprintTypeName,blueprintLocationID,
         * outputLocationID,runs,cost,teamID,licensedRuns,probability,productTypeID,productTypeName,
         * status,timeInSeconds,startDate,endDate,pauseDate,completedDate,completedCharacterID
         * 
         * <row jobID="226177166" installerID="90471974" installerName="Anton Renard" facilityID="1015061849557" solarSystemID="30002797" solarSystemName="Kaunokka" stationID="1015061829404" activityID="1" blueprintID="1013075909250" blueprintTypeID="3187" blueprintTypeName="Neutron Blaster Cannon II Blueprint" blueprintLocationID="1015061849557" outputLocationID="1015061849557" runs="10" cost="1257015.0000" teamID="0" licensedRuns="10" probability="1" productTypeID="0" productTypeName="" status="1" timeInSeconds="34775" startDate="6/29/2014 4:01:59 AM" endDate="6/29/2014 1:41:34 PM" pauseDate="1/1/0001 12:00:00 AM" completedDate="1/1/0001 12:00:00 AM" completedCharacterID="0"/>
         * 
         * 
         * 
         * corp_facilities.xml
         * 
         * facilityID,typeID,typeName,solarSystemID,solarSystemName,regionID,regionName,starbaseModifier,tax
         * 
         * <row facilityID="1015061849081" typeID="28351" typeName="Design Laboratory" solarSystemID="30002797" solarSystemName="Kaunokka" regionID="10000033" regionName="The Citadel" starbaseModifier="0.94" tax="0"/>		
         * 
         */
        
        if (!apiCheckErrors($keyid,"IndustryJobs.xml")) {
		$ijl=get_xml_contents("$API_BASEURL/corp/IndustryJobs.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/IndustryJobs_$keyid.xml",15*60);
		if (isset($ijl->error)) {
			apiSaveWarning($keyid,$ijl->error,"IndustryJobs.xml");
		} else {
			$rows=$ijl->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();
                                criusInsert($attrs,$corporationID);
			}
			apiSaveOK($keyid,"IndustryJobs.xml");
		}
	} else {
		warning("IndustryJobs.xml",$FEED_BLOCKED);
	}        
        //Crius new endpoint - jobs history
        if (!apiCheckErrors($keyid,"IndustryJobsHistory")) {
		$ijl=get_xml_contents("$API_BASEURL/corp/IndustryJobsHistory.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/IndustryJobsHistory_$keyid.xml",6*60*60);
		if (isset($ijl->error)) {
			apiSaveWarning($keyid,$ijl->error,"IndustryJobsHistory.xml");
		} else {
			$rows=$ijl->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();
				criusInsert($attrs,$corporationID);
			}
			apiSaveOK($keyid,"IndustryJobsHistory.xml");
		}
	} else {
		warning("IndustryJobsHistory.xml",$FEED_BLOCKED);
	}
        
        //CRIUS FACILITIES ENDPOINT
        // /corp/Facilities.xml.aspx
        // Cache: 60 min
        if (!apiCheckErrors($keyid,"Facilities.xml")) {
		$fac=get_xml_contents("$API_BASEURL/corp/Facilities.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/Facilities_$keyid.xml",60*60);
		if (isset($fac->error)) {
			apiSaveWarning($keyid,$fac->error,"Facilities.xml");
		} else {
			db_uquery("DELETE FROM apifacilities WHERE corporationID=$corporationID;");
			$rows=$fac->result->rowset->row;
			foreach ($rows as $row) {
//,typeID,typeName,solarSystemID,solarSystemName,regionID,regionName,starbaseModifier,tax,corporationID
				$attrs=$row->attributes();
				$sql="INSERT IGNORE INTO apifacilities VALUES (".
				$attrs->facilityID.",".
                                $attrs->typeID.",".
				ins_string($attrs->typeName).",".
				$attrs->solarSystemID.",".
                                ins_string($attrs->solarSystemName).",".
                                $attrs->regionID.",".
                                ins_string($attrs->regionName).",".
                                $attrs->starbaseModifier.",".
                                $attrs->tax.",".
				$corporationID.
				");";
				db_uquery($sql);
			}
			apiSaveOK($keyid,"Facilities.xml");
		}
	} else {
		warning("Facilities.xml",$FEED_BLOCKED);
	}
	
	//CORP SHEET: $API_BASEURL/corp/CorporationSheet.xml.aspx
	//Parameters	 userID, apiKey, characterID OR corporationID
	//Cache Time (minutes)	 360
	if (!apiCheckErrors($keyid,"CorporationSheet.xml")) {
		$csh=get_xml_contents("$API_BASEURL/corp/CorporationSheet.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/CorporationSheet_$keyid.xml",360*60);
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
	
	//WALLET JOURNAL: 	$API_BASEURL/corp/WalletJournal.xml.aspx
	//Parameters	 userID, apiKey, characterID, (rowCount)
	//Cache Time (minutes)	 15
	$MAXROWS=256; //maximum number of rows from Journal 50-2560
	$accountKeys=db_asocquery("SELECT accountKey FROM apicorps acs JOIN apiwalletdivisions awd ON acs.corporationID=awd.corporationID WHERE keyID=$keyid;");
	if (sizeof($accountKeys) > 0) {
		foreach($accountKeys as $acct) {
			$accountKey=$acct['accountKey'];
			if (!apiCheckErrors($keyid,"WalletJournal_$accountKey.xml")) {
				$wlj=get_xml_contents("$API_BASEURL/corp/WalletJournal.xml.aspx?keyID=${keyid}&vCode=${vcode}&accountKey=$accountKey&rowCount=${MAXROWS}","${mycache}/WalletJournal_${keyid}_${accountKey}.xml",15*60);
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
	//WALLET TRANSACTIONS: $API_BASEURL/corp/WalletTransactions.xml.aspx
	//Parameters	 userID, apiKey, accountKey --characterID-- 
	//Cache Time (minutes)	 15
	$accountKeys=db_asocquery("SELECT accountKey FROM apicorps acs JOIN apiwalletdivisions awd ON acs.corporationID=awd.corporationID WHERE keyID=$keyid;");
	if (sizeof($accountKeys) > 0) {
		foreach($accountKeys as $acct) {
			$accountKey=$acct['accountKey'];
			if (!apiCheckErrors($keyid,"WalletTransactions_$accountKey.xml")) {
				$wlt=get_xml_contents("$API_BASEURL/corp/WalletTransactions.xml.aspx?keyID=${keyid}&vCode=${vcode}&accountKey=$accountKey","${mycache}/WalletTransactions_${keyid}_${accountKey}.xml",15*60);
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

	//MARKET ORDERS: $API_BASEURL/corp/MarketOrders.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	if (!apiCheckErrors($keyid,"MarketOrders.xml")) {
		$mao=get_xml_contents("$API_BASEURL/corp/MarketOrders.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/MarketOrders$keyid.xml",60*60);
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
	
	//POS LIST: $API_BASEURL/corp/StarbaseList.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 360
	if (!apiCheckErrors($keyid,"StarbaseList.xml")) {
		$psl=get_xml_contents("$API_BASEURL/corp/StarbaseList.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/StarbaseList_$keyid.xml",15*60);
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
        
        //StarbaseDetails.
	//First: max_value = SELECT MAX(`contractID`) FROM `apicontractitems`;
	//If null, then = 0
	//Then: SELECT `contractID` FROM apicontracts WHERE `contractID` > max_value;
	//for each contract - fetch items, inert to DB
	//maybe it will work without php checking? i.e. max_value as sub query?
	//it works!! if there is a not-null value. A record with all zeros will do just fine.
	//ContractItems: $API_BASEURL/corp/ContractItems.xml.aspx
	//Parameters	 userID, apiKey, contractID 
	//Cache Time (minutes)	 15
	$starbases=db_asocquery("SELECT `itemID` FROM apistarbaselist WHERE `corporationID`=$corporationID;");
	if (sizeof($starbases) > 0) {
		foreach($starbases as $starbase) {
			$itemID=$starbase['itemID'];
			if (!apiCheckErrors($keyid,"StarbaseDetail.xml")) {
				$stb=get_xml_contents("$API_BASEURL/corp/StarbaseDetail.xml.aspx?keyID=${keyid}&vCode=${vcode}&itemID=$itemID","${mycache}/StarbaseDetail_${keyid}_${itemID}.xml",0);
				if (isset($stb->error)) {
					apiSaveWarning($keyid,"Error for starbase itemID=${itemID}: ".$stb->error,"StarbaseDetail.xml");
				} else {
                                        $state=$stb->result->state;
                                        $stateTimestamp=$stb->result->stateTimestamp;
                                        $onlineTimestamp=$stb->result->onlineTimestamp;
                                        $usageFlags=$stb->result->generalSettings->usageFlags;
                                        $deployFlags=$stb->result->generalSettings->deployFlags;
                                        $allowCorporationMembers=$stb->result->generalSettings->allowCorporationMembers;
                                        $allowAllianceMembers=$stb->result->generalSettings->allowAllianceMembers;
                                        $attrs=$stb->result->combatSettings->useStandingsFrom->attributes();
                                            $useStandingsFrom=$attrs->ownerID;
                                        $attrs=$stb->result->combatSettings->onStandingDrop->attributes();
                                            $onStandingDrop=$attrs->standing;
                                        $attrs=$stb->result->combatSettings->onStatusDrop->attributes();
                                            $onStatusDrop=$attrs->enabled;
                                            $onStatusDropStanding=$attrs->standing;
                                        $attrs=$stb->result->combatSettings->onAggression->attributes();
                                            $onAggression=$attrs->enabled;
                                        $attrs=$stb->result->combatSettings->onCorporationWar->attributes();
                                            $onCorporationWar=$attrs->enabled;
                                        //BIG SQL INSERT
                                        $sql="INSERT INTO apistarbasedetail VALUES (".
                                        $itemID.",".
                                        $state.",".
                                        ins_string($stateTimestamp).",".
                                        ins_string($onlineTimestamp).",".
                                        $usageFlags.",".
                                        $deployFlags.",".
                                        $allowCorporationMembers.",".
                                        $allowAllianceMembers.",".
                                        $useStandingsFrom.",".
                                        $onStandingDrop.",".
                                        $onStatusDrop.",".
                                        $onStatusDropStanding.",".
                                        $onAggression.",".
                                        $onCorporationWar.",".
                                        $corporationID.
                                        ") ON DUPLICATE KEY UPDATE".
                                            " `state`=".$state.
                                            ",`stateTimestamp`=".ins_string($stateTimestamp).
                                            ",`onlineTimestamp`=".ins_string($onlineTimestamp).
                                            ",`usageFlags`=".$usageFlags.
                                            ",`deployFlags`=".$deployFlags.
                                            ",`allowCorporationMembers`=".$allowCorporationMembers.
                                            ",`allowAllianceMembers`=".$allowAllianceMembers.
                                            ",`useStandingsFrom`=".$useStandingsFrom.
                                            ",`onStandingDrop`=".$onStandingDrop.
                                            ",`onStatusDrop`=".$onStatusDrop.
                                            ",`onStatusDropStanding`=".$onStatusDropStanding.
                                            ",`onAggression`=".$onAggression.
                                            ",`onCorporationWar`=".$onCorporationWar.';';
                                        db_uquery($sql);
                                        //FUEL
					$rows=$stb->result->rowset->row; //fuel
					foreach ($rows as $row) {
						$attrs=$row->attributes();
						$sql="INSERT INTO apistarbasefuel VALUES (".
						$itemID.",".
						$attrs->typeID.",".
						$attrs->quantity.",".
						$corporationID.
						") ON DUPLICATE KEY UPDATE".
                                                    " quantity=".$attrs->quantity;
						db_uquery($sql);
					}
					apiSaveOK($keyid,"StarbaseDetail.xml");
				}
			} else {
				warning("StarbaseDetail.xml",$FEED_BLOCKED);
			}	
		}
	}
        
        
       /*
<eveapi version="2">
<currentTime>2015-03-23 15:25:14</currentTime>
<result>
<state>4</state>
<stateTimestamp>2015-03-23 15:54:03</stateTimestamp>
<onlineTimestamp>2014-03-03 16:47:13</onlineTimestamp>
<generalSettings>
<usageFlags>15</usageFlags>
<deployFlags>0</deployFlags>
<allowCorporationMembers>1</allowCorporationMembers>
<allowAllianceMembers>0</allowAllianceMembers>
</generalSettings>
<combatSettings>
<useStandingsFrom ownerID="xxxxxxxx"/>
<onStandingDrop standing="0"/>
<onStatusDrop enabled="0" standing="0"/>
<onAggression enabled="0"/>
<onCorporationWar enabled="1"/>
</combatSettings>
<rowset name="fuel" key="typeID" columns="typeID,quantity">
<row typeID="4051" quantity="2899"/>
<row typeID="16275" quantity="16666"/>
<row typeID="24594" quantity="12663"/>
</rowset>
</result>
<cachedUntil>2015-03-23 16:12:14</cachedUntil>
</eveapi>
        */
	
	//POCOS LIST: $API_BASEURL/corp/CustomsOffices.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	//<rowset name="pocos" key="itemID" columns="itemID,solarSystemID,solarSystemName,reinforceHour,allowAlliance,allowStandings,standingLevel,taxRateAlliance,taxRateCorp,taxRateStandingHigh,taxRateStandingGood,taxRateStandingNeutral,taxRateStandingBad,taxRateStandingHorrible" />
	if (!apiCheckErrors($keyid,"CustomsOffices.xml")) {
		$ppl=get_xml_contents("$API_BASEURL/corp/CustomsOffices.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/CustomsOffices_$keyid.xml",60*60);
		if (isset($ppl->error)) {
			apiSaveWarning($keyid,$ppl->error,"CustomsOffices.xml");
		} else {
                        //clear the poco list
                        db_uquery("DELETE FROM `apipocolist` WHERE `corporationID`=$corporationID;");
                        //update the poco list
			$rows=$ppl->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();		
				$sql="INSERT IGNORE INTO `apipocolist` VALUES(".
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
	
	//POS DETAILS: $API_BASEURL/corp/StarbaseDetail.xml.aspx
	//Parameters	 keyID, vCode, itemID
	//Cache Time (minutes)	 1380
	//Needs specific ID from list! Need to get POS list first.
	/*$psd=get_xml_contents("$API_BASEURL/corp/StarbaseDetail.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/StarbaseDetail_$keyid.xml",1380*60);
	if (isset($psd->error)) {
		warning("StarbaseDetail.xml",$psd->error);
	}*/
	
	//Base URL	$API_BASEURL/corp/AccountBalance.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	if (!apiCheckErrors($keyid,"AccountBalance.xml")) {
		$dat=get_xml_contents("$API_BASEURL/corp/AccountBalance.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/AccountBalance_$keyid.xml",60*60);
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
	
	//Base URL	$API_BASEURL/corp/ContactList.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 1380
	if (!apiCheckErrors($keyid,"ContactList.xml")) {
		$dat=get_xml_contents("$API_BASEURL/corp/ContactList.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/ContactList_$keyid.xml",1380*60);
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
	
	//Base URL	$API_BASEURL/corp/Contracts.xml.aspx
	//Parameters	 userID, apiKey
	//Cache Time (minutes)	 15
	if (!apiCheckErrors($keyid,"Contracts.xml")) {
		$dat=get_xml_contents("$API_BASEURL/corp/Contracts.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/Contracts_$keyid.xml",15*60);
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
        //BUGFIX the loop below would fail if there were two corporation in LMeve with contract items to refresh
        //       because there was no corporationID filter in the "SELECT" query
        //       as a result, poller cycle for corp A would ask for corp A and corp B contracts
        //       but API would fail for corp B contracts.
        //       In the corp B cycle, API would fail for corp A contracts instead
	//ContractItems: $API_BASEURL/corp/ContractItems.xml.aspx
	//Parameters	 userID, apiKey, contractID 
	//Cache Time (minutes)	 15
	$contracts=db_asocquery("SELECT `contractID` FROM apicontracts WHERE `corporationID`=$corporationID AND `contractID` > (SELECT MAX(`contractID`) FROM `apicontractitems`) AND `type`!='Courier';");
	if (sizeof($contracts) > 0) {
		foreach($contracts as $con) {
			$contractID=$con['contractID'];
			if (!apiCheckErrors($keyid,"ContractItems.xml")) {
				$cit=get_xml_contents("$API_BASEURL/corp/ContractItems.xml.aspx?keyID=${keyid}&vCode=${vcode}&contractID=$contractID","${mycache}/ContractItems_${keyid}.xml",0);
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
	
	
	//Base URL	$API_BASEURL/corp/ContainerLog.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 Modified Short Cache
	if (!apiCheckErrors($keyid,"ContainerLog.xml")) {
		$dat=get_xml_contents("$API_BASEURL/corp/ContainerLog.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/ContainerLog$keyid.xml",180*60);
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
	
	//Base URL	$API_BASEURL/corp/FacWarStats.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	if (!apiCheckErrors($keyid,"FacWarStats.xml")) {
		$dat=get_xml_contents("$API_BASEURL/corp/FacWarStats.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/FacWarStats$keyid.xml",60*60);
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
	
	//ASSET LIST: $API_BASEURL/corp/AssetList.xml.aspx
	//Parameters	 keyID, vCode, [characterID]
	//Cache Time (minutes)	 360
	
	if (!apiCheckErrors($keyid,"AssetList.xml")) {
		$dat=get_xml_contents("$API_BASEURL/corp/AssetList.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/AssetList_$keyid.xml",360*60);
		if (isset($dat->error)) {
			apiSaveWarning($keyid,$dat->error,"AssetList.xml");
		} else {
			inform("Main","Polling assets, this may take a while...");
			db_uquery("DELETE FROM `apiassets` WHERE `corporationID`=$corporationID;");
			insertAssets($dat->result->rowset->row,0,0,$corporationID);
                        updateOfficeID($corporationID);
			apiSaveOK($keyid,"AssetList.xml");
		}
	} else {
		warning("AssetList.xml",$FEED_BLOCKED);
	}
	
	//Base URL	$API_BASEURL/corp/Locations.xml.aspx
	//Parameters	 userID, apiKey, ids
	//Cache Time (minutes)	 1440
	//This feed REQUIRES a list of IDS, for example from Poco List
	global $LM_EVEDB;
	$result=db_asocquery("SELECT DISTINCT `itemID` FROM `apipocolist`
                WHERE `corporationID`=$corporationID
                UNION
                SELECT DISTINCT `facilityID` FROM `apifacilities`
                WHERE `corporationID`=$corporationID
                UNION
                SELECT DISTINCT `itemID` FROM `apistarbaselist`
                WHERE `corporationID`=$corporationID
                UNION
                SELECT DISTINCT `itemID` FROM `apiassets` apa
                JOIN `$LM_EVEDB`.`invTypes` itp
                ON apa.`typeID`=itp.`typeID`
                WHERE `corporationID`=$corporationID AND itp.`groupID`=404;");
	$ids="";
	foreach ($result as $row) {
		$ids="${row['itemID']},$ids";
	}
	//cut the last comma
	$ids = substr_replace($ids ,"",-1);
	//if list of IDs isnt empty, ask EVE API for names
	
        if (count($result)>0) {
            if (!apiCheckErrors($keyid,"Locations.xml")) {
                    $url="$API_BASEURL/corp/Locations.xml.aspx?keyID=${keyid}&vCode=${vcode}&ids=${ids}";
                    //inform("Locations.xml", $url);
                    $dat=get_xml_contents($url,"${mycache}/Locations$keyid.xml",60*60);
                    if (isset($dat->error)) {
                            apiSaveWarning($keyid,$dat->error,"Locations.xml");
                    } else {
                        //itemID itemName x y z corporationID
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
                                    ") ON DUPLICATE KEY UPDATE ".
                                    "itemName=".ins_string($attrs->itemName).",".
                                    "x=".$attrs->x.",".
                                    "y=".$attrs->y.",".
                                    "z=".$attrs->z.",".
                                    "corporationID=".$corporationID.";";
                                    db_uquery($sql);
                            }
                            apiSaveOK($keyid,"Locations.xml");
                    }
            } else {
                    warning("Locations.xml",$FEED_BLOCKED);
            }
        }
        
        //POLL BLUEPRINTS API
	if (!apiCheckErrors($keyid,"Blueprints.xml")) {
		$mtr=get_xml_contents("$API_BASEURL/corp/Blueprints.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/Blueprints_$keyid.xml",24*60*60);
		if (isset($mtr->error)) {
			apiSaveWarning($keyid,$mtr->error,"Blueprints.xml");
		} else {
			db_uquery("DELETE FROM `apiblueprints` WHERE `corporationID`=$corporationID;");
			$rows=$mtr->result->rowset->row;
			foreach ($rows as $row) {
				$attrs=$row->attributes();
				$sql="INSERT IGNORE INTO apiblueprints VALUES (".
				$attrs->itemID.",".
				$attrs->locationID.",".
				$attrs->typeID.",".
                                ins_string($attrs->typeName).",".
                                $attrs->flagID.",".
                                $attrs->quantity.",".
                                $attrs->timeEfficiency.",".
                                $attrs->materialEfficiency.",".
                                $attrs->runs.",".				
				$corporationID.
				");";
				db_uquery($sql);
			}
                        //ok now insert maxed me and te into cfgbpo
                        echo("DEBUG: saving apiblueprints into cfgbpo...");
                        $sql="INSERT INTO cfgbpo (SELECT typeID, MAX( materialEfficiency ) AS me, MAX( timeEfficiency ) AS pe
                        FROM `apiblueprints`
                        WHERE runs = -1
                        GROUP BY typeID) ON DUPLICATE KEY UPDATE me=VALUES(me), pe=VALUES(pe);";
                        echo("done! \r\n");
			apiSaveOK($keyid,"Blueprints.xml");
		}
	} else {
		warning("Blueprints.xml",$FEED_BLOCKED);
	}
	
	//Base URL	$API_BASEURL/corp/Medals.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	$API_BASEURL/corp/MemberMedals.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	$API_BASEURL/corp/MemberSecurity.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	$API_BASEURL/corp/MemberSecurityLog.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	$API_BASEURL/corp/Standings.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	$API_BASEURL/corp/OutpostList.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 1380
	
	//Base URL	$API_BASEURL/corp/OutpostServiceDetail.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 1380
	
	//Base URL	$API_BASEURL/corp/Shareholders.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60
	
	//Base URL	$API_BASEURL/corp/Titles.xml.aspx
	//Parameters	 userID, apiKey, characterID
	//Cache Time (minutes)	 60

	//KILLBOARD: $API_BASEURL/corp/KillLog.xml.aspx
	//Parameters	 userID, apiKey, beforeKillID, characterID
	//Cache Time (minutes)	 60
        if (!apiCheckErrors($keyid,"KillLog.xml")) {
		$klg=get_xml_contents("$API_BASEURL/corp/KillLog.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/KillLog_$keyid.xml",60*60);
                //echo($klg);
                if (isset($klg->error)) {
                      apiSaveWarning($keyid,$klg->error,"KillLog.xml");
                }  else {
			$kills=$klg->result->rowset->row;
			if (count($kills)>0) foreach ($kills as $kill) {
                            $finalBlowCharacterID=0;
                            //var_dump($kill);
				$a=$kill->attributes();
                                $at=array();
                                $i=array(); //XML kill items
                                $v=$kill->victim->attributes();
                                
                                foreach ($kill->rowset as $rowset) {
                                    $attr=$rowset->attributes();
                                    switch ($attr['name']) {
                                        case 'attackers':
                                            $at=$rowset;
                                            break;
                                        case 'items':
                                            $i=$rowset;
                                            break;
                                    }
                                }
                                
                                $sql="SELECT COUNT(*) AS `count` FROM `apikills` WHERE `killID`=".$a->killID.";";
                                $ret=db_asocquery($sql);
                                $ret=$ret[0]['count'];
                                //echo("ret=$ret\r\n");
                                if ($ret>0) {
                                    //inform('Killog.xml','KillID '.$a->killID.' already exists in db, skipping.');
                                    continue; //skip the rest of the loop if kill already was in the db
                                }
                                
				$sql="INSERT IGNORE INTO `apikills` VALUES( ".
                                    $a->killID.",".
                                    $a->solarSystemID.",".
                                    ins_string($a->killTime).",".
                                    $a->moonID.");";
                                db_uquery($sql);
                                 
                                $sql="INSERT IGNORE INTO `apikillvictims` VALUES( ".
                                    $a->killID.",".
                                    $v->characterID.",".
                                    ins_string($v->characterName).",".
                                    $v->corporationID.",".
                                    ins_string($v->corporationName).",".
                                    $v->allianceID.",".
                                    ins_string($v->allianceName).",".
                                    $v->factionID.",".
                                    ins_string($v->factionName).",".
                                    $v->damageTaken.",".
                                    $v->shipTypeID.");";
				db_uquery($sql);
  
                                if (count($at)>0) foreach($at as $row) {
                                    $attr=$row->attributes();
                                    $sql="INSERT IGNORE INTO `apikillattackers` VALUES( ".
                                        $a->killID.",".
                                        $attr->characterID.",".
                                        ins_string($attr->characterName).",".
                                        $attr->corporationID.",".
                                        ins_string($attr->corporationName).",".
                                        $attr->allianceID.",".
                                        ins_string($attr->allianceName).",".
                                        $attr->factionID.",".
                                        ins_string($attr->factionName).",".
                                        $attr->securityStatus.",".
                                        $attr->damageDone.",".
                                        $attr->finalBlow.",".
                                        $attr->weaponTypeID.",".
                                        $attr->shipTypeID.");";
                                    if ($attr->finalBlow==1) $finalBlowCharacterID=$attr->characterID;
                                    db_uquery($sql);
                                }
                                
                                //get kill items from CREST
                                $killurl="$CREST_BASEURL/killmails/".$a->killID.'/'. killmail_hash($v->characterID, $finalBlowCharacterID, $v->shipTypeID, $a->killTime).'/';
                                $crest_killmail=get_crest_contents($killurl, "${mycache}/crest_killmail.json", 0);
                                //$crest_killmail=get_crest_contents($killurl, "${mycache}/crest_killmail_".$a->killID.".json", 0);
                                
                                if (isset($crest_killmail->victim->items) && getConfigItem('useCRESTkillmails', 'enabled')=='enabled') {
                                    apiSaveOK(0,"CREST /killmails/");
                                    //inform("Killog.xml","Using CREST killmail item list for killID=".$a->killID);
                                    if (count($i)>0) foreach($crest_killmail->victim->items as $row) {
                                        if (!isset($row->quantityDropped)) $row->quantityDropped=0;
                                        if (!isset($row->quantityDestroyed)) $row->quantityDestroyed=0;
                                        $sql="INSERT IGNORE INTO `apikillitems` VALUES( ".
                                            $a->killID.",".
                                            $row->itemType->id.",".
                                            $row->flag.",".
                                            $row->quantityDropped.",".
                                            $row->quantityDestroyed.",".
                                            $row->singleton.");";
                                        db_uquery($sql);
                                    }
                                } else {
                                    if (getConfigItem('useCRESTkillmails', 'enabled')!='enabled') {
                                        warning("Killog.xml","FAILED fetching CREST killmail for killID=".$a->killID." URL=$killurl");
                                        apiSaveWarning(0, $crest_killmail, "CREST /killmails/");
                                    }
                                    //warning("Killog.xml","DATA=".print_r($crest_killmail,TRUE));
                                    //inform("Killog.xml","Using XML data for items.");
                                    //inform("Killog.xml",print_r($kill,TRUE));
                                    //if CREST call didn't work, use XML data instead (which is known to be incomplete)
                                    if (count($i)>0) foreach($i as $row) {
                                        $attr=$row->attributes();
                                        $sql="INSERT IGNORE INTO `apikillitems` VALUES( ".
                                            $a->killID.",".
                                            $attr->typeID.",".
                                            $attr->flag.",".
                                            $attr->qtyDropped.",".
                                            $attr->qtyDestroyed.",".
                                            $attr->singleton.");";
                                        db_uquery($sql);
                                    }
                                }
			}
			apiSaveOK($keyid,"KillLog.xml");
		}
	} else {
		warning("KillLog.xml",$FEED_BLOCKED);
	}
	 //else {

		
	/*//*********************************************** NEW API PARSE BLOCK
	if (!apiCheckErrors($keyid,"EXPORT.xml")) {
		$dat=get_xml_contents("$API_BASEURL/corp/EXPORT.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/EXPORT_$keyid.xml",15*60);
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
		$ecn=get_xml_contents("$API_BASEURL/eve/CharacterName.xml.aspx?IDs=${unknownIDs}","${mycache}/CharacterName_$keyid.xml",5*60);
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

inform("Main","Polling public API feeds...");

//Base URL	$API_BASEURL/eve/ConquerableStationList.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 1 (1440)
if (!apiCheckErrors($keyid,"ConquerableStationList.xml")) {
	$dat=get_xml_contents("$API_BASEURL/eve/ConquerableStationList.xml.aspx","${mycache}/ConquerableStationList.xml",1440*60);
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

//Base URL	$API_BASEURL/eve/AllianceList.xml.aspx
//Parameters	 version
//Cache Time (minutes)	 1 (1440)

//Base URL	$API_BASEURL/eve/CertificateTree.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 1 (1440)

//Base URL	$API_BASEURL/eve/ErrorList.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60 (1440)
if (!apiCheckErrors(0,"ErrorList.xml")) {
	$dat=get_xml_contents("$API_BASEURL/eve/ErrorList.xml.aspx","${mycache}/ErrorList.xml",1440*60);
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

//Base URL	$API_BASEURL/eve/FacWarStats.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60

//Base URL	$API_BASEURL/eve/FacWarTopStats.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60

//Base URL	$API_BASEURL/eve/RefTypes.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 1440
if (!apiCheckErrors(0,"RefTypes.xml")) {
	$dat=get_xml_contents("$API_BASEURL/eve/RefTypes.xml.aspx","${mycache}/RefTypes.xml",1440*60);
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

//Base URL	$API_BASEURL/eve/SkillTree.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 1440

//Base URL	$API_BASEURL/server/ServerStatus.xml.aspx/
//Parameters	 none
//Cache Time (minutes)	 3

//Base URL	$API_BASEURL/map/Jumps.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60

//Base URL	$API_BASEURL/map/Kills.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60

//Base URL	$API_BASEURL/map/FacWarSystems.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 60 (1440)

/******************** CREST PUBLIC FEEDS **************************/

inform("Main","Polling public CREST feeds...");

//Base URL	$API_BASEURL/eve/RefTypes.xml.aspx
//Parameters	 none
//Cache Time (minutes)	 23*60

if (!apiCheckErrors(0,"CREST /market/prices/")) {
        $crestroot=get_crest_root($CREST_BASEURL);
        $urladdr=$crestroot->marketPrices->href;
        //echo("DEBUG: urladdr=$urladdr\r\n");
        if ($urladdr) {
            $dat=get_crest_contents("$CREST_BASEURL/market/prices/","${mycache}/crest_market_prices.json",23*60*60);
            if (isset($dat->error)) {
                    apiSaveWarning(0,$dat,"CREST /market/prices/");
            } else {
                    db_uquery("TRUNCATE TABLE crestmarketprices;");
                    $rows=$dat->items;
                    foreach ($rows as $row) {
                        if (!isset($row->adjustedPrice)) $row->adjustedPrice=0.0;
                        if (!isset($row->averagePrice)) $row->averagePrice=0.0;
                        //typeID (type->id), adjustedPrice, averagePrice
                            $sql="INSERT INTO crestmarketprices VALUES(".
                            $row->type->id.",".
                            $row->adjustedPrice.",".
                            $row->averagePrice.
                            ");";
                            db_uquery($sql);
                    }
                    //var_dump($dat);
                    apiSaveOK(0,"CREST /market/prices/");
            }
        } else {
            warning("CREST /market/prices/",$FEED_URL_PROBLEM);
        }
} else {
	warning("CREST /market/prices/",$FEED_BLOCKED);
}

if (!apiCheckErrors(0,"CREST /industry/systems/")) {
        $crestroot=get_crest_root($CREST_BASEURL);
        $urladdr=$crestroot->industry->systems->href;
        //echo("DEBUG: urladdr=$urladdr\r\n");
        if ($urladdr) {
            $dat=get_crest_contents($urladdr,"${mycache}/crest_industry_systems.json",1*60*60);
            if (isset($dat->error)) {
                    apiSaveWarning(0,$dat,"CREST /industry/systems/");
            } else {
                    db_uquery("TRUNCATE TABLE crestindustrysystems;");
                    $rows=$dat->items;
                    foreach ($rows as $row) {
                        $solarsystemID=$row->solarSystem->id;
                        foreach ($row->systemCostIndices as $sci) {
                            $costIndex=$sci->costIndex;
                            $activityID=$sci->activityID_str;
                            //echo("$solarsystemID,$costIndex,$activityID\r\n");
                            $sql="INSERT INTO crestindustrysystems VALUES(".
                            $solarsystemID.",".
                            $costIndex.",".
                            $activityID.
                            ");";
                            db_uquery($sql);
                        }
                    }
                    //var_dump($dat);
                    apiSaveOK(0,"CREST /industry/systems/");
            }
	}
} else {
	warning("CREST /industry/systems/",$FEED_BLOCKED);
}

/*
{
    "totalCount_str": "5913",
    "items": [
        {
            "alliance": {
                "id_str": "1988009451",
                "href": "https://crest-tq.eveonline.com/alliances/1988009451/",
                "id": 1988009451,
                "name": "Curatores Veritatis Alliance"
            },
            "vulnerabilityOccupancyLevel": 4.1,
            "structureID_str": "866802670",
            "structureID": 866802670,
            "vulnerableStartTime": "2016-05-19T17:48:18",
            "solarSystem": {
                "id_str": "30001235",
                "href": "https://crest-tq.eveonline.com/solarsystems/30001235/",
                "id": 30001235,
                "name": "BR-N97"
            },
            "vulnerableEndTime": "2016-05-19T22:11:42",
            "type": {
                "id_str": "32226",
                "href": "https://crest-tq.eveonline.com/types/32226/",
                "id": 32226,
                "name": "Territorial Claim Unit"
            }
        },
        {
            "alliance": {
                "id_str": "1086308227",
                "href": "https://crest-tq.eveonline.com/alliances/1086308227/",
                "id": 1086308227,
                "name": "Rebel Alliance of New Eden"
            },
            "vulnerabilityOccupancyLevel": 4.5,
            "structureID_str": "867905377",
            "structureID": 867905377,
            "vulnerableStartTime": "2016-05-19T22:00:00",
            "solarSystem": {
                "id_str": "30000538",
                "href": "https://crest-tq.eveonline.com/solarsystems/30000538/",
                "id": 30000538,
                "name": "J7-BDX"
            },
            "vulnerableEndTime": "2016-05-20T02:00:00",
            "type": {
                "id_str": "32226",
                "href": "https://crest-tq.eveonline.com/types/32226/",
                "id": 32226,
                "name": "Territorial Claim Unit"
            }
        },
 */

/******************** EVE-CENTRAL PUBLIC FEEDS **************************/

inform("Main","Polling eve-central.com feeds...");

//Base URL	http://api.eve-central.com/api/marketstat
//Parameters	 typeID, usesystem=30000142
//Cache Time (minutes)	 60
$MAXTYPES=30;
$useSystem=getConfigItem('marketSystemID', '30000142');
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
		$dat=get_xml_contents("http://api.eve-central.com/api/marketstat?usesystem=${useSystem}${TYPES}","${mycache}/marketstat_$i.xml",60*60);
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
