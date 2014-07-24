<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewBuyOrder")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=3; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Buy Order'; //Panel name (optional)
//standard header ends here

?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	    <h2>Coming sooner than soon<sup>tm</sup></h2>
	<?php



	?>
	<form action="" method="get">
	<input type="hidden" name="id" value="<?php echo($MENUITEM); ?>">
	<input type="submit" value="OK">
	</form>
