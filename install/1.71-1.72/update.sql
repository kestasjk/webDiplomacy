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
  ADD COLUMN `isMobileValidated` bit(1) NOT NULL DEFAULT b'0';

ALTER TABLE `wD_Misc` CHANGE COLUMN `Name` `Name` ENUM('Version','Hits','Panic','Notice','Maintenance','LastProcessTime','GamesNew','GamesActive','GamesFinished','RankingPlayers','OnlinePlayers','ActivePlayers','TotalPlayers','ErrorLogs','GamesPaused','GamesOpen','GamesCrashed','LastModAction','ForumThreads','ThreadActiveThreshold','ThreadAliveThreshold','GameFeaturedThreshold','LastGroupUpdate') NOT NULL;
INSERT INTO wD_Misc (`Name`,`Value`) VALUES ('LastGroupUpdate',0);
