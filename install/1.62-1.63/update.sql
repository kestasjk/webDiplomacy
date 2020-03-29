UPDATE `wD_Misc` SET `value` = '163' WHERE `name` = 'Version';

CREATE TABLE `wD_UsernameHistory` (
  `userID` mediumint(8) NOT NULL,
  `oldUsername` varchar(30) NOT NULL,
  `newUsername` varchar(30) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `reason` varchar(50) NOT NULL,
  `changedBy` varchar(30) NOT NULL
);
