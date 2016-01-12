-- function for PoCo planet finding - fix in 0.1.53 - sometimes returned asteroid belt or moon instead of planet - groupID=7 limits query to planets only

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
    AND groupID=7
    ORDER BY distance ASC 
    LIMIT 1) a
);
