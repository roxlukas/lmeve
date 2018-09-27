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
    echo('WARNING: $ESI_BASEURL isn\'t set in config.php. Using default ESI API URL https://esi.evetech.net'  . "\r\n");
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

$ESI = new ESI(5);
/*
//get Lukas Rox from LMeve database, subsequent request should use cache
echo($ESI->Characters->getCharacterName(816121566) . "\r\n");
echo($ESI->Characters->getCharacterName(816121566) . "\r\n");
//get Rixx Javix from ESI (it does not exist in database). Subsequent request should use cache
echo($ESI->Characters->getCharacterName(245073304) . "\r\n");
echo($ESI->Characters->getCharacterName(245073304) . "\r\n");

//get market history for Cruor in The Forge
var_dump($ESI->Markets->getHistory(17926,10000002));

//get market orders for Cruor in The Forge
var_dump($ESI->Markets->getMarketOrders(17926,10000002,30000140));

//get market orders for Tritanium in The Forge
var_dump($ESI->Markets->getMarketOrders(34,10000002,30000140));
*/
//update Trit price
//$ESI->Markets->updateMinMax(34);
//test game calculated avg/adjusted prices
//$d = $ESI->Markets->getPrices();

//$o = $ESI->Markets->updateCorporationMarketOrders();

//$ESI->Contracts->updateCorporationContractItems(136961765);

//var_dump($ESI->CorporationInformation->getDivisions());

//$ESI->Wallet->updateCorpWalletBalance();

//var_dump($ESI->Wallet->getCorporationWalletJournal(1));

//var_dump($ESI->Universe->getNamesForIdsMap(array(95465499, 30000142)));

//$ESI->Wallet->updateRefTypes();

var_dump($ESI->Wallet->updateCorpWalletTransactions(4));

?>