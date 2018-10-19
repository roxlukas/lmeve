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
    echo('WARNING: $ESI_BASEURL isn\'t set in config.php. Using default ESI API URL https://esi.evetech.net');
    $ESI_BASEURL="https://esi.evetech.net"; 
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
        
}

try {
    inform("Main","Updating public routes...");
    if (!isset($ESI)) $ESI = new ESI(null);
    inform("Main","ESI->updatePublic()...");
    $ESI->updatePublic();
    //update game server status
    $servers=array('tranquility','singularity');
    foreach($servers as $s) {
        $ESI->setDatasource($s);
        $ESI->Status->updateServerStatus();
    }
} catch (Exception $e) {
    warning("Main","Exception occured in ESI(tokenID=$tokenID): " . $e->getMessage());
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
