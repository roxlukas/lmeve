<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditHoursPerPoint")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Edit hours-per-point'; //Panel name (optional)
//standard header ends here
global $LM_EVEDB;

?>		    <div class="tytul">
			<?php echo($PANELNAME); ?><br>
		    </div>
		    
<?php

                if (!token_verify()) die("Invalid or expired token.");

		$new=FALSE;
		$nr=$_GET['nr'];
		if (!ctype_digit($nr)) {
			if ($nr=='new') {
				$new=TRUE;				
			} else {
				echo("Wrong parameter nr.");
                                return;
			}
		}
		 
		if(!$new) {
			if (db_count("SELECT `activityID` from `cfgpoints` WHERE `activityID`=$nr;")==0) {
                            echo("Such record does not exist.");
                            return;
			}
		}

		$hrsPerPoint=secureGETnum('hrsPerPoint'); //zbierz zmienne od przegladarki

		if ($new) {
                        echo("Inserting NEW record is currently not possible for this table.");
                        return;
			$sql="INSERT INTO `cfgpoints` VALUES (
			DEFAULT,
			$hrsPerPoint
			);";
			db_uquery($sql);
			echo('Hours per point correctly saved.<br><br>');
		} else {
			$sql="UPDATE `cfgpoints` SET
			`hrsPerPoint`=$hrsPerPoint
			WHERE `activityID`=$nr;";
			db_uquery($sql);
			echo('Hours per point correctly saved.<br><br>');
		}
		?>
		<br>
                <input type="button" value="OK" onclick="location.href='?id=5&id2=10';">
		<script type="text/javascript">location.href="index.php?id=5&id2=10";</script>