<?php
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

if (!token_verify()) die("Invalid or expired token.");

$keyid=secureGETnum('keyid');
$verification=secureGETstr('verification');

$ret=db_uquery("INSERT INTO `cfgapikeys` VALUES(DEFAULT,$keyid,'$verification');");

if ($ret===FALSE) die("Error saving API Key in database.");

?>
            <span class="tytul">
		<?php echo($PANELNAME); ?>
	    </span>
        
                <form method="get" action="">
		<input type="hidden" name="id" value="5" />
		<input type="hidden" name="id2" value="17" />
		<input type="submit" value="OK" />
		</form>

		<script type="text/javascript">location.href="index.php?id=5&id2=17";</script>

