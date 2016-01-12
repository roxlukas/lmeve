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
        /**** ORIGINAL TASKS SQL, works fast ****/
        $sql_original="SELECT a.*,b.runsDone,b.jobsDone,c.jobsSuccess,d.jobsCompleted,e.runsCompleted
	FROM (SELECT acm.name, lmt.characterID, itp.typeName, lmt.typeID, rac.activityName, lmt.activityID, lmt.taskID, lmt.runs
	FROM lmtasks lmt
	JOIN apicorpmembers acm
	ON acm.characterID=lmt.characterID
	JOIN $LM_EVEDB.invTypes itp
	ON lmt.typeID=itp.typeID
	JOIN $LM_EVEDB.ramActivities rac
	ON lmt.activityID=rac.activityID
	WHERE $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND lmt.taskCreateTimestamp BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)) OR (singleton=0))
	) AS a
	LEFT JOIN	
	(SELECT lmt.taskID, SUM(aij.runs)*itp.portionSize AS runsDone, COUNT(*) AS jobsDone
	FROM lmtasks lmt
	JOIN $LM_EVEDB.invTypes itp
	ON lmt.typeID=itp.typeID
	JOIN apiindustryjobs aij
	ON lmt.typeID=aij.outputTypeID AND lmt.activityID=aij.activityID AND lmt.characterID=aij.installerID
	WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
	AND $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND lmt.taskCreateTimestamp BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)) OR (singleton=0))
	GROUP BY lmt.characterID, lmt.typeID, lmt.activityID, lmt.taskID
	) AS b
	ON a.taskID=b.taskID
	LEFT JOIN	
	(SELECT lmt.taskID, SUM(successfulRuns) AS jobsSuccess
	FROM lmtasks lmt
	JOIN apiindustryjobs aij
	ON lmt.typeID=aij.outputTypeID AND lmt.activityID=aij.activityID AND lmt.characterID=aij.installerID
	WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
	AND $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND lmt.taskCreateTimestamp BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)) OR (singleton=0))
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
	WHERE aij.completed=1 AND beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
	AND $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)) OR (singleton=0))
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
	WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day) AND aij.endProductionTime < UTC_TIMESTAMP()
	AND $MYTASKS AND $SELECTEDCHAR
	AND ((singleton=1 AND lmt.taskCreateTimestamp BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)) OR (singleton=0))
	GROUP BY lmt.characterID, lmt.typeID, lmt.activityID, lmt.taskID
	) AS e
	ON a.taskID=e.taskID
	$ORDERBY";

        /**** NEW TASKS SQL - way slower ****/
        /* runs - number of individual items to build/invent in a task
         * runsDone - how many individual items started (ammo, ships, mods)
         * jobsDone - how many industry jobs started
         * jobsSuccess - successful invention jobs
         * jobsCompleted - how many industry jobs actually completed 
         * runsCompleted - how many individual items actually completed */
        $howOldSingletons=getConfigItem('singletonTaskExpiration','90');
        $thisMonth="BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)";
        $singletonOrNot="((singleton=0 AND beginProductionTime $thisMonth) OR (singleton=1 AND taskCreateTimestamp > DATE_SUB(UTC_TIMESTAMP(), INTERVAL $howOldSingletons day) AND beginProductionTime > taskCreateTimestamp))";
        //$singletonOrNot="beginProductionTime $thisMonth";
        
	$sql="SELECT a.*,COALESCE(b.runsDone,0) AS runsDone,
	COALESCE(b.jobsDone,0) AS jobsDone,
	COALESCE(c.jobsSuccess,0) AS jobsSuccess,
	COALESCE(d.jobsCompleted,0) AS jobsCompleted,
	COALESCE(e.runsCompleted,0) AS runsCompleted
	FROM (SELECT acm.name, lmt.characterID, itp.typeName, lmt.typeID, rac.activityName, lmt.activityID, lmt.taskID, lmt.runs, lmt.taskCreateTimestamp, lmt.singleton
	FROM lmtasks lmt
	JOIN apicorpmembers acm
	ON acm.characterID=lmt.characterID
	JOIN $LM_EVEDB.invTypes itp
	ON lmt.typeID=itp.typeID
	JOIN $LM_EVEDB.ramActivities rac
	ON lmt.activityID=rac.activityID
	WHERE $MYTASKS AND $SELECTEDCHAR
	) AS a
	LEFT JOIN	
	(SELECT lmt.taskID, SUM(aij.runs)*itp.portionSize AS runsDone, COUNT(*) AS jobsDone
	FROM lmtasks lmt
	JOIN $LM_EVEDB.invTypes itp
	ON lmt.typeID=itp.typeID
	JOIN apiindustryjobs aij
	ON lmt.typeID=aij.outputTypeID AND lmt.activityID=aij.activityID AND lmt.characterID=aij.installerID
	WHERE $singletonOrNot
	AND $MYTASKS AND $SELECTEDCHAR
	GROUP BY lmt.characterID, lmt.typeID, lmt.activityID, lmt.taskID
	) AS b
	ON a.taskID=b.taskID
	LEFT JOIN	
	(SELECT lmt.taskID, SUM(successfulRuns) AS jobsSuccess
	FROM lmtasks lmt
	JOIN apiindustryjobs aij
	ON lmt.typeID=aij.outputTypeID AND lmt.activityID=aij.activityID AND lmt.characterID=aij.installerID
	WHERE $singletonOrNot
	AND $MYTASKS AND $SELECTEDCHAR
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
	WHERE aij.completed=1 AND $singletonOrNot
	AND $MYTASKS AND $SELECTEDCHAR
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
	WHERE $singletonOrNot AND aij.endProductionTime < UTC_TIMESTAMP()
	AND $MYTASKS AND $SELECTEDCHAR
	GROUP BY lmt.characterID, lmt.typeID, lmt.activityID, lmt.taskID
	) AS e
	ON a.taskID=e.taskID
        WHERE ((a.singleton=1 AND a.taskCreateTimestamp > DATE_SUB(UTC_TIMESTAMP(), INTERVAL $howOldSingletons day)) OR (a.singleton=0))
	$ORDERBY"; 
	//echo("NEW QUERY DEBUG:<hr/> $sql<hr/>");
    //echo("OLD QUERY DEBUG:<hr/> $sql_original<hr/>");
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
    if (empty($lista)) return FALSE;
    $sql_del="DELETE FROM `lmtasks` WHERE `taskID` IN ($lista);";
    $ret=db_uquery($sql_del);
    if ($ret!==FALSE) {
        return(TRUE);
    } else {
        return FALSE;
    }
}

function getExpiredSingletonTasksCount() {
    $singletonTaskExpiration=getConfigItem('singletonTaskExpiration','90');
    $sql_sel="SELECT * FROM `lmtasks` WHERE singleton=1 AND taskCreateTimestamp < DATE_SUB(UTC_TIMESTAMP(), INTERVAL $singletonTaskExpiration day);";
    $ile=db_count($sql_sel);
    return($ile);
}

function clearExpiredSingletonTasks() {
    $singletonTaskExpiration=getConfigItem('singletonTaskExpiration','90');
    $sql_del="DELETE FROM `lmtasks` WHERE singleton=1 AND taskCreateTimestamp < DATE_SUB(UTC_TIMESTAMP(), INTERVAL $singletonTaskExpiration day);";
    $ret=db_uquery($sql_del);
    if ($ret!==FALSE) {
        return(TRUE);
    } else {
        return FALSE;
    }
}

function getTasksByLab($nr) {
    $year=date("Y"); $month=date("m");
    $tasks=db_asocquery("SELECT * FROM `lmtasks` WHERE `structureID`=$nr
    AND ((`singleton`=1 AND `taskCreateTimestamp` BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)) OR (`singleton`=0));");
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
		echo('<h3>There are no tasks assigned!</h3>');
	} else {
            echo("Found ".count($tasklist)." tasks.");
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
                    //dirty hack to hide completed one time tasks
                    if ($row['singleton']==1 && $row['runsDone'] >= $row['runs']) continue;
                    //end dirty hack
                    
			echo('<tr><td style="padding: 0px; width: 32px;">');
                        echo("<a name=\"kit_anchor_${row['taskID']}\"></a>");
			taskhrefedit($row['characterID'],$year.$month);
				echo("<img src=\"https://imageserver.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
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
                        if ($row['singleton']==1) echo("&nbsp;<img src=\"ccp_icons/38_16_238.png\" style=\"vertical-align: middle;\" alt=\"Non-recurring task\" title=\"Non-recurring task\" />");
                        echo('</td>');
                    }
                        echo('<td style="padding: 0px; width: 32px;">');
			itemhrefedit($row['typeID']);
				echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
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
                                    echo($percent1.'%');
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
		echo('<h3>There are no tasks assigned!</h3>');
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
				echo("<img src=\"https://imageserver.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
			echo('</a>');
			echo('</td><td class="tab">');
			taskhrefedit($row['characterID'],$year.$month);
				echo($row['name']);
			echo('</a>');
			echo('</td><td class="tab" style="padding: 0px; width: 32px;">');
			if ($rights) edittaskhrefedit($row['taskID']);
				echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
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
		echo('<h3>There are no jobs in progress!</h3>');
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
                            echo("<img src=\"https://imageserver.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
			echo('</td>');
                        if (!$MOBILE) {
                            echo('<td>');
                                echo($row['name']);
                            echo('</td>');
                        }
                        echo('<td>');
                            echo($row['activityName']);    
			echo('</td><td style="padding: 0px; width: 32px;">');
				echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
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

function getPoints() {
    global $LM_EVEDB;
    $points=db_asocquery("SELECT rac.`activityName`,cpt.* FROM $LM_EVEDB.`ramActivities` rac JOIN `cfgpoints` cpt ON rac.`activityID`=cpt.`activityID` ORDER BY `activityName`;");
    return $points;
}

function showPoints($points) {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    $rights_edithours=FALSE;
    $ONEPOINT=getConfigItem('iskPerPoint','15000000'); //loaded from db now! :-)
    
    echo("<h2>Points");
    if (checkrights("Administrator,EditHoursPerPoint")) { ?>
        <input type="button" value="Edit hours-per-point" onclick="location.href='?id=5&id2=10';">
    <?php 
        $rights_edithours=TRUE;
    }
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
}

function getTimesheet($corporationID, $year, $month, $aggregate=FALSE) {
    global $LM_EVEDB;
    $ONEPOINT=getConfigItem('iskPerPoint','15000000'); //loaded from db now! :-)
    
    if (!$aggregate) {
    $sql_all="SELECT *,ROUND((points*$ONEPOINT),2) as wage FROM (
	SELECT `characterID`,`name`,`activityName`,SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600)/hrsPerPoint AS points
	FROM `apiindustryjobs` aij
	JOIN $LM_EVEDB.`ramActivities` rac
	ON aij.activityID=rac.activityID
	JOIN cfgpoints cpt
	ON aij.activityID=cpt.activityID
	JOIN apicorpmembers acm
	ON aij.installerID=acm.characterID
	WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
	AND aij.corporationID=$corporationID
	GROUP BY `characterID`,`name`,`activityName`
	ORDER BY `name`,`activityName`) AS wages;";
    } else {
        $sql_all="SELECT *,ROUND((points*$ONEPOINT),2) as wage FROM (
	SELECT lmc.`userID` AS `characterID`,lmu.`login` AS `name`,`activityName`,SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600)/hrsPerPoint AS points
	FROM `apiindustryjobs` aij
	JOIN $LM_EVEDB.`ramActivities` rac
	ON aij.`activityID`=rac.`activityID`
	JOIN cfgpoints cpt
	ON aij.`activityID`=cpt.`activityID`
	JOIN apicorpmembers acm
	ON aij.`installerID`=acm.`characterID`
        JOIN lmchars lmc
        ON aij.`installerID`=lmc.`charID`
        JOIN lmusers lmu
        ON lmc.`userID`=lmu.`userID`
	WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
	AND aij.corporationID=$corporationID
	GROUP BY lmu.`userID`,lmu.`login`,`activityName`
	ORDER BY lmu.`login`,`activityName`) AS wages;";
    }
    //echo("<h3>DEBUG</h3><pre>$sql_all</pre>");	
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
    return $rearrange;
}

function timesheetHeader() {
    global $MOBILE;
    
    ?>			
    <table class="lmframework">
    <tr><th width="32" style="padding: 0px; text-align: center;">
            <b></b>
    </th><th style="text-align: center;">
            <b>Name</b>
    </th><?php if (!$MOBILE) { ?><th width="64" style="text-align: center;">
            <b>Copying</b>
    </td><th width="64" style="text-align: center;">
            <b>Invention</b>
    </th><th width="64" style="text-align: center;">
            <b>Manufacturing</b>
    </th><th width="64" style="text-align: center;">
            <b>ME</b>
    </th><th width="64" style="text-align: center;">
            <b>PE</b>
    </th><?php 
    /* <th width="64" style="text-align: center;">
            <b>Reverse engineering</b>
    </th> */
    }  ?><th width="48" style="text-align: center;">
            <b>Points</b>
    </th><th width="96" style="text-align: center;">
            <b>ISK</b>
    </td>
    </tr>
    <?php
}

function timesheetFooter() {
    ?>
    </table>
    <?php  
}

function timesheetRow ($row,& $totals,$rights_viewallchars=FALSE,$aggregate=FALSE) {
    global $DECIMAL_SEP, $THOUSAND_SEP, $MOBILE;
    
    echo('<tr><td style="padding: 0px;">');
        if (!$aggregate) {
            if ($rights_viewallchars) charhrefedit($row['characterID']);
                    echo("<img src=\"https://imageserver.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['name']}\" />");
            if ($rights_viewallchars) echo('</a>');
        } else {
                    echo("<img src=\"https://imageserver.eveonline.com/character/0_32.jpg\" title=\"${row['name']}\" />");
        }
    echo('</td><td>');
            if ($rights_viewallchars && !$aggregate) charhrefedit($row['characterID']);
                    echo(stripslashes($row['name']));
            if ($rights_viewallchars && !$aggregate) echo('</a>');
    echo('</td><td style="text-align: center;">');
    if (!$MOBILE) {
        echo(number_format($row['activities']['Copying']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
        $totals['Copying']+=$row['activities']['Copying']['points'];
        echo('</td><td style="text-align: center;">');

        echo(number_format($row['activities']['Invention']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
        $totals['Invention']+=$row['activities']['Invention']['points'];
        echo('</td><td style="text-align: center;">');

        echo(number_format($row['activities']['Manufacturing']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
        $totals['Manufacturing']+=$row['activities']['Manufacturing']['points'];
        echo('</td><td style="text-align: center;">');

        echo(number_format($row['activities']['Researching Material Efficiency']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
        $totals['Researching Material Efficiency']+=$row['activities']['Researching Material Efficiency']['points'];
        echo('</td><td style="text-align: center;">');

        echo(number_format($row['activities']['Researching Time Efficiency']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
        $totals['Researching Time Efficiency']+=$row['activities']['Researching Time Efficiency']['points'];
        echo('</td><td style="text-align: center;">');
        /*
        echo(number_format($row['activities']['Reverse Engineering']['points'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
        $totals['Reverse Engineering']+=$row['activities']['Reverse Engineering']['points'];
        echo('</td><td style="text-align: center;">');
         */
    }
    echo(number_format(stripslashes($row['totalpoints']), 2, $DECIMAL_SEP, $THOUSAND_SEP));
    $totals['totalpoints']+=$row['totalpoints'];
    echo('</td><td style="text-align: right;">');

    echo(number_format(stripslashes($row['wage']), 2, $DECIMAL_SEP, $THOUSAND_SEP));
    echo('</td>');
    echo('</tr>');
    $totals['ISK']+=stripslashes($row['wage']);
}

function timesheetTotals($totals,$label='Total') {
    global $DECIMAL_SEP, $THOUSAND_SEP, $MOBILE;
    
    ?>
    <tr><th width="32" style="padding: 0px; text-align: center;">
            <b></b>
    </th><th style="text-align: left;">
            <b><?php echo($label); ?></b>
    </th><?php if (!$MOBILE) { ?><th width="64" style="text-align: center;">
            <b><?php echo(number_format($totals['Copying'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
    </th><th width="64" style="text-align: center;">
            <b><?php echo(number_format($totals['Invention'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
    </th><th width="64" style="text-align: center;">
            <b><?php echo(number_format($totals['Manufacturing'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
    </th><th width="64" style="text-align: center;">
            <b><?php echo(number_format($totals['Researching Material Efficiency'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
    </th><th width="64" style="text-align: center;">
            <b><?php echo(number_format($totals['Researching Time Efficiency'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
    </th><?php /* <th width="64" style="text-align: center;">
            <b><?php echo(number_format($totals['Reverse Engineering'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
    </th> */ } ?><th width="48" style="text-align: center;">
            <b><?php echo(number_format($totals['totalpoints'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
    </th><th width="96" style="text-align: right;">
            <b><?php echo(number_format($totals['ISK'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?></b>
    </th>
    </tr>
    <?php
}
    
function showTimesheet($timesheet,$aggregate=FALSE) {
    $rights_viewallchars=checkrights("Administrator,ViewAllCharacters");
    
    $mychars=getMyChars(true);   

    $totals['ISK']=0.0;
    $totals['Copying']=0.0;
    $totals['Invention']=0.0;
    $totals['Manufacturing']=0.0;
    $totals['Researching Material Efficiency']=0.0;
    $totals['Researching Time Efficiency']=0.0;
    //$totals['Reverse Engineering']=0.0;
    $totals['totalpoints']=0.0;
    
    //draw table header
    timesheetHeader();
    //draw "My characters" header
    if ($mychars) {
        echo('<tr><th colspan="'. ($MOBILE ? 4 : 9) .'" style="text-align: center; font-weight: bold;">My characters</th></tr>');
    }
    //display data for "My characters"
    foreach($timesheet as $row) {
        if (!$aggregate) {
            if ($mychars!=false && in_array($row['characterID'], $mychars)) {
                timesheetRow($row,$totals,$rights_viewallchars,$aggregate);
            }
        } else {
            if ($row['characterID']==$_SESSION['granted']) {
                timesheetRow($row,$totals,$rights_viewallchars,$aggregate);
            }
        }
    }
    //display subtotal for "My Characters"
    if ($mychars) {
        timesheetTotals($totals,"My Total");
    }
    //display data for everyone else
    foreach($timesheet as $row) {
        if (!$aggregate) {
            if ($mychars==false || !in_array($row['characterID'], $mychars)) {
                timesheetRow($row,$totals,$rights_viewallchars,$aggregate);
            }
        } else {
            if ($row['characterID']!=$_SESSION['granted']) {
                timesheetRow($row,$totals,$rights_viewallchars,$aggregate);
            }
        }
    }
    //display total for everyone
    timesheetTotals($totals);
    //close table
    timesheetFooter();  
}

function charhrefedit($nr) {
    echo("<a href=\"index.php?id=9&id2=6&nr=$nr\" title=\"Click to open character information\">");
}

?>
