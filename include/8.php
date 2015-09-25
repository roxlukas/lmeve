<?php
//begin
    checksession(); //check if we are called by a valid session
    global $MOBILE;
//routing
    $id=8;
    $id2=$_GET['id2'];
//default route
    if ($id2=='') $id2=2;
//submenu
    ?>
    <strong>Industry</strong>
    <table cellpadding="0" cellspacing="2">
    <tr>
    <?php if (checkrights("Administrator,ViewActivity")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="<?php echo ($id); ?>" />
        <input type="hidden" name="id2" value="2" />
        <input type="submit" value="Industry Activity" />
        </form></td>
    <?php } ?>
      
    <?php if (checkrights("Administrator,ViewActivity")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="<?php echo ($id); ?>" />
        <input type="hidden" name="id2" value="0" />
        <input type="submit" value="Industry: products" />
        </form></td>
    <?php } ?>
    <?php if (checkrights("Administrator,ViewActivity")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="<?php echo ($id); ?>" />
        <input type="hidden" name="id2" value="1" />
        <input type="submit" value="Industry: characters" />
        </form></td>
    <?php } ?> 
    <?php if ($MOBILE) echo('</tr></table><table><tr>'); else echo('<td id="separator" style="width: 10px;"></td>');?>
        
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
<strong>Combat</strong>
<table cellpadding="0" cellspacing="2">
    <tr>
    <?php if (checkrights("Administrator,ViewActivity")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="<?php echo ($id); ?>" />
        <input type="hidden" name="id2" value="3" />
        <input type="submit" value="PVE Activity" />
        </form></td>
    <?php } ?> 
        
        <td id="separator" style="width: 10px;"></td>
        
 <?php if (checkrights("Administrator,ViewActivity")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="<?php echo ($id); ?>" />
        <input type="hidden" name="id2" value="6" />
        <input type="submit" value="PVP Activity" />
        </form></td>
    <?php } ?>
    </tr>
    </table>
<strong>Technical</strong>
<table cellpadding="0" cellspacing="2">
    <tr>
    <?php if (checkrights("Administrator,ViewAPIStats")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="<?php echo ($id); ?>" />
        <input type="hidden" name="id2" value="4" />
        <input type="submit" value="EVE API Statistics" />
        </form></td>
    <?php } ?>
        <?php if (checkrights("Administrator,ViewCDNStats")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="<?php echo ($id); ?>" />
        <input type="hidden" name="id2" value="5" />
        <input type="submit" value="WebGL Proxy Statistics" />
        </form></td>
    <?php } ?>
    </tr>
</table>

    <?php
//end submenu

//controller
    switch ($id2) {
        case 0:
            include("80.php");  //activity - industry - corp - by item
            break;
	case 1:
            include("81.php");  //activity - industry - corp - by character
            break;
        case 2:
            include("82.php");  //activity - overview
            break;
        case 3:
            include("83.php");  //activity - pve
            break;
        case 4:
            include("84.php");  //API Statistics
            break;
        case 5:
            include("85.php");  //WebGL Proxy Statistics
            break;
        case 6:
            include("86.php");  //activity - pvp
            break;
    }
?>

