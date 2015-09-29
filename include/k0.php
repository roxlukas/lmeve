<?php 
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewKillboard")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Killboard'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;
include_once('killboard.php');
include_once('inventory.php');

$characterID=secureGETnum('characterID');
$corporationID=secureGETnum('corporationID');
$allianceID=secureGETnum('allianceID');
$solarSystemID=secureGETnum('solarSystemID');

if (isset($characterID) || isset($corporationID) || isset($allianceID) || isset($solarSystemID)) {
    //back navigation
    //submenu
        ?>
        <table cellpadding="0" cellspacing="2">
        <tr>
         <?php
    //back butan    
        ?>
            <td>
                <input type="button" onclick="window.history.back();" value="&laquo; back"/>
            </td>
        </tr>
        </table>


        <?php
    //end submenu
    ?>
    <a name="top"></a>
    <div class="tytul">
        Killboard<br/>
    </div>
    <?php
} else {
    //months navigation
    //submenu
        ?>
        <table cellpadding="0" cellspacing="2">
        <tr>
         <?php
    //show proper year-month buttons
        $date=secureGETnum("date");
        if (strlen($date)==6) {
                $year=substr($date,0,4); $month=substr($date,4,2);
        } else {
                $year=date("Y"); $month=date("m");	
        }
        switch ($month) {
            case 1:
                $NEXTMONTH=2; $NEXTYEAR=$year; $PREVMONTH=12; $PREVYEAR=$year-1;
                break;
            case 12:
                $NEXTMONTH=1; $NEXTYEAR=$year+1; $PREVMONTH=11; $PREVYEAR=$year;
                break;
            default:
                $NEXTMONTH=$month+1; $NEXTYEAR=$year; $PREVMONTH=$month-1; $PREVYEAR=$year;
        }
        ?>
            <td>
                <form method="get" action="">
                <input type="hidden" name="id" value="<?php echo ($id); ?>">
                <input type="hidden" name="id2" value="<?php echo ($id2); ?>">
                <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">
                <input type="submit" value="&laquo; previous month">
                </form>
            </td><td>
                <form method="get" action="">
                <input type="hidden" name="id" value="<?php echo ($id); ?>">
                <input type="hidden" name="id2" value="<?php echo ($id2); ?>">
                <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $NEXTYEAR).sprintf("%02d", $NEXTMONTH)); ?>">
                <input type="submit" value="next month &raquo;">
                </form>			
            </td>
        </tr>
        </table>


        <?php
    //end submenu
    ?>
    <a name="top"></a>
    <div class="tytul">
        Killboard for <?php echo("$year-$month"); ?><br/>
    </div>
    <?php
}


    showKills(getKills($month, $year, $corporationID, $allianceID, $characterID, $solarSystemID));
?>
