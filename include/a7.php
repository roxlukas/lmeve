<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOreValues")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Ore values table'; //Panel name (optional)
//standard header ends here

include_once 'market.php';
include_once 'materials.php';

global $LM_EVEDB, $DECIMAL_SEP, $THOUSAND_SEP,$USERSTABLE;

generate_meta('Ore Values Chart shows mineral composition and current value of all ores available in EVE Online.');

$ores_raw = getRecycleMaterialsOres();
$ores = filterOresMakeup($ores_raw);
$minerals = filterOresMinerals($ores_raw);

//echo("<h3>Ores</h3><pre>"); var_dump($ores); echo("</pre>");
//echo("<h3>Minerals</h3><pre>"); var_dump($minerals); echo("</pre>");

?>
    <div class="tytul">
        <?php echo($PANELNAME); ?><br>
    </div>

<?php
    $keys=db_asocquery("SELECT * FROM `lmnbapi` lma LEFT JOIN `$USERSTABLE` lmu ON lma.`userID`=lmu.`userID` WHERE lma.`userID`=${_SESSION['granted']};");
    if (count($keys)>0) $apikey='key='.$keys[0]['apiKey'].'&'; else $apikey='';
?>
Also available in LMeve API <a href="api.php?<?=$apikey?>endpoint=ORECHART" target="_blank">api.php?<?=$apikey?>endpoint=ORECHART</a><br/>
<br/>
<?php displayOreChart($ores, $minerals); ?>