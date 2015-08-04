<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditTasks")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=1; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Search for item type'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

$query=secureGETstr('query',256);
$new=FALSE;
$nr=$_GET['nr'];
if (!ctype_digit($nr)) {
	if ($nr=='new') {
		$new=TRUE;					
	} else {
		echo("Wrong parameter nr");
		return;
	}
}

function althrefedit($nr) {
	global $MENUITEM;
    echo("<a href=\"index.php?id=10&id2=1&nr=$nr\" title=\"Click to open database\">");
}

?>	   
 <div class="tytul">
		<?php echo("$PANELNAME"); ?><br>
	    </div>
	    <?php
			if (!empty($query)) {
				$items=db_asocquery("SELECT itp.`typeID`, itp.`typeName`, ibt.`blueprintTypeID`, iit.`productTypeID`, ibt.`techLevel` AS bpoTechLevel, iit.`techLevel` AS itemTechLevel
				FROM $LM_EVEDB.`invTypes` itp
				LEFT JOIN $LM_EVEDB.`invBlueprintTypes` ibt
				ON itp.typeID=ibt.blueprintTypeID
				LEFT JOIN $LM_EVEDB.`invBlueprintTypes` iit
				ON itp.typeID=iit.productTypeID
				WHERE `typeName` LIKE '%$query%'
				AND ( (ibt.`blueprintTypeID` IS NOT NULL) OR (iit.`productTypeID` IS NOT NULL) )
				AND itp.`published`=1
				LIMIT 30;");
			}
		?>
	    <table cellpadding="0" cellspacing="2">
	    <tr>
	    
	    <td>
		<form method="get" action="">
		<input type="text" name="query" value="<?php echo(stripslashes($query)); ?>" size="20">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="id2" value="5">
		<input type="hidden" name="nr" value="<?php echo(stripslashes($nr)); ?>">
	    <input type="submit" value="Search">
		</form>
		</td>
		
		</tr></table>
	    
	<?php

	
	/*function hrefedit($nr,$date) {
		global $MENUITEM,$year,$month;
		echo("<a href=\"index.php?id=1&id2=0&date=$date&nr=$nr\">");
	}*/
	
	if (!sizeof($items)>0) {
		echo('<h3>No types found</h3>');
	} else {
			?>
			<table cellspacing="2" cellpadding="0">
			<tr><td class="tab-header">
				<b>Icon</b>
			</td><td class="tab-header">
				<b>Name</b>
			</td><td class="tab-header">
				<b>Actions</b>
			</td>
			</tr>
			<?php
				//var_dump($tasklist);
				foreach($items as $row) {
					echo('<tr><td class="tab" style="padding: 0px; width: 32px;">');
						althrefedit($row[typeID]);
						echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
						echo('</a>');
					echo('</td><td class="tab">');
						althrefedit($row[typeID]);
						echo($row['typeName']);
						echo('</a>');
					echo('</td><td class="tab">');
						if (!empty($row['productTypeID'])) {
							echo('<input type="button" value="Manufacturing" onclick="location.href=\'?id=1&id2=1&nr='.$nr.'&typeID='.$row[typeID].'&activityID=1\';">');
						}
						if (!empty($row['blueprintTypeID'])) {
							if ($row['bpoTechLevel']==2 || $row['bpoTechLevel']==3) echo('<input type="button" value="Invention" onclick="location.href=\'?id=1&id2=1&nr='.$nr.'&typeID='.$row[typeID].'&activityID=8\';">');
							echo('<input type="button" value="Copying" onclick="location.href=\'?id=1&id2=1&nr='.$nr.'&typeID='.$row[typeID].'&activityID=5\';">');
							echo('<input type="button" value="ME" onclick="location.href=\'?id=1&id2=1&nr='.$nr.'&typeID='.$row[typeID].'&activityID=4\';">');
							echo('<input type="button" value="PE" onclick="location.href=\'?id=1&id2=1&nr='.$nr.'&typeID='.$row[typeID].'&activityID=3\';">');
						}
						
						
					echo('</td>');
					echo('</tr>');
				}
				echo('</table>');
	}
	echo('<form method="get" action=""><input type="hidden" name="id" value="1"><input type="hidden" name="id2" value="1"><input type="hidden" name="nr" value="'.$nr.'"><input type="submit" value="Cancel"></form>');
	
	?>

