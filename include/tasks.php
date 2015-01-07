<?php
//Task related functions
//getTasks($sqlwhere1, $sqlwhere2) - get tasks from DB
//$sqlwhere1 - first SQL parameter, in main query
//$sqlwhere2 - second SQL parameter, in both main query and in subqueries

include_once("percentage.php");

/**
 * getTasks() get task information from db for a specific character
 *
 * @global type $LM_EVEDB - static data dump schema
 * @global type $USERSTABLE - table with usernames
 * @param type $MYTASKS - WHERE characterID IN (xxx, yyy)
 * @param type $SELECTEDCHAR - WHERE characterID=xxx
 * @param type $ORDERBY - ORDER BY typeName
 * @param type $year - 2013
 * @param type $month - 07
 * @return boolean 
 */
function getTasks($MYTASKS, $SELECTEDCHAR, $ORDERBY, $year, $month) {
	global $LM_EVEDB, $USERSTABLE;

	$sql="SELECT a.*,b.runsDone,b.jobsDone,c.jobsSuccess,d.jobsCompleted,e.runsCompleted
	FROM (SELECT acm.name, lmt.characterID, itp.typeName, lmt.typeID, rac.activityName, lmt.activityID, lmt.taskID, lmt.runs
	FROM lmtasks lmt
	JOIN apicorpmembers acm
	ON acm.characterID=lmt.characterID
	JOIN $LM_EVEDB.invTypes itp
	ON lmt.typeID=itp.typeID
	JOIN $LM_EVEDB.ramActivities rac
	ON lmt.activityID=rac.activityID
	WHERE $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND lmt.taskCreateTimestamp BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')) OR (singleton=0))
	) AS a
	LEFT JOIN	
	(SELECT lmt.taskID, SUM(aij.runs)*itp.portionSize AS runsDone, COUNT(*) AS jobsDone
	FROM lmtasks lmt
	JOIN $LM_EVEDB.invTypes itp
	ON lmt.typeID=itp.typeID
	JOIN apiindustryjobs aij
	ON lmt.typeID=aij.outputTypeID AND lmt.activityID=aij.activityID AND lmt.characterID=aij.installerID
	WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')
	AND $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND lmt.taskCreateTimestamp BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')) OR (singleton=0))
	GROUP BY lmt.characterID, lmt.typeID, lmt.activityID, lmt.taskID
	) AS b
	ON a.taskID=b.taskID
	LEFT JOIN	
	(SELECT lmt.taskID, SUM(successfulRuns) AS jobsSuccess
	FROM lmtasks lmt
	JOIN apiindustryjobs aij
	ON lmt.typeID=aij.outputTypeID AND lmt.activityID=aij.activityID AND lmt.characterID=aij.installerID
	WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')
	AND $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND lmt.taskCreateTimestamp BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')) OR (singleton=0))
	GROUP BY lmt.characterID, lmt.typeID, lmt.activityID, lmt.taskID
	) AS c
	ON a.taskID=c.taskID
	LEFT JOIN	
	(SELECT lmt.taskID, COUNT(*) AS jobsCompleted, SUM(aij.runs) * itp.portionSize AS runsCompleted
	FROM lmtasks lmt
	JOIN apiindustryjobs aij
	ON lmt.typeID=aij.outputTypeID AND lmt.activityID=aij.activityID AND lmt.characterID=aij.installerID
        JOIN $LM_EVEDB.invTypes itp
	ON lmt.typeID=itp.typeID
	WHERE aij.completed=1 AND beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')
	AND $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')) OR (singleton=0))
	GROUP BY lmt.characterID, lmt.typeID, lmt.activityID, lmt.taskID
	) AS d
	ON a.taskID=d.taskID
        LEFT JOIN	
	(SELECT lmt.taskID, SUM(aij.runs) * itp.portionSize AS runsCompleted
	FROM lmtasks lmt
	JOIN apiindustryjobs aij
	ON lmt.typeID=aij.outputTypeID AND lmt.activityID=aij.activityID AND lmt.characterID=aij.installerID
        JOIN $LM_EVEDB.invTypes itp
	ON lmt.typeID=itp.typeID
	WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01') AND aij.endProductionTime < UTC_TIMESTAMP()
	AND $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND lmt.taskCreateTimestamp BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')) OR (singleton=0))
	GROUP BY lmt.characterID, lmt.typeID, lmt.activityID, lmt.taskID
	) AS e
	ON a.taskID=e.taskID
	$ORDERBY";
	//echo("DEBUG:<hr/> $sql<hr/>");
	return(db_asocquery($sql));
}

function getOrphanedTasks() {
    $sql_sel="SELECT * FROM `lmtasks` lmt
            LEFT JOIN `apicorpmembers` apc ON lmt.`characterID` = apc.`characterID`
            WHERE apc.`characterID` IS NULL;";
    $tasks=db_asocquery($sql_sel);
    $lista="";
    if (count($tasks)>0) {
        foreach($tasks as $task) {
            $lista.=$task['taskID'].',';
        }
        //cut trailing comma
        $lista=rtrim($lista,',');
        return($lista);
    } else {
        return FALSE;
    }
}

function getOrphanedTasksCount() {
    $sql_sel="SELECT * FROM `lmtasks` lmt
            LEFT JOIN `apicorpmembers` apc ON lmt.`characterID` = apc.`characterID`
            WHERE apc.`characterID` IS NULL;";
    $ile=db_count($sql_sel);
    return($ile);
}

function clearOrphanedTasks() {
    $lista=getOrphanedTasks();
    $sql_del="DELETE FROM `lmtasks` WHERE `taskID` IN ($lista);";
    $ret=db_uquery($sql_del);
    if ($ret!==FALSE) {
        return($ile);
    } else {
        return FALSE;
    }
}

function getTasksByLab($nr) {
    $year=date("Y"); $month=date("m");
    $tasks=db_asocquery("SELECT * FROM `lmtasks` WHERE `structureID`=$nr
    AND ((`singleton`=1 AND `taskCreateTimestamp` BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')) OR (`singleton`=0));");
    return ($tasks);
}

/**
 * getTask() get task information from db for a specific character
 *
 * @global type $LM_EVEDB - static data dump schema
 * @global type $USERSTABLE - table with usernames
 * @param type $taskID - id of task
 * @return boolean 
 */
function getTask($taskID) {
	global $LM_EVEDB, $USERSTABLE;
	$sql="SELECT * FROM `lmtasks` lmt
            WHERE lmt.`taskID`=$taskID;";
	//echo("DEBUG:<hr/> $sql<hr/>");
        $raw=db_asocquery($sql);
        if (count($raw)>0) {
            return $raw[0];
        } else {
            return false;
        }
}

function taskhrefedit($nr,$date) {
	global $MENUITEM,$year,$month;
	echo("<a href=\"index.php?id=1&id2=0&date=$date&nr=$nr\" title=\"Click to see tasks for this Character\">");
}

function edittaskhrefedit($nr) {
	global $MENUITEM,$year,$month;
	echo("<a href=\"index.php?id=1&id2=1&nr=$nr\" title=\"Click to edit this Task\">");
}

function itemhrefedit($nr) {
	echo("<a href=\"index.php?id=10&id2=1&nr=$nr\" title=\"Click to open Database\">");
}

function getMyChars($array=false) {
    $ret="";
    $chars=db_asocquery("SELECT acm.characterID FROM apicorpmembers acm
	JOIN lmchars lmc
	ON lmc.charID=acm.characterID
        WHERE lmc.userID=${_SESSION[granted]};");
    if (!empty($chars)) {
        if ($array==false) {
            foreach ($chars as $c) {
                $ret=$ret.$c['characterID'].',';
            }
            $ret = substr_replace($ret,"",-1); //cut the last comma
        } else {
            $i=0;
            foreach ($chars as $c) {
                $ret[$i++]=$c['characterID'];
            }
        }
        return $ret;
    } else {
        return false;
    }
}

/**
 * showTasks() - show a HTML table with tasks in $tasklist
 * 
 * @param type $tasklist
 * @return type 
 */
function showTasks($tasklist) {
    global $MOBILE;
	if (!sizeof($tasklist)>0) {
		echo('<h3>There is no tasks assigned!</h3>');
	} else {
	?>
	<table class="lmframework">
	<tr><th>
		
	</th><?php if(!$MOBILE) { ?><th>
		Character
	</th><th>
		Task
	</th><?php } ?><th>
		
	</th><?php if(!$MOBILE) { ?><th>
		Type
	</th><?php } ?><th>
		Done
	</th><th>
		Quantity
	</th><th>
		Progress
	</th><th>
		Success
	</th><th style="width: 36px;">
		Kit
	</th>
	</tr>
	<?php
		//var_dump($tasklist);
		$rights=checkrights("Administrator,EditTasks");
		foreach($tasklist as $row) {
			echo('<tr><td style="padding: 0px; width: 32px;">');
                        echo("<a name=\"kit_anchor_${row['taskID']}\"></a>");
			taskhrefedit($row['characterID'],$year.$month);
				echo("<img src=\"https://image.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
			echo('</a>');
			echo('</td>');
                    if(!$MOBILE) {
                        echo('<td>');
			taskhrefedit($row['characterID'],$year.$month);
				echo($row['name']);
			echo('</a>');
			echo('</td>');
                        
                        echo('<td>');
			if ($rights) edittaskhrefedit($row['taskID']);
				echo($row['activityName']);
                                echo("&nbsp;<img src=\"ccp_icons/38_16_208.png\" style=\"vertical-align: middle;\" />");
			if ($rights) echo('</a>');
                        echo('</td>');
                    }
                        echo('<td style="padding: 0px; width: 32px;">');
			itemhrefedit($row['typeID']);
				echo("<img src=\"ccp_img/${row[typeID]}_32.png\" title=\"${row['typeName']}\" />");
			echo('</a>');
			echo('</td>');
                    if(!$MOBILE) {
                        echo('<td>');
			itemhrefedit($row['typeID']);
				echo($row['typeName']);
                                echo("&nbsp;<img src=\"ccp_icons/38_16_208.png\" style=\"vertical-align: middle;\" />");
			echo('</a>');
			echo('</td>');
                    }
                        echo('<td style="text-align: center;">');
				if (empty($row['runsDone'])) $row['runsDone']=0;
				echo($row['runsDone']);
			echo('</td><td style="text-align: center;">');
				echo($row['runs']);
			echo('</td><td style="text-align: center;">');
                                //var_dump($row);
				if ($row['runs'] > 0) {
                                        $percent1=round(100*$row['runsDone']/$row['runs']);
                                        $percent2=round(100*($row['runsDone']-$row['runsCompleted'])/$row['runs']);
                                }  else {
                                    $percent1=0;
                                    $percent2=0;
                                }
                                if ($MOBILE) {
                                    echo($percent1+$percent2.'%');
                                } else {
                                    percentbar2($percent1,$percent2,"Done ${row['runsDone']} of ${row['runs']}");
                                }
				echo('</td><td style="text-align: center;">');
				if (empty($row['jobsCompleted'])) $row['jobsCompleted']=0;
				if (empty($row['jobsSuccess'])) $row['jobsSuccess']=0;
				if (($row['activityID']==7) || ($row['activityID']==8)) {
				if ($row['runsCompleted'] > 0) $realperc=round(100*$row['jobsSuccess']/$row['runsCompleted']); else $realperc=0;
                                if ($MOBILE) {
                                    echo($realperc.'%');
                                } else {   
                                    percentbar($realperc,"${row['jobsSuccess']} successful in ${row['runsCompleted']} attempts");
                                }
				}
			echo('</td><td style="text-align: center; padding: 0px; width: 48px;">');
                                //$row['runs'] - total number of runs, $row['runsDone'] - number of completed runs
                                $remainingRuns=$row['runs']-$row['runsDone']; if ($remainingRuns<0) $remainingRuns=0;
                                //v1
				//echo("<a href=\"#kit_anchor_${row['taskID']}\" title=\"FULL kit for this task\" onclick=\"getKit('kit_row_".$row['taskID']."','kit_".$row['taskID']."',".$row['typeID'].",".$row['activityID'].",".$row['runs'].")\">");
                                //v2
                                //echo("<span title=\"FULL kit for this task\" onclick=\"getKit('kit_row_".$row['taskID']."','kit_".$row['taskID']."',".$row['typeID'].",".$row['activityID'].",".$row['runs'].")\">");
                                //v3
                                echo("<span title=\"FULL kit for this task\" onclick=\"getKit2('kit_row_".$row['taskID']."','kit_".$row['taskID']."',".$row['taskID'].",".$row['runs'].")\">");
                                //44_32_10 - white container icon
                                //57_64_10 - production line
                                //6_64_2 - calculator - for pricing quotation! -> materials.php
                                //26_64_11 - container
                                //7_64_16 - plastic wrap
                                //12_64_3 - market box <- best!
                                echo("<img src=\"ccp_icons/12_64_3.png\" style=\"width: 24px; height: 24px;\" /></span>");
                                //v1
                                //echo("<a href=\"#kit_anchor_${row['taskID']}\" title=\"REMAINING kit for this task\" onclick=\"getKit('kit_row_".$row['taskID']."','kit_".$row['taskID']."',".$row['typeID'].",".$row['activityID'].",".$remainingRuns.")\">");
                                //v2
                                //echo("<span title=\"REMAINING kit for this task\" onclick=\"getKit('kit_row_".$row['taskID']."','kit_".$row['taskID']."',".$row['typeID'].",".$row['activityID'].",".$remainingRuns.")\">");
                                //v3
                                echo("<span title=\"REMAINING kit for this task\" onclick=\"getKit2('kit_row_".$row['taskID']."','kit_".$row['taskID']."',".$row['taskID'].",".$remainingRuns.")\">");
                                echo("<img src=\"ccp_icons/7_64_16.png\" style=\"width: 24px; height: 24px;\" /></span>");
			echo('</td>');
			
			echo('</tr>');
                        //Empty row... workaround for even-odd coloring purposes
                        echo("<tr style=\"display: none\"><td colspan=\"10\">");
                        echo('</td></tr>');
                        //Kit holder
                        echo("<tr id=\"kit_row_${row['taskID']}\" style=\"display: none\"><td colspan=\"10\">");
                        echo("<div id=\"kit_${row['taskID']}\"><em>Loading kit data...</em></div>");
                        echo('</td></tr>');
		}
		echo('</table>');
	}
	return;
}

/**
 *
 * @deprecated
 * @param type $tasklist
 * @return type 
 */
function showTasks_old($tasklist) {
	if (!sizeof($tasklist)>0) {
		echo('<h3>There is no tasks assigned!</h3>');
	} else {
	?>
	<table cellspacing="2" cellpadding="0">
	<tr><td class="tab-header">
		<b></b>
	</td><td class="tab-header">
		<b>Character</b>
	</td><td class="tab-header">
		<b></b>
	</td><td class="tab-header">
		<b>Task</b>
	</td><td class="tab-header">
		<b>Type</b>
	</td><td class="tab-header">
		<b>Done</b>
	</td><td class="tab-header">
		<b>Quantity</b>
	</td><td class="tab-header">
		<b>Progress</b>
	</td><td class="tab-header">
		<b>Success</b>
	</td>
	</tr>
	<?php
		//var_dump($tasklist);
		$rights=checkrights("Administrator,EditTasks");
		foreach($tasklist as $row) {
			echo('<tr><td class="tab" style="padding: 0px; width: 32px;">');
			taskhrefedit($row['characterID'],$year.$month);
				echo("<img src=\"https://image.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
			echo('</a>');
			echo('</td><td class="tab">');
			taskhrefedit($row['characterID'],$year.$month);
				echo($row['name']);
			echo('</a>');
			echo('</td><td class="tab" style="padding: 0px; width: 32px;">');
			if ($rights) edittaskhrefedit($row['taskID']);
				echo("<img src=\"ccp_img/${row[typeID]}_32.png\" title=\"${row['typeName']}\" />");
			if ($rights) echo('</a>');
			echo('</td><td class="tab">');
			if ($rights) edittaskhrefedit($row['taskID']);
				echo($row['activityName']);
			if ($rights) echo('</a>');
			echo('</td><td class="tab">');
			if ($rights) edittaskhrefedit($row['taskID']);
				echo($row['typeName']);
			if ($rights) echo('</a>');
			echo('</td><td class="tab" style="text-align: center;">');
				if (empty($row['runsDone'])) $row['runsDone']=0;
				echo($row['runsDone']);
			echo('</td><td class="tab" style="text-align: center;">');
				echo($row['runs']);
			echo('</td><td class="tab">');
				if ($row['runs'] > 0) $realperc=round(100*$row['runsDone']/$row['runs']); else $realperc=0;
                                percentbar($realperc,"Done ${row['runsDone']} of ${row['runs']}");
				echo('</td><td class="tab">');
				if (empty($row['jobsCompleted'])) $row['jobsCompleted']=0;
				if (empty($row['jobsSuccess'])) $row['jobsSuccess']=0;
				if (($row['activityID']==7) || ($row['activityID']==8)) {
				if ($row['jobsCompleted'] > 0) $realperc=round(100*$row['jobsSuccess']/$row['jobsCompleted']); else $realperc=0;
                                percentbar($realperc,"${row['jobsSuccess']} successful in ${row['jobsCompleted']} attempts");
				}
			echo('</td>');
			
			echo('</tr>');
		}
		echo('</table>');
	}
	return;
}

/**
 * getCurrentJobs() get industry jobs currently in progress
 *
 * @global type $LM_EVEDB - static data dump schema
 * @global type $USERSTABLE - table with usernames
 * @param type $MYTASKS - WHERE characterID IN (xxx, yyy)
 * @param type $SELECTEDCHAR - WHERE characterID=xxx
 * @param type $ORDERBY - ORDER BY typeName
 * @return boolean 
 */
function getCurrentJobs($MYTASKS, $SELECTEDCHAR, $ORDERBY) {
	global $LM_EVEDB, $USERSTABLE;

	$sql="SELECT itp.`typeID`, itp.`typeName`, acm.`characterID`, acm.`name`, aij.`beginProductionTime` ,aij.`endProductionTime`, rac.`activityName`
	FROM apiindustryjobs aij
        JOIN apicorpmembers acm
	ON acm.characterID=aij.installerID
	JOIN $LM_EVEDB.invTypes itp
	ON aij.outputTypeID=itp.typeID
	JOIN $LM_EVEDB.ramActivities rac
	ON aij.activityID=rac.activityID
	WHERE $MYTASKS AND $SELECTEDCHAR
	AND aij.`endProductionTime` > UTC_TIMESTAMP()
        AND aij.`completed` = 0
	$ORDERBY";
	//echo("DEBUG:<hr/> $sql<hr/>");
	return(db_asocquery($sql));
}

/**
 * showCurrentJobs() - show a HTML table with jobs in $jobslist
 * 
 * @param type $jobslist
 * @return type 
 */
function showCurrentJobs($jobslist) {
    global $MOBILE;
	if (!sizeof($jobslist)>0) {
		echo('<h3>There is no jobs in progress!</h3>');
	} else {
	?>
        <em>All times shown in EVE time.</em>
	<table class="lmframework">
	<tr><th>
		
	</th><?php if (!$MOBILE) { ?><th>
		Character
	</th><?php } ?><th>
		Activity
	</th><th>
		
	</th><th>
		Type
	</th><th>
		Start Time
	</th><th>
		End Time
	</th>
	</tr>
	<?php
		foreach($jobslist as $row) {
			echo('<tr><td style="padding: 0px; width: 32px;">');
                            echo("<img src=\"https://image.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
			echo('</td>');
                        if (!$MOBILE) {
                            echo('<td>');
                                echo($row['name']);
                            echo('</td>');
                        }
                        echo('<td>');
                            echo($row['activityName']);    
			echo('</td><td style="padding: 0px; width: 32px;">');
				echo("<img src=\"ccp_img/${row[typeID]}_32.png\" title=\"${row['typeName']}\" />");
			echo('</td><td>');
                                itemhrefedit($row['typeID']);
				echo($row['typeName']);
                                echo("&nbsp;<img src=\"ccp_icons/38_16_208.png\" style=\"vertical-align: middle;\" />");
			echo('</a>');
			echo('</td><td>');
				echo($row['beginProductionTime']);
			echo('</td><td>');
				echo($row['endProductionTime']);
			echo('</td>');
                                			
			echo('</tr>');
		}
		echo('</table>');
                
	}
	return;
}

?>