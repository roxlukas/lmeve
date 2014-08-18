<?php
	date_default_timezone_set('Europe/Warsaw');
	//set_include_path("../include");
	include_once("../include/log.php");
	include_once("../include/db.php");
	include_once('../include/yaml_graphics.php');
        include_once('../include/yaml_certificates.php');
        include_once('../include/yaml_blueprints.php');
        
        $updateTypeIDs=FALSE;
        $updateGraphicIDs=FALSE;
        $updateCertificates=FALSE;
        $updateBlueprints=FALSE;
        $updateLegacy=FALSE;
        
        //--usage, --help, /? - display usage (argc==1 || argc==2)
        //--all - update all
        //--typeIDs - update typeIDs
        //--graphicIDs - update graphicIDs
        //--certificates - update certificates
        //--blueprints - update blueprints
        //--legacy - update legacy industry tables
        
        echo('LMeve YAML static data updater'.PHP_EOL.'(c) 2014 by Lukasz "Lukas Rox" Pozniak'.PHP_EOL.PHP_EOL);
        
        if ($argc==2 && $argv[1]=='--all') {
            $updateTypeIDs=TRUE;
            $updateGraphicIDs=TRUE;
            $updateCertificates=TRUE;
            $updateBlueprints=TRUE;
            $updateLegacy=TRUE;
        } else if ($argc>=2) {
            foreach($argv as $arg) {
                if ($arg=='--typeIDs') $updateTypeIDs=TRUE;
                if ($arg=='--graphicIDs') $updateGraphicIDs=TRUE;
                if ($arg=='--certificates') $updateCertificates=TRUE;
                if ($arg=='--blueprints') $updateBlueprints=TRUE;
                if ($arg=='--legacy') $updateLegacy=TRUE;
            }
        } else if ($argc==1 || ($argc==2 && ($argv[1]=='--usage' || $argv[1]='--help' || $argv[1]='/?')) ) {
            echo('--usage, --help, /? - display this help'.PHP_EOL);
            echo('--all - update all tables'.PHP_EOL);
            echo('--typeIDs - update all tables'.PHP_EOL);
            echo('--graphicIDs - update all tables'.PHP_EOL);
            echo('--certificates - update all tables'.PHP_EOL);
            echo('--blueprints - update all tables'.PHP_EOL);
            echo('--legacy - update all tables'.PHP_EOL);
            return;
        } 
        
    echo("Using static data from ./data/$LM_EVEDB/".PHP_EOL.PHP_EOL);

    if ($updateTypeIDs) {
        echo('Calling updateYamlTypeIDs(), please wait... ');
            updateYamlTypeIDs(FALSE);
        echo("done\r\n");
    }
    
    if ($updateGraphicIDs) {
        echo('Calling updateYamlGraphicIDs(), please wait... ');
            updateYamlGraphicIDs(FALSE);
        echo("done\r\n");
    }
    
    if ($updateCertificates) {
        echo('Calling updateYamlCertificates(), please wait... ');
            updateYamlCertificates(FALSE);
        echo("done\r\n");
    }
    
    if ($updateBlueprints) {    
        echo('Calling updateYamlBlueprints(), please wait... ');   
            updateYamlBlueprints(FALSE);
        echo("done\r\n");
    }
    
    if ($updateLegacy) {
        echo('Calling recreateLegacyTables(), please wait... '); 
            recreateLegacyTables();
        echo("done\r\n");
    }
?>