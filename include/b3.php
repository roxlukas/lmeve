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

?>
	    <span class="tytul">
		<?php echo($PANELNAME); ?>
	    </span>
            </span><span>Deleting: <strong><?php echo(stripslashes($wikipage)); ?></strong></span><br />
            
<?php

	$do=$_GET['do'];
		
		
	if ($do==1) {
		$sql="DELETE FROM `wiki` WHERE `wikipage`='$wikipage';";
		db_uquery($sql);
		echo('Wiki page removed.<br><br>');
		echo('<script type="text/javascript">location.href="index.php?id=11";</script>');
	?>
		<form type="get" action="">
		<input type="hidden" name="id" value="11" />
		<input type="submit" value="OK" />
		</form>
		<?php
	} else {
		?>
		<table border="0"><tr><td>
		<form type="get" action="">
		<input type="hidden" name="id" value="11" />
		<input type="hidden" name="id2" value="3" />
		<input type="hidden" name="do" value="1" />
                <input type="hidden" name="wikipage" value="<?php echo($wikipage); ?>" />
		Are you sure to delete <strong><?php echo($wikipage); ?></strong> page? <input type="submit" value="Yes">
		</form></td><td>
		<form type="get" action="">
		<input type="hidden" name="id" value="11">
		<input type="submit" value="No">
		</form></td></tr></table>
		<?php
	}
	    