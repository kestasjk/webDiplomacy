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

ALTER TABLE `wD_Sessions`
	ADD COLUMN `ipv6` BINARY(16) NULL DEFAULT NULL;
ALTER TABLE `wD_AccessLog`
	ADD COLUMN `ipv6` BINARY(16) NULL DEFAULT NULL;


CREATE TABLE `wD_ModForumMessages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `toID` int(10) unsigned NOT NULL,
  `fromUserID` mediumint(8) unsigned NOT NULL,
  `timeSent` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `subject` varchar(100) NOT NULL,
  `type` enum('ThreadStart','ThreadReply') NOT NULL,
  `replies` smallint(5) unsigned NOT NULL,
  `latestReplySent` int(10) unsigned NOT NULL,
  `silenceID` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `latest` (`timeSent`),
  KEY `threadReplies` (`type`,`toID`,`timeSent`),
  KEY `latestReplySent` (`latestReplySent`),
  KEY `profileLinks` (`type`,`fromUserID`,`timeSent`),
  KEY `type` (`type`,`latestReplySent`)
) ENGINE=InnoDB;
ALTER TABLE `wD_Users` MODIFY `notifications` set('PrivateMessage','GameMessage','Unfinalized','GameUpdate','ModForum','CountrySwitch','ForceModMessage');

ALTER TABLE `wD_ModForumMessages` ADD `adminReply` enum('Yes','No') NOT NULL DEFAULT 'No';
ALTER TABLE `wD_ModForumMessages` ADD `status` enum('New','Open','Resolved') NOT NULL DEFAULT 'New';

ALTER TABLE `wD_ModForumMessages` MODIFY `status` enum('New','Open','Resolved','Bugs','Sticky') NOT NULL DEFAULT 'New';
ALTER TABLE `wD_ModForumMessages` ADD `toUserID` mediumint(8) unsigned DEFAULT 0;
ALTER TABLE `wD_ModForumMessages` ADD  `forceReply` enum('Yes','No','Done') NOT NULL DEFAULT 'No';

CREATE TABLE `wD_ForceReply` (
  `id` int(10) unsigned NOT NULL,
  `toUserID` mediumint(8) unsigned DEFAULT 0,
  `forceReply` enum('Yes','No','Done') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`,`toUserID`)
) ENGINE=InnoDB;

ALTER TABLE `wD_ModForumMessages` DROP `toUserID`;	
ALTER TABLE `wD_ModForumMessages` DROP `forceReply`;	
ALTER TABLE `wD_ModForumMessages` ADD `assigned` mediumint(8) unsigned DEFAULT 0;
ALTER TABLE `wD_ForceReply` ADD `status` enum('Sent','Read','Replied') NOT NULL DEFAULT 'Sent';
ALTER TABLE `wD_ForceReply` ADD `readIP`  int(10) unsigned NOT NULL;
ALTER TABLE `wD_ForceReply` ADD `readTime` int(10) unsigned NOT NULL;
ALTER TABLE `wD_ForceReply` ADD `replyIP` int(10) unsigned NOT NULL;

ALTER TABLE `wD_Misc` CHANGE COLUMN `Name` `Name` ENUM('Version','Hits','Panic','Notice','Maintenance','LastProcessTime','GamesNew','GamesActive','GamesFinished','RankingPlayers','OnlinePlayers','ActivePlayers','TotalPlayers','ErrorLogs','GamesPaused','GamesOpen','GamesCrashed','LastModAction','ForumThreads','ThreadActiveThreshold','ThreadAliveThreshold','GameFeaturedThreshold','LastGroupUpdate','LastStatsUpdate') NOT NULL;
INSERT INTO wD_Misc (`Name`,`Value`) VALUES ('LastStatsUpdate',0);
INSERT INTO wD_Misc (`Name`,`Value`) VALUES ('LastGroupUpdate',0);


ALTER TABLE `wD_ModForumMessages`
	ADD COLUMN `gameID` MEDIUMINT UNSIGNED NULL DEFAULT NULL,
	ADD COLUMN `requestType` VARCHAR(150) NULL DEFAULT NULL;

ALTER TABLE `wD_Games`
	ADD COLUMN `gameMasterUserID` MEDIUMINT UNSIGNED NULL DEFAULT NULL,
	ADD COLUMN `relationshipLimit` FLOAT NULL DEFAULT NULL,
	ADD COLUMN `suspicionLimit` FLOAT NULL DEFAULT NULL,
	ADD COLUMN `identityRequirement` SET('Facebook','Google','SMS','Paypal') NULL DEFAULT NULL;

ALTER TABLE `wD_Backup_Games`
	ADD COLUMN `gameMasterUserID` MEDIUMINT UNSIGNED NULL DEFAULT NULL,
	ADD COLUMN `relationshipLimit` FLOAT NULL DEFAULT NULL,
	ADD COLUMN `suspicionLimit` FLOAT NULL DEFAULT NULL,
	ADD COLUMN `identityRequirement` SET('Facebook','Google','SMS','Paypal') NULL DEFAULT NULL;

CREATE TABLE wD_UserCodeConnections (
  `userID` mediumint(8) unsigned ,
  `type` ENUM('Cookie','IP','Fingerprint','FingerprintPro') NOT NULL,
  `code` BINARY(16) NOT NULL,
  `earliest` TIMESTAMP NOT NULL,
  `latest` TIMESTAMP NOT NULL,
  `count` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`userID`,`type`,`code`)
) ENGINE=InnoDB;


ALTER TABLE wD_AccessLog ADD COLUMN browserFingerprintBin BINARY(16) NULL DEFAULT NULL;
UPDATE wD_AccessLog SET browserFingerprintBin=HEX(browserFingerprint) WHERE browserFingerprint IS NOT NULL;
ALTER TABLE wD_AccessLog DROP COLUMN browserFingerprint;
ALTER TABLE wD_AccessLog CHANGE COLUMN browserFingerprintBin browserFingerprint BINARY(16) NULL DEFAULT NULL;
ALTER TABLE wD_AccessLog ADD INDEX `indBrowserFingerprint` (`browserFingerprint`);

UPDATE wD_AccessLog SET ipv6=CAST(ip as BINARY);
ALTER TABLE wD_AccessLog DROP COLUMN ip;
ALTER TABLE wD_AccessLog CHANGE COLUMN ipv6 ip BINARY(16) NOT NULL;
ALTER TABLE wD_AccessLog ADD INDEX `indIP` (`ip`);
ALTER TABLE wD_AccessLog ADD INDEX `lastRequest` (`lastRequest`);

ALTER TABLE wD_Sessions CHANGE COLUMN browserFingerprint browserFingerprint BINARY(16) NULL DEFAULT NULL;
ALTER TABLE wD_Sessions CHANGE COLUMN ip ip BINARY(16) NULL DEFAULT NULL;

ALTER TABLE `wD_UserConnections`
	ADD COLUMN `countMatchedFingerprintUsers` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `countMatchedFingerprintProUsers` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `wD_UserConnections`
	ADD COLUMN `day0hour0` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour1` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour2` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour3` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour4` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour5` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour6` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour7` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour8` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour9` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour10` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour11` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour12` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour13` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour14` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour15` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour16` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour17` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour18` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour19` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour20` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour21` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour22` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day0hour23` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour0` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour1` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour2` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour3` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour4` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour5` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour6` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour7` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour8` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour9` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour10` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour11` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour12` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour13` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour14` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour15` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour16` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour17` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour18` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour19` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour20` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour21` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour22` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day1hour23` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour0` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour1` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour2` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour3` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour4` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour5` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour6` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour7` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour8` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour9` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour10` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour11` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour12` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour13` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour14` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour15` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour16` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour17` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour18` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour19` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour20` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour21` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour22` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day2hour23` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour0` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour1` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour2` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour3` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour4` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour5` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour6` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour7` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour8` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour9` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour10` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour11` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour12` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour13` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour14` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour15` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour16` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour17` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour18` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour19` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour20` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour21` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour22` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day3hour23` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour0` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour1` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour2` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour3` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour4` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour5` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour6` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour7` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour8` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour9` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour10` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour11` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour12` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour13` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour14` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour15` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour16` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour17` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour18` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour19` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour20` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour21` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour22` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day4hour23` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour0` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour1` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour2` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour3` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour4` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour5` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour6` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour7` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour8` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour9` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour10` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour11` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour12` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour13` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour14` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour15` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour16` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour17` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour18` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour19` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour20` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour21` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour22` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day5hour23` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour0` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour1` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour2` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour3` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour4` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour5` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour6` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour7` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour8` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour9` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour10` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour11` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour12` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour13` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour14` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour15` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour16` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour17` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour18` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour19` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour20` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour21` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour22` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `day6hour23` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `totalHits` INT UNSIGNED NOT NULL DEFAULT '0'
	;
ALTER TABLE wD_UserCodeConnections ADD INDEX (type,code);
ALTER TABLE wD_UserCodeConnections ADD COLUMN isNew TINYINT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE wD_UserCodeConnections ADD INDEX (isNew);

INSERT INTO wD_UserConnections (userID)
SELECT id FROM wD_Users u
LEFT JOIN wD_UserConnections c ON c.userID = u.id
WHERE c.userID IS NULL;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
	SELECT userID, 'Cookie' type, CAST(cookieCode AS BINARY) code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
	FROM wD_AccessLog
	GROUP BY userID, cookieCode
) r
ON DUPLICATE KEY UPDATE latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
	SELECT userID, 'IP' type, ip code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
	FROM wD_AccessLog
	GROUP BY userID, ip
) r
ON DUPLICATE KEY UPDATE latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
	SELECT userID, 'Fingerprint' type, browserFingerprint code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
	FROM wD_AccessLog
	WHERE browserFingerprint IS NOT NULL AND browserFingerprint <> 0
	GROUP BY userID, browserFingerprint
) r
ON DUPLICATE KEY UPDATE latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
	SELECT linkedId userId, 'FingerprintPro' type, 
		FROM_BASE64(visitorId) code, FROM_UNIXTIME(CAST(LEFT(requestId,10) AS INT)) earliestRequest, 
		FROM_UNIXTIME(CAST(LEFT(requestId,10) AS INT)) latestRequest, 
		1 requestCount
 	FROM wD_FingerprintProRequests f
) r
ON DUPLICATE KEY UPDATE latest=greatest(latestRequest, latest), count=count+requestCount;

UPDATE wD_UserConnections SET countMatchedIPUsers = 0, countMatchedCookieUsers = 0, countMatchedFingerprintUsers = 0, countMatchedFingerprintProUsers = 0;

UPDATE wD_UserConnections uc
INNER JOIN (
	SELECT a.userID, a.type, COUNT(*) matches
	FROM wD_UserCodeConnections a
	INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'IP'
	GROUP BY a.userID, a.type
) rec ON rec.userID = uc.userId
SET countMatchedIPUsers = countMatchedIPUsers + rec.matches;
UPDATE wD_UserConnections uc
INNER JOIN (
	SELECT a.userID, a.type, COUNT(*) matches
	FROM wD_UserCodeConnections a
	INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'Cookie'
	GROUP BY a.userID, a.type
) rec ON rec.userID = uc.userId
SET countMatchedCookieUsers = countMatchedCookieUsers + rec.matches;
UPDATE wD_UserConnections uc
INNER JOIN (
	SELECT a.userID, a.type, COUNT(*) matches
	FROM wD_UserCodeConnections a
	INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'Fingerprint'
	GROUP BY a.userID, a.type
) rec ON rec.userID = uc.userId
SET countMatchedFingerprintUsers = countMatchedFingerprintUsers + rec.matches;
UPDATE wD_UserConnections uc
INNER JOIN (
	SELECT a.userID, a.type, COUNT(*) matches
	FROM wD_UserCodeConnections a
	INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'FingerprintPro'
	GROUP BY a.userID, a.type
) rec ON rec.userID = uc.userId
SET countMatchedFingerprintProUsers = countMatchedFingerprintProUsers + rec.matches;

UPDATE wD_UserConnections uc
SET uc.day0hour0  = 0,
	uc.day0hour1  = 0,
	uc.day0hour2  = 0,
	uc.day0hour3  = 0,
	uc.day0hour4  = 0,
	uc.day0hour5  = 0,
	uc.day0hour6  = 0,
	uc.day0hour7  = 0,
	uc.day0hour8  = 0,
	uc.day0hour9  = 0,
	uc.day0hour10 = 0,
	uc.day0hour11 = 0,
	uc.day0hour12 = 0,
	uc.day0hour13 = 0,
	uc.day0hour14 = 0,
	uc.day0hour15 = 0,
	uc.day0hour16 = 0,
	uc.day0hour17 = 0,
	uc.day0hour18 = 0,
	uc.day0hour19 = 0,
	uc.day0hour20 = 0,
	uc.day0hour21 = 0,
	uc.day0hour22 = 0,
	uc.day0hour23 = 0,
	uc.day1hour0  = 0,
	uc.day1hour1  = 0,
	uc.day1hour2  = 0,
	uc.day1hour3  = 0,
	uc.day1hour4  = 0,
	uc.day1hour5  = 0,
	uc.day1hour6  = 0,
	uc.day1hour7  = 0,
	uc.day1hour8  = 0,
	uc.day1hour9  = 0,
	uc.day1hour10 = 0,
	uc.day1hour11 = 0,
	uc.day1hour12 = 0,
	uc.day1hour13 = 0,
	uc.day1hour14 = 0,
	uc.day1hour15 = 0,
	uc.day1hour16 = 0,
	uc.day1hour17 = 0,
	uc.day1hour18 = 0,
	uc.day1hour19 = 0,
	uc.day1hour20 = 0,
	uc.day1hour21 = 0,
	uc.day1hour22 = 0,
	uc.day1hour23 = 0,
	uc.day2hour0  = 0,
	uc.day2hour1  = 0,
	uc.day2hour2  = 0,
	uc.day2hour3  = 0,
	uc.day2hour4  = 0,
	uc.day2hour5  = 0,
	uc.day2hour6  = 0,
	uc.day2hour7  = 0,
	uc.day2hour8  = 0,
	uc.day2hour9  = 0,
	uc.day2hour10 = 0,
	uc.day2hour11 = 0,
	uc.day2hour12 = 0,
	uc.day2hour13 = 0,
	uc.day2hour14 = 0,
	uc.day2hour15 = 0,
	uc.day2hour16 = 0,
	uc.day2hour17 = 0,
	uc.day2hour18 = 0,
	uc.day2hour19 = 0,
	uc.day2hour20 = 0,
	uc.day2hour21 = 0,
	uc.day2hour22 = 0,
	uc.day2hour23 = 0,
	uc.day3hour0  = 0,
	uc.day3hour1  = 0,
	uc.day3hour2  = 0,
	uc.day3hour3  = 0,
	uc.day3hour4  = 0,
	uc.day3hour5  = 0,
	uc.day3hour6  = 0,
	uc.day3hour7  = 0,
	uc.day3hour8  = 0,
	uc.day3hour9  = 0,
	uc.day3hour10 = 0,
	uc.day3hour11 = 0,
	uc.day3hour12 = 0,
	uc.day3hour13 = 0,
	uc.day3hour14 = 0,
	uc.day3hour15 = 0,
	uc.day3hour16 = 0,
	uc.day3hour17 = 0,
	uc.day3hour18 = 0,
	uc.day3hour19 = 0,
	uc.day3hour20 = 0,
	uc.day3hour21 = 0,
	uc.day3hour22 = 0,
	uc.day3hour23 = 0,
	uc.day4hour0  = 0,
	uc.day4hour1  = 0,
	uc.day4hour2  = 0,
	uc.day4hour3  = 0,
	uc.day4hour4  = 0,
	uc.day4hour5  = 0,
	uc.day4hour6  = 0,
	uc.day4hour7  = 0,
	uc.day4hour8  = 0,
	uc.day4hour9  = 0,
	uc.day4hour10 = 0,
	uc.day4hour11 = 0,
	uc.day4hour12 = 0,
	uc.day4hour13 = 0,
	uc.day4hour14 = 0,
	uc.day4hour15 = 0,
	uc.day4hour16 = 0,
	uc.day4hour17 = 0,
	uc.day4hour18 = 0,
	uc.day4hour19 = 0,
	uc.day4hour20 = 0,
	uc.day4hour21 = 0,
	uc.day4hour22 = 0,
	uc.day4hour23 = 0,
	uc.day5hour0  = 0,
	uc.day5hour1  = 0,
	uc.day5hour2  = 0,
	uc.day5hour3  = 0,
	uc.day5hour4  = 0,
	uc.day5hour5  = 0,
	uc.day5hour6  = 0,
	uc.day5hour7  = 0,
	uc.day5hour8  = 0,
	uc.day5hour9  = 0,
	uc.day5hour10 = 0,
	uc.day5hour11 = 0,
	uc.day5hour12 = 0,
	uc.day5hour13 = 0,
	uc.day5hour14 = 0,
	uc.day5hour15 = 0,
	uc.day5hour16 = 0,
	uc.day5hour17 = 0,
	uc.day5hour18 = 0,
	uc.day5hour19 = 0,
	uc.day5hour20 = 0,
	uc.day5hour21 = 0,
	uc.day5hour22 = 0,
	uc.day5hour23 = 0,
	uc.day6hour0  = 0,
	uc.day6hour1  = 0,
	uc.day6hour2  = 0,
	uc.day6hour3  = 0,
	uc.day6hour4  = 0,
	uc.day6hour5  = 0,
	uc.day6hour6  = 0,
	uc.day6hour7  = 0,
	uc.day6hour8  = 0,
	uc.day6hour9  = 0,
	uc.day6hour10 = 0,
	uc.day6hour11 = 0,
	uc.day6hour12 = 0,
	uc.day6hour13 = 0,
	uc.day6hour14 = 0,
	uc.day6hour15 = 0,
	uc.day6hour16 = 0,
	uc.day6hour17 = 0,
	uc.day6hour18 = 0,
	uc.day6hour19 = 0,
	uc.day6hour20 = 0,
	uc.day6hour21 = 0,
	uc.day6hour22 = 0,
	uc.day6hour23 = 0,
	uc.totalHits = 0
	;

INSERT INTO wD_UserConnections (userID)
SELECT userID
FROM (
	SELECT userID, DAYOFWEEK(lastRequest)-1 d, HOUR(lastRequest) h, SUM(hits) c
	FROM wD_AccessLog
	GROUP BY userID, DAYOFWEEK(lastRequest), HOUR(lastRequest)
) rec
ON DUPLICATE KEY UPDATE  
	day0hour0  = day0hour0  + IF(d=0 AND h=0 ,c,0),
	day0hour1  = day0hour1  + IF(d=0 AND h=1 ,c,0),
	day0hour2  = day0hour2  + IF(d=0 AND h=2 ,c,0),
	day0hour3  = day0hour3  + IF(d=0 AND h=3 ,c,0),
	day0hour4  = day0hour4  + IF(d=0 AND h=4 ,c,0),
	day0hour5  = day0hour5  + IF(d=0 AND h=5 ,c,0),
	day0hour6  = day0hour6  + IF(d=0 AND h=6 ,c,0),
	day0hour7  = day0hour7  + IF(d=0 AND h=7 ,c,0),
	day0hour8  = day0hour8  + IF(d=0 AND h=8 ,c,0),
	day0hour9  = day0hour9  + IF(d=0 AND h=9 ,c,0),
	day0hour10 = day0hour10 + IF(d=0 AND h=10,c,0),
	day0hour11 = day0hour11 + IF(d=0 AND h=11,c,0),
	day0hour12 = day0hour12 + IF(d=0 AND h=12,c,0),
	day0hour13 = day0hour13 + IF(d=0 AND h=13,c,0),
	day0hour14 = day0hour14 + IF(d=0 AND h=14,c,0),
	day0hour15 = day0hour15 + IF(d=0 AND h=15,c,0),
	day0hour16 = day0hour16 + IF(d=0 AND h=16,c,0),
	day0hour17 = day0hour17 + IF(d=0 AND h=17,c,0),
	day0hour18 = day0hour18 + IF(d=0 AND h=18,c,0),
	day0hour19 = day0hour19 + IF(d=0 AND h=19,c,0),
	day0hour20 = day0hour20 + IF(d=0 AND h=20,c,0),
	day0hour21 = day0hour21 + IF(d=0 AND h=21,c,0),
	day0hour22 = day0hour22 + IF(d=0 AND h=22,c,0),
	day0hour23 = day0hour23 + IF(d=0 AND h=23,c,0),
	day1hour0  = day1hour0  + IF(d=1 AND h=0 ,c,0),
	day1hour1  = day1hour1  + IF(d=1 AND h=1 ,c,0),
	day1hour2  = day1hour2  + IF(d=1 AND h=2 ,c,0),
	day1hour3  = day1hour3  + IF(d=1 AND h=3 ,c,0),
	day1hour4  = day1hour4  + IF(d=1 AND h=4 ,c,0),
	day1hour5  = day1hour5  + IF(d=1 AND h=5 ,c,0),
	day1hour6  = day1hour6  + IF(d=1 AND h=6 ,c,0),
	day1hour7  = day1hour7  + IF(d=1 AND h=7 ,c,0),
	day1hour8  = day1hour8  + IF(d=1 AND h=8 ,c,0),
	day1hour9  = day1hour9  + IF(d=1 AND h=9 ,c,0),
	day1hour10 = day1hour10 + IF(d=1 AND h=10,c,0),
	day1hour11 = day1hour11 + IF(d=1 AND h=11,c,0),
	day1hour12 = day1hour12 + IF(d=1 AND h=12,c,0),
	day1hour13 = day1hour13 + IF(d=1 AND h=13,c,0),
	day1hour14 = day1hour14 + IF(d=1 AND h=14,c,0),
	day1hour15 = day1hour15 + IF(d=1 AND h=15,c,0),
	day1hour16 = day1hour16 + IF(d=1 AND h=16,c,0),
	day1hour17 = day1hour17 + IF(d=1 AND h=17,c,0),
	day1hour18 = day1hour18 + IF(d=1 AND h=18,c,0),
	day1hour19 = day1hour19 + IF(d=1 AND h=19,c,0),
	day1hour20 = day1hour20 + IF(d=1 AND h=20,c,0),
	day1hour21 = day1hour21 + IF(d=1 AND h=21,c,0),
	day1hour22 = day1hour22 + IF(d=1 AND h=22,c,0),
	day1hour23 = day1hour23 + IF(d=1 AND h=23,c,0),
	day2hour0  = day2hour0  + IF(d=2 AND h=0 ,c,0),
	day2hour1  = day2hour1  + IF(d=2 AND h=1 ,c,0),
	day2hour2  = day2hour2  + IF(d=2 AND h=2 ,c,0),
	day2hour3  = day2hour3  + IF(d=2 AND h=3 ,c,0),
	day2hour4  = day2hour4  + IF(d=2 AND h=4 ,c,0),
	day2hour5  = day2hour5  + IF(d=2 AND h=5 ,c,0),
	day2hour6  = day2hour6  + IF(d=2 AND h=6 ,c,0),
	day2hour7  = day2hour7  + IF(d=2 AND h=7 ,c,0),
	day2hour8  = day2hour8  + IF(d=2 AND h=8 ,c,0),
	day2hour9  = day2hour9  + IF(d=2 AND h=9 ,c,0),
	day2hour10 = day2hour10 + IF(d=2 AND h=10,c,0),
	day2hour11 = day2hour11 + IF(d=2 AND h=11,c,0),
	day2hour12 = day2hour12 + IF(d=2 AND h=12,c,0),
	day2hour13 = day2hour13 + IF(d=2 AND h=13,c,0),
	day2hour14 = day2hour14 + IF(d=2 AND h=14,c,0),
	day2hour15 = day2hour15 + IF(d=2 AND h=15,c,0),
	day2hour16 = day2hour16 + IF(d=2 AND h=16,c,0),
	day2hour17 = day2hour17 + IF(d=2 AND h=17,c,0),
	day2hour18 = day2hour18 + IF(d=2 AND h=18,c,0),
	day2hour19 = day2hour19 + IF(d=2 AND h=19,c,0),
	day2hour20 = day2hour20 + IF(d=2 AND h=20,c,0),
	day2hour21 = day2hour21 + IF(d=2 AND h=21,c,0),
	day2hour22 = day2hour22 + IF(d=2 AND h=22,c,0),
	day2hour23 = day2hour23 + IF(d=2 AND h=23,c,0),
	day3hour0  = day3hour0  + IF(d=3 AND h=0 ,c,0),
	day3hour1  = day3hour1  + IF(d=3 AND h=1 ,c,0),
	day3hour2  = day3hour2  + IF(d=3 AND h=2 ,c,0),
	day3hour3  = day3hour3  + IF(d=3 AND h=3 ,c,0),
	day3hour4  = day3hour4  + IF(d=3 AND h=4 ,c,0),
	day3hour5  = day3hour5  + IF(d=3 AND h=5 ,c,0),
	day3hour6  = day3hour6  + IF(d=3 AND h=6 ,c,0),
	day3hour7  = day3hour7  + IF(d=3 AND h=7 ,c,0),
	day3hour8  = day3hour8  + IF(d=3 AND h=8 ,c,0),
	day3hour9  = day3hour9  + IF(d=3 AND h=9 ,c,0),
	day3hour10 = day3hour10 + IF(d=3 AND h=10,c,0),
	day3hour11 = day3hour11 + IF(d=3 AND h=11,c,0),
	day3hour12 = day3hour12 + IF(d=3 AND h=12,c,0),
	day3hour13 = day3hour13 + IF(d=3 AND h=13,c,0),
	day3hour14 = day3hour14 + IF(d=3 AND h=14,c,0),
	day3hour15 = day3hour15 + IF(d=3 AND h=15,c,0),
	day3hour16 = day3hour16 + IF(d=3 AND h=16,c,0),
	day3hour17 = day3hour17 + IF(d=3 AND h=17,c,0),
	day3hour18 = day3hour18 + IF(d=3 AND h=18,c,0),
	day3hour19 = day3hour19 + IF(d=3 AND h=19,c,0),
	day3hour20 = day3hour20 + IF(d=3 AND h=20,c,0),
	day3hour21 = day3hour21 + IF(d=3 AND h=21,c,0),
	day3hour22 = day3hour22 + IF(d=3 AND h=22,c,0),
	day3hour23 = day3hour23 + IF(d=3 AND h=23,c,0),
	day4hour0  = day4hour0  + IF(d=4 AND h=0 ,c,0),
	day4hour1  = day4hour1  + IF(d=4 AND h=1 ,c,0),
	day4hour2  = day4hour2  + IF(d=4 AND h=2 ,c,0),
	day4hour3  = day4hour3  + IF(d=4 AND h=3 ,c,0),
	day4hour4  = day4hour4  + IF(d=4 AND h=4 ,c,0),
	day4hour5  = day4hour5  + IF(d=4 AND h=5 ,c,0),
	day4hour6  = day4hour6  + IF(d=4 AND h=6 ,c,0),
	day4hour7  = day4hour7  + IF(d=4 AND h=7 ,c,0),
	day4hour8  = day4hour8  + IF(d=4 AND h=8 ,c,0),
	day4hour9  = day4hour9  + IF(d=4 AND h=9 ,c,0),
	day4hour10 = day4hour10 + IF(d=4 AND h=10,c,0),
	day4hour11 = day4hour11 + IF(d=4 AND h=11,c,0),
	day4hour12 = day4hour12 + IF(d=4 AND h=12,c,0),
	day4hour13 = day4hour13 + IF(d=4 AND h=13,c,0),
	day4hour14 = day4hour14 + IF(d=4 AND h=14,c,0),
	day4hour15 = day4hour15 + IF(d=4 AND h=15,c,0),
	day4hour16 = day4hour16 + IF(d=4 AND h=16,c,0),
	day4hour17 = day4hour17 + IF(d=4 AND h=17,c,0),
	day4hour18 = day4hour18 + IF(d=4 AND h=18,c,0),
	day4hour19 = day4hour19 + IF(d=4 AND h=19,c,0),
	day4hour20 = day4hour20 + IF(d=4 AND h=20,c,0),
	day4hour21 = day4hour21 + IF(d=4 AND h=21,c,0),
	day4hour22 = day4hour22 + IF(d=4 AND h=22,c,0),
	day4hour23 = day4hour23 + IF(d=4 AND h=23,c,0),
	day5hour0  = day5hour0  + IF(d=5 AND h=0 ,c,0),
	day5hour1  = day5hour1  + IF(d=5 AND h=1 ,c,0),
	day5hour2  = day5hour2  + IF(d=5 AND h=2 ,c,0),
	day5hour3  = day5hour3  + IF(d=5 AND h=3 ,c,0),
	day5hour4  = day5hour4  + IF(d=5 AND h=4 ,c,0),
	day5hour5  = day5hour5  + IF(d=5 AND h=5 ,c,0),
	day5hour6  = day5hour6  + IF(d=5 AND h=6 ,c,0),
	day5hour7  = day5hour7  + IF(d=5 AND h=7 ,c,0),
	day5hour8  = day5hour8  + IF(d=5 AND h=8 ,c,0),
	day5hour9  = day5hour9  + IF(d=5 AND h=9 ,c,0),
	day5hour10 = day5hour10 + IF(d=5 AND h=10,c,0),
	day5hour11 = day5hour11 + IF(d=5 AND h=11,c,0),
	day5hour12 = day5hour12 + IF(d=5 AND h=12,c,0),
	day5hour13 = day5hour13 + IF(d=5 AND h=13,c,0),
	day5hour14 = day5hour14 + IF(d=5 AND h=14,c,0),
	day5hour15 = day5hour15 + IF(d=5 AND h=15,c,0),
	day5hour16 = day5hour16 + IF(d=5 AND h=16,c,0),
	day5hour17 = day5hour17 + IF(d=5 AND h=17,c,0),
	day5hour18 = day5hour18 + IF(d=5 AND h=18,c,0),
	day5hour19 = day5hour19 + IF(d=5 AND h=19,c,0),
	day5hour20 = day5hour20 + IF(d=5 AND h=20,c,0),
	day5hour21 = day5hour21 + IF(d=5 AND h=21,c,0),
	day5hour22 = day5hour22 + IF(d=5 AND h=22,c,0),
	day5hour23 = day5hour23 + IF(d=5 AND h=23,c,0),
	day6hour0  = day6hour0  + IF(d=6 AND h=0 ,c,0),
	day6hour1  = day6hour1  + IF(d=6 AND h=1 ,c,0),
	day6hour2  = day6hour2  + IF(d=6 AND h=2 ,c,0),
	day6hour3  = day6hour3  + IF(d=6 AND h=3 ,c,0),
	day6hour4  = day6hour4  + IF(d=6 AND h=4 ,c,0),
	day6hour5  = day6hour5  + IF(d=6 AND h=5 ,c,0),
	day6hour6  = day6hour6  + IF(d=6 AND h=6 ,c,0),
	day6hour7  = day6hour7  + IF(d=6 AND h=7 ,c,0),
	day6hour8  = day6hour8  + IF(d=6 AND h=8 ,c,0),
	day6hour9  = day6hour9  + IF(d=6 AND h=9 ,c,0),
	day6hour10 = day6hour10 + IF(d=6 AND h=10,c,0),
	day6hour11 = day6hour11 + IF(d=6 AND h=11,c,0),
	day6hour12 = day6hour12 + IF(d=6 AND h=12,c,0),
	day6hour13 = day6hour13 + IF(d=6 AND h=13,c,0),
	day6hour14 = day6hour14 + IF(d=6 AND h=14,c,0),
	day6hour15 = day6hour15 + IF(d=6 AND h=15,c,0),
	day6hour16 = day6hour16 + IF(d=6 AND h=16,c,0),
	day6hour17 = day6hour17 + IF(d=6 AND h=17,c,0),
	day6hour18 = day6hour18 + IF(d=6 AND h=18,c,0),
	day6hour19 = day6hour19 + IF(d=6 AND h=19,c,0),
	day6hour20 = day6hour20 + IF(d=6 AND h=20,c,0),
	day6hour21 = day6hour21 + IF(d=6 AND h=21,c,0),
	day6hour22 = day6hour22 + IF(d=6 AND h=22,c,0),
	day6hour23 = day6hour23 + IF(d=6 AND h=23,c,0),
	totalHits = totalHits + c
	;

ALTER TABLE `wD_Sessions` ADD COLUMN `firstRequest` TIMESTAMP NOT NULL DEFAULT current_timestamp() AFTER `lastRequest`;
ALTER TABLE `wD_AccessLog` ADD COLUMN `firstRequest` TIMESTAMP NOT NULL DEFAULT current_timestamp() AFTER `lastRequest`;
UPDATE wD_AccessLog SET firstRequest = lastRequest;

ALTER TABLE `wD_Misc` CHANGE COLUMN `Name` `Name` ENUM('Version','Hits','Panic','Notice','Maintenance','LastProcessTime','GamesNew','GamesActive','GamesFinished','RankingPlayers','OnlinePlayers','ActivePlayers','TotalPlayers','ErrorLogs','GamesPaused','GamesOpen','GamesCrashed','LastModAction','ForumThreads','ThreadActiveThreshold','ThreadAliveThreshold','GameFeaturedThreshold','LastGroupUpdate','LastStatsUpdate','LastMessageID') NOT NULL COLLATE 'utf8mb3_general_ci' FIRST;
INSERT INTO wD_Misc (Name,value) VALUES ('LastMessageID', 0);

ALTER TABLE `wD_UserConnections`
ADD COLUMN `gameMessageCount` INT(11) NOT NULL DEFAULT '0' AFTER `totalHits`,
ADD COLUMN `gameMessageLength` INT(11) NOT NULL DEFAULT '0' AFTER `gameMessageCount`;

ALTER TABLE `wD_UserCodeConnections`
CHANGE COLUMN `type` `type` ENUM('Cookie','IP','Fingerprint','FingerprintPro','MessageCount','MessageLength') NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `userID`;

ALTER TABLE `wD_Sessions` CHANGE `cookieCode` `cookieCode` BIGINT(10) UNSIGNED NOT NULL; 
ALTER TABLE `wD_AccessLog` CHANGE `cookieCode` `cookieCode` BIGINT(10) UNSIGNED NOT NULL; 

CREATE TABLE IF NOT EXISTS `wD_IPLookups` (
  `ipCode` binary(16) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `security` set('vpn','proxy','tor','relay') DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `region` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `continent` varchar(50) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `network` varchar(50) DEFAULT NULL,
  `autonomous_system_number` varchar(50) DEFAULT NULL,
  `autonomous_system_organization` varchar(50) DEFAULT NULL,
  `timeInserted` bigint(20) UNSIGNED NOT NULL,
  `timeLastHit` bigint(20) UNSIGNED NOT NULL,
  `timeLookedUp` bigint(20) UNSIGNED DEFAULT NULL,
  `hits` int(10) UNSIGNED DEFAULT 0,
  PRIMARY KEY (`ipCode`)
) ENGINE=InnoDB;


DELETE FROM wD_DATC WHERE testID IN (902,903,904);
DELETE FROM wD_DATCOrders WHERE testID IN (902,903,904);

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES
(902, 1, 'wD.Test.2', 'Testing for an adjudication error regarding self dislodgement and paradoxes', 'NotPassed');

INSERT INTO wD_DATCOrders (testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, criteria, viaConvoy, legal) VALUES 
(902, 5, 'Army', 19, 'Move', 20, NULL, 'Hold', 'No', 'Yes'),
(902, 5, 'Army', 20, 'Move', 22, NULL, 'Hold', 'No', 'Yes'),
(902, 5, 'Fleet', 22, 'Move', 69, NULL, 'Hold', 'No', 'Yes'),
(902, 7, 'Army', 24, 'Move', 22, NULL, 'Hold', 'No', 'Yes'),
(902, 7, 'Fleet', 69, 'Move', 80, NULL, 'Hold', 'No', 'Yes'),
(902, 3, 'Fleet', 23, 'Support Move', 22, 20, 'Hold', 'No', 'Yes');

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES
(903, 1, 'wD.Test.3', 'Testing for an adjudication error regarding self dislodgement and paradoxes', 'NotPassed');

INSERT INTO wD_DATCOrders (testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, criteria, viaConvoy, legal) VALUES 
(903, 5, 'Army', 19, 'Move', 20, NULL, 'Hold', 'No', 'Yes'),
(903, 5, 'Army', 20, 'Move', 22, NULL, 'Hold', 'No', 'Yes'),
(903, 5, 'Fleet', 22, 'Move', 69, NULL, 'Hold', 'No', 'Yes'),
(903, 7, 'Army', 24, 'Move', 22, NULL, 'Hold', 'No', 'Yes'),
(903, 5, 'Fleet', 69, 'Move', 21, NULL, 'Hold', 'No', 'Yes'),
(903, 7, 'Fleet', 21, 'Move', 20, NULL, 'Hold', 'No', 'Yes'),
(903, 3, 'Fleet', 23, 'Support Move', 22, 20, 'Hold', 'No', 'Yes'),
(903, 3, 'Fleet', 24, 'Support Move', 69, 22, 'Hold', 'No', 'Yes');


CREATE TABLE `wD_UserIdentity` (
`id` INT UNSIGNED NOT NULL,
`userID` MEDIUMINT(8) UNSIGNED NOT NULL,
`identityType` ENUM('facebook','google','youtube','instagram','github','twitter','playdiplomacy',
'backstabbr','vdiplomacy','webdiplomacyFork','paypal','sms','photo','relationshipDeclared',
'relationshipModChecked','longTimePlayer','forumMember') NOT NULL,
`systemVerified` TINYINT UNSIGNED NOT NULL DEFAULT 0,
`systemVerifiedTime` BIGINT UNSIGNED NULL DEFAULT NULL,
`timeCreated` BIGINT UNSIGNED NOT NULL,
`identityText` VARCHAR(500) NULL DEFAULT NULL,
`identityNumber` BIGINT UNSIGNED NULL DEFAULT NULL,
`modVerified` TINYINT UNSIGNED NOT NULL DEFAULT 0,
`modVerifiedTime` TINYINT UNSIGNED NULL DEFAULT NULL,
`modRequestedTime` BIGINT UNSIGNED NULL DEFAULT NULL,
`modRequestedUserID` BIGINT UNSIGNED NULL DEFAULT NULL,
`modUserID` MEDIUMINT UNSIGNED NULL DEFAULT NULL,
`timeSubmitted` BIGINT UNSIGNED NULL DEFAULT NULL,
`modRequestedFromGroupID` INT UNSIGNED NULL DEFAULT NULL,
`isDirty` TINYINT UNSIGNED NOT NULL DEFAULT 0,
`score` TINYINT NOT NULL DEFAULT 0,
`userComment` VARCHAR(500) NULL DEFAULT NULL,
`modComment` VARCHAR(500) NULL DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX `userID_identityType` (`userID`, `identityType`)
)
ENGINE=InnoDB
;

ALTER TABLE `wD_Games` ADD COLUMN `minimumIdentityScore` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `minimumNMRScore`;

ALTER TABLE `wD_Users` ADD COLUMN `identityScore` TINYINT(3) UNSIGNED;

-- Add a column to allow a single bot to play as multiple bot user accounts to reduce memory requirements by adding an offset
-- to game IDs.
ALTER TABLE wD_ApiKeys ADD COLUMN multiplexOffset INT UNSIGNED NULL;