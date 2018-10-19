<?php 
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewKillboard")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Killboard'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;
include_once('killboard.php');
include_once('inventory.php');


//submenu
    ?>
    <table cellpadding="0" cellspacing="2">
    <tr>
     <?php
//back butan    
    ?>
        <td>
            <input type="button" onclick="window.history.back();" value="&laquo; back"/>
	</td>
    </tr>
    </table>


    <?php
//end submenu

?>

<a name="top"></a>
<div class="tytul">
    Kill report<br/>
</div>
<?php
    $killID=secureGETnum('killID');
    if (empty($killID)) {
        echo("killID cannot be empty.");
        return;
    }
    
    $km = getKill($killID);
    
    

    if(count($km)>0) {
        $items=$km['items'];

        $iskLost=0;
        $iskDropped=0;
        $iskShip=getAveragePrice($km['shipTypeID']);
        
        if (count($items)>0) {
            foreach($items as $item) {
                $iskLost+=$item['qtyDestroyed']*$item['averagePrice'];
                $iskDropped+=$item['qtyDropped']*$item['averagePrice'];
            }
        }
        
        $iskTotal = $iskLost + $iskDropped + $iskShip;
        
        $title = $km['shipTypeName'] . ' | ' . $km['characterName'] . ' | ' . $km['solarSystemName'] . ' | ' . generate_title();
        $description = $km['characterName'] . ' (' . $km['corporationName'] . ') lost their ' . $km['shipTypeName'] . ' in ' . $km['solarSystemName'] . ' (' . $km['regionName'] . ') Total Value: ' . number_format($iskTotal, 0, $DECIMAL_SEP, $THOUSAND_SEP) . ' ISK';
        $image = getTypeIDicon($km['shipTypeID'], 64);
        generate_meta($description, $title, $image);
    }
            
    
    
    showKill($km);
?>
