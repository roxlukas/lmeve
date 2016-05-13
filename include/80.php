<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewActivity")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=8; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Statistics'; //Panel name (optional)
//standard header ends here

include('tasks.php');

$date=secureGETnum("date");
$mychars=secureGETnum("mychars");

global $LM_EVEDB;

$rights_edittasks=checkrights("Administrator,EditTasks");
$rights_viewallchars=checkrights("Administrator,ViewAllCharacters");

if (strlen($date)==6) {
	$year=substr($date,0,4);
	$month=substr($date,4,2);
} else {
	$year=date("Y");
	$month=date("m");	
}

		function hrefedit($nr) {
			global $MENUITEM;
		    echo("<a href=\"index.php?id=9&id2=6&nr=$nr\" title=\"Click to open character information\">");
		}
		
		function althrefedit($nr) {
			global $MENUITEM;
		    echo("<a href=\"index.php?id=10&id2=1&nr=$nr\" title=\"Click to open database\">");
		}
		
		
	    
		?>
		<a name="top"></a>
		    <div class="tytul">
			Industry Statistics for <?php echo("$year-$month"); ?><br>
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
		    <input type="hidden" name="id" value="8">
		    <input type="hidden" name="id2" value="<?php echo($_GET['id2']); ?>">
		    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $year).sprintf("%02d", $month)); ?>">
		    <?php if ($mychars==1) { ?>
				<input type="hidden" name="mychars" value="0">
				<input type="submit" value="All characters" title="Click to show all Characters">
		    <?php } else { ?>
				<input type="hidden" name="mychars" value="1">
				<input type="submit" value="Only my characters" title="Click to filter output by your Characters only">
		    <?php } ?>
			</form>			
			</td></tr></table>
			<?php /*
		    <a href="?id=<?php echo($MENUITEM); ?>&date=<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">&laquo; previous month</a> |
		    <a href="?id=<?php echo($MENUITEM); ?>&date=<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">next month &raquo;</a> | 
		    <a href="?id=<?php echo($MENUITEM); ?>&id2=0&date=<?php echo(sprintf("%04d", $year).sprintf("%02d", $month)); ?>">By item</a> |
		    <a href="?id=<?php echo($MENUITEM); ?>&id2=1&date=<?php echo(sprintf("%04d", $year).sprintf("%02d", $month)); ?>">By character</a><br/>
		    */ ?>
			<a href="#down">Scroll down</a>
		    </div>
		    
		    <?php
		    		    
		    $corps=db_asocquery("SELECT * FROM apicorps;");
		    foreach ($corps as $corp) { //begin corps loop
				echo("<h1><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
				
if ($mychars==1) {
        echo('<em>The list is filtered by your characters only. Click "All characters" button to show entire corp.</em>');
	if($ids=getMyChars($array=false)) {
		$mycharssql="`installerID` IN ($ids)";
	} else {
		$mycharssql="FALSE";
	}
} else {
	$mycharssql="TRUE";
}

$sql="SELECT `typeID` , `typeName` , `name` , `installerID`, rac.`activityID`, rac.`activityName` , SUM(runs)*portionSize AS runCount, COUNT(jobID) AS jobsCount
FROM apiindustryjobs aij
JOIN $LM_EVEDB.ramActivities rac ON aij.`activityID` = rac.`activityID`
JOIN $LM_EVEDB.invTypes inv ON aij.outputTypeID = inv.typeID
JOIN apicorpmembers acm ON aij.installerID = acm.characterID
AND aij.corporationID = ${corp['corporationID']}
AND beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
WHERE $mycharssql
GROUP BY `typeID` , `typeName` , `name` , `installerID`, rac.`activityName`
ORDER BY typeName ASC, name ASC, SUM( runs ) DESC;";

				$data=db_asocquery($sql);
				
			
				$rearrange=array();
				
				foreach($data as $row) {
					$rearrange[$row['typeID']]['characters'][stripslashes($row['name'])]['runCount']=stripslashes($row['runCount']);
					$rearrange[$row['typeID']]['amount']+=stripslashes($row['runCount']);
					$rearrange[$row['typeID']]['characters'][stripslashes($row['name'])]['jobsCount']=stripslashes($row['jobsCount']);
					$rearrange[$row['typeID']]['characters'][stripslashes($row['name'])]['installerID']=stripslashes($row['installerID']);
					$rearrange[$row['typeID']]['characters'][stripslashes($row['name'])]['name']=stripslashes($row['name']);
					$rearrange[$row['typeID']]['typeName']=stripslashes($row['typeName']);
					$rearrange[$row['typeID']]['activityName']=stripslashes($row['activityName']);
					$rearrange[$row['typeID']]['activityID']=stripslashes($row['activityID']);
					$rearrange[$row['typeID']]['typeID']=$row['typeID'];
				}
				
				//var_dump($rearrange);
				
			?>
				<table class="lmframework">
				<tr><th width="32" style="padding: 0px; text-align: center;">
					<b>Icon</b>
				</th><th style="text-align: center;">
					<b><a href="?id=8&id2=0&mychars=<?php if ($mychars==1) echo(1); else echo(0); ?>">Type Name</a></b>
				</th><th style="width: 120px; text-align: center;">
					<b>Activity</b>
				</th><th style="text-align: center;">
					<b>Amount</b>
				</th><th width="<?php
						if ($rights_edittasks) {
							echo('350');
						} else {
							echo('300');
						}
					 ?>" style="text-align: center;">
					<b><a href="?id=8&id2=1&mychars=<?php if ($mychars==1) echo(1); else echo(0); ?>">Contributors</a></b>
				</th>
				</tr>
			 <?php

			foreach($rearrange as $row) {
				echo('<tr><td>');
				althrefedit($row['typeID']);
				echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
				echo('</a>');
				echo('</td><td>');
				althrefedit($row['typeID']);
				echo(stripslashes($row['typeName']));
				echo('</a>');
				echo('</td><td>');
				echo(stripslashes($row['activityName']));
				echo('</td><td style="text-align: right;">');
				echo('<img src="'.getUrl().'ccp_icons/2_64_9.png" title="Total produced items amount" style="float: left; width: 16px; height: 16px;"> ');
				echo(stripslashes($row['amount']));
				echo('</td><td>');
				echo('<table class="lmframework" width="100%">');
				foreach($row['characters'] as $contrib) {
					echo('<tr><td width="32" style="padding: 0px;">');
					echo("<img src=\"https://imageserver.eveonline.com/character/${contrib['installerID']}_32.jpg\" title=\"${contrib['name']}\" />");
					echo('</td><td style="text-align: left;">');
						if ($rights_viewallchars) hrefedit($contrib['installerID']);
							echo(stripslashes($contrib['name']));
						if ($rights_viewallchars) echo('</a>');
					echo('</td><td width="40" style="text-align: left;">');
					echo('<img src="'.getUrl().'ccp_icons/9_64_16.png" title="Industry jobs count" style="float: left; width: 16px; height: 16px;"> ');
					echo(stripslashes($contrib['jobsCount'])); 
					echo('</td><td width="60" style="text-align: left;">');
					echo('<img src="'.getUrl().'ccp_icons/2_64_9.png" title="Produced items amount" style="float: left; width: 16px; height: 16px;"> ');
					echo(stripslashes($contrib['runCount']));
					if ($rights_edittasks) {
						echo('</td><td width="50">');
						/*?>
						<form method="get" action="">
						<input type="hidden" name="id" value="1">
						<input type="hidden" name="id2" value="1">
						<input type="hidden" name="nr" value="new">
						<input type="hidden" name="typeID" value="<?php echo($row['typeID']); ?>">
						<input type="hidden" name="activityID" value="1">
						<input type="hidden" name="characterID" value="<?php echo($contrib['installerID']); ?>">
						<input type="hidden" name="runs" value="<?php echo($contrib['runCount']); ?>">
						<input type="submit" value="Assign task">
						</form>
						<?php*/
						echo('<a href="?id=1&id2=1&nr=new&typeID='.$row[typeID].'&activityID='.$row['activityID'].'&characterID='.$contrib['installerID'].'&runs='.$contrib['runCount'].'" title="Click to assign this task">[Assign]</a>');
					}
					echo('</td></tr>');
				}
				echo('</table>');
				echo('</td>');
				echo('</tr>');
			}
			?>

			</table>
			<?php 
			
	}//end corps loop
		?>
		
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
			
		    </div><br>
		
