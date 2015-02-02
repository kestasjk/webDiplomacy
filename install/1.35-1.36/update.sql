ALTER TABLE `wD_Users` ADD COLUMN `cdCount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `nmrCount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `cdTakenCount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `phaseCount` int(10) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `gameCount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `reliabilityRating` double NOT NULL DEFAULT '1',
  ADD COLUMN `deletedCDs` int(11) DEFAULT '0';

CREATE TABLE IF NOT EXISTS `wD_NMRs` (
	  `gameID` mediumint(5) unsigned NOT NULL,
	  `userID` mediumint(8) unsigned NOT NULL,
	  `countryID` tinyint(3) unsigned NOT NULL,
	  `turn` smallint(5) unsigned NOT NULL,
	  `bet` smallint(5) unsigned NOT NULL,
	  `SCCount` smallint(5) unsigned NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `wD_NMRs`
  ADD KEY `gameID` (`gameID`,`userID`), ADD KEY `userID` (`userID`);

ALTER TABLE wD_CivilDisorders ADD COLUMN forcedByMod BOOLEAN DEFAULT 0;

UPDATE `wD_Misc` SET `value` = '136' WHERE `name` = 'Version';
