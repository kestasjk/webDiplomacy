CREATE TABLE IF NOT EXISTS `wD_LikePost` (
  `userID` mediumint(8) unsigned NOT NULL,
  `likeMessageID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userID`,`likeMessageID`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `wD_MuteThread` (
  `userID` mediumint(8) unsigned NOT NULL,
  `muteThreadID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userID`,`muteThreadID`)
) ENGINE=MyISAM;

ALTER TABLE `wD_LikePost` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `wD_MuteThread` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `wD_MuteUser` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;
ALTER TABLE `wD_MuteCountry` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE wD_Misc SET `value`=103 WHERE `name`='Version';
