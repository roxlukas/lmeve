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

		$nr=$_GET['nr'];
		if (!ctype_digit($nr)) {
					die("Wrong parameter nr.");
		}
		$do=$_GET['do'];		
		
		if (db_count("SELECT `id` from `message` WHERE `id`=$nr")==0) die("Such record does not exist.");
		
		$message=message("WHERE m.`id`=$nr");
		$message=$message[0];
		
	if ($do==1) {
		
		if ($message['msgto']==$_SESSION['granted']) {	
			$sql="DELETE FROM `message` WHERE `id`=$nr";
			db_uquery($sql);
			echo('Message deleted.<br><br>');
			echo('<script type="text/javascript">location.href="index.php?id=4";</script>');
		} else {
			$do_logu=sprintf("<b>Brak uprawnie�</b> przy pr�bie skasowania wiadomo�ci ID=<b>%d</b> login: <b>%s</b>.",$nr,$user[$_SESSION['granted']]);
			loguj("../var/access.txt",$do_logu);
			echo('Permission denied.<br><br>');
		}
	?>
		<form type="get" action="">
		<input type="hidden" name="id" value="4">
		<input type="submit" value="OK">
		</form>
		<?php
	} else {
		?>
		<table border="0"><tr><td>
		<form type="get" action=""><?php
		echo("<input type=\"hidden\" name=\"nr\" value=\"$nr\">");
		?><input type="hidden" name="id" value="4">
		<input type="hidden" name="id2" value="4">
		<input type="hidden" name="do" value="1">
		Are you sure to delete this message? <input type="submit" value="Yes">
		</form></td><td>
		<form type="get" action="">
		<input type="hidden" name="id" value="4">
		<input type="submit" value="No">
		</form></td></tr></table>
		<?php
	}
