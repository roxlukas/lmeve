<?php
include_once('csrf.php');

function getShipSkins($shipTypeID) {
	global $LM_EVEDB;
	$sql="SELECT shp.*,skn.*,mat.*,lic.* FROM $LM_EVEDB.`skinShip` shp
	JOIN $LM_EVEDB.`skins` skn
	ON shp.`skinID` = skn.`skinID`
	LEFT JOIN $LM_EVEDB.`skinMaterialsRGB` mat
	ON skn.`skinMaterialID` = mat.`skinMaterialID`
	JOIN $LM_EVEDB.`skinLicense` lic
	ON lic.`skinID` = skn.`skinID`
	JOIN $LM_EVEDB.`invTypes` itp
	ON lic.`licenseTypeID`=itp.`typeID`
	WHERE shp.`typeID` = $shipTypeID AND duration=-1";
	$skins=db_asocquery($sql);
	return($skins);
}

function getAllRacialSkins($race) {
    global $LM_EVEDB;
    $sql="SELECT DISTINCT `sofFactionName` FROM $LM_EVEDB.`yamlGraphicIDs` WHERE `sofRaceName`='$race' AND `sofFactionName` IS NOT NULL";
    $skins=db_asocquery($sql);
    return($skins);
}

function showAllRacialSkins($skins) {
    //var_dump($skins);
	if (count($skins)>0) {
		?>
                <table class="lmframework" width="100%"><tr><th>Custom SKINs</th></tr>
                    <tr><td>Select custom skin: <select id="customskins" onchange="loadPreview(settings,this.value);">
                            <?php
                                foreach ($skins as $race) {
                                    $skin=$race['sofFactionName'];
                                    echo("<option value=\"$skin\">$skin</option>");
                                }
                            ?>
                        </select></td></tr>
                </table><?php
	}
}

function getShipBySkin($skinTypeID) {
	global $LM_EVEDB;
        $DEBUG=FALSE;
        if ($DEBUG) echo("<pre>DEBUG getShipBySkin($skinTypeID)\r\n");
	$sql="SELECT shp.*,lic.*,itp.* FROM $LM_EVEDB.`skinShip` shp
	JOIN $LM_EVEDB.`skinLicense` lic
	ON lic.`skinID` = shp.`skinID`
        JOIN $LM_EVEDB.`invTypes` itp
	ON shp.`typeID`=itp.`typeID`
	WHERE lic.`licenseTypeID` = $skinTypeID";
        if ($DEBUG) echo("SQL = $sql\r\n");
	$skins=db_asocquery($sql);
        if ($DEBUG) print_r($skins[0]);
        if ($DEBUG) echo("</pre>");
        if (count($skins)>0) return $skins[0]; else return FALSE;
}

function getSkin($skinLicenseID) {
	global $LM_EVEDB;
	$sql="SELECT * FROM $LM_EVEDB.`skinLicense` lic
	JOIN $LM_EVEDB.`skins` skn
	ON lic.`skinID` = skn.`skinID`
	LEFT JOIN $LM_EVEDB.`skinMaterialsRGB` mat
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
    //falback
    if (empty($skin['colorWindow'])) $skin['colorWindow']='a0a0a0';
    if (empty($skin['colorPrimary'])) $skin['colorPrimary']='808080';
    if (empty($skin['colorSecondary'])) $skin['colorSecondary']='a0a0a0';
    if (empty($skin['colorHull'])) $skin['colorHull']='404040';
	$rnd=md5(random_pseudo_bytes_wrapper(24));
	?>
	<canvas id="skin_<?=$rnd?>" width="<?=$size?>" height="<?=$size?>" />
	<script type="text/javascript">
		var colors_<?=$rnd?> = {
			"window": "#<?=$skin['colorWindow']?>",
			"primary": "#<?=$skin['colorPrimary']?>",
			"secondary": "#<?=$skin['colorSecondary']?>",
			"hull": "#<?=$skin['colorHull']?>"
		}
		drawSkinIcon('skin_<?=$rnd?>',colors_<?=$rnd?>);
	</script>
	<?php
}

function showSkins($skins) {
	/*echo('<pre>');
	var_dump($skins);
	echo('</pre>');*/
	if (count($skins)>0) {
            //echo("<pre>DEBUG: ".print_r($skins,TRUE)."</pre>");
		?><table class="lmframework" width="100%"><tr><th colspan="3">Ship SKINs</th></tr><?php
		foreach ($skins as $skin) {
			$rnd=md5(random_pseudo_bytes_wrapper(24));
			?><tr>
				<td style="width: 32px; padding: 0px;">
					<?php displaySkinIcon($skin,32); ?>
				</td>
				<td><?php skinhrefedit($skin['licenseTypeID']); echo($skin['internalName']); ?></a></td>
				<td style="width: 36px; text-align: center;"><input type="button" id="3dbutton_<?=$rnd?>" onclick="toggler_on('3dpreview'); loadPreview(settings,'<?=$skin['material']?>');" value="3D" disabled/>
					<script type="text/javascript">
						if (WGLSUPPORT) {
							document.getElementById('3dbutton_<?=$rnd?>').disabled=false;
							document.getElementById('3dbutton_<?=$rnd?>').title="Click to preview this SKIN";
                                                        getDesignerSkin(<?=$skin['skinID']?>, '3dbutton_<?=$rnd?>');
						}
					</script>
				</td>
			</tr><?php
		}
		?></table><?php
	}
}

function showWglCtrlPanel() {
    ?>
    <table class="lmframework" width="100%"><tr><th colspan="2">Ship Control Panel</th></tr>
        <tr><td>
                <strong>Thrusters</strong>
            </td><td>
                <input type="range" min="0" max="200" step="1" oninput="ship.setBoosterStrength(1.0 * this.value / 100.0);" onchange="ship.setBoosterStrength(1.0 * this.value / 100.0);"/>
            </td>
        </tr>
    </table>
    <?php
}


?>