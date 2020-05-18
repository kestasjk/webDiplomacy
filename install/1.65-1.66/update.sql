UPDATE `wD_Misc` SET `value` = '166' WHERE `name` = 'Version';

CREATE TABLE `wD_GhostRatings` (
`userID` mediumint(8) unsigned NOT NULL,
`categoryID` mediumint(8) unsigned NOT NULL,
`rating` FLOAT,
`peakRating` FLOAT,
`monthYear` smallint(4) unsigned NOT NULL,
INDEX ( `userID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_GhostRatingsHistory` (
`userID` mediumint(8) unsigned NOT NULL,
`categoryID` mediumint(8) unsigned NOT NULL,
`monthYear` smallint(4) unsigned NOT NULL,
`rating` FLOAT,
INDEX ( `userID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_GhostRatingsBackup` (
  `userID` mediumint(8) unsigned NOT NULL,
  `categoryID` mediumint(8) unsigned NOT NULL,
  `gameID` mediumint(8) unsigned NOT NULL,
  `adjustment` FLOAT,
  `timeFinished` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `wD_Games` ADD COLUMN `grCalculated` INT NOT NULL DEFAULT 0;