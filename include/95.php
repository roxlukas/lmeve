<?php
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOwnCharacters,ViewAllCharacters,EditCharacters")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=9; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Characters'; //Panel name (optional)
//standard header ends here

include_once("character.php");

global $LM_EVEDB;

if (!token_verify()) die("Invalid or expired token.");
$keyid=secureGETnum('keyid');
$verification=secureGETstr('verification');



$chars=getCharactersXML($keyid,$verification);

$valid_chars=filterByCorps($chars);

$final_chars=filterByMembersApi($valid_chars);

$connected=connectCharacters($final_chars);

?>
            <span class="tytul">
		<?php echo($PANELNAME); ?>
	    </span>
        
        <table class="lmframework">
            <tr><th>Characters available in Your personal API</th><td></td><th>Characters eligible to link</th><td></td><th>Characters available in Corporation API</th></tr>
            <tr><td style="text-align: center;"><?php displayCharacters($chars); ?></td><td><img src="<?=getUrl()?>ccp_icons/9_64_6.png" alt="-&gt;" /></td><td style="text-align: center;"><?php displayCharacters($valid_chars); ?></td><td><img src="<?=getUrl()?>ccp_icons/9_64_6.png" alt="-&gt;" /></td><td style="text-align: center;"><?php displayCharacters($final_chars); ?></td></tr>
        </table>
<?php
    if ($connected>0) {
        echo("<h3>$connected character(s) have been linked to your LMEve account.</h3>"); 
    } else {
        echo('<h3>No characters linked!</h3> If characters are eligible, but do not show in corporation API (last column), please try again later.');
    }
?>
                <form method="get" action="">
		<input type="hidden" name="id" value="9" />
		<input type="hidden" name="id2" value="0" />
		<input type="submit" value="OK" />
		</form>
        <!--
		<script type="text/javascript">location.href="index.php?id=9&id2=0";</script>
	    //-->
