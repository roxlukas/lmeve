<?php
//YAML - graphics related functions
//include_once('spyc/Spyc.php');
include_once('yaml_common.php');

function updateYamlBlueprints($silent=true) {
    global $LM_EVEDB;
    
    $file="../data/$LM_EVEDB/blueprints.yaml";
    
    if (!file_exists($file)) {
        echo("File $file does not exist. Make sure YAML files from EVE SDE are in appropriate directories.");
        return FALSE;
    }
    
    $createyamlBlueprintTypes="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlBlueprintTypes` (
      `blueprintTypeID` int(11) NOT NULL,
      `maxProductionLimit` int(11) NOT NULL,
      PRIMARY KEY (`blueprintTypeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlBlueprintTypes);
    
    $createyamlBlueprintProducts="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlBlueprintProducts` (
      `blueprintTypeID` int(11) NOT NULL,
      `productTypeID` int(11) NOT NULL,
      `activityID` int(11) NOT NULL,
      `probability` decimal(20,2) NOT NULL DEFAULT 1.0,
      `time` int(11) NOT NULL,
      PRIMARY KEY (`blueprintTypeID`,`productTypeID`,`activityID`),
      KEY `blueprintTypeID` (`blueprintTypeID`),
      KEY `productTypeID` (`productTypeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlBlueprintProducts);
    
    $createyamlBlueprintMaterials="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlBlueprintMaterials` (
      `blueprintTypeID` int(11) NOT NULL,
      `materialTypeID` int(11) NOT NULL,
      `quantity` int(11) NOT NULL,
      `activityID` int(11) NOT NULL,
      `consume` tinyint(5) NOT NULL,
      PRIMARY KEY (`blueprintTypeID`,`materialTypeID`,`activityID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlBlueprintMaterials);
    
    $createyamlBlueprintSkills="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlBlueprintSkills` (
      `blueprintTypeID` int(11) NOT NULL,
      `activityID` int(11) NOT NULL,
      `skillTypeID` int(11) NOT NULL,
      `level` tinyint(5) NOT NULL,
      PRIMARY KEY (`blueprintTypeID`,`activityID`,`skillTypeID`),
      KEY `blueprintTypeID` (`blueprintTypeID`),
      KEY `skillTypeID` (`skillTypeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlBlueprintSkills);
    
    if (!$silent) echo('loading YAML...');
    //switching from Spyc to YAML PECL module
    $blueprints = yaml_parse_wrapper($file);
    if ($blueprints===FALSE) die("yaml_parse_file failed for $file");
    
    //var_dump($blueprints);
    
    if (!empty($blueprints)) {
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlBlueprintTypes`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlBlueprintProducts`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlBlueprintMaterials`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlBlueprintSkills`;");
    } else return false;
    $bigyamlBlueprintTypes="INSERT IGNORE INTO `$LM_EVEDB`.`yamlBlueprintTypes` VALUES ";
    $bigyamlBlueprintProducts="INSERT INTO `$LM_EVEDB`.`yamlBlueprintProducts` VALUES ";
    $bigyamlBlueprintMaterials="INSERT IGNORE INTO `$LM_EVEDB`.`yamlBlueprintMaterials` VALUES ";
    $bigyamlBlueprintSkills="INSERT IGNORE INTO `$LM_EVEDB`.`yamlBlueprintSkills` VALUES ";
    
    foreach($blueprints as $blueprintTypeID => $blueprint) {
        //first, store the BPO typeID
        $blueprintTypeID=yaml_prepare($blueprint['blueprintTypeID']);
        $maxProductionLimit=yaml_prepare($blueprint['maxProductionLimit'],0);
        $bigyamlBlueprintTypes.="($blueprintTypeID, $maxProductionLimit),";
        //var_dump($blueprint);
        //second, walk activities
        foreach ($blueprint['activities'] as $activityName => $activity) {
            //var_dump($activity);
            $activityID=yaml_activity2ID($activityName);
            $time=yaml_prepare($activity['time'],0);
            
            //walk new (post-Phoebe) flat YAML output
            foreach($activity as $key => $entries) {
                if (is_array($entries)) foreach($entries as $entry) {
                    switch($key) {
                        case 'materials':
                            $materialTypeID=yaml_prepare($entry['typeID']);
                            $quantity=yaml_prepare($entry['quantity'],0);
                            $consume=0; //all ingredient are always consumed post Phoebe
                            $bigyamlBlueprintMaterials.="($blueprintTypeID, $materialTypeID, $quantity, $activityID, $consume),";
                            break;
                        case 'products':
                            $quantity=yaml_prepare($entry['quantity']);
                            $probability=yaml_prepare($entry['probability'],'1.0');
                            $productTypeID=yaml_prepare($entry['typeID']);
                            $bigyamlBlueprintProducts.="($blueprintTypeID, $productTypeID, $activityID, $probability, $time),";
                            break;
                        case 'skills':
                            $skillTypeID=yaml_prepare($entry['typeID']);
                            $level=yaml_prepare($entry['level'],1);
                            $bigyamlBlueprintSkills.="($blueprintTypeID, $activityID, $skillTypeID, $level),";
                            break;
                    }
                }
            }
            
            /*******************************************************/
            
        }
    }
    //trim last comma
    $bigyamlBlueprintTypes=rtrim($bigyamlBlueprintTypes,',').";";
    $bigyamlBlueprintProducts=rtrim($bigyamlBlueprintProducts,',').";";
    $bigyamlBlueprintMaterials=rtrim($bigyamlBlueprintMaterials,',').";";
    $bigyamlBlueprintSkills=rtrim($bigyamlBlueprintSkills,',').";";
    //exec queries
    if (!$silent) echo('insert to DB...');
    
    db_uquery($bigyamlBlueprintTypes);
    db_uquery($bigyamlBlueprintProducts);
    db_uquery($bigyamlBlueprintMaterials);
    db_uquery($bigyamlBlueprintSkills);
    
    return TRUE;
}

function recreateLegacyTables() {
    
    global $LM_EVEDB;
    
    $createinvBlueprintTypes="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`invBlueprintTypes` (
      `blueprintTypeID` int(11) NOT NULL,
      `parentBlueprintTypeID` int(11) DEFAULT NULL,
      `productTypeID` int(11) DEFAULT NULL,
      `productionTime` int(11) DEFAULT NULL,
      `techLevel` smallint(6) DEFAULT NULL,
      `researchProductivityTime` int(11) DEFAULT NULL,
      `researchMaterialTime` int(11) DEFAULT NULL,
      `researchCopyTime` int(11) DEFAULT NULL,
      `researchTechTime` int(11) DEFAULT NULL,
      `productivityModifier` int(11) DEFAULT NULL,
      `materialModifier` smallint(6) DEFAULT NULL,
      `wasteFactor` smallint(6) DEFAULT NULL,
      `maxProductionLimit` int(11) DEFAULT NULL,
      PRIMARY KEY (`blueprintTypeID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    db_uquery($createinvBlueprintTypes);
    
    db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`invBlueprintTypes`;");
    
    $insertinvBlueprintTypes="INSERT IGNORE INTO `$LM_EVEDB`.`invBlueprintTypes`
    SELECT DISTINCT
    ybt.`blueprintTypeID`,
    NULL AS `parentBlueprintTypeID`,
    manu.`productTypeID`,
    manu.`time` AS `productionTime`,
    COALESCE(dgm.`valueInt`,dgm.`valueFloat`) AS `techLevel`,
    te.`time` AS `researchProductivityTime`,
    me.`time` AS `researchMaterialTime`,
    copy.`time` AS `researchCopyTime`,
    inv.`time` AS `researchTechTime`,
    0 AS `productivityModifier`,
    0 AS `materialModifier`,
    0 AS `wasteFactor`,
    ybt.`maxProductionLimit`   
    FROM `$LM_EVEDB`.`yamlBlueprintTypes` ybt
    LEFT JOIN `$LM_EVEDB`.`yamlBlueprintProducts` manu
      ON ybt.`blueprintTypeID`=manu.`blueprintTypeID` AND manu.`activityID`=1
    LEFT JOIN `$LM_EVEDB`.`yamlBlueprintProducts` copy
      ON ybt.`blueprintTypeID`=copy.`blueprintTypeID` AND copy.`activityID`=5
    LEFT JOIN `$LM_EVEDB`.`yamlBlueprintProducts` te
      ON ybt.`blueprintTypeID`=te.`blueprintTypeID` AND te.`activityID`=3
    LEFT JOIN `$LM_EVEDB`.`yamlBlueprintProducts` me
      ON ybt.`blueprintTypeID`=me.`blueprintTypeID` AND me.`activityID`=4
    LEFT JOIN `$LM_EVEDB`.`yamlBlueprintProducts` inv
      ON ybt.`blueprintTypeID`=inv.`blueprintTypeID` AND inv.`activityID`=8
    LEFT JOIN `$LM_EVEDB`.`invMetaTypes` imt
      ON manu.`productTypeID`=imt.`typeID`
    LEFT JOIN `$LM_EVEDB`.`dgmTypeAttributes` dgm
      ON manu.`productTypeID`=dgm.`typeID` AND dgm.`attributeID`=422
    WHERE TRUE;";
    
    db_uquery($insertinvBlueprintTypes);
    
    $updateTechLevelinvBlueprintTypes="UPDATE `$LM_EVEDB`.`invBlueprintTypes`
    SET `techLevel` = 1 WHERE `techLevel` IS NULL;";
    
    db_uquery($updateTechLevelinvBlueprintTypes);
    
    $createramTypeRequirements="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`ramTypeRequirements` (
    `typeID` int(11) NOT NULL,
    `activityID` tinyint(3) unsigned NOT NULL,
    `requiredTypeID` int(11) NOT NULL,
    `quantity` int(11) DEFAULT NULL,
    `damagePerJob` double DEFAULT NULL,
    `recycle` tinyint(1) DEFAULT NULL,
    PRIMARY KEY (`typeID`,`activityID`,`requiredTypeID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    db_uquery($createramTypeRequirements);
    
    db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`ramTypeRequirements`;");
    
    $insertramTypeRequirements="INSERT IGNORE INTO `$LM_EVEDB`.`ramTypeRequirements`      
    SELECT DISTINCT ybp.`productTypeID` AS `typeID`,
        ybp.`activityID`,
        ybm.`materialTypeID` AS `requiredTypeID`,
        ybm.`quantity`,
        ybm.`consume` AS `damagePerJob`,
        ybm.`consume` AS `recycle`
    FROM `$LM_EVEDB`.`yamlBlueprintProducts` ybp
    JOIN `$LM_EVEDB`.`yamlBlueprintMaterials` ybm
    ON ybp.`blueprintTypeID`=ybm.`blueprintTypeID` AND ybp.`activityID`=ybm.`activityID`;";
    
    db_uquery($insertramTypeRequirements);
    /*
     * invMetaTypes
     * typeID	parentTypeID	metaGroupID
	Edytuj	Usuń	11174	609	2
     * 
     * mozna polaczyc imt.typeID z productTypeID LEFT JOINem. Jesli NULL = Tech I, jesli 2 - Tech II 
     */
}

//Pre-Phoebe release
function updateYamlBlueprints_pre_Phoebe($silent=true) {
    global $LM_EVEDB;
    
    $file="../data/$LM_EVEDB/blueprints.yaml";
    
    if (!file_exists($file)) {
        echo("File $file does not exist. Make sure YAML files from EVE SDE are in appropriate directories.");
        return FALSE;
    }
    
    $createyamlBlueprintTypes="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlBlueprintTypes` (
      `blueprintTypeID` int(11) NOT NULL,
      `maxProductionLimit` int(11) NOT NULL,
      PRIMARY KEY (`blueprintTypeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlBlueprintTypes);
    
    $createyamlBlueprintProducts="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlBlueprintProducts` (
      `blueprintTypeID` int(11) NOT NULL,
      `productTypeID` int(11) NOT NULL,
      `activityID` int(11) NOT NULL,
      `probability` decimal(20,2) NOT NULL DEFAULT 1.0,
      `time` int(11) NOT NULL,
      PRIMARY KEY (`blueprintTypeID`,`productTypeID`,`activityID`),
      KEY `blueprintTypeID` (`blueprintTypeID`),
      KEY `productTypeID` (`productTypeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlBlueprintProducts);
    
    $createyamlBlueprintMaterials="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlBlueprintMaterials` (
      `blueprintTypeID` int(11) NOT NULL,
      `materialTypeID` int(11) NOT NULL,
      `quantity` int(11) NOT NULL,
      `activityID` int(11) NOT NULL,
      `consume` tinyint(5) NOT NULL,
      PRIMARY KEY (`blueprintTypeID`,`materialTypeID`,`activityID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlBlueprintMaterials);
    
    $createyamlBlueprintSkills="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlBlueprintSkills` (
      `blueprintTypeID` int(11) NOT NULL,
      `activityID` int(11) NOT NULL,
      `skillTypeID` int(11) NOT NULL,
      `level` tinyint(5) NOT NULL,
      PRIMARY KEY (`blueprintTypeID`,`activityID`,`skillTypeID`),
      KEY `blueprintTypeID` (`blueprintTypeID`),
      KEY `skillTypeID` (`skillTypeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlBlueprintSkills);
    
    if (!$silent) echo('loading YAML...');
    //switching from Spyc to YAML PECL module
    $blueprints = yaml_parse_wrapper($file);
    if ($blueprints===FALSE) die("yaml_parse_file failed for $file");
    
    if (!empty($blueprints)) {
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlBlueprintTypes`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlBlueprintProducts`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlBlueprintMaterials`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlBlueprintSkills`;");
    } else return false;
    $bigyamlBlueprintTypes="INSERT INTO `$LM_EVEDB`.`yamlBlueprintTypes` VALUES ";
    $bigyamlBlueprintProducts="INSERT INTO `$LM_EVEDB`.`yamlBlueprintProducts` VALUES ";
    $bigyamlBlueprintMaterials="INSERT INTO `$LM_EVEDB`.`yamlBlueprintMaterials` VALUES ";
    $bigyamlBlueprintSkills="INSERT INTO `$LM_EVEDB`.`yamlBlueprintSkills` VALUES ";
    
    foreach($blueprints as $blueprintTypeID => $blueprint) {
        //first, store the BPO typeID
        $blueprintTypeID=yaml_prepare($blueprint['blueprintTypeID']);
        $maxProductionLimit=yaml_prepare($blueprint['maxProductionLimit'],0);
        $bigyamlBlueprintTypes.="($blueprintTypeID, $maxProductionLimit),";
        //var_dump($blueprint);
        //second, walk activities
        foreach ($blueprint['activities'] as $activityID => $activity) {
            $time=yaml_prepare($activity['time'],0);
            //thirdly, walk products (if they exist)
            if (is_array($activity['products'])) {
                foreach ($activity['products'] as $productTypeID => $product) {
                    $quantity=yaml_prepare($product['quantity']);
                    $probability=yaml_prepare($product['probability'],'1.0');
                    $bigyamlBlueprintProducts.="($blueprintTypeID, $productTypeID, $activityID, $probability, $time),";
                }
            } else {
                //optionally I add copying, ME and TE research with productTypeID equal blueprintTypeID
                $bigyamlBlueprintProducts.="($blueprintTypeID, $blueprintTypeID, $activityID, 1.0, $time),";
            }
            //fourthly, walk materials
            if (is_array($activity['materials'])) {
                foreach ($activity['materials'] as $materialTypeID => $material) {
                    $quantity=yaml_prepare($material['quantity'],0);
                    $consume=yaml_prepare($material['consume'],1); if ($consume!=1) $consume=0;
                    $bigyamlBlueprintMaterials.="($blueprintTypeID, $materialTypeID, $quantity, $activityID, $consume),";
                }
            }
            //fifthly, walk skills
            if (is_array($activity['skills'])) {
                foreach ($activity['skills'] as $skillTypeID => $skill) {
                    $level=yaml_prepare($skill['level'],1);
                    $bigyamlBlueprintSkills.="($blueprintTypeID, $activityID, $skillTypeID, $level),";
                }
            }
        }
    }
    //trim last comma
    $bigyamlBlueprintTypes=rtrim($bigyamlBlueprintTypes,',').";";
    $bigyamlBlueprintProducts=rtrim($bigyamlBlueprintProducts,',').";";
    $bigyamlBlueprintMaterials=rtrim($bigyamlBlueprintMaterials,',').";";
    $bigyamlBlueprintSkills=rtrim($bigyamlBlueprintSkills,',').";";
    //exec queries
    if (!$silent) echo('insert to DB...');
    
    db_uquery($bigyamlBlueprintTypes);
    db_uquery($bigyamlBlueprintProducts);
    db_uquery($bigyamlBlueprintMaterials);
    db_uquery($bigyamlBlueprintSkills);
    
    return TRUE;
}
?>
