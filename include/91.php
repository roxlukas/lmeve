<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditCharacters")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=9; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Characters'; //Panel name (optional)
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
				if (db_count("SELECT `charID` from `lmchars` WHERE `charID`=$nr")==0) {
					echo("Such record does not exist.");
					return;
				}
				$char=db_asocquery("SELECT * from `lmchars` WHERE `charID`=$nr");
				$char=$char[0];
				if (checkrights("Administrator,EditCharacters")) { ?>
				<form action="" method="get">
				<input type="hidden" name="id" value="9">
				<input type="hidden" name="id2" value="3">
				<input type="hidden" name="nr" value="<?php echo($nr); ?>">
				<input type="submit" class="yellow" value="Disconnect character">
				</form>
				<?php }
			}
			
			$logins=db_asocquery("SELECT userID, login FROM $USERSTABLE ORDER BY login;");
			$chars=db_asocquery("SELECT characterID, name FROM `apicorpmembers` ORDER BY name;");

		    echo('<form method="post" action="?id=9&id2=2">');
                    token_generate();
		    echo('<input type="hidden" name="nr" value="');
		    echo($nr);
		    echo('">');
		    echo('<table border="0" cellspacing="2" cellpadding=""><tr><td width="150" class="tab">');
		    echo('Owner login:<br></td><td width="200" class="tab">');
		    echo('<select name="userID">');
		    foreach($logins as $row) {
				if ($row['userID']==$char['userID']) $select='selected'; else $select='';
				echo("<option value=\"${row['userID']}\" $select>${row['login']}</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150" class="tab">Character:<br></td><td width="200" class="tab">');
		    echo('<select name="charID">');
		    foreach($chars as $row) {
				if ($row['characterID']==$char['charID']) $select='selected'; else $select='';
				echo("<option value=\"${row['characterID']}\" $select>${row['name']}</option>");
		    }
		    echo('</select>');
		    echo('</td></tr>');

		    echo('</table>');
		    
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="60" valign="top"><input type="submit" value="OK"><br></form></td><td width="75" valign="top"><form method="get" action=""><input type="hidden" name="id" value="9"><input type="hidden" name="id2" value="0"><input type="submit" value="Cancel"></form></td>');
		    echo('</tr></table></div>');
		?>
