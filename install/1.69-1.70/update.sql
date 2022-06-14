UPDATE `wD_Misc` SET `value` = '170' WHERE `name` = 'Version';

ALTER TABLE `wD_Games` ADD COLUMN `phaseMinutesRB` smallint(5) DEFAULT -1 AFTER `phaseMinutes`;
ALTER TABLE `wD_Backup_Games` ADD COLUMN `phaseMinutesRB` smallint(5) DEFAULT -1 AFTER `phaseMinutes`;
