<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Settings'; //Panel name (optional)
//standard header ends here

$LOCKFILE="../var/poller.lock";

?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	<?php


		$do=$_GET['do'];
		
			
	if ($do==1) {
			
		if (file_exists($LOCKFILE) && ((time()-filemtime($LOCKFILE)) > 900)) {
			unlink($LOCKFILE);
			echo('API Poller lock file removed - poller restarted.');
		} else {
			echo('Poller lock file is OK.<br/>');
		}
			
		?>
		<form action="index.php" method="get">
		<input type="hidden" name="id" value="8">
		<input type="hidden" name="id2" value="4">
		<input type="submit" value="OK">
		</form>
		<script type="text/javascript">location.href="index.php?id=5&id2=0";</script>
		<?php
	} else {
	
		if (file_exists($LOCKFILE) && ((time()-filemtime($LOCKFILE)) > 900)) {
			echo('Are you sure to remove poller lock file?<br/>');
			?>
			<table border="0"><tr><td>
			<form type="get" action=""><?php
			echo("<input type=\"hidden\" name=\"nr\" value=\"$nr\">");
			?><input type="hidden" name="id" value="5"/>
			<input type="hidden" name="id2" value="9"/>
			<input type="hidden" name="do" value="1"/>
			<input type="submit" value="Yes">
			</form></td><td>
			<form type="get" action="">
			<input type="hidden" name="id" value="8"/>
                        <input type="hidden" name="id2" value="4"/>
			<input type="submit" value="No">
			</form></td></tr></table>
			<?php
		} else {
			echo('Poller lock file is OK.<br/>');
			?>
			<form action="index.php" method="get">
			<input type="hidden" name="id" value="8">
			<input type="hidden" name="id2" value="4">
			<input type="submit" value="OK">
			</form>
			<script type="text/javascript">location.href="index.php?id=8&id2=4";</script>
			<?php
		}
	}
?>
		

