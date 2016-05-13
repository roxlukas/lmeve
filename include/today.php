<?php
/**********************************************************************************
								LM Framework v3
								
	A simple PHP based application framework.
	
	Contact: pozniak.lukasz@gmail.com
	
	Copyright (c) 2005-2013, �ukasz Po�niak
	All rights reserved.

	Redistribution and use in source and binary forms, with or without modification,
	are permitted provided that the following conditions are met:
	
	Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
	THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS
	BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
	OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
	WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
	OF THE POSSIBILITY OF SUCH DAMAGE.

**********************************************************************************/


//standard header for each included file
checksession(); //check if we are called by a valid session
/*if (!checkrights("ViewOverview")) { //"Administrator,ViewOverview"
	global $LANGNORIGHTS;
	echo("<h2>$LANGNORIGHTS</h2>");
	return;
}*/
$MENUITEM=255; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Today'; //Panel name (optional)
//standard header ends here
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="today">
<tr><td width="1">
	<script type="text/javascript" src="<?=getUrl()?>resizer.js"></script>
  	<script type="text/javascript" src="<?=getUrl()?>skrypty.js"></script>
</td><td width="60%" valign="top">
	<?php
		include("checkpoller.php");
	?>
	<h1><?php
		printf("%s",date('Y'));
	?></h1>
	<h2><?php
		$dnityg=array('Sunday',
		'Monday',
		'Tuesday',
		'Wednesday',
		'Thursday',
		'Friday',
		'Saturday');
		$miesiace=array('',
'January',
'February',
'March',
'April',
'May',
'June',
'July',
'August',
'September',
'October',
'November',
'December');
		$dow=date('w');
		$d=date('d');
		$mi=date('m');
		printf("%s, %s %s",$dnityg[$dow], str2num($d), $miesiace[str2num($mi)]);
	?></h2><?php
		$message=message("WHERE `msgto`=${_SESSION['granted']} AND `msgread`=0");
		$nowe_wiad=0;
		foreach($message as $row) {
			$nowe_wiad=1;
		}
		?>
		<hr></hr>
		
		<?php /*
			echo('<h2>Rights mgmt testing:</h2>');
			$rights=db_asocquery("SELECT * FROM lmrights;");
			foreach($rights as $right) {
				$reply=checkright($right['rightName']);
				if ($reply) $reply='TRUE'; else $reply='FALSE';
				echo("Right: ${right['rightName']} = $reply<br/>");
			}
			echo("<br/>Testing multiple rights: checkrights(\"Administrator,ViewOverview\") = ");
			$reply=checkrights("Administrator,ViewOverview");
			if ($reply) echo('TRUE<br/>'); else echo('FALSE<br/>');
			echo("<br/>Testing multiple rights: checkrights(\"Administrator\") = ");
			$reply=checkrights("Administrator");
			if ($reply) echo('TRUE<br/>'); else echo('FALSE<br/>');
			*/
		?>
		
		
		<h2>Messages</h2>
		<?php
if($nowe_wiad==1) {

		function hrefedit2($nr) {
		    echo('<a href="index.php?id=4&id2=3&nr=');
		    echo($nr);
		    echo('">');
		}

		foreach($message as $row) {
		    hrefedit2($row['id']);
	    	    echo('<img src="'.getUrl().'img/msgnew.gif" alt="MSGnew"> ');
			echo(' <b>');
		    echo($row['msgtopic']);
			echo('</b>, from user <b> ');
		    echo($row['od']);
			echo('</b>, received <b>');
		    echo($row['msgdate']);
		    echo('</b></a><br>');
		}
} else {
?>
<a href="index.php?id=4">No new messages.</a><br>
<?php
}
		?>
<br>
		<form method="get" action="">
		    <input type="hidden" name="id" value="4">
		    <input type="hidden" name="id2" value="1">
		    <input type="hidden" name="nr" value="new">
		    <input type="submit" value="New message">
		</form>

</td><td width="10%">
&nbsp;
</td><td width="30%" valign="top">
	<div class="today-list">
		<hr></hr>
		<h2>My tasks</h2>

		<br>

	</div>
</td>

</tr></table>
