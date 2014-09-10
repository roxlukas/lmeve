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

?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	<?php

		$nr=$_GET['nr'];	
		        
		if (!ctype_digit($nr)) {
			die("Wrong parameter nr.");
		}
		$nr=addslashes($nr);
		if (db_count("SELECT `errorID` from `apistatus` WHERE `errorID`=$nr")==0) {
			echo("Such record does not exist.");
			return;
		}
		$do=$_GET['do'];
		
		$status=db_asocquery("SELECT * FROM `apistatus` WHERE `errorID`=$nr");
		$status=$status[0];
			
	if ($do==1) {
			
			db_uquery("UPDATE `apistatus` SET `errorCode`='0', `errorCount`='0' WHERE `errorID`=$nr;");
			echo('Status has been reset.');
		
		?>
		<input type="hidden" name="id" value="8">
		<input type="hidden" name="id2" value="4">
		<input type="submit" value="OK">
		</form>
		<script type="text/javascript">location.href="index.php?id=8&id2=4";</script>
		<?php
	} else {
		?>
		
		Are you sure to reset <strong><?php echo($status['fileName']); ?></strong> status?<br/>
		
		<table border="0"><tr><td>
		<form type="get" action=""><?php
		echo("<input type=\"hidden\" name=\"nr\" value=\"$nr\">");
		?><input type="hidden" name="id" value="5"/>
		<input type="hidden" name="id2" value="8"/>
		<input type="hidden" name="do" value="1"/>
		<input type="submit" value="Yes">
		</form></td><td>
		<form type="get" action="">
		<input type="hidden" name="id" value="8"/>
                <input type="hidden" name="id2" value="4"/>
		<input type="submit" value="No">
		</form></td></tr></table>
		<?php
	}

		?>
		

