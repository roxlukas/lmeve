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
				if (db_count("SELECT `userID` from `$USERSTABLE` WHERE `userID`=$nr")==0) {
					echo("Such record does not exist.");
					return;
				}
				$user=db_asocquery("SELECT * from `$USERSTABLE` WHERE `userID`=$nr");
			}
		    $user=$user[0];

		    echo('<form method="post" action="?id=7&id2=3">');
                    token_generate();
		    echo('<input type="hidden" name="nr" value="');
		    echo($nr);
		    echo('">');
		    echo('<table border="0" cellspacing="2" cellpadding=""><tr><td width="150" class="tab">');
		    echo('Login:<br></td><td width="200" class="tab"><input type="text" name="login" size="25" value="');
		    echo(stripslashes($user['login']));
		    echo('"><br></td></tr>');
		    
		    echo('<tr><td width="150" class="tab">Password:<br></td><td width="200" class="tab"><input type="password" maxlength="128" name="pass" size="25" value="');
		    //echo(stripslashes($admin['pass']));
		    //security breach!
		    echo('"><br></td></tr>');
		    
	    
		    echo('<tr><td width="150" class="tab">CSS stylesheet:<br></td><td width="200" class="tab">');
		    /*
		    echo('<input type="text" maxlength="50" name="prefs3" size="25" value="');
		    echo(stripslashes($user['css']));
		    if ($new) echo("css/page.css");
		    echo('"><br><b>Aideron:</b> css/page.css<br><b>Classic:</b> css/old.css<br><b>Nighlty:</b> css/black.css<br>');
		    */
		    echo('<select name="prefs3">');
			$dir=scandir('./css');
			if ($new) $user['css']="css/page.css";
			foreach ($dir as $entry) {
				if (preg_match('/css$/',$entry)) {
					echo('<option value="css/');
					echo($entry);
					if ($user['css']=='css/'.$entry) {
						echo('" selected>');
					} else {
						echo('">');
					}
					echo($entry);
					echo('</option>');
				}
			}
			echo('</select>');

		    echo('</td></tr>');
		    
		    echo('<tr><td width="150" class="tab">Default tab after logon:<br></td><td width="200" class="tab"><select name="prefs2">');
				global $menu;
				foreach ($menu as $i => $menuitem) {
				echo('<option value="');
				echo($i);
				if ($user['defaultPage']==$i) {
					echo('" selected>');
				} else {
					echo('">');
				}
				echo($menuitem['name']);
				echo('</option>');
			}
			echo('</select><br></td></tr>');
			
			echo('<tr><td width="150" class="tab">Roles:<br></td><td width="250" class="tab">');
		    
		    $roles=db_asocquery("SELECT * from lmroles;");
		    
		    if (!$new) {
				$userroles=db_asocquery("SELECT ut.`userID`,lmr.`roleID`,lmr.`roleName` FROM 
				`$USERSTABLE` ut
				JOIN lmuserroles lur
				ON ut.`userID`=lur.`userID`
				JOIN lmroles lmr
				ON lur.`roleID`=lmr.`roleID`
				WHERE ut.`userID`=$nr;");
		    }

			echo('<table border="0" cellspacing="2" cellpadding="">');
			foreach ($roles as $role) {
				echo('<tr><td width="150" class="tab">');
				echo("<input type=\"checkbox\" name=\"role_${role['roleID']}\" ");
				$found=FALSE;
				if (count($userroles) > 0) {
					foreach ($userroles as $userrole) {
						if ($role['roleID']==$userrole['roleID']) $found=TRUE;
					}
				}
				if ($found) {
					echo('checked>');
				} else {
					echo('>');
				}
				echo($role['roleName']);
				echo('</td></tr>');
			}
			echo('</table>');
		    
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150" class="tab">Enabled:<br></td><td width="250" class="tab">');
		    echo('<input type="checkbox" name="act" ');
		    if (($user['act']==1)||($new)) {
		    	echo('checked>');
		    } else {
				echo('>');
		    }
		    echo('<br></td></tr>');

		    echo('</table>');
		    
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="60" valign="top"><input type="submit" value="OK"><br></form></td><td width="75" valign="top"><form method="get" action=""><input type="hidden" name="id" value="7"><input type="submit" value="Cancel"></form></td>');
		    echo('</tr></table></div>');
		?>
