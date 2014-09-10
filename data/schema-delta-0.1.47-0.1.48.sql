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