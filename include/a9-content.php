<?php
function hrefedit_item($nr) {
		echo("<a href=\"index.php?id=10&id2=1&nr=$nr\">");
	}
        
function cachedContent() {
    set_time_limit(180); //can work up to 3 minutes
    ob_start();
    /**********************************/
        global $LM_EVEDB, $EC_PRICE_TO_USE_FOR_SELL;
        echo("<em>Static Data schema: $LM_EVEDB</em><br />");

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
        </thead>
        <?php

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
    /**********************************/ 
    $ret=ob_get_contents();
    ob_end_clean();
    return $ret;
}

?>
