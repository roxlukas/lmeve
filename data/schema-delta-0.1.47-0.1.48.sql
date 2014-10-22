--schema delta for db based configuration and keys for LMeve external JSON api

CREATE TABLE IF NOT EXISTS `lmconfig` (
  `itemLabel` varchar(64) NOT NULL,
  `itemValue` text NOT NULL,
  PRIMARY KEY (`itemLabel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lmnbapi` (
  `apiKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `apiKey` varchar(64) NOT NULL,
  `lastAccess` datetime NULL,
  `lastIP` varchar(32) NULL,
  PRIMARY KEY (`apiKeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--itemID,locationID,typeID,typeName,flagID,quantity,timeEfficiency,materialEfficiency,runs

CREATE TABLE IF NOT EXISTS `apiblueprints` (
  `itemID` bigint(11) NOT NULL,
  `locationID` bigint(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `typeName` varchar(256) NULL,
  `flagID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `timeEfficiency` int(11) NOT NULL,
  `materialEfficiency` int(11) NOT NULL,
  `runs` int(11) NOT NULL,
  `corporationID` int(11) NOT NULL,
  PRIMARY KEY (`itemID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;