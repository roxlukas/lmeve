-- performance fix for Inventory & Buy Calculator

ALTER TABLE `apiprices` ADD INDEX  `type` (  `type` );
ALTER TABLE `apiprices` ADD INDEX  `typeID` (  `typeID` );
