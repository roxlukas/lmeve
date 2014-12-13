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

function hrefedit_item($nr) {
		echo("<a href=\"index.php?id=10&id2=1&nr=$nr\">");
	}
	

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
                "20"  => array( "file" => "20-content.php", "rights" => "Administrator,ViewInventory", "validTime" => 7200 )
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
        case 'GET_PROFIT_CALC':
            if (!checkrights("Administrator,ViewProfitCalc")) {
                echo("<h2>${LANG['NORIGHTS']}</h2>");
                return;
            }
            $rowcount=10;
            if (isset($_GET['page'])) $page=secureGETnum('page'); else $page=0;
            if (isset($_GET['offset'])) $offset=secureGETnum('offset'); else $offset=$page*$rowcount;
            if (isset($_GET['getrowcount'])) {
                echo($rowcount);
                return;
            }
            if (isset($_GET['getlength'])) {
                $length=db_count("SELECT itp.`typeID`, itp.`typeName`, app.${EC_PRICE_TO_USE_FOR_SELL['price']}, app.`volume`
                FROM `$LM_EVEDB`.`invTypes` itp
                JOIN `$LM_EVEDB`.`yamlBlueprintProducts` ybp
                ON itp.`typeID`=ybp.`productTypeID`
                JOIN `cfgmarket` cfm
                ON itp.`typeID`=cfm.`typeID`
                JOIN `apiprices` app
                ON itp.`typeID`=app.`typeID`
                WHERE itp.`published` = 1
                AND ybp.`activityID` = 1
                AND app.`type` = '${EC_PRICE_TO_USE_FOR_SELL['type']}'
                AND app.${EC_PRICE_TO_USE_FOR_SELL['price']} > 0
                ORDER BY itp.`typeName`");
                echo($length);
                return;
            }
            
            global $LM_EVEDB, $EC_PRICE_TO_USE_FOR_SELL;

            $items=db_asocquery("SELECT itp.`typeID`, itp.`typeName`, app.${EC_PRICE_TO_USE_FOR_SELL['price']}, app.`volume`
                FROM `$LM_EVEDB`.`invTypes` itp
                JOIN `$LM_EVEDB`.`yamlBlueprintProducts` ybp
                ON itp.`typeID`=ybp.`productTypeID`
                JOIN `cfgmarket` cfm
                ON itp.`typeID`=cfm.`typeID`
                JOIN `apiprices` app
                ON itp.`typeID`=app.`typeID`
                WHERE itp.`published` = 1
                AND ybp.`activityID` = 1
                AND app.`type` = '${EC_PRICE_TO_USE_FOR_SELL['type']}'
                AND app.${EC_PRICE_TO_USE_FOR_SELL['price']} > 0
                ORDER BY itp.`typeName`
                LIMIT $offset, $rowcount
                ;");
            

            if (sizeof($items)>0) {
                foreach($items as $row) {
                        //$priceData=db_asocquery("SELECT * FROM `apiprices` WHERE `typeID`=${row['typeID']} AND `type`='sell';");
                        $cost=calcTotalCosts($row['typeID']);
                        $unitprofit=$row[$EC_PRICE_TO_USE_FOR_SELL['price']]-$cost;
                        $profit=100*($unitprofit)/$cost;

                        echo('<tr><td style="padding: 0px; width: 32px;">');
                                hrefedit_item($row['typeID']);
                                echo("<img src=\"ccp_img/${row['typeID']}_32.png\" title=\"${row['typeName']}\" />");
                                echo('</a>');
                        echo('</td><td>');
                                hrefedit_item($row['typeID']);
                                echo($row['typeName']);
                                echo('</a>');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($cost, 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($row[$EC_PRICE_TO_USE_FOR_SELL['price']], 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($row['volume'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($unitprofit, 2, $DECIMAL_SEP, $THOUSAND_SEP).' ISK');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($profit, 1, $DECIMAL_SEP, $THOUSAND_SEP).' %');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($unitprofit*$row['volume']/1000000000, 1, $DECIMAL_SEP, $THOUSAND_SEP));
                        echo('</td>');
                        echo('</tr>');
                }
            }
            break;
	default:
            echo('Error in AJAX call.');
    }
?>