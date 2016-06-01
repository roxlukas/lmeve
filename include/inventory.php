<?php

include_once('materials.php');
include_once('yaml_graphics.php');
include_once('skins.php');
include_once("percentage.php");

function dbhrefedit($nr) {
    echo("<a href=\"index.php?id=10&id2=1&nr=$nr\" title=\"Click to open database\">");
}

function shipshrefedit($nr) {
    echo("<a href=\"index.php?id=10&id2=10&typeID=$nr\" title=\"Click to display ship in CCP WebGL\">");
}

function towershrefedit($nr) {
    echo("<a href=\"index.php?id=2&id2=2&towerid=$nr\" title=\"Click to view Labs\">");
}

function labshrefedit($nr) {
    echo("<a href=\"index.php?id=2&id2=3&nr=$nr\"  title=\"Click to show kit for this Lab/Array\">");
}

function toonhrefedit($nr) {
    echo("<a href=\"index.php?id=9&id2=6&nr=$nr\" title=\"Click to open character information\">");
}

function outsiderhrefedit($characterName) {
    $cn=rawurlencode($characterName);
    echo("<a href=\"https://gate.eveonline.com/Profile/${cn}\" target=\"_blank\" title=\"Click to open character information\">");
}

function pocohrefedit($nr) {
    echo("<a href=\"index.php?id=2&id2=7&nr=$nr\" >");
}

function invhrefedit($nr=0,$crpID=0) {
    echo("<a href=\"index.php?id=2&id2=8&nr=$nr&corporationID=$crpID\" title=\"Click to open\">");
}

function getSilos($corporationID=0) {
    if ($corporationID==0) {
        $whereSilo=TRUE;
    } else {
        $whereSilo="silos.`corporationID`=$corporationID";
    }
    global $LM_EVEDB;
    $sql="SELECT silos.`itemID`,silotype.`typeID` AS `siloTypeID`,silotype.`typeName` AS `siloTypeName`,
        silotype.`capacity`,contenttype.`typeID` AS `contentTypeID`,contenttype.`typeName` AS `contentTypeName`,
        contents.`quantity`,contenttype.`volume`,contents.`quantity` * contenttype.`volume` AS `contentsVolume`,
        100 * contents.`quantity` * contenttype.`volume` / silotype.`capacity` AS `filledPercent`,silos.`locationID`,
        map.`itemName` AS `locationName`
FROM `apiassets` silos
JOIN `$LM_EVEDB`.`invTypes` silotype
ON silos.`typeID`=silotype.`typeID`
JOIN `apiassets` contents
ON silos.`itemID`=contents.`parentItemID`
JOIN `$LM_EVEDB`.`invTypes` contenttype
ON contents.`typeID`=contenttype.`typeID`
JOIN `$LM_EVEDB`.`mapDenormalize` map
ON silos.`locationID`=map.`itemID`
WHERE silotype.`groupID`=404
AND $whereSilo ;";
    return db_asocquery($sql);
}

function showSilos($silos) {   
    global $DECIMAL_SEP, $THOUSAND_SEP;
        if (count($silos)>0) {
            ?>
            <table class="lmframework" style="" id="silos">
		<tr>
                <th style="width: 64px; padding: 0px;"></th>

                <th style="">Type</th>
                <th style="width: 150px;">Location</th>
                <th style="width: 64px; padding: 0px;"></th>

                <th style="width: 150px;">Contents</th>

                <th style="width: 100px;">
                    Volume [m3]
                </th>
                <th style="width: 100px;">
                    Filled percent
                </th>
            </tr>	
	    <?php
			foreach ($silos as $row) {
                            /*
                            Array ( 
                             * [itemID] => 1019271661332 
                             * [siloTypeID] => 14343 
                             * [siloTypeName] => Silo 
                             * [capacity] => 20000 
                             * [contentTypeID] => 16647 
                             * [contentTypeName] => Caesium 
                             * [quantity] => 2400 
                             * [volume] => 0.8 
                             * [contentsVolume] => 1920 
                             * [filledPercent] => 9.6 ) 
                             */
            ?>
            <tr>
                <td style="width: 64px; padding: 0px;">
                    <img src="<?php echo(getTypeIDicon($row['siloTypeID'],64)); ?>" title="<?=$row['siloTypeName']?>" />
                </td>
                <td>
                    <?=$row['siloTypeName']?>
                </td>
                <td>
                    <?=$row['locationName']?>
                </td>
                <td style="width: 64px; padding: 0px;">
                    <img src="<?php echo(getTypeIDicon($row['contentTypeID'],64)); ?>" title="<?=$row['contentTypeName']?>" />
                </td>
                <td>
                    <?=$row['contentTypeName']?>
                </td>
                <td>
                    <?=number_format($row['contentsVolume'], 0, $DECIMAL_SEP, $THOUSAND_SEP)?>
                </td>
                <td>
                    <?php percentbar(round($row['filledPercent']),'Full'); ?>
                </td>
            </tr>
            <?php
            }
            ?>
	    </table>
	    <?php
        } else {
		echo('<table class="lmframework" style="min-width: 800px; width: 90%;"><tr><th style="text-align: center;">Corporation doesn\'t have any Silos</th</tr></table>');
        }
}

function getInventory($parentItemID=0,$corporationID=0) {
    global $LM_EVEDB;
    if ($corporationID==0) $crp='TRUE'; else $crp="ast.`corporationID`=$corporationID";
    $sql="SELECT ast.*,ing.`categoryID`,itp.`groupID`,
        COALESCE(apl.`itemName`,itp.`typeName`) AS `typeName`,
        COALESCE(map.`itemName`,sta.`stationName`) AS `locationName`,
        COALESCE(apl.`itemName`,NULL) AS `itemName`,
        apc.`corporationName`
    FROM `apiassets` ast
    JOIN $LM_EVEDB.`invTypes` itp
    ON ast.`typeID`=itp.`typeID`
    JOIN $LM_EVEDB.`invGroups` ing
    ON itp.`groupID`=ing.`groupID`
    JOIN `apicorps` apc
    ON ast.`corporationID`=apc.`corporationID`
    LEFT JOIN $LM_EVEDB.`mapDenormalize` map
    ON ast.`locationID`=map.`itemID`
    LEFT JOIN `apiconquerablestationslist` sta
    ON ast.`locationID`=sta.`stationID`
    LEFT JOIN `apilocations` apl
    ON ast.`itemID`=apl.`itemID`
    WHERE `parentItemID`=$parentItemID AND $crp
    ORDER BY `locationName`,`flag`,itp.`groupID`,itp.`typeName`;";
    //echo("DEBUG <pre>$sql</pre>");
    $rawdata=db_asocquery($sql);
    return($rawdata);
}

function showInventory($data,$parentItemID=0,$corporationID=0,$hrefcallback='invhrefedit') {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    if ($corporationID>0) {
        $divisions=getCorpDivisions($corporationID);
        $lastFlag=0;
    }
    if ($parentItemID==0) {
        $lastLocation='';
    }
    echo('<table class="lmframework" style="width: 100%;"><tr><td>');
    if (count($data)>0) {
        foreach($data as $row) {
            /*
             * itemID 	parentItemID 	locationID 	typeID 	quantity 	flag 	singleton 	rawQuantity 	corporationID 	typeName 	locationName 	itemName
    1212459367 	0 	66005021 	27 	1 	4 	1 	-1 	414731375 	Office 	NULL 	Unknown
    1007028980226 	0 	60005020 	1944 	2 	62 	0 	NULL 	414731375 	Bestower 	Tollus X - Moon 4 - Republic Justice Department Tr... 	Unknown
             */
            
            //Office Hack
            if( $row['typeID']==27 ) {
                //this is an office
                //$row['typeName']=$row['locationName'];
                $row['typeID']=28089;
            }
            //Corp Divisions Hack
            if ($corporationID>0) {
                if ($row['flag']!=$lastFlag) {
                    $lastFlag=$row['flag'];
                    echo("</td></tr><tr><th><h3>".$divisions[$row['flag']]."</h3></th></tr><tr><td>");
                }
            }
            //Locations Hack
            if ($parentItemID==0) {
                if ($row['locationName']!=$lastLocation) {
                    $lastLocation=$row['locationName'];
                    echo("</td></tr><tr><th><h3>$lastLocation</h3></th></tr><tr><td>");
                }
            }
            ?>
            <div style="margin: 10px; width: 64px; height: 100px; float: left;">
                <div style="position: absolute;">
            <?php 
            if (function_exists($hrefcallback)) call_user_func($hrefcallback,$row['itemID'], $row['corporationID']);
            
?>
            <img src="<?php echo(getTypeIDicon($row['typeID'],64));?>" title="<?=$row['typeName']?>" /></a>
                </div>
                <?php if ($row['singleton']==0) { ?>
                <div style="position: absolute; margin-top: 50px; width: 64px; text-align: right;">
                    <span style="background: rgba(0,0,0,0.5); padding: 2px; font-size: 11px;"><?php echo(number_format($row['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP)); ?></span>
                </div>
                <?php } ?>
                <div style="margin-top: 66px; width: 64px; text-align: center;"><?=$row['typeName']?></div>
            </div>
            <?php          
        }
    } else {
        echo('No items found.');
    }
    echo('</td></tr></table>');
}

function getSlotFlags() {
    $SLOTS=array(
        //low slots
        11 => array('x' => 423+32, 'y' => 445+32),
        12 => array('x' => 387+32, 'y' => 464+32),
        13 => array('x' => 348+32, 'y' => 481+32),
        14 => array('x' => 308+32, 'y' => 488+32),
        15 => array('x' => 267+32, 'y' => 488+32),
        16 => array('x' => 225+32, 'y' => 480+32),
        17 => array('x' => 186+32, 'y' => 466+32),
        18 => array('x' => 150+32, 'y' => 445+32),
        //med slots
        19 => array('x' => 484+32, 'y' => 114+32),
        20 => array('x' => 505+32, 'y' => 149+32),
        21 => array('x' => 519+32, 'y' => 188+32),
        22 => array('x' => 528+32, 'y' => 230+32),
        23 => array('x' => 529+32, 'y' => 271+32),
        24 => array('x' => 521+32, 'y' => 313+32),
        25 => array('x' => 507+32, 'y' => 353+32),
        26 => array('x' => 487+32, 'y' => 388+32),
        //high slots
        27 => array('x' => 151+32, 'y' => 57+32),
        28 => array('x' => 187+32, 'y' => 35+32),
        29 => array('x' => 227+32, 'y' => 21+32),
        30 => array('x' => 268+32, 'y' => 13+32),
        31 => array('x' => 309+32, 'y' => 13+32),
        32 => array('x' => 349+32, 'y' => 21+32),
        33 => array('x' => 390+32, 'y' => 35+32),
        34 => array('x' => 427+32, 'y' => 55+32),
        //rig slots
        92 => array('x' => 100+32, 'y' => 103+32),
        93 => array('x' => 77+32, 'y' => 139+32),
        94 => array('x' => 60+32, 'y' => 180+32),
        //subsystem slots
        125 => array('x' => 49+32, 'y' => 236+32),
        126 => array('x' => 49+32, 'y' => 279+32),
        127 => array('x' => 59+32, 'y' => 322+32),
        128 => array('x' => 76+32, 'y' => 362+32),
        129 => array('x' => 100+32, 'y' => 398+32)
    );
    //echo("<pre>".serialize($SLOTS)."</pre>");
    return $SLOTS;
}

function drawTypeIDIcon($typeID,$typeName,$x,$y,$size) {
    ?>
    <div style="position: absolute; margin-left: <?=$x-floor($size/2)?>px; margin-top: <?=$y-floor($size/2)?>px;">
        <img src="<?php echo(getTypeIDicon($typeID,64));?>" title="<?=$typeName?>" style="width: <?=$size?>px; height: <?=$size?>px;" />
    </div>
    <?php
}

function showInventoryFitting($data,$shipTypeID,$vertical=FALSE) {
    global $LM_EVEDB, $DECIMAL_SEP, $THOUSAND_SEP;
    
    $SLOTS=getSlotFlags();
    $ICONSIZE=48;
    $AMMOICONSIZE=32;
    
    $model=getResourceFromYaml($shipTypeID);
    
    $item=db_asocquery("SELECT itp.*,igp.`categoryID`
		FROM $LM_EVEDB.`invTypes` itp
        JOIN $LM_EVEDB.`invGroups` igp
        ON itp.`groupID`=igp.`groupID`
		WHERE `typeID` = $shipTypeID ;");
    if (count($item)>0) {
        $item=$item[0];
    } else {
        $item['volume']=30000;
        $item['categoryID']=6;
    }
    
    ?>
    
    
    <table class="lmframework" style="width: 100%;"><tr><td style="width:636px;">
    <div style="width: 636px; height: 563px; float: left;">
          <div style="position: absolute; margin-left: 52px; margin-top: 20px; ">
              <img src="<?php echo(getTypeIDicon($shipTypeID,512));?>" style="width: 532px; height: 532px;" />
          </div>
          <div style="position: absolute; margin-left: 52px; margin-top: 20px; ">
              <canvas id="wglCanvas" width="532" height="532" style="width: 532px; height: 532px;"></canvas>
          </div>
          <div style="position: absolute; margin-left: 0px; margin-top: 0px; pointer-events: none; ">
              <img src="<?=getUrl()?>img/fitting_mask.png" />
          </div>

    <?php
    //fitting mask center
    $xcen=318;
    $ycen=281;
    if (count($data)>0) {
        foreach($data as $row) {
            if (array_key_exists($row['flag'], $SLOTS)) {
                if (isset($row['categoryID']) && $row['categoryID']==8) {
                    $x=$SLOTS[$row['flag']][x];
                    $y=$SLOTS[$row['flag']][y];
                    $dx=$xcen-$x;
                    $dy=$ycen-$y;
                    $rprim=$ICONSIZE/2+($ICONSIZE-$AMMOICONSIZE)/2;
                    $r=round(sqrt($dx*$dx+$dy*$dy));
                    $xprim=round($rprim*$dx/$r);
                    $yprim=round($rprim*$dy/$r);
                    //echo("DEBUG: x=$x y=$y dx=$dx dy=$dy r=$r rprim=$rprim xprim=$xprim yprim=$yprim<br/>");
                    drawTypeIDIcon($row['typeID'],$row['typeName'],$SLOTS[$row['flag']][x]+$xprim,$SLOTS[$row['flag']][y]+$yprim,$AMMOICONSIZE);
                } else {
                    drawTypeIDIcon($row['typeID'],$row['typeName'],$SLOTS[$row['flag']][x],$SLOTS[$row['flag']][y],$ICONSIZE);
                }
            ?>
            
            <?php   
            }
        }
    }
    ?>
       </div>
    </td><?php if($vertical) echo("</tr><tr>"); ?><td style="vertical-align: top;">
        <table class="lmframework" style="width:100%"><tr><td style="vertical-align: top;">
    <?php
        $flags=getInvFlags();
        foreach($data as $row) {
            if (!array_key_exists($row['flag'], $SLOTS)) {
                //flagID hack
                if ($row['flag']!=$lastFlag) {
                    $lastFlag=$row['flag'];
                    echo("</td></tr><tr><th><h3>".$flags[$row['flag']]."</h3></th></tr><tr><td>");
                }
            ?>
            <div style="margin: 10px; width: 64px; height: 100px; float: left;">
                <div style="position: absolute;">
            <?php invhrefedit($row['itemID'], $row['corporationID']); ?>
            <img src="<?php echo(getTypeIDicon($row['typeID'],64));?>" title="<?=$row['typeName']?>" /></a>
                </div>
                <?php if ($row['singleton']==0) { ?>
                <div style="position: absolute; margin-top: 50px; width: 64px; text-align: right;">
                    <?php
                        //inventory support
                        if (isset($row['quantity'])) {
                            ?><span style="background: rgba(0,0,0,0.5); padding: 2px; font-size: 11px;"><?php 
                            echo(number_format($row['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
                            ?></span><?php
                        }
                        //killboard support
                        if (isset($row['qtyDropped']) && $row['qtyDropped'] > 0) {
                            ?><span title="Dropped" style="background: rgba(0,128,0,0.5); padding: 2px; font-size: 11px;"><?php 
                            echo(number_format($row['qtyDropped'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
                            ?></span><?php
                        }
                        if (isset($row['qtyDestroyed']) && $row['qtyDestroyed'] > 0) {
                            ?><span title="Destroyed" style="background: rgba(128,0,0,0.5); padding: 2px; font-size: 11px;"><?php 
                            echo(number_format($row['qtyDestroyed'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
                            ?></span><?php
                        }
                    ?>
                </div>
                <?php } ?>
                <div style="margin-top: 66px; width: 64px; text-align: center;"><?=$row['typeName']?></div>
            </div>
            <?php   
            }
        }
    ?>
        </td></tr></table>
    </td></tr></table>
<?php if (getConfigItem(useWebGLpreview,'enabled')=='enabled') { ?>
    <script type="text/javascript" src="<?=getUrl()?>ccpwgl/external/glMatrix-0.9.5.min.js"></script>
    <script type="text/javascript" src="<?=getUrl()?>ccpwgl/ccpwgl_int.js"></script>
    <script type="text/javascript" src="<?=getUrl()?>ccpwgl/test/TestCamera2.js"></script>
    <script type="text/javascript" src="<?=getUrl()?>ccpwgl/ccpwgl.js"></script>
    <script type="text/javascript" src="<?=getUrl()?>webgl.js"></script>
    <script type="text/javascript">
        //webgl suprt
        settings.canvasID = 'wglCanvas';
        settings.sofHullName = '<?=$model['sofHullName']?>';
        settings.sofRaceName = '<?=$model['sofRaceName']?>';
        settings.sofFactionName = '<?=$model['sofFactionName']?>';
        settings.background = '<?=$model['background']?>';
        settings.categoryID = <?=$item['categoryID']?>;
        settings.volume = <?=$item['volume']?>;
        settings.graphicFile = '<?=$model['graphicFile']?>';
        if (WGLSUPPORT && settings.categoryID==6) {
            loadPreview(settings,'default');
            
            
        }
    </script>
    <?php
    }
}

function getCorpDivisions($corporationID) {
    $ret=array();
    $sql="SELECT `accountKey`-885 AS `flag`, description FROM `apidivisions` WHERE `corporationID`=$corporationID;";
    $raw=db_asocquery($sql);
    if (count($raw)>0) {
        foreach($raw as $row) {
            if ($row['flag']==115) $row['flag']=4; // first hangar is actually a 'Hangar' (flag: 4), the rest is 'Corp Security Access Group' 2 through 7
            $ret[$row['flag']]=$row['description'];
        }
    } else return FALSE;
    return($ret);
}

function getInvFlags() {
    global $LM_EVEDB;
    $ret=array();
    $sql="SELECT `flagID`, `flagText` FROM $LM_EVEDB.`invFlags`;";
    $raw=db_asocquery($sql);
    if (count($raw)>0) {
        foreach($raw as $row) {
            $ret[$row['flagID']]=$row['flagText'];
        }
    } else return FALSE;
    return($ret);
}

function getInventoryHeader($itemID) {
    global $LM_EVEDB;
    $sql="SELECT ast.*,ing.`categoryID`,itp.`groupID`,
        COALESCE(apl.`itemName`,itp.`typeName`) AS `typeName`,
        COALESCE(map.`itemName`,sta.`stationName`) AS `locationName`,
        COALESCE(apl.`itemName`,NULL) AS `itemName`,
        apc.`corporationName`
    FROM `apiassets` ast
    JOIN $LM_EVEDB.`invTypes` itp
    ON ast.`typeID`=itp.`typeID`
    JOIN $LM_EVEDB.`invGroups` ing
    ON itp.`groupID`=ing.`groupID`
    JOIN `apicorps` apc
    ON ast.`corporationID`=apc.`corporationID`
    LEFT JOIN $LM_EVEDB.`mapDenormalize` map
    ON ast.`locationID`=map.`itemID`
    LEFT JOIN `apiconquerablestationslist` sta
    ON ast.`locationID`=sta.`stationID`
    LEFT JOIN `apilocations` apl
    ON ast.`itemID`=apl.`itemID`
    WHERE ast.`itemID`=$itemID;";
    //echo("DEBUG <pre>$sql</pre>");
    $rawdata=db_asocquery($sql);
    return($rawdata);
}

function showInventoryHeader($itemdata,$corporationID) {
    echo('<table class="lmframework" style="width:100%;"><tr>');
    if (count($itemdata)==1) {
        $row=$itemdata[0];
        if( $row['typeID']==27 ) {
                //this is an office
                $row['typeID']=28089;
            }
        ?>
            <th style="width: 64px;">
                <?php if($row['parentItemID']==0) $crpID=0; else $crpID=$row['corporationID'];
                    invhrefedit($row['parentItemID'], $crpID); ?>
                <img src="<?=getUrl()?>ccp_icons/23_64_1.png" alt="&lt; back"/></a>
            </th>
            <th style="width: 64px;">
                <img src="<?php echo(getTypeIDicon($row['typeID'],64));?>" title="<?=$row['typeName']?>" />
            </th>
            <th style="text-align: center;">
                <h2><?=$row['locationName']?></h2>
                <em><?=$row['typeName']?></em>
            </th>
        <?php            
    } else {
        echo('<th>Parent item not found.</th>');
    }
    echo('</tr></table>');
}

function getControlTowers($where='TRUE') {
    global $LM_EVEDB;
    $sql="SELECT asl.*,asd.*,asf.`typeID` AS `fuelTypeID`, asf.`quantity` AS `fuelQuantity`,
          iftp.`typeName` AS `fuelTypeName`,ictr.`purpose`,ictr.`quantity` AS `requiredQuantity`,
          apl.itemName,apl.x,apl.y,apl.z,itp.`typeName`,ssn.`itemName` AS `solarSystemName`,ssm.`itemName` AS `moonName` 
    FROM `apistarbaselist` asl
    JOIN `apistarbasedetail` asd
    ON asl.`itemID`=asd.`itemID`
    JOIN `apistarbasefuel` asf
    ON asl.`itemID`=asf.`itemID`
    JOIN $LM_EVEDB.`invControlTowerResources` ictr
    ON asf.`typeID`=ictr.`resourceTypeID` AND asl.`typeID`=ictr.`controlTowerTypeID`
    JOIN $LM_EVEDB.`invTypes` iftp
    ON asf.`typeID`=iftp.`typeID`
    JOIN $LM_EVEDB.`invNames` ssn
    ON asl.`locationID`=ssn.`itemID`
    JOIN $LM_EVEDB.`invNames` ssm
    ON asl.`moonID`=ssm.`itemID`
    JOIN $LM_EVEDB.`invTypes` itp
    ON asl.`typeID`=itp.`typeID`
    LEFT JOIN `apilocations` apl
    ON asl.`itemID`=apl.`itemID`
    WHERE $where
    ORDER BY ssm.`itemName`,ictr.`purpose`,iftp.`typeName`;";
    //echo("DEBUG: $sql");
    $rawdata=db_asocquery($sql);
    $ret=array();
    foreach($rawdata as $row) { //zmiana struktury danych
        $itemID=$row['itemID'];
        $fuelTypeID=$row['fuelTypeID'];
        $ret[$itemID]['itemID']=$itemID;
        $ret[$itemID]['state']=$row['state'];
        $ret[$itemID]['towerTypeID']=$row['typeID'];
        $ret[$itemID]['towerTypeName']=$row['typeName'];
        $ret[$itemID]['towerName']=$row['itemName'];
        $ret[$itemID]['locationID']=$row['locationID'];
        $ret[$itemID]['moonID']=$row['moonID'];
        $ret[$itemID]['stateTimestamp']=$row['stateTimestamp'];
        $ret[$itemID]['onlineTimestamp']=$row['onlineTimestamp'];
        $ret[$itemID]['standingOwnerID']=$row['standingOwnerID'];
        $ret[$itemID]['usageFlags']=$row['usageFlags'];
        $ret[$itemID]['deployFlags']=$row['deployFlags'];
        $ret[$itemID]['allowCorporationMembers']=$row['allowCorporationMembers'];
        $ret[$itemID]['allowAllianceMembers']=$row['allowAllianceMembers'];
        $ret[$itemID]['onStandingDrop']=$row['onStandingDrop'];
        $ret[$itemID]['onStatusDrop']=$row['onStatusDrop'];
        $ret[$itemID]['onStatusDropStanding']=$row['onStatusDropStanding'];
        $ret[$itemID]['onAggression']=$row['onAggression'];
        $ret[$itemID]['onCorporationWar']=$row['onCorporationWar'];
        $ret[$itemID]['fuel'][$fuelTypeID]['fuelTypeID']=$fuelTypeID;
        $ret[$itemID]['fuel'][$fuelTypeID]['fuelTypeName']=$row['fuelTypeName'];
        $ret[$itemID]['fuel'][$fuelTypeID]['fuelQuantity']=$row['fuelQuantity'];
        $ret[$itemID]['fuel'][$fuelTypeID]['requiredQuantity']=$row['requiredQuantity'];
        $ret[$itemID]['fuel'][$fuelTypeID]['purpose']=$row['purpose'];
        $ret[$itemID]['fuel'][$fuelTypeID]['timeLeft']=floor($row['fuelQuantity']/$row['requiredQuantity']);
        $ret[$itemID]['location']['x']=$row['x'];
        $ret[$itemID]['location']['y']=$row['y'];
        $ret[$itemID]['location']['z']=$row['z'];
        $ret[$itemID]['location']['solarSystemName']=$row['solarSystemName'];
        $ret[$itemID]['location']['moonName']=$row['moonName'];
        
    }
    if ($rawdata !== FALSE) {
        return $ret;
    } else {
        return FALSE;
    }
}

function getControlTowersOld($where='TRUE') {
    global $LM_EVEDB;
    $sql="SELECT asl.*,apl.itemName,apl.x,apl.y,apl.z,itp.`typeName`,ssn.`itemName` AS `solarSystemName`,ssm.`itemName` AS `moonName` 
    FROM `apistarbaselist` asl
    JOIN $LM_EVEDB.`invNames` ssn
    ON asl.`locationID`=ssn.`itemID`
    JOIN $LM_EVEDB.`invNames` ssm
    ON asl.`moonID`=ssm.`itemID`
    JOIN $LM_EVEDB.`invTypes` itp
    ON asl.`typeID`=itp.`typeID`
    LEFT JOIN `apilocations` apl
    ON asl.`itemID`=apl.`itemID`
    WHERE $where;";
    //echo("DEBUG: $sql");
    $rawdata=db_asocquery($sql);
    return $rawdata;
}

function getLabs($where='TRUE') {
    global $LM_EVEDB;
    $sql_labs="SELECT apf.*,apl.itemName
    FROM `apifacilities` apf
    JOIN `apilocations` apl
    ON apf.`facilityID`=apl.`itemID`
    WHERE $where
    ORDER BY apl.itemName;";
    //echo("DEBUG: $sql_labs<br/>");
    $rawlabdata=db_asocquery($sql_labs);
    return $rawlabdata;
}

function getLabDetails($facilityID) {
    global $LM_EVEDB;
    $sql="SELECT apf.*,apl.*
    FROM `apifacilities` apf
    JOIN `apilocations` apl
    ON apf.`facilityID`=apl.`itemID`
    WHERE `facilityID`=$facilityID;";
    
    $raw=db_asocquery($sql);
    if (count($raw)>0) {
        $raw=$raw[0];
        $x=$raw['x']; $y=$raw['y']; $z=$raw['z']; 
        $ct=getControlTowers("SQRT(POW($x-apl.x,2)+POW($y-apl.y,2)+POW($z-apl.z,2)) < 30000");
        if (count($ct)>0) {
            //typeName solarSystemName moonName
            $raw['towerTypeName']=$ct[0]['typeName'];
            $raw['solarSystemName']=$ct[0]['solarSystemName'];
            $raw['moonName']=$ct[0]['moonName'];
        } else {
            $raw['towerTypeName']='Unknown';
            $raw['solarSystemName']='Unknown';
            $raw['moonName']='Unknown';
        }
        return $raw;
    } else {
        return false;
    }
}

function getSimpleTasks($where='TRUE') {
    $year=date("Y"); $month=date("m");
    global $LM_EVEDB;
    $sql="SELECT lmt.*,itp.`typeName`,acm.`name` AS characterName FROM `lmtasks` lmt
    JOIN $LM_EVEDB.`invTypes` itp
    ON lmt.`typeID`=itp.`typeID`
    LEFT JOIN `apicorpmembers` acm
    ON lmt.`characterID`=acm.`characterID`
    WHERE ((lmt.`singleton`=1 AND lmt.`taskCreateTimestamp` BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)) OR (lmt.`singleton`=0))
    AND $where";
    $raw=db_asocquery($sql);
    return($raw);
}

function getLabsAndTasks($corporationID) {
    global $LM_EVEDB;
    $DEBUG=FALSE;
    $raw_towers=getControlTowers("asl.`corporationID`=$corporationID");
    if ($DEBUG) {
        echo('DEBUG:<br/>$raw_towers=<pre>'); var_dump($raw_towers); echo('</pre>');
    }
    
    $towers=array();
    $labs=array();
    if (count($raw_towers)>0) {
        $raw_tasks=getSimpleTasks();
        foreach($raw_towers as $tower) {
            if ($DEBUG) {
                echo('$tower=<pre>'); var_dump($tower); echo('</pre>');
            }
            $x=$tower['location'][x];
            $y=$tower['location'][y];
            $z=$tower['location'][z];
            $raw_labs=getLabs("SQRT(POW($x-apl.x,2)+POW($y-apl.y,2)+POW($z-apl.z,2)) < 30000");
            if ($DEBUG) {
                echo('$raw_labs=<pre>'); var_dump($raw_labs); echo('</pre>');
            }
            $towers[$tower['itemID']]=$tower;
            foreach($raw_labs as $lab) {
                $towers[$tower['itemID']]['labs'][$lab['facilityID']]=$lab;
                $labs[$lab['facilityID']]['towerID']=$tower['itemID'];
            }
        }
   
        
        foreach($raw_tasks as $task) {
            if (!is_null($task['structureID']) && array_key_exists($task['structureID'], $labs)) {
                $towerID=$labs[$task['structureID']]['towerID'];
                $towers[$towerID]['labs'][$task['structureID']]['users'][$task['characterID']]=$task['characterName'];
                $towers[$towerID]['labs'][$task['structureID']]['products'][$task['typeID']]=$task['typeName'];
            }
        }
        
    }
    //var_dump($towers);
    return $towers;
}

function showLabsAndTasks($towers) {
    $rights_viewallchars=checkrights("Administrator,ViewAllCharacters");
    $rights_editpos=checkrights("Administrator,EditPOS");
    if (count($towers)>0) foreach($towers as $tower) {
        //if (count($tower['labs'])>0) { 
        if (true) { 
        ?>
        <table class="lmframework" style="width: 70%; min-width: 608px;" id="">
            <tr><th colspan="7" style="text-align: center;">
                <h3><?php echo($tower['location']['moonName'].' ("'.$tower['towerName'].'")'); ?></h3>
            </th>
            </tr>
            <tr><th style="width: 32px; min-width: 32px; padding: 0px; text-align: center;">
                Icon
            </th><th style="width: 27%; min-width: 160px;">
                Name
            </th><th style="width: 27%; min-width: 160px;">
                Structure Type
            </th><th style="width: 20%; min-width: 128px;">
                Users
            </th><th style="width: 20%; min-width: 128px;">
                Products
            </th><th style="width: 32px; min-width: 32px; padding: 0px; text-align: center;">
                Kit
            </th><th style="width: 32px; min-width: 32px; padding: 0px; text-align: center;" title="Inventory">
                Inv
            </th>
            </tr>
            <?php
            if (count($tower['labs'])>0) foreach ($tower['labs'] as $facilityID => $row) {
                ?>
                <tr><td width="32" style="padding: 0px; text-align: center;">
                    <?php dbhrefedit($row['typeID']); echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />"); echo('</a>'); ?>
                </td><td>
                    <?php 
                    labshrefedit($facilityID); echo(stripslashes($row['itemName'])); echo('</a>');
                     ?>
                </td><td style="">
                    <?php
                    dbhrefedit($row['typeID']); echo(stripslashes($row['typeName']));  echo('</a>');
                     ?>
                </td><td style="">
                    <?php 
                    if (count($row['users'])>0) foreach ($row['users'] as $user => $name) {
                        if ($rights_viewallchars) toonhrefedit($user);
                        echo("<img src=\"https://imageserver.eveonline.com/character/${user}_32.jpg\" title=\"$name\">");
			if ($rights_viewallchars) echo('</a>');
                    }
                    ?>
                </td><td>
                    <?php 
                    if (count($row['products'])>0) foreach ($row['products'] as $product => $name) {
                        dbhrefedit($product);
                        echo("<img src=\"".getTypeIDicon($product)."\" title=\"$name\">");
                        echo('</a>');
                    }
                    ?> 
                </td><td>
                    <?php 
                    labshrefedit($facilityID); echo("<img src=\"ccp_icons/12_64_3.png\" style=\"width: 24px; height: 24px;\" title=\"Show Kit\" /></span>"); echo('</a>');
                     ?> 
                </td><td>
                    <?php 
                     invhrefedit($facilityID); echo("<img src=\"ccp_icons/26_64_11.png\" style=\"width: 24px; height: 24px;\" title=\"Open Inventory\"/></span>"); echo('</a>');
                     ?> 
                </td>
                </tr>
                <?php
            }
            ?>
                
            </table><br/>
        <?php
        }
    } else {
        echo("<h3>Corporation does not have any Control Towers.</h3>");
    }
}

function showControlTowers($controltowers) {   
        if (count($controltowers)>0) {
            ?>
            <table class="lmframework" style="min-width: 800px; width: 90%;" id="">
			
	    <?php
			foreach ($controltowers as $row) {
            ?>
            <tr>
                <td width="30%" style="text-align: center;">
                    <?php towershrefedit($row['itemID']); ?>
                    <h1 style="text-align: center;"><?=$row['towerName']?></h1>
                    <img src="<?php echo(getTypeIDicon($row['towerTypeID'],64)); ?>" title="<?=$row['towerTypeName']?>" />
                    <h3 style="text-align: center;"><?=$row['location']['moonName']?></h3>
                    <i><?=$row['towerTypeName']?></i>
                    </a>
                </td>
                <td width="40%" style="vertical-align: top;">
                    <table class="lmframework" style="width: 100%;">
                        <tr>
                            <th colspan="2" style="text-align: center;">
                                State
                            </th>
                        </tr>
                        <tr>
                            <td>
                                State
                            </td>
                            <td>
                                <?php
                                switch($row['state']) {
                                case 1:
                                    echo('anchored');
                                    break;
                                case 4:
                                    echo('<span style="color: #080; font-weight: bold;">online</span>');
                                    break;
                                default:
                                    echo('unknown');
                                }?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Online since
                            </td>
                            <td>
                                <?=$row['onlineTimestamp']?>
                            </td>
                        </tr>
                    </table>
                    <table class="lmframework" style="width: 100%;">
                        <tr>
                            <th colspan="4" style="text-align: center;">
                                Fuel
                            </th>
                        </tr>
                        <?php
                            foreach ($row['fuel'] as $fuel) {
                                $timeleft=$fuel['timeLeft'];
                                if ($timeleft < 48 && $fuel['purpose']==1) $style=" color: red; font-weight: bold;"; else $style="";
                                if ($timeleft > 24) {
                                    $days=floor($timeleft/24);
                                    $hours=$timeleft%24;
                                    $timeleft="$days d $hours h";
                                } else {
                                    $timeleft="$timeleft h";
                                }
                                echo('<tr><td style="padding: 0px; width: 32px;"><img src="'.getTypeIDicon($fuel['fuelTypeID']).'" title="" /></td><td>'.$fuel['fuelTypeName'].'</td><td style="text-align: right;">'.$fuel['fuelQuantity'].'</td><td style="text-align: right;'.$style.'">'.$timeleft.'</td></tr>');
                            }
                        ?>
                    </table>

                </td>
                <td width="30%" style="vertical-align: top;">
                    <table class="lmframework" style="width: 100%;">
                        <tr>
                            <th colspan="2" style="text-align: center;">
                                Usage settings
                            </th>
                        </tr>
                        <tr>
                            <td>
                                Allow corp
                            </td>
                            <td style="text-align: right;">
                                <?php echo($row['allowCorporationMembers']==1 ? 'yes' : 'no'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Allow alliance
                            </td>
                            <td style="text-align: right;">
                                <?php echo($row['allowAllianceMembers']==1 ? 'yes' : 'no'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" style="text-align: center">
                                Defense settings
                            </th>
                        </tr>
                        <tr>
                            <td>
                                Attack on standings
                            </td>
                            <td style="text-align: right;">
                                <?php echo($row['onStandingDrop']==1 ? 'yes' : 'no'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Attack on aggression
                            </td>
                            <td style="text-align: right;">
                                <?php echo($row['onAggression']==1 ? 'yes' : 'no'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Attack when at war
                            </td>
                            <td style="text-align: right;">
                                <?php echo($row['onCorporationWar']==1 ? 'yes' : 'no'); ?>
                            </td>
                        </tr>
                    </table>
                </td>    
            </tr>
            <?php
            }
            ?>
	    </table>
	    <?php
        } else {
		echo('<table class="lmframework" style="min-width: 800px; width: 90%;"><tr><th style="text-align: center;">Corporation doesn\'t have any POSes</th</tr></table>');
        }
}

function showControlTowersOld($controltowers) {   
        if (count($controltowers)>0) {
			?>
		    <table class="lmframework" style="" id="">
			<tr><th style="width: 32px; padding: 0px; text-align: center;">
				Icon
			</th><th style="">
				Name
			</th><th style="">
				Control Tower Type
			</th><th style="min-width: 120px;">
				Location
			</th><th style="width: 64px;">
				State
			</th><th style="width: 110px;">
				Online since
			</th>
			</tr>
			<?php
			foreach ($controltowers as $row) {
            ?>
            <tr><td width="32" style="padding: 0px; text-align: center;">
                <?php towershrefedit($row['itemID']); echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />"); echo('</a>'); ?>
            </td><td>
                <?php towershrefedit($row['itemID']);
                echo($row['itemName']); echo('</a>'); ?>
            </td><td>
                <?php towershrefedit($row['itemID']);
                echo($row['typeName']); echo('</a>'); ?>
            </td><td style="">
                <?php towershrefedit($row['itemID']);
                echo($row['moonName']); echo('</a>'); ?> 
            </td><td style="">
                <?php towershrefedit($row['itemID']);
                switch($row['state']) {
                    case 1:
                        echo('anchored');
                        break;
                    case 4:
                        echo('online');
                        break;
                    default:
                        echo('unknown');
                }
                echo('</a>'); ?>
            </td><td>
                <?php towershrefedit($row['itemID']);
                echo($row['onlineTimestamp']); echo('</a>'); ?> 
            </td>
            </tr>
            <?php
            }
            ?>
			</table>
			<?php
        } else {
		echo('<table class="lmframework" style="width: 564px;"><tr><th style="text-align: center;">Corporation doesn\'t have any POSes</th</tr></table>');
        }
}

/*
 * Laurvier II = 40316877 (mapping: invNames)
 * Laurvier II planet typeID=2016 (mapping: invItems)
 * mapDenormalize: itemID typeID groupID solarSystemID constellationID regionID orbitID x y z radius itemName security celestialIndex orbitIndex  * 
 * 
 * Specific PoCo income -> apiwalletjournal column argID1=40316877 and argName1=Laurvier II
 * 
 * apilocations - itemID=1012675032345 "Customs Office (Laurvier II)" 70731768720.1602 -10758809884.6656 47766339694.8543 corporationID=414731375
 * apipocolist - itemID=1012675032345 locationID=30005002 locationName=Laurvier 19 1 1 -10 0 0 0 0.05 0.07 0.1 0.15 corporationID=414731375
 */
/*
CREATE FUNCTION hello (s CHAR(20))
RETURNS CHAR(50) DETERMINISTIC
RETURN CONCAT('Hello, ',s,'!');
 * 
select (pow(:x-x,2)+pow(:y-y,2)+pow(:z-z,2)) distance,itemName,itemID,typeID
from mapDenormalize
where solarsystemid=:solarsystemid
order by distance asc
limit 1
 */
function getPocos($where='TRUE') {
    global $LM_EVEDB;
    //refresh mapDenormalize VIEW for Stored Procedure
    db_uquery("CREATE OR REPLACE VIEW `mapDenormalize` AS SELECT * FROM `$LM_EVEDB`.`mapDenormalize`");
    //do the real select
    //`solarsystemID` BETWEEN 30000001 AND 31002604 is a fix for XML API bug that keeps returning destroyed POCOs
    //in solar systems like: solarSystemID=1915	solarSystemName='EVE Singleton Parent Junkyard - Week 15'
    $sql="SELECT apo.*, thirtyDayIncome(`planetItemID`) AS `planetIncome`, ina.`itemName` AS `planetName`, ite.`typeID` AS `planetTypeID`, itp.`typeName` AS `planetTypeName`
    FROM 
        (SELECT apo1.*,apl.itemName, findNearest(apl.x, apl.y, apl.z, apo1.solarSystemID) AS `planetItemID`
        FROM `apipocolist` apo1
        LEFT JOIN `apilocations` apl
        ON apo1.`itemID`=apl.`itemID`
        WHERE `solarsystemID` BETWEEN 30000001 AND 31002604) AS apo
    LEFT JOIN `$LM_EVEDB`.`invItems` AS ite
    ON apo.`planetItemID`=ite.itemID
    LEFT JOIN `$LM_EVEDB`.`invNames` AS ina
    ON apo.`planetItemID`=ina.itemID
    LEFT JOIN `$LM_EVEDB`.`invTypes` AS itp
    ON ite.`typeID`=itp.`typeID`
    WHERE $where";
    $raw=db_asocquery($sql);
    //echo("<pre>".print_r($raw,TRUE)."</pre>");
    return($raw);
}

function getPocoIncome($corporationID) {
    $year=date("Y"); $month=date("m");
    switch ($month) {
                case 1:
                        $PREVMONTH=12;
                        $PREVYEAR=$year-1;
                break;
                case 12:
                        $PREVMONTH=11;
                        $PREVYEAR=$year;
                break;
                default:
                        $PREVMONTH=$month-1;
                        $PREVYEAR=$year;
    }
    $sql="SELECT SUM(awj.amount) AS amount, 'current' AS month FROM
    apiwalletjournal awj
    JOIN apireftypes art
    ON awj.refTypeID=art.refTypeID
    WHERE awj.date BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
    AND awj.corporationID = $corporationID
    AND awj.refTypeID IN (96, 97)
    UNION
    SELECT SUM(awj.amount) AS amount, 'previous' AS month FROM
    apiwalletjournal awj
    JOIN apireftypes art
    ON awj.refTypeID=art.refTypeID
    WHERE awj.date BETWEEN '".sprintf("%04d", $PREVYEAR)."-".sprintf("%02d", $PREVMONTH)."-01' AND LAST_DAY('".sprintf("%04d", $PREVYEAR)."-".sprintf("%02d", $PREVMONTH)."-01')
    AND awj.corporationID = $corporationID
    AND awj.refTypeID IN (96, 97);";
    $poco_raw=db_asocquery($sql);
    return $poco_raw;
}

function getSinglePocoIncome($planetItemID) {
    $year=date("Y"); $month=date("m");
    switch ($month) {
                case 1:
                        $PREVMONTH=12;
                        $PREVYEAR=$year-1;
                break;
                case 12:
                        $PREVMONTH=11;
                        $PREVYEAR=$year;
                break;
                default:
                        $PREVMONTH=$month-1;
                        $PREVYEAR=$year;
    }
    $sql="SELECT SUM(awj.amount) AS amount, 'current' AS month FROM
    apiwalletjournal awj
    JOIN apireftypes art
    ON awj.refTypeID=art.refTypeID
    WHERE awj.date BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
    AND awj.`argID1`=$planetItemID
    AND awj.refTypeID IN (96, 97)
    UNION
    SELECT SUM(awj.amount) AS amount, 'previous' AS month FROM
    apiwalletjournal awj
    JOIN apireftypes art
    ON awj.refTypeID=art.refTypeID
    WHERE awj.date BETWEEN '".sprintf("%04d", $PREVYEAR)."-".sprintf("%02d", $PREVMONTH)."-01' AND LAST_DAY('".sprintf("%04d", $PREVYEAR)."-".sprintf("%02d", $PREVMONTH)."-01')
    AND awj.`argID1`=$planetItemID
    AND awj.refTypeID IN (96, 97);";
    $poco_raw=db_asocquery($sql);
    return $poco_raw;
}

function showPocoIncome($raw) {
    $TABWIDTH='1016px';
    $day=date('j'); $days=date('t');
    global $DECIMAL_SEP, $THOUSAND_SEP;
    if (count($raw)==2) {
    ?>
    <table class="lmframework" style="width: <?php echo($TABWIDTH); ?>;" id="income">
        <tr><th>
                Previous month income
        </th><th>
                This month income
        </th>
        </tr>		
        <tr><td style="text-align: center;">
            <?php echo(number_format($raw[1]['amount'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK
        </td><td style="text-align: center;">
            <?php echo(number_format($raw[0]['amount'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK
            (Estimated: <?php echo(number_format($raw[0]['amount']/($day/$days), 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK)
        </td>
    </table>
    <?php
    }
}

function getPocoClients($planetItemID) {
    $year=date("Y"); $month=date("m");
    $sql="SELECT MAX( date ) AS lastAccess, COUNT( * ) AS timesAccessed, SUM(amount) AS taxPaid, ownerID1 As characterID, ownerName1 AS characterName
FROM `apiwalletjournal`
WHERE `argID1`=$planetItemID
AND `date` BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)
GROUP BY `ownerID1`
ORDER BY `taxPaid` DESC;";
    return db_asocquery($sql);
}

function showPocoClients($clients) {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    
    if (count($clients)>0) {
        ?>
        <table class="lmframework" id="pococlients">
        <tr><th style="width: 32px; padding: 0px; text-align: center;">

        </th><th style="text-align: center;">
                Character Name
        </th><th style="text-align: center;">
                Tax paid
        </th><th style="text-align: center;">
                Times accessed
        </th><th style="text-align: center;">
                Last access
        </th>
        </tr>
        <?php
        foreach ($clients as $row) {
            echo('<tr><td style="width: 32px; padding: 0px; text-align: center;">');
                outsiderhrefedit($row['characterName']);
                    echo("<img src=\"https://imageserver.eveonline.com/character/${row['characterID']}_32.jpg\" title=\"${row['characterName']}\" />");
                echo('</a>');
            echo('</td><td style="text-align: left;">');
                outsiderhrefedit($row['characterName']);
                    echo($row['characterName']);
                echo('</a>');
            echo('</td><td style="text-align: right;">');
                    echo(number_format($row['taxPaid'], 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK');
            echo('</td><td style="text-align: center;">');
                    echo($row['timesAccessed']);
            echo('</td><td style="text-align: left;">');
                    echo($row['lastAccess']);
            echo('</td></tr>');
        }
        ?>
        </table>
        <?php
    } else {
        echo('No clients');
    }
}

function showPocos($pocos, $income=null) {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    $TABWIDTH='1024px';
        if (count($pocos)>0) {
            //find max monthly income for percentage scaling
            $maxIncome=0.0;
            foreach ($pocos as $row) {
                if ($row['planetIncome']>$maxIncome) $maxIncome=$row['planetIncome'];
            }
            //display header
			?>
			<table class="lmframework" style="width: 90%; min-width: <?php echo($TABWIDTH); ?>" id="pocos">
			<tr><th style="width: 64px; padding: 0px; text-align: center;" rowspan="2">
				Icon
			</th><th style="width: 100px; text-align: center;" rowspan="2">
				Location
			</th><th style="width: 64px; text-align: center;" rowspan="2">
				Reinforced Hours
			</th><th style="width: 64px; text-align: center;" rowspan="2">
				Allow Alliance
			</th><th style="width: 64px; text-align: center;" rowspan="2">
				Allow Standings
			</th><th style="width: 64px; text-align: center;" rowspan="2">
				Min Standings
			</th><th colspan="7" style="text-align: center;">
				Tax rates
			</th>
			</tr>
			<tr>
			<th style="width: 64px;">
				Alliance
			</th><th style="width: 64px; text-align: center;">
				Corp
			</th><th style="width: 64px; text-align: center;">
				Excellent Standing
			</th><th style="width: 64px; text-align: center;">
				Good Standing
			</th><th style="width: 64px; text-align: center;">
				Neutral Standing
			</th><th style="width: 64px; text-align: center;">
				Bad Standing
			</th><th style="width: 64px; text-align: center;">
				Horrible Standing
			</th>
			</tr>
            <?php
            //walk each PoCo
            foreach ($pocos as $row) {
            ?>
                <tr><td style="padding: 0px; text-align: center;">
                    <?php 
                    echo("<a href=\"?id=10&id2=1&nr=2233\"><img src=\"".getTypeIDicon(2233)."\" title=\"Customs Office\" /></a>");
                    echo("<a href=\"?id=10&id2=1&nr=".$row['planetTypeID']."\"><img src=\"".getTypeIDicon($row['planetTypeID'])."\" title=\"".$row['planetTypeName']."\" /></a>");
                    ?>
                    
                </td>
                    <?php

                      $perc=round(100*$row['planetIncome']/$maxIncome);
                      $good=array(0,192,0,0.5);
                      $bad=array(192,0,0,0.5);
                      for ($i=0; $i<4; $i++) {
                          $color[$i] = round ($bad[$i] + ($good[$i]-$bad[$i])*$perc/100);
                          //echo("good[$i]=".$good[$i]." bad[$i]=".$bad[$i]." color[$i]=".$color[$i]."<br/>");
                      }
                      $bar_color='rgba('.$color[0].','.$color[1].','.$color[2].','.$color[3].')';
                      $empty_color='rgba(0,0,0,0.0)';
                      $perc.='%';
                      echo("<td style=\"background: -webkit-gradient(linear, left top, right top, color-stop($perc,$bar_color), color-stop($perc,$empty_color));
                            background: -moz-linear-gradient(left center, $bar_color $perc, $empty_color $perc);
                            background: -o-linear-gradient(left, $bar_color $perc, $empty_color $perc);
                            background: linear-gradient(to right, $bar_color $perc, $empty_color $perc);\">");                  
                      echo('<span title="Income in last 30 days: '.number_format($row['planetIncome'], 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK">');
                      pocohrefedit($row['planetItemID']);

                          echo($row['planetName']);
                          echo('</a></span>');
                    ?>
                </td><td style="text-align: center;">
                    <?php echo( ($row['reinforceHour']-1) .'-'. ($row['reinforceHour']+1 )); ?> 
                </td><td style="text-align: center;">
                    <?php if ($row['allowAlliance']==0) echo('No'); else echo('Yes'); ?>
                </td><td style="text-align: center;">
                    <?php if ($row['allowStandings']==0) echo('No'); else echo('Yes'); ?> 
                </td><td style="text-align: center;">
                    <?php echo($row['standingLevel']);  ?>
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateAlliance']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateCorp']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingHigh']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingGood']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingNeutral']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingBad']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingHorrible']);  ?>%
                </td>
                </tr>
            <?php
            }
            ?>
			</table>
			<?php
            if (!is_null($income)) showPocoIncome($income);
        } else {
		echo('<table class="lmframework" style="width: '.$TABWIDTH.';"><tr><th style="text-align: center;">Corporation doesn\'t have any POCOs</th</tr></table>');
        }
        
    
}

function getCorp($corporationID) {
    $ret=db_asocquery("SELECT * FROM apicorps WHERE `corporationID`=$corporationID;");
    if (count($ret)>0) {
        $ret=$ret[0];
        return $ret;
    } else {
        return FALSE;
    }
}

function showPocoDetail($pocos,$income=null) {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    $TABWIDTH='1016px';
        if (count($pocos)>0) {
            //find max monthly income for percentage scaling
            $maxIncome=0.0;
            foreach ($pocos as $row) {
                if ($row['planetIncome']>$maxIncome) $maxIncome=$row['planetIncome'];
            }
            //display header
			?>
                        <table class="lmframework" style="width: <?php echo($TABWIDTH); ?>" id="pocos">
			<tr><th style="width: 64px; padding: 0px; text-align: center;">
				Icon
			</th><th style="text-align: center;">
				Location
			</th>
                        <th style="text-align: center;">
				Owner corporation
			</th></tr>
                        <tr><td style="padding: 0px; text-align: center;">
                            <?php 
                            echo("<a href=\"?id=10&id2=1&nr=".$row['planetTypeID']."\"><img src=\"".getTypeIDicon($row['planetTypeID'],64)."\" title=\"".$row['planetTypeName']."\" /></a>");
                            ?>
			</td><td style="text-align: center;">
                            <h2><?=$row['planetName']?></h2>
                            <?=$row['planetTypeName']?>
                        </td><td style="text-align: center;">
                            <h2><img src="https://imageserver.eveonline.com/Corporation/<?=$row['corporationID']?>_32.png" style="vertical-align: middle;"> <?php $corp=getCorp($row['corporationID']); echo($corp['corporationName']); ?></h2>
				
			</td></tr>
                        </table>
            
			<table class="lmframework" style="width: <?php echo($TABWIDTH); ?>" id="pocos">
			<tr><th style="width: 64px; text-align: center;" rowspan="2">
				Reinforced Hours
			</th><th style="width: 64px; text-align: center;" rowspan="2">
				Allow Alliance
			</th><th style="width: 64px; text-align: center;" rowspan="2">
				Allow Standings
			</th><th style="width: 64px; text-align: center;" rowspan="2">
				Min Standings
			</th><th colspan="7" style="text-align: center;">
				Tax rates
			</th>
			</tr>
			<tr>
			<th style="width: 64px;">
				Alliance
			</th><th style="width: 64px; text-align: center;">
				Corp
			</th><th style="width: 64px; text-align: center;">
				Excellent Standing
			</th><th style="width: 64px; text-align: center;">
				Good Standing
			</th><th style="width: 64px; text-align: center;">
				Neutral Standing
			</th><th style="width: 64px; text-align: center;">
				Bad Standing
			</th><th style="width: 64px; text-align: center;">
				Horrible Standing
			</th>
			</tr>
            <?php
            //walk each PoCo
            foreach ($pocos as $row) {
            ?>
                <tr><td style="text-align: center;">
                    <?php echo( ($row['reinforceHour']-1) .'-'. ($row['reinforceHour']+1 )); ?> 
                </td><td style="text-align: center;">
                    <?php if ($row['allowAlliance']==0) echo('No'); else echo('Yes'); ?>
                </td><td style="text-align: center;">
                    <?php if ($row['allowStandings']==0) echo('No'); else echo('Yes'); ?> 
                </td><td style="text-align: center;">
                    <?php echo($row['standingLevel']);  ?>
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateAlliance']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateCorp']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingHigh']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingGood']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingNeutral']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingBad']);  ?>%
                </td><td style="text-align: center;">
                    <?php echo(100 * $row['taxRateStandingHorrible']);  ?>%
                </td>
                </tr>
                <tr>
                    <th colspan="3" style="text-align: center;">Income in the last 30 days</th>
                    <th colspan="3" style="text-align: center;">Previous month income</th>
                    <th colspan="5" style="text-align: center;">Current month income</th>
                </tr><tr>
                    <td colspan="3" style="text-align: center;"><?php echo(number_format($row['planetIncome'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK</td>
                    <td colspan="3" style="text-align: center;">
                        <?php
                            if (!is_null($income)) echo(number_format($income[1]['amount'], 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK');
                        ?>
                    </td>
                    <td colspan="5" style="text-align: center;">
                        <?php
                            $day=date('j'); $days=date('t');
                            if (!is_null($income)) echo(number_format($income[0]['amount'], 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK');
                            if (!is_null($income)) echo(' (Estimated: '.number_format($income[0]['amount']/($day/$days), 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK)');
                        ?>
                    </td>
                </tr>
            <?php
            }
            ?>
	    </table>
	    <?php
            //if (!is_null($income)) showPocoIncome($income);
        } else {
		echo('<table class="lmframework" style="width: '.$TABWIDTH.';"><tr><th style="text-align: center;">Corporation doesn\'t have any POCOs</th</tr></table>');
        }
        
    
}

function getStock($where='TRUE') {
    global $LM_EVEDB;
    $sql="SELECT cfs.*,itp.`typeName`,apa.*,apl.`itemName` AS locationName,app.`max` as price,itp.`groupID`, igp.`groupName` 
        FROM `cfgstock` cfs
        JOIN $LM_EVEDB.`invTypes` itp
        ON cfs.`typeID`=itp.`typeID`
        JOIN $LM_EVEDB.`invGroups` igp
        ON itp.`groupID`=igp.`groupID`
        LEFT JOIN `apiprices` app
        ON cfs.`typeID`=app.`typeID`
        JOIN `apiassets` apa
        ON cfs.`typeID`=apa.`typeID`
        LEFT JOIN $LM_EVEDB.`mapDenormalize` apl
        ON apa.`locationID`=apl.`itemID`
        WHERE $where AND (app.type='buy' OR app.type IS NULL)
        ORDER BY itp.`groupID`, itp.`typeName`;";
    //echo("DEBUG: $sql");
    $rawdata=db_asocquery($sql);
    //Data transformation (rows -> structure)
    $inventory=array();
    foreach ($rawdata as $row) {
        $inventory[$row['groupID']]['groupID']=$row['groupID'];
        $inventory[$row['groupID']]['groupName']=$row['groupName'];
        $inventory[$row['groupID']]['types'][$row['typeID']]['typeID']=$row['typeID'];
        $inventory[$row['groupID']]['types'][$row['typeID']]['typeName']=$row['typeName'];
        $inventory[$row['groupID']]['types'][$row['typeID']]['amount']=$row['amount']; //required amount
        $inventory[$row['groupID']]['types'][$row['typeID']]['quantity']+=$row['quantity']; //actual amount
        if (!empty($row['price'])) {
            $inventory[$row['groupID']]['types'][$row['typeID']]['value']+=$row['price']*$row['quantity']; //value = price * actual amount
            $inventory[$row['groupID']]['types'][$row['typeID']]['price']=$row['price'];
        } else {
            $inventory[$row['groupID']]['types'][$row['typeID']]['value']+=0; //value = price * actual amount
            $inventory[$row['groupID']]['types'][$row['typeID']]['price']=0;
        }
        if (!empty($row['locationID']) && !empty($row['locationName'])) {
            $inventory[$row['groupID']]['types'][$row['typeID']]['locations'][$row['locationID']]['locationID']=$row['locationID']; //location
            $inventory[$row['groupID']]['types'][$row['typeID']]['locations'][$row['locationID']]['locationName']=$row['locationName']; //location name
        }
        //flags in future
    }
    return($inventory);
}

function showStock($inventory, $corpID) {
    global $LM_BUYCALC_SHOWHINTS;
    $LM_HINTGREEN='We need this, and will be happy to buy it.';
    $LM_HINTYELLOW='We *can* buy this, but we would prefer something green instead.';
    $LM_HINTRED='We don\'t need this right now.';
    $LM_HINTGREENIMG='ccp_icons/38_16_183.png';
    $LM_HINTYELLOWIMG='ccp_icons/38_16_167.png';
    $LM_HINTREDIMG='ccp_icons/38_16_151.png';
    $LM_HINTLOW=100;
    $LM_HINTHIGH=200;
    global $DECIMAL_SEP, $THOUSAND_SEP;
    foreach($inventory as $groupID => $group) {
        $subtotal=0;
    ?>
    <table class="lmframework" style="width: 70%; min-width: 595px;" id="inv_group_name_<?php echo($corpID.'_'.$group['groupID']); ?>" title="Click to show/hide items in this group" onclick="div_toggler('inv_group_<?php echo($corpID.'_'.$group['groupID']); ?>')">
        <tr><th style="width: 100%; text-align: center;"><img src="<?=getUrl()?>img/plus.gif" style="float: left;"/> <?php echo($group['groupName']); ?></th></tr>
    </table>
    
<div id="inv_group_<?php echo($corpID.'_'.$group['groupID']); ?>" style="display: none;">
    <table class="lmframework" style="width: 70%; min-width: 595px;" id="">
        <script type="text/javascript">rememberToggleDiv('inv_group_<?php echo($corpID.'_'.$group['groupID']); ?>');</script>
        <tr><td style="width: 32px; padding: 0px; text-align: center;">
            Icon
        </td><td style="width: 30%; min-width: 119px;">
            Type Name
        </td><td style="width: 15%; min-width: 90px;"">
            Current Amount
        </td><td style="width: 15%; min-width: 90px;">
            Required Amount
        </td><td style="width: 110px;">
            Percentage
        </td><td style="width: 15%; min-width: 100px;">
            Value
        </td>
        </tr>
        <?php
        foreach ($group['types'] as $typeID => $row) {
            ?>
            <tr><td width="32" style="padding: 0px; text-align: center;">
                <?php dbhrefedit($row['typeID']); echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />"); echo('</a>'); ?>
            </td><td>
                <?php dbhrefedit($row['typeID']);
                if (($LM_BUYCALC_SHOWHINTS) && (isset($inventory[$groupID]['types'][$typeID]['amount'])) && (isset($inventory[$groupID]['types'][$typeID]['quantity']))) {
                                        //if we have corresponding typeID with amount and quantity
                                        $amount=$inventory[$groupID]['types'][$typeID]['amount']; //required amount
                                        $quantity=$inventory[$groupID]['types'][$typeID]['quantity']; //actual quantity
                                        if ($amount>0) {
                                            $percent=100*$quantity/$amount;
                                            if ($percent < $LM_HINTLOW) {
                                                echo('<img src="'.getUrl().$LM_HINTGREENIMG.'" style="display: inline; vertical-align:bottom;  margin: 0 5px;" title="'.$LM_HINTGREEN.'" />');
                                            } else if ($percent < $LM_HINTHIGH) {
                                                echo('<img src="'.getUrl().$LM_HINTYELLOWIMG.'" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="'.$LM_HINTYELLOW.'" />');
                                            } else {
                                                echo('<img src="'.getUrl().$LM_HINTREDIMG.'" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="'.$LM_HINTRED.'" />');
                                            }
                                        }
                                    }
                echo($row['typeName']); echo('</a>'); ?>
            </td><td style="text-align: right;">
                <?php dbhrefedit($row['typeID']); echo(number_format($row['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP)); echo('</a>'); //actual amount ?> 
            </td><td style="text-align: right;">
                <?php dbhrefedit($row['typeID']); echo(number_format($row['amount'], 0, $DECIMAL_SEP, $THOUSAND_SEP)); echo('</a>'); //required amount ?>
            </td><td><center>
                <?php if ($row['amount'] > 0) {
                        $percent1=round(100*$row['quantity']/$row['amount']);
                      }  else {
                        $percent1=0;
                      }
                      percentbar($percent1,"$percent1 %"); ?>
            </center></td><td style="text-align: right;">
                <?php dbhrefedit($row['typeID']); echo(number_format($row['value'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); $subtotal+=$row['value']; echo('</a>'); ?>
            </td>
            </tr>
            <?php
        }
        ?>
        </table>
</div>
    <table class="lmframework" style="width: 70%; min-width: 595px;" id="group_subtotal_<?php echo($group['groupID']); ?>">
        <tr><td style="width: 32px; min-width: 32px; padding: 0px; text-align: center;">

        </td><td style="width: 30%; min-width: 119px;">
            
        </td><td style="width: 15%; min-width: 90px;"">
            
        </td><td style="width: 15%; min-width: 90px;">
            
        </td><td style="width: 110px; min-width: 100px;">
            
        </td><td style="width: 15%; min-width: 100px; text-align: right;">
            <?php echo(number_format($subtotal, 2, $DECIMAL_SEP, $THOUSAND_SEP)); $total+=$subtotal; ?>
        </td>
        </tr>
    </table>
    <?php
    }
    ?>
    <table class="lmframework" style="width: 70%; min-width: 595px;">
        <tr><th style="width: 75%;">
              Total
        </th><th style="width: 25%; text-align: right;">
             <?php echo(number_format($total, 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?>   
        </th></tr>
    </table>
    <?php
    
}
?>
