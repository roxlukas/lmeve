<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditStock")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=10; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Item Database'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

$nr=secureGETnum('nr');
$amount=secureGETnum('amount');
$update=secureGETnum('update');

if (empty($nr)) {
	echo("Wrong parameter nr.");
	return;
}


?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
		<?php
			$stockFlag=db_asocquery("SELECT * FROM `cfgstock` WHERE `typeID`=$nr;");
			if (count($stockFlag)>0) { //If record already exist we can either
                            if ($update==1) { //UPDATE
                                db_uquery("UPDATE `cfgstock` SET `amount`=$amount WHERE `typeID`=$nr;"); 
                            } else { //UNTICK - DELETE
                               db_uquery("DELETE FROM `cfgstock` WHERE `typeID`=$nr;"); //delete
                            }
			} else { //If no record exist we can insert a new one TICK - INSERT
				db_uquery("INSERT INTO `cfgstock` VALUES ($nr,$amount);"); //insert
			}
		?>
	<form method="get" action="">
	<input type="hidden" name="id" value="10">
	<input type="hidden" name="id2" value="1">
	<input type="hidden" name="nr" value="<?php echo($nr); ?>">
	<input type="submit" value="OK">
	</form>
	<script type="text/javascript">location.href="index.php?id=10&id2=1&nr=<?php echo($nr); ?>";</script>
