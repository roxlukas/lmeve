<?php
	date_default_timezone_set(@date_default_timezone_get());
	//set_include_path("../include");
        error_reporting(E_ALL ^ E_NOTICE);
        
        $mypath=str_replace('\\','/',dirname(__FILE__));
	include_once("$mypath/../include/log.php");
	include_once("$mypath/../include/db.php");
	include_once("$mypath/../include/yaml_graphics.php");
        include_once("$mypath/../include/yaml_skins.php");
        include_once("$mypath/../include/yaml_certificates.php");
        include_once("$mypath/../include/yaml_blueprints.php");
        
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
        
        echo('LMeve YAML static data updater'.PHP_EOL.'(c) 2014-2016 by Lukasz "Lukas Rox" Pozniak'.PHP_EOL.PHP_EOL);
        echo('This tool is deprecated as of Citadel release.'.PHP_EOL);
        
        if ($argc==2 && $argv[1]=='--all') {
            //$updateTypeIDs=TRUE;
            //$updateGraphicIDs=TRUE;
            //$updateCertificates=TRUE;
            //$updateBlueprints=TRUE;
            //$updateLegacy=TRUE;
            //$updateSkins=TRUE;
            echo('This tool is deprecated as of Citadel release. LMeve recreates required tables on first login.'.PHP_EOL);
            die();
        } else if ($argc>=2) {
            foreach($argv as $arg) {
                if ($arg=='--typeIDs') $updateTypeIDs=TRUE;
                if ($arg=='--graphicIDs') $updateGraphicIDs=TRUE;
                if ($arg=='--certificates') $updateCertificates=TRUE;
                if ($arg=='--blueprints') $updateBlueprints=TRUE;
                if ($arg=='--legacy') $updateLegacy=TRUE;
                if ($arg=='--skins') $updateSkins=TRUE;
            }
        } else if ($argc==1 || ($argc==2 && ($argv[1]=='--usage' || $argv[1]='--help' || $argv[1]='/?')) ) {
            echo('--usage, --help, /? - display this help'.PHP_EOL);
            echo('--all - update all tables'.PHP_EOL);
            echo('--typeIDs - update yamltypeids'.PHP_EOL);
            echo('--graphicIDs - update yamlgraphicids'.PHP_EOL);
            echo('--certificates - update certificates'.PHP_EOL);
            echo('--blueprints - update yasmlblueprints'.PHP_EOL);
            echo('--legacy - convert yamlblueprints into invBlueprintTypes and ramTypeRequirements'.PHP_EOL);
            echo('--skins - update new skin tables (Galatea and later)'.PHP_EOL);
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
    
    if ($updateSkins) {
        echo('Calling updateYamlSkins(), please wait... '); 
            updateYamlSkins(FALSE);
        echo("done\r\n");
        echo('Calling updateYamlSkinLicenses(), please wait... '); 
            updateYamlSkinLicenses(FALSE);
        echo("done\r\n");
        echo('Calling updateYamlSkinMaterials(), please wait... '); 
            updateYamlSkinMaterials(FALSE);
        echo("done\r\n");
        echo('Calling updateYamlSkinMaterialSets(), please wait... '); 
            updateYamlSkinMaterialSets(FALSE);
        echo("done\r\n");
    }
?>