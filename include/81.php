<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewActivity")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=8; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Overview'; //Panel name (optional)
//standard header ends here

include('tasks.php');
	
global $LM_EVEDB;

$rights_edittasks=checkrights("Administrator,EditTasks");
$rights_viewallchars=checkrights("Administrator,ViewAllCharacters");
	
$date=secureGETnum("date");
$mychars=secureGETnum("mychars");

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
			</td>
			
			</tr></table>
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
				
			
				$sql="SELECT `typeID` , `typeName` , `name` , `installerID`, rac.activityName, rac.activityID, SUM(runs)*portionSize AS runCount, COUNT(jobID) AS jobsCount
FROM apiindustryjobs aij
JOIN $LM_EVEDB.ramActivities rac ON aij.`activityID` = rac.`activityID`
JOIN $LM_EVEDB.invTypes inv ON aij.outputTypeID = inv.typeID
JOIN apicorpmembers acm ON aij.installerID = acm.characterID
WHERE rac.activityID IS NOT NULL
AND aij.corporationID = ${corp['corporationID']}
AND beginProductionTime BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
AND $mycharssql
GROUP BY `typeID` , `typeName` , `name` , `installerID`, rac.activityName
ORDER BY name ASC, typeName ASC, SUM( runs ) DESC;";

				$data=db_asocquery($sql);
				
				$rearrange=array();
				
				foreach($data as $row) {
					$rearrange[$row['installerID']]['items'][stripslashes($row['typeID'])]['jobsCount']=stripslashes($row['jobsCount']);
					$rearrange[$row['installerID']]['items'][stripslashes($row['typeID'])]['runCount']=stripslashes($row['runCount']);
					$rearrange[$row['installerID']]['items'][stripslashes($row['typeID'])]['typeName']=stripslashes($row['typeName']);
					$rearrange[$row['installerID']]['items'][stripslashes($row['typeID'])]['typeID']=stripslashes($row['typeID']);
					$rearrange[$row['installerID']]['items'][stripslashes($row['typeID'])]['activityName']=stripslashes($row['activityName']);
					$rearrange[$row['installerID']]['items'][stripslashes($row['typeID'])]['activityID']=stripslashes($row['activityID']);
					$rearrange[$row['installerID']]['name']=stripslashes($row['name']);
					$rearrange[$row['installerID']]['installerID']=$row['installerID'];
				}
				
				//var_dump($rearrange);
				
				?>
				
				<table class="lmframework">
				<tr><th width="32" style="padding: 0px; text-align: center;">
					
				</th><th width="120" style="text-align: center;">
					<a href="?id=8&id2=1&mychars=<?php if ($mychars==1) echo(1); else echo(0); ?>">Name</a>
				</th><th style="text-align: center;">
					<a href="?id=8&id2=0&mychars=<?php if ($mychars==1) echo(1); else echo(0); ?>">Item Types</a>
				</th>
				</tr>
			   <?php

			foreach($rearrange as $row) {
				echo('<tr><td>');
					if ($rights_viewallchars) hrefedit($row['installerID']);
						echo("<img src=\"https://imageserver.eveonline.com/character/${row['installerID']}_32.jpg\" title=\"${row['name']}\" />");
					if ($rights_viewallchars) echo('</a>');
				echo('</td><td>');
					if ($rights_viewallchars) hrefedit($row['installerID']);
						echo(stripslashes($row['name']));
					if ($rights_viewallchars) echo('</a>');	
				echo('</td><td>');
				foreach($row['items'] as $items) {
					if ($rights_edittasks) {
						echo('<a href="?id=1&id2=1&nr=new&typeID='.$items['typeID'].'&activityID='.$items['activityID'].'&characterID='.$row['installerID'].'&runs='.$items['runCount'].'">');
						$CLICK="\n&raquo; Click to assign this task &laquo;";
					}
					echo("<img src=\"".getTypeIDicon($items['typeID'])."\" title=\"${items['typeName']}\n${items['activityName']}\nJobs: ${items['jobsCount']}\nAmount: ${items['runCount']}${CLICK}\" />");
					if ($rights_edittasks) echo('</a>');
				}
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
		
