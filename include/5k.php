<?php
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='EVE Corp API keys'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

?>		    
		    <div class="tytul">
			<?php echo($PANELNAME); ?><br>
		    </div>
		    <?php
		$nr=secureGETnum('nr');
		$do=$_POST['do'];		

		if (db_count("SELECT `apiKeyID` from `cfgapikeys` WHERE `apiKeyID`=$nr;")==0) die("Such key does not exist.");
		
		
	if ($do==1) {
            if (!token_verify()) die("Invalid or expired token.");
			db_uquery("DELETE FROM `cfgapikeys` WHERE `apiKeyID`=$nr");
			echo('API Key has been deleted.<br><br>');
			echo('<script type="text/javascript">location.href="index.php?id=5&id2=17";</script>');
	?>
		<input type="button" value="OK" onclick="location.href='?id=5&id2=17';"/>
		</form>
		<?php
	} else {
		?>
		
		
		<strong>Are you really sure to delete this API Key?</strong><br>

		<table border="0"><tr><td>
		<form method="post" action="?id=5&id2=20">
                <input type="hidden" name="nr" value="<?php echo($nr); ?>">
                <?php token_generate(); ?>
		<input type="hidden" name="do" value="1">
		<input type="submit" value="Yes">
		</form></td><td>
		<input type="button" value="No" onclick="location.href='?id=5&id2=17';"/></td></tr></table>
		<?php
	}
?>