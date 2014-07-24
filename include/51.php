<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Settings'; //Panel name (optional)
//standard header ends here

?><a name="top"></a>
<div class="tytul">
Settings
</div>
<div class="tleft">
<h2>Application logs:</h2><br>
<form method="get" action="">
<input type="hidden" name="id" value="5">
<input type="submit" value="Close">
</form>
<a href="#down">Scroll down</a><br>
<?php
//echo('<textarea cols="80" rows="25">');
echo(czytajlog("../var/access.txt"));
//echo('</textarea>');
?>
<a href="#top">Scroll up</a><br>
<form method="get" action="">
<input type="hidden" name="id" value="5">
<input type="submit" value="Close">
</form>
</div>
<a name="down"></a>
