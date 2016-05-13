<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewMarket,ViewBuyCalc,ViewBuyOrders,ViewSellOrders,EditBuyOrders,EditSellOrders")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=3; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Market'; //Panel name (optional)
//standard header ends here

include("market.php");
global $LM_EVEDB;

?>

            <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>

<?php
if (checkrights("Administrator,ViewBuyOrders")) { 
	echo('<h3>Buyback Orders</h3>');
	echo('<em><img src="'.getUrl().'ccp_icons/38_16_208.png" alt="(i)"/> Buyback orders are buyback contracts from corp members to the corporation.</em><br />');
	
	$buybacklist=getBuybackOrders("WHERE TRUE ORDER BY timestmp DESC LIMIT 10");
	showBuyback($buybacklist);
}

if (checkrights("Administrator,ViewMarket")) { 
	echo('<h3>Market Orders</h3>');
	echo('<em><img src="'.getUrl().'ccp_icons/38_16_208.png" alt="(i)"/> Market Orders show current in game market order status.</em><br />');
        $corps=db_asocquery("SELECT * FROM apicorps;");
	foreach ($corps as $corp) { //begin corps loop
            echo("<h3><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_32.png\" style=\"vertical-align: middle;\" /> ${corp['corporationName']}</h3>");
            $marketdata=getMarketOrders("WHERE amo.bid=0 AND amo.orderState=0 AND amo.volRemaining>0 AND amo.corporationID=${corp['corporationID']}");
            showMarketOrders($marketdata,'Sell orders');
            $buyorddata=getMarketOrders("WHERE amo.bid=1 AND amo.orderState=0 AND amo.volRemaining>0 AND amo.corporationID=${corp['corporationID']}");
            showMarketOrders($buyorddata,'Buy orders');
        }
}
if (checkrights("Administrator,ViewContracts")) { 
	echo('<h3>Corp Contracts</h3>');
	echo('<em>Corp Contracts show current in game contract status.</em><br />');
	echo('Coming sooner than soon<sup>tm</sup>.');
}
if (checkrights("Administrator,ViewSellOrders")) { 
	echo('<h3>Production Orders</h3>');
	echo('<em>Production Orders are contracts from the corporation to corp members and other entities.</em><br />');
	echo('Coming sooner than soon<sup>tm</sup>.');
}
?>