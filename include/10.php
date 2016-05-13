<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOwnTasks,ViewAllTasks,EditTasks")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=1; //Panel ID in menu. Used in hyperlinks
$PANELNAME='My Tasks'; //Panel name (optional)
//standard header ends here

include("tasks.php");

global $USERSTABLE,$LM_EVEDB,$MOBILE;

$date=secureGETnum("date");
$nr=secureGETnum("nr");

	if (strlen($date)==6) {
		$year=substr($date,0,4);
		$month=substr($date,4,2);
	} else {
		$year=date("Y");
		$month=date("m");	
	}
	
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

?>	    <div class="tytul">
		<?php echo("$PANELNAME $year-$month"); ?><br>
	    </div>
	    <?php
			if (!empty($nr)) {
				$name=db_query("SELECT `name` FROM `apicorpmembers` WHERE `characterID`=$nr;");
				$name=$name[0][0];
				echo("<h2>Tasks for: $name</h2>");
			}
		?>
	    <table cellpadding="0" cellspacing="2">
	    <tr>
	    <?php if (!empty($nr)) { ?>
	    <td><form action="" method="get">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="id2" value="0">
		<input type="hidden" name="date" value="<?php echo($year.$month); ?>">
		<input type="submit" value="Back to task list">
		</form></td>
		<?php } ?>	
	    <td>
		<form method="get" action="">
	    <input type="hidden" name="id" value="1">
	    <input type="hidden" name="id2" value="0">
	    <input type="hidden" name="nr" value="<?php echo($nr); ?>">
	    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">
	    <input type="submit" value="&laquo; previous month">
		</form>
		</td><td>
		<form method="get" action="">
	    <input type="hidden" name="id" value="1">
	    <input type="hidden" name="id2" value="0">
	    <input type="hidden" name="nr" value="<?php echo($nr); ?>">
	    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">
	    <input type="submit" value="next month &raquo;">
		</form>			
		</td>
                <td width="10" id="separator"></td>
                <?php if ($MOBILE) echo('</tr><tr>');  ?>
		<?php if (checkrights("Administrator,ViewAllTasks")) { ?>
	    <td><form action="" method="get">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="id2" value="3">
		<input type="hidden" name="date" value="<?php echo($year.$month); ?>">
		<input type="submit" value="View All Tasks">
		</form></td>
		<?php } ?>	
	    <?php if (checkrights("Administrator,EditTasks")) { ?>
	    <td><form action="" method="get">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="id2" value="1">
		<input type="hidden" name="nr" value="new">
		<input type="submit" value="Create Task">
		</form></td>
		<?php } ?>
            <?php if (checkrights("Administrator,EditTasks")) { ?>
	    <td><form action="" method="get">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="id2" value="6">
		<input type="submit" value="Clear Orphan Tasks">
		</form></td>
		<?php } ?>
            <?php if (checkrights("Administrator,EditTasks")) { ?>
	    <td><form action="" method="get">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="id2" value="7">
		<input type="submit" value="Clear Expired Tasks">
		</form></td>
		<?php } ?>
		</tr></table>
	    <em><img src="<?=getUrl()?>ccp_icons/38_16_208.png" alt="(i)"/> Tasks are only contributed to by the characters they have been assigned to. If you'd like to use an alt for production, ask administrator to assign that task to your alt.<br/></em>
            <br/>
	<?php

	
		
	if (!empty($nr)) {
		$SELECTEDCHAR="lmt.characterID=$nr";
		if (checkrights("Administrator,ViewAllTasks")) {
			$MYTASKS='TRUE';
		} else {
                        if ($chars=getMyChars()) {
                            $MYTASKS="lmt.`characterID` IN ($chars)";
                        } else {
                            $MYTASKS='FALSE';
                        }
		}
	} else {
		$SELECTEDCHAR='TRUE';
		if ($chars=getMyChars()) {
                     $MYTASKS="lmt.`characterID` IN ($chars)";
                } else {
                     $MYTASKS='FALSE';
                }
	}
	
	$ORDERBY_tasks="ORDER BY  a.name, a.typeName, a.activityName";
        $ORDERBY_jobs="ORDER BY  acm.name, aij.endProductionTime";
	
	$tasklist=getTasks($MYTASKS, $SELECTEDCHAR, $ORDERBY_tasks, $year, $month);
 	showTasks($tasklist);			
        
        if (checkrights("Administrator,ViewCurrentJobs")) {
             $jobslist=getCurrentJobs(str_replace('lmt.','acm.',$MYTASKS), str_replace('lmt.','acm.',$SELECTEDCHAR), $ORDERBY_jobs);
             echo('<h2>Currently in progress:</h2>');
             showCurrentJobs($jobslist);
        }
	
	?>
