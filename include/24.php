<?
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditPOS")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=2; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Edit Lab/Array'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

include_once 'inventory.php';
?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	<?php

		$new=FALSE;
		    $nr=$_GET['nr'];
		    if (!ctype_digit($nr)) {
				if ($nr=='new') {
					$new=TRUE;					
				} else {
					die("Wrong parameter nr.");
				}
		    }
		    if (!$new) {
				$nr=addslashes($nr);
				if (db_count("SELECT `structureID` from `lmlabs` WHERE `structureID`=$nr")==0) {
					echo("Such record does not exist.");
					return;
				}
                    }
                    //structureName varchar(48)
                    //structureTypeID int
                    //parentTowerID int
                    
                $structureName=secureGETstr('structureName',48);
                $structureTypeID=secureGETnum('structureTypeID');
                $parentTowerID=secureGETnum('parentTowerID');
                
		
		//walidacja
		if(empty($structureTypeID)) {
			echo('structureTypeID cannot be empty.');
			return;
		} else {
                        $types=db_asocquery("SELECT * FROM $LM_EVEDB.`invtypes` WHERE `groupID` IN (397,413) AND `typeID`=$structureTypeID;");
                        if (count($types)==0) {
                            echo('structureTypeID outside permitted range.');
                            return;
                        }
                }
		if(empty($structureName)) {
			echo('structureName cannot be empty.');
			return;
		}
                if(empty($parentTowerID)) {
			echo('parentTowerID cannot be empty.');
			return;
		} else {
                        $towers=getControlTowers("asl.`itemID`=$parentTowerID");
                        if (count($towers)==0) {
                            echo('parentTowerID outside permitted range.');
                            return;
                        }
                }
/*
 * 
structureID
parentTowerID
structureTypeID
structureName
 */		

//i jesli wszystko ok - zapis
if ($new) {
			$sql="INSERT INTO `lmlabs` VALUES (
			DEFAULT,
			$parentTowerID,
                        $structureTypeID,
                        '$structureName'
			);";
			db_uquery($sql);
			echo('Lab added successfully.<br><br>');
		} else {
			$sql="UPDATE `lmlabs` SET
			parentTowerID=$parentTowerID,
                        structureTypeID=$structureTypeID,
                        structureName='$structureName'
			WHERE `structureID`=$nr;";
			db_uquery($sql);
			echo('Lab modified successfully.<br><br>');
		}
		?>
		<br>
		<form method="get" action="">
		<input type="hidden" name="id" value="2" />
		<input type="hidden" name="id2" value="2" />
		<input type="hidden" name="nr" value="<?php echo($characterID); ?>" />
		<input type="submit" value="OK" />
		</form>
		<script type="text/javascript">location.href="index.php?id=2&id2=2&nr=<?php echo($characterID); ?>";</script>
