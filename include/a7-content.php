<?php
include_once 'market.php';
include_once 'materials.php';

function cachedContent() {
    ob_start();
    /**********************************/

    global $LM_EVEDB, $DECIMAL_SEP, $THOUSAND_SEP,$USERSTABLE;

    $ores_raw = getRecycleMaterialsOres();
    $ores = filterOresMakeup($ores_raw);
    $minerals = filterOresMinerals($ores_raw);

    //echo("<h3>Ores</h3><pre>"); var_dump($ores); echo("</pre>");
    //echo("<h3>Minerals</h3><pre>"); var_dump($minerals); echo("</pre>");

    $keys=db_asocquery("SELECT * FROM `lmnbapi` lma LEFT JOIN `$USERSTABLE` lmu ON lma.`userID`=lmu.`userID` WHERE lma.`userID`={$_SESSION['granted']};");
    if (count($keys)>0) $apikey='key='.$keys[0]['apiKey'].'&'; else $apikey='';
    ?>
    Also available in LMeve API <a href="api.php?<?=$apikey?>endpoint=ORECHART" target="_blank">api.php?<?=$apikey?>endpoint=ORECHART</a><br/>
    <br/>
    <?php displayOreChart($ores, $minerals); 
    /**********************************/ 
    $ret=ob_get_contents();
    ob_end_clean();
    return $ret;
}
