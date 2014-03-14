<?
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
	    <h2>Coming sooner than soon<sup>tm</sup></h2>
	<?php

//API IMPORT HERE

	?>
	<form action="" method="get">
	<input type="hidden" name="id" value="<?php echo($MENUITEM); ?>">
	<input type="submit" value="OK">
	</form>
