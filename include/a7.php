<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOreValues")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Ore values table'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB, $DECIMAL_SEP, $THOUSAND_SEP;

/*function dbhrefedit($nr) {
    echo("<a href=\"index.php?id=10&id2=1&nr=$nr\">");
}*/

$sql="SELECT itp.`typeID`, itp.`typeName`, itp.`volume`, itp.`portionSize`, itm.`materialTypeID` AS mineralID, itm.`quantity`, apr.`max` AS price
FROM $LM_EVEDB.`invTypes` itp
JOIN $LM_EVEDB.`invGroups` igp ON itp.`groupID` = igp.`groupID` 
JOIN $LM_EVEDB.`invTypeMaterials` itm ON itp.`typeID` = itm.`typeID`
JOIN `apiprices` apr ON itm.`materialTypeID` = apr.`typeID`
WHERE igp.`categoryID` = 25
AND itp.`typeName` NOT LIKE 'Compressed%'
AND itp.`marketGroupID` IS NOT NULL
AND itp.`volume` < 50
AND apr.`type`='buy'
ORDER BY itp.`groupID`,itp.`typeID`";
$ores_raw=db_asocquery($sql);

foreach($ores_raw as $row) {
    //generic ore data
    $ores[$row['typeID']]['typeID']=$row['typeID'];
    $ores[$row['typeID']]['typeName']=$row['typeName'];
    $ores[$row['typeID']]['volume']=$row['volume'];
    $ores[$row['typeID']]['portionSize']=$row['portionSize'];
    //mineral data
    $ores[$row['typeID']]['minerals'][$row['mineralID']]['quantity']=$row['quantity'];
    $ores[$row['typeID']]['minerals'][$row['mineralID']]['price']=$row['price'];
}

//BEGIN Clientside sorting:
?>
  <script type="text/javascript" src="<?=getUrl()?>jquery-tablesorter/jquery.tablesorter.min.js"></script>
  <link rel="stylesheet" type="text/css" href="<?=getUrl()?>jquery-tablesorter/blue/style.css">
  <script type="text/javascript">
    $(document).ready(function() { 
        addTSCustomParsers();
        
        $("#minerals").tablesorter({ 
            headers: { 
                0: { sorter: false },
                4: { sorter: 'numsep' },
                5: { sorter: 'numsep' },
                6: { sorter: 'numsep' },
                7: { sorter: 'numsep' },
                8: { sorter: 'numsep' },
                9: { sorter: 'numsep' },
                10: { sorter: 'numsep' },
                11: { sorter: 'numsep' },
                12: { sorter: 'numsep' },
                13: { sorter: 'numsep' },
                14: { sorter: 'numsep' },
            } 
        }); 
    });
  </script>
<?php
//END Clientside sorting
?>
            <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
            <table id="minerals" class="lmframework tablesorter" width="100%">
                <thead><tr>
                    <th style="width: 32px; padding: 0px; text-align: center;">Icon</th><th>Ore name</th><th>Volume</th><th>Units/batch</th><th>Tritanium</th><th>Pyerite</th><th>Mexallon</th><th>Isogen</th><th>Nocxium</th><th>Zydrine</th><th>Megacyte</th><th>Morphite</th><th>ISK/batch</th><th>ISK/unit</th><th>ISK/m<sup>3</sup></th>
                </tr></thead>
                <tbody>
            <?php
                foreach($ores as $row) {
                    $value=0;
                    $tritanium=number_format($row['minerals'][34]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][34]['quantity']*$row['minerals'][34]['price'];
                    $pyerite=number_format($row['minerals'][35]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][35]['quantity']*$row['minerals'][35]['price'];
                    $mexallon=number_format($row['minerals'][36]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][36]['quantity']*$row['minerals'][36]['price'];
                    $isogen=number_format($row['minerals'][37]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][37]['quantity']*$row['minerals'][37]['price'];
                    $nocxium=number_format($row['minerals'][38]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][38]['quantity']*$row['minerals'][38]['price'];
                    $zydrine=number_format($row['minerals'][39]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][39]['quantity']*$row['minerals'][39]['price'];
                    $megacyte=number_format($row['minerals'][40]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][40]['quantity']*$row['minerals'][40]['price'];
                    $morphite=number_format($row['minerals'][11399]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][11399]['quantity']*$row['minerals'][11399]['price'];
                    $iskunit=number_format($value/$row['portionSize'], 2, $DECIMAL_SEP, $THOUSAND_SEP);
                    $iskm3=number_format($value/$row['portionSize']/$row['volume'], 2, $DECIMAL_SEP, $THOUSAND_SEP);
                    $value=number_format($value, 2, $DECIMAL_SEP, $THOUSAND_SEP);
                    ?>
                    <tr>
                        <td style="width: 32px; padding: 0px; text-align: center;">
                    <?php dbhrefedit($row['typeID']); ?><img src="<?php echo(getTypeIDicon($row['typeID'])); ?>" title="<?php echo($row['typeName']); ?>" /></a>
                        </td>
                        <td><?php dbhrefedit($row['typeID']); echo($row['typeName']); ?></a></td>
                        <td style="text-align: right;"><?php echo($row['volume']); ?></td>
                        <td style="text-align: right;"><?php echo($row['portionSize']); ?></td>
                        <td style="text-align: center;"><?php if($tritanium>0) echo($tritanium); else echo('-'); ?></td>
                        <td style="text-align: center;"><?php if($pyerite>0) echo($pyerite); else echo('-'); ?></td>
                        <td style="text-align: center;"><?php if($mexallon>0) echo($mexallon); else echo('-'); ?></td>
                        <td style="text-align: center;"><?php if($isogen>0) echo($isogen); else echo('-'); ?></td>
                        <td style="text-align: center;"><?php if($nocxium>0) echo($nocxium); else echo('-'); ?></td>
                        <td style="text-align: center;"><?php if($zydrine>0) echo($zydrine); else echo('-'); ?></td>
                        <td style="text-align: center;"><?php if($megacyte>0) echo($megacyte); else echo('-'); ?></td>
                        <td style="text-align: center;"><?php if($morphite>0) echo($morphite); else echo('-'); ?></td>
                        <td style="text-align: right;"><?php echo($value); ?></td>
                        <td style="text-align: right;"><?php echo($iskunit); ?></td>
                        <td style="text-align: right;"><?php echo($iskm3); ?></td>
                    </tr>
                    <?php
                }
            ?>
                    </tbody>
            </table>

