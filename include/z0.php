<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOwnCharacters")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>{$LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=253; //Panel ID in menu. Used in hyperlinks
$PANELNAME='MG01'; //Panel name (optional)
//standard header ends here

include_once("mg.php");

if (isset($_SESSION['mg_character_id']) && isset($_GET['logoff_mg'])) {
    mg_destroy_session($_SESSION['mg_character_id']);
}

if (isset($_GET['select'])) {
    $characterID = secureGETnum('select');
    if (isInMembers($characterID)) {
        $s = mg_create_session($characterID);
        //echo('<pre>'); var_dump($s); echo('</pre>');
    } else {
        mg_connecting_screen("Invalid characterID");
        return;
    }
}

if (!isset($_SESSION['mg_character_id'])) {
    //character select screen
    mg_character_select_screen();
} else {
    //mg main screen
    mg_connecting_screen();
    $s = mg_create_session($_SESSION['mg_character_id']);
    ?>
    <script type="text/javascript">
        
        
        mg_call_state('tab-main');
        
        window.setInterval(function(){ mg_call_state('tab-main'); }, 5000);
    </script>
    <?php
}
