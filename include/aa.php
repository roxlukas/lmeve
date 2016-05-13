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
include_once('yaml_graphics.php');
include_once('skins.php');

global $LM_EVEDB, $LM_CCPWGL_URL, $LM_CCPWGL_USEPROXY, $MOBILE;
?>
<div class="tytul">
    <?php echo($PANELNAME); ?><br>
</div>
<em><img src="<?=getUrl()?>ccp_icons/38_16_208.png" alt="(i)"/> This page show all items in the Static Data Export which belong to categoryID 6 - ships. It contains both ships which are available to players in game, and those which remain hidden deep in the database.</em>
<?php
//itemID 	parentItemID 	locationID 	typeID 	quantity 	flag 	singleton 	rawQuantity 	corporationID 	typeName 	locationName 	itemName
$allships=db_asocquery("SELECT itp.`typeID` AS `itemID`, 0 AS `parentItemID`, 0 AS `locationID`, itp.`typeID`, 1 AS `quantity`, 1 AS `flag`, 1 AS `singleton`, 0 AS `rawQuantity`, 0 AS `corporationID`, itp.`typeName`, '' AS `locationName`,itp.`typeName` AS `itemName`
FROM $LM_EVEDB.`invTypes` itp
JOIN $LM_EVEDB.`invGroups` ing ON itp.`groupID` = ing.`groupID`
WHERE ing.`categoryID` = 6;");

$typeID=secureGETnum('typeID');

if (!empty($typeID) && $model=getResourceFromYaml($typeID)) {
    $item=db_asocquery("SELECT itp.*,igp.`categoryID`
        FROM $LM_EVEDB.`invTypes` itp
        JOIN $LM_EVEDB.`invGroups` igp
        ON itp.`groupID`=igp.`groupID`
        WHERE `typeID` = $typeID ;");
		
    if (count($item)==0) {
            echo('There is no such record in the database.');
            return;
    }

    $item=$item[0];
    ?>
    <div style="width: 100%; height: 420px; background: url(<?php echo(getTypeIDicon($typeID,512)); ?>) no-repeat center center; background-size: cover;">
        <canvas id="wglCanvas" width="720" height="420" style="width: 100%; height: 420px;"></canvas>
    </div>
    <input type="button" id="buttonFull" value="Fullscreen" style="position: relative; top: -418px; left: 2px; z-index: 10;" onclick="togglefull();"/>
    <script type="text/javascript" src="<?=getUrl()?>ccpwgl/external/glMatrix-0.9.5.min.js"></script>
    <script type="text/javascript" src="<?=getUrl()?>ccpwgl/ccpwgl_int.js"></script>
    <script type="text/javascript" src="<?=getUrl()?>ccpwgl/test/TestCamera2.js"></script>
    <script type="text/javascript" src="<?=getUrl()?>ccpwgl/ccpwgl.js"></script>
    <script type="text/javascript" src="<?=getUrl()?>webgl.js"></script>
    <script type="text/javascript">
        settings.canvasID = 'wglCanvas';
        settings.sofHullName = '<?=$model['sofHullName']?>';
        settings.sofRaceName = '<?=$model['sofRaceName']?>';
        settings.sofFactionName = '<?=$model['sofFactionName']?>';
        settings.background = '<?=$model['background']?>';
        settings.categoryID = <?=$item['categoryID']?>;
        settings.volume = <?=$item['volume']?>;
        settings.graphicFile = '<?=$model['graphicFile']?>';
        loadPreview(settings,'default');
    </script> 
    <?php
}

showInventory($allships,0,0,'dbhrefedit');

?>