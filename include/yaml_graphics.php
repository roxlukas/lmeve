<?php
//YAML - graphics related functions
//include_once('spyc/Spyc.php');
include_once('yaml_common.php');

/**
 * Updates typeID information from typeIDs.yaml file
 * 
 * @global $LM_EVEDB - EVE Static Data db name
 */
function updateYamlTypeIDs($silent=true) {
    global $LM_EVEDB;
    
    $file="../data/$LM_EVEDB/typeIDs.yaml";
    
    if (!file_exists($file)) {
        echo("File $file does not exist. Make sure YAML files from EVE SDE are in appropriate directories.");
        return FALSE;
    }
    
    $createyamlTypeIDs="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlTypeIDs` (
      `typeID` int(11) NOT NULL,
      `graphicID` int(11) NULL,
      `iconID` int(11) NULL,
      `radius` decimal(30,2) NULL,
      `soundID` int(11) NULL,
      PRIMARY KEY (`typeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlTypeIDs);
    
    $createyamlInvTraits="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlInvTraits` (
      `typeID` int(11) DEFAULT NULL,
      `skillID` int(11) DEFAULT NULL,
      `bonus` double DEFAULT NULL,
      `bonusText` text,
      `unitID` int(11) DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlInvTraits);
    
    if (!$silent) echo('loading YAML...');
    
    //switching from Spyc to YAML PECL module
    $typeIDs = yaml_parse_wrapper($file);
    
    if (!empty($typeIDs)) {
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlTypeIDs`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlInvTraits`;");
    } else return false;
    
    $biginsertTypeIDs="INSERT INTO `$LM_EVEDB`.`yamlTypeIDs` VALUES ";
    $biginsertTraits="INSERT INTO `$LM_EVEDB`.`yamlInvTraits` VALUES ";
    foreach($typeIDs as $typeID => $row) {
        $graphicID=yaml_prepare($row['graphicID']);
        $iconID=yaml_prepare($row['iconID']);
        $radius=yaml_prepare($row['radius']);
        $soundID=yaml_prepare($row['soundID']);
        $biginsertTypeIDs.="($typeID, $graphicID, $iconID, $radius, $soundID),";
        //var_dump($row['traits']);
        if (is_array($row['traits'])) { //if there are traits
            foreach ($row['traits'] as $skillID => $traits) {
                foreach ($traits as $trait) {
                    $bonus=yaml_prepare($trait['bonus']);
                    $bonusText=yaml_prepare($trait['bonusText']['en']);
                    $unitID=yaml_prepare($trait['unitID']);
                    $biginsertTraits.="($typeID, $skillID, $bonus, '$bonusText', $unitID),";
                }
            }
        }
    }
    
    $biginsertTypeIDs=rtrim($biginsertTypeIDs,',').";";
    $biginsertTraits=rtrim($biginsertTraits,',').";";
    
    if (!$silent) echo('insert to DB...');
    
    db_uquery($biginsertTypeIDs);
    db_uquery($biginsertTraits);
    
    return true;
}

/**
 * Updates typeID information from typeIDs.yaml file
 * 
 * @global $LM_EVEDB - EVE Static Data db name
 */
function updateYamlGraphicIDs($silent=true) {
    global $LM_EVEDB;
    
    $file="../data/$LM_EVEDB/graphicIDs.yaml";
    
    if (!file_exists($file)) {
        echo("File $file does not exist. Make sure YAML files from EVE SDE are in appropriate directories.");
        return FALSE;
    }
    
    $drop="DROP TABLE IF EXISTS `$LM_EVEDB`.`yamlGraphicIDs`;";
    
    $create="CREATE TABLE `$LM_EVEDB`.`yamlGraphicIDs` (
      `graphicID` int(11) NULL,
      `description` varchar(256) NULL,
      `graphicFile` varchar(512) NULL,
      `sofFactionName` varchar(128) NULL,
      `sofHullName` varchar(128) NULL,
      `sofRaceName` varchar(128) NULL,
      PRIMARY KEY (`graphicID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    
    if (!$silent) echo('loading YAML...');
    
    //switching from Spyc to YAML PECL module
    $graphicIDs = yaml_parse_wrapper($file);
    
    if (!empty($graphicIDs)) {
        //db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlGraphicIDs`;");
        db_uquery($drop);
        db_uquery($create);
    } else return false;
    $biginsert="INSERT INTO `$LM_EVEDB`.`yamlGraphicIDs` VALUES ";
    foreach($graphicIDs as $graphicID => $row) {
        if (!isset($row['description'])) $description='NULL'; else $description="'".addslashes($row['description'])."'";
        if (!isset($row['graphicFile'])) $graphicFile='NULL'; else $graphicFile="'".addslashes($row['graphicFile'])."'";
        if (!isset($row['sofFactionName'])) $sofFactionName='NULL'; else $sofFactionName="'".addslashes($row['sofFactionName'])."'";
        if (!isset($row['sofHullName'])) $sofHullName='NULL'; else $sofHullName="'".addslashes($row['sofHullName'])."'";
        if (!isset($row['sofRaceName'])) $sofRaceName='NULL'; else $sofRaceName="'".addslashes($row['sofRaceName'])."'";
        $biginsert.="($graphicID, $description, $graphicFile, $sofFactionName, $sofHullName, $sofRaceName),";
    }
    $biginsert=rtrim($biginsert,',').";";
    if (!$silent) echo('insert to DB...');
    db_uquery($biginsert);
    return true;
}

/**
 * Fetches resource file names for CCP WebGL from manually created `ccpwglmapping` table
 * 
 * @deprecated
 * @param type $typeID
 * @return array typeID, shipModel, background, thrusters or false if not found
 */
function getResourceFromMapping($typeID) {
    $modelinfo=db_asocquery("SELECT * FROM `ccpwglmapping` WHERE `typeID`=$typeID;");
    if (count($modelinfo)==1) {
        return $modelinfo[0];
    } else return false;
}

/**
 * Fetches resource file names for CCP WebGL from YAML imported data
 * 
 * @param type $typeID
 * @return array typeID, shipModel, background, thrusters or false if not found
 */
function getResourceFromYaml($typeID) {
    global $LM_EVEDB;
    $sql="SELECT * FROM `$LM_EVEDB`.`yamlTypeIDs` yti JOIN `$LM_EVEDB`.`yamlGraphicIDs` ygi ON yti.`graphicID`=ygi.`graphicID` WHERE yti.`typeID`=$typeID";
    //echo("DEBUG: $sql");
    $modelinfo=db_asocquery($sql);
    if (count($modelinfo)==1) {
        $model=$modelinfo[0];
        $returns['typeID']=$typeID;
        $returns['shipModel']=$model['graphicFile'];
        $returns['graphicFile']=$model['graphicFile'];
        $returns['sofFactionName']=$model['sofFactionName'];
        $returns['sofHullName']=$model['sofHullName'];
        $returns['sofRaceName']=$model['sofRaceName'];

        switch($model['sofRaceName']) {
            case 'caldari':
                $returns['background']='res:/dx9/scene/universe/c03_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_caldari.red';
                break;
            case 'minmatar':
                $returns['background']='res:/dx9/scene/universe/m01_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_minmatar.red';
                break;
            case 'amarr':
                $returns['background']='res:/dx9/scene/universe/a04_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_amarr.red';
                break;
            case 'gallente':
                $returns['background']='res:/dx9/scene/universe/g04_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_gallente.red';
                break;
            case 'angel':
                $returns['background']='res:/dx9/scene/universe/m01_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_minmatar.red';
                break;
            case 'sansha':
                $returns['background']='res:/dx9/scene/universe/a04_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_amarr.red';
                break;
            case 'ore':
                $returns['background']='res:/dx9/scene/universe/g04_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_gallente.red';
                break;
            default:
                $returns['background']='res:/dx9/scene/universe/g04_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_gallente.red';
        }
        return $returns;
    } else return false;
}

?>