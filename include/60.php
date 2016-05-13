<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewWallet")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=6; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Wallet'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB,$MOBILE;

$date=secureGETnum("date");

if (strlen($date)==6) {
	$year=substr($date,0,4);
	$month=substr($date,4,2);
} else {
	$year=date("Y");
	$month=date("m");
}

		function hrefedit($nr) {
		    echo("<a href=\"index.php?id=$MENUITEM&id2=1&nr=$nr\">");
		}


		?>
		<a name="top"></a>
                <script type="text/javascript" src="chart.js/Chart.min.js"></script>
                <script type="text/javascript">
                           options = {
                                    //Boolean - If we show the scale above the chart data
                                    scaleOverlay : false,
                                    //Boolean - If we want to override with a hard coded scale
                                    scaleOverride : false,
                                    //** Required if scaleOverride is true **
                                    //Number - The number of steps in a hard coded scale
                                    scaleSteps : null,
                                    //Number - The value jump in the hard coded scale
                                    scaleStepWidth : null,
                                    //Number - The scale starting value
                                    scaleStartValue : null,
                                    //String - Colour of the scale line
                                    scaleLineColor : "rgba(255,255,255,.1)",
                                    //Number - Pixel width of the scale line
                                    scaleLineWidth : 1,
                                    //Boolean - Whether to show labels on the scale
                                    scaleShowLabels : true,
                                    //Interpolated JS string - can access value
                                    scaleLabel : "<%=value%>M",
                                    //String - Scale label font declaration for the scale label
                                    scaleFontFamily : "'Arial'",
                                    //Number - Scale label font size in pixels
                                    scaleFontSize : 12,
                                    //String - Scale label font weight style
                                    scaleFontStyle : "normal",
                                    //String - Scale label font colour
                                    scaleFontColor : "#aaa",
                                    ///Boolean - Whether grid lines are shown across the chart
                                    scaleShowGridLines : true,
                                    //String - Colour of the grid lines
                                    scaleGridLineColor : "rgba(255,255,255,.1)",
                                    //Number - Width of the grid lines
                                    scaleGridLineWidth : 1,
                                    //Boolean - If there is a stroke on each bar
                                    barShowStroke : true,
                                    //Number - Pixel width of the bar stroke
                                    barStrokeWidth : 2,
                                    //Number - Spacing between each of the X value sets
                                    barValueSpacing : 5,
                                    //Number - Spacing between data sets within X values
                                    barDatasetSpacing : 1,
                                    //Boolean - Whether to animate the chart
                                    animation : true,
                                    //Number - Number of animation steps
                                    animationSteps : 60,
                                    //String - Animation easing effect
                                    animationEasing : "easeOutQuart",
                                    //Function - Fires when the animation is complete
                                    onAnimationComplete : null
                            }
                    </script>
		    <div class="tytul">
			Wallet for <?php echo("$year-$month"); ?><br>
		    </div>
		    <div class="tekst">

		    <?php //Monthly navigation
		    switch ($month) {
				case 1:
					$NEXTMONTH=2;
					$NEXTYEAR=$year;
					$PREVMONTH=12;
					$PREVYEAR=$year-1;
				break;
				case 12:
					$NEXTMONTH=1;
					$NEXTYEAR=$year+1;
					$PREVMONTH=11;
					$PREVYEAR=$year;
				break;
				default:
					$NEXTMONTH=$month+1;
					$NEXTYEAR=$year;
					$PREVMONTH=$month-1;
					$PREVYEAR=$year;
			}
		    ?>
                        
		    <table border="0" cellspacing="3" cellpadding="0">
		    <tr><td>
			<form method="get" action="">
		    <input type="hidden" name="id" value="6">
		    <input type="hidden" name="id2" value="0">
		    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">
		    <input type="submit" value="&laquo; previous month">
			</form>
			</td><td>
			<form method="get" action="">
		    <input type="hidden" name="id" value="6">
		    <input type="hidden" name="id2" value="0">
		    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">
		    <input type="submit" value="next month &raquo;">
			</form>
			</td></tr></table>
		    <?php /*
		    <a href="?id=<?php echo($MENUITEM); ?>&date=<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">&laquo; previous month</a> |  <a href="?id=<?php echo($MENUITEM); ?>&date=<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">next month &raquo;</a><br/>
		    */ ?>



			<a href="#down">Scroll down</a>
		    </div>
                        <em><img src="<?=getUrl()?>ccp_icons/38_16_208.png" alt="(i)"/> LMeve attempts to predict current month running cost, so to avoid counting previous month's wages and internal money transfers, <strong>refTypeID 37 (Corporation Account Withdrawal) is filtered out</strong>.<br/>
                        Instead, current month wages estimate is subtracted from the wallet totals. <strong>This behavior will be configurable in a future release of LMeve.</strong></em><br />
		    <?php

		    $ONEPOINT=getConfigItem('iskPerPoint','15000000'); //loaded from db now! :-)



		    $corps=db_asocquery("SELECT * FROM apicorps;");
		    foreach ($corps as $corp) { //begin corps loop
                        echo("<h1><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
                        $width=730;
			$days="";
                        $incomes="";
                        $outcomes="";

//GRAPHING
                    //getting data
                        $dayss=date("t",mktime(0,0,$month,1,$year));
                        for($i=1; $i<=$dayss; $i++) {
                            $daystab[$i]['day']="$i";
                            $daystab[$i]['income']=0;
                            $daystab[$i]['outcome']=0;
                        }

                        //refTypeID=37 is "Corporation Account Withdrawal" - previous month wages
			//refTypeID=42 is "Market Escrow"
			//refTypeID=2 is "Market Transaction"
			//refTypeID=71 is "Contract Price" - corp contracts sold (positive)
			//refTypeID=79 is "Contract Price Payment (corp)" - ore buyback from players (negative)
                        $sqlinc="SELECT SUM(awj.amount)/1000000 AS income,date_format(awj.date, '%e') AS day FROM
                        apiwalletjournal awj
                        JOIN apireftypes art
                        ON awj.refTypeID=art.refTypeID
                        WHERE awj.date BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
                        AND awj.corporationID=${corp['corporationID']}
                        AND awj.refTypeID <> 37
                        AND awj.amount > 0
                        GROUP BY date_format(awj.date, '%e')
                        ORDER BY date_format(awj.date, '%e');";
                        $sqloutc="SELECT -SUM(awj.amount)/1000000 AS outcome,date_format(awj.date, '%e') AS day FROM
                        apiwalletjournal awj
                        JOIN apireftypes art
                        ON awj.refTypeID=art.refTypeID
                        WHERE awj.date BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
                        AND awj.corporationID=${corp['corporationID']}
                        AND awj.refTypeID <> 37
                        AND awj.amount < 0
                        GROUP BY date_format(awj.date, '%e')
                        ORDER BY date_format(awj.date, '%e');";
                        //echo($sqlinc);
                        //echo($sqloutc);

                        $walletGraphIncomes=db_asocquery($sqlinc);
                        $walletGraphOutcomes=db_asocquery($sqloutc);

if (count($walletGraphIncomes)>0 || count($walletGraphOutcomes)>0) {
                    //reformatting data
                        foreach ($walletGraphIncomes as $row) {
                            $daystab[$row['day']]['income']=$row['income'];
                        }
                        foreach ($walletGraphOutcomes as $row) {
                            $daystab[$row['day']]['outcome']=$row['outcome'];
                        }
                    //ready to display
                        foreach($daystab as $row) {
                            $days.='"'.$row['day'].'",';
                            $incomes.=$row['income'].',';
                            $outcomes.=$row['outcome'].',';
                        }
                    //cut trailing commas
                        $days=rtrim($days,',');
                        $incomes=rtrim($incomes,',');
                        $outcomes=rtrim($outcomes,',');
                    //display
                        ?>
            <h2>Wallet Operations</h2>

                        <canvas id="wallet_<?php echo($corp['corporationID']); ?>" width="<?php echo($width); ?>" height="300"></canvas>
                        <script type="text/javascript">
                            var data_<?php echo($corp['corporationID']); ?> = {
                                labels : [ <?php echo($days); ?> ],
                                datasets : [
                                       {
                                           fillColor : "rgba(205,0,0,0.5)",
                                           strokeColor : "rgba(205,107,101,1.0)",
                                           <?php //pointColor : "rgba(205,107,101,1.0)",
                                           //pointStrokeColor : "#fff", ?>
                                           data : [ <?php echo($outcomes); ?> ]
                                       },
                                       {
                                           fillColor : "rgba(151,187,205,0.5)",
                                           strokeColor : "rgba(151,187,205,1.0)",
                                           <?php //pointColor : "rgba(151,187,205,1.0)",
                                           //pointStrokeColor : "#fff", ?>
                                           data : [ <?php echo($incomes); ?> ]
                                       }
                                ]
                            }

                            var ctx_<?php echo($corp['corporationID']); ?> = document.getElementById("wallet_<?php echo($corp['corporationID']); ?>").getContext("2d");
                            <?php if ($MOBILE) echo('ctx_'.$corp['corporationID'].'.canvas.width  = window.innerWidth;'); ?>
                            var walletChart_<?php echo($corp['corporationID']); ?> = new Chart(ctx_<?php echo($corp['corporationID']); ?>).Bar(data_<?php echo($corp['corporationID']); ?>,options);

                        </script>
                        <?php
}
//TABLES
			$sql="
SELECT COALESCE(b.buy,0) AS buy, COALESCE(s.sell,0) AS sell, (COALESCE(s.sell,0)-COALESCE(b.buy,0)) AS total, c.corporationID AS corporationID, a.accountKey AS accountKey
  FROM apiwalletdivisions AS a
  LEFT JOIN apicorps AS c ON a.corporationID = c.corporationID
  LEFT JOIN ( SELECT SUM(price*quantity) AS buy,accountKey,corporationID
                FROM `apiwallettransactions`
               WHERE transactionType='buy'
                 AND corporationID=${corp['corporationID']}
                 AND transactionDateTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
               GROUP BY corporationID,accountKey
            ) AS b ON (a.accountKey = b.accountKey AND c.corporationID = b.corporationID)
  LEFT JOIN ( SELECT SUM(price*quantity) AS sell,accountKey,corporationID
                FROM `apiwallettransactions`
               WHERE transactionType='sell'
                 AND corporationID=${corp['corporationID']}
                 AND transactionDateTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
               GROUP BY corporationID,accountKey
            ) AS s ON (a.accountKey = s.accountKey AND c.corporationID = s.corporationID)
 WHERE c.corporationID=${corp['corporationID']}";
			$wallet_summaries_raw=db_asocquery($sql);

			$sql="SELECT SUM(awj.amount) AS amount,awj.refTypeID,awj.accountKey FROM
apiwalletjournal awj
JOIN apireftypes art
ON awj.refTypeID=art.refTypeID
WHERE awj.date BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
AND awj.corporationID = ${corp['corporationID']}
AND awj.refTypeID IN (71, 79)
GROUP BY awj.refTypeID,awj.accountKey;";
			$contracts_raw=db_asocquery($sql);

			$sql="SELECT accountKey, balance FROM
apiaccountbalance
WHERE corporationID=${corp['corporationID']}";
			$balances=db_asocquery($sql);


			$wallet_summaries=array();

			//refactor wallet transactions SQL result into display tables
		    foreach($wallet_summaries_raw as $row) {
				//echo("DEBUG: ${row['accountKey']} | ${row['buy']} | ${row['sell']} | ${row['total']} <br>");
				if (!empty($row['buy'])) {
					$wallet_summaries[$row['accountKey']]['buy']=stripslashes($row['buy']);
				} else {
					$wallet_summaries[$row['accountKey']]['buy']=0;
				}
				if (!empty($row['sell'])) {
					$wallet_summaries[$row['accountKey']]['sell']=stripslashes($row['sell']);
				} else {
					$wallet_summaries[$row['accountKey']]['sell']=0;
				}
				$wallet_summaries[$row['accountKey']]['total']=$wallet_summaries[$row['accountKey']]['sell']-$wallet_summaries[$row['accountKey']]['buy'];
		    }

		    //refactor contracts SQL result into display tables
		    foreach($contracts_raw as $row) {
				if ($row['refTypeID']==71) {
					$wallet_summaries[$row['accountKey']]['cntrct_sell']=stripslashes($row['amount']);
					$wallet_summaries[$row['accountKey']]['total']+=stripslashes($row['amount']);
				}
				if ($row['refTypeID']==79) {
					$wallet_summaries[$row['accountKey']]['cntrct_buy']=-1*stripslashes($row['amount']);
					$wallet_summaries[$row['accountKey']]['total']+=stripslashes($row['amount']);
				}
		    }

		    //and add current balance
		    foreach($balances as $row) {
				$wallet_summaries[$row['accountKey']]['balance']=stripslashes($row['balance']);
		    }

			$sql="SELECT * FROM apiwalletdivisions WHERE corporationID=${corp['corporationID']};";

			$wallet_divisions=db_asocquery($sql);

			//Count current month wages value
			$sql="SELECT SUM(wage) AS wages FROM (SELECT *,ROUND((points*$ONEPOINT),2) as wage FROM (
SELECT `characterID`,`name`,`activityName`,SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600)/hrsPerPoint AS points
FROM `apiindustryjobs` aij
JOIN $LM_EVEDB.`ramActivities` rac
ON aij.activityID=rac.activityID
JOIN cfgpoints cpt
ON aij.activityID=cpt.activityID
JOIN apicorpmembers acm
ON aij.installerID=acm.characterID
WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
AND aij.corporationID=${corp['corporationID']}
GROUP BY `characterID`,`name`,`activityName`
ORDER BY `name`,`activityName`) AS w) AS wages;";
			$wages=db_asocquery($sql);

			//refTypeID=37 is "Corporation Account Withdrawal" - previous month wages
			//refTypeID=42 is "Market Escrow"
			//refTypeID=2 is "Market Transaction"
			//changes:
			//refTypeID=71 is "Contract Price" - corp contracts sold (positive)
			//refTypeID=79 is "Contract Price Payment (corp)" - ore buyback from players (negative)
			$sql="SELECT SUM(awj.amount) AS amount,awj.refTypeID,art.refTypeName FROM
apiwalletjournal awj
JOIN apireftypes art
ON awj.refTypeID=art.refTypeID
WHERE awj.date BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
AND awj.corporationID=${corp['corporationID']}
AND awj.refTypeID NOT IN (2, 37, 42, 71, 79)
GROUP BY awj.refTypeID,art.refTypeName
ORDER BY art.refTypeName;";
			$journal=db_asocquery($sql);


			$totals['buy']=0.0;
			$totals['sell']=0.0;
			$totals['total']=0.0;
			$totals['balance']=0.0;

			?>
<h2>Wallet Breakdown</h2>
		    <table width="<?php echo($width); ?>" class="lmframework">
		    <tr><th style="text-align: center;">
			    Description
		    </th><th style="text-align: center;">
				accountKey
		    </th><th style="text-align: center;" title="Always shows current ISK balance.">
				Current ISK Balance
		    </th><th colspan="3" style="text-align: center;"  title="Shows input and output ISK flows for the specified month.">
				ISK Flows
		    </th>
		    </tr>
		    <tr><th colspan="3" style="text-align: center;">
				Balance
		    </th><th style="text-align: center;">
				Buy
		    </th><th style="text-align: center;">
				Sell
		    </th><th style="text-align: center;">
				Net
		    </th>
		    </tr>
		   <?php
		foreach($wallet_divisions as $row) {
		    echo('<tr><td>');
		    echo($row['description']);
		    echo('</td><td>');
			echo($row['accountKey']);
		    echo('</td><td style="text-align: right;">');
		    echo(number_format($wallet_summaries[$row['accountKey']]['balance'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
		    $totals['balance']+=$wallet_summaries[$row['accountKey']]['balance'];
		    echo('</td><td style="text-align: right; line-height: 16px;">');
		    echo('<img src="'.getUrl().'ccp_icons/6_64_3.png" title="Market" style="float: left; width: 16px; height: 16px;"> ');
		    echo(number_format($wallet_summaries[$row['accountKey']]['buy'], 2, $DECIMAL_SEP, $THOUSAND_SEP).'<br/>');
		    echo('<img src="'.getUrl().'ccp_icons/64_64_10.png" title="Contracts" style="float: left; width: 16px; height: 16px;"> ');
		    echo(number_format($wallet_summaries[$row['accountKey']]['cntrct_buy'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
		    $totals['buy']+=$wallet_summaries[$row['accountKey']]['buy']+$wallet_summaries[$row['accountKey']]['cntrct_buy'];
		    echo('</td><td style="text-align: right; line-height: 16px;">');
		    echo('<img src="'.getUrl().'ccp_icons/6_64_3.png" title="Market" style="float: left; width: 16px; height: 16px;"> ');
		    echo(number_format($wallet_summaries[$row['accountKey']]['sell'], 2, $DECIMAL_SEP, $THOUSAND_SEP).'<br/>');
		    echo('<img src="'.getUrl().'ccp_icons/64_64_10.png" title="Contracts" style="float: left; width: 16px; height: 16px;"> ');
		    echo(number_format($wallet_summaries[$row['accountKey']]['cntrct_sell'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
		    $totals['sell']+=$wallet_summaries[$row['accountKey']]['sell']+$wallet_summaries[$row['accountKey']]['cntrct_sell'];
		    echo('</td><td style="text-align: right;">');
		    echo(number_format($wallet_summaries[$row['accountKey']]['total'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
		    $totals['total']+=$wallet_summaries[$row['accountKey']]['total'];
		    echo('</td>');
		    echo('</tr>');
		}
		?>
			<tr><td class="tab-header" style="text-align: center;">
			    <b>Totals</b>
		    </td><td class="tab-header" style="text-align: center;">
				<b>RefTypeID</b>
		    </td><td class="tab-header" style="text-align: center;">
				<b><?php echo(number_format($totals['balance'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
		    </td><td class="tab-header" style="text-align: center;">
				<b><?php echo(number_format($totals['buy'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
		    </td><td class="tab-header" style="text-align: center;">
				<b><?php echo(number_format($totals['sell'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
		    </td><td class="tab-header" style="text-align: center;">
				<b><?php echo(number_format($totals['total'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
		    </td>
		    </tr>
		    <tr><td style="text-align: left;" title="Shows corp members Wages in selected month.">
			    <b>Wages</b>
		    </td><td style="text-align: center;">
				<b></b>
		    </td><td style="text-align: center;">
				<b></b>
		    </td><td style="text-align: center;">
				<b></b>
		    </td><td style="text-align: center;">
				<b></b>
		    </td><td style="text-align: right;" title="Shows corp members Wages in selected month.">
				<b><?php echo(number_format(0-$wages[0]['wages'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); $netprofit=$totals['total']-$wages[0]['wages']?></b>
		    </td>
		    </tr>

		    <?php
		foreach($journal as $row) {
		    echo('<tr><td style="text-align: left;">');
		    echo($row['refTypeName']);
		    echo('</td><td>');
		    echo($row['refTypeID']);
			echo('</td><td style="text-align: right;">');
		    echo('</td><td style="text-align: right;">');
		    echo('</td><td style="text-align: right;">');
		    echo('</td><td style="text-align: right;">');
		    echo(number_format($row['amount'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
		    $netprofit+=$row['amount'];
		    echo('</td>');
		    echo('</tr>');
		}
		?>

		    <tr><td class="tab-header" style="text-align: center;">
			    <b>Net profit</b>
		    </td><td class="tab-header" style="text-align: center;">
				<b></b>
		    </td><td class="tab-header" style="text-align: center;">
				<b></b>
		    </td><td class="tab-header" style="text-align: center;">
				<b></b>
		    </td><td class="tab-header" style="text-align: center;">
				<b></b>
		    </td><td class="tab-header" style="text-align: center;">
				<b><?php echo(number_format($netprofit, 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
		    </td>
		    </tr>

		</table>

		 <?php

	}//end corps loop
		?>

		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>

		    </div><br>

