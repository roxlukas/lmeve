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
			Industry Activity for <?php echo("$year-$month"); ?><br>
		    </div>
		
		    <div class="tekst">
		    
		    <?php
		    
		    ?>
		    <a href="#down">Scroll down</a>
		    </div>
                


		    <?php
		    $corps=db_asocquery("SELECT * FROM apicorps;");
		    foreach ($corps as $corp) { //begin corps loop
                        $days="";
                        $activities="";
				echo("<h1><img src=\"https://image.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
?>

<?php
//GRAPHING		
                    //getting data
                        $dayss = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        for($i=1; $i<=$dayss; $i++) {
                            $daystab[$i]['day']="$i";
                            $daystab[$i]['activity']=0;
                        }
                        
                        $sqlact="SELECT COUNT(*) AS activity,date_format(beginProductionTime, '%e') AS day FROM
                        apiindustryjobs aij
                        WHERE date_format(beginProductionTime, '%Y%m') = '${year}${month}'
                        AND aij.corporationID=${corp['corporationID']}
                        GROUP BY date_format(beginProductionTime, '%e')
                        ORDER BY date_format(beginProductionTime, '%e');";
                        $activityGraph=db_asocquery($sqlact);
            ?><h3>Industry Activity [jobs started]</h3> <?php
            if (count($activityGraph)>0) {
                    //reformatting data
                        foreach ($activityGraph as $row) {
                            $daystab[$row['day']]['activity']=$row['activity'];
                        }
                        
                    //ready to display
                        foreach($daystab as $row) {
                            $days.='"'.$row['day'].'",';
                            if (!empty($row['activity'])) $activities.=$row['activity'].','; else $activities.='0,';
                        }
                    //cut trailing commas
                        $days=rtrim($days,',');
                        $activities=rtrim($activities,',');
                    //display
                        ?>
                    <!--<h2>&raquo; Activity</h2>-->
                        <div>
                        <canvas id="activity_<?php echo($corp['corporationID']); ?>" width="600" height="200"></canvas>
                        <script type="text/javascript">
                            var data_<?php echo($corp['corporationID']); ?> = {
                                labels : [ <?php echo($days); ?> ],
                                datasets : [
                                       {
                                           fillColor : "rgba(151,187,205,0.5)",
                                           strokeColor : "rgba(151,187,205,1.0)",
                                           <?php //pointColor : "rgba(205,107,101,1.0)",
                                           //pointStrokeColor : "#fff", ?>
                                           data : [ <?php echo($activities); ?> ]
                                       }
                                ]
                            }
                            
                            var ctx_<?php echo($corp['corporationID']); ?> = document.getElementById("activity_<?php echo($corp['corporationID']); ?>").getContext("2d");
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
		
