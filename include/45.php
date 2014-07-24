<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewMessages")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=4; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Messages'; //Panel name (optional)
//standard header ends here
?>		    <div class="tytul">
			Messages<br>
		    </div>
		<?php

		$do=$_GET['do'];
		
		
	if ($do==1) {
		$sql="DELETE FROM `message` WHERE `msgto`=${_SESSION['granted']}";
		db_uquery($sql);
		echo('All messages have been deleted.<br><br>');
		echo('<script type="text/javascript">location.href="index.php?id=4";</script>');
	?>
		<form type="get" action="">
		<input type="hidden" name="id" value="4">
		<input type="submit" value="OK">
		</form>
		<?php
	} else {
		?>
		<table border="0"><tr><td>
		<form type="get" action="">
		<input type="hidden" name="id" value="4">
		<input type="hidden" name="id2" value="5">
		<input type="hidden" name="do" value="1">
		Are you sure to delete <b>all</b> messages? <input type="submit" value="Yes">
		</form></td><td>
		<form type="get" action="">
		<input type="hidden" name="id" value="4">
		<input type="submit" value="No">
		</form></td></tr></table>
		<?php
	}
