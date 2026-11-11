UPDATE `wD_Misc` SET `value` = '185' WHERE `name` = 'Version';

-- Bounty events: scheduled live games with bonus points; see lib/bounty.php and install/1.84-1.85/readme.txt.

-- A saved, named game configuration. Bounty games are free to enter; every point paid out comes from the bonus,
-- which is the whole pot of each game created from the template.
CREATE TABLE `wD_BountyTemplates` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `variantID` tinyint(3) unsigned NOT NULL,
  `potType` enum('Winner-takes-all','Sum-of-squares') NOT NULL DEFAULT 'Winner-takes-all',
  `phaseMinutes` smallint(5) unsigned NOT NULL,
  `phaseMinutesRB` smallint(5) NOT NULL DEFAULT -1,
  `nextPhaseMinutes` smallint(5) unsigned NOT NULL,
  `phaseSwitchPeriod` smallint(5) NOT NULL DEFAULT -1,
  `anon` enum('Yes','No') NOT NULL DEFAULT 'No',
  `pressType` enum('Regular','PublicPressOnly','NoPress','RulebookPress') NOT NULL DEFAULT 'Regular',
  `drawType` enum('draw-votes-public','draw-votes-hidden') NOT NULL DEFAULT 'draw-votes-public',
  `missingPlayerPolicy` enum('Normal','Wait') NOT NULL DEFAULT 'Normal',
  `minimumReliabilityRating` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `excusedMissedTurns` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `bonus` mediumint(8) unsigned NOT NULL,
  `consolationPoints` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `poolOpenMinutes` smallint(5) unsigned NOT NULL DEFAULT 15,
  `timeCreated` int(10) unsigned NOT NULL,
  `timeModified` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- A template scheduled at a time (UTC): once, or weekly from startTime onwards. A weekly event's occurrences up to
-- skipUntil are skipped.
CREATE TABLE `wD_BountyEvents` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `templateID` mediumint(8) unsigned NOT NULL,
  `startTime` int(10) unsigned NOT NULL,
  `repeats` enum('Once','Weekly') NOT NULL DEFAULT 'Weekly',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `skipUntil` int(10) unsigned NOT NULL DEFAULT 0,
  `timeCreated` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `templateID` (`templateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- The players who asked to be told when an event's pools open
CREATE TABLE `wD_BountyReminders` (
  `eventID` mediumint(8) unsigned NOT NULL,
  `userID` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`eventID`, `userID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- One row per occurrence of an event, created when its pool opens. settings holds the template as it was then.
-- The unique key is what makes a pool open only once per occurrence.
CREATE TABLE `wD_BountyPools` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `eventID` mediumint(8) unsigned NOT NULL,
  `templateID` mediumint(8) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `startTime` int(10) unsigned NOT NULL,
  `status` enum('Open','Done','Failed','Missed') NOT NULL DEFAULT 'Open',
  `settings` text NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `timeClaimed` int(10) unsigned DEFAULT NULL,
  `timeOpened` int(10) unsigned NOT NULL,
  `timeSharded` int(10) unsigned DEFAULT NULL,
  `gamesCreated` smallint(5) unsigned NOT NULL DEFAULT 0,
  `playersPlaced` smallint(5) unsigned NOT NULL DEFAULT 0,
  `playersLeftOver` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eventStart` (`eventID`, `startTime`),
  KEY `statusStart` (`status`, `startTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- The players in a pool. Once the pool is shuffled each row records what happened to that player.
CREATE TABLE `wD_BountyPoolMembers` (
  `poolID` mediumint(8) unsigned NOT NULL,
  `userID` mediumint(8) unsigned NOT NULL,
  `timeEntered` int(10) unsigned NOT NULL,
  `outcome` enum('Waiting','Placed','LeftOver','Ineligible') NOT NULL DEFAULT 'Waiting',
  `gameID` mediumint(8) unsigned DEFAULT NULL,
  `consolationPoints` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`poolID`, `userID`),
  KEY `userOutcome` (`userID`, `outcome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Consolation points for players left out of a bounty game are paid outside any game
ALTER TABLE `wD_PointsTransactions` CHANGE `type` `type` ENUM( 'Supplement', 'Bet', 'Won', 'Returned', 'Trigger', 'Correction', 'Bounty' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

-- A default: a weekly 5-minute full-press Classic game with a 500 point bonus. The event is disabled, and its time
-- (Saturdays 18:00 UTC) is a placeholder; change its time and enable it in the admin CP's Bounty events tab.
INSERT INTO `wD_BountyTemplates` ( `name`, `variantID`, `potType`, `phaseMinutes`, `phaseMinutesRB`, `nextPhaseMinutes`,
    `phaseSwitchPeriod`, `anon`, `pressType`, `drawType`, `missingPlayerPolicy`, `minimumReliabilityRating`,
    `excusedMissedTurns`, `bonus`, `consolationPoints`, `timeCreated`, `timeModified` )
  VALUES ( 'Weekly live bounty', 1, 'Winner-takes-all', 5, -1, 5, -1, 'No', 'Regular', 'draw-votes-public', 'Normal', 0,
    1, 500, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP() );
INSERT INTO `wD_BountyEvents` ( `templateID`, `startTime`, `repeats`, `enabled`, `timeCreated` )
  VALUES ( LAST_INSERT_ID(), 1767463200, 'Weekly', 0, UNIX_TIMESTAMP() );
