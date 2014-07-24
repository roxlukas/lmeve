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
$points=db_asocquery("SELECT rac.`activityName`,cpt.* FROM $LM_EVEDB.`ramActivities` rac JOIN `cfgpoints` cpt ON rac.`activityID`=cpt.`activityID` ORDER BY `activityName`;");

function pointshrefedit($nr) {
    echo("<a href=\"index.php?id=5&id2=11&nr=$nr\" title=\"Click to edit this activity\">");
}

?>
<div class="tytul">
<?php echo($PANELNAME); ?>
</div>

<?php
    echo('<table class="lmframework">');
    echo('<tr><th>');
    echo('Activity');
    echo('</th><th>');		
    echo('Hours');
    echo('</th></tr>');
    foreach($points as $point) {
        echo('<tr><td>');
        pointshrefedit($point['activityID']);
        echo($point['activityName']);
        echo("</a>");
        echo('</td><td>');
        pointshrefedit($point['activityID']);
        echo($point['hrsPerPoint']);
        echo("</a>");
        echo('</td></tr>');
    }
    echo('</table>');
?>