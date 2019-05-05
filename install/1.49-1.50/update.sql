UPDATE `wD_Misc` SET `value` = '150' WHERE `name` = 'Version';
CREATE TABLE IF NOT EXISTS `wD_MissedTurns` (
	  `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	  `gameID` mediumint(5) unsigned NOT NULL,
	  `userID` mediumint(8) unsigned NOT NULL,
	  `countryID` tinyint(3) unsigned NOT NULL,
	  `turn` smallint(5) unsigned NOT NULL,
	  `bet` smallint(5) unsigned NOT NULL,
	  `SCCount` smallint(5) unsigned NOT NULL,
	  `forcedByMod` BOOLEAN DEFAULT 0,
	  `systemExcused` BOOLEAN DEFAULT 0,
	  `modExcused` BOOLEAN DEFAULT 0,
	  `turnDateTime` int(10) unsigned, 
	  `modExcusedReason` text,
	  `samePeriodExcused` BOOLEAN DEFAULT 0,
	  KEY `missedPerUserPerDate` (`userID`,`turnDateTime`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `wD_Members` ADD `excusedMissedTurns` int(10) unsigned DEFAULT 1;
ALTER TABLE `wD_Games` ADD `excusedMissedTurns` int(10) unsigned DEFAULT 1;
ALTER TABLE `wD_Users` ADD `yearlyPhaseCount` mediumint(8) unsigned DEFAULT 0;     
ALTER TABLE `wD_Backup_Members` ADD `excusedMissedTurns` int(10) unsigned DEFAULT 1;
ALTER TABLE `wD_Backup_Games` ADD `excusedMissedTurns` int(10) unsigned DEFAULT 1;

 CREATE TABLE IF NOT EXISTS `wD_TurnDate` (
	  `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	  `gameID` mediumint(5) unsigned NOT NULL,
	  `userID` mediumint(8) unsigned NOT NULL,
	  `countryID` tinyint(3) unsigned NOT NULL,
	  `turn` smallint(5) unsigned NOT NULL,
	  `turnDateTime` int(10) unsigned, 
	  KEY `turnsByDate` (`userID`,`turnDateTime`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8; 