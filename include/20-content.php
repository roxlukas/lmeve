<?php
include_once("inventory.php");

function cachedContent() {
    ob_start();
    /**********************************/
    ?>
    <div>
        <a href="#down">Scroll down</a>
    </div>
    <?php
    $corps=db_asocquery("SELECT * FROM apicorps;");
    foreach ($corps as $corp) { //begin corps loop
        echo("<h1><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");

        $stock=getStock("apa.`corporationID`=${corp['corporationID']}");

        //echo("DEBUG: <pre>"); print_r($inventory); echo('</pre>');
        ?>
        <h3>Current Stock</h3>
        <em>Due to EVE API limitations, Assets can only be updated every 6 hours.</em>
        <?php

        showStock($stock,$corp['corporationID']);
    }//end corps loop
    ?>
    <div class="tekst">
        <a href="#top">Scroll up</a>
        <a name="down"></a>
    </div>
    <?php
    /**********************************/ 
    $ret=ob_get_contents();
    ob_end_clean();
    return $ret;
}
