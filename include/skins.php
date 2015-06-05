<?php
function getShipSkins($shipTypeID) {
	global $LM_EVEDB;
	$sql="SELECT shp.*,skn.*,mat.*,lic.* FROM $LM_EVEDB.`skinShip` shp
	JOIN $LM_EVEDB.`skins` skn
	ON shp.`skinID` = skn.`skinID`
	JOIN $LM_EVEDB.`skinMaterials` mat
	ON skn.`skinMaterialID` = mat.`skinMaterialID`
	JOIN $LM_EVEDB.`skinLicense` lic
	ON lic.`skinID` = skn.`skinID`
	JOIN $LM_EVEDB.`invTypes` itp
	ON lic.`licenseTypeID`=itp.`typeID`
	WHERE shp.`typeID` = $shipTypeID AND duration=-1";
	$skins=db_asocquery($sql);
	return($skins);
}

function getSkin($skinLicenseID) {
	global $LM_EVEDB;
	$sql="SELECT * FROM $LM_EVEDB.`skinLicense` lic
	JOIN $LM_EVEDB.`skins` skn
	ON lic.`skinID` = skn.`skinID`
	JOIN $LM_EVEDB.`skinMaterials` mat
	ON skn.`skinMaterialID` = mat.`skinMaterialID`
	WHERE lic.`licenseTypeID` = $skinLicenseID";
	$skins=db_asocquery($sql);
	return($skins);	
}

/*
array(12) {
    ["skinID"]=>
    string(4) "1164"
    ["typeID"]=>
    string(3) "608"
    ["internalName"]=>
    string(28) "Atron InterBus Serenity Only"
    ["skinMaterialID"]=>
    string(2) "17"
    ["material"]=>
    string(8) "interbus"
    ["displayNameID"]=>
    string(6) "505361"
    ["colorWindow"]=>
    string(6) "b8d4cb"
    ["colorPrimary"]=>
    string(6) "6e6e6e"
    ["colorSecondary"]=>
    string(6) "d5af41"
    ["colorHull"]=>
    string(6) "222222"
    ["licenseTypeID"]=>
    string(5) "34970"
    ["duration"]=>
    string(2) "30"
  }
*/

function skinhrefedit($nr) {
    echo("<a href=\"index.php?id=10&id2=1&nr=$nr\" title=\"Click to open database\">");
}

function displaySkinIcon($skin,$size=64) {
	?>
	<canvas id="skin_<?=$skin['skinID']?>" width="<?=$size?>" height="<?=$size?>" />
	<script type="text/javascript">
		var colors_<?=$skin['skinID']?> = {
			"window": "#<?=$skin['colorWindow']?>",
			"primary": "#<?=$skin['colorPrimary']?>",
			"secondary": "#<?=$skin['colorSecondary']?>",
			"hull": "#<?=$skin['colorHull']?>"
		}
		drawSkinIcon('skin_<?=$skin['skinID']?>',colors_<?=$skin['skinID']?>);
	</script>
	<?php
}

function showSkins($skins) {
	/*echo('<pre>');
	var_dump($skins);
	echo('</pre>');*/
	if (count($skins)>0) {
		?><table class="lmframework" width="100%"><tr><th colspan="2">Ship SKINs</th></tr><?php
		foreach ($skins as $skin) {
			?><tr>
				<td style="width: 32px; padding: 0px;">
					<?php displaySkinIcon($skin,32); ?>
				</td>
				<td><?php skinhrefedit($skin['licenseTypeID']); echo($skin['internalName']); ?></a></td>
			</tr><?php
		}
		?></table><?php
	}
}


?>