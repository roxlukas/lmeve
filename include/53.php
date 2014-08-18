<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
/*if (!checkrights("Administrator,ViewTimesheet")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}*/
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Settings'; //Panel name (optional)
//standard header ends here

include('../config/config.php');
?>
<div class="tytul">
Settings
</div>
<div class="tleft">
<?php
//$oldpass=addslashes($_GET["oldpass"]);
//$newpass=addslashes($_GET["newpass"]);
//$newpass2=addslashes($_GET["newpass2"]);
if (!token_verify()) die("Invalid or expired token.");

$oldpass=secureGETstr("oldpass");
$newpass=secureGETstr("newpass");
$newpass2=secureGETstr("newpass2");


if (checkpass($oldpass)) {
	if ($newpass==$newpass2) {
		setpass($newpass);
		echo('Password has been changed. <br>');
		?>
		<form method="get" action="">
		<input type="hidden" name="id" value="5">
		<input type="hidden" name="id2" value="0">
		<input type="submit" value="OK">
		</form>
		<?php
	} else {
		echo('New password and confirmed password are different.');
		?>
<form method="get" action="">
<input type="hidden" name="id" value="5">
<input type="hidden" name="id2" value="2">
<input type="submit" value="OK">
</form>
		<?php
	}
} else {
	$ip = $_SERVER['REMOTE_ADDR'];
	$tmpd=date('d.m.Y G:i:s');
        $id=$_SESSION['granted'];
	$uzytk=getusers("WHERE `userID`=$id;");
        $uzytk=$uzytk[0]['login'];
	$do_logu=sprintf("<b>Unsuccessful password change!</b> login: <b>%s</b>.",$uzytk);
	loguj("../var/access.txt",$do_logu);
	echo('Wrong old password.');
	?>
<form method="get" action="">
<input type="hidden" name="id" value="5">
<input type="hidden" name="id2" value="2">
<input type="submit" value="OK">
</form>
	<?php
}

?>

</div>
