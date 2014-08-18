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
?>
<script type="text/javascript" src="skrypty.js"></script>
<div class="tytul">
Settings
</div><div class="tleft">
<h2>Preferences</h2>
<input type="button" onclick="gohref('?id=5&id2=2');" value="Change password"><br><br>
<form method="post" action="?id=5&id2=5">
<table border="0" cellspacing="2" cellpadding="0">
<?php  token_generate(); ?>
<tr><td>Default tab after logon:</td><td><select name="prefs1">
<?php

$prefs=getprefs();

global $menu;
foreach ($menu as $i => $menuitem) {
	echo('<option value="');
	echo($i);
	if ($prefs['defaultPage']==$i) {
		echo('" selected>');
	} else {
		echo('">');
	}
	echo($menuitem['name']);
	echo('</option>');
}
?>
</select></td></tr>

<tr>
<td valign="top">CSS stylesheet:</td><td>
<select name="prefs3">
<?php
$dir=scandir('./css');
foreach ($dir as $entry) {
	if (preg_match('/css$/',$entry)) {
		echo('<option value="css/');
		echo($entry);
		if ($prefs['css']=='css/'.$entry) {
			echo('" selected>');
		} else {
			echo('">');
		}
		echo($entry);
		echo('</option>');
	}
}
?>
</select>
</td>
</tr>
</table>

<div class="tleft"><table border="0"><tr>
<td width="60" valign="top"><input type="submit" value="OK"><br></form></td>
<td width="75" valign="top"><form method="get" action="">
<input type="hidden" name="id" value="5"><input type="submit" value="Cancel"><br></td></tr>

</table></div>
</form>

</div>
