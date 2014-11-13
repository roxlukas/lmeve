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

                    <div id="pageContents"><i>Loading...</i></div>
                    <script type="text/javascript">
                        ajax_get('ajax.php?act=CACHE&page=20','pageContents');
                    </script>
		
	
		
