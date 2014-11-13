<?php
set_include_path("../include");
date_default_timezone_set("Europe/Paris");
include_once('../config/config.php'); //load config file
include_once("db.php");  //db access functions
include_once("log.php");  //logging facility
include_once('auth.php'); //authentication and authorization

include_once('materials.php'); //material related subroutines
include_once('tasks.php'); //task related subroutines
include_once('inventory.php'); //inventory and pos related subroutines

include_once("csrf.php");  //anti-csrf token implementation (secure forms)

include_once('configuration.php'); //configuration settings in db

session_start();
checksession(); //check if we are called by a valid session
$act=secureGETstr('act');
if ($act=='') $act=0;
    switch ($act) {
        case 'GET_MATERIALS':
            if (!checkrights("Administrator,ViewDatabase")) {
                echo("<h2>${LANG['NORIGHTS']}</h2>");
                return;
            }
            $typeID=secureGETnum('typeID');
            $melevel=secureGETnum('melevel');
            //displayExtraMats(getExtraMats($typeID,1));
            displayBaseMaterials(getBaseMaterials($typeID, 1, $melevel));
            break;
        case 'GET_QUOTE':
            if (!checkrights("Administrator,ViewDatabase")) {
                echo("<h2>${LANG['NORIGHTS']}</h2>");
                return;
            }
            $typeID=secureGETnum('typeID');
            displayCosts($typeID);
            break;
        case 'GET_KIT2': //NEW! THIS IS THE CURRENT ONE
            if (!checkrights("Administrator,ViewOwnTasks")) {
                echo("<h2>${LANG['NORIGHTS']}</h2>");
                return;
            }
            $taskID=secureGETnum('taskID');
            $data=getTask($taskID);
            $typeID=$data['typeID'];
            $activityID=$data['activityID'];
            $structureID=$data['structureID'];
            if (!is_null($structureID)) {
                $location=getLabDetails($structureID);
            } else {
                $location=false;
            }
            $runs=secureGETnum('runs');
            if ($portionSize=getPortionSize($typeID)) {
                $runs=$runs/$portionSize;
            }
            if (!isset($activityID)) $activityID=1;
            echo('<div style="width: 400px;">');
            displayKit2(getBaseMaterials($typeID,$runs,null,$activityID),array(),$melevel,null,$location);
            echo('</div>');
            break;
        case 'CACHE':
            $pages = array (
                "20"  => array( "file" => "20-content.php", "rights" => "Administrator,ViewInventory", "validTime" => 7200 ),
                "a9"  => array( "file" => "a9-content.php", "rights" => "Administrator,ViewProfitCalc", "validTime" => 86400 )
            );
            $page=secureGETstr('page');
            if (array_key_exists($page, $pages)) {
                if (!checkrights($pages[$page]['rights'])) {
                    echo("<h2>${LANG['NORIGHTS']}</h2>");
                    return;
                } else {
                    //everything ok! include the content function and check caches
                    include($pages[$page]['file']);
                    if (function_exists('cachedContent')) {
                        //function exists. check if a cached version exists
                        $validTime=$pages[$page]['validTime'];
                        $sql="SELECT * FROM `lmpagecache` WHERE `pageLabel`='$page' AND `timestamp` >= (NOW() - INTERVAL $validTime SECOND);";
                        $data=db_asocquery($sql);            
                        if (count($data)>0) {
                            //if there is a cached version, show it
                            //echo("DEBUG: found cached version of the page.<br/>");
                            echo(stripslashes($data[0]['pageContents']));
                        } else {
                            //if there is no cached version, refresh cache
                            //echo("DEBUG: no cached version, refreshing page.<br/>");
                            $tmpContent=cachedContent();
                            db_uquery("INSERT INTO `lmpagecache` VALUES ('$page','".addslashes($tmpContent)."', NOW()) ON DUPLICATE KEY UPDATE `pageContents`='".addslashes($tmpContent)."', timestamp=NOW();");
                            echo($tmpContent);
                        }
                    } else {
                        echo("Cannot load cachedContent() from ".$pages[$page]['file']);
                    }
                }
            } else {
                echo("Invalid 'page' value.");
            }
            break;
	default:
            echo('Error in AJAX call.');
    }
?>