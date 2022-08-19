UPDATE `wD_Misc` SET `value` = '171' WHERE `name` = 'Version';

ALTER TABLE `wD_Games` ADD COLUMN `phaseMinutesRB` smallint(5) DEFAULT -1 AFTER `phaseMinutes`;
ALTER TABLE `wD_Backup_Games` ADD COLUMN `phaseMinutesRB` smallint(5) DEFAULT -1 AFTER `phaseMinutes`;

-- Change group types to be freetext for more flexibility
ALTER TABLE `wD_Groups`
	CHANGE COLUMN `type` `type` VARCHAR(50) NOT NULL DEFAULT 'Unknown' COLLATE 'utf8mb3_general_ci' AFTER `name`;
