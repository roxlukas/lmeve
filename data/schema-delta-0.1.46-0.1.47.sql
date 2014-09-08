--schema delta for EVE SSO functionality in release 0.1.47

CREATE TABLE IF NOT EXISTS `lmownerhash` (
  `characterID` bigint(11) NOT NULL,
  `ownerHash` varchar(255) NOT NULL,
  PRIMARY KEY (`characterID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;