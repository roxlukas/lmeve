<?php
//YAML - skin related functions
include_once('yaml_common.php');

/**
 * Updates skin and skinship information from skins.yaml file
 * 
 * @global $LM_EVEDB - EVE Static Data db name
 */
function updateYamlSkins($silent=true) {
    global $LM_EVEDB;
    
    $file="../data/$LM_EVEDB/skins.yaml";
    
    if (!file_exists($file)) {
        echo("File $file does not exist. Make sure YAML files from EVE SDE are in appropriate directories.");
        return FALSE;
    }
       
    $dropyamlskins="DROP TABLE IF EXISTS `$LM_EVEDB`.`skins`;";
    db_uquery($dropyamlskins);
    
    $createyamlSkins="CREATE TABLE `$LM_EVEDB`.`skins` (
      `skinID` int(11) NOT NULL,
      `internalName` varchar(70) DEFAULT NULL,
      `skinMaterialID` int(11) DEFAULT NULL,
      `allowCCPDevs` boolean DEFAULT NULL,
      `visibleSerenity` boolean DEFAULT NULL,
      `visibleTranquility` boolean DEFAULT NULL,
      PRIMARY KEY (`skinID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    db_uquery($createyamlSkins);
    
    $dropyamlskinship="DROP TABLE IF EXISTS `$LM_EVEDB`.`skinShip`;";
    db_uquery($dropyamlskinship);
    
    $createyamlSkinShip="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`skinShip` (
      `skinID` int(11) DEFAULT NULL,
      `typeID` int(11) DEFAULT NULL,
      KEY `ix_skinShip_skinID` (`skinID`),
      KEY `ix_skinShip_typeID` (`typeID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    db_uquery($createyamlSkinShip);
    
    if (!$silent) echo('loading YAML...');
    
    //switching from Spyc to YAML PECL module
    $skins = yaml_parse_wrapper($file);
    
    if (!empty($skins)) {
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`skins`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`skinShip`;");
    } else return false;
    
    $biginsertSkins="INSERT INTO `$LM_EVEDB`.`skins` VALUES ";
    $biginsertSkinShip="INSERT INTO `$LM_EVEDB`.`skinShip` VALUES ";
    foreach($skins as $skinID => $row) {
        $skinID=yaml_prepare($row['skinID']);
        $allowCCPDevs=yaml_prepare($row['allowCCPDevs']);
        $internalName=yaml_prepare($row['internalName']);
        $visibleSerenity=yaml_prepare($row['visibleSerenity']);
        $visibleTranquility=yaml_prepare($row['visibleTranquility']);
        $skinMaterialID=yaml_prepare($row['skinMaterialID']);
        
        if ($visibleTranquility==1) $visibleTranquility='1'; else $visibleTranquility='0';
        if ($visibleSerenity==1) $visibleSerenity='1'; else $visibleSerenity='0';
        if ($allowCCPDevs==1) $allowCCPDevs='1'; else $allowCCPDevs='0';
        
        $biginsertSkins.="($skinID, '$internalName', $skinMaterialID, $allowCCPDevs,$visibleSerenity,$visibleTranquility),";

        if (is_array($row['types'])) { //if there are types
            foreach ($row['types'] as $shipTypeID) {
                $biginsertSkinShip.="($skinID, $shipTypeID),";
            }
        }
    }
    
    $biginsertSkins=rtrim($biginsertSkins,',').";";
    $biginsertSkinShip=rtrim($biginsertSkinShip,',').";";
    
    if (!$silent) echo('insert to DB...');
    
    db_uquery($biginsertSkins);
    db_uquery($biginsertSkinShip);
    
    return true;
}



/**
 * Updates skinLicense information from skins.yaml file
 * 
 * @global $LM_EVEDB - EVE Static Data db name
 */
function updateYamlSkinLicenses($silent=true) {
    global $LM_EVEDB;
    
    $file="../data/$LM_EVEDB/skinLicenses.yaml";
    
    if (!file_exists($file)) {
        echo("File $file does not exist. Make sure YAML files from EVE SDE are in appropriate directories.");
        return FALSE;
    }
       
    $dropyamlskinLicense="DROP TABLE IF EXISTS `$LM_EVEDB`.`skinLicense`;";
    db_uquery($dropyamlskinLicense);
    
    $createyamlSkinLicense="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`skinLicense` (
      `licenseTypeID` int(11) NOT NULL,
      `duration` int(11) DEFAULT NULL,
      `skinID` int(11) DEFAULT NULL,
      PRIMARY KEY (`licenseTypeID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    db_uquery($createyamlSkinLicense);
      
    if (!$silent) echo('loading YAML...');
    
    //switching from Spyc to YAML PECL module
    $skins = yaml_parse_wrapper($file);
    
    if (!empty($skins)) {
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`skinLicense`;");
    } else return false;
    
    $biginsertSkinLicense="INSERT INTO `$LM_EVEDB`.`skinLicense` VALUES ";

    foreach($skins as $licenseTypeID => $row) {
        $skinID=yaml_prepare($row['skinID']);
        $duration=yaml_prepare($row['duration']);
        $licenseTypeID=yaml_prepare($row['licenseTypeID']);
        
        $biginsertSkinLicense.="($licenseTypeID, $duration, $skinID),";
    }
    
    $biginsertSkinLicense=rtrim($biginsertSkinLicense,',').";";
    
    if (!$silent) echo('insert to DB...');
    
    db_uquery($biginsertSkinLicense);
    
    return true;
}

/**
 * Updates skinMaterials information from skinMaterials.yaml file
 * 
 * @global $LM_EVEDB - EVE Static Data db name
 */
function updateYamlSkinMaterials($silent=true) {
    global $LM_EVEDB;
    
    $file="../data/$LM_EVEDB/skinMaterials.yaml";
    
    if (!file_exists($file)) {
        echo("File $file does not exist. Make sure YAML files from EVE SDE are in appropriate directories.");
        return FALSE;
    }
       
    $dropyamlskinMaterials="DROP TABLE IF EXISTS `$LM_EVEDB`.`skinMaterials`;";
    db_uquery($dropyamlskinMaterials);
    
    $createyamlSkinMaterials="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`skinMaterials` (
      `skinMaterialID` int(11) NOT NULL,
      `materialSetID` int(11) DEFAULT NULL,
      `displayNameID` int(11) DEFAULT NULL,
      PRIMARY KEY (`skinMaterialID`),
      KEY `ix_skinMaterials_materialSetID` (`materialSetID`),
      KEY `ix_skinMaterials_displayNameID` (`displayNameID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    db_uquery($createyamlSkinMaterials);
      
    if (!$silent) echo('loading YAML...');
    
    //switching from Spyc to YAML PECL module
    $skins = yaml_parse_wrapper($file);
    
    if (!empty($skins)) {
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`skinMaterials`;");
    } else return false;
    
    $biginsertSkinMaterials="INSERT INTO `$LM_EVEDB`.`skinMaterials` VALUES ";

    foreach($skins as $licenseTypeID => $row) {
        $skinMaterialID=yaml_prepare($row['skinMaterialID']);
        $materialSetID=yaml_prepare($row['materialSetID']);
        $displayNameID=yaml_prepare($row['displayNameID']);
        
        $biginsertSkinMaterials.="($skinMaterialID, $materialSetID, $displayNameID),";
    }
    
    $biginsertSkinMaterials=rtrim($biginsertSkinMaterials,',').";";
    
    if (!$silent) echo('insert to DB...');
    
    db_uquery($biginsertSkinMaterials);
    
    return true;
}

function createYamlSkinMaterialSets() {
    global $LM_EVEDB;
    $createyamlSkinMaterialSets="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`skinMaterialSets` (
          `skinMaterialSetID` int(11) NOT NULL,
          `colorHullR` decimal(17,15) DEFAULT NULL,
          `colorHullG` decimal(17,15) DEFAULT NULL,
          `colorHullB` decimal(17,15) DEFAULT NULL,
          `colorHullA` decimal(17,15) DEFAULT NULL,
          `colorPrimaryR` decimal(17,15) DEFAULT NULL,
          `colorPrimaryG` decimal(17,15) DEFAULT NULL,
          `colorPrimaryB` decimal(17,15) DEFAULT NULL,
          `colorPrimaryA` decimal(17,15) DEFAULT NULL,
          `colorSecondaryR` decimal(17,15) DEFAULT NULL,
          `colorSecondaryG` decimal(17,15) DEFAULT NULL,
          `colorSecondaryB` decimal(17,15) DEFAULT NULL,
          `colorSecondaryA` decimal(17,15) DEFAULT NULL,
          `colorWindowR` decimal(17,15) DEFAULT NULL,
          `colorWindowG` decimal(17,15) DEFAULT NULL,
          `colorWindowB` decimal(17,15) DEFAULT NULL,
          `colorWindowA` decimal(17,15) DEFAULT NULL,   
          `description` varchar(128) DEFAULT NULL,
          `sofFactionName` varchar(32) DEFAULT NULL,
          PRIMARY KEY (`skinMaterialSetID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
     return db_uquery($createyamlSkinMaterialSets);
}

function dropYamlSkinMaterialSets () {
    global $LM_EVEDB;
    $dropyamlSkinMaterialSets="DROP TABLE IF EXISTS `$LM_EVEDB`.`skinMaterialSets`;";
    return db_uquery($dropyamlSkinMaterialSets);
}

function createSkinMaterialsRGBview() {
    global $LM_EVEDB;
    $sql="CREATE OR REPLACE VIEW `$LM_EVEDB`.`skinMaterialsRGB` AS
    SELECT 
    sma.`skinMaterialID`,
    sms.`sofFactionName` AS `material`,
    sma.`displayNameID`,
    LOWER(CONCAT(HEX(FLOOR(255*sms.`colorWindowR`)),HEX(FLOOR(255*sms.`colorWindowG`)),HEX(FLOOR(255*sms.`colorWindowB`)))) AS `colorWindow`,
    LOWER(CONCAT(HEX(FLOOR(255*sms.`colorPrimaryR`)),HEX(FLOOR(255*sms.`colorPrimaryG`)),HEX(FLOOR(255*sms.`colorPrimaryB`)))) AS `colorPrimary`,
    LOWER(CONCAT(HEX(FLOOR(255*sms.`colorSecondaryR`)),HEX(FLOOR(255*sms.`colorSecondaryG`)),HEX(FLOOR(255*sms.`colorSecondaryB`)))) AS `colorSecondary`,
    LOWER(CONCAT(HEX(FLOOR(255*sms.`colorHullR`)),HEX(FLOOR(255*sms.`colorHullG`)),HEX(FLOOR(255*sms.`colorHullB`)))) AS `colorHull`
    FROM `$LM_EVEDB`.`skinMaterialSets` sms
    JOIN `$LM_EVEDB`.`skinMaterials` sma
    ON sma.`materialSetID`=sms.`skinMaterialSetID`;";
    
    return db_uquery($sql);
}

/**
 * Updates graphicMaterialSetsinformation from graphicMaterialSets.yaml file
 * 
 * @global $LM_EVEDB - EVE Static Data db name
 */
function updateYamlSkinMaterialSets($silent=true,$file=null) {
    global $LM_EVEDB;
    
    if (is_null($file)) $file="../data/$LM_EVEDB/graphicMaterialSets.yaml";
    
    if (!file_exists($file)) {
        echo("File $file does not exist.\r\nThis file might not yet be part of the SDE, in which case I will only create empty tables for the SKIN data.");
        createYamlSkinMaterialSets();
        createSkinMaterialsRGBview();
        return FALSE;
    }
       
    dropYamlSkinMaterialSets();
    
    createYamlSkinMaterialSets();
      
    if (!$silent) echo('loading YAML...');
    
    //switching from Spyc to YAML PECL module
    $skins = yaml_parse_wrapper($file);
    
    if (!empty($skins)) {
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`skinMaterialSets`;");
    } else return false;
    
    $biginsertSkinMaterials="INSERT INTO `$LM_EVEDB`.`skinMaterialSets` VALUES ";

    foreach($skins as $skinMaterialSetID => $row) {
        $colorHullR=yaml_prepare($row['colorHull'][0]);
        $colorHullG=yaml_prepare($row['colorHull'][1]);
        $colorHullB=yaml_prepare($row['colorHull'][2]);
        $colorHullA=yaml_prepare($row['colorHull'][3]);
        $colorPrimaryR=yaml_prepare($row['colorPrimary'][0]);
        $colorPrimaryG=yaml_prepare($row['colorPrimary'][1]);
        $colorPrimaryB=yaml_prepare($row['colorPrimary'][2]);
        $colorPrimaryA=yaml_prepare($row['colorPrimary'][3]);
        $colorSecondaryR=yaml_prepare($row['colorSecondary'][0]);
        $colorSecondaryG=yaml_prepare($row['colorSecondary'][1]);
        $colorSecondaryB=yaml_prepare($row['colorSecondary'][2]);
        $colorSecondaryA=yaml_prepare($row['colorSecondary'][3]);
        $colorWindowR=yaml_prepare($row['colorWindow'][0]);
        $colorWindowG=yaml_prepare($row['colorWindow'][1]);
        $colorWindowB=yaml_prepare($row['colorWindow'][2]);
        $colorWindowA=yaml_prepare($row['colorWindow'][3]);
        $description=yaml_prepare($row['description']);
        $sofFactionName=yaml_prepare($row['sofFactionName']);
        
        $biginsertSkinMaterials.="($skinMaterialSetID, $colorHullR, $colorHullG, $colorHullB, $colorHullA, $colorPrimaryR, $colorPrimaryG, $colorPrimaryB, $colorPrimaryA, $colorSecondaryR, $colorSecondaryG, $colorSecondaryB, $colorSecondaryA, $colorWindowR, $colorWindowG, $colorWindowB, $colorWindowA, '$description', '$sofFactionName'),";
    }
    
    $biginsertSkinMaterials=rtrim($biginsertSkinMaterials,',').";";
    
    if (!$silent) echo('insert to DB...');
    
    db_uquery($biginsertSkinMaterials);
    
    //view
    if (!$silent) echo('creating RGB view...');
    createSkinMaterialsRGBview();
    
    return true;
}
?>
