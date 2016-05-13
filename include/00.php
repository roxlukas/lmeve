<?php 
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewTimesheet")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Timesheet'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;
include_once('tasks.php');
include_once('stats.php');
//$rights_viewallchars=checkrights("Administrator,ViewAllCharacters");
//$rights_edithours=checkrights("Administrator,EditHoursPerPoint");

$date=secureGETnum("date");
$aggregate=secureGETstr("aggregate",3);
if ($aggregate=='yes') $aggregate=TRUE; else $aggregate=FALSE;

$width=600;

if (strlen($date)==6) {
	$year=substr($date,0,4);
	$month=substr($date,4,2);
} else {
	$year=date("Y");
	$month=date("m");	
}

function pointshrefedit($nr) {
    echo("<a href=\"index.php?id=5&id2=11&nr=$nr\" title=\"Click to edit this activity\">");
}

		

$pointsDisplayed=false;
	    
		?>

		<a name="top"></a>
		    <div class="tytul">
			Timesheet for <?php echo("$year-$month"); ?><br>
		    </div>
		
		    <div class="tekst">
		    
		    <?php
		    switch ($month) {
				case 1:
					$NEXTMONTH=2;
					$NEXTYEAR=$year;
					$PREVMONTH=12;
					$PREVYEAR=$year-1;
				break;
				case 12:
					$NEXTMONTH=1;
					$NEXTYEAR=$year+1;
					$PREVMONTH=11;
					$PREVYEAR=$year;
				break;
				default:
					$NEXTMONTH=$month+1;
					$NEXTYEAR=$year;
					$PREVMONTH=$month-1;
					$PREVYEAR=$year;
			}
		    ?>
		    <table border="0" cellspacing="3" cellpadding="0">
		    <tr><td>
			<form method="get" action="">
		    <input type="hidden" name="id" value="0">
		    <input type="hidden" name="id2" value="0">
		    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">
		    <input type="submit" value="&laquo; previous month">
			</form>
			</td><td>
			<form method="get" action="">
		    <input type="hidden" name="id" value="0">
		    <input type="hidden" name="id2" value="0">
		    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">
		    <input type="submit" value="next month &raquo;">
			</form>			
			</td></tr></table>
		    <?php /*
		    <a href="?id=<?php echo($MENUITEM); ?>&date=<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">&laquo; previous month</a> |  <a href="?id=<?php echo($MENUITEM); ?>&date=<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">next month &raquo;</a><br/>
		    */ ?>
			<a href="#down">Scroll down</a>
		    </div>
                


		    <?php
                    $points=getPoints();
		    //$ONEPOINT=getConfigItem('iskPerPoint','15000000'); //loaded from db now! :-)
		    		    
		    $corps=db_asocquery("SELECT * FROM apicorps;");
		    foreach ($corps as $corp) { //begin corps loop
                        $days="";
                        $activities="";
				echo("<h1><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
?>
<!--<script>
$(function() {
    $( "#accrd_<?php echo($corp['corporationID']); ?>" ).accordion({
      heightStyle: "content",
      header: "h2"
    });
  });
</script>-->
<div id="accrd_<?php echo($corp['corporationID']); ?>">
    <!--<h2>&raquo; Summary</h2>-->

    <div id="wrapper" style="overflow: hidden;">
        <div id="col1" style="float: left;">
            <?php if (!$pointsDisplayed) {
                    showPoints($points);
                    $pointsDisplayed=true;
                  } 
            ?>
        </div>
        <div id="col2" style="float: left; margin-right: 150px;">
            &nbsp;
        </div>
        <div id="col3" style="float: left;">
            <?php showIndustryStats(getIndustryStats($corp['corporationID'], $year, $month)) ?>
        </div>
    </div>
</div>
    <h2>Timesheet</h2>
    <?php                                
        if(!$aggregate) {
            echo('<a href="?id=0&date='.sprintf("%04d", $year).sprintf("%02d", $month).'&aggregate=yes"><img src="'.getUrl().'img/minus.gif" alt="[-]"> Aggregate by user</a>');
        } else {
            echo('<a href="?id=0&date='.sprintf("%04d", $year).sprintf("%02d", $month).'&aggregate=no"><img src="'.getUrl().'img/plus.gif" alt="[+]"> Break down by character</a>');
        }
    //Timesheet				
        showTimesheet(getTimesheet($corp['corporationID'],$year,$month, $aggregate),$aggregate);
		
}//end corps loop
?>
		
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
			
		    </div><br>
		
