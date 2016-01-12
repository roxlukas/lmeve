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

include_once 'inventory.php';

global $LM_EVEDB;

$rights_edittasks=checkrights("Administrator,EditTasks");

$typeID=secureGETnum('typeID');
$activityID=secureGETnum('activityID');
$characterID=secureGETnum('characterID');
$runs=secureGETnum('runs');

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
				if (db_count("SELECT `taskID` from `lmtasks` WHERE `taskID`=$nr")==0) {
					echo("Such record does not exist.");
					return;
				}
				$task=db_asocquery("SELECT * FROM `lmtasks` WHERE `taskID`=$nr");
				$task=$task[0];
				if ($rights_edittasks) { ?>
				<form action="" method="get">
                                <?php token_generate(); ?>
				<input type="hidden" name="id" value="1">
				<input type="hidden" name="id2" value="4">
				<input type="hidden" name="nr" value="<?php echo($nr); ?>">
				<input type="submit" value="Delete task">
				</form>
				<?php }
			}
			
			//if we have a new typeID coming in...
			if (isset($typeID)) $task['typeID']=$typeID;
			if (isset($activityID)) $task['activityID']=$activityID;
			//or character and number of runs...
			if (isset($characterID)) $task['characterID']=$characterID;
			if (isset($runs)) $task['runs']=$runs;
						
			//now we fill the drop down lists with characters and industry activities
			$chars=db_asocquery("SELECT characterID, name FROM `apicorpmembers` ORDER BY name;");
			$activities=db_asocquery("SELECT `activityID`, `activityName` FROM $LM_EVEDB.`ramActivities` WHERE `published`=1 AND `activityID`>0 ORDER BY activityName;");
                        $labs=getLabs();

		    echo('<form method="post" action="?id=1&id2=2">');
                    token_generate();
		    echo('<input type="hidden" name="nr" value="');
		    echo($nr);
		    echo('">');
		    echo('<table class="lmframework">');
		    
		    echo('<tr><td width="150">');
		    echo('Item:<br></td><td width="200">');
		    if ((!$new)||(isset($task['activityID']))) {
				//variables are set, so we can dig DB for a typeName
				/*$typeName=db_asocquery("SELECT `typeName` FROM $LM_EVEDB.`invTypes` WHERE `typeID`=${task['typeID']}");
				$typeName=$typeName[0]['typeName'];*/
				$typeName=db_asocquery("SELECT itp.`typeID`, itp.`typeName`, ibt.`blueprintTypeID`, iit.`productTypeID`, ibt.`techLevel` AS bpoTechLevel, iit.`techLevel` AS itemTechLevel
				FROM $LM_EVEDB.`invTypes` itp
				LEFT JOIN $LM_EVEDB.`invBlueprintTypes` ibt
				ON itp.typeID=ibt.blueprintTypeID
				LEFT JOIN $LM_EVEDB.`invBlueprintTypes` iit
				ON itp.typeID=iit.productTypeID
				WHERE `typeID`=${task['typeID']}
				AND ( (ibt.`blueprintTypeID` IS NOT NULL) OR (iit.`productTypeID` IS NOT NULL) )
				AND itp.`published`=1;");
				$typeName=$typeName[0];
				echo('<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td width="36">');
				echo("<img src=\"".getTypeIDicon($task['typeID'])."\" title=\"${typeName['typeName']}\" /></td><td style=\"vertical-align: middle;\"> ${typeName['typeName']}</td></tr></table>");
				//var_dump($typeName);
			} else {
				echo("No item selected.<br/>");
			}
		    echo('<input type="hidden" name="typeID" size="25" value="');
		    echo(stripslashes($task['typeID']));
		    echo('"><a href="?id=1&id2=5&nr='.$nr.'"><strong>Search items &raquo;</strong></a>');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150">');
		    echo('Activity:<br></td><td width="200">');
		    echo('<select name="activityID">');
		    foreach($activities as $row) {
				if ($row['activityID']==$task['activityID']) $select='selected'; else $select='';
				echo("<option value=\"${row['activityID']}\" $select>${row['activityName']}</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150">Character:<br></td><td width="200">');
		    echo('<select name="characterID">');
		    foreach($chars as $row) {
				if ($row['characterID']==$task['characterID']) $select='selected'; else $select='';
				echo("<option value=\"${row['characterID']}\" $select>${row['name']}</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');
                    
                    echo('<tr><td width="150">Lab/array:<br></td><td width="200">');
		    echo('<select name="structureID">');
                    if (is_null($row['structureID'])) $select='selected'; else $select='';
		    echo("<option value=\"null\" $select>- none -</option>");
		    foreach($labs as $row) {
				if ($row['facilityID']==$task['structureID']) $select='selected'; else $select='';
				echo("<option value=\"${row['facilityID']}\" $select>".stripslashes($row['itemName'])."</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150">');
		    echo('Quantity:<br></td><td width="200">');
		    echo('<input type="text" name="runs" size="25" value="');
		    echo(stripslashes($task['runs']));
		    echo('">');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150">One time task:<br></td><td width="200">');
		    echo('<input type="checkbox" name="singleton" ');
		    if (($task['singleton']==1)) {
		    	echo('checked>');
		    } else {
				echo('>');
		    }
		    echo('</td></tr>');
		    
		    
		    if (($new) && (!empty($typeName['productTypeID']))) {
				if ($typeName['itemTechLevel']==2) {
                                    ?>
                                        <tr><td width="150">Auto-add copying<br></td><td width="200">
					<input type="checkbox" name="autoadd_copy" >
					</td></tr>
                                        <tr><td width="150">Auto-add invention<br></td><td width="200">
					<input type="checkbox" name="autoadd_invention" checked>
					</td></tr>
                                        <tr><td width="150">Auto-add Tech I Manufacturing<br></td><td width="200">
					<input type="checkbox" name="autoadd_tech1" checked>
					</td></tr>
                                    <?php
				}
			}

		    echo('</table>');
		    
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="60" valign="top"><input type="submit" value="OK"><br></form></td><td width="75" valign="top"><form method="get" action=""><input type="hidden" name="id" value="1"><input type="hidden" name="id2" value="3"><input type="submit" value="Cancel"></form></td>');
		    echo('</tr></table></div>');
		?>