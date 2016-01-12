<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewDatabase")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=10; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Item Database'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

$query=secureGETstr('query',256);
$marketGroupID=secureGETnum('marketGroupID');

?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	    <?php echo("<em>Static Data schema: $LM_EVEDB</em><br />"); ?>
	<?php
		if (!empty($query)) {
				/*$items=db_asocquery("SELECT itp.`typeID`, itp.`typeName`
				FROM $LM_EVEDB.`invTypes` itp			
				WHERE `typeName` LIKE '%$query%'
				AND published = 1
				ORDER BY `typeName`;");*/
                                $items=db_asocquery("SELECT itp.`typeID`, itp.`typeName`, itp.`published`
				FROM $LM_EVEDB.`invTypes` itp			
				WHERE `typeName` LIKE '%$query%'
				ORDER BY `typeName`;");
		}
		
		if (!empty($marketGroupID)) {
			$whereparentgroup="parentGroupID=$marketGroupID";
		} else {
			$whereparentgroup="parentGroupID IS NULL";
		}
		
		$groups=db_asocquery("SELECT * FROM $LM_EVEDB.`invMarketGroups` WHERE $whereparentgroup ;");

		?>
	    <table cellpadding="0" cellspacing="2">
	    <tr>
	    
	    <td>
		<form method="get" action="">
		<input type="text" name="query" value="<?php echo(stripslashes($query)); ?>" size="20">
		<input type="hidden" name="id" value="10">
		<input type="hidden" name="id2" value="2">
	    <input type="submit" value="Search">
		</form>
		</td>
		
		</tr></table>
	    
	<?php

	
	function hrefedit_item($nr) {
		echo("<a href=\"index.php?id=10&id2=1&nr=$nr\">");
	}
	
	if (!sizeof($items)>0) {
		echo('<h3>No types found</h3>');
	} else {
			?>
			<table cellspacing="2" cellpadding="0">
			<tr><td class="tab-header">
				<b>Icon</b>
			</td><td class="tab-header">
				<b>Name</b>
			</td>
			</tr>
			<?php
				foreach($items as $row) {
					echo('<tr><td class="tab" style="padding: 0px; width: 32px;">');
						hrefedit_item($row['typeID']);
						echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
						echo('</a>');
					echo('</td><td class="tab">');
						hrefedit_item($row['typeID']);
                                                if ($row['published']!=1) echo('<em>');
						echo($row['typeName']);
                                                if ($row['published']!=1) echo('</em>');
						echo('</a>');
					echo('</td>');
					echo('</tr>');
				}
				echo('</table>');
	}
	//echo('<form method="get" action=""><input type="hidden" name="id" value="1"><input type="hidden" name="id2" value="1"><input type="hidden" name="nr" value="'.$nr.'"><input type="submit" value="Cancel"></form>');
	
	?>
