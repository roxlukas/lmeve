<?php
set_time_limit(880); //poller can work for up to 15 minutes 
//(minus 20 seconds so the next cron cycle can work correctly), afterwards it should die
$mypath=str_replace('\\','/',dirname(__FILE__));

$mycache=$mypath."/../var";
$mytmp=$mypath."/../tmp";

date_default_timezone_set(@date_default_timezone_get());
set_include_path("$mypath/../include");
include_once("log.php");
include_once("db.php");

db_uquery('TRUNCATE TABLE `lmeve-public`.`apiprices`;');
db_uquery('INSERT INTO `lmeve-public`.`apiprices` SELECT * FROM `lmeve`.`apiprices`;');
db_uquery('TRUNCATE TABLE `lmeve-public`.`apistatus`;');
db_uquery('INSERT INTO `lmeve-public`.`apistatus` SELECT * FROM `lmeve`.`apistatus` WHERE fileName=\'eve-central.com/marketstat.xml\';');

?>
