<?php

function getCdnCacheDbSize() {
    global $LM_CCPWGL_USEPROXY, $LM_CCPWGL_CACHESCHEMA;
    
    $sql="SELECT table_name AS 'table', 
    round(((data_length + index_length) / 1024 / 1024), 2) 'size' 
    FROM information_schema.TABLES 
    WHERE table_schema = '$LM_CCPWGL_CACHESCHEMA'
    AND table_name = 'lmproxyfiles';";
    
    if (!$LM_CCPWGL_USEPROXY) {
        return FALSE;
    } else {
        $data=db_asocquery($sql);
        if (count($data)>0) return $data[0]['size']; else return FALSE;
    }
}

function showTopByBytes($stats) {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    $count=count($stats);
    if ($count>0) {
        ?>
        <table class="lmframework" style="width: 100%;">
        <tr><th>
            Key
        </th><th>		
            Bytes
        </th>
        </tr>
        <?php
        foreach($stats as $stat) {
            $isUrl=preg_match('/^http.*$/', $stat['key']);
            ?>
            <tr><td>
                <?=strip_tags(shorten($stat['key'],40,$isUrl))?>
            </td><td>		
                <?=strip_tags(number_format($stat['value'], 0, $DECIMAL_SEP, ' '))?>
            </td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
}

function showTopByRequests($stats) {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    $count=count($stats);
    if ($count>0) {
        ?>
        <table class="lmframework" style="width: 100%;">
        <tr><th>
            Key
        </th><th>		
            Requests
        </th>
        </tr>
        <?php
        foreach($stats as $stat) {
            $isUrl=preg_match('/^http.*$/', $stat['key']);
            ?>
            <tr><td>
                <?=strip_tags(shorten($stat['key'],40,$isUrl))?>
            </td><td>		
                <?=strip_tags($stat['value'])?>
            </td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
}

function getTopClientsByRequests($n=5,$hours='1 day',$where='TRUE') {
   global $LM_CCPWGL_CACHESCHEMA, $LM_CCPWGL_PROXYAUDIT;
   if (!$LM_CCPWGL_PROXYAUDIT) return FALSE;
   $sql="SELECT COUNT(*) AS `value`,`ip` AS `key` FROM `$LM_CCPWGL_CACHESCHEMA`.`lmproxylog` WHERE `timestamp` > DATE_SUB(CURDATE(), INTERVAL $hours) AND $where GROUP BY `ip` ORDER BY `value` DESC LIMIT 0,$n;";
   $stats=db_asocquery($sql);
   return $stats; 
}

function getTopClientsByBytes($n=5,$hours='1 day',$where='TRUE') {
   global $LM_CCPWGL_CACHESCHEMA, $LM_CCPWGL_PROXYAUDIT;
   if (!$LM_CCPWGL_PROXYAUDIT) return FALSE;
   $sql="SELECT SUM(`bytes`) AS `value`,`ip` AS `key` FROM `$LM_CCPWGL_CACHESCHEMA`.`lmproxylog` WHERE `timestamp` > DATE_SUB(CURDATE(), INTERVAL $hours) AND $where GROUP BY `ip` ORDER BY `value` DESC LIMIT 0,$n;";
   $stats=db_asocquery($sql);
   return $stats; 
}

function getTopFilesByRequests($n=5,$hours='1 day',$where='TRUE') {
   global $LM_CCPWGL_CACHESCHEMA, $LM_CCPWGL_PROXYAUDIT;
   if (!$LM_CCPWGL_PROXYAUDIT) return FALSE;
   $sql="SELECT COUNT(*) AS `value`,`url` AS `key` FROM `$LM_CCPWGL_CACHESCHEMA`.`lmproxylog` WHERE `timestamp` > DATE_SUB(CURDATE(), INTERVAL $hours) AND $where GROUP BY `url` ORDER BY `value` DESC LIMIT 0,$n;";
   $stats=db_asocquery($sql);
   return $stats; 
}

function getTopFilesByBytes($n=5,$hours='1 day',$where='TRUE') {
   global $LM_CCPWGL_CACHESCHEMA, $LM_CCPWGL_PROXYAUDIT;
   if (!$LM_CCPWGL_PROXYAUDIT) return FALSE;
   $sql="SELECT SUM(`bytes`) AS `value`,`url` AS `key` FROM `$LM_CCPWGL_CACHESCHEMA`.`lmproxylog` WHERE `timestamp` > DATE_SUB(CURDATE(), INTERVAL $hours) AND $where GROUP BY `url` ORDER BY `value` DESC LIMIT 0,$n;";
   $stats=db_asocquery($sql);
   return $stats; 
}

function getRequestsInLast($hours='1 day',$where='TRUE') {
   global $LM_CCPWGL_CACHESCHEMA, $LM_CCPWGL_PROXYAUDIT;
   if (!$LM_CCPWGL_PROXYAUDIT) return FALSE;
   $sql="SELECT COUNT(*) AS `count` FROM `$LM_CCPWGL_CACHESCHEMA`.`lmproxylog` WHERE `timestamp` > DATE_SUB(CURDATE(), INTERVAL $hours) AND $where;";
   $stats=db_asocquery($sql);
   if (count($stats)>0) $stats=$stats[0];
   return $stats; 
}

function getBytesInLast($hours='1 day',$where='TRUE') {
   global $LM_CCPWGL_CACHESCHEMA, $LM_CCPWGL_PROXYAUDIT;
   if (!$LM_CCPWGL_PROXYAUDIT) return FALSE;
   $stats=db_asocquery("SELECT SUM(`bytes`) as bytes FROM `$LM_CCPWGL_CACHESCHEMA`.`lmproxylog` WHERE `timestamp` > DATE_SUB(CURDATE(), INTERVAL $hours) AND $where;");
   if (count($stats)>0) $stats=$stats[0];
   return $stats; 
}

function getRequestsInLast24h($where='TRUE') {
   global $LM_CCPWGL_CACHESCHEMA, $LM_CCPWGL_PROXYAUDIT;
   return getRequestsInLast('1 day',$where);
}

function getBytesInLast24h($where='TRUE') {
   global $LM_CCPWGL_CACHESCHEMA, $LM_CCPWGL_PROXYAUDIT;
   return getBytesInLast('1 day',$where);
}

function getLastProxyErrors($n) {
   $stats=getLastProxyRequests($n,"`status` != 'OK'");
   return $stats; 
}

function getLastProxyRequests($n,$where='TRUE') {
   global $LM_CCPWGL_CACHESCHEMA, $LM_CCPWGL_PROXYAUDIT;
   if (!$LM_CCPWGL_PROXYAUDIT) return FALSE;
   if (!is_numeric($n)) return FALSE;
   $stats=db_asocquery("SELECT * FROM `$LM_CCPWGL_CACHESCHEMA`.`lmproxylog` WHERE $where ORDER BY `timestamp` DESC LIMIT 0,$n;");
   return $stats; 
}

function shorten($string,$length,$isURL=FALSE) {
    if (strlen($string)<$length) {
        if ($isURL) {
            echo('<a href="'.$string.'">'.$string.'</a>');
        } else {
            echo($string);
        }
    } else {
        if ($isURL) {
            ?><a href="<?=$string?>" title="<?=$string?>"><?=substr($string,0,$length)?>...</a><?php
        } else {
            ?><span title="<?=$string?>"><?=substr($string,0,$length)?>...</span><?php
        }
    }
}

function showLastProxyRequests($stats) {
    global $DECIMAL_SEP, $THOUSAND_SEP;

    $count=count($stats);
    if ($count>0) {
        ?>
        <table class="lmframework">
        <tr><th>
            Timestamp
        </th><th>		
            IP Address
        </th><th>		
            Request
        </th><th>		
            Status
        </th><th>		
            Referer
        </th><th>		
            Used cache?
        </th><th>		
            Original URL
        </th><th>		
            HTTP code
        </th><th>		
            Bytes
        </th>
        </tr>
        <?php
        foreach($stats as $stat) {
            ?>
            <tr><td>
                <?=strip_tags($stat['timestamp'])?>
            </td><td>		
                <?=strip_tags($stat['ip'])?>
            </td><td>		
                <?=shorten(strip_tags($stat['fetch']),40)?>
            </td><td>		
                <?=strip_tags($stat['status'])?>
            </td><td>		
                <?=shorten(strip_tags($stat['referer']),40,TRUE)?>
            </td><td style="text-align: center;">		
                <?php if ($stat['cacheUsed']==1) echo('YES'); else echo('NO'); ?>
            </td><td>		
                <?=shorten(strip_tags($stat['url']),40,TRUE)?>
            </td><td style="text-align: right;">		
                <?=$stat['http_code']?>
            </td><td style="text-align: right;">		
                <?php echo(number_format($stat['bytes'], 0, $DECIMAL_SEP, ' ')); ?>
            </td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
}

function getIndustryStats($corporationID,$year,$month) {
    global $LM_EVEDB;
    //OLD date condition:
        //date_format(beginProductionTime, '%Y%m') = '${year}${month}'
    //NEW (otpimized) date condition:
        //beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')
    $stats=db_asocquery("SELECT `activityName`, COUNT(*) AS jobs, SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600) AS hours
	FROM `apiindustryjobs` aij
	JOIN $LM_EVEDB.`ramActivities` rac
	ON aij.activityID=rac.activityID
        WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')
	AND aij.corporationID=${corporationID}
	GROUP BY `activityName`
	ORDER BY `activityName`;");
    return $stats;
}

function showIndustryStats($stats) {
    $sumstat=0.0;
    $sumjobs=0;
    if (count($stats)>0) {                                        
        echo("<h2>Statistics</h2>");
        echo('<table class="lmframework">');
        echo('<tr><th>');
        echo('Activity');
        echo('</th><th>');		
        echo('Jobs');
        echo('</td><th>');		
        echo('Hours');
        echo('</th></tr>');
        foreach($stats as $stat) {
            echo('<tr><td>');
            echo($stat['activityName']);
            echo('</td><td style="text-align: right;">');
            echo(number_format($stat['jobs'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
            $sumjobs+=$stat['jobs'];
            echo('</td><td style="text-align: right;">');
            echo(number_format($stat['hours'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
            $sumstat+=$stat['hours'];
            echo('</td></tr>');
        }
        echo('<tr><th>');
        echo('TOTAL:');
        echo('</th><th style="text-align: right;">');
        echo(number_format($sumjobs, 0, $DECIMAL_SEP, $THOUSAND_SEP));
        echo('</th><th style="text-align: right;">');		
        echo(number_format($sumstat, 0, $DECIMAL_SEP, $THOUSAND_SEP));
        echo('</td></tr>');
        echo('</table>');
    }
}

function getIndustryActivity($corporationID,$year,$month) {
    $sqlact="SELECT COUNT(*) AS activity,date_format(beginProductionTime, '%e') AS day FROM
            apiindustryjobs aij
            WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')
            AND aij.corporationID=${corporationID}
            GROUP BY date_format(beginProductionTime, '%e')
            ORDER BY date_format(beginProductionTime, '%e');";
    $activityGraph=db_asocquery($sqlact);
    return $activityGraph;
}

function showIndustryActivity($corporationID,$year,$month,$activityGraph) {
    global $MOBILE;
    ?>
    <h2>Industry Activity [jobs started]</h2>
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
    <?php
              //getting data
                        $days="";
                        $activities="";
                        $dayss = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        for($i=1; $i<=$dayss; $i++) {
                            $daystab[$i]['day']="$i";
                            $daystab[$i]['activity']=0;
                        }
   
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
                        
                        <canvas id="activity_<?php echo($corporationID); ?>" width="600" height="200"></canvas>
                        <script type="text/javascript">
                            var data_<?php echo($corporationID); ?> = {
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
                            
                            var ctx_<?php echo($corporationID); ?> = document.getElementById("activity_<?php echo($corporationID); ?>").getContext("2d");
                            <?php if ($MOBILE) echo('ctx_'.$corporationID.'.canvas.width  = window.innerWidth;'); ?>
                            var activityChart_<?php echo($corporationID); ?> = new Chart(ctx_<?php echo($corporationID); ?>).Bar(data_<?php echo($corporationID); ?>,bar_options);
                            
                        </script>
                        
                <?php  
            } else {
                echo("<div class=\"tekst\"><strong>No data found.</strong></div>");
            }
}
?>
