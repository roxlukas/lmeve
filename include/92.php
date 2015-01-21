<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditCharacters")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=9; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Characters'; //Panel name (optional)
//standard header ends here

global $USERSTABLE;

?>		    <div class="tytul">
			<?php echo($PANELNAME); ?><br>
		    </div>
		    
<?php
                if (!token_verify()) die("Invalid or expired token.");
                
		$new=FALSE;
		$nr=$_POST['nr'];
		if (!ctype_digit($nr)) {
			if ($nr=='new') {
				$new=TRUE;				
			} else {
					die("Wrong parameter nr.");
			}
		}
		 
		if(!$new) {
			if (db_count("SELECT `charID` from `lmchars` WHERE `charID`=$nr")==0) {
				echo("Such record does not exist.");
				return;
			}
		}

		$charID=secureGETnum('charID'); //zbierz zmienne od przegladarki
		$userID=secureGETnum('userID');
		

		if ($new) {
			$character=db_asocquery("SELECT * from `apicorpmembers` WHERE `characterID`=$charID");
			$character=$character[0];
			$sql="INSERT INTO `lmchars` VALUES (
			$charID,
			$userID
			);";
			db_uquery($sql);
			echo('Character linked to user successfully.<br><br>');
		} else {
			$sql="UPDATE `lmchars` SET
			`userID`=$userID,
			`charID`=$charID
			WHERE charID=$nr;";
			db_uquery($sql);
			echo('Character linked to user successfully.<br><br>');
		}
		?>
		<br>
		<input type="button" value="OK" onclick="location.href='?id=9&id2=0';"/>
		<script type="text/javascript">location.href="index.php?id=9&id2=0";</script>