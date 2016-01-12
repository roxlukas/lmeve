<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditTasks")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=1; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Clear Expired Non-recurring Tasks'; //Panel name (optional)
//standard header ends here
include('tasks.php');

$singletonTaskExpiration=getConfigItem('singletonTaskExpiration','90');

?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	<?php


		$do=$_POST['do'];
			
	if ($do==1) {
            if (!token_verify()) die("Invalid or expired token.");
                if (clearExpiredSingletonTasks()) echo('Tasks have been deleted.'); else echo('No tasks have been deleted.');
		
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
		
                <strong>Are you sure to delete <?php echo(getExpiredSingletonTasksCount()); ?> expired non-recurring Tasks?</strong><br/>
                <em>Non recurring tasks expire after <?=$singletonTaskExpiration?> days.</em><br/>
		
		<table border="0"><tr><td>
		<form method="post" action="?id=1&id2=7"><?php
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