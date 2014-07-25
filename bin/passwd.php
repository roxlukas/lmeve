<?php
//set_include_path("../include");
include_once("../include/log.php");
include_once("../include/db.php");
include_once("../include/auth.php");

global $USERSTABLE,$LM_DEFAULT_CSS;

$pwd=hashpass("admin");

$users=db_count("SELECT * FROM `$USERSTABLE` WHERE `login`='admin';");
if ($users==0) {
    echo("User 'admin' does not exist. Adding user 'admin' and setting password 'admin'".PHP_EOL);
			$userid=db_query("SELECT MAX(`userID`) FROM `$USERSTABLE`;");
			$userid=$userid[0][0]; $userid++;
    db_uquery("INSERT INTO `$USERSTABLE` VALUES ($userid, 'admin', '$pwd', '127.0.0.1', '01.01.2007 12:00',0,'$LM_DEFAULT_CSS',1);");
    echo("Adding 'Administrator' role to user 'admin'".PHP_EOL);
                        $roleid=db_query("SELECT `roleID` FROM `lmroles` WHERE `roleName`='Administrator';");
			$roleid=$roleid[0][0];
    db_uquery("INSERT IGNORE INTO `lmuserroles` VALUES ($userid,$roleid);");
} else {
    echo("User 'admin' already exists. New password is 'admin'".PHP_EOL);
    db_uquery("UPDATE `$USERSTABLE` SET `pass`='$pwd',`act`=1 WHERE login='admin';");
}
echo("Login to LMeve, go to Settings and change admin password immediately".PHP_EOL);
?>