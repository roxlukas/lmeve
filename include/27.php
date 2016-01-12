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

include_once('inventory.php');

global $LM_EVEDB,$DECIMAL_SEP,$THOUSAND_SEP;

$nr=secureGETnum('nr');

?>
		<a name="top"></a>             
                
                <div class="tytul">
			Planet details<br/>
		</div>
<?php
                showPocoDetail(getPocos("apo.planetItemID=$nr"),getSinglePocoIncome($nr));
                echo('<h2>Clients List</h2><i>All paying clients who accessed this POCO in the current month.</i>');
                showPocoClients(getPocoClients($nr));
?>
		    
			
		
