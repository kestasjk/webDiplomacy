CREATE TABLE `wD_MuteUser` (
	`userID` mediumint(8) unsigned NOT NULL,
	`muteUserID` mediumint(8) unsigned NOT NULL,
	PRIMARY KEY (`userID`,`muteUserID`)
) ENGINE=MyISAM;

CREATE TABLE `wD_MuteCountry` (
	`userID` MEDIUMINT UNSIGNED NOT NULL ,
	`gameID` MEDIUMINT UNSIGNED NOT NULL ,
	`muteCountryID` TINYINT UNSIGNED NOT NULL,
	PRIMARY KEY ( `userID` , `gameID` , `muteCountryID` )
) ENGINE=MYISAM ;

UPDATE wD_Misc SET `value`=101 WHERE `name`='Version';
