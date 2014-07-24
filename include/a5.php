<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditMEPE")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=10; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Item Database'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

$nr=secureGETnum('nr');
$me=secureGETnum('me');
$pe=secureGETnum('pe');


if (!isset($nr)) {
	echo("Wrong parameter nr.");
	return;
}
if (!isset($me)) {
	echo("Wrong parameter me.");
	return;
}
if (!isset($pe)) {
	echo("Wrong parameter pe.");
	return;
}


?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
		<?php
			$mepe=db_asocquery("SELECT * FROM `cfgbpo` WHERE `typeID`=$nr;");
			if (count($mepe)>0) {
				db_uquery("UPDATE `cfgbpo` SET `me`=$me, `pe`=$pe WHERE `typeID`=$nr;"); //delete
			} else {
				db_uquery("INSERT INTO `cfgbpo` VALUES ($nr, $me, $pe);"); //insert
			}
		?>
	<form method="get" action="">
	<input type="hidden" name="id" value="10">
	<input type="hidden" name="id2" value="1">
	<input type="hidden" name="nr" value="<?php echo($nr); ?>">
	<input type="submit" value="OK">
	</form>
	<script type="text/javascript">location.href="index.php?id=10&id2=1&nr=<?php echo($nr); ?>";</script>
