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

$marketGroupID=secureGETnum('marketGroupID');

if (!empty($marketGroupID)) {
	$wheremarket="=$marketGroupID";
} else {
	$wheremarket="IS NULL";
}
?>
	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	    <?php echo("<em><img src=\"ccp_icons/38_16_208.png\" alt=\"(i)\"/> Static Data schema: $LM_EVEDB</em><br />"); ?>
	<?php
		if (!empty($marketGroupID)) {
				$items=db_asocquery("SELECT itp.`typeID`, itp.`typeName`
				FROM $LM_EVEDB.`invTypes` itp			
				WHERE `marketGroupID` $wheremarket
				AND published = 1
				LIMIT 50;");
		}
		
		$groups=db_asocquery("SELECT * FROM $LM_EVEDB.`invMarketGroups` WHERE `parentGroupID` $wheremarket ;");

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
	
	function hrefedit_group($nr) {
		echo("<a href=\"index.php?id=10&id2=0&marketGroupID=$nr\">");
	}
	
	function getMarketNode($marketGroupID) {
		global $LM_EVEDB;
		if (empty($marketGroupID)) return;
		$data=db_asocquery("SELECT * FROM $LM_EVEDB.`invMarketGroups` WHERE `marketGroupID` = $marketGroupID ;");
		if (sizeof($data)==1) return($data[0]); else return;
	}
	
	if (!empty($marketGroupID)) {
		$node=getMarketNode($marketGroupID);
		$parentGroupID=$node['parentGroupID'];
		do {
			$breadcrumbs="&gt; <a href=\"?id=10&id2=0&marketGroupID=${node['marketGroupID']}\">${node['marketGroupName']}</a> $breadcrumbs";
			if (!empty($node['parentGroupID'])) {
				$node=getMarketNode($node['parentGroupID']);
			} else {
				break;
			}
			
		} while(TRUE);
		echo("<a href=\"?id=10&id2=0\"> Start </a> $breadcrumbs");
	}

	?>
			<table cellspacing="2" cellpadding="0">
			<tr><td class="tab-header">
				<b>Icon</b>
			</td><td class="tab-header">
				<b>Name</b>
			</td>
			</tr>
	<?php
	if (!empty($marketGroupID)) {
		/*
			<tr><td class="tab">
				<b><a href="?id=10&id2=0"><img src="<?=getUrl()?>ccp_icons/23_64_4.png" style="width: 32px; height: 32px;" title="Market Home" /></a></b>
			</td><td class="tab">
				<b><a href="?id=10&id2=0">/</a></b>
			</td>
			</tr>
		*/
		?>
			<tr><td class="tab">
				<b><a href="?id=10&id2=0&marketGroupID=<?php echo($parentGroupID); ?>"><img src="<?=getUrl()?>ccp_icons/23_64_1.png" style="width: 32px; height: 32px;" title="Parent Group" /></a></b>
			</td><td class="tab">
				<b><a href="?id=10&id2=0&marketGroupID=<?php echo($parentGroupID); ?>">..</a></b>
			</td>
			</tr>
		<?php
	}
	
	if (sizeof($groups)>0) {		
				foreach($groups as $row) {
					echo('<tr><td class="tab" style="padding: 0px; width: 32px;">');
						hrefedit_group($row['marketGroupID']);
						echo("<img src=\"".getUrl()."ccp_icons/22_32_29.png\" title=\"${row['marketGroupName']}\" />");
						echo('</a>');
					echo('</td><td class="tab">');
						hrefedit_group($row['marketGroupID']);
						echo($row['marketGroupName']);
						echo('</a>');
					echo('</td>');
					echo('</tr>');
				}
	}

	if (sizeof($items)>0) {
				foreach($items as $row) {
					echo('<tr><td class="tab" style="padding: 0px; width: 32px;">');
						hrefedit_item($row['typeID']);
						echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
						echo('</a>');
					echo('</td><td class="tab">');
						hrefedit_item($row['typeID']);
						echo($row['typeName']);
						echo('</a>');
					echo('</td>');
					echo('</tr>');
				}
	}
	//echo('<form method="get" action=""><input type="hidden" name="id" value="1"><input type="hidden" name="id2" value="1"><input type="hidden" name="nr" value="'.$nr.'"><input type="submit" value="Cancel"></form>');
	echo('</table>');	
	
	?>
