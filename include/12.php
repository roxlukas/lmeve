<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditTasks")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=1; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Edit Tasks'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

?>	    <div class="tytul">
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
			if (db_count("SELECT `taskID` from `lmtasks` WHERE `taskID`=$nr")==0) {
				echo("Such record does not exist.");
				return;
			}
		}

		$typeID=secureGETnum('typeID'); //zbierz zmienne od przegladarki
		$characterID=secureGETnum('characterID');
		$activityID=secureGETnum('activityID');
		$runs=secureGETnum('runs');
                $structureID=secureGETstr('structureID');
                if (!ctype_digit($structureID)) {
			if ($structureID=='null') {
				$structureID='NULL';				
			} else {
				die("Wrong parameter structureID.");
			}
		}
		$singleton=secureGETstr('singleton',3);
		if ($singleton=='on') {
			$singleton=1;
		} else {
			$singleton=0;
		}
		$autoadd_copy=secureGETstr('autoadd_copy',3);
                $autoadd_invention=secureGETstr('autoadd_invention',3);
                $autoadd_tech1=secureGETstr('autoadd_tech1',3);
		if ($autoadd_copy=='on') $autoadd_copy=TRUE; else $autoadd_copy=FALSE;
                if ($autoadd_invention=='on') $autoadd_invention=TRUE; else $autoadd_invention=FALSE;
                if ($autoadd_tech1=='on') $autoadd_tech1=TRUE; else $autoadd_tech1=FALSE;
		
		//walidacja
		if(empty($typeID)) {
			echo('typeID cannot be empty.');
			return;
		}
		if(empty($characterID)) {
			echo('characterID cannot be empty.');
			return;
		}
		if(empty($activityID)) {
			echo('activityID cannot be empty.');
			return;
		}
		if(empty($runs)) {
			echo('Number of runs cannot be empty.');
			return;
		}	
		

//i jesli wszystko ok - zapis
if ($new) {
			$sql="INSERT INTO `lmtasks` VALUES (
			DEFAULT,
			$characterID,
			$typeID,
			$activityID,
			$runs,
			UTC_TIMESTAMP(),
			$singleton,
                        $structureID
			);";
                        //echo("DEBUG: $sql");
			db_uquery($sql);
			
			if ($autoadd_invention || $autoadd_copy || $autoadd_tech1) {
			    $sql="SELECT itp.`typeID`, itp.`typeName`, iit.`blueprintTypeID`, iit.`techLevel` AS bpoTechLevel, iit.`techLevel` AS itemTechLevel, itp.`groupID`, ing.`categoryID`, imt.`parentTypeID`, iit1.`blueprintTypeID` AS bpoT1TypeID, itp.`portionSize`
				FROM $LM_EVEDB.`invTypes` itp
				JOIN $LM_EVEDB.`invGroups` ing
				ON itp.`groupID`=ing.`groupID`
				LEFT JOIN $LM_EVEDB.`invBlueprintTypes` iit
				ON itp.`typeID`=iit.`productTypeID`
				JOIN $LM_EVEDB.`invMetaTypes` imt
				ON itp.`typeID`=imt.`typeID`
				LEFT JOIN $LM_EVEDB.`invBlueprintTypes` iit1
				ON imt.`parentTypeID`=iit1.`productTypeID`
				WHERE itp.`typeID`=$typeID
				AND imt.`metaGroupID`=2
				AND itp.`published`=1;";
				//echo("DEBUG: $sql");
				//return;
				$typeName=db_asocquery($sql);
				$typeName=$typeName[0];
				
				//invention is chance based. Assuming 50% for modules (perfect skills) and 33% ships
				if ($typeName['categoryID']!=6) {
					$multiplier=2; //need to make it categoryID dependent! - ships need x3, modules need x2
				} else {
					$multiplier=3;
				}
				
				//modules and ammo yield 10 run BPCs, rigs and ships - 1 run BPCs
				//cloaking device is best invented with 1 run BPC
				if (($typeName['categoryID']==6) || ($typeName['typeID']==11577) || ($typeName['typeID']==11578)) {
					$bpcRuns=1;
				} else {
					$bpcRuns=10;
				}
				
				$multipliedRuns=floor($multiplier*$runs/$typeName['portionSize']/$bpcRuns);
				if ($multipliedRuns < 1) $multipliedRuns=1;
				
				//insert invention task
                                if ($autoadd_invention) {
                                    $sql="INSERT INTO `lmtasks` VALUES (
                                    DEFAULT,
                                    $characterID,
                                    ${typeName['blueprintTypeID']},
                                    8,
                                    $multipliedRuns,
                                    UTC_TIMESTAMP(),
                                    $singleton,
                                    NULL
                                    );";
                                    db_uquery($sql);
                                }
				
				//insert copy task
                                if ($autoadd_copy) {
                                    $sql="INSERT INTO `lmtasks` VALUES (
                                    DEFAULT,
                                    $characterID,
                                    ${typeName['bpoT1TypeID']},
                                    5,
                                    $multipliedRuns,
                                    UTC_TIMESTAMP(),
                                    $singleton,
                                    NULL
                                    );";
                                    db_uquery($sql);
                                }
                                
                                //insert tech 1 manufacturing task
                                if ($autoadd_tech1) {
                                    $sql="INSERT INTO `lmtasks` VALUES (
                                    DEFAULT,
                                    $characterID,
                                    ${typeName['parentTypeID']},
                                    1,
                                    $runs,
                                    UTC_TIMESTAMP(),
                                    $singleton,
                                    NULL
                                    );";
                                    db_uquery($sql);
                                }
			}
						
			echo('Task added successfully.<br><br>');
		} else {
			$sql="UPDATE `lmtasks` SET
			`characterID`=$characterID,
			`typeID`=$typeID,
			`activityID`=$activityID,
			`runs`=$runs,
			`singleton`=$singleton,
                        `structureID`=$structureID
			WHERE `taskID`=$nr;";
                        //echo("DEBUG: $sql");
			db_uquery($sql);
			echo('Task modified successfully.<br><br>');
		}
		?>
		<br>
		<form method="get" action="">
		<input type="hidden" name="id" value="1" />
		<input type="hidden" name="id2" value="0" />
		<input type="hidden" name="nr" value="<?php echo($characterID); ?>" />
		<input type="submit" value="OK" />
		</form>
		<script type="text/javascript">location.href="index.php?id=1&id2=0&nr=<?php echo($characterID); ?>";</script>
