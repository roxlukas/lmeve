<?php
set_include_path("../include");
include_once("log.php");
include_once("db.php");
include_once("auth.php");

echo(hashpass("admin"));

?>