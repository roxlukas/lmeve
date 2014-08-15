<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewPOS")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=2; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Logistics - Facility Kit'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

include_once 'inventory.php';
include_once 'materials.php';
include_once 'tasks.php';
?>	
<div class="tytul">
    <?php echo("$PANELNAME"); ?><br/>
</div>
<a href="#down">Scroll down</a>
<?php
    $nr=$_GET['nr'];
    if (!ctype_digit($nr)) {
        die("Wrong parameter nr.");
    }
    $nr=addslashes($nr);
    $lab=getLabs("apf.`facilityID`=$nr");
    if (count($lab)==0) {
        echo("Such record does not exist.");
	return;
    }
    $tasks=getTasksByLab($nr);
    
    //var_dump($tasks);
    
    displayFacilityKit($tasks);

?>
<a href="#top">Scroll up</a>
<a name="down"></a>