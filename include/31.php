<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewBuyCalc")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=3; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Buy Calculator'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

include_once("market.php");
include_once("inventory.php");

?>	    
<div class="tytul">
    <?php echo($PANELNAME); ?><br/>
</div>
<!--<em><b>Warning:</b> There is a weird performance problem with this form with the <b>LastPass</b> extension enabled. I'm investigating it right now.<br/>If you run into problems, try disabling that add-on, or use in-game browser instead.</em>-->
<?php
// GETTING BUY CALC DATA
$buycalc=getBuyCalc();
$inventory=getStock();
                
// DISPLAYING THE BUY CALC                
showBuyCalc($buycalc,$inventory);
// END OF DISPLAY
?>