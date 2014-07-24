<?php
	date_default_timezone_set('Europe/Warsaw');
	//set_include_path("../include");
	include_once("../include/log.php");
	include_once("../include/db.php");
	include_once('../include/yaml_graphics.php');
        include_once('../include/yaml_certificates.php');
        include_once('../include/yaml_blueprints.php');
        
    echo("Using $LM_EVEDB.\r\n");
/*
    echo('Calling updateYamlTypeIDs(), please wait... ');
	updateYamlTypeIDs();
    echo("done\r\n");
            
    echo('Calling updateYamlGraphicIDs(), please wait... ');
	updateYamlGraphicIDs();
    echo("done\r\n");
    
    echo('Calling updateYamlCertificates(), please wait... ');
        updateYamlCertificates();
    echo("done\r\n");
    
    echo('Calling updateYamlBlueprints(), please wait... ');   
        updateYamlBlueprints();
    echo("done\r\n");
*/ 
    echo('Calling recreateLegacyTables(), please wait... '); 
        recreateLegacyTables();
    echo("done\r\n");
?>