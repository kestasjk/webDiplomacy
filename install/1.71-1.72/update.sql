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

ALTER TABLE `wD_Sessions` ADD COLUMN `firstRequest` TIMESTAMP NOT NULL DEFAULT current_timestamp() AFTER `lastRequest`;
ALTER TABLE `wD_AccessLog` ADD COLUMN `firstRequest` TIMESTAMP NOT NULL DEFAULT current_timestamp() AFTER `lastRequest`;
UPDATE wD_AccessLog SET firstRequest = lastRequest;

ALTER TABLE `wD_Misc` CHANGE COLUMN `Name` `Name` ENUM('Version','Hits','Panic','Notice','Maintenance','LastProcessTime','GamesNew','GamesActive','GamesFinished','RankingPlayers','OnlinePlayers','ActivePlayers','TotalPlayers','ErrorLogs','GamesPaused','GamesOpen','GamesCrashed','LastModAction','ForumThreads','ThreadActiveThreshold','ThreadAliveThreshold','GameFeaturedThreshold','LastGroupUpdate','LastStatsUpdate','LastMessageID') NOT NULL COLLATE 'utf8mb3_general_ci' FIRST;
INSERT INTO wD_Misc (Name,value) VALUES ('LastMessageID', 0);

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

ALTER TABLE `wD_Games` ADD COLUMN `minimumIdentityScore` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wD_Games` ADD COLUMN `minimumNMRScore` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wD_Backup_Games` ADD COLUMN `minimumIdentityScore` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wD_Backup_Games` ADD COLUMN `minimumNMRScore` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `wD_Users` ADD COLUMN `identityScore` TINYINT(3) UNSIGNED;

-- Add a column to allow a single bot to play as multiple bot user accounts to reduce memory requirements by adding an offset
-- to game IDs.
ALTER TABLE wD_ApiKeys ADD COLUMN multiplexOffset INT UNSIGNED NULL;

 ALTER TABLE `wD_ApiKeys` DROP PRIMARY KEY; 
 ALTER TABLE `wD_ApiKeys` ADD PRIMARY KEY(`apiKey`, `userID`); 
  ALTER TABLE `wD_ApiKeys` DROP INDEX `apiKey`; 

CREATE TABLE IF NOT EXISTS `wD_Member_Delegate` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` mediumint(8) UNSIGNED NOT NULL,
  `userIDDelegatedTo` mediumint(8) UNSIGNED NOT NULL,
  `gameID` mediumint(8) UNSIGNED NOT NULL,
  `countryID` mediumint(8) UNSIGNED NOT NULL,
  `delegationStartTime` int(8) UNSIGNED DEFAULT NULL,
  `delegationEndTime` int(10) UNSIGNED DEFAULT NULL,
  `createdTime` int(10) UNSIGNED DEFAULT NULL,
  `acceptedTime` int(10) UNSIGNED DEFAULT NULL,
  `cancelledTime` int(10) UNSIGNED DEFAULT NULL,
  `expiredTime` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
	INDEX ( `userID` ) ,
	INDEX ( `userIDDelegatedTo` ),
	INDEX ( `delegationEndTime` )
) ENGINE=InnoDB ;

ALTER TABLE `wD_ApiKeys`
	ADD COLUMN `description` VARCHAR(500) NULL DEFAULT NULL,
	ADD COLUMN `label` VARCHAR(500) NULL DEFAULT NULL;

ALTER TABLE `wD_ApiPermissions`
	CHANGE COLUMN `submitOrdersForUserInCD` `submitOrdersForUserInCD` ENUM('No','Yes') NOT NULL DEFAULT 'No',
	CHANGE COLUMN `listGamesWithPlayersInCD` `listGamesWithPlayersInCD` ENUM('No','Yes') NOT NULL DEFAULT 'No',
	ADD COLUMN `getRedactedMessages` ENUM('No','Yes') NULL DEFAULT 'No',
	ADD COLUMN `submitOrdersForDelegatedMembers` ENUM('No','Yes') NULL DEFAULT 'No',
	ADD COLUMN `submitMessages` ENUM('No','Yes') NULL DEFAULT 'No',
	ADD COLUMN `voteDraw` ENUM('No','Yes') NULL DEFAULT 'No',
	ADD COLUMN `playBotsVsHuman` ENUM('No','Yes') NULL DEFAULT 'No',
	ADD COLUMN `playBotVsHuman` ENUM('No','Yes') NULL DEFAULT 'No',
	ADD COLUMN `minimumPhaseLength` MEDIUMINT NULL DEFAULT 3600,
	ADD COLUMN `variantIDs` VARCHAR(50) NULL DEFAULT '';

UPDATE wD_ApiPermissions SET getRedactedMessages='No', submitOrdersForDelegatedMembers='No', submitMessages='No', voteDraw='No', playBotsVsHuman='No', playBotVsHuman='No', minimumPhaseLength=3600, variantIDs='';

ALTER TABLE `wD_ApiPermissions`
	CHANGE COLUMN `getRedactedMessages` `getRedactedMessages` ENUM('No','Yes') NOT NULL DEFAULT 'No',
	CHANGE COLUMN `submitOrdersForDelegatedMembers` `submitOrdersForDelegatedMembers` ENUM('No','Yes') NOT NULL DEFAULT 'No',
	CHANGE COLUMN `submitMessages` `submitMessages` ENUM('No','Yes') NOT NULL DEFAULT 'No',
	CHANGE COLUMN `voteDraw` `voteDraw` ENUM('No','Yes') NOT NULL DEFAULT 'No',
	CHANGE COLUMN `playBotsVsHuman` `playBotsVsHuman` ENUM('No','Yes') NOT NULL DEFAULT 'No',
	CHANGE COLUMN `playBotVsHuman` `playBotVsHuman` ENUM('No','Yes') NOT NULL DEFAULT 'No',
	CHANGE COLUMN `minimumPhaseLength` `minimumPhaseLength` MEDIUMINT NOT NULL DEFAULT 3600,
	CHANGE COLUMN `variantIDs` `variantIDs` VARCHAR(50) NOT NULL DEFAULT '';

DROP TABLE IF EXISTS wD_UserCodeConnections;
CREATE TABLE `wD_UserCodeConnections` (
	`type` ENUM('Cookie','IP','IPVPN','Fingerprint','FingerprintPro','MessageCount','MessageLength','LatLon','Network','City','UserTurn','UserTurnMissed') NOT NULL,
	`userID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`code` BINARY(16) NOT NULL,
	`earliest` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	`latest` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`count` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`previousCount` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`isNew` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
	`isUpdated` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
	PRIMARY KEY (`userID`, `type`, `code`) USING BTREE,
	INDEX `typeCode` (`type`, `code`) USING BTREE,
	INDEX `updatedTypeCode` (`type`, `isUpdated`, `code`) USING BTREE
)
ENGINE=InnoDB
;
DROP TABLE IF EXISTS wD_UserCodeConnectionMatches;
CREATE TABLE `wD_UserCodeConnectionMatches` (
	`type` ENUM('Cookie','IP','IPVPN','Fingerprint','FingerprintPro','MessageCount','MessageLength','LatLon','Network','City','UserTurn','UserTurnMissed') NOT NULL,
	`userIDFrom` MEDIUMINT(8) UNSIGNED NOT NULL,
	`userIDTo` MEDIUMINT(8) UNSIGNED NOT NULL,
	`matches` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`previousMatches` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`matchCount` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`previousMatchCount` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`isNew` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
	`isUpdated` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
	PRIMARY KEY (`type`,`userIDFrom`,`userIDTo`) USING BTREE,
	INDEX `typeUpdatedFrom` (`type`, `isUpdated`, `userIDFrom`) USING BTREE,
	INDEX `typeUpdatedTo` (`type`, `isUpdated`, `userIDTo`) USING BTREE
)
ENGINE=InnoDB
;
DROP TABLE IF EXISTS wD_UserConnections;
CREATE TABLE IF NOT EXISTS `wD_UserConnections` (
  `userID` mediumint(8) UNSIGNED NOT NULL,
  `modLastCheckedBy` mediumint(8) UNSIGNED DEFAULT NULL,
  `modLastCheckedOn` int(10) UNSIGNED DEFAULT NULL,
  `matchesLastUpdatedOn` int(10) UNSIGNED DEFAULT NULL,
  `totalHits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `period0` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period1` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period2` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period3` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period4` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period5` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period6` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period7` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period8` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period9` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period10` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period11` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period12` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period13` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period14` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period15` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period16` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period17` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period18` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period19` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period20` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period21` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period22` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `period23` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedCookie` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedCookieTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedOtherCookieTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countCookie` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countCookieTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedIP` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedIPTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedOtherIPTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countIP` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countIPTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countIPVPN` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countIPVPNTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedFingerprint` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedFingerprintTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedOtherFingerprintTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countFingerprint` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countFingerprintTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedFingerprintPro` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedFingerprintProTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedOtherFingerprintProTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countFingerprintPro` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countFingerprintProTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countLatLon` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countLatLonTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countNetwork` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countNetworkTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countCity` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countCityTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countUserTurn` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countUserTurnTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countMessageLength` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countMessageLengthTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countMessageCount` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `countMessageCountTotal` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `matchedUserTurnMissed` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `matchedUserTurnMissedTotal` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `matchedOtherUserTurnMissedTotal` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `matchedUserTurn` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `matchedUserTurnTotal` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `matchedOtherUserTurnTotal` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `countUserTurnMissed` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `countUserTurnMissedTotal` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `suspicionRelationshipsMod` mediumint(8) UNSIGNED DEFAULT NULL,
  `suspicionRelationshipsPeer` mediumint(8) UNSIGNED DEFAULT NULL,
  `suspicionIPLookup` mediumint(8) UNSIGNED DEFAULT NULL,
  `suspicionLocationLookup` mediumint(8) UNSIGNED DEFAULT NULL,
  `suspicionCookieCode` mediumint(8) UNSIGNED DEFAULT NULL,
  `suspicionBrowserFingerprint` mediumint(8) UNSIGNED DEFAULT NULL,
  `suspicionFingerprintPro` mediumint(8) UNSIGNED DEFAULT NULL,
  `suspicionGameActivity` mediumint(8) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB ;

UPDATE wD_UserCodeConnections SET isNew = 1, isUpdated = 1, previousCount = 0;

-- Prevent full table scanes in phpBB like module
ALTER TABLE IF EXISTS `phpbb_posts_likes` ADD INDEX `CountLikes` (`user_id`, `post_id`);

ALTER TABLE `wD_UserOptions` ADD COLUMN `mapUI` enum('Point and click','Dropdown menus') NOT NULL DEFAULT 'Point and click';
ALTER TABLE `wD_UserOptions` CHANGE COLUMN `displayUpcomingLive` `displayUpcomingLive` enum('No','Yes') NOT NULL DEFAULT 'No';

UPDATE wD_UserOptions SET mapUI = 'Point and click';
UPDATE wD_UserOptions SET displayUpcomingLive = 'No';

ALTER TABLE wD_Sessions ADD COLUMN `webPushrSID` INTEGER UNSIGNED DEFAULT NULL;
ALTER TABLE wD_Users ADD COLUMN `webPushrSID` INTEGER UNSIGNED DEFAULT NULL;
ALTER TABLE `wD_Games` ADD COLUMN `sandboxCreatedByUserID` MEDIUMINT NULL DEFAULT NULL;
ALTER TABLE `wD_Games` ADD INDEX `sandboxList` (`sandboxCreatedByUserID`);


CREATE TABLE `wD_Backup_Log` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`gameID` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
	`turn` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
	`timestamp` BIGINT UNSIGNED NOT NULL DEFAULT 0,
	`isExported` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	INDEX `timestamp` (`timestamp`)
)
;

ALTER TABLE `wD_Backup_Games` ADD INDEX `gameID` (`id`);
ALTER TABLE `wD_Backup_Members` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_MovesArchive` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_Orders` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_TerrStatus` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_TerrStatusArchive` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_Units` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_GameMessages` ADD INDEX `gameID` (`gameID`);


ALTER TABLE `wD_Backup_Games` ADD INDEX `gameID` (`id`);
ALTER TABLE `wD_Backup_Members` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_MovesArchive` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_Orders` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_TerrStatus` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_TerrStatusArchive` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_Units` ADD INDEX `gameID` (`gameID`);
ALTER TABLE `wD_Backup_GameMessages` ADD INDEX `gameID` (`gameID`);


ALTER TABLE `wD_Misc` CHANGE COLUMN `Name` `Name` ENUM('Version','Hits','Panic','Notice','Maintenance','LastProcessTime','GamesNew','GamesActive','GamesFinished','RankingPlayers','OnlinePlayers','ActivePlayers','TotalPlayers','ErrorLogs','GamesPaused','GamesOpen','GamesCrashed','LastModAction','ForumThreads','ThreadActiveThreshold','ThreadAliveThreshold','GameFeaturedThreshold','LastGroupUpdate','LastStatsUpdate','LastMessageID','LastNMRWarningUpdate','LastConnectionUpdate','LastBackupUpdate') NOT NULL COLLATE 'utf8mb3_general_ci' FIRST;
INSERT INTO wD_Misc (`Name`,`Value`) VALUES ('LastNMRWarningUpdate',0);
INSERT INTO wD_Misc (`Name`,`Value`) VALUES ('LastConnectionUpdate',0);
INSERT INTO wD_Misc (`Name`,`Value`) VALUES ('LastBackupUpdate',0);

ALTER TABLE wD_ModForumMessages ADD gameTurn smallint(5) unsigned NULL, ADD isUserRead tinyint unsigned NULL, ADD isUserReplied tinyint unsigned NULL, ADD isUserMustReply tinyint unsigned NULL, ADD isModRead tinyint unsigned NULL, ADD isModReplied tinyint unsigned NULL, ADD isThanked tinyint unsigned NULL;
ALTER TABLE wD_TurnDate ADD INDEX(`gameID`,`turnDateTime`);
ALTER TABLE wD_ModForumMessages ADD isThanked tinyint unsigned NULL;

UPDATE wD_ModForumMessages fm 
INNER JOIN (
    SELECT fm.id, MAX(td.turn) AS turn 
    FROM wD_TurnDate td 
    INNER JOIN wD_ModForumMessages fm ON fm.gameID = td.gameID AND fm.timeSent > td.turnDateTime 
    GROUP BY fm.id
) t ON t.id = fm.id
SET fm.gameTurn = t.turn;

UPDATE wD_ModForumMessages SET isUserRead = 1, isUserReplied = 1, isModRead = 1, isModReplied = 1, isUserMustReply = 0, isThanked = 0;
DROP TABLE `wD_ForceReply`;
ALTER TABLE wD_ModForumMessages ADD INDEX(`type`,`assigned`,`isModRead`);

ALTER TABLE wD_ModForumMessages ADD latestReplySentTime int unsigned NULL;
UPDATE wD_ModForumMessages SET latestReplySentTime = timeSent;
UPDATE wD_ModForumMessages fm 
INNER JOIN (
  SELECT toID, MAX(timeSent) latestReplySentTime 
  FROM wD_ModForumMessages 
  WHERE type = 'ThreadReply'
  GROUP BY toID
) x ON fm.id = x.toID
SET fm.latestReplySentTime = x.latestReplySentTime;

ALTER TABLE wD_ModForumMessages CHANGE COLUMN `status` `status` enum('New','Open','Resolved','Bugs','Sticky','Deleted');
UPDATE wD_ModForumMessages SET status = 'Deleted' WHERE status = '';

ALTER TABLE wD_Groups ADD gameTurn smallint(5) unsigned NULL;

UPDATE wD_Groups fm 
INNER JOIN (
    SELECT fm.id, MAX(td.turn) AS turn 
    FROM wD_TurnDate td 
    INNER JOIN wD_Groups fm ON fm.gameID = td.gameID AND fm.timeCreated > td.turnDateTime 
    GROUP BY fm.id
) t ON t.id = fm.id
SET fm.gameTurn = t.turn;


ALTER TABLE `wD_UserConnections` 
  ADD `matchedCookieCount` int(8) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedOtherCookieCount` int(8) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedIPCount` int(8) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedOtherIPCount` int(8) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedFingerprintCount` int(8) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedOtherFingerprintCount` int(8) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedFingerprintProCount` int(8) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedOtherFingerprintProCount` int(8) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedUserTurnMissedCount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedOtherUserTurnMissedCount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedUserTurnCount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  ADD `matchedOtherUserTurnCount` int(10) UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE wD_UserCodeConnectionMatches ADD earliestFrom TIMESTAMP NULL, ADD latestFrom TIMESTAMP NULL, ADD earliestTo TIMESTAMP NULL, ADD latestTo TIMESTAMP NULL;

ALTER TABLE wD_ApiKeys ADD username varchar(150) NULL, ADD description varchar(500) NULL, 
  ADD lastActive bigint UNSIGNED NULL, 
  ADD lastOrder bigint unsigned NULL, 
  ADD lastMessage bigint unsigned null, 
  ADD lastVote bigint unsigned null, 
  ADD messagesSent int unsigned null, 
  ADD phasesPlayed int unsigned null, 
  ADD gamesPlaying int unsigned null, 
  ADD gamesWon int unsigned null, 
  ADD gamesDrawn int unsigned null, 
  ADD gamesLostToHuman int unsigned null, 
  ADD gamesLostToBot int unsigned null;

ALTER TABLE wD_ApiKeys 
  ADD supplyCenters int unsigned null, 
  ADD ordersSaved int unsigned null, 
  ADD ordersCompleted int unsigned null, 
  ADD ordersReady int unsigned null;

UPDATE wD_ApiKeys a INNER JOIN wD_Users u ON u.id = a.userID SET a.username = u.username;
UPDATE wD_ApiKeys a SET a.lastActive = a.lastHit;
/*
UPDATE wD_ApiKeys a INNER JOIN (
  SELECT a.userID, COUNT(*) messageCount
  FROM wD_ApiKeys a
  INNER JOIN wD_Members m ON m.userID = a.userID
  INNER JOIN wD_GameMessages gm ON gm.gameID = m.gameID AND gm.fromCountryID = m.countryID
  GROUP BY a.userID
) msg ON msg.userID = a.userID
SET a.messagesSent = msg.messageCount;
*/

UPDATE wD_ApiKeys a 
INNER JOIN (
  SELECT a.userID, 
    SUM(m.gameMessagesSent) messagesSent, 
    SUM(g.turn) phasesPlayed,
    SUM(IF(m.status = 'Playing',1,0)) gamesPlaying, 
    SUM(IF(m.status = 'Won',1,0)) gamesWon, 
    SUM(IF(m.status = 'Drawn',1,0)) gamesDrawn,
    SUM(IF(m.status = 'Defeated' AND ma.id IS NOT NULL AND ama.userID IS NULL,1,0)) gamesLostToHuman, 
    SUM(IF(m.status = 'Defeated' AND ma.id IS NOT NULL AND ama.userID IS NOT NULL,1,0)) gamesLostToBot,
    SUM(m.supplyCenterNo) supplyCenters,
    SUM(IF(m.status = 'Playing' AND NOT m.orderStatus LIKE '%None%',1,0)) ordersNeeded,
    -- orderCount.orderCount -- Verifies that ordersNeeded is correct
    SUM(IF(m.status = 'Playing' AND (NOT m.orderStatus LIKE '%None%') AND m.orderStatus LIKE '%Saved%',1,0)) ordersSaved,
    SUM(IF(m.status = 'Playing' AND (NOT m.orderStatus LIKE '%None%') AND m.orderStatus LIKE '%Completed%',1,0)) ordersCompleted,
    SUM(IF(m.status = 'Playing' AND (NOT m.orderStatus LIKE '%None%') AND m.orderStatus LIKE '%Ready%',1,0)) ordersReady,
    MAX(g.turn) oldestGameTurn,
    MIN(g.turn) newestGameTurn,
    MIN(g.id) oldestGameID,
    MAX(g.id) newestGameID,
  FROM wD_ApiKeys a
  INNER JOIN wD_Members m ON m.userID = a.userID
  INNER JOIN wD_Games g ON g.id = m.gameID
  LEFT JOIN wD_Members ma ON ma.gameID = g.id AND ma.status = 'Won' AND ma.id <> m.id
  LEFT JOIN wD_ApiKeys ama ON ama.userID = ma.userID
  -- LEFT JOIN (SELECT m.userID, COUNT(DISTINCT oc.gameID) orderCount FROM wD_Orders oc INNER JOIN wD_Members m ON m.gameID = oc.gameID AND m.countryID = oc.countryID GROUP BY m.userID) orderCount ON orderCount.userID = a.userID
  WHERE a.userID > 181040
  GROUP BY a.userID
) sts ON sts.userID = a.userID
SET a.lastActive = a.lastHit,
  a.messagesSent = sts.messagesSent,
  a.phasesPlayed = sts.phasesPlayed,
  a.gamesPlaying = sts.gamesPlaying,
  a.gamesWon = sts.gamesWon,
  a.gamesDrawn = sts.gamesDrawn,
  a.gamesLostToHuman = sts.gamesLostToHuman,
  a.gamesLostToBot = sts.gamesLostToBot,
  a.ordersSaved = sts.ordersSaved,
  a.ordersCompleted = sts.ordersCompleted,
  a.ordersReady = sts.ordersReady;

orderStatus        | set('None','Saved','Completed','Ready')
status             | enum('Playing','Defeated','Left','Won','Drawn','Survived','Resigned')

playerTypes              | enum('Members','Mixed','MemberVsBots')


SELECT g.id, IF(a.userID IS NULL,1,0) fromHuman, gm.timeSent, gm.turn, gm.phaseMarker, gm.fromCountryID, gm.toCountryID, gm.message
FROM wD_Games g
INNER JOIN wD_Members m ON m.gameID = g.id
LEFT JOIN wD_ApiKeys a ON a.userID = m.userID AND a.multiplexOffset IS NOT NULL
INNER JOIN wD_GameMessages gm ON gm.gameID = g.id AND gm.fromCountryID = m.countryID
WHERE g.id IN (
  SELECT DISTINCT gameID FROM wD_Members m WHERE m.userID IN (SELECT userID FROM wD_ApiKeys WHERE multiplexOffset IS NOT NULL)
)
ORDER BY g.id, gm.timeSent;

-- Add a like count to the users table to prevent having to constantly count for each post:
ALTER TABLE `phpbb_users` ADD `webdip_like_count` INT(0) UNSIGNED NULL AFTER `webdip_user_id`;

-- Calculate the initial counts:
UPDATE phpbb_users u
SET webdip_like_count = 0
UPDATE phpbb_users u
INNER JOIN (
    SELECT p.poster_id, COUNT(*) AS likes
    FROM phpbb_posts p
    INNER JOIN phpbb_posts_likes l ON l.post_id = p.post_id
    GROUP BY p.poster_id
) x ON x.poster_id = u.user_id
SET u.webdip_like_count = x.likes;
-- This will be updated when a like is added or removed, and recounted
-- in total on a daily basis.