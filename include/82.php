<?php 
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewActivity")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Statistics'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;
include_once('tasks.php');
include_once('stats.php');
$width=600;

$date=secureGETnum("date");

if (strlen($date)==6) {
	$year=substr($date,0,4);
	$month=substr($date,4,2);
} else {
	$year=date("Y");
	$month=date("m");	
}

    
        ?>

        <a name="top"></a>
            <div class="tytul">
                Industry Statistics for <?php echo("$year-$month"); ?><br>
            </div>

            <div class="tekst">

            <?php

            ?>
            <a href="#down">Scroll down</a>
            </div>

            <?php
            $corps=db_asocquery("SELECT * FROM apicorps;");
            foreach ($corps as $corp) { //begin corps loop
                //echo("<h1><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
                ?>
                <div id="corp">
                        <h1><img src="https://imageserver.eveonline.com/Corporation/<?=$corp['corporationID']?>_64.png" style="vertical-align: middle;"> <?=$corp['corporationName']?></h1>
                        <div id="wrapper" style="overflow: hidden;">
                            <div id="col1" style="float: left;">
                                <?php showIndustryActivities($corp['corporationID'],$year, $month, getIndustryActivities($corp['corporationID'], $year, $month)) ?>
                            </div>
                            <div id="col2" style="float: left;">
                                <?php showIndustryStats(getIndustryStats($corp['corporationID'], $year, $month)) ?>
                            </div>
                        </div>
                </div>
                <?php
                //GRAPHING		

            
			
			
            }//end corps loop
		?>
		
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
			
		    </div><br>
		
