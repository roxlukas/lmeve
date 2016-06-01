<?php

include_once('yaml_skins.php');
include_once('yaml_blueprints.php');

/**
 * LMeve was written at the same time Fuzzysteve started to include YAML files in his SDE conversion
 * due to lack of data, Lukas Rox wrote his own YAML importer, which resulted in slightly different database schema
 * this function creates database VIEWs to create a compatibility layer between Fuzzysteve conversion and LMeve
 */
function recreateSdeCompatViews() {
    global $LM_EVEDB;    
    
    //blueprints
    if (!checkIfTableExists('yamlBlueprintTypes')) db_uquery("CREATE OR REPLACE VIEW `$LM_EVEDB`.`yamlBlueprintTypes` AS
        SELECT `typeID` as `blueprintTypeID`, `maxProductionLimit` FROM `$LM_EVEDB`.`industryBlueprints`");
    
    if (!checkIfTableExists('yamlBlueprintProducts')) db_uquery("CREATE OR REPLACE VIEW `$LM_EVEDB`.`yamlBlueprintProducts` AS
        SELECT 
        iap.`typeID` AS `blueprintTypeID`,
        iap.`productTypeID`,
        iap.`activityID`,
        iapr.`probability`,
        iac.`time`
        FROM `$LM_EVEDB`.`industryActivityProducts` iap
        LEFT JOIN `$LM_EVEDB`.`industryActivityProbabilities` iapr
        ON iap.`typeID`=iapr.`typeID` AND iap.`activityID`=8
        JOIN `$LM_EVEDB`.`industryActivity` iac
        ON iap.`typeID`=iac.`typeID` AND iac.`activityID`=iap.`activityID`;");
    
     if (!checkIfTableExists('yamlBlueprintMaterials')) db_uquery("CREATE OR REPLACE VIEW `$LM_EVEDB`.`yamlBlueprintMaterials` AS
        SELECT `typeID` AS `blueprintTypeID`,
        `materialTypeID`,
        `quantity`,
        `activityID`,
        0 AS `consume`
        FROM `$LM_EVEDB`.`industryActivityMaterials`;");
     
     if (!checkIfTableExists('yamlBlueprintSkills')) db_uquery("CREATE OR REPLACE VIEW `$LM_EVEDB`.`yamlBlueprintSkills` AS
        SELECT `typeID` AS `blueprintTypeID`,
        `activityID`,
        `skillID` AS `skillTypeID`,
        `level`
        FROM `$LM_EVEDB`.`industryActivitySkills`;");
     
     if (!checkIfTableExists('invBlueprintTypes')) recreateLegacyTables();
     if (!checkIfTableExists('ramTypeRequirements')) recreateLegacyTables();
     
     //graphics
     if (!checkIfTableExists('yamlGraphicIDs')) db_uquery("CREATE OR REPLACE VIEW `$LM_EVEDB`.`yamlGraphicIDs` AS SELECT
         `graphicID`,
         `description`, 
         `graphicFile`, 
         `sofFactionName`, 
         `sofHullName`,
         `sofRaceName`
      FROM `$LM_EVEDB`.`eveGraphics`;");
     
     if (!checkIfTableExists('yamlTypeIDs')) db_uquery("CREATE OR REPLACE VIEW `$LM_EVEDB`.`yamlTypeIDs` AS SELECT
         `typeID`,
         `graphicID`, 
         `iconID`, 
         0 AS `radius`, 
         `soundID`
      FROM `$LM_EVEDB`.`invTypes`;");
     
     if (!checkIfTableExists('yamlInvTraits')) db_uquery("CREATE OR REPLACE VIEW `$LM_EVEDB`.`yamlInvTraits` AS SELECT
      `typeID`,
      `skillID`,
      `bonus`,
      `bonusText`,
      `unitID`
      FROM `$LM_EVEDB`.`invTraits`;");
     
     //skins - SDE tables are compatible - no need to update
     //updateYamlSkins(FALSE);
     //updateYamlSkinLicenses(FALSE);
     //updateYamlSkinMaterials(FALSE);
     if (!checkIfTableExists('skinMaterialSets')) updateYamlSkinMaterialSets(TRUE,'../data/graphicMaterialSets.yaml');
     if (!checkIfTableExists('skinMaterialsRGB')) createSkinMaterialsRGBview();
    return TRUE;
}

function checkIfTableExists($tab) {
    global $LM_EVEDB;
    
    $ret=db_asocquery("SHOW TABLES FROM `$LM_EVEDB`;");
    foreach ($ret as $row) {
        foreach ($row as $col) {
            if ($col==$tab) return TRUE;
        }
    }
    return FALSE;
}

function updateUserstable() {
    global $USERSTABLE;
    $table=db_asocquery("DESCRIBE $USERSTABLE;");
    foreach ($table as $column) {
        if ($column['Field']=='pass' && $column['Type']!='varchar(64)') {
            db_uquery("ALTER TABLE  `$USERSTABLE` CHANGE  `pass` `pass` VARCHAR(64) NOT NULL DEFAULT  '';");
        }
    }    
}

function updateCrestIndustrySystems() {
    $table=db_asocquery("DESCRIBE `crestindustrysystems`;");
    foreach ($table as $column) {
        if ($column['Field']=='costIndex' && $column['Type']!='decimal(20,4)') {
            db_uquery("ALTER TABLE  `crestindustrysystems` CHANGE  `costIndex` `costIndex` DECIMAL(20,4) NOT NULL;");
        }
    }
}
