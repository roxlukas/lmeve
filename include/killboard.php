<?php
include_once('inventory.php');

function getKill($killID) {
    global $LM_EVEDB;
    $sql="SELECT apk.*,akv.*,itp.`typeName` AS `shipTypeName`,
    mas.`solarSystemName`, mas.`security` AS `solarSystemSec`, mar.`regionName`
    FROM `apikills` apk 
    JOIN `apikillvictims` akv
    ON apk.`killID` = akv.`killID`
    JOIN `$LM_EVEDB`.`invTypes` itp
    ON akv.`shipTypeID`=itp.`typeID`
    JOIN `$LM_EVEDB`.`mapSolarSystems` mas
    ON apk.`solarSystemID`=mas.`solarSystemID`
    JOIN `$LM_EVEDB`.`mapRegions` mar
    ON mas.`regionID`=mar.`regionID`
    WHERE apk.`killID`=$killID;";
    $kills=db_asocquery($sql);
    if (count($kills)>0) {
        $kill=$kills[0];
        $kill['involved']=getAttackers($killID);
        $kill['items']=getDestroyedItems($killID);
        $kill['graphics']=getDestroyedItems($killID,TRUE);
        return $kill;
    } else return FALSE;
}

/*
    sha1(victimCharacterID + attackerCharacterID + shipTypeID + killTime)

    Where:

    victimCharacterID = the character ID of the victim. If the characterID == 0, such as a kill for a POS mod or similar, this is the string "None" instead
    attackerCharacterID = the character ID of the attacker that got the final blow. If the characterID == 0, such as an NPC, this is the string "None" instead
    shipTypeID = the typeID of the victim's ship
    killTime = a 64-bit timestamp. CCP luckily doesn't store precision greater than 1 second otherwise we couldn't do this, so you can use the following math to convert from a regular unix timestamp: (unixtime * 10000000) + 116444736000000000 
    */
function killmail_hash($victimCharacterID,$attackerCharacterID,$shipTypeID,$killTime) {
    $unixtime=number_format((strtotime($killTime.' UTC') * 10000000) + 116444736000000000,0,'',''); //'UTC' is there because otherwise strtotime() takes OS default timezone into account
    if ($victimCharacterID==0) $victimCharacterID='None';
    if ($attackerCharacterID==0) $attackerCharacterID='None';
    $str="$victimCharacterID$attackerCharacterID$shipTypeID$unixtime";
    $hash=sha1($str);
    //inform('killmail_hash()',"$str sha1 hash=$hash");
    return $hash;
}

function getAttackers($killID) {
    $sql="SELECT * FROM `apikillattackers` 
        WHERE `killID`=$killID
        ORDER BY `damageDone` DESC;";
    return db_asocquery($sql);
}

function getDestroyedItems($killID,$getGraphics=FALSE) {
    global $LM_EVEDB;
    if ($getGraphics===TRUE) {
        $sql="SELECT aki.*,itp.`typeName`,igp.`groupID`,igp.`groupName`,igp.`categoryID`,ygi.`graphicFile`,cmp.`averagePrice` 
        FROM `apikillitems` aki
        JOIN `$LM_EVEDB`.`invTypes` itp
        ON aki.`typeID`=itp.`typeID`
        JOIN `$LM_EVEDB`.`invGroups` igp
        ON itp.`groupID`=igp.`groupID`
        JOIN `$LM_EVEDB`.`yamlTypeIDs` yti
        ON aki.`typeID`=yti.`typeID`
        JOIN `$LM_EVEDB`.`yamlGraphicIDs` ygi
        ON yti.`graphicID`=ygi.`graphicID`
        LEFT JOIN `crestmarketprices` cmp
        ON aki.`typeID`=cmp.`typeID`
        WHERE `killID`=$killID AND igp.`categoryID`=7 AND ygi.`graphicFile` IS NOT NULL AND aki.`flag` BETWEEN 27 AND 34
        ORDER BY `flag`;";
    } else {
        $sql="SELECT aki.*,itp.`typeName`,igp.`groupID`,igp.`groupName`,igp.`categoryID`,cmp.`averagePrice` 
        FROM `apikillitems` aki
        JOIN `$LM_EVEDB`.`invTypes` itp
        ON aki.`typeID`=itp.`typeID`
        JOIN `$LM_EVEDB`.`invGroups` igp
        ON itp.`groupID`=igp.`groupID`
        LEFT JOIN `crestmarketprices` cmp
        ON aki.`typeID`=cmp.`typeID`
        WHERE `killID`=$killID
        ORDER BY `flag`;";
    }
    //$items=killMailToInventory(db_asocquery($sql));
    $items=db_asocquery($sql);
    //echo("<pre>".var_dump($items)."</pre>");
    return $items;
}

function getKills($month,$year,$corporationID=0,$allianceID=0,$characterID=0,$solarSystemID=0,$limit_records=0,$limit_offset=0) {
    global $LM_EVEDB;
    if ($corporationID>0) $whereCorp="akv.`corporationID`=$corporationID OR aka.`corporationID`=$corporationID OR inv.`corporationID`=$corporationID"; else $whereCorp="TRUE";
    if ($characterID>0) $whereChar="akv.`characterID`=$characterID OR aka.`characterID`=$characterID OR inv.`characterID`=$characterID"; else $whereChar="TRUE";
    if ($allianceID>0) $whereAlly="akv.`allianceID`=$allianceID OR aka.`allianceID`=$allianceID OR inv.`allianceID`=$allianceID"; else $whereAlly="TRUE";
    if ($solarSystemID>0) $whereSystem="apk.`solarSystemID`=$solarSystemID"; else $whereSystem="TRUE";
    if ($limit_records>0) $limit="LIMIT $limit_records"; else if ($limit_records>0 && $limit_offset>0) $limit="LIMIT $limit_records,$limit_offset"; else $limit="";
    if ($year>0 && $month>0) $whereTime="apk.`killTime` BETWEEN '${year}-${month}-01' AND DATE_ADD(LAST_DAY('${year}-${month}-01'), INTERVAL 1 day)"; else $whereTime="TRUE";
    $sql="SELECT DISTINCT apk.*,akv.*,
    aka.`characterID` AS atkCharacterID,
    aka.`corporationID` AS atkCorporationID,
    aka.`allianceID` AS atkAllianceID,
    aka.`characterName` AS atkCharacterName,
    aka.`corporationName` AS atkCorporationName,
    aka.`allianceName` AS atkAllianceName,
    involved.`involved`,
    mas.`solarSystemName`, mas.`security` AS `solarSystemSec`, mar.`regionName`, itp.`typeName` AS `shipTypeName`
    FROM `apikills` apk
    JOIN `apikillvictims` akv
    ON apk.`killID`=akv.`killID`
    JOIN `apikillattackers` aka
    ON apk.`killID`=aka.`killID` AND aka.`finalBlow`=1
    JOIN `apikillattackers` inv
    ON apk.`killID`=inv.`killID`
    JOIN (SELECT `killID`,COUNT(*) AS involved FROM `apikillattackers` GROUP BY `killID`) AS involved
    ON apk.`killID`=involved.`killID`
    JOIN `$LM_EVEDB`.`mapSolarSystems` mas
    ON apk.`solarSystemID`=mas.`solarSystemID`
    JOIN `$LM_EVEDB`.`mapRegions` mar
    ON mas.`regionID`=mar.`regionID`
    JOIN `$LM_EVEDB`.`invTypes` itp
    ON akv.`shipTypeID`=itp.`typeID`
    WHERE $whereTime
    AND $whereCorp
    AND $whereChar
    AND $whereAlly
    AND $whereSystem
    ORDER BY `killTime` DESC
    $limit;";
    $kills=db_asocquery($sql);
    return $kills;
}

function killhrefedit($killID) {
    echo("<a href=\"?id=12&id2=1&killID=$killID\" title=\"Click to open killmail\">");
}

function charkillshrefedit($id) {
    echo("<a href=\"?id=12&id2=0&characterID=$id\" title=\"Click to find more kills by this character\">");
}

function corpkillshrefedit($id) {
    echo("<a href=\"?id=12&id2=0&corporationID=$id\" title=\"Click to find more kills by this corporation\">");
}

function allykillshrefedit($id) {
    echo("<a href=\"?id=12&id2=0&allianceID=$id\" title=\"Click to find more kills by this alliance\">");
}

function systemkillshrefedit($id) {
    echo("<a href=\"?id=12&id2=0&solarSystemID=$id\" title=\"Click to find more kills in this system\">");
}

function secStatusColor($security) {
	//if ($security < 0.1) return '#F30202';
	switch ($security) {
		case 0.1:
			return '#DC3201';
		case 0.2:
			return '#EB4903';
		case 0.3:
			return '#F66301';
		case 0.4:
			return '#E58000';
		case 0.5:
			return '#F5F501';
		case 0.6:
			return '#96F933';
		case 0.7:
			return '#00FF00';
		case 0.8:
			return '#02F34B';
		case 0.9:
			return '#4BF3C3';
		case 1.0:
			return '#33F9F9';
	}
	return '#F30202';
}

function getKbMinWidth() {
    return '100%';
}

function killMailToInventory($kill_items) {
    $new_kill_items=array();
    $lowslot=11; $lastlowslot=$lowslot;
    $midslot=19; $lastmidslot=$midslot;
    $highslot=27; $lasthighslot=$highslot;
    $rigslot=92; $lastrigslot=$rigslot;
    if (count($kill_items)>0) foreach ($kill_items as $row) {
        //$count=$row['qtyDestroyed']+$row['qtyDropped'];
        //echo('flag='.$row['flag'].'<br/>');
        if ($row['flag'] >= 11 && $row['flag']<19 && $row['categoryID']==7) {
            for ($i=$lastlowslot; $i<=$row['flag']; $i++) {
                //echo($row['typeName'].' '.$i.'<br/>');
                $tmp=$row; $tmp['flag']=$i;
                array_push($new_kill_items,$tmp);
            }
            $lastlowslot=$row['flag']+1;
        } else if ($row['flag'] >= 19 && $row['flag']<27 && $row['categoryID']==7) {
            for ($i=$lastmidslot; $i<=$row['flag']; $i++) {
                //echo($row['typeName'].' '.$i.'<br/>');
                $tmp=$row; $tmp['flag']=$i;
                array_push($new_kill_items,$tmp);
            }
            $lastmidslot=$row['flag']+1;
        } else if ($row['flag'] >= 27 && $row['flag']<=34 && $row['categoryID']==7) {
            for ($i=$lasthighslot; $i<=$row['flag']; $i++) {
                //echo($row['typeName'].' '.$i.'<br/>');
                $tmp=$row; $tmp['flag']=$i;
                array_push($new_kill_items,$tmp);
            }
            $lasthighslot=$row['flag']+1;
        } else if ($row['flag'] >= 92 && $row['flag']<=94 && $row['categoryID']==7) {
            for ($i=$lastrigslot; $i<=$row['flag']; $i++) {
                //echo($row['typeName'].' '.$i.'<br/>');
                $tmp=$row; $tmp['flag']=$i;
                array_push($new_kill_items,$tmp);
            }
            $lastrigslot=$row['flag']+1;
        } else {
            array_push($new_kill_items,$row);
        }
    }
    //echo('<pre>'); var_dump($new_kill_items);echo('</pre>');
    return $new_kill_items;
}

function showKills($kills) {
    ?>
    <center>
    <table class="lmframework" style="width: 95%; min-width: 600px; max-width: 1280px;">
    <?php
    if(count($kills)>0) {
        ?><tr><th>Date</th><th></th><th>Location</th><th colspan="2">Victim</th><th colspan="2">Final Blow</th></tr><?php
        foreach ($kills as $kill) {
            ?>
            <tr>
                <td style="min-width: 56px;"><?=str_replace(' ', '<br />', $kill['killTime'])?></td>
                <td style="padding: 0px; width: 48px;"><?php killhrefedit($kill['killID']); ?><img src="<?=getTypeIDicon($kill['shipTypeID'],64)?>" alt="" style="width: 48px; height: 48px" /></a></td>
                <td style="min-width: 90px;"><strong><?php systemkillshrefedit($kill['solarSystemID']); ?><?=$kill['solarSystemName']?> <span style="color: <?=  secStatusColor(round($kill['solarSystemSec'],1))?>;"><?=round($kill['solarSystemSec'],1)?></span></a></strong><br/>
                    <?=$kill['regionName']?>
                </td>
                <td style="padding: 0px; width: 48px;"><?php
                    if ($kill['allianceID']==0) {
                        corpkillshrefedit($kill['corporationID']);
                        echo("<img src=\"https://imageserver.eveonline.com/Corporation/${kill['corporationID']}_64.png\" alt=\"\" style=\"width: 48px; height: 48px\" /></a>");
                    } else {
                        allykillshrefedit($kill['allianceID']);
                        echo("<img src=\"https://imageserver.eveonline.com/Alliance/${kill['allianceID']}_64.png\" alt=\"\" style=\"width: 48px; height: 48px\" /></a>");
                    }
                ?></td>
                <td><strong><?php charkillshrefedit($kill['characterID']); ?><?=$kill['characterName']?></a></strong>
                    (<?php dbhrefedit($kill['shipTypeID']); ?><?=$kill['shipTypeName']?></a>)<br/>
                    <?php corpkillshrefedit($kill['corporationID']); ?>
                    <?=$kill['corporationName']?></a> 
                    <?php if ($kill['allianceName']!='') { echo("/ "); allykillshrefedit($kill['allianceID']); echo($kill['allianceName']); echo('</a>'); } ?>
                </td>
                <td style="padding: 0px; width: 48px;"><?php
                    if ($kill['atkAllianceID']==0) {
                        corpkillshrefedit($kill['atkCorporationID']);
                        echo("<img src=\"https://imageserver.eveonline.com/Corporation/${kill['atkCorporationID']}_64.png\" alt=\"\" style=\"width: 48px; height: 48px\" /></a>");
                    } else {
                        allykillshrefedit($kill['atkAllianceID']);
                        echo("<img src=\"https://imageserver.eveonline.com/Alliance/${kill['atkAllianceID']}_64.png\" alt=\"\" style=\"width: 48px; height: 48px\" /></a>");
                    }
                ?></td>
                <td><strong><?php charkillshrefedit($kill['atkCharacterID']); ?><?=$kill['atkCharacterName']?></a></strong>
                    (<?=$kill['involved']?>)<br/>
                    <?php corpkillshrefedit($kill['atkCorporationID']); ?>
                    <?=$kill['atkCorporationName']?></a>
                    <?php if ($kill['atkAllianceName']!='') { echo("/ "); allykillshrefedit($kill['atkAllianceID']); echo($kill['atkAllianceName']); echo('</a>'); } ?>
                </td>
            </tr>
            <?php
        }
        
    } else {
        ?><tr><th style="text-align: center;">No kills found.</th></tr><?php
    }
    ?>
    </table></center>
    <?php
}

function showCharacter($char,$showCorpLogos=FALSE,$skipHTMLTable=FALSE) {
    if (!$skipHTMLTable) echo('<table class="lmframework" style="min-width: '.getKbMinWidth().';"><tr>');
    ?>
    <td style="padding: 0px; width: 64px;">
         <?php charkillshrefedit($char['characterID']); echo("<img src=\"https://imageserver.eveonline.com/Character/${char['characterID']}_64.jpg\" alt=\"\" /></a>"); ?>
    </td>
    <td style="padding: 0px; width: 32px; vertical-align: top;">
        <?php if($showCorpLogos) {
            corpkillshrefedit($char['corporationID']); ?><img src="https://imageserver.eveonline.com/Corporation/<?=$char['corporationID']?>_32.png" alt="" /></a><br/><?php 
            if ($char['allianceID']>0) { allykillshrefedit($char['allianceID']); ?><img src="https://imageserver.eveonline.com/Alliance/<?=$char['allianceID']?>_32.png" alt="" /></a><?php }
        } else {
            dbhrefedit($char['shipTypeID']); ?><img src="<?=getTypeIDicon($char['shipTypeID'],32)?>" alt="" /></a><br/><?php 
            dbhrefedit($char['weaponTypeID']); ?><img src="<?=getTypeIDicon($char['weaponTypeID'],32)?>" alt="" /></a>     
        <?php }
        ?>
    </td>
    <td style="text-align: left; min-width: 150px;">
         <strong><?php charkillshrefedit($char['characterID']); ?><?=$char['characterName']?></a></strong><br/>
         <?php corpkillshrefedit($char['corporationID']); ?><?=$char['corporationName']?></a><br/>
         <?php allykillshrefedit($char['allianceID']); ?><?=$char['allianceName']?></a><br/>
    </td>
    <?php
    if (!$skipHTMLTable) echo('<table><tr>');
}

function showInvolved($involved) {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    ?>
    <table class="lmframework" style="min-width: <?=getKbMinWidth()?>;">
    <?php
    if(count($involved)>0) {
        ?><tr><th colspan="3"><?=count($involved)?> Involved</th><th>Damage</th></tr><?php
        foreach ($involved as $row) {
            ?><tr>
            <?php showCharacter($row,FALSE,TRUE); ?>
            <td style="text-align: center;">
                 <h3><?=number_format($row['damageDone'], 0, $DECIMAL_SEP, $THOUSAND_SEP)?></h3>
                 <?php if($row['finalBlow']==1) echo('Final blow'); ?>
            </td>
            </tr>
            <?php
        }
    } else {
        ?><tr><th style="text-align: center;">No data.</th></tr><?php
    }
    ?>
    </table>
    <?php
}

function getAveragePrice($typeID) {
    if (empty($typeID)) return FALSE;
    global $LM_EVEDB;
        $sql="SELECT `typeID`,`averagePrice` 
        FROM `crestmarketprices` cmp
        WHERE `typeID`=$typeID;";
    $ret=db_asocquery($sql);
    if (count($ret)>0) return $ret[0]['averagePrice']; else return FALSE;
}

function getFinalBlowCharID($attackers) {
    if (count($attackers)>0) {
        foreach ($attackers as $attacker) {
            if ($attacker['finalBlow']==1) return $attacker['characterID'];
        }
    } else return FALSE;
}

function showVictim($victim) {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    $items=$victim['items'];
    ?>
    <table class="lmframework" style="min-width: <?=getKbMinWidth()?>;">
    <?php
    if(count($victim)>0) {
        $iskLost=0;
        $iskDropped=0;
        $iskShip=getAveragePrice($victim['shipTypeID']);
        if (count($items)>0) {
            foreach($items as $item) {
                $iskLost+=$item['qtyDestroyed']*$item['averagePrice'];
                $iskDropped+=$item['qtyDropped']*$item['averagePrice'];
            }
        }
        ?><tr><th colspan="3">Victim</th><th>Damage</th></tr>
            <tr>
            <?php showCharacter($victim,TRUE,TRUE); ?>
            <td style="text-align: center;">
                 <h3><?=number_format($victim['damageTaken'], 0, $DECIMAL_SEP, $THOUSAND_SEP)?></h3>
            </td>
            </tr>
            <tr>
                <th style="text-align: right;" colspan="2">Ship:</th>
                <td style="text-align: left;" colspan="2"><?=$victim['shipTypeName']?></td>
            </tr>
            <tr>
                <th style="text-align: right;" colspan="2">System:</th>
                <td style="text-align: left;" colspan="2"><?php systemkillshrefedit($victim['solarSystemID']); ?><?=$victim['solarSystemName']?> (<span style="color: <?=  secStatusColor(round($victim['solarSystemSec'],1))?>;"><?=round($victim['solarSystemSec'],1)?></span>)</a> / <?=$victim['regionName']?></td>
            </tr>
            <tr>
                <th style="text-align: right;" colspan="2">Time:</th>
                <td style="text-align: left;" colspan="2"><?=$victim['killTime']?></td>
            </tr>
            <tr>
                <th style="text-align: right;" colspan="2">Dropped:</th>
                <td style="text-align: left;" colspan="2"><?=number_format($iskDropped, 0, $DECIMAL_SEP, $THOUSAND_SEP)?> ISK</td>
            </tr>
            <tr>
                <th style="text-align: right;" colspan="2">Destroyed:</th>
                <td style="text-align: left;" colspan="2"><span style="color: red;"><?=number_format($iskLost+$iskShip, 0, $DECIMAL_SEP, $THOUSAND_SEP)?> ISK</span></td>
            </tr>
            
            <tr>
                <th style="text-align: right;" colspan="2">Total:</th>
                <th style="text-align: left;" colspan="2"><?=number_format($iskDropped+$iskLost+$iskShip, 0, $DECIMAL_SEP, $THOUSAND_SEP)?> ISK</th>
            </tr>
            <tr>
                <th style="text-align: right;" colspan="4"><a href="https://public-crest.eveonline.com/killmails/<?=$victim['killID']?>/<?=killmail_hash($victim['characterID'], getFinalBlowCharID($victim['involved']), $victim['shipTypeID'], $victim['killTime'])?>/" target="_blank" style="color: green;"><img src="<?=getUrl()?>ccp_icons/38_16_193.png" alt="" style="vertical-align: middle;"/> CREST verified</a></th>
            </tr>
            <?php
        
    } else {
        ?><tr><th style="text-align: center;">No data.</th></tr><?php
    }
    ?>
    </table>
    <?php
}

function showKill($kill) {
    if ($kill===FALSE) {
        echo("No such killID");
        return FALSE;
    }
    ?>
    <table style="width: 100%; max-width: 1280px;"><tr><td style="width: 636px; vertical-align: top;">
    <?php
        showInventoryFitting($kill['items'], $kill['shipTypeID'],TRUE);
    ?>
    <td style="vertical-align: top;"> 
    <?php
        showVictim($kill);
        showInvolved($kill['involved']);
    ?>
    </td></tr></table>
    <?php
    generateTurretCode($kill['graphics']);
}

function generateTurretCode($graphics) {
    $index=1;
    if (count($graphics)>0) {
        ?><script type="text/javascript">
        <?php
        foreach ($graphics as $turret) {
            ?>
                loadTurret(<?=$index++?>, '<?=$turret['graphicFile']?>');
            <?php
        }
        ?></script><?php
    }
}

function showSummary($kills) {
    
}
?>
