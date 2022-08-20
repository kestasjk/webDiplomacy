UPDATE `wD_Misc` SET `value` = '171' WHERE `name` = 'Version';

ALTER TABLE `wD_Games` ADD COLUMN `phaseMinutesRB` smallint(5) DEFAULT -1 AFTER `phaseMinutes`;
ALTER TABLE `wD_Backup_Games` ADD COLUMN `phaseMinutesRB` smallint(5) DEFAULT -1 AFTER `phaseMinutes`;

-- Change group types to be freetext for more flexibility
ALTER TABLE `wD_Groups`
	CHANGE COLUMN `type` `type` VARCHAR(50) NOT NULL DEFAULT 'Unknown' AFTER `name`;

ALTER TABLE `wD_GroupUsers`
	ADD COLUMN `isDirty` BIT(1) NOT NULL DEFAULT 0 AFTER `timeCreated`,
	ADD COLUMN `messageCount` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `isDirty`,
	ADD COLUMN `timeLastMessageSent` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `messageCount`,
	ADD COLUMN `timeWeightingRequired` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `timeLastMessageSent`,
	ADD COLUMN `timeMessageRequired` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `timeWeightingRequired`;
ALTER TABLE `wD_GroupUsers`
	ADD INDEX `isActive_isDirty` (`isActive`, `isDirty`);

CREATE TABLE `wD_Group_UserByUserBySourceWeights` (
	`fromUserID` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	`toUserID` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	`source` VARCHAR(5) NOT NULL DEFAULT '',
	`weighting` DECIMAL(9,4) NULL DEFAULT NULL,
	`judgeCount` MEDIUMINT(9) NOT NULL DEFAULT '0',
	PRIMARY KEY (`fromUserID`, `toUserID`, `source`) USING BTREE
) ENGINE=InnoDB;