UPDATE `wD_Misc` SET `value` = '172' WHERE `name` = 'Version';

ALTER TABLE `wD_AccessLog`
	ADD COLUMN `fingerprintProVisitorId` VARCHAR(50) NULL DEFAULT NULL,
	ADD COLUMN `fingerprintProConfidence` FLOAT NULL DEFAULT NULL;

ALTER TABLE `wD_Sessions`
	ADD COLUMN `fingerprintProVisitorId` VARCHAR(50) NULL DEFAULT NULL,
	ADD COLUMN `fingerprintProConfidence` FLOAT NULL DEFAULT NULL;

CREATE TABLE `wD_FingerprintProRequests` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`requestId` VARCHAR(50) NOT NULL,
	`visitorId` VARCHAR(50) NOT NULL,
	`linkedId` INT UNSIGNED NULL DEFAULT NULL,
	`confidence` FLOAT NULL DEFAULT NULL,
	`visitorFound` TINYINT NULL DEFAULT NULL,
	`incognito` TINYINT NULL DEFAULT NULL,
	`latitude` FLOAT NULL DEFAULT NULL,
	`longitude` FLOAT NULL DEFAULT NULL,
	`accuracyRadius` FLOAT NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `linkedId` (`linkedId`),
	INDEX `visitorId` (`visitorId`)
)
;

ALTER TABLE `wD_Users`
  ADD COLUMN `mobileCountryCode` mediumint(8) UNSIGNED DEFAULT NULL,
  ADD COLUMN `mobileNumber` bigint(20) UNSIGNED DEFAULT NULL,
  ADD COLUMN `isMobileValidated` bit(1) NOT NULL DEFAULT b'0',
  ADD COLUMN `missedPhasesLiveLastWeek` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
  ADD COLUMN `missedPhasesLiveLastMonth` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
  ADD COLUMN `missedPhasesLiveLastYear` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
  ADD COLUMN `missedPhasesNonLiveLastWeek` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
  ADD COLUMN `missedPhasesNonLiveLastMonth` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
  ADD COLUMN `missedPhasesNonLiveLastYear` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
  ADD COLUMN `missedPhasesTotalLastWeek` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
  ADD COLUMN `missedPhasesTotalLastMonth` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
  ADD COLUMN `missedPhasesTotalLastYear` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
  ADD COLUMN `isPhasesDirty` TINYINT(8) UNSIGNED NOT NULL DEFAULT '0'; 
ALTER TABLE `wD_Users` ADD INDEX(`isPhasesDirty`); 
ALTER TABLE `wD_MissedTurns` ADD INDEX `indUsersInReliabilityPeriod` (`reliabilityPeriod`, `userID`); 
ALTER TABLE `wD_MissedTurns` ADD INDEX(`turnDateTime`); 
ALTER TABLE `wD_MissedTurns` ADD INDEX `indIncludedInReliabilityPeriod` (`reliabilityPeriod`, `turnDateTime`); 

ALTER TABLE wD_Games ADD INDEX `ind_gmLookup` (`gameOver`, `processStatus`, `processTime`); 

-- This index was only on gameID which let deadlocks occur when two members lock their own record in the same game
ALTER TABLE `wD_Members` DROP INDEX `gid`;
ALTER TABLE `wD_Members` ADD INDEX `gid` (gameID, countryID);

ALTER TABLE `wD_FingerprintProRequests` ADD `timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `accuracyRadius`; 

CREATE TABLE IF NOT EXISTS `wD_GroupSourceJudgeUserWeightings` (
	`groupID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`source` ENUM('Self','Peer','Mod') NOT NULL,
	`judgeUserID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`userID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`weighting` DECIMAL(9,4) NOT NULL
)
ENGINE=InnoDB
;
CREATE TABLE IF NOT EXISTS `wD_GroupSourceJudgeUserToUserWeightings` (
	`groupID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`source` VARCHAR(5) NOT NULL,
	`judgeUserID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`fromUserID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`toUserID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`toWeighting` DECIMAL(9,4) NOT NULL
)
ENGINE=InnoDB
;
CREATE TABLE IF NOT EXISTS `wD_GroupSourceUserToUserLinks` (
	`source` VARCHAR(5) NOT NULL,
	`fromUserID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`toUserID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`avgPositiveWeighting` DECIMAL(9,4) NOT NULL,
	`maxPositiveWeighting` DECIMAL(9,4) NOT NULL,
	`countPositiveWeighting` DECIMAL(9,4) NOT NULL,
	`avgNegativeWeighting` DECIMAL(9,4) NOT NULL,
	`maxNegativeWeighting` DECIMAL(9,4) NOT NULL,
	`countNegativeWeighting` DECIMAL(9,4) NOT NULL
)
ENGINE=InnoDB
;
CREATE TABLE `wD_GroupUserToUserLinks` (
	`fromUserID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`toUserID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`peerAvgScore` DECIMAL(9,4) NOT NULL DEFAULT 0,
	`peerCount` DECIMAL(9,4) NOT NULL DEFAULT 0,
	`modAvgScore` DECIMAL(9,4) NOT NULL DEFAULT 0,
	`modCount` DECIMAL(9,4) NOT NULL DEFAULT 0,
	`selfAvgScore` DECIMAL(9,4) NOT NULL DEFAULT 0,
	`selfCount` DECIMAL(9,4) NOT NULL DEFAULT 0
)
ENGINE=InnoDB
;

ALTER TABLE `wD_GroupUsers`
	DROP COLUMN `timeWeightingRequired`,
	DROP COLUMN `timeMessageRequired`,
	ADD COLUMN  `isWeightingNeeded` BIT NOT NULL DEFAULT 0,
	ADD COLUMN  `isMessageNeeded` BIT NOT NULL DEFAULT 0,
	ADD COLUMN `isWeightingWaiting` BIT NOT NULL DEFAULT 0,
	ADD COLUMN `isMessageWaiting` BIT NOT NULL DEFAULT 0,
	ADD INDEX `userNeedsWeighting` (`isWeightingNeeded`, `userID`),
	ADD INDEX `userNeedsMessage` (`isMessageNeeded`, `userID`),
	ADD INDEX `modMessageWaiting` (`isMessageWaiting`, `modUserID`),
	ADD INDEX `modWeightingWaiting` (`isWeightingWaiting`, `modUserID`);

ALTER TABLE `wD_Groups`
	ADD COLUMN `modUserID` MEDIUMINT UNSIGNED NULL DEFAULT NULL,
	ADD COLUMN `isMessageNeeded` BIT NOT NULL DEFAULT 0,
	ADD COLUMN `isMessageWaiting` BIT NOT NULL DEFAULT 0,
	ADD INDEX `indMessageNeeded` (`isMessageNeeded`, `ownerUserID`),
	ADD INDEX `indMessageWaiting` (`isMessageWaiting`, `modUserID`);


ALTER TABLE `wD_Misc` CHANGE COLUMN `Name` `Name` ENUM('Version','Hits','Panic','Notice','Maintenance','LastProcessTime','GamesNew','GamesActive','GamesFinished','RankingPlayers','OnlinePlayers','ActivePlayers','TotalPlayers','ErrorLogs','GamesPaused','GamesOpen','GamesCrashed','LastModAction','ForumThreads','ThreadActiveThreshold','ThreadAliveThreshold','GameFeaturedThreshold','LastGroupUpdate','LastStatsUpdate') NOT NULL;
INSERT INTO wD_Misc (`Name`,`Value`) VALUES ('LastStatsUpdate',0);
INSERT INTO wD_Misc (`Name`,`Value`) VALUES ('LastGroupUpdate',0);
