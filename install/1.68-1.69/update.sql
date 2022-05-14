ALTER TABLE `wD_Misc` CHANGE `value` `value` BIGINT(10) UNSIGNED NOT NULL; 
UPDATE `wD_Misc` SET `value` = '169' WHERE `name` = 'Version';

ALTER TABLE `wD_Users` ADD COLUMN `groupTag` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `wD_Members` ADD COLUMN `groupTag` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `wD_Backup_Members` ADD COLUMN `groupTag` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;

ALTER TABLE `wD_AccessLog` ADD COLUMN `browserFingerprint` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `wD_Sessions` ADD COLUMN `browserFingerprint` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 


ALTER TABLE `wD_TurnDate` ADD `isInReliabilityPeriod` BOOLEAN NULL DEFAULT FALSE AFTER `id`; 
ALTER TABLE `wD_TurnDate` CHANGE `turnDateTime` `turnDateTime` INT(10) UNSIGNED NOT NULL; 

ALTER TABLE `wD_TurnDate` 
    ADD INDEX `indUsersInReliabilityPeriod` (`isInReliabilityPeriod`, `userID`) USING BTREE,
	ADD INDEX `indTimestamp` (`turnDateTime`) USING BTREE,
	ADD INDEX `indIncludedInReliabilityPeriod` (`isInReliabilityPeriod`, `turnDateTime`) USING BTREE;

    
UPDATE wD_TurnDate SET isInReliabilityPeriod = 1 WHERE turnDateTime > UNIX_TIMESTAMP() - 365*24*60*60;

ALTER TABLE `wD_Notices` CHANGE `type` `type` ENUM('PM','Game','User','Group') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 