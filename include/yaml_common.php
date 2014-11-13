<?php

function yaml_prepare($str,$default='NULL') {
    if (is_array($str)) $str=implode(' ', $str);
    if (!isset($str)) return $default; else return addslashes($str);
}

function yaml_activity2ID($input) {
    if (is_numeric($input)) return $input;
    switch($input) {
        case 'manufacturing':
            return 1;
            break;
        case 'research_time':
            return 3;
            break;
        case 'research_material':
            return 4;
            break;
        case 'copying':
            return 5;
            break;
        case 'invention':
            return 8;
            break;
        default:
            return false;
    }
}
?>
