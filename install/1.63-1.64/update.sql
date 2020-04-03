UPDATE `wD_Misc` SET `value` = '164' WHERE `name` = 'Version';

CREATE TABLE `wD_EmailHistory` (
  `userID` mediumint(8) NOT NULL,
  `oldEmail` varchar(90) NOT NULL,
  `newEmail` varchar(90) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `reason` varchar(50) NOT NULL,
  `changedBy` varchar(30) NOT NULL
);