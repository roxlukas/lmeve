<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewBuyCalc")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=3; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Buy Calculator'; //Panel name (optional)
//standard header ends here

include_once("market.php");
include_once("inventory.php");
include_once("configuration.php");
global $LM_EVEDB;

if (!token_verify()) die("Invalid or expired token.");

$evepraisal = preg_split('/[\r\n]+/', secureGETstr('evepraisal'));

$items = evepraisal_parser($evepraisal);

echo('<h3>New contract</h3><p>Below is what LMeve understood from your paste:</p>');

//showItems($items['items']);

$stock = getBuyingStock();

showQuote($stock, $items['items']);

