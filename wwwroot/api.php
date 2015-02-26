<?php
//LMeve Northbound API
//currently working calls:
//api.php?key=<apikey>&endpoint=MATERIALS&typeID=<itemID>&meLevel=<ME level 0-10>
//api.php?key=<apikey>&endpoint=TASKS
//api.php?key=<apikey>&endpoint=TASKS&characterID=<charID>

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

if($LM_FORCE_SSL && $_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

$endpoint=secureGETstr('endpoint');
$key=secureGETstr('key');

function formatMaterials($materials) {
    $ret=array();
    if (count($materials>0)) {
        foreach ($materials as $row) {
            array_push($ret,array('typeID'=>$row['typeID'], 'typeName'=>$row['typeName'], 'quantity'=>$row['notperfect'], 'melevel'=>$row['waste'], 'consumed'=>$row['damagePerJob'] ));
        }
    }
    return $ret;
}

function checkApiKey($key) {
    $ret=db_asocquery("SELECT * FROM `lmnbapi` WHERE `apiKey`='$key';");
    if (count($ret)==1) {
        db_uquery("UPDATE `lmnbapi` SET `lastAccess`='".date('y-m-d H:i:s')."', `lastIP`='".$_SERVER['REMOTE_ADDR']."' WHERE `apiKey`='$key';");
        return TRUE;
    } else return FALSE;
}

function RESTfulError($msg,$http_error_code=400) {
    header("HTTP/1.0 $http_error_code");
    echo(json_encode(array('errorMsg' => $msg, 'errorCode' => $http_error_code)));
    die();
}

if (getConfigItem('northboundApi')!='enabled') RESTfulError("API is disabled.",400);

if (!checkApiKey($key)) RESTfulError("Invalid LMeve Northbound API KEY.",401);

//Add proper JSON MIME type in header
header("Content-type: application/json");
//Add CORS header in header so API can be used with web apps on other servers
header("Access-Control-Allow-Origin: *");

    switch ($endpoint) {
        case 'MATERIALS':
            $typeID=secureGETnum('typeID');
            $meLevel=secureGETnum('meLevel');
            if (empty($meLevel)) $meLevel=0;
            if ($melevel>10) $melevel=10;
            if (empty($typeID)) RESTfulError('Missing typeID parameter.',400);
            echo(json_encode(formatMaterials(getBaseMaterials($typeID, 1, $meLevel))));
            break;
        case 'TASKS':
            $characterID=secureGETnum('characterID');
            $sql="lmt.`characterID`=".$characterID;
            if (empty($characterID)) $sql="TRUE";
            echo(json_encode(getTasks($sql, "TRUE", "",date("Y"),date("m"))));
            break;
        case 'INVTYPES':
            $typeID=secureGETnum('typeID');
            if (empty($typeID)) RESTfulError('Missing typeID parameter.',400);
            $items=db_asocquery("SELECT itp.*,cre.`averagePrice` FROM $LM_EVEDB.`invTypes` itp LEFT JOIN `crestmarketprices` cre ON itp.`typeID`=cre.`typeID` WHERE itp.`typeID`=$typeID;");
            if (count($items)==0) RESTfulError('typeID not found.',404);
            $item=$items[0];
            $traitData=db_asocquery("SELECT yit.*, eun.displayName
                FROM `$LM_EVEDB`.`yamlInvTraits` yit
                LEFT JOIN `$LM_EVEDB`.`eveUnits` eun
                ON yit.`unitID`=eun.`unitID`
                WHERE `typeID`=$typeID AND `skillID`=-1;");
            $bonusData=db_asocquery("SELECT yit.*, eun.displayName
                FROM `$LM_EVEDB`.`yamlInvTraits` yit
                LEFT JOIN `$LM_EVEDB`.`eveUnits` eun
                ON yit.`unitID`=eun.`unitID`
                WHERE `typeID`=$typeID AND `skillID`!=-1;");
            $dogmaData=db_asocquery("SELECT valueFloat,valueInt,displayName,description
                FROM $LM_EVEDB.`dgmTypeAttributes` AS dta
                JOIN $LM_EVEDB.`dgmAttributeTypes` AS da
                ON dta.attributeID=da.attributeID
                WHERE dta.typeID=$typeID
                AND displayName != '';");
            if (count($traitData) > 0) $item['traits']=$traitData;
            if (count($bonusData) > 0) $item['bonuses']=$bonusData;
            if (count($dogmaData) > 0) $item['attributes']=$dogmaData;
            echo(json_encode($item));
            break;
        case 'INVGROUPS':
            $groupID=secureGETnum('groupID');
            if (empty($groupID)) $where_gid="TRUE"; else $where_gid="`groupID`=$groupID";
            $categoryID=secureGETnum('categoryID');
            if (empty($categoryID)) $where_cid="TRUE"; else $where_cid="`categoryID`=$categoryID";
            $groups=db_asocquery("SELECT * FROM `$LM_EVEDB`.`invGroups`
                WHERE $where_gid AND $where_cid;");
            if (count($groups)==0) RESTfulError('No data found.',404);
            echo(json_encode($groups));
            break;  
        case 'INVCATEGORIES':
            $categoryID=secureGETnum('categoryID');
            if (empty($categoryID)) $where_cid="TRUE"; else $where_cid="`categoryID`=$categoryID";
            $categories=db_asocquery("SELECT * FROM `$LM_EVEDB`.`invCategories`
                WHERE $where_cid;");
            if (count($categories)==0) RESTfulError('No data found.',404);
            echo(json_encode($categories));
            break; 
        case 'GRAPHICID':
            $typeID=secureGETnum('typeID');
            if (empty($typeID)) RESTfulError('Missing typeID parameter.',400);
            $items=db_asocquery("SELECT yti.`typeID`,itp.`groupID`,itp.`typeName`,ygi.* FROM `$LM_EVEDB`.`yamlTypeIDs` yti
                JOIN `$LM_EVEDB`.`yamlGraphicIDs` ygi
                ON yti.`graphicID`=ygi.`graphicID`
                JOIN `$LM_EVEDB`.`invTypes` itp
                ON yti.`typeID`=itp.`typeID`
                WHERE yti.`typeID`=$typeID;");
            if (count($items)==0) RESTfulError('typeID not found.',404);
            $item=$items[0];
            echo(json_encode($item));
            break;
        case 'ALLGRAPHICIDS':
            RESTfulError('ALLGRAPHICIDS endpoint is now obsolete. Use GRAPHICIDS instead.',404);
            break;
        case 'GRAPHICIDS':
            $groupID=secureGETnum('groupID');
            if (empty($groupID)) $where_gid="TRUE"; else $where_gid="itp.`groupID`=$groupID";
            $items=db_asocquery("SELECT yti.`typeID`,itp.`groupID`,itp.`typeName`,ygi.* FROM `$LM_EVEDB`.`yamlTypeIDs` yti
                JOIN `$LM_EVEDB`.`yamlGraphicIDs` ygi
                ON yti.`graphicID`=ygi.`graphicID`
                JOIN `$LM_EVEDB`.`invTypes` itp
                ON yti.`typeID`=itp.`typeID`
                WHERE $where_gid;");
            if (count($items)==0) RESTfulError('No data found.',404);
            echo(json_encode($items));
            break;
	default:	
            RESTfulError('Invalid endpoint.',404);
    }
?>