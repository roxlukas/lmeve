<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditRoles")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=7; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Roles'; //Panel name (optional)
//standard header ends here

global $USERSTABLE;

?>	    

            <table cellpadding="0" cellspacing="2">
	    <tr><td>
	    <?php if (checkrights("Administrator,EditRoles")) { ?>
	    <form action="" method="get">
		<input type="hidden" name="id" value="7">
		<input type="hidden" name="id2" value="4">
		<input type="hidden" name="nr" value="new">
		<input type="submit" class="yellow" value="Add new role">
		</form>
		<?php } ?>
		</td><td>
		<?php if (checkrights("Administrator,EditUsers")) { ?>
	    <form method="get" action="">
		    <input type="hidden" name="id" value="7">
		    <input type="hidden" name="id2" value="0">
		    <input type="submit" class="yellow" value="Edit users">
		</form>
		<?php } ?>
		</td></tr></table>

            <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>

	    
	    
	<?php
	
	$roles=db_asocquery("SELECT * from lmroles;");
	
	function hrefedit($nr) {
		global $MENUITEM;
		echo("<a href=\"index.php?id=7&id2=4&nr=$nr\">");
	}
	
	?>
				
				<table cellspacing="2" cellpadding="0">
				<tr><td class="tab-header">
					<b>Id</b>
				</td><td class="tab-header" style="text-align: center;">
					<b>Role</b>
				</td>
				</tr>
			   <?php
			
			foreach($roles as $row) {
				echo('<tr><td class="tab">');
				hrefedit($row['roleID']);
				echo("${row['roleID']}");
				echo('</a></td><td class="tab">');
				hrefedit($row['roleID']);
				echo(stripslashes($row['roleName']));
				echo('</a></td>');
				echo('</tr>');
			}
	
	?>
	</table>

	
