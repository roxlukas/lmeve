-- schema delta for starbase detail, starbase fuel and crest industry system index

CREATE TABLE IF NOT EXISTS `apistarbasedetail` (
  `itemID` bigint(11) NOT NULL,
  `state` int(11) NOT NULL,
  `stateTimestamp` datetime NOT NULL,
  `onlineTimestamp` datetime NOT NULL,
  `usageFlags` int(11) NOT NULL,
  `deployFlags` int(11) NOT NULL,
  `allowCorporationMembers` int(11) NOT NULL,
  `allowAllianceMembers` int(11) NOT NULL,
  `useStandingsFrom` int(11) NOT NULL,
  `onStandingDrop` int(11) NOT NULL,
  `onStatusDrop` int(11) NOT NULL,
  `onStatusDropStanding` int(11) NOT NULL,
  `onAggression` int(11) NOT NULL,
  `onCorporationWar` int(11) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`itemID`),
  KEY `corporationID` (`corporationID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `apistarbasefuel` (
  `itemID` bigint(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `quantity` bigint(11) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`itemID`,`typeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `crestindustrysystems` (
  `solarSystemID` bigint(11) NOT NULL,
  `costIndex` decimal(20,2) NOT NULL,
  `activityID` int(11) NOT NULL,
  PRIMARY KEY (`solarSystemID`,`activityID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;