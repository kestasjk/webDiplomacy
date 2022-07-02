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

ALTER TABLE `wD_ForumMessages` CHANGE `type` `type` ENUM('ThreadStart','ThreadReply','GroupDiscussion') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

CREATE TABLE `wD_Groups` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(15) NOT NULL,
	`type` ENUM('Person','Family','School','Work','Other','Unknown') NOT NULL,
	`isActive` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	`display` SET('Profile','Usertag','AnonGames','Moderators') NOT NULL DEFAULT '',
	`timeCreated` BIGINT(20) UNSIGNED NOT NULL,
	`ownerUserId` MEDIUMINT(8) UNSIGNED NOT NULL,
	`description` VARCHAR(2000) NULL DEFAULT NULL,
	`moderatorNotes` VARCHAR(2000) NULL DEFAULT NULL,
	`timeChanged` BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `indGroupsLastChanged` (`timeChanged`) USING BTREE
)
ENGINE=InnoDB
;

CREATE TABLE `wD_GroupUsers` (
	`userId` MEDIUMINT(8) UNSIGNED NOT NULL,
	`groupId` MEDIUMINT(8) UNSIGNED NOT NULL,
	`isActive` TINYINT(1) NOT NULL,
	`userWeighting` TINYINT(1) NOT NULL,
	`ownerWeighting` TINYINT(1) NOT NULL,
	`modWeighting` TINYINT(1) NOT NULL,
	`modUserId` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	`createdByUserId` MEDIUMINT(8) UNSIGNED NOT NULL,
	`timeChanged` BIGINT(20) UNSIGNED NOT NULL,
	`timeCreated` BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY (`userId`, `groupId`, `isActive`) USING BTREE,
	UNIQUE INDEX `groupUsersByGroup` (`userId`, `groupId`, `isActive`) USING BTREE,
	INDEX `groupUsersChanged` (`timeChanged`) USING BTREE
)
ENGINE=InnoDB
;
CREATE TABLE `wD_UserOpenIDLinks` (
  `userId` mediumint(8) UNSIGNED NOT NULL,
  `source` enum('facebook','google','sms') NOT NULL,
  `given_name` varchar(1000) DEFAULT NULL,
  `family_name` varchar(1000) DEFAULT NULL,
  `nickname` varchar(1000) DEFAULT NULL,
  `name` varchar(1000) DEFAULT NULL,
  `picture` varchar(1000) DEFAULT NULL,
  `updated_at` varchar(1000) DEFAULT NULL,
  `email_verified` varchar(1000) DEFAULT NULL,
  `email` varchar(1000) DEFAULT NULL,
  `sub` varchar(1000) DEFAULT NULL,
  `aud` varchar(1000) DEFAULT NULL,
  `locale` varchar(1000) DEFAULT NULL,
  `timeCreated` bigint(20) UNSIGNED NOT NULL,
  `timeUpdated` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `wD_UserOpenIDLinks`
  ADD PRIMARY KEY (`userId`,`source`);

ALTER TABLE wD_Groups ADD COLUMN gameId MEDIUMINT NULL;

DELETE d FROM wD_TurnDate d INNER JOIN wD_Games g ON g.id = d.gameID WHERE g.playerTypes = 'MemberVsBots';
DELETE d FROM wD_MissedTurns d INNER JOIN wD_Games g ON g.id = d.gameID WHERE g.playerTypes = 'MemberVsBots';

 ALTER TABLE wD_Users CHANGE `reliabilityRating` `reliabilityRating` double NOT NULL DEFAULT '100';

 ALTER TABLE wD_MissedTurns ADD COLUMN reliabilityPeriod TINYINT NULL DEFAULT -1;