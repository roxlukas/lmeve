<?php
include_once("db.php");

function setConfigItem($itemLabel,$itemValue) {
    return db_uquery("INSERT INTO `lmconfig` VALUES ('$itemLabel','$itemValue') ON DUPLICATE KEY UPDATE `itemValue`='$itemValue';");
}

function getConfigItem($itemLabel,$default=FALSE) {
    $ret=db_asocquery("SELECT `itemValue` FROM `lmconfig` WHERE `itemLabel`='$itemLabel';");
    if (count($ret)==1 && !empty($ret[0]['itemValue'])) return $ret[0]['itemValue']; else return $default;
}

?>
