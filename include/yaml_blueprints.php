<?php

//YAML - blueprint related functions
include_once('spyc/Spyc.php');
include_once('yaml_common.php');

function updateYamlBlueprints() {
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
    
    $blueprints = Spyc::YAMLLoad($file);
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
    db_uquery($bigyamlBlueprintTypes);
    db_uquery($bigyamlBlueprintProducts);
    db_uquery($bigyamlBlueprintMaterials);
    db_uquery($bigyamlBlueprintSkills);
    
    return TRUE;
}
/*

28662:
  activities:
    1:
      materials:
        641:
          quantity: 1
        3828:
          quantity: 450
        11399:
          quantity: 975
        11478:
          quantity: 30
        11531:
          quantity: 375
        11535:
          quantity: 863
        11541:
          quantity: 6000
        11545:
          quantity: 37500
        11547:
          quantity: 225
        11553:
          quantity: 3000
        11556:
          quantity: 3795
      products:
        28661:
          quantity: 1
      skills:
        3380:
          level: 5
        3398:
          level: 4
        11450:
          level: 4
        11452:
          level: 4
      time: 360000
    3:
      materials:
        3814:
          quantity: 60
        9836:
          quantity: 50
        11467:
          quantity: 15
      skills:
        3403:
          level: 5
        11450:
          level: 5
        11452:
          level: 5
      time: 126000
    4:
      materials:
        3814:
          quantity: 70
        9836:
          quantity: 50
        11467:
          quantity: 15
      skills:
        3409:
          level: 5
        11450:
          level: 5
        11452:
          level: 5
      time: 126000
    5:
      materials:
        3812:
          quantity: 300
        11467:
          quantity: 5
      skills:
        11450:
          level: 5
        11452:
          level: 5
      time: 288000
  blueprintTypeID: 28662
  maxProductionLimit: 1
 * 
 * 
 * 
 array(3) {
  ["activities"]=>
  array(4) {
    [1]=>
    array(3) {
      ["materials"]=>
      array(1) {
        [38]=>
        array(1) {
          ["quantity"]=>
          int(86)
        }
      }
      ["products"]=>
      array(1) {
        [165]=>
        array(1) {
          ["quantity"]=>
          int(1)
        }
      }
      ["time"]=>
      int(600)
    }
    [3]=>
    array(1) {
      ["time"]=>
      int(210)
    }
    [4]=>
    array(1) {
      ["time"]=>
      int(210)
    }
    [5]=>
    array(1) {
      ["time"]=>
      int(480)
    }
  }
  ["blueprintTypeID"]=>
  int(681)
  ["maxProductionLimit"]=>
  int(300)
}
 */

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
    
    $insertinvBlueprintTypes="INSERT INTO `$LM_EVEDB`.`invBlueprintTypes`
    SELECT DISTINCT
    ybt.`blueprintTypeID`,
    NULL AS `parentBlueprintTypeID`,
    manu.`productTypeID`,
    manu.`time` AS `productionTime`,
    imt.`metaGroupID` AS `techLevel`,
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
    
    $insertramTypeRequirements="INSERT INTO `$LM_EVEDB`.`ramTypeRequirements`      
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
?>
