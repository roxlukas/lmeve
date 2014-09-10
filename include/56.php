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

?>
<a name="top"></a>
<div class="tytul">
Settings
</div>
<div class="tleft">
<h2>Edit links</h2><br>
<form method="post" action="?id=5&id2=7">
<?php token_generate(); ?>
<!--<input type="hidden" name="id" value="5">
<input type="hidden" name="id2" value="7">-->
<table border="0" cellspacing="2" cellpadding="0"><tr><td width="150" class="tab">
Links:<br>
</td><td width="200" class="tab">
<textarea id="link" name="link" cols="80" rows="25"><?php 
//backward compatibility
$sql="SELECT * FROM linki LIMIT 1;";
$linki=db_asocquery($sql);
$linki=$linki[0]['link'];
//now use new config table
$sidebar=getConfigItem('leftSidebar',$linki);

echo(stripslashes(htmlspecialchars_decode($sidebar)));
?></textarea>
</td></tr>
</table>
<table border="0" cellspacing="0" cellpadding="0"><tr><td valign="top">
<input type="submit" value="Save"><br>
</form>
</td><td valign="top">
<form method="get" action="">
<input type="hidden" name="id" value="5">
<input type="submit" value="Cancel"><br>
</form>
</td></tr></table>
