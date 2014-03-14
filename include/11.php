<?
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
			$activities=db_asocquery("SELECT `activityID`, `activityName` FROM $LM_EVEDB.`ramactivities` WHERE `published`=1 AND `activityID`>0 ORDER BY activityName;");
                        $labs=db_asocquery("SELECT lml.* FROM `lmlabs` lml ORDER BY lml.`structureName`;");

		    echo('<form method="get" action="">');
		    echo('<input type="hidden" name="id" value="1">');
		    echo('<input type="hidden" name="id2" value="2">');
		    echo('<input type="hidden" name="nr" value="');
		    echo($nr);
		    echo('">');
		    echo('<table border="0" cellspacing="2" cellpadding="">');
		    
		    echo('<tr><td width="150" class="tab">');
		    echo('Item:<br></td><td width="200" class="tab">');
		    if ((!$new)||(isset($task['activityID']))) {
				//variables are set, so we can dig DB for a typeName
				/*$typeName=db_asocquery("SELECT `typeName` FROM $LM_EVEDB.`invtypes` WHERE `typeID`=${task['typeID']}");
				$typeName=$typeName[0]['typeName'];*/
				$typeName=db_asocquery("SELECT itp.`typeID`, itp.`typeName`, ibt.`blueprintTypeID`, iit.`productTypeID`, ibt.`techLevel` AS bpoTechLevel, iit.`techLevel` AS itemTechLevel
				FROM $LM_EVEDB.`invtypes` itp
				LEFT JOIN $LM_EVEDB.`invblueprinttypes` ibt
				ON itp.typeID=ibt.blueprintTypeID
				LEFT JOIN $LM_EVEDB.`invblueprinttypes` iit
				ON itp.typeID=iit.productTypeID
				WHERE `typeID`=${task['typeID']}
				AND ( (ibt.`blueprintTypeID` IS NOT NULL) OR (iit.`productTypeID` IS NOT NULL) )
				AND itp.`published`=1;");
				$typeName=$typeName[0];
				echo('<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td width="36">');
				echo("<img src=\"ccp_img/${task['typeID']}_32.png\" title=\"${typeName['typeName']}\" /></td><td style=\"vertical-align: middle;\"> ${typeName['typeName']}</td></tr></table>");
				//var_dump($typeName);
			} else {
				echo("No item selected.<br/>");
			}
		    echo('<input type="hidden" name="typeID" size="25" value="');
		    echo(stripslashes($task['typeID']));
		    echo('"><a href="?id=1&id2=5&nr='.$nr.'"><strong>Search items &raquo;</strong></a>');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150" class="tab">');
		    echo('Activity:<br></td><td width="200" class="tab">');
		    echo('<select name="activityID">');
		    foreach($activities as $row) {
				if ($row['activityID']==$task['activityID']) $select='selected'; else $select='';
				echo("<option value=\"${row['activityID']}\" $select>${row['activityName']}</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150" class="tab">Character:<br></td><td width="200" class="tab">');
		    echo('<select name="characterID">');
		    foreach($chars as $row) {
				if ($row['characterID']==$task['characterID']) $select='selected'; else $select='';
				echo("<option value=\"${row['characterID']}\" $select>${row['name']}</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');
                    
                    echo('<tr><td width="150" class="tab">Lab/array:<br></td><td width="200" class="tab">');
		    echo('<select name="structureID">');
                    if (is_null($row['structureID'])) $select='selected'; else $select='';
		    echo("<option value=\"null\" $select>- none -</option>");
		    foreach($labs as $row) {
				if ($row['structureID']==$task['structureID']) $select='selected'; else $select='';
				echo("<option value=\"${row['structureID']}\" $select>".stripslashes($row['structureName'])."</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150" class="tab">');
		    echo('Quantity:<br></td><td width="200" class="tab">');
		    echo('<input type="text" name="runs" size="25" value="');
		    echo(stripslashes($task['runs']));
		    echo('">');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150" class="tab">One time task:<br></td><td width="200" class="tab">');
		    echo('<input type="checkbox" name="singleton" ');
		    if (($task['singleton']==1)) {
		    	echo('checked>');
		    } else {
				echo('>');
		    }
		    echo('</td></tr>');
		    
		    
		    if (($new) && (!empty($typeName['productTypeID']))) {
				if ($typeName['itemTechLevel']==2) {
					echo('<tr><td width="150" class="tab">Auto-add invention and copying<!--and Tech 1 manufacturing:--><br></td><td width="200" class="tab">');
					echo('<input type="checkbox" name="autoadd" checked>');
					echo('</td></tr>');
				}
			}

		    echo('</table>');
		    
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="60" valign="top"><input type="submit" value="OK"><br></form></td><td width="75" valign="top"><form method="get" action=""><input type="hidden" name="id" value="1"><input type="hidden" name="id2" value="3"><input type="submit" value="Cancel"></form></td>');
		    echo('</tr></table></div>');
		?>