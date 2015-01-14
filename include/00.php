<?php 
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
include_once('tasks.php');
include_once('stats.php');

$rights_viewallchars=checkrights("Administrator,ViewAllCharacters");
$rights_edithours=checkrights("Administrator,EditHoursPerPoint");

$date=secureGETnum("date");
$width=600;

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

		function charhrefedit($nr) {
			global $MENUITEM;
		    echo("<a href=\"index.php?id=9&id2=6&nr=$nr\" title=\"Click to open character information\">");
		}

$pointsDisplayed=false;
	    
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
		    $points=db_asocquery("SELECT rac.`activityName`,cpt.* FROM $LM_EVEDB.`ramActivities` rac JOIN `cfgpoints` cpt ON rac.`activityID`=cpt.`activityID` ORDER BY `activityName`;");
		    $ONEPOINT=getConfigItem('iskPerPoint','15000000'); //loaded from db now! :-)
		    		    
		    
		    		    
		    $corps=db_asocquery("SELECT * FROM apicorps;");
		    foreach ($corps as $corp) { //begin corps loop
                        $days="";
                        $activities="";
				echo("<h1><img src=\"https://image.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
?>
<!--<script>
$(function() {
    $( "#accrd_<?php echo($corp['corporationID']); ?>" ).accordion({
      heightStyle: "content",
      header: "h2"
    });
  });
</script>-->
<div id="accrd_<?php echo($corp['corporationID']); ?>">
    <!--<h2>&raquo; Summary</h2>-->
<div>
<?php	
        
				
				echo('<table cellspacing="2" cellpadding="0" width="100%">');
				echo('<tr><td width="40%" style="vertical-align: top;">');
                  if (!$pointsDisplayed) {
					echo("<h2>Points");
                                        if (checkrights("Administrator,EditHoursPerPoint")) { ?>
                                            <input type="button" value="Edit hours-per-point" onclick="location.href='?id=5&id2=10';">
                                        <?php }
					echo('</h2><table class="lmframework">');
					echo('<tr><th>');
					echo('Activity');
					echo('</td><th>');		
					echo('Hours');
					echo('</td></tr>');
					foreach($points as $point) {
						echo('<tr><td>');
                                                if ($rights_edithours) pointshrefedit($point['activityID']);
						echo($point['activityName']);
                                                if ($rights_edithours) echo("</a>");
						echo('</td><td>');
                                                if ($rights_edithours) pointshrefedit($point['activityID']);
						echo($point['hrsPerPoint']);
                                                if ($rights_edithours) echo("</a>");
						echo('</td></tr>');
					}
					echo('</table>');
                                        
					echo("<strong>1 point = ".number_format($ONEPOINT, 2, $DECIMAL_SEP, $THOUSAND_SEP)." ISK</strong>");
                                        if (checkrights("Administrator")) { ?>
                                            <input type="button" value="Edit" onclick="location.href='?id=5&id2=0';">
                                        <?php }
                                        echo('<br/>');
                                        $pointsDisplayed=true;
                    }
					echo('</td><td width="60%" style="vertical-align: top;">');
					
	//display stats here	
        showIndustryStats(getIndustryStats($corp['corporationID'], $year, $month));
				echo('</td></tr></table>');
?>
</div>                        
                    </div>
                    <!--<h2>Timesheet</h2>-->
                <?php                                
                                            
//Timesheet				
			
				$sql_all="SELECT *,ROUND((points*$ONEPOINT),2) as wage FROM (
	SELECT `characterID`,`name`,`activityName`,SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600)/hrsPerPoint AS points
	FROM `apiindustryjobs` aij
	JOIN $LM_EVEDB.`ramActivities` rac
	ON aij.activityID=rac.activityID
	JOIN cfgpoints cpt
	ON aij.activityID=cpt.activityID
	JOIN apicorpmembers acm
	ON aij.installerID=acm.characterID
	WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')
	AND aij.corporationID=${corp['corporationID']}
	GROUP BY `characterID`,`name`,`activityName`
	ORDER BY `name`,`activityName`) AS wages;";
	
	
				$data=db_asocquery($sql_all);
                                
                                //echo("<pre>DB=\n");
                                //var_dump($data);
				//echo("</pre>");
                                
				$rearrange=array();
				
				foreach($data as $row) {
					$rearrange[$row['characterID']]['activities'][stripslashes($row['activityName'])]['points']=stripslashes($row['points']);
					$rearrange[$row['characterID']]['activities'][stripslashes($row['activityName'])]['activityName']=stripslashes($row['activityName']);
					$rearrange[$row['characterID']]['totalpoints']+=stripslashes($row['points']);
					$rearrange[$row['characterID']]['wage']+=stripslashes($row['wage']);
					$rearrange[$row['characterID']]['name']=stripslashes($row['name']);
					$rearrange[$row['characterID']]['characterID']=$row['characterID'];
				}
				
				//echo("<pre>rearrange=\n");
                                //var_dump($rearrange);
				//echo("</pre>");
				
				?>
				
				<table class="lmframework">
				<tr><th width="32" style="padding: 0px; text-align: center;">
					<b></b>
				</th><th style="text-align: center;">
					<b>Name</b>
				</th><th width="64" style="text-align: center;">
					<b>Copying</b>
				</td><th width="64" style="text-align: center;">
					<b>Invention</b>
				</th><th width="64" style="text-align: center;">
					<b>Manufacturing</b>
				</th><th width="64" style="text-align: center;">
					<b>ME</b>
				</th><th width="64" style="text-align: center;">
					<b>PE</b>
				</th><th width="64" style="text-align: center;">
					<b>Reverse engineering</b>
				</th><th width="48" style="text-align: center;">
					<b>Points</b>
				</th><th width="96" style="text-align: center;">
					<b>ISK</b>
				</td>
				</tr>
			   <?php
                        $mychars=getMyChars(true);   
                        //var_dump($mychars);
			$totals['ISK']=0.0;
			$totals['Copying']=0.0;
			$totals['Invention']=0.0;
			$totals['Manufacturing']=0.0;
			$totals['Researching Material Efficiency']=0.0;
			$totals['Researching Time Efficiency']=0.0;
			$totals['Reverse Engineering']=0.0;
			$totals['totalpoints']=0.0;
                        if ($mychars) {
                            echo('<tr><th colspan="10" style="text-align: center;">My characters</th></tr>');
                        }
			foreach($rearrange as $row) {
                                if ($mychars!=false && in_array($row['characterID'], $mychars)) {
                                    echo('<tr><td style="padding: 0px;">');
                                            if ($rights_viewallchars) charhrefedit($row['characterID']);
                                                    echo("<img src=\"https://image.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
                                            if ($rights_viewallchars) echo('</a>');
                                    echo('</td><td>');
                                            if ($rights_viewallchars) charhrefedit($row['characterID']);
                                                    echo(stripslashes($row['name']));
                                            if ($rights_viewallchars) echo('</a>');
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Copying']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Copying']+=$row['activities']['Copying']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Invention']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Invention']+=$row['activities']['Invention']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Manufacturing']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Manufacturing']+=$row['activities']['Manufacturing']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Researching Material Efficiency']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Researching Material Efficiency']+=$row['activities']['Researching Material Efficiency']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Researching Time Efficiency']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Researching Time Efficiency']+=$row['activities']['Researching Time Efficiency']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Reverse Engineering']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Reverse Engineering']+=$row['activities']['Reverse Engineering']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format(stripslashes($row['totalpoints']), 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['totalpoints']+=$row['totalpoints'];
                                    echo('</td><td style="text-align: right;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format(stripslashes($row['wage']), 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    echo('</td>');
                                    echo('</tr>');
                                    $totals['ISK']+=stripslashes($row['wage']);
                                }
			}
                        if ($mychars) {
                            ?>
                            <tr><th width="32" style="padding: 0px; text-align: center;">
					<b></b>
				</th><th style="text-align: left;">
					<b>My total</b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Copying'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Invention'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Manufacturing'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Researching Material Efficiency'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Researching Time Efficiency'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Reverse Engineering'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="48" style="text-align: center;">
					<b><?php echo(number_format($totals['totalpoints'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="96" style="text-align: right;">
					<b><?php echo(number_format($totals['ISK'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th>
                            </tr>
                            <?php
                            //echo('<tr><th colspan="10" style="text-align: center;">Other characters</th></tr>');
                        }
			foreach($rearrange as $row) {
                                if ($mychars==false || !in_array($row['characterID'], $mychars)) {
                                    echo('<tr><td style="padding: 0px;">');
                                            if ($rights_viewallchars) charhrefedit($row['characterID']);
                                                    echo("<img src=\"https://image.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
                                            if ($rights_viewallchars) echo('</a>');
                                    echo('</td><td>');
                                            if ($rights_viewallchars) charhrefedit($row['characterID']);
                                                    echo(stripslashes($row['name']));
                                            if ($rights_viewallchars) echo('</a>');
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Copying']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Copying']+=$row['activities']['Copying']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Invention']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Invention']+=$row['activities']['Invention']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Manufacturing']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Manufacturing']+=$row['activities']['Manufacturing']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Researching Material Efficiency']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Researching Material Efficiency']+=$row['activities']['Researching Material Efficiency']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Researching Time Efficiency']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Researching Time Efficiency']+=$row['activities']['Researching Time Efficiency']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format($row['activities']['Reverse Engineering']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['Reverse Engineering']+=$row['activities']['Reverse Engineering']['points'];
                                    echo('</td><td style="text-align: center;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format(stripslashes($row['totalpoints']), 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    $totals['totalpoints']+=$row['totalpoints'];
                                    echo('</td><td style="text-align: right;">');
                                    //charhrefedit($row['characterID']);
                                    echo(number_format(stripslashes($row['wage']), 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                    echo('</td>');
                                    echo('</tr>');
                                    $totals['ISK']+=stripslashes($row['wage']);
                                }
			}
			?>
			<tr><th width="32" style="padding: 0px; text-align: center;">
					<b></b>
				</th><th style="text-align: left;">
					<b>Total</b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Copying'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Invention'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Manufacturing'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Researching Material Efficiency'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Researching Time Efficiency'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="64" style="text-align: center;">
					<b><?php echo(number_format($totals['Reverse Engineering'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="48" style="text-align: center;">
					<b><?php echo(number_format($totals['totalpoints'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th><th width="96" style="text-align: right;">
					<b><?php echo(number_format($totals['ISK'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
				</th>
			</tr>
			</table>
			<?php 
			
	}//end corps loop
		?>
		
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
			
		    </div><br>
		
