<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewProfitCalc")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=10; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Profit Chart'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB,$EC_PRICE_TO_USE_FOR_SELL;

include_once('materials.php'); //material related subroutines

$marketGroupID=secureGETnum('marketGroupID');

if (!empty($marketGroupID)) {
	$wheremarket="=$marketGroupID";
} else {
	$wheremarket="IS NULL";
}

//BEGIN Clientside sorting:
?>
  <script type="text/javascript" src="jquery-tablesorter/jquery.tablesorter.min.js"></script>
  <link rel="stylesheet" type="text/css" href="jquery-tablesorter/blue/style.css">
  <script type="text/javascript">
    $(document).ready(function() { 
        $("#items").tablesorter({ 
            headers: { 0: { sorter: false } } 
        }); 
    });
  </script>
<?php
//END Clientside sorting
?>
	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
  
  
    <div id="pageContents"><i>Loading...</i></div>
    <script type="text/javascript">
        ajax_get('ajax.php?act=CACHE&page=a9','pageContents');
    </script>
<?php /*
   $items=db_asocquery("SELECT itp.`typeID`, itp.`typeName`, app.${EC_PRICE_TO_USE_FOR_SELL['price']}, app.`volume`
		FROM `$LM_EVEDB`.`invTypes` itp
                JOIN `$LM_EVEDB`.`yamlBlueprintProducts` ybp
                ON itp.`typeID`=ybp.`productTypeID`
                JOIN `cfgmarket` cfm
                ON itp.`typeID`=cfm.`typeID`
                JOIN `apiprices` app
                ON itp.`typeID`=app.`typeID`
		WHERE itp.`published` = 1
                AND ybp.`activityID` = 1
                AND app.`type` = '${EC_PRICE_TO_USE_FOR_SELL['type']}'
                AND app.${EC_PRICE_TO_USE_FOR_SELL['price']} > 0
                ORDER BY itp.`typeName`
                ;");
	
	function hrefedit_item($nr) {
		echo("<a href=\"index.php?id=10&id2=1&nr=$nr\">");
	}
	?>
			<table id="items" class="lmframework tablesorter" cellspacing="2" cellpadding="0" style="min-width:700px; width: 90%;">
			<thead><tr><th>
				<b>Icon</b>
			</th><th>
				<b>Name</b>
			</th><th>
				<b>Manufacturing cost</b>
			</th><th>
				<b>Market price</b>
			</th><th>
				<b>Market volume</b>
			</th><th>
				<b>Unit Profit</b>
			</th><th>
				<b>Profit [%]</b>
			</th><th>
				<b>Market Profitability (B isk)</b>
			</th>
			</tr>
	</thead> <?php
            
            if (sizeof($items)>0) {
                foreach($items as $row) {
                        //$priceData=db_asocquery("SELECT * FROM `apiprices` WHERE `typeID`=${row['typeID']} AND `type`='sell';");
                        $cost=calcTotalCosts($row['typeID']);
                        $unitprofit=$row[$EC_PRICE_TO_USE_FOR_SELL['price']]-$cost;
                        $profit=100*($unitprofit)/$cost;
                        echo('<tr><td style="padding: 0px; width: 32px;">');
                                hrefedit_item($row['typeID']);
                                echo("<img src=\"ccp_img/${row['typeID']}_32.png\" title=\"${row['typeName']}\" />");
                                echo('</a>');
                        echo('</td><td>');
                                hrefedit_item($row['typeID']);
                                echo($row['typeName']);
                                echo('</a>');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($cost, 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($row[$EC_PRICE_TO_USE_FOR_SELL['price']], 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($row['volume'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($unitprofit, 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($profit, 1, $DECIMAL_SEP, $THOUSAND_SEP).' %');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($unitprofit*$row['volume']/1000000000, 1, $DECIMAL_SEP, $THOUSAND_SEP));
                        echo('</td>');
                        echo('</tr>');
                }
        }
	
	
	echo('</table>');
    */    
?>