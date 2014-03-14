<? 
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewTimesheet")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Timesheet'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

$rights_viewallchars=checkrights("Administrator,ViewAllCharacters");
$rights_edithours=checkrights("Administrator,EditHoursPerPoint");

$date=secureGETnum("date");

if (strlen($date)==6) {
	$year=substr($date,0,4);
	$month=substr($date,4,2);
} else {
	$year=date("Y");
	$month=date("m");	
}

function pointshrefedit($nr) {
    echo("<a href=\"index.php?id=5&id2=11&nr=$nr\" title=\"Click to edit this activity\">");
}

		function hrefedit($nr) {
			global $MENUITEM;
		    echo("<a href=\"index.php?id=9&id2=6&nr=$nr\" title=\"Click to open character information\">");
		}
		//$idu=range(0,$ileuser-1);
	    $orderhow="ASC";
	    //$idz=range(0,$ilezlec-1);
	    //domyslnie ascending
	    if ($ord=='desc') {
			$orderhow="DESC";
	    }
	    
	    switch ($sort) {
	    case 1:
			$order="name " . $orderhow;
		break;
	    case 2:
	    	$order="ipaddr " . $orderhow;
		break;
	    }
	    
		?>
		<a name="top"></a>
		    <div class="tytul">
			Timesheet for <?php echo("$year-$month"); ?><br>
		    </div>
		
		    <div class="tekst">
		    
		    <?php
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
		    <input type="hidden" name="id" value="0">
		    <input type="hidden" name="id2" value="0">
		    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">
		    <input type="submit" value="&laquo; previous month">
			</form>
			</td><td>
			<form method="get" action="">
		    <input type="hidden" name="id" value="0">
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
		    
		    <?php
		    $points=db_asocquery("SELECT rac.`activityName`,cpt.* FROM $LM_EVEDB.`ramactivities` rac JOIN `cfgpoints` cpt ON rac.`activityID`=cpt.`activityID` ORDER BY `activityName`;");
		    $ONEPOINT=15000000; //it should be loaded from DB, static for now
		    		    
		    
		    		    
		    $corps=db_asocquery("SELECT * FROM apicorps;");
		    foreach ($corps as $corp) { //begin corps loop
				echo("<h1><img src=\"https://image.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
				
				$stats=db_asocquery("SELECT `activityName`, COUNT(*) AS jobs, SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600) AS hours
	FROM `apiindustryjobs` aij
	JOIN $LM_EVEDB.`ramactivities` rac
	ON aij.activityID=rac.activityID
	WHERE date_format(beginProductionTime, '%Y%m') = '${year}${month}'
	AND aij.corporationID=${corp['corporationID']}
	GROUP BY `activityName`
	ORDER BY `activityName`;");
				
				echo('<table cellspacing="2" cellpadding="0" width="100%">');
				echo('<tr><td width="40%" style="vertical-align: top;">');
				
					echo("<h2>Points");
                                        if (checkrights("Administrator,EditHoursPerPoint")) { ?>
                                            <input type="button" value="Edit hours-per-point" onclick="location.href='?id=5&id2=10';">
                                        <?php }
					echo('</h2><table cellspacing="2" cellpadding="0">');
					echo('<tr><td class="tab-header">');
					echo('Activity');
					echo('</td><td class="tab-header">');		
					echo('Hours');
					echo('</td></tr>');
					foreach($points as $point) {
						echo('<tr><td class="tab">');
                                                if ($rights_edithours) pointshrefedit($point['activityID']);
						echo($point['activityName']);
                                                if ($rights_edithours) echo("</a>");
						echo('</td><td class="tab">');
                                                if ($rights_edithours) pointshrefedit($point['activityID']);
						echo($point['hrsPerPoint']);
                                                if ($rights_edithours) echo("</a>");
						echo('</td></tr>');
					}
					echo('</table>');
                                        
					echo("<strong>1 point = ".number_format($ONEPOINT, 2, $DECIMAL_SEP, $THOUSAND_SEP)." ISK</strong><br/>");
					
					echo('</td><td width="60%" style="vertical-align: top;">');
					
					$sumstat=0.0;
					$sumjobs=0;
					echo("<h2>Statistics</h2>");
					echo('<table cellspacing="2" cellpadding="0">');
					echo('<tr><td class="tab-header">');
					echo('Activity');
					echo('</td><td class="tab-header">');		
					echo('Jobs');
					echo('</td><td class="tab-header">');		
					echo('Hours');
					echo('</td></tr>');
					foreach($stats as $stat) {
						echo('<tr><td class="tab">');
						echo($stat['activityName']);
						echo('</td><td class="tab" style="text-align: right;">');
						echo(number_format($stat['jobs'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
						$sumjobs+=$stat['jobs'];
						echo('</td><td class="tab" style="text-align: right;">');
						echo(number_format($stat['hours'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
						$sumstat+=$stat['hours'];
						echo('</td></tr>');
					}
					echo('<tr><td class="tab">');
					echo('<strong>TOTAL:</strong>');
					echo('</td><td class="tab" style="text-align: right;">');
					echo('<strong>');
					echo(number_format($sumjobs, 0, $DECIMAL_SEP, $THOUSAND_SEP));
					echo('</strong>');
					echo('</td><td class="tab" style="text-align: right;"><strong>');		
					echo(number_format($sumstat, 0, $DECIMAL_SEP, $THOUSAND_SEP));
					echo('</strong></td></tr>');
					echo('</table>');
				
				echo('</td></tr></table>');
				
				
			
				$sql="SELECT *,ROUND((points*$ONEPOINT),2) as wage FROM (
	SELECT `characterID`,`name`,`activityName`,SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600)/hrsPerPoint AS points
	FROM `apiindustryjobs` aij
	JOIN $LM_EVEDB.`ramactivities` rac
	ON aij.activityID=rac.activityID
	JOIN cfgpoints cpt
	ON aij.activityID=cpt.activityID
	JOIN apicorpmembers acm
	ON aij.installerID=acm.characterID
	WHERE date_format(beginProductionTime, '%Y%m') = '${year}${month}'
	AND aij.corporationID=${corp['corporationID']}
	GROUP BY `characterID`,`name`,`activityName`
	ORDER BY `name`,`activityName`) AS wages;";
	
	/*$sql2="SELECT *,ROUND((points*$ONEPOINT),2) as wage FROM (
	SELECT installerID AS characterID,'Unknown' AS name,`activityName`,SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600)/hrsPerPoint AS points
	FROM `apiindustryjobs` aij
	JOIN `ramactivities` rac
	ON aij.activityID=rac.activityID
	JOIN cfgpoints cpt
	ON aij.activityID=cpt.activityID
	WHERE date_format(beginProductionTime, '%Y%m') = '${year}${month}'
	AND aij.corporationID=${corp['corporationID']}
	AND installerID NOT IN (SELECT characterID FROM apicorpmembers WHERE corporationID=${corp['corporationID']})
	GROUP BY `installerID`,`activityName`
	ORDER BY `installerID`,`activityName`) AS wages;";
	
	TOTALS:
	
	SELECT `activityName`,SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600) AS hours
	FROM `apiindustryjobs` aij
	JOIN `ramactivities` rac
	ON aij.activityID=rac.activityID
	WHERE date_format(beginProductionTime, '%Y%m') = '201303'
	AND aij.corporationID=98126753
	GROUP BY `activityName`
	ORDER BY `activityName`;
	
	*/
				$data=db_asocquery($sql);
				
				$rearrange=array();
				
				foreach($data as $row) {
					$rearrange[$row['characterID']]['activities'][stripslashes($row['activityName'])]['points']=stripslashes($row['points']);
					$rearrange[$row['characterID']]['activities'][stripslashes($row['activityName'])]['activityName']=stripslashes($row['activityName']);
					$rearrange[$row['characterID']]['totalpoints']+=stripslashes($row['points']);
					$rearrange[$row['characterID']]['wage']+=stripslashes($row['wage']);
					$rearrange[$row['characterID']]['name']=stripslashes($row['name']);
					$rearrange[$row['characterID']]['characterID']=$row['characterID'];
				}
				
				//var_dump($rearrange);
				
				?>
				
				<table cellspacing="2" cellpadding="0">
				<tr><td class="tab-header" width="32" style="padding: 0px; text-align: center;">
					<b></b>
				</td><td class="tab-header" style="text-align: center;">
					<b>Name</b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b>Copying</b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b>Invention</b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b>Manufacturing</b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b>ME</b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b>PE</b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b>Reverse engineering</b>
				</td><td class="tab-header" width="48" style="text-align: center;">
					<b>Points</b>
				</td><td class="tab-header" width="96" style="text-align: center;">
					<b>ISK</b>
				</td>
				</tr>
			   <?
			$totals['ISK']=0.0;
			$totals['Copying']=0.0;
			$totals['Invention']=0.0;
			$totals['Manufacturing']=0.0;
			$totals['Researching Material Productivity']=0.0;
			$totals['Researching Time Productivity']=0.0;
			$totals['Reverse Engineering']=0.0;
			$totals['totalpoints']=0.0;
			foreach($rearrange as $row) {
				echo('<tr><td class="tab" style="padding: 0px;">');
					if ($rights_viewallchars) hrefedit($row['characterID']);
						echo("<img src=\"https://image.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
					if ($rights_viewallchars) echo('</a>');
				echo('</td><td class="tab">');
					if ($rights_viewallchars) hrefedit($row['characterID']);
						echo(stripslashes($row['name']));
					if ($rights_viewallchars) echo('</a>');
				echo('</td><td class="tab" style="text-align: center;">');
				//hrefedit($row['characterID']);
				echo(number_format($row['activities']['Copying']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
				$totals['Copying']+=$row['activities']['Copying']['points'];
				echo('</td><td class="tab" style="text-align: center;">');
				//hrefedit($row['characterID']);
				echo(number_format($row['activities']['Invention']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
				$totals['Invention']+=$row['activities']['Invention']['points'];
				echo('</td><td class="tab" style="text-align: center;">');
				//hrefedit($row['characterID']);
				echo(number_format($row['activities']['Manufacturing']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
				$totals['Manufacturing']+=$row['activities']['Manufacturing']['points'];
				echo('</td><td class="tab" style="text-align: center;">');
				//hrefedit($row['characterID']);
				echo(number_format($row['activities']['Researching Material Productivity']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
				$totals['Researching Material Productivity']+=$row['activities']['Researching Material Productivity']['points'];
				echo('</td><td class="tab" style="text-align: center;">');
				//hrefedit($row['characterID']);
				echo(number_format($row['activities']['Researching Time Productivity']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
				$totals['Researching Time Productivity']+=$row['activities']['Researching Time Productivity']['points'];
				echo('</td><td class="tab" style="text-align: center;">');
				//hrefedit($row['characterID']);
				echo(number_format($row['activities']['Reverse Engineering']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
				$totals['Reverse Engineering']+=$row['activities']['Reverse Engineering']['points'];
				echo('</td><td class="tab" style="text-align: center;">');
				//hrefedit($row['characterID']);
				echo(number_format(stripslashes($row['totalpoints']), 2, $DECIMAL_SEP, $THOUSAND_SEP));
				$totals['totalpoints']+=$row['totalpoints'];
				echo('</td><td class="tab" style="text-align: right;">');
				//hrefedit($row['characterID']);
				echo(number_format(stripslashes($row['wage']), 2, $DECIMAL_SEP, $THOUSAND_SEP));
				echo('</td>');
				echo('</tr>');
				$totals['ISK']+=stripslashes($row['wage']);
			}
			?>
			<tr><td class="tab-header" width="32" style="padding: 0px; text-align: center;">
					<b></b>
				</td><td class="tab-header" style="text-align: left;">
					<b>Total</b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Copying'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Invention'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Manufacturing'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Researching Material Productivity'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Researching Time Productivity'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</td><td class="tab-header" width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Reverse Engineering'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</td><td class="tab-header" width="48" style="text-align: center;">
					<b><?php echo(number_format($totals['totalpoints'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</td><td class="tab-header" width="96" style="text-align: right;">
					<b><?php echo(number_format($totals['ISK'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</td>
			</tr>
			</table>
			<? 
			
	}//end corps loop
		?>
		
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
			
		    </div><br>
		
