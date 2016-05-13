<?php
//standard header for each included file
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOwnCharacters,ViewAllCharacters,EditCharacters")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=9; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Characters'; //Panel name (optional)
//standard header ends here

?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	    <img src="<?=getUrl()?>ccp_icons/2_64_16.png"  alt="Characters" style="float: left;"/><h2>Input your personal API Key to link your in-game characters to your LMeve account</h2>
<?php
//Users sometimes attempt to use LMeve with personal API keys in this form, without first setting up a corp API Key
//so if there are no corp API KEYs set first, this form should not be available to avoid confusion.
$keys=db_asocquery("SELECT COUNT(*) AS `count` FROM `cfgapikeys`;");
$count=$keys[0]['count'];
if ($count > 0) {
?>
            <img src="<?=getUrl()?>ccp_icons/38_16_208.png" alt="(!) " style="float: left;"/><a href="https://community.eveonline.com/support/api-key/CreatePredefined?accessMask=16777216" target="_blank">Click this link to go to EVE Online Support site</a> and generate a predefined personal API key with an access mask of 16777216<br/><br/>
            <strong style="color: red;">Your personal API Key will only be accessed once, and will not be stored in LMeve.</strong><br/><br/>
	<?php



//API IMPORT HERE

	?>
    
	<form action="?id=9&id2=5" method="post">
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
		<input type="hidden" name="id" value="9" />
		<input type="hidden" name="id2" value="0" />
		<input type="submit" value="Cancel" />
        </form>
    </td></tr></table>
<?php
} else {
    ?>
          <strong style="color: red;">You must set up Corp API Key first in "Settings".</strong><br/><br/>  
    <?php
}