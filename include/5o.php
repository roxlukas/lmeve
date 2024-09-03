<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>{$LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='ESI API Tokens'; //Panel name (optional)
//standard header ends here
global $LM_EVEDB;

?>
<div class="tytul">
<?php echo($PANELNAME); ?>
</div>
		    <?php
		$nr=secureGETnum('nr');
		$do=$_POST['do'];		

		if (db_count("SELECT `tokenID` from `cfgesitoken` WHERE `tokenID`=$nr;")==0) die("Such token does not exist.");
		
		
	if ($do==1) {
            if (!token_verify()) die("Invalid or expired token.");
			db_uquery("DELETE FROM `cfgesitoken` WHERE `tokenID`=$nr");
			echo('ESI Token has been deleted.<br><br>');
			echo('<script type="text/javascript">location.href="index.php?id=5&id2=21";</script>');
	?>
		<input type="button" value="OK" onclick="location.href='?id=5&id2=21';"/>
		</form>
		<?php
	} else {
		?>
		
		
		<strong>Are you really sure to delete this ESI Token?</strong><br>

		<table border="0"><tr><td>
		<form method="post" action="?id=5&id2=24">
                <input type="hidden" name="nr" value="<?php echo($nr); ?>">
                <?php token_generate(); ?>
		<input type="hidden" name="do" value="1">
		<input type="submit" value="Yes">
		</form></td><td>
		<input type="button" value="No" onclick="location.href='?id=5&id2=21';"/></td></tr></table>
		<?php
	}
?>