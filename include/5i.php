<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='EVE Corp API keys'; //Panel name (optional)
//standard header ends here
global $LM_EVEDB;

if (!token_verify()) die("Invalid or expired token."); ?>
<div class="tytul">
<?php echo($PANELNAME);?>
</div>
<img src="<?=getUrl()?>ccp_icons/7_64_5.png"  alt="Corporation" style="float: left;"/>
	<form action="?id=5&id2=19" method="post">
        <?php token_generate(); ?>
            <table class="lmframework">
                <tr><td>Key ID:</td><td><input type="text" name="keyid" value="" size="12"></td></tr>
                <tr><td>Verification:</td><td><input type="text" name="verification" value="" size="64"></td></tr>
            </table>
    <table><tr><td>    
	<input type="submit" value="OK">
	</form>
    </td><td>
        <form method="get" action="">
		<input type="hidden" name="id" value="5" />
		<input type="hidden" name="id2" value="17" />
		<input type="submit" value="Cancel" />
        </form>
    </td></tr></table>
