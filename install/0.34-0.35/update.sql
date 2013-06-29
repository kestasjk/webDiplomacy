CREATE TABLE `wD_VariantData` (
  `variantID` tinyint(3) unsigned NOT NULL,
  `gameID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `systemToken` int(10) unsigned NOT NULL DEFAULT '0',
  `typeID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `offset` int(10) unsigned NOT NULL DEFAULT '0',
  `val_int` int(11) NOT NULL DEFAULT '0',
  `val_float` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `wD_VariantData` ADD PRIMARY KEY ( `variantID` , `systemToken` , `typeID` , `userID` , `offset` ) ;

INSERT INTO wD_VariantData (variantID, systemToken, userID, offset, val_float )
SELECT 1, 948379409, u.id, 1, ChanceEngland
FROM wD_Users u
WHERE NOT ChanceEngland = 0.142857
UNION SELECT 1, 948379409, u.id, 2, ChanceFrance
FROM wD_Users u
WHERE NOT ChanceFrance = 0.142857
UNION SELECT 1, 948379409, u.id, 3, ChanceItaly
FROM wD_Users u
WHERE NOT ChanceItaly = 0.142857
UNION SELECT 1, 948379409, u.id, 4, ChanceGermany
FROM wD_Users u
WHERE NOT ChanceGermany = 0.142857
UNION SELECT 1, 948379409, u.id, 5, ChanceAustria
FROM wD_Users u
WHERE NOT ChanceAustria = 0.142857
UNION SELECT 1, 948379409, u.id, 6, ChanceRussia
FROM wD_Users u
WHERE NOT ChanceRussia = 0.142857
UNION SELECT 1, 948379409, u.id, 7, ChanceTurkey
FROM wD_Users u
WHERE NOT ChanceTurkey = 0.142857;


UPDATE `wD_Misc` SET `value` = '135' WHERE `name` = 'Version';