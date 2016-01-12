<?php
//YAML - graphics related functions
//include_once('spyc/Spyc.php');
include_once('yaml_common.php');

function updateYamlCertificates($silent=true) {
    global $LM_EVEDB;
    $file="../data/$LM_EVEDB/certificates.yaml";
    
    if (!file_exists($file)) {
        echo("File $file does not exist. Make sure YAML files from EVE SDE are in appropriate directories.");
        return FALSE;
    }
    
    $createyamlCrtCertificates="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlCrtCertificates` (
      `certificateID` int(11) NOT NULL,
      `description` varchar(4096) DEFAULT NULL,
      `groupID` int(11) NOT NULL,
      `name` varchar(256) DEFAULT NULL,
      PRIMARY KEY (`certificateID`),
      KEY `groupID` (`groupID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlCrtCertificates);

    $createyamlCrtMasteries="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlCrtMasteries` (
      `skillTypeID` int(11) NOT NULL,
      `certificateID` int(11) NOT NULL,
      `crtMasteryLevel` int(11) NOT NULL,
      `skillLevelReq` int(11) NOT NULL,
      KEY `skillTypeID` (`skillTypeID`, `certificateID`),
      KEY `crtMasteryLevel` (`crtMasteryLevel`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlCrtMasteries);

    $createyamlCrtMasteryLevels="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlCrtMasteryLevels` (
      `crtMasteryLevel` int(11) NOT NULL,
      `masteryLevel` varchar(16) NOT NULL,
      PRIMARY KEY (`crtMasteryLevel`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlCrtMasteryLevels);

    $createyamlCrtRecommendations="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlCrtRecommendations` (
      `certificateID` int(11) NOT NULL,
      `typeID` int(11) NOT NULL,
      KEY `certificateID` (`certificateID`,`typeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlCrtRecommendations);

    $createyamlCrtSkills="CREATE TABLE IF NOT EXISTS `$LM_EVEDB`.`yamlCrtSkills` (
      `skillTypeID` int(11) NOT NULL,
      `certificateID` int(11) NOT NULL,
      KEY `certificateID` (`certificateID`),
      KEY `skillTypeID` (`skillTypeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    db_uquery($createyamlCrtSkills);
    
    $yamlCrtMasteryLevels['basic']=1;
    $yamlCrtMasteryLevels['standard']=2;
    $yamlCrtMasteryLevels['improved']=3;
    $yamlCrtMasteryLevels['advanced']=4;
    $yamlCrtMasteryLevels['elite']=5;
    
    //switching from Spyc to YAML PECL module
    $certificates = yaml_parse_wrapper($file);
    
    //if data loaded correctly, prepare tables by clearing them
    if (!empty($certificates)) {
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlCrtCertificates`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlCrtMasteries`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlCrtRecommendations`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlCrtSkills`;");
        db_uquery("TRUNCATE TABLE `$LM_EVEDB`.`yamlCrtMasteryLevels`;");
    } else return false;
    
    //create mastery Levels table
    foreach($yamlCrtMasteryLevels as $masteryLevel => $crtMasteryLevel) {
        db_uquery("INSERT INTO `$LM_EVEDB`.`yamlCrtMasteryLevels` VALUES ($crtMasteryLevel, '$masteryLevel');");
    }
    
    //prepare big inserts
    $yamlCrtCertificatesinsert="INSERT INTO `$LM_EVEDB`.`yamlCrtCertificates` VALUES ";
    $yamlCrtRecommendationsinsert="INSERT INTO `$LM_EVEDB`.`yamlCrtRecommendations` VALUES ";
    $yamlCrtSkillsinsert="INSERT INTO `$LM_EVEDB`.`yamlCrtSkills` VALUES ";
    $yamlCrtSkillsinsert="INSERT INTO `$LM_EVEDB`.`yamlCrtSkills` VALUES ";
    $yamlCrtMasteriesinsert="INSERT INTO `$LM_EVEDB`.`yamlCrtMasteries` VALUES ";
    
    //DEBUG: array dump
    //echo('<pre>');
    //print_r($certificates);
    //echo('</pre>');
    
    //certificates loop
    foreach($certificates as $certificateID => $row) {
        $description=addslashes($row['description']);
        $name=addslashes($row['name']);
        $yamlCrtCertificatesinsert.="($certificateID, '$description', ${row['groupID']}, '$name'),";
        //recommendations loop
        if (!empty($row['recommendedFor'])) {
            foreach($row['recommendedFor'] as $recommendation) {
                $yamlCrtRecommendationsinsert.="($certificateID, $recommendation),";
            }
        }
        //skills loop
        if (!empty($row['skillTypes'])) {
            foreach($row['skillTypes'] as $skillTypeID => $skillrow) {
                $yamlCrtSkillsinsert.="($skillTypeID, $certificateID),";
                //masteries loop
                foreach($skillrow as $masteryLevel => $masteryrow) {
                    $crtMasteryLevel=$yamlCrtMasteryLevels[$masteryLevel];
                    $skillLevelReq=$masteryrow;
                    $yamlCrtMasteriesinsert.="($skillTypeID, $certificateID, $crtMasteryLevel, $skillLevelReq),";
                }
            }
        }
    }
    
    //finish big inserts
    $yamlCrtCertificatesinsert=rtrim($yamlCrtCertificatesinsert,',').";";
    $yamlCrtRecommendationsinsert=rtrim($yamlCrtRecommendationsinsert,',').";";
    $yamlCrtSkillsinsert=rtrim($yamlCrtSkillsinsert,',').";";
    $yamlCrtMasteriesinsert=rtrim($yamlCrtMasteriesinsert,',').";";
    
    //execute big inserts
    if (!$silent) echo('insert to DB...');
    
    db_uquery($yamlCrtCertificatesinsert);
    db_uquery($yamlCrtRecommendationsinsert);
    db_uquery($yamlCrtSkillsinsert);
    db_uquery($yamlCrtMasteriesinsert);
    return true;
}
?>
