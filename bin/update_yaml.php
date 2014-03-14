<?php
	date_default_timezone_set('Europe/Warsaw');
	set_include_path("../include");
	include_once("log.php");
	include_once("db.php");
	include_once('graphics.php');
        include_once('certificates.php');

	echo("Using $LM_EVEDB.\r\n");

    echo('Calling updateYamlTypeIDs(), please wait... ');
	updateYamlTypeIDs();
    echo("done\r\n");
    echo('Calling updateYamlGraphicIDs(), please wait... ');
	updateYamlGraphicIDs();
    echo("done\r\n");
    echo('Calling updateYamlCertificates(), please wait... ');
        updateYamlCertificates();
    echo("done\r\n");
?>