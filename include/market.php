<?php
include_once("percentage.php");
include_once("configuration.php");

global $LM_BUYCALC_SHOWHINTS, $LM_HINTGREEN, $LM_HINTYELLOW, $LM_HINTRED, $LM_HINTGREENIMG, $LM_HINTYELLOWIMG, $LM_HINTREDIMG, $LM_HINTLOW, $LM_HINTHIGH;
$LM_HINTGREEN = "We need this, and will be happy to buy it.";
$LM_HINTYELLOW = "We *can* buy this, but we would prefer something green instead.";
$LM_HINTRED = "We don't need this right now.";
$LM_HINTGREENIMG = "ccp_icons/38_16_183.png";
$LM_HINTYELLOWIMG = "ccp_icons/38_16_167.png";
$LM_HINTREDIMG = "ccp_icons/38_16_151.png";
$LM_HINTLOW = 100;
$LM_HINTHIGH = 200;

function shorthash($input) {
	global $LM_SALT;
	$hash = substr(strtolower(preg_replace('/[0-9_\/]+/','',base64_encode(sha1($LM_SALT.$input)))),0,14);
	return $hash;
}

function longhash($input) {
	global $LM_SALT;
	$hash = base64_encode(sha1($LM_SALT.$input));
	return $hash;
}

function getBuybackOrders($where) {
	global $USERSTABLE;
	return db_asocquery("SELECT lmb.*,lmu.login,apc.issuerID,apc.status,apc.type,apc.dateIssued,apc.price
	FROM `lmbuyback` lmb 
	JOIN `$USERSTABLE` lmu 
	ON lmb.`userID`=lmu.`userID` 
	LEFT JOIN `apicontracts` apc
	ON lmb.`shortHash`=apc.`title`
	$where;");
}

function buyhrefedit($nr) {
    echo("<a href=\"index.php?id=3&id2=3&nr=$nr\" title=\"Click to show order details\">");
}

function charhrefedit($nr) {
    echo("<a href=\"index.php?id=9&id2=6&nr=$nr\" title=\"Click to open character information\">");
}

//38_16_118.png - OK 38_16_111.png -NOK
//38_16_193.png - OK 38_16_194.png -NOK
function OKIMG() {
	return('<img src="'.getUrl().'ccp_icons/38_16_193.png" style="vertical-align: text-bottom;" />');
}
function NOKIMG() {
	return('<img src="'.getUrl().'ccp_icons/38_16_194.png" style="vertical-align: text-bottom;" />');
}
function RDYIMG() {
	return('<img src="'.getUrl().'ccp_icons/38_16_118.png" style="vertical-align: text-bottom;" />');
}

function showBuyback($buybacklist) {
    $rnd = md5(random_pseudo_bytes_wrapper(24));
    global $DECIMAL_SEP, $THOUSAND_SEP;
    $rights_viewbuyorders=checkrights("Administrator,EditBuyOrders");
    if (!sizeof($buybacklist)>0) {
            echo('<h3>There are no buyback orders!</h3>');
    } else {
    ?>
    <script type="text/javascript">
        $(document).ready(function() {      
            addTSCustomParsers();
            $("#buyback_<?=$rnd?>").tablesorter({ 
                headers: { 
                    2: { sorter: 'numsep' },
                } 
            }); 
        });
    </script>
    <table id="buyback_<?=$rnd?>" class="lmframework tablesorter">
    <thead><tr><th>
            Date
    </th><th>
            User
    </th><th>
            Value
    </th><th>
            Description
    </th><th>
            Hash
    </th><th>
            Contract
    </th>
        </tr></thead>
    <?php


        foreach($buybacklist as $row) {
                $order_fullhash=longhash($row['orderSerialized'].$row['timestmp']);
                $contract=unserialize($row['orderSerialized']);
                $value=0;
                if (count($contract) > 0) {
                        foreach($contract as $item) {
                                $value+=$item['quantity']*$item['unitprice'];
                        }
                } else {
                        $value=0.0;
                }
                echo('<tr>');
                echo('<td>');
                        if ($rights_viewbuyorders) buyhrefedit($row['orderID']);
                        echo(date('Y.m.d H:i:s',$row['timestmp']));
                        if ($rights_viewbuyorders) echo('</a>');
                echo('</td>');
                echo('<td>');
                        if ($rights_viewbuyorders) buyhrefedit($row['orderID']);
                        echo($row['login']);
                        if ($rights_viewbuyorders) echo('</a>');
                echo('</td>');
                echo('<td style="text-align: right;">');
                        if ($rights_viewbuyorders) buyhrefedit($row['orderID']);
                        echo(number_format($value, 2, $DECIMAL_SEP, $THOUSAND_SEP));
                        if ($rights_viewbuyorders) echo('</a>');
                echo('</td>');
                echo('<td>');
                        if ($rights_viewbuyorders) buyhrefedit($row['orderID']);
                        echo($row['shortHash']);
                        if ($rights_viewbuyorders) echo('</a>');
                echo('</td>');
                echo('<td>');
                        //echo($row['orderSerialized']);
                        //echo("SAVED: {$row['fullHash']}<br />");
                        //echo("CALC: $order_fullhash<br />"); 
                        if ($rights_viewbuyorders) buyhrefedit($row['orderID']);
                        if ($row['fullHash']==$order_fullhash) echo(OKIMG()." OK"); else echo(NOKIMG()." TAMPERED");
                        if ($rights_viewbuyorders) echo('</a>');
                echo('<td>');
                        if ($rights_viewbuyorders) buyhrefedit($row['orderID']);
                        if ($row['dateIssued']!='') {
                                //there is contract
                                /*$contractprice=round(str_replace('.',',',$row['price']));
                                $orderprice=round($value);*/
                                $contractprice=$row['price'];
                                $orderprice=$value;
                                if (abs($contractprice-$orderprice)<1) {
                                        showContractStatus($row['status']);
                                } else {
                                        echo(NOKIMG()." Wrong price in game: ");
                                        echo(number_format($contractprice, 2, $DECIMAL_SEP, $THOUSAND_SEP));
                                        echo(" ISK<br />");
                                }
                        } else {
                                //no contract yet
                                echo(NOKIMG()." No contract<br />");
                        }
                if ($rights_viewbuyorders) echo('</a>');
                echo('</td>');			
                echo('</tr>');
            }
            echo('</table>');
    }
    return;
}

function showContractStatus($status) {
    switch ($status) {
        //for XML API
            case 'Outstanding':
            echo(RDYIMG()." READY<br />");
            break;
            case 'Completed':
            echo(OKIMG()." Completed<br />");
            break;
        //for ESI statusis different
            case 'outstanding':
            echo(RDYIMG()." READY<br />");
            break;
            case 'finished':
            echo(OKIMG()." Completed<br />");
            break;
            default:
            echo(NOKIMG()." Wrong status<br />");
    }
}

function showBuybackOrder($row) {
	global $LM_EVEDB,$DECIMAL_SEP,$THOUSAND_SEP;
	
	$rights_viewallchars=checkrights("Administrator,ViewAllCharacters");

	$items=unserialize($row['orderSerialized']);
	$order_fullhash=longhash($row['orderSerialized'].$row['timestmp']);

	foreach($items as $item) {
		//$typeName=db_query("SELECT `typeName` from $LM_EVEDB.`invTypes` WHERE `typeID`={$item['typeID']};");
		//$typeName=$typeName[0][0];
		//$items=$items.$item['quantity'].'x '.$typeName.'<br />';
		$value+=$item['quantity']*$item['unitprice'];
	}

	echo('<table>');
	echo("<tr><td class=\"tab-header\"><strong>Date:</strong></td><td class=\"tab\">".date('Y.m.d H:i:s',$row['timestmp'])."</td></tr>");
	echo("<tr><td class=\"tab-header\"><strong>Buyback order price:</strong></td><td class=\"tab\">".number_format($value, 2, $DECIMAL_SEP, $THOUSAND_SEP)." ISK</td></tr>");
	echo("<tr><td class=\"tab-header\"><strong>Contract Description:</strong></td><td class=\"tab\">{$row['shortHash']}</td></tr>");
	echo("<tr><td class=\"tab-header\"><strong>Hash:</strong></td><td class=\"tab\">");
		echo('<table border="0" cellspacing="2" cellpadding="0">');
		echo('<tr><td class="tab"><strong>Hash:</strong></td><td class="tab">'.$order_fullhash.'</td></tr>');
		if ($row['fullHash']==$order_fullhash) {
			echo('<tr><td class="tab"><strong>Valid:</strong></td><td class="tab"><img src="'.getUrl().'ccp_icons/38_16_193.png" style="vertical-align: text-bottom;" /> VALID</td></tr>');
		} else {
			echo('<tr><td class="tab"><strong>Should be:</strong></td><td class="tab">'.$row['fullHash'].'</td></tr>');
			echo('<tr><td class="tab"><strong>Valid:</strong></td><td class="tab"><img src="'.getUrl().'ccp_icons/38_16_194.png" style="vertical-align: text-bottom;" /> TAMPERED</td></tr>');
		}
		echo('</table>');
	echo("</td></tr>");
	
		echo("<tr><td class=\"tab-header\"><strong>In-game contract: </strong></td><td class=\"tab\">");
		
		echo('<table class="lmframework">');
		echo('<tr><td><strong>Status:</strong></td><td>');
		echo("");
		if ($row['dateIssued']!='') {
			//there is contract
			$contractprice=$row['price'];
			$orderprice=$value;
			if (abs($contractprice-$orderprice)<1) {
				showContractStatus($row['status']);
			} else {
				echo(NOKIMG()." Wrong price in game: ");
				echo(number_format($contractprice, 2, $DECIMAL_SEP, $THOUSAND_SEP));
				echo(" ISK<br />");
			}
			echo('</td></tr>');
			echo("<tr><td><strong>Contract price</strong></td><td>");
			echo(number_format($row['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
			echo("<br /></td></tr>");
			$charName=db_query("SELECT `name` FROM `apicorpmembers` WHERE `characterID`={$row['issuerID']};");
			$charName=$charName[0][0];
			echo("<tr><td><strong>Issued by:</strong></td><td>");
				if ($rights_viewallchars) charhrefedit($row['issuerID']);
					echo(stripslashes($charName));
				if ($rights_viewallchars) echo('</a>');
			echo("<br /></td></tr>");
			echo("<tr><td><strong>Issue date:</strong></td><td>{$row['dateIssued']}<br /></td></tr>");
		} else {
			//no contract yet
			echo(NOKIMG()." No contract<br />");
			echo('</td></tr>');
		}
		echo('</table>');
		
		
		
	echo("</td></tr>");

	
	echo("<tr><td class=\"tab-header\"><strong>Order items:</strong></td><td class=\"tab\">");
		echo('<table border="0" cellspacing="2" cellpadding="0">');
		echo('<tr><td class="tab-header">Material</td><td class="tab-header">Quantity</td></tr>');
		foreach($items as $item) {
			$typeName=db_query("SELECT `typeName` from $LM_EVEDB.`invTypes` WHERE `typeID`={$item['typeID']};");
			$typeName=$typeName[0][0];
			echo('<tr><td class="tab"><a href="?id=10&id2=1&nr='.$item['typeID'].'"><img src="'.getTypeIDicon($item['typeID']).'" style="width: 16px; height: 16px; vertical-align: text-bottom;" /> '.$typeName.'</td><td class="tab" style="text-align: right;"> '.number_format($item['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP).'</td></tr>');
		}
		echo('</table>');
	echo("</td></tr>");
	
	echo('</table>');
}
		
function itemhrefedit($nr) {
    echo("<a href=\"index.php?id=10&id2=1&nr=$nr\" title=\"Click to open database\">");
}

function getMarketOrders($where) {
    global $LM_EVEDB;
    return db_asocquery("SELECT amo.*,acm.`name`,itp.`typeName`,COALESCE(sta2.`stationName`,sta.`stationName`) AS `stationName`,mss.`solarSystemName`
        FROM apimarketorders amo
        JOIN apicorpmembers acm
            ON amo.`charID`=acm.`characterID`
        JOIN $LM_EVEDB.invTypes itp
            ON amo.`typeID`=itp.`typeID`
        LEFT JOIN $LM_EVEDB.staStations sta
            ON amo.`stationID`=sta.`stationID`
        LEFT JOIN apiconquerablestationslist sta2
            ON amo.`stationID`=sta2.`stationID`
        JOIN $LM_EVEDB.mapSolarSystems mss
            ON COALESCE(sta2.`solarSystemID`,sta.`solarSystemID`)=mss.`solarSystemID`
        $where
        ORDER BY `typeName`;");
}

function showMarketOrders($orderlist,$label=null,$sell=TRUE) {
        $rnd = md5(random_pseudo_bytes_wrapper(24));
	global $DECIMAL_SEP, $THOUSAND_SEP;
        $rights_viewallchars=checkrights("Administrator,ViewAllCharacters");
        
	if (!sizeof($orderlist)>0) {
                if (is_null($label)) $label='market orders';
		echo("<h3>There are no $label!</h3>");
	} else {
	?>
        <script type="text/javascript">
        $(document).ready(function() { 
            addTSCustomParsers();

            $("#orders_<?=$rnd?>").tablesorter({ 
                headers: { 
                    2: { sorter: false },
                    4: { sorter: false },
                    7: { sorter: 'numsep' },
                    
                } 
            }); 
        });
        </script>
	<table id="orders_<?=$rnd?>" class="lmframework tablesorter"><thead>
        <?php
            if (!is_null($label)) {
                ?><tr><th colspan="9"><?=$label?></th></tr><?php
            }
        ?>
	<tr><th>
		Date
	</th><th>
		&nbsp;
	</th><th>
		Character
	</th><th>
		&nbsp;
	</th><th>
		Type
	</th><th>
		System
	</th><th>
		Price
	</th><th colspan="2">
		<?php if ( $sell === TRUE ) echo('Volume sold'); else echo('Volume bought'); ?>
	</th>
        </tr></thead>
	<?php
                $total=0.0;
		foreach($orderlist as $row) {
			echo('<tr>');
			echo('<td>');
                            echo($row['issued']);
			echo('</td>');
			echo('<td style="padding: 0px; width: 32px;">');
                            if ($rights_viewallchars) charhrefedit($row['charID']);
                                echo("<img src=\"" . getCharacterPortrait($row['charID'], 32) . "\" title=\"{$row['name']}\" />");
                            if ($rights_viewallchars) echo("</a>");
			echo('</td>');
                        echo('<td>');
                            if ($rights_viewallchars) charhrefedit($row['charID']);
                                echo($row['name']);
                            if ($rights_viewallchars) echo("</a>");
			echo('</td>');
                        echo('<td style="padding: 0px; width: 32px;">');
                            itemhrefedit($row['typeID']);
                                echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"{$row['typeName']}\" />");
                            echo("</a>");
			echo('</td>');
                        echo('<td>');
                            itemhrefedit($row['typeID']);
                                echo($row['typeName']);
                            echo("</a>");
			echo('</td>');
                        echo('<td>');
                            echo("<span title=\"{$row['stationName']}\">");
                            echo($row['solarSystemName']);
                            echo("&nbsp;<img src=\"ccp_icons/38_16_208.png\" style=\"vertical-align: middle;\" />");
                            echo('</span>');
			echo('</td>');
                        echo('<td style="text-align: right;">');
                            echo(number_format($row['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP)."&nbsp;ISK");
			echo('</td>');
                        echo('<td style="text-align: center;">');
                            echo($row['volEntered']-$row['volRemaining']." of ". $row['volEntered']);
			echo('</td>');
                        echo('<td>');
                            percentbar(floor(100*($row['volEntered']-$row['volRemaining'])/$row['volEntered']), $row['volEntered']-$row['volRemaining']." of {$row['volEntered']}");
			echo('</td>');
			echo('</tr>');
                        $total+=$row['volRemaining']*$row['price'];
                        
		}
                echo("<tr><th colspan=\"9\" style=\"text-align: right;\">".number_format($total, 2, $DECIMAL_SEP, $THOUSAND_SEP)."&nbsp;ISK still on market</th></tr>");
		echo('</table>');
                
	}
	return;
}

function buchrefedit($nr) {
	global $MENUITEM;
	echo("<a href=\"index.php?id=10&id2=1&nr=$nr\" title=\"Click to see item details\">");
}

function getBuyCalc($inventory) {
    global $LM_EVEDB,$LM_HINTLOW,$LM_HINTHIGH;
    
    $buyCalcPriceModifier = getConfigItem('buyCalcPriceModifier', 1.0);
    $buyCalcPriceModifierHigh = getConfigItem('buyCalcPriceModifierHigh', 0.9);
    $buyCalcPriceModifierVeryHigh = getConfigItem('buyCalcPriceModifierVeryHigh', 0.8);

    $buycalc=db_asocquery("SELECT buy.`typeID`, itp.`typeName`, itp.`groupID`, igp.`groupName`, apr.`max`
    FROM `cfgbuying` AS buy
    JOIN $LM_EVEDB.`invTypes` AS itp
    ON buy.`typeID`=itp.`typeID`
    JOIN $LM_EVEDB.`invGroups` AS igp
    ON itp.`groupID`=igp.`groupID`
    JOIN `apiprices` AS apr
    ON buy.`typeID`=apr.`typeID`
    WHERE  apr.`type`='buy'
    ORDER BY itp.`groupID`, itp.`typeName`
    ");
    foreach($buycalc as $row) {
        $rearrange[$row['groupID']]['groupID']=$row['groupID'];
        $rearrange[$row['groupID']]['groupName']=$row['groupName'];
        $rearrange[$row['groupID']]['types'][$row['typeID']]['typeID']=$row['typeID'];
        $rearrange[$row['groupID']]['types'][$row['typeID']]['typeName']=$row['typeName'];

        //apply different price modifiers depending on stock
        $amount = $inventory[$row['groupID']]['types'][$row['typeID']]['amount']; //required amount
        $quantity = $inventory[$row['groupID']]['types'][$row['typeID']]['quantity']; //actual quantity
        if ($amount>0) {
            $percent = 100 * $quantity/$amount;
            if ($percent < $LM_HINTLOW) {
                $rearrange[$row['groupID']]['types'][$row['typeID']]['maxbuy'] = round($buyCalcPriceModifier * $row['max'],2);
            } else if ($percent < $LM_HINTHIGH) {
                $rearrange[$row['groupID']]['types'][$row['typeID']]['maxbuy'] = round($buyCalcPriceModifierHigh * $row['max'],2);
            } else {
                $rearrange[$row['groupID']]['types'][$row['typeID']]['maxbuy'] = round($buyCalcPriceModifierVeryHigh * $row['max'],2);
            }
        } else { //fall back to default multiplier
            $rearrange[$row['groupID']]['types'][$row['typeID']]['maxbuy'] = round($buyCalcPriceModifier * $row['max'],2);
        }
    }
    return($rearrange);
}
/**
 * Parse contents of user paste a'la evepraisal
 * 
 * @param string $evepraisal
 * @return type
 */
function evepraisal_parser($evepraisal) {
    $lines = preg_split('/[\r\n]+/', $evepraisal);
    $items = array();
    $unknown_line = array();
    $unknown_id = array();
    foreach ($evepraisal as $line) {
        $line_ = preg_replace('/\s\s+|\t+/', '|', $line);
        if (preg_match('/^([^\|]+)\|([^\|]+)\|([\d.\,]+)/', $line_, $m)) {
            $typeName = trim($m[1]);
            $typeID = getTypeID($typeName);
            $amount = preg_replace('/[,\.]/', '', $m[3]);
            if (!($typeID === FALSE)) {
                if (is_array($items[$typeID])) {
                    $items[$typeID]['amount'] += $amount;
                } else {
                    $items[$typeID] = array('typeID' => $typeID, 'typeName' => $typeName, 'amount' => $amount);
                }
            } else {
                $unknown_id[$typeName] = $line;
            }
        } else if (preg_match('/^([^\|]+)\|([\d.\,]+)\|([^\|]+)\|([^\|]+)/', $line_, $m)) {
            $typeName = trim($m[1]);
            $typeID = getTypeID($typeName);
            $amount = preg_replace('/[,\.]/', '', $m[2]);
            if (!($typeID === FALSE)) {
                if (is_array($items[$typeID])) {
                    $items[$typeID]['amount'] += $amount;
                } else {
                    $items[$typeID] = array('typeID' => $typeID, 'typeName' => $typeName, 'amount' => $amount);
                }
            } else {
                $unknown_id[$typeName] = $line;
            }
        } else if (preg_match('/^([^\|]+)\s+([\d.\,]+)$/', $line, $m)) {
            $typeName = trim($m[1]);
            $typeID = getTypeID($typeName);
            $amount = preg_replace('/[,\.]/', '', $m[2]);
            if (!($typeID === FALSE)) {
                if (is_array($items[$typeID])) {
                    $items[$typeID]['amount'] += $amount;
                } else {
                    $items[$typeID] = array('typeID' => $typeID, 'typeName' => $typeName, 'amount' => $amount);
                }
            } else {
                $unknown_id[$typeName] = $line;
            }
        }else {
            array_push($unknown_line, $line);
        }
    }
    return array('items' => $items, 'unknown_id' => $unknown_id, 'unknown_line' => $unknown_line);
}

function showItems($items, $label="Items") {
    global $DECIMAL_SEP, $THOUSAND_SEP;
    
    if (!is_array($items) || count($items) == 0) {
        ?> <table class="lmframework"><tr><th>No items to display</th></tr></table> <?php
    } else {
        ?> <table class="lmframework"><tr><th colspan="3"><?=$label?></th></tr> <?php
        foreach ($items as $item) {
            ?> <tr><td><?= dbhrefedit($item['typeID']) ?><img src="<?= getTypeIDicon($item['typeID'])?>" title="<?=$item['typeName']?>"/></a></td>
            <td><?= dbhrefedit($item['typeID']) . $item['typeName']?></a></td>
            <td><?= number_format($item['amount'], 0, $DECIMAL_SEP, $THOUSAND_SEP)?></td></tr> <?php
        }
        ?> </table> <?php
    }
}

function showQuote($stock, $items, $label = "Quote") { 
    global $LM_BUYCALC_SHOWHINTS, $LM_HINTGREEN, $LM_HINTYELLOW, $LM_HINTRED, $LM_HINTGREENIMG, $LM_HINTYELLOWIMG, $LM_HINTREDIMG, $LM_HINTLOW, $LM_HINTHIGH;
    /*
  [25590]=>
  array(2) {
    ["stock"]=>
    array(6) {
      ["typeID"]=>
      string(5) "25590"
      ["typeName"]=>
      string(28) "Contaminated Nanite Compound"
      ["amount"]=>
      string(1) "0"
      ["quantity"]=>
      int(1199)
      ["value"]=>
      float(149875000)
      ["price"]=>
      string(9) "125000.00"
    }
    ["items"]=>
    array(3) {
      ["typeID"]=>
      string(5) "25590"
      ["typeName"]=>
      string(28) "Contaminated Nanite Compound"
      ["amount"]=>
      string(1) "4"
    }
  }
     */
    $common = array();
    
    if(is_array($stock) && count($stock) > 0 && is_array($items) && count($items) > 0) {
        foreach ($stock as $groupID => $group) {
            foreach ($group['types'] as $typeID => $type) {
                if (array_key_exists($typeID, $items)) {
                    $common[$typeID]['stock'] = $type;
                    $common[$typeID]['items'] = $items[$typeID];
                }
            }
        }
    }
    
   $total = 0;
   
   $buyCalcPriceModifier = getConfigItem('buyCalcPriceModifier', 1.0);
   $buyCalcPriceModifierHigh = getConfigItem('buyCalcPriceModifierHigh', 0.9);
   $buyCalcPriceModifierVeryHigh = getConfigItem('buyCalcPriceModifierVeryHigh', 0.8);

   ?> <form method="post" action="index.php?id=3&id2=2"> <?php
           token_generate();
   
   if (!is_array($items) || count($items) == 0) {
        ?> <table class="lmframework"><tr><th>No items to display</th></tr></table> <?php
    } else {
        ?> <table class="lmframework"><tr><th colspan="4"><?=$label?></th></tr> <?php
        foreach ($items as $typeID => $item) {
            
            if (array_key_exists($typeID, $common)) {
                
                if ($common[$typeID]['stock']['amount'] > 0) {
                    $percent = 100 * $common[$typeID]['stock']['quantity'] / $common[$typeID]['stock']['amount'];
                    if ($percent < $LM_HINTLOW) {
                        $common[$typeID]['stock']['price'] = round($buyCalcPriceModifier * $common[$typeID]['stock']['price'],2);
                    } else if ($percent < $LM_HINTHIGH) {
                        $common[$typeID]['stock']['price'] = round($buyCalcPriceModifierHigh * $common[$typeID]['stock']['price'],2);
                    } else {
                        $common[$typeID]['stock']['price'] = round($buyCalcPriceModifierVeryHigh * $common[$typeID]['stock']['price'],2);
                    }
                } else { //fall back to default multiplier
                    $common[$typeID]['stock']['price'] = round($buyCalcPriceModifier * $common[$typeID]['stock']['price'],2);
                }
                $total += $item['amount'] * $common[$typeID]['stock']['price'];
                $price = number_format($item['amount'] * $common[$typeID]['stock']['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP) . " ISK";
                $buying = showHint($common[$typeID]['stock']['quantity'], $common[$typeID]['stock']['amount']);
                $form = "<input type=\"hidden\" name=\"q_{$item['typeID']}\" value=\"{$item['amount']}\">";
                $css = "";
            } else {
                $price = "0.00 ISK";
                $buying = showHint(2,1);
                $form = "";
                $css = "background: rgba(255,0,0,0.3);";
            }
            ?> <tr>
            <td style="<?=$css?>"><?= dbhrefedit($item['typeID']) ?><img src="<?= getTypeIDicon($item['typeID'])?>" title="<?=$item['typeName']?>"/></a></td>
            <td style="<?=$css?>"><?= $buying?> <?= dbhrefedit($item['typeID']) . $item['typeName']?></a></td>
            <td style="text-align: right; <?=$css?>"><?= number_format($item['amount'], 0, $DECIMAL_SEP, $THOUSAND_SEP)?></td>
            <td style="<?=$css?>"><?= $price?><?= $form?></td>
            </tr> <?php
        }
        ?> <tr><th colspan="4">Total: <?=number_format($total, 2, $DECIMAL_SEP, $THOUSAND_SEP)?> ISK</th></tr> <?php
        ?> </table> <?php
    }
    
    ?>
	<input type="hidden" name="id" value="<?php echo($MENUITEM); ?>">
	<input type="submit" value="Submit contract">
	</form>
    <?php
    //$_SESSION['buycalc_quote'] = $common;
    /*echo('<pre>');
    echo("common="); var_dump($common); echo("\r\n");
    echo("stock="); var_dump($stock); echo("\r\n");
    echo('</pre>');*/
}

function showHint($quantity, $amount) {
    global $LM_BUYCALC_SHOWHINTS, $LM_HINTGREEN, $LM_HINTYELLOW, $LM_HINTRED, $LM_HINTGREENIMG, $LM_HINTYELLOWIMG, $LM_HINTREDIMG, $LM_HINTLOW, $LM_HINTHIGH;
    
    if ($LM_BUYCALC_SHOWHINTS && $amount > 0) {
        $percent = 100 * $quantity / $amount;
        if ($percent < $LM_HINTLOW) {
            return('<img src="'.getUrl().$LM_HINTGREENIMG.'" style="display: inline; vertical-align:bottom;  margin: 0 5px;" title="'.$LM_HINTGREEN.'" />');
        } else if ($percent < $LM_HINTHIGH) {
            return('<img src="'.getUrl().$LM_HINTYELLOWIMG.'" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="'.$LM_HINTYELLOW.'" />');
        } else {
            return('<img src="'.getUrl().$LM_HINTREDIMG.'" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="'.$LM_HINTRED.'" />');
        }
    } else {
        return "";
    }
}

function showBuyCalc($buycalc,$inventory=array()) {
    global $LM_BUYCALC_SHOWHINTS, $LM_HINTGREEN, $LM_HINTYELLOW, $LM_HINTRED, $LM_HINTGREENIMG, $LM_HINTYELLOWIMG, $LM_HINTREDIMG, $LM_HINTLOW, $LM_HINTHIGH;

    $rights_viewdatabase=checkrights("Administrator,ViewDatabase");
    ?>
    
    <script type="text/javascript">
    <?php echo("var all_fields=[\r\n");
        foreach($buycalc as $groupID => $group) {
	    foreach($group['types'] as $row) {
		echo("[ 'q_{$row['typeID']}', 'v_{$row['typeID']}' ],\r\n");
	    }
        }
        echo("];\r\n");
	?>
	</script>
	<script type="text/javascript" src="<?=getUrl()?>buycalc.js"></script>
	<script type="text/javascript" src="<?=getUrl()?>skrypty.js"></script>
	<!--<form method="post" action="index.php?id=3&id2=2" onsubmit="return confirm('Are you sure you want to submit this order?');">-->
        
        <?php token_generate(); ?>
	<table width="100%" cellspacing="2" cellpadding="0"><tr><td style="width: 70%; text-align: left; vertical-align: top;">
        <h3>Paste your items here:</h3>
          <table class="lmframework" style="width: 80%; min-width: 455px;">
              <tr><th>Evepraisal buyback quote</th></tr>
              <tr><td>
          <form method="post" action="index.php?id=3&id2=6">
              <?php token_generate(); ?>
              <textarea id="evepraisal" name="evepraisal" cols="60" rows="15" placeholder="paste data from client here..." style="width: 100%; height: 185px;"></textarea><br/>
              <input type="submit" value="Parse list">
          </form>
          </td></tr></table>  
        <h3>Or select items to sell manually below:</h3>
	<form method="post" action="index.php?id=3&id2=2">
	<?php
		$tabindex=1;
                
                if ($LM_BUYCALC_SHOWHINTS) {
                ?>
                    
                    <table class="lmframework" style="width: 80%; min-width: 455px;">
                        <tr><th>Hints:</th></tr>
                        <tr><td><img src="<?=getUrl()?><?php echo($LM_HINTGREENIMG); ?>" style="display: inline; vertical-align:bottom;  margin: 0 5px;" title="<?php echo($LM_HINTGREEN); ?>" /><?php echo($LM_HINTGREEN); ?></td></tr>
                        <tr><td><img src="<?=getUrl()?><?php echo($LM_HINTYELLOWIMG); ?>" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="<?php echo($LM_HINTYELLOW); ?>" /><?php echo($LM_HINTYELLOW); ?></td></tr>
                        <tr><td><img src="<?=getUrl()?><?php echo($LM_HINTREDIMG); ?>" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="<?php echo($LM_HINTRED); ?>" /><?php echo($LM_HINTRED); ?></td></tr>
                    </table>
                <?php
                }
		foreach($buycalc as $groupID => $group) {
                    ?>
                    <table class="lmframework" style="width: 80%; min-width: 455px;" id="buc_group_name_<?php echo($group['groupID']); ?>" title="Click to show/hide items in this group" onclick="div_toggler('buc_group_<?php echo($group['groupID']); ?>')">
                    <tr><th style="width: 100%; text-align: center;"><img src="<?=getUrl()?>img/plus.gif" style="float: left;"/> <?php echo($group['groupName']); ?></th></tr>
                    </table>
                    <div id="buc_group_<?php echo($group['groupID']); ?>" style="display: none;">
                    <table class="lmframework" style="width: 80%; min-width: 455px;" >
                        <script type="text/javascript">rememberToggleDiv('buc_group_<?php echo($group['groupID']); ?>');</script>
                    <tr><td style="width: 32px; min-width: 32px;">
                            Icon
                    </td><td style="min-width: 125px;">
                            Type
                    </td><td style="width: 75px; min-width: 75px;">
                            Quantity
                    </td><td style="width: 75px; min-width: 75px;">
                            Unit Price
                    </td><td style="width: 75px; min-width: 75px;">
                            Value
                    </td>
                    </tr>
                    <?php
			foreach($group['types'] as $typeID => $row) {
				echo('<tr><td style="padding: 0px; width: 32px;">');
				if ($rights_viewdatabase) buchrefedit($row['typeID']);
					echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"{$row['typeName']}\" />");
				if ($rights_viewdatabase) echo('</a>');
				echo('</td>');
				echo('<td>');
				if ($rights_viewdatabase) buchrefedit($row['typeID']);
                                    //Show demand hints
                                    if (($LM_BUYCALC_SHOWHINTS) && (isset($inventory[$groupID]['types'][$typeID]['amount'])) && (isset($inventory[$groupID]['types'][$typeID]['quantity']))) {
                                        //if we have corresponding typeID with amount and quantity
                                        $amount=$inventory[$groupID]['types'][$typeID]['amount']; //required amount
                                        $quantity=$inventory[$groupID]['types'][$typeID]['quantity']; //actual quantity
                                        echo(showHint($quantity, $amount));
                                    }
                                    //echo(' ');
                                    echo($row['typeName']);    
				if ($rights_viewdatabase) echo('</a>');
				echo('</td>');
				echo('<td>');
					echo("<input name=\"q_{$row['typeID']}\" id=\"q_{$row['typeID']}\" type=\"text\" size=\"10\" value=\"0\" onclick=\"select_all(this);\" onkeyup=\"calc_row('q_{$row['typeID']}','p_{$row['typeID']}','v_{$row['typeID']}','total',all_fields,event)\" tabindex=\"$tabindex\">");
				$tabindex+=1;
				echo('</td>');
				echo('<td>');
					echo("<div id=\"p_{$row['typeID']}\" style=\"text-align: right;\">{$row['maxbuy']}</div>");
				echo('</td>');
				echo('<td>');
					echo("<div id=\"v_{$row['typeID']}\" style=\"text-align: right;\">0.00</div>");
				echo('</td></tr>');
                                $type++;
			}
                        ?>
                        </table>
                        </div>
                    <?php
		}
                    
         echo("Types: $type"); ?>
	</td><td style="width: 30%; text-align: left; vertical-align: top;">
	
		<table class="lmframework" style="position: fixed; top: 200px; ">
		<tr><th colspan="2"  style="text-align: center;">
			<h2 id="total">0.00 ISK</h2>
		</th></tr>
		<tr><th style="width: 50%; text-align: center;">
			<input type="reset" value="Reset Form" onclick="form_reset(all_fields,'total');">
		</th><th style="width: 50%; text-align: center;">
			<input type="button" value="Submit Order" onclick="form_submit(this.form);">
		</th></tr>
		</table>
	
	</td></tr></table>
	</form>
    <?php
}
?>