--schema delta for page cache

CREATE TABLE IF NOT EXISTS `lmpagecache` (
  `pageLabel` varchar(32) NOT NULL,
  `pageContents` mediumtext NULL,
  `timestamp` TIMESTAMP NOT NULL,
  PRIMARY KEY (`pageLabel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--function for PoCo planet finding

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

--function for PoCo last 30 days income

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
