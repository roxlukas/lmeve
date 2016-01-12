<?php
include_once("inventory.php");

function cachedContent() {
    ob_start();
    /**********************************/
    ?>
    <a href="#down">Scroll down</a>
                        </div>

    <?php
    $corps=db_asocquery("SELECT * FROM apicorps;");
    foreach ($corps as $corp) { //begin corps loop
        echo("<h1><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");

        $pocos=getPocos("apo.`corporationID`=${corp['corporationID']}");
        $income=getPocoIncome($corp['corporationID']);

        //echo("DEBUG: <pre>"); print_r($pocos); echo('</pre>');
        ?>

        <?php

        showPocos($pocos,$income);
    }//end corps loop
    ?>

                    <div class="tekst">
                            <a href="#top">Scroll up</a>
                            <a name="down"></a>
                    </div><br>

    <?php
    /**********************************/ 
    $ret=ob_get_contents();
    ob_end_clean();
    return $ret;
}