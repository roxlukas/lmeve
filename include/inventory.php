<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once("percentage.php");

function dbhrefedit($nr) {
    echo("<a href=\"index.php?id=10&id2=1&nr=$nr\" title=\"Click to open database\">");
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

function getControlTowers($where='TRUE') {
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
    global $LM_EVEDB;
    $sql="SELECT lmt.*,itp.`typeName`,acm.`name` AS characterName FROM `lmtasks` lmt
    JOIN $LM_EVEDB.`invTypes` itp
    ON lmt.`typeID`=itp.`typeID`
    LEFT JOIN `apicorpmembers` acm
    ON lmt.`characterID`=acm.`characterID`
    WHERE $where";
    $raw=db_asocquery($sql);
    return($raw);
}

function getLabsAndTasks($corporationID) {
    global $LM_EVEDB;
    $raw_towers=getControlTowers("asl.`corporationID`=$corporationID");
    
    $towers=array();
    $labs=array();
    if (count($raw_towers)>0) {
        $raw_tasks=getSimpleTasks();
        foreach($raw_towers as $tower) {
            //var_dump($tower);
            $x=$tower[x];
            $y=$tower[y];
            $z=$tower[z];
            $raw_labs=getLabs("SQRT(POW($x-apl.x,2)+POW($y-apl.y,2)+POW($z-apl.z,2)) < 30000");
            //var_dump($raw_labs);
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
            <tr><th colspan="6" style="text-align: center;">
                <?php echo($tower['moonName'].' ("'.$tower['itemName'].'")'); ?>
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
            </th>
            </tr>
            <?php
            if (count($tower['labs'])>0) foreach ($tower['labs'] as $facilityID => $row) {
                ?>
                <tr><td width="32" style="padding: 0px; text-align: center;">
                    <?php dbhrefedit($row['typeID']); echo("<img src=\"ccp_img/${row['typeID']}_32.png\" title=\"${row['typeName']}\" />"); echo('</a>'); ?>
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
                        echo("<img src=\"https://image.eveonline.com/character/${user}_32.jpg\" title=\"$name\">");
			if ($rights_viewallchars) echo('</a>');
                    }
                    ?>
                </td><td>
                    <?php 
                    if (count($row['products'])>0) foreach ($row['products'] as $product => $name) {
                        dbhrefedit($product);
                        echo("<img src=\"ccp_img/${product}_32.png\" title=\"$name\">");
                        echo('</a>');
                    }
                    ?> 
                </td><td>
                    <?php 
                    labshrefedit($facilityID); echo("<img src=\"ccp_icons/12_64_3.png\" style=\"width: 24px; height: 24px;\" /></span>"); echo('</a>');
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
                <?php towershrefedit($row['itemID']); echo("<img src=\"ccp_img/${row['typeID']}_32.png\" title=\"${row['typeName']}\" />"); echo('</a>'); ?>
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
select (pow(:x-x,2)+pow(:y-y,2)+pow(:z-z,2)) distance,itemName,itemID,typeID
from mapDenormalize
where solarsystemid=:solarsystemid
order by distance asc
limit 1
 */
function getPocos($where='TRUE') {
    global $LM_EVEDB;
    $sql="SELECT apo.*,apl.itemName FROM `apipocolist` apo
    LEFT JOIN `apilocations` apl
    ON apo.`itemID`=apl.`itemID`
    WHERE $where";
    $raw=db_asocquery($sql);
    return($raw);
}

function showPocos($pocos) {
    
        if (count($pocos)>0) {
			?>
			<table class="lmframework" style="width: 984px;" id="pocos">
			<tr><th style="width: 32px; padding: 0px; text-align: center;" rowspan="2">
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
			foreach ($pocos as $row) {
            ?>
            <tr><td width="32" style="padding: 0px; text-align: center;">
                <?php echo("<a href=\"?id=10&id2=1&nr=2233\"><img src=\"ccp_img/2233_32.png\" title=\"Customs Office\" /></a>"); ?>
            </td><td style="">
                <?php if (is_null($row['itemName'])) echo($row['solarSystemName']); else {
                    preg_match('/^Customs Office \(([-_\w\s]+)\)/',$row['itemName'],$m);
                    echo($m[1]);
                } ?>
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
        } else {
		echo('<table class="lmframework" style="width: 984px;"><tr><th style="text-align: center;">Corporation doesn\'t have any POCOs</th</tr></table>');
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
        <tr><th style="width: 100%; text-align: center;"><img src="img/plus.gif" style="float: left;"/> <?php echo($group['groupName']); ?></th></tr>
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
                <?php dbhrefedit($row['typeID']); echo("<img src=\"ccp_img/${row['typeID']}_32.png\" title=\"${row['typeName']}\" />"); echo('</a>'); ?>
            </td><td>
                <?php dbhrefedit($row['typeID']);
                if (($LM_BUYCALC_SHOWHINTS) && (isset($inventory[$groupID]['types'][$typeID]['amount'])) && (isset($inventory[$groupID]['types'][$typeID]['quantity']))) {
                                        //if we have corresponding typeID with amount and quantity
                                        $amount=$inventory[$groupID]['types'][$typeID]['amount']; //required amount
                                        $quantity=$inventory[$groupID]['types'][$typeID]['quantity']; //actual quantity
                                        if ($amount>0) {
                                            $percent=100*$quantity/$amount;
                                            if ($percent < $LM_HINTLOW) {
                                                echo('<img src="'.$LM_HINTGREENIMG.'" style="display: inline; vertical-align:bottom;  margin: 0 5px;" title="'.$LM_HINTGREEN.'" />');
                                            } else if ($percent < $LM_HINTHIGH) {
                                                echo('<img src="'.$LM_HINTYELLOWIMG.'" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="'.$LM_HINTYELLOW.'" />');
                                            } else {
                                                echo('<img src="'.$LM_HINTREDIMG.'" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="'.$LM_HINTRED.'" />');
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
