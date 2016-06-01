<?php
set_include_path("../include");
date_default_timezone_set(@date_default_timezone_get());
if (!is_file('../config/config.php')) die('Config file not found.');
include_once('../config/config.php'); //load config file
if ($LM_DEBUG==TRUE) error_reporting(E_ALL ^ E_NOTICE); else error_reporting(0);
include_once("db.php");  //db access functions
include_once("log.php");  //logging facility
include_once('auth.php'); //authentication and authorization
include_once('materials.php'); //material related subroutines
include_once('tasks.php'); //task related subroutines
include_once('inventory.php'); //inventory and pos related subroutines
include_once('stats.php'); //real time stats
include_once("csrf.php");  //anti-csrf token implementation (secure forms)
include_once('configuration.php'); //configuration settings in db

function hrefedit_item($nr) {
		echo("<a href=\"index.php?id=10&id2=1&nr=$nr\">");
	}
	
global $THOUSAND_SEP,$DECIMAL_SEP,$LM_CCPWGL_CACHESCHEMA;
        
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
        case 'GET_PROXY_STATS':
            if (!checkrights("Administrator,ViewCDNStats")) {
                echo("<h2>${LANG['NORIGHTS']}</h2>");
                return;
            }
            ?>
            <div id="top" style="width: 100%;">
                <h3>Top clients and files</h3>
                <table style="width: 100%; padding: 0px;">
                    <tr>
                        <td>
                            <?php showTopByRequests(getTopClientsByRequests()); ?>
                        </td>
                        <td>
                            <?php showTopByBytes(getTopClientsByBytes()); ?>
                        </td>
                        <td>
                            <?php showTopByRequests(getTopFilesByRequests()); ?>
                        </td>
                        <td>
                            <?php showTopByBytes(getTopFilesByBytes()); ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div id="latest">
                <h3>Latest requests</h3>
                <?php showLastProxyRequests(getLastProxyRequests(10)); ?>
            </div>
            <div id="errors">
                <h3>Errors</h3>
                <?php showLastProxyRequests(getLastProxyErrors(5)); ?>
            </div>
            <?php
            break;
        case 'GET_CACHE_SIZE':
            if (!checkrights("Administrator,ViewCDNStats")) {
                echo("<h2>${LANG['NORIGHTS']}</h2>");
                return;
            }
            ?>
            <h3>CDN Cache schema: <a href="#"><?=$LM_CCPWGL_CACHESCHEMA?></a><br/>CDN Cache size: <a href="#"><?php echo(getCdnCacheDbSize()); ?> MB</a></h3>
            <?php
            break;
        case 'GET_CACHE_STATS':
            if (!checkrights("Administrator,ViewCDNStats")) {
                echo("<h2>${LANG['NORIGHTS']}</h2>");
                return;
            }
            $cache_total=getRequestsInLast24h(); $cache_total=$cache_total['count'];
            $cache_hits=getRequestsInLast24h("`cacheUsed`=1"); $cache_hits=$cache_hits['count'];
            $bytes=getBytesInLast24h(); $bytes=number_format($bytes['bytes'],0, $DECIMAL_SEP, ' ');
            $cdnbytes=getBytesInLast24h("`cacheUsed`=0"); $cdnbytes=number_format($cdnbytes['bytes'],0, $DECIMAL_SEP, ' ');
            $ratio=number_format(100 * $cache_hits / $cache_total, 1, $DECIMAL_SEP, $THOUSAND_SEP);
            
            ?> 
            <div id="visits" style="overflow: hidden; margin: 0px auto 0px auto; width: 200px; height: 80px;">
                <center>
                    <div id="users" style="text-align: center; float: left;">
                        <h1><?php echo(getVisitors()); ?></h1>
                        unique visitors
                    </div>
                    <div id="hits" style="margin-left: 20px; margin-top: 8px; text-align: center; float: left;">
                        <h2><?php echo(getRequests()); ?></h2>
                        requests
                    </div>
                </center>
            </div>
            <table class="lmframework" style="width: 100%;"><tr>
                <th>Cache hit ratio:</th><td><?=$ratio?>% (<?=$cache_hits?>/<?=$cache_total?>)</td>
                <th>Bytes received from origin:</th><td><?=$cdnbytes?></td>
                <th>Bytes sent to clients:</th><td><?=$bytes?></td>
            </tr></table> <?php
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
            $techLevel=getTechLevel($typeID);
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
            
            if ($activityID==8) { //invention materials are now bound to T1 BP, not T2 BP
                if ($techLevel==2) {
                    $tmpBPO=getT1BPOforT2BPO($typeID);
                } else if ($techLevel==3) {
                    $tmpBPO=getRelicForT3BPC($typeID);
                }
                //echo("<h2>Invention DEBUG</h2><pre>".print_r($tmpBPO,TRUE)."</pre>");
                $typeID=$tmpBPO['blueprintTypeID'];
            }
            
            echo('<div style="width: 400px;">');
            displayKit2(getBaseMaterials($typeID,$runs,null,$activityID),array(),$melevel,null,$location);
            echo('</div>');
            break;
        case 'CACHE':
            $pages = array (
                "20"  => array( "file" => "20-content.php", "rights" => "Administrator,ViewInventory", "validTime" => 21600 ), // Inventory 6h cache
                "26"  => array( "file" => "26-content.php", "rights" => "Administrator,ViewPOS", "validTime" => 43200 ) // PoCo 12h cache
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
                                echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
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
                                //echo(number_format($profit, 1, $DECIMAL_SEP, $THOUSAND_SEP).' %');
                                echo(number_format($profit, 1, $DECIMAL_SEP, '').' %');
                        echo('</td><td style="text-align:right;">');
                                echo(number_format($unitprofit*$row['volume']/1000000000, 1, $DECIMAL_SEP, $THOUSAND_SEP));
                        echo('</td>');
                        echo('</tr>');
                }
            }
            break;
        case 'GET_REGIONS':
            $wspace=secureGETstr('wspace',5);
            if (strtolower($wspace)=='true') $whereWSpace='TRUE'; else $whereWSpace='`regionID` < 11000000';
            $regions=db_asocquery("SELECT `regionID`,`regionName` FROM `$LM_EVEDB`.`mapRegions` WHERE $whereWSpace ORDER BY `regionName`;");
            //Add proper JSON MIME type in header
            header("Content-type: application/json");
            echo(json_encode($regions));
            break;
        case 'GET_SOLARSYSTEMS':
            $regionID=secureGETnum('regionID');
            if (!isset($regionID)) {
                echo("Error: regionID required");
                break;
            }
            $systems=db_asocquery("SELECT `solarSystemID`,`solarSystemName` FROM `$LM_EVEDB`.`mapSolarSystems` WHERE `regionID`=$regionID ORDER BY `solarSystemName`;");
            //Add proper JSON MIME type in header
            header("Content-type: application/json");
            echo(json_encode($systems));
            break;
        case 'GET_POLLERMESSAGE':
            if (!checkrights("Administrator,ViewAPIStats")) {
                echo("<h2>${LANG['NORIGHTS']}</h2>");
                return;
            }
            $sql="SELECT *
            FROM `apistatus`
            ORDER BY date DESC
            LIMIT 0,1;";
            $errors = db_asocquery($sql);
            $LOCKFILE="../var/poller.lock";
            $message=$errors[0];
            if (file_exists($LOCKFILE)) $message['pollerActive']=TRUE; else $message['pollerActive']=FALSE;
            //Add proper JSON MIME type in header
            header("Content-type: application/json");
            echo(json_encode($message));
            break;    
	default:
            echo('Error in AJAX call.');
    }
?>