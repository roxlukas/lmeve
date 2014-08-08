--typeID, adjustedPrice, averagePrice

CREATE TABLE IF NOT EXISTS `crestmarketprices` (
  `typeID` int(11) NOT NULL,
  `adjustedPrice` decimal(20,2) NOT NULL,
  `averagePrice` decimal(20,2) NOT NULL,
  PRIMARY KEY (`typeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;	

