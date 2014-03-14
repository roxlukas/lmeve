<?
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditPOS")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=2; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Delete Lab/Array'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

?>		    
		    <div class="tytul">
			<?php echo($PANELNAME); ?><br>
		    </div>
		    <?php
		$nr=secureGETnum('nr');
		$do=$_GET['do'];		
		$lab=db_asocquery("SELECT * FROM `lmlabs` WHERE `structureID`=$nr");
		if (count($lab)==0) die("Such record does not exist.");
                $lab=$lab[0];
		
		
	if ($do==1) {
			db_uquery("DELETE FROM `lmlabs` WHERE `structureID`=$nr");
			echo('Lab/Array removed.<br><br>');
			echo('<script type="text/javascript">location.href="index.php?id=2&id2=2";</script>');
	?>
		<form method="get" action="">
		<input type="hidden" name="id" value="2">
		<input type="hidden" name="id2" value="2">
		<input type="submit" value="OK">
		</form>
		<?php
	} else {
		?>
		
		
		<strong>Are you really sure to delete lab "<?php echo($lab['structureName']); ?>"?</strong><br>

		<table border="0"><tr><td>
		<form method="get" action=""><?php
		echo("<input type=\"hidden\" name=\"nr\" value=\"$nr\">");
		?><input type="hidden" name="id" value="2">
		<input type="hidden" name="id2" value="5">
		<input type="hidden" name="do" value="1">
		<input type="submit" value="Yes">
		</form></td><td>
		<form method="get" action="">
		<input type="hidden" name="id" value="2">
		<input type="hidden" name="id2" value="2">
		<input type="submit" value="No">
		</form></td></tr></table>
		<?php
	}
?>