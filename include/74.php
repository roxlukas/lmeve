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
				if (db_count("SELECT `roleID` from `lmroles` WHERE `roleID`=$nr")==0) {
					echo("Such record does not exist.");
					return;
				}
				$role=db_asocquery("SELECT * from `lmroles` WHERE `roleID`=$nr");
				$role=$role[0];
				if (checkrights("Administrator,EditRoles")) { ?>
				<form action="" method="get">
				<input type="hidden" name="id" value="7">
				<input type="hidden" name="id2" value="6">
				<input type="hidden" name="nr" value="<?php echo($nr); ?>">
				<input type="submit" class="yellow" value="Delete role">
				</form>
				<?php }
			}

		    echo('<form method="post" action="?id=7&id2=5">');
		    echo('<input type="hidden" name="nr" value="');
		    echo($nr);
		    echo('">');
                    token_generate();
		    echo('<table border="0" cellspacing="2" cellpadding=""><tr><td width="150" class="tab">');
		    echo('Role Name:<br></td><td width="200" class="tab"><input type="text" name="roleName" size="25" value="');
		    echo(stripslashes($role['roleName']));
		    echo('"><br></td></tr>');
		    
		    echo('<tr><td width="150" class="tab">Rights:<br></td><td width="200" class="tab">');
		    
			$rights=db_asocquery("SELECT * from lmrights ORDER BY `rightName`;");
		    
		    if (!$new) {
				$rolerights=db_asocquery("SELECT lmr.`roleID`,lrr.`rightID`,lr.`rightName` FROM 
				lmroles lmr
				JOIN lmrolerights lrr
				ON lmr.`roleID`=lrr.`roleID`
				JOIN lmrights lr
				ON lrr.`rightID`=lr.`rightID`
				WHERE lmr.`roleID`=$nr;");
		    }

			echo('<table border="0" cellspacing="2" cellpadding="">');
			foreach ($rights as $right) {
				echo('<tr><td width="150" class="tab">');
				echo("<input type=\"checkbox\" name=\"right_${right['rightID']}\" ");
				$found=FALSE;
				if (count($rolerights) > 0) {
					foreach ($rolerights as $roleright) {
						if ($right['rightID']==$roleright['rightID']) $found=TRUE;
					}
				}
				if ($found) {
					echo('checked>');
				} else {
					echo('>');
				}
				echo($right['rightName']);
				echo('</td></tr>');
			}
			echo('</table>');
		    
		    echo('</td></tr>');

		    echo('</table>');
		    
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="60" valign="top"><input type="submit" value="OK"><br></form></td><td width="75" valign="top"><form method="get" action=""><input type="hidden" name="id" value="7"><input type="hidden" name="id2" value="1"><input type="submit" value="Cancel"></form></td>');
		    echo('</tr></table></div>');
		?>
