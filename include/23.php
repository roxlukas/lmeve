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
?>	
<div class="tytul">
    <?php echo("$PANELNAME"); ?><br/>
</div>
<?php
                    $rights_editpos=checkrights("Administrator,EditPOS");
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
                                
				$structure=db_asocquery("SELECT * FROM `lmlabs` WHERE `structureID`=$nr");
				$structure=$structure[0];
				if ($rights_editpos) { ?>
				<form action="" method="get">
				<input type="hidden" name="id" value="2">
				<input type="hidden" name="id2" value="5">
				<input type="hidden" name="nr" value="<?php echo($nr); ?>">
				<input type="submit" value="Delete">
				</form>
				<?php }
			} else {
                            $parentTowerID=secureGETnum('parent');
                        }
			
						
			//fill the drop down lists with pos and structures
                        $towers=getControlTowers("TRUE ORDER BY `moonName`");
                        $types=db_asocquery("SELECT * FROM $LM_EVEDB.`invtypes` WHERE `groupID` IN (397,413) ORDER BY `typeName`;");
			
		    echo('<form method="get" action="">');
		    echo('<input type="hidden" name="id" value="2">');
		    echo('<input type="hidden" name="id2" value="4">');
		    echo('<input type="hidden" name="nr" value="');
		    echo($nr);
		    echo('">');
		    echo('<table class="lmframework">');
		    
		    echo('<tr><td width="150">');
		    echo('Structure Name:<br></td><td width="200">');
		    echo('<input type="text" name="structureName" size="48" value="');
		    echo(stripslashes($structure['structureName']));
		    echo('">');

		    echo('</td></tr>');
		    
		    echo('<tr><td width="150">');
		    echo('Structure Type:<br></td><td width="200">');
		    echo('<select name="structureTypeID">');
		    foreach($types as $row) {
				if ($row['typeID']==$structure['structureTypeID']) $select='selected'; else $select='';
				echo("<option value=\"${row['typeID']}\" $select>${row['typeName']}</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150">POS:<br></td><td width="200">');
		    echo('<select name="parentTowerID">');
		    foreach($towers as $row) {
                        if ($new) {
                            if ($row['itemID']==$parentTowerID) $select='selected'; else $select='';
                        } else {
                            if ($row['itemID']==$structure['parentTowerID']) $select='selected'; else $select='';
                        }
			echo("<option value=\"${row['itemID']}\" $select>${row['moonName']}</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');

		    echo('</table>');
		    
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="60" valign="top"><input type="submit" value="OK"><br></form></td><td width="75" valign="top"><form method="get" action=""><input type="hidden" name="id" value="2"><input type="hidden" name="id2" value="2"><input type="submit" value="Cancel"></form></td>');
		    echo('</tr></table></div>');
		?>
		
