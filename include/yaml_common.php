<?php

function yaml_prepare($str,$default='NULL') {
    if (is_array($str)) $str=implode(' ', $str);
    if (!isset($str)) return $default; else return addslashes($str);
}
?>
