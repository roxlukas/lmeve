<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewWiki")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=11; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Wiki'; //Panel name (optional)
//standard header ends here

include("wiki.php");
global $LM_EVEDB;

?>
	    <span class="tytul">
		<?php echo($PANELNAME); ?>
	    </span><span>Viewing: <strong><?php echo(stripslashes($wikipage)); ?></strong></span><br />
<?php
    showWikiPage($wikipage,getWikiPage($wikipage));
?>