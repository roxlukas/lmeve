<?php
//YAML - graphics related functions
include_once('spyc/Spyc.php');

/**
 * Updates typeID information from typeIDs.yaml file
 * 
 * @global $LM_EVEDB - EVE Static Data db name
 */
function updateYamlTypeIDs() {
    /*
CREATE TABLE IF NOT EXISTS `yamltypeids` (
  `typeID` int(11) NOT NULL,
  `graphicID` int(11) NULL,
  `iconID` int(11) NULL,
  `radius` decimal(30,2) NULL,
  `soundID` int(11) NULL,
  PRIMARY KEY (`typeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 
    */
    global $LM_EVEDB;
    $typeIDs = Spyc::YAMLLoad("../data/$LM_EVEDB/typeIDs.yaml");
    if (!empty($typeIDs)) {
        db_uquery("TRUNCATE TABLE `yamltypeids`;");
    } else return false;
    $biginsert="INSERT INTO `yamltypeids` VALUES ";
    foreach($typeIDs as $typeID => $row) {
        if (!isset($row['graphicID'])) $graphicID='NULL'; else $graphicID=addslashes($row['graphicID']);
        if (!isset($row['iconID'])) $iconID='NULL'; else $iconID=addslashes($row['iconID']);
        if (!isset($row['radius'])) $radius='NULL'; else $radius=addslashes($row['radius']);
        if (!isset($row['soundID'])) $soundID='NULL'; else $soundID=addslashes($row['soundID']);
        $biginsert.="($typeID, $graphicID, $iconID, $radius, $soundID),";
    }
    $biginsert=rtrim($biginsert,',').";";
    db_uquery($biginsert);
    return true;
}

/**
 * Updates typeID information from typeIDs.yaml file
 * 
 * @global $LM_EVEDB - EVE Static Data db name
 */
function updateYamlGraphicIDs() {
    /*
CREATE TABLE IF NOT EXISTS `yamlgraphicids` (
  `graphicID` int(11) NULL,
  `colorScheme` varchar(256) NULL,
  `description` varchar(256) NULL,
  `graphicFile` varchar(512) NULL,
  `graphicName` varchar(256) NULL,
  `graphicType` varchar(256) NULL,
  `gfxRaceID` varchar(64) NULL,
  `collidable` boolean NULL,
  `directoryID` int(11) NULL,
  PRIMARY KEY (`graphicID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 
    */
    global $LM_EVEDB;
    $graphicIDs = Spyc::YAMLLoad("../data/$LM_EVEDB/graphicIDs.yaml");
    if (!empty($graphicIDs)) {
        db_uquery("TRUNCATE TABLE `yamlgraphicids`;");
    } else return false;
    $biginsert="INSERT INTO `yamlgraphicids` VALUES ";
    foreach($graphicIDs as $graphicID => $row) {
        if (!isset($row['colorScheme'])) $colorScheme='NULL'; else $colorScheme="'".addslashes($row['colorScheme'])."'";
        if (!isset($row['description'])) $description='NULL'; else $description="'".addslashes($row['description'])."'";
        if (!isset($row['graphicFile'])) $graphicFile='NULL'; else $graphicFile="'".addslashes($row['graphicFile'])."'";
        if (!isset($row['graphicName'])) $graphicName='NULL'; else $graphicName="'".addslashes($row['graphicName'])."'";
        if (!isset($row['graphicType'])) $graphicType='NULL'; else $graphicType="'".addslashes($row['graphicType'])."'";
        if (!isset($row['gfxRaceID'])) $gfxRaceID='NULL'; else $gfxRaceID="'".addslashes($row['gfxRaceID'])."'";
        if (!isset($row['collidable'])) $collidable='NULL'; else $collidable=$row['collidable'];
        if (!isset($row['directoryID'])) $directoryID='NULL'; else $directoryID=$row['directoryID'];
        $biginsert.="($graphicID, $colorScheme, $description, $graphicFile, $graphicName, $graphicType, $gfxRaceID, $collidable, $directoryID),";
    }
    $biginsert=rtrim($biginsert,',').";";
    db_uquery($biginsert);
    return true;
}

/**
 * Fetches resource file names for CCP WebGL from manually created `ccpwglmapping` table
 * 
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
    $modelinfo=db_asocquery("SELECT * FROM `yamltypeids` yti JOIN `yamlgraphicids` ygi ON yti.`graphicID`=ygi.`graphicID` WHERE yti.`typeID`=$typeID;");
    if (count($modelinfo)==1) {
        $model=$modelinfo[0];
        $returns['typeID']=$typeID;
        $returns['shipModel']=$model['graphicFile'];
        /*
        NULL
        Caldari
        Minmatar
        Amarr
        Gallente
        Jove
        Angel
        Sansha
        ORE
        Concord
        RogueDrone
        SOCT
        Generic
        Sleeper
        Talocan
         * (28661, 'res:/dx9/model/ship/gallente/battleship/gb2/duvolle/gb2_t2_duvolle.red', 'res:/dx9/scene/universe/g04_cube.red', 'res:/dx9/model/ship/booster/booster_gallente.red'),
(28659, 'res:/dx9/model/ship/amarr/battleship/ab1/sarum/ab1_t2_sarum.red', 'res:/dx9/scene/universe/a04_cube.red', 'res:/dx9/model/ship/booster/booster_amarr.red'),
(28710, 'res:/dx9/model/ship/caldari/battleship/cb1/laidai/cb1_t2_laidai.red', 'res:/dx9/scene/universe/c03_cube.red', 'res:/dx9/model/ship/booster/booster_caldari.red'),
(28665, 'res:/dx9/model/ship/minmatar/battleship/mb2/brutor/mb2_t2_brutor.red', 'res:/dx9/scene/universe/m01_cube.red', 'res:/dx9/model/ship/booster/booster_minmatar.red');
        */
        switch($model['gfxRaceID']) {
            case 'Caldari':
                $returns['background']='res:/dx9/scene/universe/c03_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_caldari.red';
                break;
            case 'Minmatar':
                $returns['background']='res:/dx9/scene/universe/m01_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_minmatar.red';
                break;
            case 'Amarr':
                $returns['background']='res:/dx9/scene/universe/a04_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_amarr.red';
                break;
            case 'Gallente':
                $returns['background']='res:/dx9/scene/universe/g04_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_gallente.red';
                break;
            case 'Angel':
                $returns['background']='res:/dx9/scene/universe/m01_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_minmatar.red';
                break;
            case 'Sansha':
                $returns['background']='res:/dx9/scene/universe/a04_cube.red';
                $returns['thrusters']='res:/dx9/model/ship/booster/booster_amarr.red';
                break;
            case 'ORE':
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