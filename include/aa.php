<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewDatabase")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=10; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Ship Explorer'; //Panel name (optional)
//standard header ends here

include_once 'inventory.php';
global $LM_EVEDB;
?>
<div class="tytul">
    <?php echo($PANELNAME); ?><br>
</div>
<em><img src="ccp_icons/38_16_208.png" alt="(i)"/> This page show all items in the Static Data Export which belong to categoryID 6 - ships. It contains both ships which are available to players in game, and those which remain hidden deep in the database.</em>
<?php
//itemID 	parentItemID 	locationID 	typeID 	quantity 	flag 	singleton 	rawQuantity 	corporationID 	typeName 	locationName 	itemName
$allships=db_asocquery("SELECT itp.`typeID` AS `itemID`, 0 AS `parentItemID`, 0 AS `locationID`, itp.`typeID`, 1 AS `quantity`, 1 AS `flag`, 1 AS `singleton`, 0 AS `rawQuantity`, 0 AS `corporationID`, itp.`typeName`, '' AS `locationName`,itp.`typeName` AS `itemName`
FROM $LM_EVEDB.`invTypes` itp
JOIN $LM_EVEDB.`invGroups` ing ON itp.`groupID` = ing.`groupID`
WHERE ing.`categoryID` = 6;");

showInventory($allships,0,0,TRUE);

?>