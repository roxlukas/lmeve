INSERT IGNORE INTO `lmeve`.`cfgmarket`
SELECT imt.`typeID` FROM `eve_hyp100_dbo`.`invMetaTypes` imt
JOIN `eve_hyp100_dbo`.`invTypes` itp
ON imt.`typeID`=itp.`typeID`
WHERE itp.`published`=1
AND imt.`metaGroupID`=2;