<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewAllTasks,EditTasks")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=1; //Panel ID in menu. Used in hyperlinks
$PANELNAME='All Tasks'; //Panel name (optional)
//standard header ends here

include("tasks.php");

global $USERSTABLE;

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
	    <input type="hidden" name="id2" value="3">
	    <input type="hidden" name="nr" value="<?php echo($nr); ?>">
	    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">
	    <input type="submit" value="&laquo; previous month">
		</form>
		</td><td>
		<form method="get" action="">
	    <input type="hidden" name="id" value="1">
	    <input type="hidden" name="id2" value="3">
	    <input type="hidden" name="nr" value="<?php echo($nr); ?>">
	    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">
	    <input type="submit" value="next month &raquo;">
		</form>			
		</td>
	    <td width="10" id="separator"></td><td><form action="" method="get">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="id2" value="0">
		<input type="hidden" name="date" value="<?php echo($year.$month); ?>">
		<input type="submit" value="View Own Tasks">
		</form></td>
	    <?php if (checkrights("Administrator,EditTasks")) { ?>
	    <td><form action="" method="get">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="id2" value="1">
		<input type="hidden" name="nr" value="new">
		<input type="submit" class="yellow" value="Create Task">
		</form></td>
		<?php } ?>
		</tr></table>
	    
	<?php
		
	$MYTASKS='TRUE';
	
	if (!empty($nr)) {
		$SELECTEDCHAR="lmt.characterID=$nr";
	} else {
		$SELECTEDCHAR='TRUE';
	}
	
	$ORDERBY="ORDER BY a.typeName, a.name, a.activityName";
	
	$tasklist=getTasks($MYTASKS, $SELECTEDCHAR, $ORDERBY, $year, $month);
		
	showTasks($tasklist);
	
	?>
