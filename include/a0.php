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
include_once('inventory.php');

if (getConfigItem('item_group_explorer','disabled')=='enabled') $item_group_explorer = TRUE; else $item_group_explorer = FALSE;

$marketGroupID=secureGETnum('marketGroupID');

if (!empty($marketGroupID)) {
	$wheremarket="=$marketGroupID";
} else {
	$wheremarket="IS NULL";
}

if ($item_group_explorer) {
    $db_group_id_field = 'groupID';
    $db_group_name_field = 'groupName';
    $db_group_table = 'invGroups';
    $parent_group = "`published` = 1 AND `categoryID` IN (6,7,8,18,22,23,32,39,40,46,65,66,87) ORDER BY `groupName`";
    $mode = 'Item Group mode - check <a href="?id=5">Settings</a>.';
} else {
    $db_group_id_field = 'marketGroupID';
    $db_group_name_field = 'marketGroupName';
    $db_group_table = 'invMarketGroups';
    $parent_group = "`parentGroupID` $wheremarket";
    $mode = 'Market Group mode - check <a href="?id=5">Settings</a>.';
}

?>
	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	    <?php echo("<em><img src=\"ccp_icons/38_16_208.png\" alt=\"(i)\"/> Static Data schema: $LM_EVEDB</em> <img src=\"ccp_icons/38_16_208.png\" alt=\"(i)\"/> $mode<br />"); ?>
	<?php
		if (!empty($marketGroupID)) {
				$items=db_asocquery("SELECT itp.`typeID`, itp.`typeName`
				FROM $LM_EVEDB.`invTypes` itp			
				WHERE `$db_group_id_field` $wheremarket
				AND published = 1
				LIMIT 50;");
		}
		
		//$groups=db_asocquery("SELECT * FROM $LM_EVEDB.`invMarketGroups` WHERE `parentGroupID` $wheremarket ;");
                $groups=db_asocquery("SELECT * FROM $LM_EVEDB.`$db_group_table` WHERE $parent_group ;");
                
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
	
	function getMarketNode($marketGroupID,$db_group_table,$db_group_id_field) {
		global $LM_EVEDB;
		if (empty($marketGroupID)) return;
		$data=db_asocquery("SELECT * FROM $LM_EVEDB.`$db_group_table` WHERE `$db_group_id_field` = $marketGroupID ;");
		if (sizeof($data)==1) return($data[0]); else return;
	}
	
	if (!empty($marketGroupID)) {
		$node=getMarketNode($marketGroupID,$db_group_table,$db_group_id_field);
		$parentGroupID=$node['parentGroupID'];
		do {
			$breadcrumbs="&gt; <a href=\"?id=10&id2=0&marketGroupID=${node[$db_group_id_field]}\">${node[$db_group_name_field]}</a> $breadcrumbs";
			if (!empty($node['parentGroupID'])) {
				$node=getMarketNode($node['parentGroupID'],$db_group_table,$db_group_id_field);
			} else {
				break;
			}
			
		} while(TRUE);
		echo("<a href=\"?id=10&id2=0\"> Start </a> $breadcrumbs");
	}
        
        if (empty($last_node)) $last_node="Item Database";

	?>
			<table class="lmframework">
			<tr><th>
				<b>Icon</b>
			</th><th>
				<b>Name</b>
			</th>
                        <?php inventorySettingsHeaders(); ?>
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
			<tr><td>
				<b><a href="?id=10&id2=0&marketGroupID=<?php echo($parentGroupID); ?>"><img src="<?=getUrl()?>ccp_icons/23_64_1.png" style="width: 32px; height: 32px;" title="Parent Group" /></a></b>
			</td><td>
				<b><a href="?id=10&id2=0&marketGroupID=<?php echo($parentGroupID); ?>">..</a></b>
			</td>
                        <?php inventorySettings(0,FALSE); ?>
			</tr>
		<?php
	}
	
	if (sizeof($groups)>0) {		
            if (($item_group_explorer && empty($marketGroupID)) || !$item_group_explorer ) {
				foreach($groups as $row) {
					echo('<tr><td style="padding: 0px; width: 32px;">');
						hrefedit_group($row[$db_group_id_field]);
						echo("<img src=\"".getUrl()."ccp_icons/22_32_29.png\" title=\"${row['marketGroupName']}\" />");
						echo('</a>');
					echo('</td><td>');
						hrefedit_group($row[$db_group_id_field]);
						echo($row[$db_group_name_field]);
						echo('</a>');
					echo('</td>');
                                        inventorySettings(0,FALSE);
					echo('</tr>');
				}
            }
	}

	if (sizeof($items)>0) {
				foreach($items as $row) {
					echo('<tr><td style="padding: 0px; width: 32px;">');
						hrefedit_item($row['typeID']);
						echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
						echo('</a>');
					echo('</td><td>');
						hrefedit_item($row['typeID']);
						echo($row['typeName']);
						echo('</a>');
					echo('</td>');
                                        inventorySettings($row['typeID'],FALSE);
					echo('</tr>');
				}
	}
	//echo('<form method="get" action=""><input type="hidden" name="id" value="1"><input type="hidden" name="id2" value="1"><input type="hidden" name="nr" value="'.$nr.'"><input type="submit" value="Cancel"></form>');
	echo('</table>');	
	
        $title = generate_title($last_node);
        $description = "LMeve Database - $last_node";
        generate_meta($description, $title);
	?>
