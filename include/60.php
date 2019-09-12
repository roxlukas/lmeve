<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewWallet")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=6; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Wallet'; //Panel name (optional)
//standard header ends here
include_once 'wallet.php';

global $LM_EVEDB,$MOBILE;

$date=secureGETnum("date");

if (strlen($date)==6) {
	$year=substr($date,0,4);
	$month=substr($date,4,2);
} else {
	$year=date("Y");
	$month=date("m");
}

		function hrefedit($nr) {
		    echo("<a href=\"index.php?id=$MENUITEM&id2=1&nr=$nr\">");
		}


		?>
		<a name="top"></a>
                
		    <div class="tytul">
			Wallet for <?php echo("$year-$month"); ?><br>
		    </div>
		    <div class="tekst">

		    <?php //Monthly navigation
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
		    <input type="hidden" name="id" value="6">
		    <input type="hidden" name="id2" value="0">
		    <input type="hidden" name="date" value="<?php echo(sprintf("%04d", $PREVYEAR).sprintf("%02d", $PREVMONTH)); ?>">
		    <input type="submit" value="&laquo; previous month">
			</form>
			</td><td>
			<form method="get" action="">
		    <input type="hidden" name="id" value="6">
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
                        <em><img src="<?=getUrl()?>ccp_icons/38_16_208.png" alt="(i)"/> LMeve attempts to predict current month running cost, so to avoid counting previous month's wages and internal money transfers, <strong>refTypeID 37 (Corporation Account Withdrawal) is filtered out</strong>.<br/>
                        Instead, current month wages estimate is subtracted from the wallet totals. <strong>This behavior will be configurable in a future release of LMeve.</strong></em><br />
		    <?php

		    

                    displayWallets($date);

		    
		?>

		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>

		    </div><br>

