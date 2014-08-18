<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
/*if (!checkrights("Administrator,ViewTimesheet")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}*/
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Settings'; //Panel name (optional)
//standard header ends here

?>		    <div class="tytul">
			Settings<br>
		    </div>
		<?php
                if (!token_verify()) die("Invalid or expired token.");
		
		$prefs['defaultPage']=secureGETnum('prefs1');
		$prefs['css']=$css=secureGETstr('prefs3',50);
		
		setprefs($prefs);
		
		?>
		Preferences saved.<br>
		<br>
	<form method="get" action="">
	<input type="hidden" name="id" value="5">
	<input type="submit" value="OK">
	</form>
	<script type="text/javascript">location.href="index.php?id=5";</script>
