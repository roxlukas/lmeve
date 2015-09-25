<?php 
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewActivity")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='PVP Statistics'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;
include_once('tasks.php');
$width=600;

$date=secureGETnum("date");

if (strlen($date)==6) {
	$year=substr($date,0,4);
	$month=substr($date,4,2);
} else {
	$year=date("Y");
	$month=date("m");	
}

$pointsDisplayed=false;
	    
		?>
<script type="text/javascript" src="chart.js/Chart.min.js"></script>
                <script type="text/javascript">
                            pie_options = {
                                    //Boolean - Whether we should show a stroke on each segment
                                    segmentShowStroke : true,
                                    //String - The colour of each segment stroke
                                    segmentStrokeColor : "#fff",
                                    //Number - The width of each segment stroke
                                    segmentStrokeWidth : 2,
                                    //Boolean - Whether we should animate the chart	
                                    animation : true,
                                    //Number - Amount of animation steps
                                    animationSteps : 100,
                                    //String - Animation easing effect
                                    animationEasing : "easeOutBounce",
                                    //Boolean - Whether we animate the rotation of the Pie
                                    animateRotate : true,
                                    //Boolean - Whether we animate scaling the Pie from the centre
                                    animateScale : false,
                                    //Function - Will fire on animation completion.
                                    onAnimationComplete : null
                            }
                           bar_options = {
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
                                    scaleLabel : "<%=value%>",
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
		<a name="top"></a>
		    <div class="tytul">
			PVP Statistics for <?php echo("$year-$month"); ?><br>
		    </div>
		
		    <div class="tekst">
		    <a href="#down">Scroll down</a>
		    </div>
                
		    <?php
                    
		    $corps=db_asocquery("SELECT * FROM apicorps;");
		    foreach ($corps as $corp) { //begin corps loop
                        $days="";
                        $kills="";
                        $losses="";
                        $daystab="";
				echo("<h1><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
?>

<?php
//GRAPHING		
                    //getting data
                        $dayss = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        for($i=1; $i<=$dayss; $i++) {
                            $daystab[$i]['day']="$i";
                            $daystab[$i]['activity']=0;
                        }
                        
                        $sqlkill="SELECT COUNT(*) AS kills,date_format(`killTime`, '%e') AS day FROM
                        (SELECT DISTINCT `killID`,`corporationID` FROM `apikillattackers`) AS aka
                        JOIN `apikills` aks
                        ON aks.`killID`=aka.`killID`
                        WHERE `killTime` BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
                        AND aka.`corporationID`=${corp['corporationID']}
                        GROUP BY date_format(`killTime`, '%e')
                        ORDER BY date_format(`killTime`, '%e');";
                        
                        $killGraph=db_asocquery($sqlkill);
                        
                        $sqlloss="SELECT COUNT(*) AS losses,date_format(`killTime`, '%e') AS day FROM
                        `apikillvictims` akv
                        JOIN `apikills` aks
                        ON aks.`killID`=akv.`killID`
                        WHERE `killTime` BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
                        AND akv.`corporationID`=${corp['corporationID']}
                        GROUP BY date_format(`killTime`, '%e')
                        ORDER BY date_format(`killTime`, '%e');";
                        
                        $lossGraph=db_asocquery($sqlloss);
                        
                        
                        
            ?><h3>PVP Activity [kills, losses]</h3> <?php
            if (count($killGraph)>0 || count($lossGraph)>0 ) {
                    //reformatting data
                        foreach ($killGraph as $row) {
                            $daystab[$row['day']]['kills']=$row['kills'];
                        }
                        foreach ($lossGraph as $row) {
                            $daystab[$row['day']]['losses']=$row['losses'];
                        }
                        
                        
                    //ready to display
                        foreach($daystab as $row) {
                            $days.='"'.$row['day'].'",';
                            if (!empty($row['kills'])) $kills.=$row['kills'].','; else $kills.='0,';
                            if (!empty($row['losses'])) $losses.=$row['losses'].','; else $losses.='0,';
                        }
                        
                    //cut trailing commas
                        $days=rtrim($days,',');
                        $kills=rtrim($kills,',');
                        $losses=rtrim($losses,',');
                        
                    //display
                        ?>
                    <!--<h2>&raquo; Activity</h2>-->
                        <div>
                        <canvas id="pvp_<?php echo($corp['corporationID']); ?>" width="600" height="200"></canvas>
                        <script type="text/javascript">
                            var data_<?php echo($corp['corporationID']); ?> = {
                                labels : [ <?php echo($days); ?> ],
                                datasets : [
                                       {
                                           fillColor : "rgba(151,187,205,0.5)",
                                           strokeColor : "rgba(151,187,205,1.0)",
                                           <?php //pointColor : "rgba(205,107,101,1.0)",
                                           //pointStrokeColor : "#fff", ?>
                                           data : [ <?php echo($kills); ?> ]
                                       },
                                       {
                                           fillColor : "rgba(205,0,0,0.5)",
                                           strokeColor : "rgba(205,101,101,1.0)",
                                           <?php //pointColor : "rgba(205,107,101,1.0)",
                                           //pointStrokeColor : "#fff", ?>
                                           data : [ <?php echo($losses); ?> ]
                                       },
                                ]
                            }
                            
                            var ctx_<?php echo($corp['corporationID']); ?> = document.getElementById("pvp_<?php echo($corp['corporationID']); ?>").getContext("2d");
                            <?php if ($MOBILE) echo('ctx_'.$corp['corporationID'].'.canvas.width  = window.innerWidth;'); ?>
                            var activityChart_<?php echo($corp['corporationID']); ?> = new Chart(ctx_<?php echo($corp['corporationID']); ?>).Bar(data_<?php echo($corp['corporationID']); ?>,bar_options);
                            
                        </script>
                        </div>
                    </div>
                    <!--<h2>Timesheet</h2>-->
                <?php                                
            } else {
                echo("<div class=\"tekst\"><strong>No data found.</strong></div>");
            }
			
			
	}//end corps loop
		?>
		
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
			
		    </div><br>
		
