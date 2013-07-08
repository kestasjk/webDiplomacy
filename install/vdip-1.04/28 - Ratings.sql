CREATE TABLE `wD_Ratings` (
  `userID` mediumint(8) unsigned NOT NULL,
  `ratingType` enum('VDip') CHARACTER SET utf8 NOT NULL DEFAULT 'VDip',
  `gameID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rating` smallint(5) unsigned NOT NULL DEFAULT '1500',
  `fixed` enum('variantID', 'potType', 'pressType') CHARACTER SET utf8 DEFAULT NULL,
  KEY `userID` (`userID`),
  KEY `ratingType` (`ratingType`),
  KEY `gameID` (`gameID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
