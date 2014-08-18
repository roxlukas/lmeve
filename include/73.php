<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditUsers")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=7; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Users'; //Panel name (optional)
//standard header ends here

global $USERSTABLE;

?>		    <div class="tytul">
			Users<br>
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
		 if (!$new) {
			$nr=addslashes($nr);
			if (db_count("SELECT `userID` from `$USERSTABLE` WHERE `userID`=$nr")==0) die("Such record does not exist.");
		 }

		$templogin=secureGETstr('login',24); //zbierz zmienne od przegladarki
		$temppass=secureGETstr('pass',128);
		$tempdefault=secureGETnum('prefs2');
		$tempcss=secureGETstr('prefs3',50);
		$tempact=secureGETstr('act',3);
		
		$roles=db_asocquery("SELECT * FROM lmroles;");
		
		if ($tempact=='on') {
			$tempact=1;
		} else {
			$tempact=0;
		}		
		

//i jesli wszystko ok - zapis
if ($new) {
			$sql="SELECT MAX(`userID`) FROM `$USERSTABLE`;";
			$userid=db_query($sql);
			//var_dump($ret);
			$userid=$userid[0][0];
			$userid++;
			
			$temppass=hashpass($temppass);
			
			$sql="INSERT INTO `$USERSTABLE` VALUES (
			$userid,
			'$templogin',
			'$temppass',
			'127.0.0.1',
			'01.01.2007 12:00',
			$tempdefault,
			'$tempcss',
			$tempact
			);";
			db_uquery($sql);
			//Insert roles
			foreach ($roles as $role) {
				if (secureGETstr('role_'.$role['roleID'],3)=='on') {
					$rsql="INSERT INTO lmuserroles VALUES($userid,${role['roleID']})";
					//echo($rsql."<br>");
					db_uquery($rsql);
				}
			}
			echo('User added successfully.<br><br>');
		} else {
			if (empty($temppass)) $temppass=''; else $temppass="`pass`='".hashpass($temppass)."',"; //je�li puste has�o to nie zmieniaj
			$sql="UPDATE `$USERSTABLE` SET
			`login`='$templogin',
			$temppass
			`defaultPage`=$tempdefault,
			`css`='$tempcss',
			`act`=$tempact
			WHERE `userID`=$nr;";
			//echo($sql);
			db_uquery($sql);
			//update roles
			db_uquery("DELETE FROM `lmuserroles` WHERE `userID`=$nr;");
			foreach ($roles as $role) {
				if(secureGETstr('role_'.$role['roleID'],3)=='on') {
					$rsql="INSERT INTO `lmuserroles` VALUES($nr,${role['roleID']})";
					//echo($rsql."<br>");
					db_uquery($rsql);
				}
			}
			echo('User modified successfully.<br><br>');
		}
		?>
		<br>
		<form method="get" action="">
		<input type="hidden" name="id" value="7">
		<input type="submit" value="OK">
		</form>
		<script type="text/javascript">location.href="index.php?id=7&id2=0";</script>
