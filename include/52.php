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
<a name="top"></a>
<div class="tytul">
Settings
</div>
<div class="tleft">
<h2>Change password</h2><br>
<?php
    if (secureGETnum('legacy')==1) echo('<h3>Your password must be changed.</h3><em><img src="'.getUrl().'ccp_icons/38_16_208.png" alt="(i)"/> Your old password was hashed using less secure md5 algorithm. New password will be hashed 10.000 times using a more secure algorithm, sha256.</em>');
?>
<form method="post" action="?id=5&id2=3">
<?php
token_generate();
//<input type="hidden" name="force_relog" value="yes">
?>
<table border="0" cellspacing="2" cellpadding="0"><tr><td width="150" class="tab">
Old password:<br>
</td><td width="200" class="tab">
<input type="password" name="oldpass" value="">
</td></tr>
<tr><td width="150" class="tab">
New password:<br>
</td>
<td width="200" class="tab">
<input type="password" name="newpass" value="">
</td></tr>
<tr><td width="150" class="tab">
Confirm password:<br>
</td>
<td width="200" class="tab">
<input type="password" name="newpass2" value="">
</td></tr></table>
<table border="0" cellspacing="0" cellpadding="0"><tr><td valign="top">
<input type="submit" value="Change password"><br>
</form>
</td><td valign="top">
<form method="get" action="">
<input type="hidden" name="id" value="5">
<input type="submit" value="Cancel"><br>
</form>
</td></tr></table>
