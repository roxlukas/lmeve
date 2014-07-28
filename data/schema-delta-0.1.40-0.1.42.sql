--facilityID,typeID,typeName,solarSystemID,solarSystemName,regionID,regionName,starbaseModifier,tax,corporationID

CREATE TABLE IF NOT EXISTS `apifacilities` (
  `facilityID` bigint(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `typeName` varchar(255) NOT NULL,
  `solarSystemID` int(11) NOT NULL,
  `solarSystemName` varchar(255) NOT NULL,
  `regionID` int(11) NOT NULL,
  `regionName` varchar(255) NOT NULL,
  `starbaseModifier` decimal(20,2) NOT NULL,
  `tax` decimal(20,2) NOT NULL,
  `corporationID` int(11) NOT NULL,
  PRIMARY KEY (`facilityID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;	

ALTER TABLE  `lmtasks` CHANGE  `structureID`  `structureID` BIGINT( 11 ) NULL DEFAULT NULL;