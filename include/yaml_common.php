<?php

function yaml_prepare($str,$default='NULL') {
    if (!isset($str)) return $default; else return addslashes($str);
}
?>
