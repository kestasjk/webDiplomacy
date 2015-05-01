 CREATE TABLE `wD_WatchedGames` (
	  `userID` mediumint(8) unsigned NOT NULL,
	  `gameID` mediumint(8) unsigned NOT NULL,
	  KEY `gid` (`gameID`),
	  KEY `uid` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


UPDATE `wD_Misc` SET `value` = '138' WHERE `name` = 'Version';
