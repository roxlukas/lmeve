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
        case 'GET_KIT': //OLD!! DEPRECATED, DO NOT USE
            if (!checkrights("Administrator,ViewOwnTasks")) {
                echo("<h2>${LANG['NORIGHTS']}</h2>");
                return;
            }
            $typeID=secureGETnum('typeID');
            if ($mepe=getMEPE($typeID)) {
                $melevel=$mepe['me'];
            } else {
                $techlevel=getTechLevel($typeID);
                switch ($techlevel) {
                    case 1:
                        $melevel=0;
                        break;
                    case 2:
                        $melevel=2;
                        break;
                    default:
                        $melevel=0;
                }
            }
            $runs=secureGETnum('runs');
            if ($portionSize=getPortionSize($typeID)) {
                $runs=$runs/$portionSize;
            }
            $activityID=secureGETnum('activityID');
            //echo("DEBUG: typeID=$typeID, activityID=$activityID, melevel=$melevel, runs=$runs<br/>");
            //walidacja
            if (!isset($activityID)) $activityID=1;
            //$melevel=0;
            echo('<div style="width: 400px;">');
//            displayExtraMats(getExtraMats($typeID,$activityID,$runs));
            displayKit(getBaseMaterials($typeID,$runs),getExtraMats($typeID,$activityID,$runs),$melevel,getWasteFactor($typeID));
            echo('</div>');
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
            if ($mepe=getMEPE($typeID)) {
                $melevel=$mepe['me'];
            } else {
                $techlevel=getTechLevel($typeID);
                switch ($techlevel) {
                    case 1:
                        $melevel=0;
                        break;
                    case 2:
                        $melevel=2;
                        break;
                    default:
                        $melevel=0;
                }
            }
            $runs=secureGETnum('runs');
            if ($portionSize=getPortionSize($typeID)) {
                $runs=$runs/$portionSize;
            }
            //$activityID=secureGETnum('activityID');
            //echo("DEBUG: typeID=$typeID, activityID=$activityID, melevel=$melevel, runs=$runs<br/>");
            //walidacja
            if (!isset($activityID)) $activityID=1;
            //$melevel=0;
            echo('<div style="width: 400px;">');
//            displayExtraMats(getExtraMats($typeID,$activityID,$runs));
            displayKit2(getBaseMaterials($typeID,$runs,null,$activityID),array(),$melevel,getWasteFactor($typeID),$location);
            echo('</div>');
            break;
	default:
            echo('Error in AJAX call.');
    }
?>