<?php
//public killboard

set_include_path("../include");
date_default_timezone_set(@date_default_timezone_get());
include_once('../config/config.php'); //load config file
include_once("db.php");  //db access functions
include_once("log.php");  //logging facility
include_once('auth.php'); //authentication and authorization
include_once('menu.php'); //authentication and authorization

include_once('materials.php'); //material related subroutines
include_once('inventory.php'); //inventory and pos related subroutines
include_once('killboard.php'); //inventory and pos related subroutines

include_once("csrf.php");  //anti-csrf token implementation (secure forms)

include_once('configuration.php'); //configuration settings in db

if($LM_FORCE_SSL && $_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

if (getConfigItem('publicKillboard')!='enabled') die('<h1>Public Killboard is disabled.</h1>Enable it in Settings');

function page_kills() {
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
}

function page_singlekill() {
    global $LM_EVEDB;
    include_once('killboard.php');
    include_once('inventory.php');

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
        Kill report<br/>
    </div>
    <center>
    <?php
        $killID=secureGETnum('killID');
        if (empty($killID)) {
            echo("killID cannot be empty.");
            return;
        }
        showKill(getKill($killID));
    ?></center><?php
}

function showpage_public() {
    $id=12;
    $id2=$_GET['id2'];
    switch ($id2) {
        case 0:
            page_kills();  //killboard
            break;
        case 1:
            page_singlekill();  //view single kill
            break;
    }
}

function template_public() {
	global $LM_APP_NAME, $lmver, $LANG, $LM_READONLY;
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<META http-equiv="Content-Type" content="text/html; charset=iso-8859-2">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
	<link rel="alternate" type="application/rss+xml" title="RSS" href="rss.php">
	<title><?php echo("$LM_APP_NAME"); ?> Killboard</title>
	<link type="text/css" href="css/rixx_fullscreen.css" rel="stylesheet">
        <!--<link rel="stylesheet" href="jquery-ui/css/ui-darkness/jquery-ui-1.10.3.custom.min.css" />-->
	<link rel="icon" href="favicon.ico" type="image/ico">
        <script type="text/javascript" src="<?=getUrl()?>jquery-ui/js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="<?=getUrl()?>jquery-ui/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="<?=getUrl()?>chart.js/Chart.min.js"></script>
        <script type="text/javascript" src="<?=getUrl()?>ajax.js"></script>
        <script type="text/javascript" src="<?=getUrl()?>skrypty.js"></script>
	</head>
	<body text="#000000" bgcolor="#FFFFFF">
	<center>
	<table class="tab-container">
	<tr><td width="100%" class="tab-horizbar">
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr><td width="33%" align="left"><div class="top">Logged in as:<b> Guest</b><br></div></td>
                <td width="34%"><div id="evetime" title="Current EVE Time" style="margin-left: auto; margin-right: auto; width: 36px;" class="top">--:--</div>
                <script type="text/javascript">
                    window.setInterval(function(){ showEvetime('evetime'); }, 5000);
                    showEvetime('evetime');
                </script>
                </td>
		<td width="33%"><div class="top2">
		<br></div></div></td></tr>
		</table>
	</td></tr>
	<tr><td width="100%" class="tab-logo">
	<img src="<?=getUrl()?>img/LMeve.png" alt="Logo">
	</td></tr>
	<tr><td width="100%" style="padding: 0;">
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr>

		</tr>
		</table>
	</td></tr>
	<tr><td width="100%" class="tab-horizbar">
	<br>
	</td></tr>
	<tr><td width="100%" style="padding: 0;">
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr>
		<td width="100%" class="tab-main" id="tab-main" valign="top">
			<?php
                            showpage_public();
			?>
		</td>
		</tr>
		</table>
	
	</td></tr>
	<tr><td width="100%" class="tab-horizbar">
	<a href="index.php?id=254">About</a><br>
	</td></tr>
	</table>
	<?php
	include("copyright.php");
	?>
	<script type="text/javascript" src="<?=getUrl()?>resizer.js"></script>
	</center>
	</body>
	</html>
	<?php
}

template_public();
?>