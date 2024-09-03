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

$_SESSION['ssomode'] = "addkey";

?>
<div class="tytul">
<?php echo($PANELNAME); ?>
</div>
Redirecting to EVE Online SSO...<br/>
<br/>
Click the button if it didn't happen automatically:
<script type="text/javascript">
    jsRedirect("ssologin.php");
</script>
<input type="button" value="OK" onclick="location.href='ssologin.php';"/>