<?php
date_default_timezone_set(@date_default_timezone_get());
set_include_path("../include");
include_once("log.php");
include_once("db.php");

$MYSQLDUMP='/usr/bin/mysqldump';
$BACKUP='../backup';
$BZIP2='/bin/bzip2';

echo("Running a full DB Backup...\r\n");

$date=date('Y-m-d');

echo(shell_exec("$MYSQLDUMP --user $LM_dbuser --password=$LM_dbpass $LM_dbname >$BACKUP/${LM_dbname}_$date.sql"));
echo("Compressing backup file...\r\n");
echo(shell_exec("$BZIP2 $BACKUP/${LM_dbname}_$date.sql"));
echo("Removing old backups...\r\n");
echo(shell_exec("find $BACKUP -mtime +5 | xargs rm"));
?>
