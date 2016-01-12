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
    showKill(getKill($killID));
?>
