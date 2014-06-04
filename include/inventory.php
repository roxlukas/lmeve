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
    echo("<a href=\"index.php?id=2&id2=3&nr=$nr\"  title=\"Click to edit Lab/Array\">");
}

function toonhrefedit($nr) {
    echo("<a href=\"index.php?id=9&id2=6&nr=$nr\" title=\"Click to open character information\">");
}

function getControlTowers($where='TRUE') {
    global $LM_EVEDB;
    $sql="SELECT asl.*,itp.`typeName`,ssn.`itemName` AS `solarSystemName`,ssm.`itemName` AS `moonName` 
    FROM `apistarbaselist` asl
    JOIN $LM_EVEDB.`invnames` ssn
    ON asl.`locationID`=ssn.`itemID`
    JOIN $LM_EVEDB.`invnames` ssm
    ON asl.`moonID`=ssm.`itemID`
    JOIN $LM_EVEDB.`invtypes` itp
    ON asl.`typeID`=itp.`typeID`
    WHERE $where;";
    //echo("DEBUG: $sql");
    $rawdata=db_asocquery($sql);
    return $rawdata;
}

function getLabs($where='TRUE') {
    global $LM_EVEDB;
    $sql_labs="SELECT lml.*,itp.`typeName`
    FROM `lmlabs` lml
    JOIN $LM_EVEDB.`invtypes` itp
    ON lml.`structureTypeID`=itp.`typeID`
    WHERE $where
    ORDER BY lml.parentTowerID,lml.structureName;";
    $rawlabdata=db_asocquery($sql_labs);
    return $rawlabdata;
}

function getLabDetails($structureID) {
    global $LM_EVEDB;
    $sql="SELECT lml.*,itp.`typeName`
    FROM `lmlabs` lml
    JOIN $LM_EVEDB.`invtypes` itp
    ON lml.`structureTypeID`=itp.`typeID`
    WHERE `structureID`=$structureID
    ORDER BY lml.parentTowerID,lml.structureName;";
    $raw=db_asocquery($sql);
    if (count($raw)>0) {
        $raw=$raw[0];
        $ct=getControlTowers("asl.`itemID`=${raw['parentTowerID']}");
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
    JOIN $LM_EVEDB.`invtypes` itp
    ON lmt.`typeID`=itp.`typeID`
    LEFT JOIN `apicorpmembers` acm
    ON lmt.`characterID`=acm.`characterID`
    WHERE $where";
    $raw=db_asocquery($sql);
    return($raw);
}

function getLabsAndTasks($corporationID) {
    global $LM_EVEDB;
    $raw_towers=getControlTowers("`corporationID`=$corporationID");
    if (count($raw_towers)>0) {
        $raw_labs=getLabs();
        $raw_tasks=getSimpleTasks();
        foreach($raw_towers as $tower) {
            $towers[$tower['itemID']]=$tower;
        }
        foreach($raw_labs as $lab) {
            if (array_key_exists($lab['parentTowerID'], $towers)) {
                $towers[$lab['parentTowerID']]['labs'][$lab['structureID']]=$lab;
                $labs[$lab['structureID']]=$lab;
            }
        }
        foreach($raw_tasks as $task) {
            if (!is_null($task['structureID']) && array_key_exists($task['structureID'], $labs)) {
                //echo($task['structureID'].",");
                $parentTowerID=$labs[$task['structureID']]['parentTowerID'];
                $towers[$parentTowerID]['labs'][$task['structureID']]['users'][$task['characterID']]=$task['characterName'];
                $towers[$parentTowerID]['labs'][$task['structureID']]['products'][$task['typeID']]=$task['typeName'];
            }
        }
    }
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
            <tr><th colspan="5" style="text-align: center;">
                <?php echo($tower['moonName']); ?>
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
            </th>
            </tr>
            <?php
            if (count($tower['labs'])>0) foreach ($tower['labs'] as $row) {
                ?>
                <tr><td width="32" style="padding: 0px; text-align: center;">
                    <?php dbhrefedit($row['structureTypeID']); echo("<img src=\"ccp_img/${row['structureTypeID']}_32.png\" title=\"${row['typeName']}\" />"); echo('</a>'); ?>
                </td><td>
                    <?php if ($rights_editpos) labshrefedit($row['structureID']);
                    echo(stripslashes($row['structureName']));
                    if ($rights_editpos) echo('</a>'); ?> 
                </td><td style="">
                    <?php if ($rights_editpos) labshrefedit($row['structureID']);
                    echo(stripslashes($row['typeName'])); 
                    if ($rights_editpos) echo('</a>'); ?>
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
                </td>
                </tr>
                <?php
            }
            ?>
                <tr><td>
                
                </td><td style="text-align: center;">
                    <input type="button" onclick="location.href='?id=2&id2=3&nr=new&parent=<?php echo($tower['itemID']); ?>'" value="Add Lab" />
                </td><td>

                </td><td>

                </td><td>

                </td>
                </tr>
            </table>
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
			</th><th style="width: 150px;">
				Control Tower Type
			</th><th style="min-width: 160px;">
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
			echo('<h3>Corporation doesn\'t have any POSs</h3>');
        }
        
    
    
}

function getPocos($where='TRUE') {
    global $LM_EVEDB;
    $sql="SELECT * FROM `apipocolist` apl
    WHERE $where";
    $raw=db_asocquery($sql);
    return($raw);
}

function showPocos($pocos) {
    
        if (count($pocos)>0) {
			?>
			<table class="lmframework" style="" id="">
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
                <?php echo("<img src=\"ccp_img/2233_32.png\" title=\"Customs Office\" />"); ?>
            </td><td style="">
                <?php echo($row['solarSystemName']);  ?>
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
			echo('<h3>Corporation doesn\'t have any POCOs</h3>');
        }
        
    
}

function getStock($where='TRUE') {
    global $LM_EVEDB;
    $sql="SELECT cfs.*,itp.`typeName`,apa.*,apl.`locationName`,app.`max` as price,itp.`groupID`, igp.`groupName` 
        FROM `cfgstock` cfs
        JOIN $LM_EVEDB.`invtypes` itp
        ON cfs.`typeID`=itp.`typeID`
        JOIN $LM_EVEDB.`invgroups` igp
        ON itp.`groupID`=igp.`groupID`
        LEFT JOIN `apiprices` app
        ON cfs.`typeID`=app.`typeID`
        JOIN `apiassets` apa
        ON cfs.`typeID`=apa.`typeID`
        LEFT JOIN `apilocations` apl
        ON apa.`locationID`=apl.`locationID`
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
