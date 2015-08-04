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
		    <input type="hidden" name="id2" value="9">
		    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">
		    <input type="submit" value="&laquo; previous month">
			</form>
			</td><td>
			<form method="get" action="">
		    <input type="hidden" name="id" value="6">
		    <input type="hidden" name="id2" value="9">
		    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">
		    <input type="submit" value="next month &raquo;">
			</form>			
			</td></tr></table>
		    <?php /*
		    <a href="?id=<?php echo($MENUITEM); ?>&date=<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">&laquo; previous month</a> |  <a href="?id=<?php echo($MENUITEM); ?>&date=<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">next month &raquo;</a><br/>
		    */ ?>
		    
		    
		    
			<a href="#down">Scroll down</a>
		    </div>
		    
		    <?php
		    
		    $ONEPOINT=getConfigItem('iskPerPoint','15000000'); //loaded from db now! :-)
		    
		    $corps=db_asocquery("SELECT * FROM apicorps;");
		    foreach ($corps as $corp) { //begin corps loop
		    echo("<h1><img src=\"http://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
			    
			$sql="SELECT DISTINCT * FROM (
SELECT b.buy,s.sell,s.sell-b.buy AS total,s.corporationID,s.accountKey FROM 
(SELECT SUM(price*quantity) AS sell,accountKey,corporationID FROM `apiwallettransactions`
WHERE transactionType='sell'
AND corporationID=${corp['corporationID']}
AND transactionDateTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
GROUP BY corporationID,accountKey) AS s
LEFT JOIN
(SELECT SUM(price*quantity) AS buy,accountKey,corporationID FROM `apiwallettransactions`
WHERE transactionType='buy'
AND corporationID=${corp['corporationID']}
AND transactionDateTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
GROUP BY corporationID,accountKey) AS b
ON s.accountKey=b.accountKey
UNION ALL
SELECT b.buy,s.sell,s.sell-b.buy AS total,s.corporationID,s.accountKey FROM 
(SELECT SUM(price*quantity) AS sell,accountKey,corporationID FROM `apiwallettransactions`
WHERE transactionType='sell'
AND corporationID=${corp['corporationID']}
AND transactionDateTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
GROUP BY corporationID,accountKey) AS s
RIGHT JOIN
(SELECT SUM(price*quantity) AS buy,accountKey,corporationID FROM `apiwallettransactions`
WHERE transactionType='buy'
AND corporationID=${corp['corporationID']}
AND transactionDateTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
GROUP BY corporationID,accountKey) AS b
ON s.accountKey=b.accountKey) AS raw_summary";
			$wallet_summaries_raw=db_asocquery($sql);
			
			
			
			$sql="SELECT accountKey, balance FROM
apiaccountbalance
WHERE corporationID=${corp['corporationID']}";
			$balances=db_asocquery($sql);
			
			$wallet_summaries=array();
			//var_dump($wallet_summaries_raw);
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
		    
		    foreach($balances as $row) {
				$wallet_summaries[$row['accountKey']]['balance']=stripslashes($row['balance']);
		    }
			
			$sql="SELECT * FROM apiwalletdivisions WHERE corporationID=${corp['corporationID']};";
			
			$wallet_divisions=db_asocquery($sql);
			
                        global $LM_EVEDB;
                        
			//Count current month wages value
			$sql="SELECT SUM(wage) AS wages FROM (SELECT *,ROUND((points*$ONEPOINT),2) as wage FROM (
SELECT `characterID`,`name`,`activityName`,SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600)/hrsPerPoint AS points
FROM `apiindustryjobs` aij
JOIN `$LM_EVEDB`.`ramActivities` rac
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
			$sql="SELECT SUM(awj.amount) AS amount,awj.refTypeID,art.refTypeName FROM
apiwalletjournal awj
JOIN apireftypes art
ON awj.refTypeID=art.refTypeID
WHERE awj.date BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
AND awj.corporationID=${corp['corporationID']}
AND awj.refTypeID NOT IN (2, 37, 42)
GROUP BY awj.refTypeID,art.refTypeName
ORDER BY art.refTypeName;";
			$journal=db_asocquery($sql);
			
		
			$totals['buy']=0.0;
			$totals['sell']=0.0;
			$totals['total']=0.0;
			$totals['balance']=0.0;
			
			?>
		    
		    <table cellspacing="2" cellpadding="0">
		    <tr><td rowspan="2" class="tab-header" style="text-align: center;">
			    <b>Description</b>
		    </td><td rowspan="2" class="tab-header" style="text-align: center;">
				<b>accountKey</b>
		    </td><td class="tab-header" style="text-align: center;">
				<b>ISK Balance</b>
		    </td><td colspan="3" class="tab-header" style="text-align: center;">
				<b>ISK Flows</b>
		    </td>
		    </tr>
		    <tr><td class="tab-header" style="text-align: center;">
				<b>Balance</b>
		    </td><td class="tab-header" style="text-align: center;">
				<b>Buy</b>
		    </td><td class="tab-header" style="text-align: center;">
				<b>Sell</b>
		    </td><td class="tab-header" style="text-align: center;">
				<b>Net</b>
		    </td>
		    </tr>
		   <?php
		foreach($wallet_divisions as $row) {
		    echo('<tr><td class="tab">');
		    echo($row['description']);
		    echo('</td><td class="tab">');
			echo($row['accountKey']);
		    echo('</td><td class="tab" style="text-align: right;">');
		    echo(number_format($wallet_summaries[$row['accountKey']]['balance'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
		    $totals['balance']+=$wallet_summaries[$row['accountKey']]['balance'];
		    echo('</td><td class="tab" style="text-align: right;">');
		    echo(number_format($wallet_summaries[$row['accountKey']]['buy'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
		    $totals['buy']+=$wallet_summaries[$row['accountKey']]['buy'];
		    echo('</td><td class="tab" style="text-align: right;">');
		    echo(number_format($wallet_summaries[$row['accountKey']]['sell'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
		    $totals['sell']+=$wallet_summaries[$row['accountKey']]['sell'];
		    echo('</td><td class="tab" style="text-align: right;">');
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
		    <tr><td class="tab" style="text-align: left;">
			    <b>Wages</b>
		    </td><td class="tab" style="text-align: center;">
				<b></b>
		    </td><td class="tab" style="text-align: center;">
				<b></b>
		    </td><td class="tab" style="text-align: center;">
				<b></b>
		    </td><td class="tab" style="text-align: center;">
				<b></b>
		    </td><td class="tab" style="text-align: center;">
				<b><?php echo(number_format(0-$wages[0]['wages'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); $netprofit=$totals['total']-$wages[0]['wages']?></b>
		    </td>
		    </tr>
		    
		    <?php
		foreach($journal as $row) {
		    echo('<tr><td class="tab" style="text-align: left;">');
		    echo($row['refTypeName']);
		    echo('</td><td class="tab">');
		    echo($row['refTypeID']);
			echo('</td><td class="tab" style="text-align: right;">');
		    echo('</td><td class="tab" style="text-align: right;">');
		    echo('</td><td class="tab" style="text-align: right;">');
		    echo('</td><td class="tab" style="text-align: right;">');
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
		
