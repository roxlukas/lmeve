<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,EditHoursPerPoint")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Edit hours-per-point'; //Panel name (optional)
//standard header ends here
global $LM_EVEDB;


?>
<div class="tytul">
<?php echo($PANELNAME); ?>
</div>

<?php
    $new=FALSE;
    $nr=$_GET['nr'];
    if (!ctype_digit($nr)) {
        if ($nr=='new') {
            $new=TRUE;					
        } else {
            echo("Wrong parameter nr.");
            return;
        }
    }
    if (!$new) {
        $nr=addslashes($nr);
        if (db_count("SELECT `activityID` from `cfgpoints` WHERE `activityID`=$nr;")==0) {
            echo("Such record does not exist.");
            return;
        }
        $data=db_asocquery("SELECT rac.`activityName`,cpt.*
                FROM $LM_EVEDB.`ramActivities` rac
                JOIN `cfgpoints` cpt
                ON rac.`activityID`=cpt.`activityID`
                WHERE cpt.`activityID`=$nr;");
        $data=$data[0];
    } else {
        $data=array();
    }
    
    echo('<form method="post" action="?id=5&id2=12&nr='.$nr.'">');
    token_generate();
		    echo('<table border="0" cellspacing="2" cellpadding=""><tr><td width="150" class="tab">');
		    echo('Activity:<br></td><td width="200" class="tab">');
                        echo($data['activityName']);
		    echo('</td></tr>');
		    
		    echo('<tr><td width="150" class="tab">Points per hour:<br></td><td width="200" class="tab">');
                        echo("<input type=\"text\" name=\"hrsPerPoint\" value=\"${data['hrsPerPoint']}\" />");
		    echo('</td></tr>');

		    echo('</table>');
		    
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="60" valign="top"><input type="submit" value="OK"><br></form></td><td width="75" valign="top"><input type="button" value="Cancel" onclick="location.href=\'?id=5&id2=10\'"></td>');
		    echo('</tr></table></div>');
?>