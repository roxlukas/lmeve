<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewInventory")) { //"Administrator,ViewOverview"
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
			Inventory<br/>
		    </div>

		    
			<a href="#down">Scroll down</a>
		    </div>
		    
<?php
include_once("inventory.php");

$corps=db_asocquery("SELECT * FROM apicorps;");
foreach ($corps as $corp) { //begin corps loop
    echo("<h1><img src=\"https://image.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
    
    $stock=getStock("apa.`corporationID`=${corp['corporationID']}");
    
    //echo("DEBUG: <pre>"); print_r($inventory); echo('</pre>');
    ?>
    <h3>Current Stock</h3>
    <em>Due to EVE API limitations, Assets can only be updated every 6 hours.</em>
    <?php
    
    showStock($stock,$corp['corporationID']);
}//end corps loop
?>
		
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
			
		    </div><br>
		
