<?php

include_once('dbcatalog.php');

function login_hook() {
    updateUserstable();
    recreateSdeCompatViews();
    updateCrestIndustrySystems();
    createCitadelsView();
    esiUpdateAll();
    updateApiAssets();
    decryptorTables();
}

?>