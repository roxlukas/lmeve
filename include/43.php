<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
/*if (!checkrights("Administrator,ViewTimesheet")) { //"Administrator,ViewOverview"
	global $LANGNORIGHTS;
	echo("<h2>$LANGNORIGHTS</h2>");
	return;
}*/
$MENUITEM=4; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Messages'; //Panel name (optional)
//standard header ends here
?>

		    <div class="tytul">
			Messages<br>
		    </div>
		    
<?php
		    $nr=$_GET['nr'];
		    if (!ctype_digit($nr)) {
				die("Nieprawid�owy parametr nr.");
		    }
			if (db_count("SELECT `id` from message WHERE `id`=$nr")==0) die("Such record does not exist.");
			$message=message("WHERE m.`id`=$nr");
			$message=$message[0];

if ($message['msgto']==$_SESSION['granted']) {
		    echo('<form method="get" action="">');
		    echo('<input type="hidden" name="nr" value="');
		    echo($nr);
		    echo('"><input type="hidden" name="id" value="4"><input type="hidden" name="id2" value="4"><input type="submit" value="Delete message"><br>');
		    echo('</form>');

		    echo('<table border="0" cellspacing="2" cellpadding="">');
		    echo('<tr><td width="150" class="tab-header">From:<br></td><td width="200" class="tab-header">');
		    echo($message['od']);
		    echo('<br></td></tr>');
		    echo('<tr><td width="150" class="tab-header">To:<br></td><td width="200" class="tab-header">');
		    echo($message['do']);
		    echo('<br></td></tr>');
		    echo('<tr><td width="150" class="tab-header">Date:<br></td><td width="200" class="tab-header">');
		    echo(stripslashes($message['msgdate']));
		    echo('<br></td></tr>');
		    echo('<tr><td width="150" class="tab-header">Topic:<br></td><td width="200" class="tab-header">');
		    echo(stripslashes($message['msgtopic']));
		    echo('<br></td></tr>');
		    echo('<tr><td width="150" class="tab-header"><br></td><td width="200" class="tab-header">');
		    echo('<table border="0" width="420" cellspacing="0" cellpadding="2"><tr><td width="3" height="40"></td><td class="tab-text" valign="top">');
		    echo(stripslashes($message['msg']));
		    echo('</td><td width="5"></td></tr></table>');
		    echo('</td></tr></table></div>');
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="50" valign="top"><form method="get" action=""><input type="hidden" name="id" value="4"><input type="submit" value="OK"></form>');
		    echo('</td>');
		    echo('<td width="80" valign="top"><form method="get" action=""><input type="hidden" name="id" value="4"><input type="hidden" name="id2" value="1">');
		    echo('<input type="hidden" name="nr" value="new"><input type="hidden" name="adr" value="');
		    echo($message['msgfrom']);
		    echo('"><input type="hidden" name="re" value="');
		    echo(stripslashes($message['msgtopic']));
		    echo('"><input type="hidden" name="txt" value="');
			$msgre=str_replace("<br>","\r\n",stripslashes($message['msg']));
			$msgre=str_replace('"','',$msgre);
		    echo($msgre);
		    echo('"><input type="submit" value="Reply"></form>');
		    echo('</td></tr></table></div>');

		    //odznacz jako przeczytane
		    $sql="UPDATE `message` SET `msgread`=1 WHERE `id`=$nr";
		    db_uquery($sql);
			
} else {
		    echo('Permission denied.');
		    $dologu=sprintf("<b>Brak uprawnie�</b> przy pr�bie odczytu wiadomo�ci ID=<b>%d</b> login: <b>%s</b>.",$nr,$user[$_SESSION['granted']]);
		    loguj("../var/access.txt",$dologu);
}
		?>

