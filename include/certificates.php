<?php
//YAML - graphics related functions
include_once('spyc/Spyc.php');

function updateYamlCertificates() {
    /*
    CREATE TABLE `yamlcrtcertificates` (
      `certificateID` int(11) NOT NULL,
      `description` varchar(4096) DEFAULT NULL,
      `groupID` int(11) NOT NULL,
      `name` varchar(256) DEFAULT NULL,
      PRIMARY KEY (`certificateID`),
      KEY `groupID` (`groupID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

    CREATE TABLE `yamlcrtmasteries` (
      `skillTypeID` int(11) NOT NULL,
      `certificateID` int(11) NOT NULL,
      `crtMasteryLevel` int(11) NOT NULL,
      `skillLevelReq` int(11) NOT NULL,
      KEY `skillTypeID` (`skillTypeID`, `certificateID`),
      KEY `crtMasteryLevel` (`crtMasteryLevel`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

    CREATE TABLE `yamlcrtmasterylevels` (
      `crtMasteryLevel` int(11) NOT NULL,
      `masteryLevel` varchar(16) NOT NULL,
      PRIMARY KEY (`crtMasteryLevel`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

    CREATE TABLE `yamlcrtrecommendations` (
      `certificateID` int(11) NOT NULL,
      `typeID` int(11) NOT NULL,
      KEY `certificateID` (`certificateID`,`typeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

    CREATE TABLE `yamlcrtskills` (
      `skillTypeID` int(11) NOT NULL,
      `certificateID` int(11) NOT NULL,
      KEY `certificateID` (`certificateID`),
      KEY `skillTypeID` (`skillTypeID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    */
    global $LM_EVEDB;
    $yamlcrtmasterylevels['basic']=1;
    $yamlcrtmasterylevels['standard']=2;
    $yamlcrtmasterylevels['improved']=3;
    $yamlcrtmasterylevels['advanced']=4;
    $yamlcrtmasterylevels['elite']=5;
    
    //there is a bug in SPYC that incorrectly loads descriptions. Workaround:
    // instead of: $certificates = Spyc::YAMLLoad("../data/$LM_EVEDB/certificates.yaml");
    $certificatesraw=file_get_contents("../data/$LM_EVEDB/certificates.yaml");
    $certificatesraw=str_replace('description: ', "description: ingore\r\n        ", $certificatesraw);
    //another bug in "reccommended for" parsing. workaround:
    $certificatesraw=str_replace('    - ', "        - ", $certificatesraw); 
    $certificates = Spyc::YAMLLoadString($certificatesraw);
    
    //if data loaded correctly, prepare tables by clearing them
    if (!empty($certificates)) {
        db_uquery("TRUNCATE TABLE `yamlcrtcertificates`;");
        db_uquery("TRUNCATE TABLE `yamlcrtmasteries`;");
        db_uquery("TRUNCATE TABLE `yamlcrtrecommendations`;");
        db_uquery("TRUNCATE TABLE `yamlcrtskills`;");
        db_uquery("TRUNCATE TABLE `yamlcrtmasterylevels`;");
    } else return false;
    
    //create mastery Levels table
    foreach($yamlcrtmasterylevels as $masteryLevel => $crtMasteryLevel) {
        db_uquery("INSERT INTO `yamlcrtmasterylevels` VALUES ($crtMasteryLevel, '$masteryLevel');");
    }
    
    //prepare big inserts
    $yamlcrtcertificatesinsert="INSERT INTO `yamlcrtcertificates` VALUES ";
    $yamlcrtrecommendationsinsert="INSERT INTO `yamlcrtrecommendations` VALUES ";
    $yamlcrtskillsinsert="INSERT INTO `yamlcrtskills` VALUES ";
    $yamlcrtskillsinsert="INSERT INTO `yamlcrtskills` VALUES ";
    $yamlcrtmasteriesinsert="INSERT INTO `yamlcrtmasteries` VALUES ";
    
    //DEBUG: array dump
    //echo('<pre>');
    //print_r($certificates);
    //echo('</pre>');
    
    //certificates loop
    foreach($certificates as $certificateID => $row) {
        $description=addslashes(implode(' ',$row['description']));
        $name=addslashes($row['name']);
        $yamlcrtcertificatesinsert.="($certificateID, '$description', ${row['groupID']}, '$name'),";
        //recommendations loop
        if (!empty($row['recommendedFor'])) {
            foreach($row['recommendedFor'] as $recommendation) {
                $yamlcrtrecommendationsinsert.="($certificateID, $recommendation),";
            }
        }
        //skills loop
        if (!empty($row['skillTypes'])) {
            foreach($row['skillTypes'] as $skillTypeID => $skillrow) {
                $yamlcrtskillsinsert.="($skillTypeID, $certificateID),";
                //masteries loop
                foreach($skillrow as $masteryLevel => $masteryrow) {
                    $crtMasteryLevel=$yamlcrtmasterylevels[$masteryLevel];
                    $skillLevelReq=$masteryrow;
                    $yamlcrtmasteriesinsert.="($skillTypeID, $certificateID, $crtMasteryLevel, $skillLevelReq),";
                }
            }
        }
    }
    
    //finish big inserts
    $yamlcrtcertificatesinsert=rtrim($yamlcrtcertificatesinsert,',').";";
    $yamlcrtrecommendationsinsert=rtrim($yamlcrtrecommendationsinsert,',').";";
    $yamlcrtskillsinsert=rtrim($yamlcrtskillsinsert,',').";";
    $yamlcrtmasteriesinsert=rtrim($yamlcrtmasteriesinsert,',').";";
    
    //execute big inserts
    db_uquery($yamlcrtcertificatesinsert);
    db_uquery($yamlcrtrecommendationsinsert);
    db_uquery($yamlcrtskillsinsert);
    db_uquery($yamlcrtmasteriesinsert);
    return true;
}
?>
