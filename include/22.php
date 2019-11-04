<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewPOS")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=2; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Industry Facilities'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

include_once 'inventory.php';
?>	
<div class="tytul">
    <?php echo("$PANELNAME"); ?><br/>
</div>
<a href="#down">Scroll down</a>
<?php
    $corps=db_asocquery("SELECT * FROM apicorps;");
    foreach ($corps as $corp) { //begin corps loop
        echo("<h1><img src=\"" . getCorporationLogo($corp['corporationID'], 64) . "\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
        showECAndTasks(getECAndTasks($corp['corporationID']));
        showLabsAndTasks(getLabsAndTasks($corp['corporationID']));
    }//end corps loop
?>
<a href="#top">Scroll up</a>
<a name="down"></a>
		
