<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Settings'; //Panel name (optional)
//standard header ends here

?>		    <div class="tytul">
			Settings<br>
		    </div>
		<?php
                if (!token_verify()) die("Invalid or expired token.");
                
		$blad=0;

		$templink=secureGETstr('link',4096); //zbierz zmienne od przegladarki
                setConfigItem('leftSidebar', $templink);
		
			/*$sql="UPDATE `linki` SET
			link='$templink'
			WHERE TRUE;";
			db_uquery($sql);*/
			echo('Links have been saved.<br><br>');
		?>
		<br>
		<form method="get" action="">
		<input type="hidden" name="id" value="5">
		<input type="submit" value="OK">
		</form>
		<script type="text/javascript">location.href="index.php?id=5";</script>
