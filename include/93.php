<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOwnCharacters,ViewAllCharacters")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=9; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Characters'; //Panel name (optional)
//standard header ends here

global $USERSTABLE;

?>		    
		    <div class="tytul">
			<?php echo($PANELNAME); ?><br>
		    </div>
		    <?php
		$nr=secureGETnum('nr');
		$do=$_POST['do'];		
		
		if (db_count("SELECT `charID` from `lmchars` WHERE `charID`=$nr")==0) die("Such record does not exist.");
		
		
	if ($do==1) {
            if (!token_verify()) die("Invalid or expired token.");
			db_uquery("DELETE FROM `lmchars` WHERE `charID`=$nr");
			echo('Character disconnected from user.<br><br>');
			echo('<script type="text/javascript">location.href="index.php?id=9&id2=0";</script>');
	?>
		<form method="get" action="">
		<input type="hidden" name="id" value="9">
		<input type="hidden" name="id2" value="0">
		<input type="submit" value="OK">
		</form>
		<?php
	} else {
		?>
		
		
		<strong>Are you really sure to disconnect this character?</strong><br>

		<table border="0"><tr><td>
		<form method="post" action="?id=9&id2=3">
                <input type="hidden" name="nr" value="<?php echo($nr); ?>">
                <?php token_generate(); ?>
		<input type="hidden" name="do" value="1">
		<input type="submit" value="Yes">
		</form></td><td>
		<form method="get" action="">
		<input type="hidden" name="id" value="9">
		<input type="hidden" name="id2" value="0">
		<input type="submit" value="No">
		</form></td></tr></table>
		<?php
	}
?>