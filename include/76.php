<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditRoles")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=7; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Roles'; //Panel name (optional)
//standard header ends here


?>		    
		    <div class="tytul">
			<?php echo($PANELNAME); ?><br>
		    </div>
		    <?php
		$nr=secureGETnum('nr');
		$do=$_POST['do'];		
		
		if (db_count("SELECT `roleID` from `lmroles` WHERE `roleID`=$nr")==0) die("Such record does not exist.");
		
		if ($nr==1) {
			echo('Cannot delete Administrator role.');
			?>
			<form method="get" action="">
			<input type="hidden" name="id" value="7">
			<input type="hidden" name="id2" value="1">
			<input type="submit" value="OK">
			</form>
			<?php
			return;
		}
		
		
	if ($do==1) {
            if (!token_verify()) die("Invalid or expired token.");
            
			db_uquery("DELETE FROM `lmrolerights` WHERE `roleID`=$nr");
			db_uquery("DELETE FROM `lmuserroles` WHERE `roleID`=$nr");
			db_uquery("DELETE FROM `lmroles` WHERE `roleID`=$nr");
			echo('Role has been removed.<br><br>');
			echo('<script type="text/javascript">location.href="index.php?id=7&id2=1";</script>');
	?>
		<form method="get" action="">
		<input type="hidden" name="id" value="7">
		<input type="hidden" name="id2" value="1">
		<input type="submit" value="OK">
		</form>
		<?php
	} else {
		?>
		
		
		<strong>Are you really sure to delete this role?</strong><br>
		<?php
			$sql="SELECT COUNT(*) FROM lmrolerights WHERE roleID=$nr;";
			$ile=db_query($sql);
			$ilepraw=$ile[0][0];
			if ($ilepraw>0) echo("There is $ilepraw rights connected to this role.<br>");
			
			$sql="SELECT COUNT(*) FROM lmuserroles WHERE roleID=$nr;";
			$ile=db_query($sql);
			$ileuser=$ile[0][0];
			if ($ileuser>0) echo("There is $ileuser users using this role.<br>");
		?>
		<table border="0"><tr><td>
		<form method="post" action="?id=7&id2=6"><?php
		echo("<input type=\"hidden\" name=\"nr\" value=\"$nr\">");
                token_generate();
		?>
		<input type="hidden" name="do" value="1">
		<input type="submit" value="Yes">
		</form></td><td>
		<form method="get" action="">
		<input type="hidden" name="id" value="7">
		<input type="hidden" name="id2" value="1">
		<input type="submit" value="No">
		</form></td></tr></table>
		<?php
	}
?>