 CREATE TABLE `wD_WatchedGames` (
	  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	  `userID` mediumint(8) unsigned NOT NULL,
	  `gameID` mediumint(8) unsigned NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `gid` (`gameID`),
	  KEY `uid` (`userID`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=644516 DEFAULT CHARSET=utf8;


UPDATE `wD_Misc` SET `value` = '137' WHERE `name` = 'Version';
