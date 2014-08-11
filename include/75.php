<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditRoles")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=7; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Roles'; //Panel name (optional)
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
		 if (!$new) {
			$nr=addslashes($nr);
			if (db_count("SELECT `roleID` from `lmroles` WHERE `roleID`=$nr")==0) die("Such record does not exist.");
		 }

		$roleName=secureGETstr('roleName',256); //zbierz zmienne od przegladarki
		
		$rights=db_asocquery("SELECT * FROM lmrights;");
		

		

//i jesli wszystko ok - zapis
if ($new) {
			$sql="SELECT MAX(`roleID`) FROM `lmroles`;";
			$newid=db_query($sql);
			//var_dump($ret);
			$newid=$newid[0][0];
			$newid++;

			$sql="INSERT INTO `lmroles` VALUES (
			$newid,
			'$roleName'
			);";
			db_uquery($sql);

			//Insert rights
			foreach ($rights as $right) {
				if (secureGETstr('right_'.$right['rightID'],3)=='on') {
					$rsql="INSERT INTO lmrolerights VALUES($newid,${right['rightID']})";
					db_uquery($rsql);
				}
			}
			echo('Role added successfully.<br><br>');
		} else {
			
			$sql="UPDATE `lmroles` SET
			`roleName`='$roleName'
			WHERE roleID=$nr;";
			//echo($sql);
			db_uquery($sql);
			//update roles
			db_uquery("DELETE FROM lmrolerights WHERE roleID=$nr;");
			foreach ($rights as $right) {
				if(secureGETstr('right_'.$right['rightID'],3)=='on') {
					$rsql="INSERT INTO lmrolerights VALUES($nr,${right['rightID']})";
					//echo($rsql."<br>");
					db_uquery($rsql);
				}
			}
			echo('Role modified successfully.<br><br>');
		}
		?>
		<br>
		<form method="get" action="">
		<input type="hidden" name="id" value="7">
		<input type="hidden" name="id2" value="1">
		<input type="submit" value="OK">
		</form>
		<script type="text/javascript">location.href="index.php?id=7&id2=1";</script>
