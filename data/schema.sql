-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 14 Mar 2014, 10:26
-- Wersja serwera: 5.1.66
-- Wersja PHP: 5.3.3-7+squeeze14

-- IMPORTANT:
--
-- Import this file before using LMeve. Remember to set the config options in /config/config.php
-- After setting up new SALT in config.php, generate admin password using /bin/passwd.php
-- set this password in 'lmusers' table for admin user
-- then you can login to application using admin/admin
-- password should be be changed in Settings later

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `lmeve`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apiaccountbalance`
--

CREATE TABLE IF NOT EXISTS `apiaccountbalance` (
  `accountID` bigint(11) NOT NULL,
  `accountKey` int(11) NOT NULL,
  `balance` decimal(20,2) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`accountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apiaccountbalance`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apiassets`
--

CREATE TABLE IF NOT EXISTS `apiassets` (
  `itemID` bigint(11) NOT NULL,
  `parentItemID` bigint(11) NOT NULL,
  `locationID` bigint(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `flag` tinyint(4) NOT NULL,
  `singleton` tinyint(4) NOT NULL,
  `rawQuantity` int(11) DEFAULT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`itemID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apiassets`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apiconquerablestationslist`
--

CREATE TABLE IF NOT EXISTS `apiconquerablestationslist` (
  `stationID` int(11) NOT NULL,
  `stationName` varchar(256) NOT NULL,
  `stationTypeID` int(11) NOT NULL,
  `solarSystemID` int(11) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  `corporationName` varchar(256) NOT NULL,
  PRIMARY KEY (`stationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apicontactlist`
--

CREATE TABLE IF NOT EXISTS `apicontactlist` (
  `contactID` bigint(11) NOT NULL,
  `contactName` varchar(128) NOT NULL,
  `standing` decimal(5,2) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`contactID`,`corporationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apicontactlist`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apicontainerlog`
--

CREATE TABLE IF NOT EXISTS `apicontainerlog` (
  `logTime` datetime NOT NULL,
  `itemID` bigint(11) NOT NULL,
  `itemTypeID` int(11) NOT NULL,
  `actorID` bigint(11) NOT NULL,
  `actorName` varchar(64) NOT NULL,
  `flag` int(11) NOT NULL,
  `locationID` bigint(11) NOT NULL,
  `action` varchar(128) NOT NULL,
  `passwordType` varchar(32) DEFAULT NULL,
  `typeID` varchar(32) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `oldConfiguration` varchar(256) DEFAULT NULL,
  `newConfiguration` varchar(256) DEFAULT NULL,
  `corporationID` bigint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apicontainerlog`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apicontractitems`
--

CREATE TABLE IF NOT EXISTS `apicontractitems` (
  `contractID` bigint(11) NOT NULL,
  `recordID` bigint(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `singleton` int(11) NOT NULL,
  `included` int(11) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`recordID`),
  KEY `contractID` (`contractID`),
  KEY `typeID` (`typeID`),
  KEY `corporationID` (`corporationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apicontractitems`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apicontracts`
--

CREATE TABLE IF NOT EXISTS `apicontracts` (
  `contractID` bigint(11) NOT NULL,
  `issuerID` bigint(11) NOT NULL,
  `issuerCorpID` bigint(11) NOT NULL,
  `assigneeID` bigint(11) NOT NULL,
  `acceptorID` bigint(11) NOT NULL,
  `startStationID` bigint(11) NOT NULL,
  `endStationID` bigint(11) NOT NULL,
  `type` varchar(32) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `forCorp` int(11) NOT NULL,
  `availability` varchar(32) DEFAULT NULL,
  `dateIssued` datetime NOT NULL,
  `dateExpired` datetime NOT NULL,
  `dateAccepted` datetime NOT NULL,
  `numDays` int(11) NOT NULL,
  `dateCompleted` datetime NOT NULL,
  `price` decimal(20,2) NOT NULL,
  `reward` decimal(20,2) NOT NULL,
  `collateral` decimal(20,2) NOT NULL,
  `buyout` decimal(20,2) NOT NULL,
  `volume` decimal(20,2) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`contractID`),
  KEY `issuerID` (`issuerID`),
  KEY `issuerCorpID` (`issuerCorpID`),
  KEY `assigneeID` (`assigneeID`),
  KEY `acceptorID` (`acceptorID`),
  KEY `corporationID` (`corporationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apicontracts`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apicorpmembers`
--

CREATE TABLE IF NOT EXISTS `apicorpmembers` (
  `characterID` bigint(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `startDateTime` datetime NOT NULL,
  `baseID` bigint(11) NOT NULL,
  `base` varchar(1024) DEFAULT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `corporationID` bigint(11) DEFAULT NULL,
  PRIMARY KEY (`characterID`),
  KEY `corporationID` (`corporationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apicorpmembers`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apicorps`
--

CREATE TABLE IF NOT EXISTS `apicorps` (
  `corporationID` int(11) NOT NULL,
  `corporationName` varchar(255) NOT NULL,
  `characterID` int(11) NOT NULL,
  `characterName` varchar(255) NOT NULL,
  `keyID` varchar(255) NOT NULL,
  PRIMARY KEY (`corporationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apicorps`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apicorpsheet`
--

CREATE TABLE IF NOT EXISTS `apicorpsheet` (
  `corporationID` int(11) NOT NULL,
  `corporationName` varchar(255) NOT NULL,
  `ticker` varchar(6) NOT NULL,
  `ceoID` int(11) NOT NULL,
  `ceoName` varchar(255) NOT NULL,
  `stationID` bigint(11) NOT NULL,
  `stationName` varchar(1024) NOT NULL,
  `description` varchar(2048) NOT NULL,
  `url` varchar(255) NOT NULL,
  `allianceID` int(11) NOT NULL,
  `taxRate` int(11) NOT NULL,
  `memberCount` int(11) NOT NULL,
  `memberLimit` int(11) NOT NULL,
  `shares` int(11) NOT NULL,
  `graphicId` int(11) NOT NULL,
  `shape1` int(11) NOT NULL,
  `shape2` int(11) NOT NULL,
  `shape3` int(11) NOT NULL,
  `color1` int(11) NOT NULL,
  `color2` int(11) NOT NULL,
  `color3` int(11) NOT NULL,
  PRIMARY KEY (`corporationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apicorpsheet`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apidivisions`
--

CREATE TABLE IF NOT EXISTS `apidivisions` (
  `corporationID` int(11) NOT NULL,
  `accountKey` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  KEY `corporationId` (`corporationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apidivisions`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apierrorlist`
--

CREATE TABLE IF NOT EXISTS `apierrorlist` (
  `errorCode` int(11) NOT NULL,
  `errorText` varchar(1024) NOT NULL,
  PRIMARY KEY (`errorCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apierrorlist`
--

INSERT INTO `apierrorlist` (`errorCode`, `errorText`) VALUES
(100, 'Expected before ref/trans ID = 0: wallet not previously loaded.'),
(101, 'Wallet exhausted: retry after {0}.'),
(102, 'Expected before ref/trans ID [{0}] but supplied [{1}]: wallet previously loaded.'),
(103, 'Already returned one week of data: retry after {0}.'),
(105, 'Invalid characterID.'),
(106, 'Must provide userID or keyID parameter for authentication.'),
(107, 'Invalid beforeRefID provided.'),
(108, 'Invalid accountKey provided.'),
(109, 'Invalid accountKey: must be in the range 1000 to 1006.'),
(110, 'Invalid beforeTransID provided.'),
(111, '''{0}'' is not a valid integer.'),
(112, 'Version mismatch.'),
(113, 'Version escalation is not allowed at this time.'),
(114, 'Invalid itemID provided.'),
(115, 'Assets already downloaded: retry after {0}.'),
(116, 'Industry jobs already downloaded: retry after {0}.'),
(117, 'Market orders already downloaded: retry after {0}.'),
(118, 'BeforeKillID was supplied but no Kill Log exists in cache or is expired. Please refresh Kill Log with no beforeKillID supplied to get the most recent kills.'),
(119, 'Kill log exhausted (You can only fetch kills that are less than a month old): New kills will be accessible at: {0}. If you are not expecting this message it is possible that some other application is using this key!'),
(120, 'Expected beforeKillID [{0}] but supplied [{1}]. Please supply the expected killID! If you are not expecting this message it is possible that some other application is using this key!'),
(121, 'Invalid beforeKillID provided.'),
(122, 'Invalid or missing list of names.'),
(123, 'Invalid or missing list of IDs.'),
(124, 'Character not enlisted in Factional Warfare.'),
(125, 'Corporation not enlisted in Factional Warfare.'),
(126, 'Invalid ID found in ID list. Please ensure input is a comma seperated list of valid 32-bit non-negative integers.'),
(127, 'Please supply valid eventIDs.'),
(128, 'IDs contained repeated instances of (at least) ID {0}. Please do not make redundant requests.'),
(129, 'Input may not exceed {0} IDs.'),
(130, 'All input must be valid ownerIDs or typeIDs.'),
(131, 'Calendar Event List not populated with upcoming events. You cannot request any random eventID.'),
(132, 'Calendar Event not found in upcoming events list. You cannot request any random eventID or CCP sponsored events.'),
(133, 'Calendar Event List of attendees currently not accessible for this event.'),
(134, 'Invalid or missing contractID.'),
(135, 'Owner is not the owner of all itemIDs or a non-existant itemID was passed in. If you are not trying to scrape the API, please ensure your input are valid locations associated with the key owner.'),
(200, 'Current security level not high enough.'),
(201, 'Character does not belong to account.'),
(202, 'API key authentication failure.'),
(203, 'Authentication failure.'),
(204, 'Authentication failure.'),
(205, 'Authentication failure (final pass).'),
(206, 'Character must have Accountant or Junior Accountant roles.'),
(207, 'Not available for NPC corporations.'),
(208, 'Character must have Accountant, Junior Accountant, or Trader roles.'),
(209, 'Character must be a Director or CEO.'),
(210, 'Authentication failure.'),
(211, 'Login denied by account status.'),
(212, 'Authentication failure (final pass).'),
(213, 'Character must have Factory Manager role.'),
(220, 'Invalid Corporation Key. Key owner does not fullfill role requirements anymore.'),
(221, 'Illegal page request! Please verify the access granted by the key you are using!'),
(222, 'Key has expired. Contact key owner for access renewal.'),
(223, 'Authentication failure. Legacy API keys can no longer be used. Please create a new key on support.eveonline.com and make sure your application supports Customizable API Keys.'),
(501, 'GetID({0}) is invalid or not loaded.'),
(503, 'GetSkillpointsForLevel({0}, {1}): invalid input.'),
(504, 'GetRace({0}): invalid race.'),
(505, 'GetGender({0}): invalid gender.'),
(506, 'GetBloodline({0}): invalid bloodline.'),
(507, 'GetAttributeName({0}): invalid attribute.'),
(508, 'GetRefType({0}): invalid reftype.'),
(509, 'attributeID {0} has null data components.'),
(510, 'Character does not appear to have a corporation.  Not loaded?'),
(511, 'AccountCanQuery({0}): invalid accountKey.'),
(512, 'Invalid charID passed to CharData.GetCharacter().'),
(513, 'Failed to get character roles in corporation.'),
(514, 'Invalid corpID passed to CorpData.GetCorporation().'),
(516, 'Failed getting user information.'),
(517, 'CSV header/row count mismatch.'),
(518, 'Unable to get current TQ time.'),
(519, 'Failed getting starbase detail information.'),
(520, 'Unexpected failure accessing database.'),
(521, 'Invalid username and/or password passed to UserData.LoginWebUser().'),
(522, 'Failed getting character information.'),
(523, 'Failed getting corporation information.'),
(531, 'Failed getting contract information.'),
(532, 'Failed getting market order information.'),
(901, 'Web site database temporarily disabled.'),
(902, 'EVE backend database temporarily disabled.'),
(903, 'Rate limited [{0}]: please obey all cachedUntil timers.'),
(904, 'Your IP address has been temporarily blocked because it is causing too many errors. See the cacheUntil timestamp for when it will be opened again. IPs that continually cause a lot of errors in the API will be permanently banned, please take measures to minimize problematic API calls from your application.'),
(999, 'User forced test error condition.'),
(1001, 'Cache is invalid');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apifacwarstats`
--

CREATE TABLE IF NOT EXISTS `apifacwarstats` (
  `factionID` int(11) NOT NULL,
  `factionName` varchar(64) NOT NULL,
  `enlisted` datetime NOT NULL,
  `pilots` int(11) NOT NULL,
  `killsYesterday` int(11) NOT NULL,
  `killsLastWeek` int(11) NOT NULL,
  `killsTotal` int(11) NOT NULL,
  `victoryPointsYesterday` int(11) NOT NULL,
  `victoryPointsLastWeek` int(11) NOT NULL,
  `victoryPointsTotal` int(11) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`corporationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apifacwarstats`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apiindustryjobs`
--

CREATE TABLE IF NOT EXISTS `apiindustryjobs` (
  `jobID` bigint(11) NOT NULL,
  `assemblyLineID` bigint(11) NOT NULL,
  `containerID` bigint(11) NOT NULL,
  `installedItemID` bigint(11) NOT NULL,
  `installedItemLocationID` bigint(11) NOT NULL,
  `installedItemQuantity` bigint(11) NOT NULL,
  `installedItemProductivityLevel` bigint(11) NOT NULL,
  `installedItemMaterialLevel` bigint(11) NOT NULL,
  `installedItemLicensedProductionRunsRemaining` bigint(11) NOT NULL,
  `outputLocationID` bigint(11) NOT NULL,
  `installerID` bigint(11) NOT NULL,
  `runs` int(11) NOT NULL,
  `licensedProductionRuns` int(11) NOT NULL,
  `installedInSolarSystemID` bigint(11) NOT NULL,
  `containerLocationID` bigint(11) NOT NULL,
  `materialMultiplier` decimal(16,15) NOT NULL,
  `charMaterialMultiplier` decimal(16,15) NOT NULL,
  `timeMultiplier` decimal(16,15) NOT NULL,
  `charTimeMultiplier` decimal(16,15) NOT NULL,
  `installedItemTypeID` int(11) NOT NULL,
  `outputTypeID` int(11) NOT NULL,
  `containerTypeID` int(11) NOT NULL,
  `installedItemCopy` int(11) NOT NULL,
  `completed` int(11) NOT NULL,
  `completedSuccessfully` int(11) NOT NULL,
  `successfulRuns` int(11) NULL,
  `installedItemFlag` int(11) NOT NULL,
  `outputFlag` int(11) NOT NULL,
  `activityID` int(11) NOT NULL,
  `completedStatus` int(11) NOT NULL,
  `installTime` datetime NOT NULL,
  `beginProductionTime` datetime NOT NULL,
  `endProductionTime` datetime NOT NULL,
  `pauseProductionTime` datetime NOT NULL,
  `corporationID` bigint(11) DEFAULT NULL,
  PRIMARY KEY (`jobID`),
  KEY `installedItemTypeID` (`installedItemTypeID`),
  KEY `outputTypeID` (`outputTypeID`),
  KEY `installerID` (`installerID`),
  KEY `endProductionTime` (`endProductionTime`),
  KEY `beginProductionTime` (`beginProductionTime`),
  KEY `installedItemID` (`installedItemID`),
  KEY `installTime` (`installTime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apiindustryjobs`
--

CREATE TABLE IF NOT EXISTS `apiindustryjobscrius` (
  `jobID` bigint(11) NOT NULL,
  `installerID` bigint(11) NOT NULL,
  `installerName` varchar(255) NOT NULL,
  `facilityID` bigint(11) NOT NULL,
  `solarSystemID` int(11) NOT NULL,
  `solarSystemName` varchar(255) NOT NULL,
  `stationID` bigint(11) NOT NULL,
  `activityID` int(11) NOT NULL,
  `blueprintID` bigint(11) NOT NULL,
  `blueprintTypeID` int(11) NOT NULL,
  `blueprintTypeName` varchar(255) NOT NULL,
  `blueprintLocationID` bigint(11) NOT NULL,
  `outputLocationID` bigint(11) NOT NULL,
  `runs` int(11) NOT NULL,
  `cost` decimal (20,2) NOT NULL,
  `teamID` bigint(11) NOT NULL,
  `licensedRuns` int(11) NOT NULL,
  `probability` decimal (20,2) NOT NULL,
  `productTypeID` int(11) NOT NULL,
  `productTypeName` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `timeInSeconds` int(11) NOT NULL,
  `startDate` datetime NOT NULL,
  `endDate` datetime NOT NULL,
  `pauseDate` datetime NOT NULL,
  `completedDate` datetime NOT NULL,
  `completedCharacterID` bigint(11) NOT NULL,
  `successfulRuns` int(11) NULL,
  `corporationID` bigint(11) DEFAULT NULL,
  PRIMARY KEY (`jobID`),
  KEY `installerID` (`installerID`),
  KEY `facilityID` (`facilityID`),
  KEY `solarSystemID` (`solarSystemID`),
  KEY `stationID` (`stationID`),
  KEY `blueprintTypeID` (`blueprintTypeID`),
  KEY `productTypeID` (`productTypeID`),
  KEY `startDate` (`startDate`),
  KEY `endDate` (`endDate`),
  KEY `completedDate` (`completedDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;				

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apilocations`
--
CREATE TABLE IF NOT EXISTS `apilocations` (
	`itemID` BIGINT(20) NOT NULL,
	`itemName` VARCHAR(256) NOT NULL,
	`x` DOUBLE NOT NULL,
	`y` DOUBLE NOT NULL,
	`z` DOUBLE NOT NULL,
	`corporationID` INT(11) NOT NULL,
	PRIMARY KEY (`itemID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

--
-- Zrzut danych tabeli `apilocations`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apimarketorders`
--

CREATE TABLE IF NOT EXISTS `apimarketorders` (
  `orderID` bigint(11) NOT NULL,
  `charID` bigint(11) NOT NULL,
  `stationID` int(11) NOT NULL,
  `volEntered` int(11) NOT NULL,
  `volRemaining` int(11) NOT NULL,
  `minVolume` int(11) NOT NULL,
  `orderState` int(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `range` int(11) NOT NULL,
  `accountKey` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `escrow` decimal(20,2) NOT NULL,
  `price` decimal(20,2) NOT NULL,
  `bid` int(11) NOT NULL,
  `issued` datetime NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`orderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apimarketorders`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apipollerstats`
--

CREATE TABLE IF NOT EXISTS `apipollerstats` (
  `statID` int(11) NOT NULL AUTO_INCREMENT,
  `statDateTime` datetime NOT NULL,
  `pollerSeconds` decimal(10,3) NOT NULL,
  PRIMARY KEY (`statID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apiprices`
--

CREATE TABLE IF NOT EXISTS `apiprices` (
  `typeID` int(11) NOT NULL,
  `volume` bigint(11) NOT NULL,
  `avg` decimal(20,2) NOT NULL,
  `max` decimal(20,2) NOT NULL,
  `min` decimal(20,2) NOT NULL,
  `stddev` decimal(20,2) NOT NULL,
  `median` decimal(20,2) NOT NULL,
  `percentile` decimal(20,2) DEFAULT NULL,
  `type` varchar(5) NOT NULL,
  KEY `typeID` (`typeID`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apireftypes`
--

CREATE TABLE IF NOT EXISTS `apireftypes` (
  `refTypeID` int(11) NOT NULL,
  `refTypeName` varchar(128) NOT NULL,
  PRIMARY KEY (`refTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apireftypes`
--

INSERT INTO `apireftypes` (`refTypeID`, `refTypeName`) VALUES
(0, 'Undefined'),
(1, 'Player Trading'),
(2, 'Market Transaction'),
(3, 'GM Cash Transfer'),
(4, 'ATM Withdraw'),
(5, 'ATM Deposit'),
(6, 'Backward Compatible'),
(7, 'Mission Reward'),
(8, 'Clone Activation'),
(9, 'Inheritance'),
(10, 'Player Donation'),
(11, 'Corporation Payment'),
(12, 'Docking Fee'),
(13, 'Office Rental Fee'),
(14, 'Factory Slot Rental Fee'),
(15, 'Repair Bill'),
(16, 'Bounty'),
(17, 'Bounty Prize'),
(18, 'Agents_temporary'),
(19, 'Insurance'),
(20, 'Mission Expiration'),
(21, 'Mission Completion'),
(22, 'Shares'),
(23, 'Courier Mission Escrow'),
(24, 'Mission Cost'),
(25, 'Agent Miscellaneous'),
(26, 'LP Store'),
(27, 'Agent Location Services'),
(28, 'Agent Donation'),
(29, 'Agent Security Services'),
(30, 'Agent Mission Collateral Paid'),
(31, 'Agent Mission Collateral Refunded'),
(32, 'Agents_preward'),
(33, 'Agent Mission Reward'),
(34, 'Agent Mission Time Bonus Reward'),
(35, 'CSPA'),
(36, 'CSPAOfflineRefund'),
(37, 'Corporation Account Withdrawal'),
(38, 'Corporation Dividend Payment'),
(39, 'Corporation Registration Fee'),
(40, 'Corporation Logo Change Cost'),
(41, 'Release Of Impounded Property'),
(42, 'Market Escrow'),
(43, 'Agent Services Rendered'),
(44, 'Market Fine Paid'),
(45, 'Corporation Liquidation'),
(46, 'Brokers Fee'),
(47, 'Corporation Bulk Payment'),
(48, 'Alliance Registration Fee'),
(49, 'War Fee'),
(50, 'Alliance Maintainance Fee'),
(51, 'Contraband Fine'),
(52, 'Clone Transfer'),
(53, 'Acceleration Gate Fee'),
(54, 'Transaction Tax'),
(55, 'Jump Clone Installation Fee'),
(56, 'Manufacturing'),
(57, 'Researching Technology'),
(58, 'Researching Time Productivity'),
(59, 'Researching Material Productivity'),
(60, 'Copying'),
(61, 'Duplicating'),
(62, 'Reverse Engineering'),
(63, 'Contract Auction Bid'),
(64, 'Contract Auction Bid Refund'),
(65, 'Contract Collateral'),
(66, 'Contract Reward Refund'),
(67, 'Contract Auction Sold'),
(68, 'Contract Reward'),
(69, 'Contract Collateral Refund'),
(70, 'Contract Collateral Payout'),
(71, 'Contract Price'),
(72, 'Contract Brokers Fee'),
(73, 'Contract Sales Tax'),
(74, 'Contract Deposit'),
(75, 'Contract Deposit Sales Tax'),
(76, 'Secure EVE Time Code Exchange'),
(77, 'Contract Auction Bid (corp)'),
(78, 'Contract Collateral Deposited (corp)'),
(79, 'Contract Price Payment (corp)'),
(80, 'Contract Brokers Fee (corp)'),
(81, 'Contract Deposit (corp)'),
(82, 'Contract Deposit Refund'),
(83, 'Contract Reward Deposited'),
(84, 'Contract Reward Deposited (corp)'),
(85, 'Bounty Prizes'),
(86, 'Advertisement Listing Fee'),
(87, 'Medal Creation'),
(88, 'Medal Issued'),
(89, 'Betting'),
(90, 'DNA Modification Fee'),
(91, 'Sovereignty bill'),
(92, 'Bounty Prize Corporation Tax'),
(93, 'Agent Mission Reward Corporation Tax'),
(94, 'Agent Mission Time Bonus Reward Corporation Tax'),
(95, 'Upkeep adjustment fee'),
(96, 'Planetary Import Tax'),
(97, 'Planetary Export Tax'),
(98, 'Planetary Construction'),
(99, 'Corporate Reward Payout'),
(101, 'Bounty Surcharge'),
(102, 'Contract Reversal'),
(103, 'Corporate Reward Tax'),
(106, 'Store Purchase'),
(107, 'Store Purchase Refund'),
(108, 'PLEX sold for Aurum'),
(109, 'Lottery Give Away'),
(111, 'Aurum Token exchanged for Aur'),
(112, 'Datacore Fee'),
(113, 'War Surrender Fee'),
(114, 'War Ally Contract'),
(115, 'Bounty Reimbursement'),
(116, 'Kill Right'),
(117, 'Fee for processing one or more security tags'),
(10001, 'Modify ISK'),
(10002, 'Primary Marketplace Purchase'),
(10003, 'Battle Reward'),
(10004, 'New Character Starting Funds'),
(10005, 'Corporation Account Withdrawal'),
(10006, 'Corporation Account Deposit'),
(10007, 'Battle WP Win Reward'),
(10008, 'Battle WP Loss Reward'),
(10009, 'Battle Win Reward'),
(10010, 'Battle Loss Reward'),
(10011, 'Unknown'),
(10012, 'District Contract Deposit'),
(10013, 'District Contract Deposit Refund'),
(10014, 'District Contract Collateral'),
(10015, 'District Contract Collateral Refund'),
(10016, 'District Contract Reward'),
(10017, 'District Clone Transportation'),
(10018, 'District Clone Transportation Refund'),
(10019, 'District Infrastructure'),
(10020, 'District Clone Sales'),
(10021, 'District Clone Purchase'),
(10022, 'Biomass Reward'),
(11001, 'Modify AUR'),
(11002, 'Respec payment'),
(11003, 'Unknown'),
(11004, 'Unknown'),
(11005, 'Unknown');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apistarbaselist`
--

CREATE TABLE IF NOT EXISTS `apistarbaselist` (
  `itemID` bigint(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `locationID` int(11) NOT NULL,
  `moonID` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `stateTimestamp` datetime NOT NULL,
  `onlineTimestamp` datetime NOT NULL,
  `standingOwnerID` int(11) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`itemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apistarbaselist`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apistatus`
--

CREATE TABLE IF NOT EXISTS `apistatus` (
  `errorID` int(11) NOT NULL AUTO_INCREMENT,
  `keyID` varchar(255) NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `errorCode` int(11) NOT NULL,
  `errorCount` int(11) NOT NULL DEFAULT '0',
  `errorMessage` varchar(1024) NOT NULL,
  PRIMARY KEY (`errorID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apiwalletdivisions`
--

CREATE TABLE IF NOT EXISTS `apiwalletdivisions` (
  `corporationID` int(11) NOT NULL,
  `accountKey` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  KEY `corporationId` (`corporationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apiwalletdivisions`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apiwalletjournal`
--

CREATE TABLE IF NOT EXISTS `apiwalletjournal` (
  `date` datetime NOT NULL,
  `refID` bigint(11) NOT NULL,
  `refTypeID` int(11) NOT NULL,
  `ownerName1` varchar(255) DEFAULT NULL,
  `ownerID1` int(11) NOT NULL,
  `ownerName2` varchar(255) DEFAULT NULL,
  `ownerID2` int(11) NOT NULL,
  `argName1` varchar(255) DEFAULT NULL,
  `argID1` int(11) NOT NULL,
  `amount` decimal(20,2) NOT NULL,
  `balance` decimal(20,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `corporationID` bigint(11) NOT NULL,
  `accountKey` int(11) NOT NULL DEFAULT '1000',
  PRIMARY KEY (`refID`),
  KEY `refTypeID` (`refTypeID`),
  KEY `corporationID` (`corporationID`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apiwalletjournal`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `apiwallettransactions`
--

CREATE TABLE IF NOT EXISTS `apiwallettransactions` (
  `transactionDateTime` datetime NOT NULL,
  `transactionID` bigint(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `typeName` varchar(255) DEFAULT NULL,
  `typeID` int(11) NOT NULL,
  `price` decimal(20,2) NOT NULL,
  `clientID` bigint(11) NOT NULL,
  `clientName` varchar(255) DEFAULT NULL,
  `characterID` bigint(11) NOT NULL,
  `characterName` varchar(255) DEFAULT NULL,
  `stationID` int(11) NOT NULL,
  `stationName` varchar(255) DEFAULT NULL,
  `transactionType` varchar(255) DEFAULT NULL,
  `transactionFor` varchar(255) DEFAULT NULL,
  `journalTransactionID` bigint(11) NOT NULL,
  `accountKey` int(11) NOT NULL,
  `corporationID` bigint(11) NOT NULL,
  PRIMARY KEY (`transactionID`),
  KEY `transactionDateTime` (`transactionDateTime`),
  KEY `typeID` (`typeID`),
  KEY `characterID` (`characterID`),
  KEY `corporationID` (`corporationID`),
  KEY `journalTransactionID` (`journalTransactionID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `apiwallettransactions`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `ccpwglmapping`
--

CREATE TABLE IF NOT EXISTS `ccpwglmapping` (
  `typeID` int(11) NOT NULL AUTO_INCREMENT,
  `shipModel` varchar(1024) NOT NULL,
  `background` varchar(1024) NOT NULL,
  `thrusters` varchar(1024) NOT NULL,
  PRIMARY KEY (`typeID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `cfgapikeys`
--

CREATE TABLE IF NOT EXISTS `cfgapikeys` (
  `apiKeyID` int(11) NOT NULL DEFAULT '0',
  `keyID` varchar(255) NOT NULL,
  `vCode` varchar(255) NOT NULL,
  PRIMARY KEY (`apiKeyID`),
  UNIQUE KEY `keyID` (`keyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `cfgapikeys`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `cfgbpo`
--

CREATE TABLE IF NOT EXISTS `cfgbpo` (
  `typeID` int(11) NOT NULL,
  `me` int(11) NOT NULL,
  `pe` int(11) NOT NULL,
  PRIMARY KEY (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `cfgbpo`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `cfgbuying`
--

CREATE TABLE IF NOT EXISTS `cfgbuying` (
  `typeID` int(11) NOT NULL,
  PRIMARY KEY (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `cfgbuying`
--

INSERT INTO `cfgbuying` (`typeID`) VALUES
(34),
(35),
(36),
(37),
(38),
(39),
(40),
(11399);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `cfgmarket`
--

CREATE TABLE IF NOT EXISTS `cfgmarket` (
  `typeID` int(11) NOT NULL,
  PRIMARY KEY (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `cfgmarket`
--

INSERT INTO `cfgmarket` (`typeID`) VALUES
(34),
(35),
(36),
(37),
(38),
(39),
(40),
(11399);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `cfgpoints`
--

CREATE TABLE IF NOT EXISTS `cfgpoints` (
  `activityID` int(11) NOT NULL,
  `hrsPerPoint` int(11) NOT NULL,
  PRIMARY KEY (`activityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `cfgpoints`
--

INSERT INTO `cfgpoints` (`activityID`, `hrsPerPoint`) VALUES
(1, 600),
(3, 2000),
(4, 2000),
(5, 1500),
(7, 300),
(8, 300);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `cfgstock`
--

CREATE TABLE IF NOT EXISTS `cfgstock` (
  `typeID` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `cfgstock`
--

INSERT INTO `cfgstock` (`typeID`, `amount`) VALUES
(34, 0),
(35, 0),
(36, 0),
(37, 0),
(38, 0),
(39, 0),
(40, 0),
(11399, 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `linki`
--

CREATE TABLE IF NOT EXISTS `linki` (
  `idLink` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(4096) COLLATE latin2_bin DEFAULT NULL,
  PRIMARY KEY (`idLink`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 COLLATE=latin2_bin AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `linki`
--

INSERT INTO `linki` (`idLink`, `link`) VALUES
(1, '&lt;h2&gt;&lt;u&gt;Links&lt;/u&gt;&lt;/h2&gt;\r\n\r\n&lt;script&gt;\r\n$(function() {\r\n    $( \\&quot;#accordion\\&quot; ).accordion({\r\n      heightStyle: \\&quot;content\\&quot;\r\n    });\r\n  });\r\n&lt;/script&gt;\r\n&lt;div id=\\&quot;accordion\\&quot;&gt;\r\n  &lt;h3&gt;&amp;#187; Aideron Technologies&lt;/h3&gt;\r\n  &lt;div&gt;\r\n&lt;ul&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;index.php?id=3&amp;id2=1\\&quot;&gt;Buy Calculator&lt;/a&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;index.php?id=10&amp;id2=7\\&quot;&gt;Ore Values&lt;/a&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;index.php?id=2&amp;id2=2\\&quot;&gt;Lab assignments&lt;/a&gt;&lt;/li&gt;\r\n&lt;!--&lt;li&gt;&lt;a href=\\&quot;https://docs.google.com/spreadsheet/ccc?key=0Atv4WV8DEJUPdENuZGxLd3E4NS1Hb3d1azRDakxVWlE#gid=4\\&quot;&gt;OLD GDOCS Buy calculator&lt;/a&gt;&lt;/li&gt;--&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;h3&gt;&amp;#187; Blogs by Aideron members&lt;/h3&gt;\r\n&lt;div&gt;\r\n&lt;ul&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;http://highdrag.wordpress.com/\\&quot;&gt;Highdrag Podcast&lt;/a&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;http://eve-prosper.blogspot.dk/\\&quot;&gt;Eve-Prosper&lt;/a&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;http://eve-x.blogspot.com/\\&quot;&gt;EVE-Xperience&lt;/a&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;http://www.ninveah.com/\\&quot;&gt;Inner Sanctum of Ninveah&lt;/a&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;http://ninveah.podbean.com/\\&quot;&gt;Broadcasts from the Ninveah&lt;/a&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;http://www.warpto0.blogspot.com\\&quot;&gt;Warp to Zero&lt;/a&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;a href=\\&quot;http://pozniak.pl/wp/\\&quot;&gt;Torchwood Archives&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmbuyback`
--

CREATE TABLE IF NOT EXISTS `lmbuyback` (
  `orderID` int(11) NOT NULL AUTO_INCREMENT,
  `orderSerialized` text,
  `timestmp` int(11) NOT NULL,
  `shortHash` varchar(256) DEFAULT NULL,
  `fullHash` varchar(256) DEFAULT NULL,
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`orderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `lmbuyback`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmchars`
--

CREATE TABLE IF NOT EXISTS `lmchars` (
  `charID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`charID`),
  KEY `userid` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `lmchars`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmlabs` (deprecated)
--

CREATE TABLE IF NOT EXISTS `lmlabs` (
  `structureID` int(11) NOT NULL AUTO_INCREMENT,
  `parentTowerID` bigint(20) DEFAULT NULL,
  `structureTypeID` int(11) NOT NULL,
  `structureName` varchar(48) NOT NULL,
  PRIMARY KEY (`structureID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `lmlabs`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmrights`
--

CREATE TABLE IF NOT EXISTS `lmrights` (
  `rightID` int(11) NOT NULL AUTO_INCREMENT,
  `rightName` varchar(256) NOT NULL,
  PRIMARY KEY (`rightID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=38 ;

--
-- Zrzut danych tabeli `lmrights`
--

INSERT INTO `lmrights` (`rightID`, `rightName`) VALUES
(1, 'Administrator'),
(2, 'ViewTimesheet'),
(3, 'ViewOverview'),
(4, 'ViewOwnTasks'),
(5, 'ViewAllTasks'),
(6, 'EditTasks'),
(7, 'ViewAPIStats'),
(8, 'ViewWallet'),
(9, 'ViewUsers'),
(10, 'EditUsers'),
(11, 'ViewMessages'),
(12, 'ViewInventory'),
(13, 'ViewMarket'),
(14, 'EditRoles'),
(15, 'ViewOwnCharacters'),
(16, 'ViewAllCharacters'),
(17, 'EditCharacters'),
(18, 'ViewDatabase'),
(19, 'EditPricesFlag'),
(20, 'EditBuyingFlag'),
(21, 'EditStock'),
(22, 'ViewBuyCalc'),
(23, 'ViewBuyOrders'),
(24, 'EditBuyOrders'),
(25, 'ViewSellOrders'),
(26, 'EditSellOrders'),
(27, 'EditMEPE'),
(28, 'ViewContracts'),
(29, 'EditHoursPerPoint'),
(30, 'ViewCurrentJobs'),
(31, 'ViewRealNames'),
(32, 'ViewOreValues'),
(33, 'ViewWiki'),
(34, 'EditWiki'),
(35, 'ViewPOS'),
(36, 'EditPOS'),
(37, 'ViewActivity');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmrolerights`
--

CREATE TABLE IF NOT EXISTS `lmrolerights` (
  `roleID` int(11) NOT NULL,
  `rightID` int(11) NOT NULL,
  UNIQUE KEY `roleID` (`roleID`,`rightID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `lmrolerights`
--

INSERT INTO `lmrolerights` (`roleID`, `rightID`) VALUES
(1, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 7),
(2, 8),
(2, 9),
(2, 11),
(2, 12),
(2, 13),
(2, 15),
(2, 16),
(2, 18),
(2, 19),
(2, 20),
(2, 22),
(2, 23),
(2, 24),
(2, 25),
(2, 27),
(2, 28),
(2, 30),
(2, 31),
(2, 33),
(2, 34),
(2, 35),
(2, 36),
(2, 37),
(3, 2),
(3, 3),
(3, 4),
(3, 7),
(3, 8),
(3, 11),
(3, 12),
(3, 13),
(3, 15),
(3, 18),
(3, 22),
(3, 30),
(3, 31),
(3, 32),
(3, 33),
(3, 35),
(3, 37),
(4, 4),
(4, 11),
(4, 15),
(4, 18),
(4, 22),
(5, 5),
(5, 7),
(5, 8),
(5, 12),
(5, 13),
(5, 16),
(5, 19),
(5, 22),
(5, 23),
(5, 27),
(5, 28),
(5, 35),
(5, 36),
(6, 18),
(7, 11),
(7, 18),
(7, 22),
(7, 32);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmroles`
--

CREATE TABLE IF NOT EXISTS `lmroles` (
  `roleID` int(11) NOT NULL AUTO_INCREMENT,
  `roleName` varchar(256) NOT NULL,
  PRIMARY KEY (`roleID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Zrzut danych tabeli `lmroles`
--

INSERT INTO `lmroles` (`roleID`, `roleName`) VALUES
(1, 'Administrator'),
(2, 'Officer'),
(3, 'Member'),
(4, 'Limited'),
(5, 'Logistics'),
(6, 'Database only'),
(7, 'Buy Calc Only');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmtasks`
--

CREATE TABLE IF NOT EXISTS `lmtasks` (
  `taskID` int(11) NOT NULL AUTO_INCREMENT,
  `characterID` int(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `activityID` int(11) NOT NULL,
  `runs` int(11) NOT NULL,
  `taskCreateTimestamp` datetime NOT NULL,
  `singleton` tinyint(3) NOT NULL,
  `structureID` bigint(11) DEFAULT NULL,
  PRIMARY KEY (`taskID`),
  KEY `characterID` (`characterID`),
  KEY `activityID` (`activityID`),
  KEY `typeID` (`typeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `lmtasks`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmuserapikeys`
--

CREATE TABLE IF NOT EXISTS `lmuserapikeys` (
  `apiKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `keyID` varchar(255) NOT NULL,
  `vCode` varchar(255) NOT NULL,
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`apiKeyID`),
  UNIQUE KEY `keyID` (`keyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `lmuserapikeys`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmuserroles`
--

CREATE TABLE IF NOT EXISTS `lmuserroles` (
  `userID` int(11) NOT NULL,
  `roleID` int(11) NOT NULL,
  UNIQUE KEY `roleID` (`userID`,`roleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `lmuserroles`
--

INSERT INTO `lmuserroles` (`userID`, `roleID`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `lmusers`
--

CREATE TABLE IF NOT EXISTS `lmusers` (
  `userID` int(8) NOT NULL,
  `login` varchar(24) CHARACTER SET latin2 NOT NULL,
  `pass` varchar(64) NOT NULL DEFAULT '',
  `lastip` varchar(16) CHARACTER SET latin2 DEFAULT '127.0.0.1',
  `last` varchar(24) CHARACTER SET latin2 DEFAULT NULL,
  `defaultPage` int(8) DEFAULT NULL,
  `css` varchar(50) CHARACTER SET latin2 DEFAULT NULL,
  `act` int(11) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `lmusers`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `message`
--

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `msgto` int(8) NOT NULL,
  `msgfrom` int(8) NOT NULL,
  `msgdate` varchar(24) DEFAULT NULL,
  `msgread` int(8) DEFAULT NULL,
  `msgtopic` varchar(128) DEFAULT NULL,
  `msg` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `message`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `message_sent`
--

CREATE TABLE IF NOT EXISTS `message_sent` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `msgto` int(8) NOT NULL,
  `msgfrom` int(8) NOT NULL,
  `msgdate` varchar(24) DEFAULT NULL,
  `msgread` int(8) DEFAULT NULL,
  `msgtopic` varchar(128) DEFAULT NULL,
  `msg` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `message_sent`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `wiki`
--

CREATE TABLE IF NOT EXISTS `wiki` (
  `idpage` int(11) NOT NULL AUTO_INCREMENT,
  `wikipage` varchar(32) NOT NULL,
  `contents` text,
  PRIMARY KEY (`idpage`),
  UNIQUE KEY `wikipage` (`wikipage`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `wiki`
--

INSERT INTO `wiki` (`idpage`, `wikipage`, `contents`) VALUES
(1, 'start', '=====Wiki start page=====\r\n\r\n===Fill your wiki with information!===\r\n\r\n* it allows bullet lists\r\n* it allows bullet lists\r\n* it allows bullet lists\r\n');

-- --------------------------------------------------------

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

CREATE TABLE IF NOT EXISTS `crestmarketprices` (
  `typeID` int(11) NOT NULL,
  `adjustedPrice` decimal(20,2) NOT NULL,
  `averagePrice` decimal(20,2) NOT NULL,
  PRIMARY KEY (`typeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;	

-- schema delta for EVE SSO functionality in release 0.1.47

CREATE TABLE IF NOT EXISTS `lmownerhash` (
  `characterID` bigint(11) NOT NULL,
  `ownerHash` varchar(255) NOT NULL,
  PRIMARY KEY (`characterID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- schema delta for db based configuration and keys for LMeve external JSON api

CREATE TABLE IF NOT EXISTS `lmconfig` (
  `itemLabel` varchar(64) NOT NULL,
  `itemValue` text NOT NULL,
  PRIMARY KEY (`itemLabel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lmnbapi` (
  `apiKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `apiKey` varchar(64) NOT NULL,
  `lastAccess` datetime NULL,
  `lastIP` varchar(32) NULL,
  PRIMARY KEY (`apiKeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

-- schema delta for page cache

CREATE TABLE IF NOT EXISTS `lmpagecache` (
  `pageLabel` varchar(32) NOT NULL,
  `pageContents` mediumtext NOT NULL,
  `timestamp` TIMESTAMP NOT NULL,
  PRIMARY KEY (`pageLabel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- function for PoCo planet finding

DROP FUNCTION IF EXISTS `findNearest`;

CREATE FUNCTION `findNearest`(`x1` DOUBLE, `y1` DOUBLE, `z1` DOUBLE, `solarSystemID1` INT)
RETURNS INT 
NOT DETERMINISTIC 
READS SQL DATA 
SQL SECURITY DEFINER
RETURN (
SELECT a.itemID FROM 
    (SELECT (POW(x1-x,2)+POW(y1-y,2)+POW(z1-z,2)) AS distance,itemID
    FROM mapDenormalize 
    WHERE `solarSystemID`=solarSystemID1
    ORDER BY distance ASC 
    LIMIT 1) a
);

-- function for PoCo last 30 days income

DROP FUNCTION IF EXISTS `thirtyDayIncome`;

CREATE FUNCTION `thirtyDayIncome`(`planetID` INT)
RETURNS DOUBLE 
NOT DETERMINISTIC 
READS SQL DATA 
SQL SECURITY DEFINER
RETURN (
SELECT SUM(awj.amount) AS amount FROM
apiwalletjournal awj
WHERE
awj.`argID1`=`planetID`
AND awj.`refTypeID` IN (96, 97)
AND awj.`date` BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()
);

-- apipocolist - table for pocos

CREATE TABLE IF NOT EXISTS `apipocolist` (
  `itemID` bigint(20) NOT NULL,
  `solarSystemID` int(11) NOT NULL,
  `solarSystemName` varchar(256) NOT NULL,
  `reinforceHour` int(11) NOT NULL,
  `allowAlliance` int(11) NOT NULL,
  `allowStandings` int(11) NOT NULL,
  `standingLevel` int(11) NOT NULL,
  `taxRateAlliance` float NOT NULL,
  `taxRateCorp` float NOT NULL,
  `taxRateStandingHigh` float NOT NULL,
  `taxRateStandingGood` float NOT NULL,
  `taxRateStandingNeutral` float NOT NULL,
  `taxRateStandingBad` float NOT NULL,
  `taxRateStandingHorrible` float NOT NULL,
  `corporationID` int(11) NOT NULL,
  PRIMARY KEY (`itemID`),
  KEY `itemID` (`itemID`),
  KEY `solarSystemID` (`solarSystemID`),
  KEY `corporationID` (`corporationID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- schema delta for northbound api

-- (change included in table definition)

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
  `costIndex` decimal(20,4) NOT NULL,
  `activityID` int(11) NOT NULL,
  PRIMARY KEY (`solarSystemID`,`activityID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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