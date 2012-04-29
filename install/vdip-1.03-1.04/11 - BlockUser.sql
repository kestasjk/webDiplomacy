CREATE TABLE `wD_BlockUser` (
	`userID` mediumint(8) unsigned NOT NULL,
	`blockUserID` mediumint(8) unsigned NOT NULL,
	`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`userID`,`blockUserID`)
) ENGINE=MyISAM;
