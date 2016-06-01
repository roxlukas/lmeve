<?php

include_once('dbcatalog.php');

function login_hook() {
    updateUserstable();
    recreateSdeCompatViews();
    updateCrestIndustrySystems();
}

?>