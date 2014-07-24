<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditBuyOrders")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=3; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Buyback Order Details'; //Panel name (optional)
//standard header ends here

include('market.php');

$nr=secureGETnum('nr');

?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	<?php

	//$orders=db_asocquery("SELECT * FROM `lmbuyback` WHERE `orderID`=$nr;");
	$orders=getBuybackOrders("WHERE `orderID`=$nr");
	if (count($orders)!=1) {
		echo('Wrong parameter nr.');
		return;
	}

	showBuybackOrder($orders[0]);

	?>
	<form action="" method="get">
	<input type="hidden" name="id" value="<?php echo($MENUITEM); ?>">
	<input type="submit" value="OK">
	</form>
