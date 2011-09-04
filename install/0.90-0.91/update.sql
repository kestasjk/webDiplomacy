-- phaseHours -> phaseMinutes
ALTER TABLE `wD_Games`
 ADD `phaseMinutes` smallint(5) unsigned NOT NULL DEFAULT 1440;
UPDATE `wD_Games` SET phaseMinutes=phaseHours*60;
ALTER TABLE `wD_Games`
 DROP `phaseHours`,
 DROP INDEX `phase_7`,
 ADD INDEX (`phaseMinutes`),
 ADD INDEX `phase_7` (`phase`, `phaseMinutes`);

-- Anonymous games, press-types
 ALTER TABLE `wD_Games`
 ADD `anon` enum('Yes', 'No') NOT NULL DEFAULT 'No',
 ADD `pressType` enum('Regular', 'PublicPressOnly', 'NoPress') NOT NULL DEFAULT 'Regular',
 ADD INDEX (`anon`),
 ADD INDEX (`pressType`);
 
 UPDATE wD_Misc SET value=91 WHERE name = 'Version';