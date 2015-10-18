<?php
$POLLER_VERSION="26";
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
$API_BASEURL="https://api.eveonline.com"; 
$CREST_BASEURL="https://public-crest.eveonline.com";
$USER_AGENT="LMeve/1.0 API Poller Version/$POLLER_VERSION";

$FEED_BLOCKED="This feed is blocked due to previous errors.";
$FEED_URL_PROBLEM="Can't get CREST url from CREST root.";

include_once('libpoller.php');

/*************************************************************************************************/

//PROGRAM BEGINNING, START TIME (for performance measurements)
$time_start = microtime_float();

//TRY TO CREATE LOCK FILE OR EXIT
//if (!lock_set($mylock)) critical("Main","Lock file already exists.");
if (!lock_set($mylock)) warning("Main","Lock file already exists.");

//MAIN PROGRAM LOOP - POLL DATA FOR EACH KEY/CODE PAIR

if ($argc != 3) die("Personal API Killmail loader.\r\nUse: php ".__FILE__." keyID vCode\r\n");
	
	$keyid=$argv[1];
	$vcode=$argv[2];
	
	inform("Main","Polling keyID $keyid...");
        
        $characters=array();
	
	if (!apiCheckErrors($keyid,"APIKeyInfo.xml")) {
		$aki=get_xml_contents("$API_BASEURL/account/APIKeyInfo.xml.aspx?keyID=${keyid}&vCode=${vcode}","${mycache}/APIKeyInfo_$keyid.xml",0*60);
		if (isset($aki->error)) {
			apiSaveWarning($keyid,$aki->error,"APIKeyInfo.xml");
			continue;
		} else {
			$rows=$aki->result->key->rowset->row;
                        if (count($rows)>0) foreach($rows as $row) {
                            $attr=$row->attributes();
                            array_push($characters,$attr);
                            echo("character=".$attr['characterName']." characterID=".$attr['characterID']."\r\n");
                        }
			apiSaveOK($keyid,"APIKeyInfo.xml");
		}
	} else {
		warning("APIKeyInfo.xml",$FEED_BLOCKED);
		continue;
	}
	if (count($characters)>0) {
            foreach($characters as $row) {
                $characterID=$row['characterID'];

	//KILLBOARD: $API_BASEURL/corp/KillLog.xml.aspx
	//Parameters	 userID, apiKey, beforeKillID, characterID
	//Cache Time (minutes)	 60
        if (!apiCheckErrors($keyid,"KillLog.xml")) {
		$klg=get_xml_contents("$API_BASEURL/char/KillLog.xml.aspx?keyID=${keyid}&vCode=${vcode}&characterID=${characterID}","${mycache}/KillLog_${keyid}_${characterID}.xml",60*60);
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
            }
        } else {
            die("This keyID has no characters available.\r\n");
        }
//REMOVE LOCK FILE
lock_unset($mylock);

//CALCULATE TIME
$time_end = microtime_float();
$time = $time_end - $time_start;
//2013-03-28 15:20:40
inform("Main","Success! Import took $time seconds.");
?>
