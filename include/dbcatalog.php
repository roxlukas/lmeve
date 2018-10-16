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

function updateApiAssets() {
    $table=db_asocquery("DESCRIBE `apiassets`;");
    foreach ($table as $column) {
        if ($column['Field']=='flag' && $column['Type']!='int(11)') {
            db_uquery("ALTER TABLE `apiassets` CHANGE  `flag` `flag` int(11) NOT NULL;");
        }
    }
}

function updateCfgApiKeys() {
	$table=db_asocquery("DESCRIBE `cfgapikeys`;");
	//bugfix for multiple corps
	//ALTER TABLE `cfgapikeys` CHANGE `apiKeyID` `apiKEyID` INT( 11 ) NOT NULL AUTO_INCREMENT
}

function copyECfromAssetsToFacilities() {
    global $LM_EVEDB;
    //copy Engineering complexes to Facilities
    $sql = "INSERT IGNORE INTO `apifacilities` SELECT 
	apa.`itemID` AS facilityID,
	itp.`typeID`,
	itp.`typeName`,
	apa.`locationID` AS `solarSystemID`,
	map.`itemName` AS `solarSystemName`,
	map.`regionID`,
	reg.`regionName`,
	0.0 AS `starbaseModifier`,
	0.0 AS `tax`,
	apa.`corporationID`

        FROM `apiassets` apa
        JOIN `$LM_EVEDB`.`invTypes` itp
        ON apa.`typeID` = itp.`typeID`
        JOIN `$LM_EVEDB`.`mapDenormalize` map
        ON apa.`locationID` = map.`itemID`
        JOIN `$LM_EVEDB`.`mapRegions` reg
        ON map.`regionID` = reg.`regionID`
        WHERE itp.`groupID`=1404;";
    //Insert Engineering complexes from Assets into Facilities!
    return db_uquery($sql);
}


function createCitadelsView() {
    global $LM_EVEDB;

    $sql = "CREATE OR REPLACE VIEW `apicitadels` AS SELECT ass.*,itp.`typeName`,itp.`groupID`
    FROM `apiassets` ass
    JOIN `$LM_EVEDB`.`invTypes` itp
    ON ass.`typeID` = itp.`typeID`
    WHERE itp.`groupID` IN (1404, 1657);";

    return db_uquery($sql);
    //Citadel services - flag 127 in Assets API
}

//ESI updates
/**
 * Function updates all tables necessary for ESI support
 * 
 * @return type
 */
function esiUpdateAll() {
    esiCreateApiassetnames();
    $a = esiUpdateApicorps();
    $b = esiCreateCfgesitoken();
    $c = esiCreateEsistatus();
    $d = esiUpdateApiCorpMembers();
    $e = esiUpdateApiIndustryJobsCrius();
    $f = esiUpdateApimarketorders();
    $g = esiUpdateApiContractItems();
    $h = esiUpdateApiAssets();
    $i = esiUpdateApikills();
    return $a && $b && $c && $d && $e && $f && $g && $h && $i;
}

/**
 * Function checks if the table apicorps has the necessary columns for ESI support
 * 
 * @return boolean returns TRUE if table was correctly updated or if the update was already performed. Returns FALSE if update was not possible.
 */
function esiUpdateApicorps() {
    $table = db_asocquery("DESCRIBE `apicorps`;");
    $found = FALSE;
    foreach ($table as $column) {
        if ($column['Field']=='tokenID' && $column['Type']=='int(11)') {
            $found = TRUE;
        }
    }    
    if ($found === FALSE) {
        return db_uquery("ALTER TABLE `apicorps` ADD COLUMN `tokenID` int(11) NULL DEFAULT  NULL;") &&
        db_uquery("ALTER TABLE `apicorps` CHANGE COLUMN `keyID` `keyID` VARCHAR(255) NULL DEFAULT NULL;");
    }
    return TRUE;
}

function esiUpdateApiAssets() {
    $table = db_asocquery("DESCRIBE `apiassets`;");
    $found = FALSE;
    foreach ($table as $column) {
        if ($column['Field']=='is_blueprint_copy' && $column['Type']=='int(11)') {
            $found = TRUE;
        }
    }    
    if ($found === FALSE) {
        return db_uquery("ALTER TABLE `apiassets` ADD `is_blueprint_copy` INT NULL DEFAULT NULL AFTER `singleton`;");
    }
    return TRUE;
}

//ALTER TABLE `apikillvictims` ADD PRIMARY KEY ( `killID` )

function esiUpdateApikills() {
    $table = db_asocquery("DESCRIBE `apikills`;");
    $found = FALSE;
    foreach ($table as $column) {
        if ($column['Field']=='killmail_hash' && $column['Type']=='varchar(40)') {
            $found = TRUE;
        }
    }    
    if ($found === FALSE) {
        return db_uquery("ALTER TABLE `apikills` ADD `killmail_hash` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
    }
    return TRUE;
}

/**
 * Function checks if the table apimarketorders has the necessary columns for ESI support
 * 
 * @return boolean returns TRUE if table was correctly updated or if the update was already performed. Returns FALSE if update was not possible.
 */
function esiUpdateApimarketorders() {
    $table = db_asocquery("DESCRIBE `apimarketorders`;");
    $found = FALSE;
    foreach ($table as $column) {
        if ($column['Field']=='range' && $column['Type']=='int(11)') {
            $found = TRUE;
        }
    }    
    if ($found === TRUE) {
        return db_uquery("ALTER TABLE `apimarketorders` CHANGE COLUMN `range` `range` VARCHAR(12) NULL DEFAULT NULL;");
    }
    return TRUE;
}

/**
 * Function checks if the table apicontractitems has the necessary columns for ESI support
 * 
 * @return boolean returns TRUE if table was correctly updated or if the update was already performed. Returns FALSE if update was not possible.
 */
function esiUpdateApiContractItems() {
    $table = db_asocquery("DESCRIBE `apicontractitems`;");
    $found = FALSE;
    foreach ($table as $column) {
        if ($column['Field']=='rawQuantity' && $column['Type']=='int(11)') {
            $found = TRUE;
        }
    }    
    if ($found === FALSE) {
        return db_uquery("ALTER TABLE `apicontractitems` ADD COLUMN `rawQuantity` int(11) NULL DEFAULT  NULL AFTER `quantity`;");
    }
    return TRUE;
}

/**
 * Function checks if the table apiindustryjobscrius has the necessary columns for ESI support
 * 
 * @return boolean returns TRUE if table was correctly updated or if the update was already performed. Returns FALSE if update was not possible.
 */
function esiUpdateApiIndustryJobsCrius() {
    $table = db_asocquery("DESCRIBE `apiindustryjobscrius`;");
    $found = FALSE;
    foreach ($table as $column) {
        if ($column['Field']=='status' && $column['Type']=='int(11)') {
            $found = TRUE;
        }
    }    
    if ($found === TRUE) {
        return db_uquery("ALTER TABLE `apiindustryjobscrius` CHANGE COLUMN `status` `status` VARCHAR(255) NULL DEFAULT NULL;");
    }
    return TRUE;
}

function esiCreateApiassetnames() {
    if (!checkIfTableExists('apiassetnames')) {
        return db_uquery("CREATE TABLE IF NOT EXISTS `apiassetnames` (
            `itemID` bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `itemName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1;");
    }
    return TRUE;
}

function esiCreateCfgesitoken() {
    if (!checkIfTableExists('cfgesitoken')) {
        return db_uquery("CREATE TABLE IF NOT EXISTS `cfgesitoken` (
            `tokenID` int(11) NOT NULL AUTO_INCREMENT,
            `token` varchar(255) NOT NULL,
            PRIMARY KEY (`tokenID`),
            UNIQUE KEY `keyID` (`token`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
    }
    return TRUE;
}

function esiCreateEsistatus() {
    if (!checkIfTableExists('esistatus')) {
        return db_uquery("CREATE TABLE IF NOT EXISTS `esistatus` (
        `errorID` int(11) NOT NULL AUTO_INCREMENT,
        `tokenID` varchar(255) NOT NULL,
        `route` varchar(255) NOT NULL,
        `date` datetime NOT NULL,
        `errorCode` int(11) NOT NULL,
        `errorCount` int(11) NOT NULL DEFAULT '0',
        `errorMessage` varchar(1024) NOT NULL,
        PRIMARY KEY (`errorID`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
    }
    return TRUE;
}

function esiUpdateApiCorpMembers() {
    $table = db_asocquery("DESCRIBE `apicorpmembers`;");
    $found = FALSE;
    foreach ($table as $column) {
        if (
                ($column['Field']=='logonDateTime' && $column['Type']=='datetime')
                || ($column['Field']=='logoffDateTime' && $column['Type']=='datetime') 
                || ($column['Field']=='solarSystemID' && $column['Type']=='bigint(11)') 
                || ($column['Field']=='shipID' && $column['Type']=='int(11)') 
           ) {
                $found = TRUE;
        }
    }    
    if ($found === FALSE) {
        $a = db_uquery("ALTER TABLE `apicorpmembers` ADD COLUMN `logonDateTime` datetime NULL DEFAULT NULL;");
        $b = db_uquery("ALTER TABLE `apicorpmembers` ADD COLUMN `logoffDateTime` datetime NULL DEFAULT NULL;");
        $c = db_uquery("ALTER TABLE `apicorpmembers` ADD COLUMN `solarSystemID` bigint(11) NULL DEFAULT NULL;");
        $d = db_uquery("ALTER TABLE `apicorpmembers` ADD COLUMN `shipID` int(11) NULL DEFAULT NULL;");
    }
    return $a && $b && $c && $d;
}
