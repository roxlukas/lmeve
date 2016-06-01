<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewPOS")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
include_once('inventory.php');
$MENUITEM=2; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Silo Browser'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB,$DECIMAL_SEP,$THOUSAND_SEP;

?>
		<a name="top"></a>             
                
<div class="tytul">
    <?=$PANELNAME?><br/>
</div>
<a href="#down">Scroll down</a>
<?php
    $corps=db_asocquery("SELECT * FROM apicorps;");
    foreach ($corps as $corp) { //begin corps loop
        echo("<h1><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
        showSilos(getSilos($corp['corporationID']));
    }//end corps loop
?>
<a href="#top">Scroll up</a>
<a name="down"></a>
		