<?php
$POLLER_VERSION="28";
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

include_once("$mypath/../config/config.php"); //API URLs are now in config.php

if (!isset($ESI_BASEURL)) {
    echo('WARNING: $ESI_BASEURL isn\'t set in config.php. Using default ESI API URL https://esi.tech.ccp.is');
    $ESI_BASEURL="https://esi.tech.ccp.is"; 
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
include_once("$mypath/../include/ssofunctions.php");

include_once('ESI.class.php');

/*************************************************************************************************/

//PROGRAM BEGINNING, START TIME (for performance measurements)
$time_start = microtime_float();

//TRY TO CREATE LOCK FILE OR EXIT
//if (!lock_set($mylock)) critical("Main","Lock file already exists.");
if (!lock_set($mylock)) warning("Main","Lock file already exists.");

//LOAD API KEYS
$api_keys=load_esitokens_from_db(); //LOAD FROM DB

//MAIN PROGRAM LOOP - POLL DATA FOR EACH KEY/CODE PAIR
foreach ($api_keys as $api_key) {
	
	$tokenID = $api_key['tokenID'];
	inform("Main","Polling tokenID $tokenID...");
        
        try {
            inform("Main","new ESI(tokenID=$tokenID)...");
            $ESI = new ESI($tokenID);
            inform("Main","ESI(tokenID=$tokenID)->updateAll()...");
            $ESI->updateAll();
        } catch (Exception $e) {
            warning("Main","Exception occured in ESI(tokenID=$tokenID): " . $e->getMessage());
        }
        /*
        $cacheFileName="${mycache}/esi_${tokenID}_characters_${characterID}.json";
        $cacheTime = 3600;
        $route = 'v4/characters';
        if (!esiCheckErrors($tokenID,$route)) {
            $ret = get_esi_contents("$ESI_BASEURL/$route/$characterID/", $cacheFileName, $cacheTime);
            if (isset($ret->error)) {
                    esiSaveWarning($keyid,$ret,$route);
                    continue;
            } else {
                $corporationID = null;
                if (isset($ret->corporation_id)) {
                    $corporationID = $ret->corporation_id;
                } else {
                    warning("ESI","Cannot get corporationID affiliation for characterID=$characterID.");
                    continue;
                }
            }
        }
        var_dump($corporationID);
         */
}

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
			if (count($rows)>0) foreach ($rows as $row) {
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
