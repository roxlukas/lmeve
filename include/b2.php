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

global $LM_EVEDB;

if (!token_verify()) die("Invalid or expired token.");

?>
            <span class="tytul">
		<?php echo($PANELNAME); ?>
	    </span>
            </span><span>Saving: <strong><?php echo(stripslashes($wikipage)); ?></strong></span><br />
            
<?php
    $contents=strip_tags(secureGETstr('contents'));
    db_uquery("INSERT INTO `wiki` VALUES(DEFAULT, '$wikipage','$contents') ON DUPLICATE KEY UPDATE contents='$contents';");
?>
                <form method="get" action="">
		<input type="hidden" name="id" value="11" />
		<input type="hidden" name="id2" value="1" />
		<input type="hidden" name="wikipage" value="<?php echo($wikipage); ?>" />
		<input type="submit" value="OK" />
		</form>
		<script type="text/javascript">location.href="index.php?id=11&id2=1&wikipage=<?php echo($wikipage); ?>";</script>
	    