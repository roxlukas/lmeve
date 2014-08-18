<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewMessages")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=4; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Messages'; //Panel name (optional)
//standard header ends here
?>

		    <div class="tytul">
			New message<br>
		    </div>
		    
<?php
		    //$nr=$_GET['nr'];
		    if (isset($_GET['adr'])) $adrset=true;
		    $adr=secureGETnum('adr');
		    //echo("<hr>adr=$adr<hr>");
		    $re=secureGETstr('re',128);
		    $txt=secureGETstr('txt',4096);
			$admini=getusers();
		    echo('<form method="post" action="?id=4&id2=2">');
                    token_generate();
		    echo('<input type="hidden" name="nr" value="new"><input type="hidden" name="id" value="4"><input type="hidden" name="id2" value="2"><br>');

		    echo('<table border="0" cellspacing="2" cellpadding="">');
		    echo('<tr><td width="150" class="tab-header">To:<br></td><td width="200" class="tab-header">');
			if (!$adrset) {
				echo('<select name="msgto">');
				echo('<option value="-1">* everyone</option>');
		    		foreach($admini as $row) {
						if ($row['act']==1) {
							echo('<option value="');
							echo($row['userID']);
							echo('">');
							echo($row['login']);
							echo('</option>');
						}
		    		}
				echo('</select>');
			} else {
				echo('<input type="hidden" name="msgto" value="');
				echo($adr);
				echo('">');
				$admin=asoc_row($admini,'userID',$adr);
				echo($admin['login']);
			}
		    echo('<br></td></tr>');
		    echo('<tr><td width="150" class="tab-header">Topic:<br></td><td width="200" class="tab-header">');
		    echo('<input type="text" size="60" name="msgtopic" value="');
		    if (!empty($re)) {
		    	echo("RE: $re");
		    }
		    echo('">');
		    echo('<br></td></tr>');
		    echo('<tr><td width="150" class="tab-header"><br></td><td width="200" class="tab-header">');
		    echo('<table border="0" width="310" cellspacing="0" cellpadding="2"><tr><td width="3" height="40"></td><td bgcolor="#e0f0ff" valign="top">');
		    echo('<textarea name="msg" cols="80" rows="16">');
		    if (!empty($txt)) {
		    	$txt=str_replace("<br>","\r\n",stripslashes($txt));
		    	$txt=stripslashes($txt);
		    	$admin=asoc_row($admini,'userID',$adr);
		    	printf("\n\n%s wrote:\n>> %s",$admin['login'],$txt);
		    }
		    echo('</textarea>');
		    echo('</td><td width="5"></td></tr></table>');
		    echo('</td></tr></table></div>');
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="50" valign="top"><input type="submit" value="OK"></form>');
		    echo('<td width="75" valign="top"><form method="get" action=""><input type="hidden" name="id" value="4"><input type="submit" value="Cancel"></form>');
		    echo('</td></tr></table></div>');
		    echo('</form>');

		?>

