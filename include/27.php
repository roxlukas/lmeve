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

include('inventory.php');

global $LM_EVEDB,$DECIMAL_SEP,$THOUSAND_SEP;

$nr=secureGETnum('nr');

?>
		<a name="top"></a>             
                
                <div class="tytul">
			Player-owned Customs Offices<br/>
		</div>
<?php
                showPocos(getPocos("apo.planetItemID=$nr"),null,TRUE);
                echo('<h2>Clients List</h2>');
                showPocoClients(getPocoClients($nr));
?>
		    
			
		
