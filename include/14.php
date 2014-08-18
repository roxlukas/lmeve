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

?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	<?php

		$nr=$_REQUEST['nr'];	
		        
		if (!ctype_digit($nr)) {
			die("Wrong parameter nr.");
		}
		$nr=addslashes($nr);
		if (db_count("SELECT `taskID` from `lmtasks` WHERE `taskID`=$nr")==0) {
			echo("Such record does not exist.");
			return;
		}
		$do=$_POST['do'];
			
	if ($do==1) {
                        if (!token_verify()) die("Invalid or expired token.");
                        
			$task=db_asocquery("SELECT * FROM `lmtasks` WHERE `taskID`=$nr");
			$task=$task[0];
			
			db_uquery("DELETE FROM `lmtasks` WHERE taskID=$nr;");
			
			echo('Task has been deleted.');
		
		?>
                <form method="get" action="">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="id2" value="0">
		<input type="hidden" name="nr" value="<?php echo($task['characterID']); ?>">
		<input type="submit" value="OK">
		</form>
		<script type="text/javascript">location.href="index.php?id=1&id2=0&nr=<?php echo($task['characterID']); ?>";</script>
		<?php
	} else {
		?>
		
		Are you sure to delete this task?<br/>
		
		<table border="0"><tr><td>
		<form method="post" action="?id=1&id2=4"><?php
		echo("<input type=\"hidden\" name=\"nr\" value=\"$nr\">");
                token_generate();
		?>
		<input type="hidden" name="do" value="1">
		<input type="submit" value="Yes">
		</form></td><td>
		<form method="get" action="">
		<input type="hidden" name="id" value="1">
		<input type="submit" value="No">
		</form></td></tr></table>
		<?php
	}

		?>
		

