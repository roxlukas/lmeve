-- schema delta for killmails

CREATE TABLE IF NOT EXISTS `apikills` (
  `killID` int(11) not null,
  `solarSystemID` int(11) NOT NULL,
  `killTime` datetime NULL,
  `moonID` int(11) NOT NULL,
  PRIMARY KEY (`killID`),
  KEY `apikills_IX_solarSystemID` (`solarSystemID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `apikillvictims` (
  `killID` int(11) not null,
  `characterID` int(11) not null,
  `characterName` varchar(255) not null,
  `corporationID` int(11) null,
  `corporationName` varchar(255) null,
  `allianceID` int(11) null,
  `allianceName` varchar(255) null,
  `factionID` int(11) null,
  `factionName` varchar(255) null,
  `damageTaken` int(11) not null,
  `shipTypeID` int(11) not null,
  KEY `apikillvictims_IX_killID` (`killID`),
  KEY `apikillvictims_IX_characterID` (`characterID`),
  KEY `apikillvictims_IX_corporationID` (`corporationID`),
  KEY `apikillvictims_IX_allianceID` (`allianceID`),
  KEY `apikillvictims_IX_factionID` (`factionID`),
  KEY `apikillvictims_IX_shipTypeID` (`shipTypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `apikillattackers` (
  `killID` int(11) not null,
  `characterID` int(11) not null,
  `characterName` varchar(255) not null,
  `corporationID` int(11) null,
  `corporationName` varchar(255) null,
  `allianceID` int(11) null,
  `allianceName` varchar(255) null,
  `factionID` int(11) null,
  `factionName` varchar(255) null,
  `securityStatus` decimal(15,2) not null,
  `damageDone` int(11) not null,
  `finalBlow` int(11) not null,
  `weaponTypeID` int(11) not null,
  `shipTypeID` int(11) not null,
  KEY `apikillattackers_IX_killID` (`killID`),
  KEY `apikillattackers_IX_characterID` (`characterID`),
  KEY `apikillattackers_IX_corporationID` (`corporationID`),
  KEY `apikillattackers_IX_allianceID` (`allianceID`),
  KEY `apikillattackers_IX_factionID` (`factionID`),
  KEY `apikillattackers_IX_weaponTypeID` (`shipTypeID`),
  KEY `apikillattackers_IX_shipTypeID` (`shipTypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `apikillitems` (
  `killID` int(11) not null,
  `typeID` int(11) not null,
  `flag` int(11) not null,
  `qtyDropped` int(11) null,
  `qtyDestroyed` int(11) null,
  `singleton` int(11) not null,
  KEY `apikillitems_IX_killID` (`killID`),
  KEY `apikillitems_IX_typeID` (`typeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE  `lmusers` CHANGE  `pass`  `pass` VARCHAR( 64 );