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
	
	
		$message=message_sent("WHERE `msgfrom`=${_SESSION['granted']}");

		include('filter.php');
		//$idmsg=range(0,$ilemsg-1);

		function hrefedit($nr) {
		    echo('<a href="index.php?id=4&id2=7&nr=');
		    echo($nr);
		    echo('">');
		}
		?>
		<a name="top"></a>
		    <div class="tytul">
			Sent<br>
		    </div>
		   
		    <div class="tekst">
			<a href="#down">Scroll down</a>
		    </div>
		    <table cellspacing="2" cellpadding="0">
		    <tr><td width="250" class="tab-header">
			    <b>Topic</b>
		    </td><td width="90" class="tab-header">
			    <b>Sent to</b>
		    </td><td width="100" class="tab-header">
			    <b>Date</b>
		    </td></tr>

		<?php

		foreach($message as $row) {
		  $wyl=0;
		    echo('<tr><td class="tab">');
		    hrefedit($row['id']);
			echo('<img src="'.getUrl().'img/msg.gif" alt="MSG"> ');
		    echo(stripslashes($row['msgtopic']));
		    echo('</a></td><td class="tab">');
		    hrefedit($row['id']);
		    if ($row['msgto']=='-1') {
				echo('* everyone');
		    }
		    echo($row['do']);
		    echo('</a></td><td class="tab">');
		    hrefedit($row['id']);
		    echo(stripslashes($row['msgdate']));
		    echo("</a></td></tr>");
		}
		?>
		</table>
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
		    </div><br>
		<form method="get" action="">
		    <input type="hidden" name="id" value="4">
		    <input type="hidden" name="id2" value="1">
		    <input type="hidden" name="nr" value="new">
		    <input type="submit" value="New message">
		</form>

