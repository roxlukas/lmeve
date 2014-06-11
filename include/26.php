<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewPOS")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=2; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Inventory'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB,$DECIMAL_SEP,$THOUSAND_SEP;

?>
		<a name="top"></a>             
                
<div class="tytul">
			Player-owned Customs Offices<br/>
		    </div>

		    
			<a href="#down">Scroll down</a>
		    </div>
		    
<?php
include_once("inventory.php");

$corps=db_asocquery("SELECT * FROM apicorps;");
foreach ($corps as $corp) { //begin corps loop
    echo("<h1><img src=\"https://image.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
    
    $pocos=getPocos("apo.`corporationID`=${corp['corporationID']}");
    
    //echo("DEBUG: <pre>"); print_r($pocos); echo('</pre>');
    ?>
    
    <?php
    
    showPocos($pocos,$corp['corporationID']);
}//end corps loop
?>
		
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
			
		    </div><br>
		
