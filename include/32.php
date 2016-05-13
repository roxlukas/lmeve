<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewBuyCalc")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=3; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Buy Calculator'; //Panel name (optional)
//standard header ends here

include_once("market.php");
include_once("configuration.php");
global $LM_EVEDB;

if (!token_verify()) die("Invalid or expired token.");

$buycalc=db_asocquery("SELECT buy.`typeID`, itp.`typeName`, itp.`groupID`, apr.`max`
		FROM `cfgbuying` AS buy
		JOIN $LM_EVEDB.`invTypes` AS itp
		ON buy.`typeID`=itp.`typeID`
		JOIN `apiprices` AS apr
		ON buy.`typeID`=apr.`typeID`
		WHERE  apr.`type`='buy'
		ORDER BY itp.`typeName`
		");
		
$total=0;

$buyCalcPriceModifier=getConfigItem('buyCalcPriceModifier', 1.0);

foreach($buycalc as $row) {
        $row['max']=round($buyCalcPriceModifier * $row['max'],2);
	$q=secureGETnum('q_'.$row['typeID']);
	if ($q>0) {
		$order[$row['typeID']]['typeID']=$row['typeID'];
		$order[$row['typeID']]['quantity']=$q;
		$order[$row['typeID']]['unitprice']=$row['max'];
		$total+=$row['max']*$q;
		$items=$items.$q.'x '.$row['typeName'].'<br />';
	}
}

$timestamp=time();
$order_string=serialize($order);
$order_shorthash=shorthash($order_string.$timestamp);
$order_fullhash=longhash($order_string.$timestamp);

db_uquery("INSERT INTO lmbuyback VALUES(
	DEFAULT,
	'$order_string',
	$timestamp,
	'$order_shorthash',
	'$order_fullhash',
	${_SESSION['granted']}
);");

?>	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	<?php
echo("<h2>Use the following values for your contract:</h2>");
echo('<table>');
echo("<tr><td class=\"tab\"><strong>Contract price:</strong></td><td class=\"tab\"><input type=\"text\" value=\"$total\" size=\"30\" name=\"isk\"> ISK</td></tr>");
echo("<tr><td class=\"tab\"><strong>Description:</strong></td><td class=\"tab\"><input type=\"text\" value=\"$order_shorthash\" size=\"30\" name=\"hash\"></td></tr>");
echo("<tr><td class=\"tab\"><strong>Items:</strong></td><td class=\"tab\">$items</td></tr>");
echo('</table>');
//echo("SERIALIZED PAYLOAD: $order_string<br />");
//echo("SERIALIZED PAYLOAD SIZE: <strong>".strlen($order_string)."</strong><br />");

	?>
	<form action="" method="get">
	<input type="hidden" name="id" value="<?php echo($MENUITEM); ?>">
	<input type="submit" value="OK">
	</form>
<center><img src="<?=getUrl()?>img/contract.png" alt="How to setup a contract" /></center>