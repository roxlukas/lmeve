<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditWiki")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=11; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Wiki'; //Panel name (optional)
//standard header ends here

include('wiki.php');
global $LM_EVEDB;

?>
<form action="?id=11&id2=2" method="post">
    <?php    token_generate(); ?>
            <span class="tytul">
		<?php echo($PANELNAME); ?>
	    </span><span>Editing: <strong><?php echo(stripslashes($wikipage)); ?></strong> <input type="submit" value="Save" /> <input type="button" value="View page" onclick="location.href='?id=11&id2=0&wikipage=<?php echo($wikipage); ?>'" /></span><br />
<br />
    <input type="hidden" name="wikipage" value="<?php echo($wikipage); ?>"/>
    <textarea cols="140" rows="35" name="contents"><?php $row=getWikiPage($wikipage); echo($row['contents']); ?></textarea>
</form>
	    