/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_AccessLog` (
  `userID` mediumint(8) unsigned NOT NULL,
  `lastRequest` timestamp NOT NULL DEFAULT '1970-01-02 00:00:01',
  `hits` smallint(5) unsigned NOT NULL,
  `cookieCode` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `userAgent` binary(2) NOT NULL,
  KEY `userID` (`userID`),
  KEY `ip` (`ip`),
  KEY `cookieCode` (`cookieCode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_AdminLog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `userID` mediumint(8) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `details` text NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Backup_GameMessages` (
  `id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `timeSent` int(10) unsigned NOT NULL,
  `message` text CHARACTER SET utf8 NOT NULL,
  `turn` smallint(5) unsigned NOT NULL,
  `toCountryID` tinyint(3) unsigned NOT NULL,
  `fromCountryID` tinyint(3) unsigned NOT NULL,
  `gameID` mediumint(8) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Backup_Games` (
  `variantID` tinyint(3) unsigned NOT NULL,
  `id` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `turn` smallint(5) unsigned NOT NULL DEFAULT '0',
  `phase` enum('Finished','Pre-game','Diplomacy','Retreats','Builds') CHARACTER SET utf8 NOT NULL DEFAULT 'Pre-game',
  `processTime` int(10) unsigned DEFAULT NULL,
  `pot` smallint(5) unsigned NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `gameOver` enum('No','Won','Drawn') CHARACTER SET utf8 NOT NULL DEFAULT 'No',
  `processStatus` enum('Not-processing','Processing','Crashed','Paused') CHARACTER SET utf8 NOT NULL DEFAULT 'Not-processing',
  `password` varbinary(16) DEFAULT NULL,
  `potType` enum('Winner-takes-all','Points-per-supply-center') CHARACTER SET utf8 NOT NULL,
  `pauseTimeRemaining` mediumint(8) unsigned DEFAULT NULL,
  `minimumBet` smallint(5) unsigned DEFAULT NULL,
  `phaseMinutes` smallint(5) unsigned NOT NULL DEFAULT '1440',
  `anon` enum('Yes','No') CHARACTER SET utf8 NOT NULL DEFAULT 'No',
  `pressType` enum('Regular','PublicPressOnly','NoPress') CHARACTER SET utf8 NOT NULL DEFAULT 'Regular',
  `attempts` smallint(5) unsigned NOT NULL DEFAULT '0',
  `missingPlayerPolicy` enum('Normal','Strict') CHARACTER SET utf8 NOT NULL DEFAULT 'Normal'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Backup_Members` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `userID` mediumint(8) unsigned NOT NULL,
  `gameID` mediumint(8) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `status` enum('Playing','Defeated','Left','Won','Drawn','Survived','Resigned') CHARACTER SET utf8 NOT NULL DEFAULT 'Playing',
  `timeLoggedIn` int(10) unsigned NOT NULL,
  `bet` mediumint(8) unsigned NOT NULL,
  `missedPhases` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `newMessagesFrom` set('0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59','60','61','62','63') CHARACTER SET utf8 NOT NULL,
  `supplyCenterNo` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `unitNo` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `votes` set('Draw','Pause','Cancel') CHARACTER SET utf8 NOT NULL,
  `pointsWon` mediumint(8) unsigned DEFAULT NULL,
  `gameMessagesSent` mediumint(8) unsigned DEFAULT NULL,
  `orderStatus` set('None','Saved','Completed','Ready') CHARACTER SET utf8 NOT NULL DEFAULT 'None'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Backup_MovesArchive` (
  `gameID` mediumint(8) unsigned NOT NULL,
  `turn` smallint(5) unsigned NOT NULL,
  `terrID` smallint(5) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `unitType` enum('Army','Fleet') CHARACTER SET utf8 DEFAULT NULL,
  `success` enum('Yes','No') CHARACTER SET utf8 NOT NULL,
  `dislodged` enum('Yes','No') CHARACTER SET utf8 NOT NULL DEFAULT 'No',
  `type` enum('Hold','Move','Support hold','Support move','Convoy','Retreat','Disband','Build Army','Build Fleet','Wait','Destroy') CHARACTER SET utf8 NOT NULL,
  `toTerrID` smallint(5) unsigned DEFAULT NULL,
  `fromTerrID` smallint(5) unsigned DEFAULT NULL,
  `viaConvoy` enum('No','Yes') CHARACTER SET utf8 NOT NULL DEFAULT 'No'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Backup_Orders` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `gameID` mediumint(8) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `type` enum('Hold','Move','Support hold','Support move','Convoy','Retreat','Disband','Build Army','Build Fleet','Wait','Destroy') CHARACTER SET utf8 NOT NULL,
  `unitID` int(10) unsigned DEFAULT NULL,
  `toTerrID` smallint(5) unsigned DEFAULT NULL,
  `fromTerrID` smallint(5) unsigned DEFAULT NULL,
  `viaConvoy` enum('No','Yes') CHARACTER SET utf8 DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Backup_TerrStatus` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `terrID` smallint(5) unsigned NOT NULL,
  `occupiedFromTerrID` smallint(5) unsigned DEFAULT NULL,
  `standoff` enum('No','Yes') CHARACTER SET utf8 NOT NULL DEFAULT 'No',
  `gameID` mediumint(8) unsigned NOT NULL,
  `occupyingUnitID` int(10) unsigned DEFAULT NULL,
  `retreatingUnitID` int(10) unsigned DEFAULT NULL,
  `countryID` tinyint(3) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Backup_TerrStatusArchive` (
  `terrID` smallint(5) unsigned NOT NULL,
  `turn` smallint(5) unsigned NOT NULL,
  `standoff` enum('No','Yes') CHARACTER SET utf8 NOT NULL DEFAULT 'No',
  `gameID` mediumint(8) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Backup_Units` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('Army','Fleet') CHARACTER SET utf8 NOT NULL,
  `terrID` smallint(5) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `gameID` mediumint(8) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_BannedNumbers` (
  `number` int(10) unsigned NOT NULL,
  `numberType` enum('CookieCode','IP') NOT NULL,
  `userID` mediumint(8) unsigned NOT NULL,
  `hasResponded` enum('Yes','No') NOT NULL DEFAULT 'No',
  UNIQUE KEY `numberType` (`numberType`,`number`),
  KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Borders` (
  `mapID` tinyint(3) unsigned NOT NULL,
  `fromTerrID` smallint(5) unsigned NOT NULL,
  `toTerrID` smallint(5) unsigned NOT NULL,
  `fleetsPass` enum('No','Yes') NOT NULL,
  `armysPass` enum('No','Yes') NOT NULL,
  KEY `fromTo` (`fromTerrID`,`toTerrID`),
  KEY `toFrom` (`toTerrID`,`fromTerrID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_CivilDisorders` (
  `gameID` smallint(5) unsigned NOT NULL,
  `userID` mediumint(8) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `turn` smallint(5) unsigned NOT NULL,
  `bet` smallint(5) unsigned NOT NULL,
  `SCCount` smallint(5) unsigned NOT NULL,
  KEY `gameID` (`gameID`,`userID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_CoastalBorders` (
  `mapID` tinyint(3) unsigned NOT NULL,
  `fromTerrID` smallint(5) unsigned NOT NULL,
  `toTerrID` smallint(5) unsigned NOT NULL,
  `fleetsPass` enum('No','Yes') NOT NULL,
  `armysPass` enum('No','Yes') NOT NULL,
  KEY `fromTo` (`fromTerrID`,`toTerrID`),
  KEY `toFrom` (`toTerrID`,`fromTerrID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_DATC` (
  `testID` smallint(6) NOT NULL,
  `variantID` tinyint(3) unsigned NOT NULL,
  `testName` char(15) NOT NULL,
  `testDesc` text,
  `status` enum('NotPassed','Passed','Invalid') DEFAULT 'NotPassed',
  PRIMARY KEY (`testID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `wD_DATC` VALUES (101,1,'6.A.1','TEST CASE, MOVING TO AN AREA THAT IS NOT A NEIGHBOUR','NotPassed'),(102,1,'6.A.2','TEST CASE, MOVE ARMY TO SEA','NotPassed'),(103,1,'6.A.3','TEST CASE, MOVE FLEET TO LAND','NotPassed'),(104,1,'6.A.4','TEST CASE, MOVE TO OWN SECTOR','NotPassed'),(105,1,'6.A.5','TEST CASE, MOVE TO OWN SECTOR WITH CONVOY','NotPassed'),(106,1,'6.A.6','TEST CASE, ORDERING A UNIT OF ANOTHER COUNTRY','Invalid'),(107,1,'6.A.7','TEST CASE, ONLY ARMIES CAN BE CONVOYED','NotPassed'),(108,1,'6.A.8','TEST CASE, SUPPORT TO HOLD YOURSELF IS NOT POSSIBLE','NotPassed'),(109,1,'6.A.9','TEST CASE, FLEETS MUST FOLLOW COAST IF NOT ON SEA','NotPassed'),(110,1,'6.A.10','TEST CASE, SUPPORT ON UNREACHABLE DESTINATION NOT POSSIBLE','NotPassed'),(111,1,'6.A.11','TEST CASE, SIMPLE BOUNCE','NotPassed'),(112,1,'6.A.12','TEST CASE, BOUNCE OF THREE UNITS','NotPassed'),(201,1,'6.B.1','TEST CASE, MOVING WITH UNSPECIFIED COAST WHEN COAST IS NECESSARY','NotPassed'),(202,1,'6.B.2','TEST CASE, MOVING WITH UNSPECIFIED COAST WHEN COAST IS NOT NECESSARY','NotPassed'),(203,1,'6.B.3','TEST CASE, MOVING WITH WRONG COAST WHEN COAST IS NOT NECESSARY','Invalid'),(204,1,'6.B.4','TEST CASE, SUPPORT TO UNREACHABLE COAST ALLOWED','NotPassed'),(205,1,'6.B.5','TEST CASE, SUPPORT FROM UNREACHABLE COAST NOT ALLOWED','NotPassed'),(206,1,'6.B.6','TEST CASE, SUPPORT CAN BE CUT WITH OTHER COAST','NotPassed'),(207,1,'6.B.7','TEST CASE, SUPPORTING WITH UNSPECIFIED COAST','Invalid'),(208,1,'6.B.8','TEST CASE, SUPPORTING WITH UNSPECIFIED COAST WHEN ONLY ONE COAST IS POSSIBLE','Invalid'),(209,1,'6.B.9','TEST CASE, SUPPORTING WITH WRONG COAST','Invalid'),(210,1,'6.B.10','TEST CASE, UNIT ORDERED WITH WRONG COAST','Invalid'),(211,1,'6.B.11','TEST CASE, COAST CAN NOT BE ORDERED TO CHANGE','Invalid'),(212,1,'6.B.12','TEST CASE, ARMY MOVEMENT WITH COASTAL SPECIFICATION','Invalid'),(213,1,'6.B.13','TEST CASE, COASTAL CRAWL NOT ALLOWED','NotPassed'),(214,1,'6.B.14','TEST CASE, BUILDING WITH UNSPECIFIED COAST','Invalid'),(301,1,'6.C.1','TEST CASE, THREE ARMY CIRCULAR MOVEMENT','NotPassed'),(302,1,'6.C.2','TEST CASE, THREE ARMY CIRCULAR MOVEMENT WITH SUPPORT','NotPassed'),(303,1,'6.C.3','TEST CASE, A DISRUPTED THREE ARMY CIRCULAR MOVEMENT','NotPassed'),(304,1,'6.C.4','TEST CASE, A CIRCULAR MOVEMENT WITH ATTACKED CONVOY','NotPassed'),(305,1,'6.C.5','TEST CASE, A DISRUPTED CIRCULAR MOVEMENT DUE TO DISLODGED CONVOY','NotPassed'),(306,1,'6.C.6','TEST CASE, TWO ARMIES WITH TWO CONVOYS','NotPassed'),(307,1,'6.C.7','TEST CASE, DISRUPTED UNIT SWAP','NotPassed'),(401,1,'6.D.1','TEST CASE, SUPPORTED HOLD CAN PREVENT DISLODGEMENT','NotPassed'),(402,1,'6.D.2','TEST CASE, A MOVE CUTS SUPPORT ON HOLD','NotPassed'),(403,1,'6.D.3','TEST CASE, A MOVE CUTS SUPPORT ON MOVE','NotPassed'),(404,1,'6.D.4','TEST CASE, SUPPORT TO HOLD ON UNIT SUPPORTING A HOLD ALLOWED','NotPassed'),(405,1,'6.D.5','TEST CASE, SUPPORT TO HOLD ON UNIT SUPPORTING A MOVE ALLOWED','NotPassed'),(406,1,'6.D.6','TEST CASE, SUPPORT TO HOLD ON CONVOYING UNIT ALLOWED','NotPassed'),(407,1,'6.D.7','TEST CASE, SUPPORT TO HOLD ON MOVING UNIT NOT ALLOWED','NotPassed'),(408,1,'6.D.8','TEST CASE, FAILED CONVOY CAN NOT RECEIVE HOLD SUPPORT','NotPassed'),(409,1,'6.D.9','TEST CASE, SUPPORT TO MOVE ON HOLDING UNIT NOT ALLOWED','NotPassed'),(410,1,'6.D.10','TEST CASE, SELF DISLODGMENT PROHIBITED','NotPassed'),(411,1,'6.D.11','TEST CASE, NO SELF DISLODGMENT OF RETURNING UNIT','NotPassed'),(412,1,'6.D.12','TEST CASE, SUPPORTING A FOREIGN UNIT TO DISLODGE OWN UNIT PROHIBITED','NotPassed'),(413,1,'6.D.13','TEST CASE, SUPPORTING A FOREIGN UNIT TO DISLODGE A RETURNING OWN UNIT PROHIBITED','NotPassed'),(414,1,'6.D.14','TEST CASE, SUPPORTING A FOREIGN UNIT IS NOT ENOUGH TO PREVENT DISLODGEMENT','NotPassed'),(415,1,'6.D.15','TEST CASE, DEFENDER CAN NOT CUT SUPPORT FOR ATTACK ON ITSELF','NotPassed'),(416,1,'6.D.16','TEST CASE, CONVOYING A UNIT DISLODGING A UNIT OF SAME POWER IS ALLOWED','NotPassed'),(417,1,'6.D.17','TEST CASE, DISLODGEMENT CUTS SUPPORTS','NotPassed'),(418,1,'6.D.18','TEST CASE, A SURVIVING UNIT WILL SUSTAIN SUPPORT','NotPassed'),(419,1,'6.D.19','TEST CASE, EVEN WHEN SURVIVING IS IN ALTERNATIVE WAY','NotPassed'),(420,1,'6.D.20','TEST CASE, UNIT CAN NOT CUT SUPPORT OF ITS OWN COUNTRY','NotPassed'),(421,1,'6.D.21','TEST CASE, DISLODGING DOES NOT CANCEL A SUPPORT CUT','NotPassed'),(422,1,'6.D.22','TEST CASE, IMPOSSIBLE FLEET MOVE CAN NOT BE SUPPORTED','NotPassed'),(423,1,'6.D.23','TEST CASE, IMPOSSIBLE COAST MOVE CAN NOT BE SUPPORTED','NotPassed'),(424,1,'6.D.24','TEST CASE, IMPOSSIBLE ARMY MOVE CAN NOT BE SUPPORTED','NotPassed'),(425,1,'6.D.25','TEST CASE, FAILING HOLD SUPPORT CAN BE SUPPORTED','NotPassed'),(426,1,'6.D.26','TEST CASE, FAILING MOVE SUPPORT CAN BE SUPPORTED','NotPassed'),(427,1,'6.D.27','TEST CASE, FAILING CONVOY CAN BE SUPPORTED','NotPassed'),(428,1,'6.D.28','TEST CASE, IMPOSSIBLE MOVE AND SUPPORT','NotPassed'),(429,1,'6.D.29','TEST CASE, MOVE TO IMPOSSIBLE COAST AND SUPPORT','NotPassed'),(430,1,'6.D.30','TEST CASE, MOVE WITHOUT COAST AND SUPPORT','NotPassed'),(431,1,'6.D.31','TEST CASE, A TRICKY IMPOSSIBLE SUPPORT','NotPassed'),(432,1,'6.D.32','TEST CASE, A MISSING FLEET','Invalid'),(433,1,'6.D.33','TEST CASE, UNWANTED SUPPORT ALLOWED','NotPassed'),(434,1,'6.D.34','TEST CASE, SUPPORT TARGETING OWN AREA NOT ALLOWED','NotPassed'),(501,1,'6.E.1','TEST CASE, DISLODGED UNIT HAS NO EFFECT ON ATTACKERS AREA','NotPassed'),(502,1,'6.E.2','TEST CASE, NO SELF DISLODGEMENT IN HEAD TO HEAD BATTLE','NotPassed'),(503,1,'6.E.3','TEST CASE, NO HELP IN DISLODGING OWN UNIT','NotPassed'),(504,1,'6.E.4','TEST CASE, NON-DISLODGED LOSER HAS STILL EFFECT','NotPassed'),(505,1,'6.E.5','TEST CASE, LOSER DISLODGED BY ANOTHER ARMY HAS STILL EFFECT','NotPassed'),(506,1,'6.E.6','TEST CASE, NOT DISLODGE BECAUSE OF OWN SUPPORT HAS STILL EFFECT','NotPassed'),(507,1,'6.E.7','TEST CASE, NO SELF DISLODGEMENT WITH BELEAGUERED GARRISON','NotPassed'),(508,1,'6.E.8','TEST CASE, NO SELF DISLODGEMENT WITH BELEAGUERED GARRISON AND HEAD TO HEAD BATTLE','NotPassed'),(509,1,'6.E.9','TEST CASE, ALMOST SELF DISLODGEMENT WITH BELEAGUERED GARRISON','NotPassed'),(510,1,'6.E.10','TEST CASE, ALMOST CIRCULAR MOVEMENT WITH NO SELF DISLODGEMENT WITH BELEAGUERED GARRISON','NotPassed'),(511,1,'6.E.11','TEST CASE, NO SELF DISLODGEMENT WITH BELEAGUERED GARRISON, UNIT SWAP WITH ADJACENT CONVOYING AND TWO COASTS','NotPassed'),(512,1,'6.E.12','TEST CASE, SUPPORT ON ATTACK ON OWN UNIT CAN BE USED FOR OTHER MEANS','NotPassed'),(513,1,'6.E.13','TEST CASE, THREE WAY BELEAGUERED GARRISON','NotPassed'),(514,1,'6.E.14','TEST CASE, ILLEGAL HEAD TO HEAD BATTLE CAN STILL DEFEND','NotPassed'),(515,1,'6.E.15','TEST CASE, THE FRIENDLY HEAD TO HEAD BATTLE','NotPassed'),(601,1,'6.F.1','TEST CASE, NO CONVOY IN COASTAL AREAS','NotPassed'),(602,1,'6.F.2','TEST CASE, AN ARMY BEING CONVOYED CAN BOUNCE AS NORMAL','NotPassed'),(603,1,'6.F.3','TEST CASE, AN ARMY BEING CONVOYED CAN RECEIVE SUPPORT','NotPassed'),(604,1,'6.F.4','TEST CASE, AN ATTACKED CONVOY IS NOT DISRUPTED','NotPassed'),(605,1,'6.F.5','TEST CASE, A BELEAGUERED CONVOY IS NOT DISRUPTED','NotPassed'),(606,1,'6.F.6','TEST CASE, DISLODGED CONVOY DOES NOT CUT SUPPORT','NotPassed'),(607,1,'6.F.7','TEST CASE, DISLODGED CONVOY DOES NOT CAUSE CONTESTED AREA','Invalid'),(608,1,'6.F.8','TEST CASE, DISLODGED CONVOY DOES NOT CAUSE A BOUNCE','NotPassed'),(609,1,'6.F.9','TEST CASE, DISLODGE OF MULTI-ROUTE CONVOY','NotPassed'),(610,1,'6.F.10','TEST CASE, DISLODGE OF MULTI-ROUTE CONVOY WITH FOREIGN FLEET','NotPassed'),(611,1,'6.F.11','TEST CASE, DISLODGE OF MULTI-ROUTE CONVOY WITH ONLY FOREIGN FLEETS','NotPassed'),(612,1,'6.F.12','TEST CASE, DISLODGED CONVOYING FLEET NOT ON ROUTE','NotPassed'),(613,1,'6.F.13','TEST CASE, THE UNWANTED ALTERNATIVE','NotPassed'),(614,1,'6.F.14','TEST CASE, SIMPLE CONVOY PARADOX','NotPassed'),(615,1,'6.F.15','TEST CASE, SIMPLE CONVOY PARADOX WITH ADDITIONAL CONVOY','NotPassed'),(616,1,'6.F.16','TEST CASE, PANDIN\'S PARADOX','NotPassed'),(617,1,'6.F.17','TEST CASE, PANDIN\'S EXTENDED PARADOX','NotPassed'),(618,1,'6.F.18','TEST CASE, BETRAYAL PARADOX','NotPassed'),(619,1,'6.F.19','TEST CASE, MULTI-ROUTE CONVOY DISRUPTION PARADOX','NotPassed'),(620,1,'6.F.20','TEST CASE, UNWANTED MULTI-ROUTE CONVOY PARADOX','NotPassed'),(621,1,'6.F.21','TEST CASE, DAD\'S ARMY CONVOY','NotPassed'),(622,1,'6.F.22','TEST CASE, SECOND ORDER PARADOX WITH TWO RESOLUTIONS','NotPassed'),(623,1,'6.F.23','TEST CASE, SECOND ORDER PARADOX WITH TWO EXCLUSIVE CONVOYS','NotPassed'),(624,1,'6.F.24','TEST CASE, SECOND ORDER PARADOX WITH NO RESOLUTION','NotPassed'),(701,1,'6.G.1','TEST CASE, TWO UNITS CAN SWAP PLACES BY CONVOY','NotPassed'),(702,1,'6.G.2','TEST CASE, KIDNAPPING AN ARMY','NotPassed'),(703,1,'6.G.3','TEST CASE, KIDNAPPING WITH A DISRUPTED CONVOY','NotPassed'),(704,1,'6.G.4','TEST CASE, KIDNAPPING WITH A DISRUPTED CONVOY AND OPPOSITE MOVE','NotPassed'),(705,1,'6.G.5','TEST CASE, SWAPPING WITH INTENT','Invalid'),(706,1,'6.G.6','TEST CASE, SWAPPING WITH UNINTENDED INTENT','Invalid'),(707,1,'6.G.7','TEST CASE, SWAPPING WITH ILLEGAL INTENT','Invalid'),(708,1,'6.G.8','TEST CASE, EXPLICIT CONVOY THAT ISN\'T THERE','NotPassed'),(709,1,'6.G.9','TEST CASE, SWAPPED OR DISLODGED?','Invalid'),(710,1,'6.G.10','TEST CASE, SWAPPED OR AN HEAD TO HEAD BATTLE?','NotPassed'),(711,1,'6.G.11','TEST CASE, A CONVOY TO AN ADJACENT PLACE WITH A PARADOX','NotPassed'),(712,1,'6.G.12','TEST CASE, SWAPPING TWO UNITS WITH TWO CONVOYS','NotPassed'),(713,1,'6.G.13','TEST CASE, SUPPORT CUT ON ATTACK ON ITSELF VIA CONVOY','NotPassed'),(714,1,'6.G.14','TEST CASE, BOUNCE BY CONVOY TO ADJACENT PLACE','NotPassed'),(715,1,'6.G.15','TEST CASE, BOUNCE AND DISLODGE WITH DOUBLE CONVOY','NotPassed'),(716,1,'6.G.16','TEST CASE, THE TWO UNIT IN ONE AREA BUG, MOVING BY CONVOY','NotPassed'),(717,1,'6.G.17','TEST CASE, THE TWO UNIT IN ONE AREA BUG, MOVING OVER LAND','NotPassed'),(718,1,'6.G.18','TEST CASE, THE TWO UNIT IN ONE AREA BUG, WITH DOUBLE CONVOY','NotPassed'),(801,1,'wD.Intro.1','webDiplomacy introduction page example scenarios: Hold.','NotPassed'),(802,1,'wD.Intro.2','webDiplomacy introduction page example scenarios: Move.','NotPassed'),(803,1,'wD.Intro.3','webDiplomacy introduction page example scenarios: Support move.','NotPassed'),(804,1,'wD.Intro.4','webDiplomacy introduction page example scenarios: Support move vs support hold.','NotPassed'),(805,1,'wD.Intro.5','webDiplomacy introduction page example scenarios: Convoy.','NotPassed'),(806,1,'wD.Intro.6','webDiplomacy introduction page example scenarios: Defend.','NotPassed'),(807,1,'wD.Intro.7','webDiplomacy introduction page example scenarios: Bounce.','NotPassed'),(808,1,'wD.Intro.8','webDiplomacy introduction page example scenarios: Support moves.','NotPassed'),(809,1,'wD.Intro.9','webDiplomacy introduction page example scenarios: Support moves vs support holds deadlock.','NotPassed'),(810,1,'wD.Intro.10','webDiplomacy introduction page example scenarios: Support move attacked','NotPassed'),(811,1,'wD.Intro.11','webDiplomacy introduction page example scenarios: Support hold and move attacked','NotPassed'),(812,1,'wD.Intro.12','webDiplomacy introduction page example scenarios: Complex scenario 1','NotPassed'),(901,1,'wD.Test.1','Testing the maximum sized convoy for this map.','NotPassed');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_DATCOrders` (
  `testID` smallint(6) NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `unitType` enum('Army','Fleet') NOT NULL,
  `terrID` smallint(5) unsigned NOT NULL,
  `moveType` enum('Hold','Move','Support hold','Support move','Convoy','Retreat','Disband','Build Army','Build Fleet','Wait','Destroy') NOT NULL,
  `toTerrID` smallint(5) unsigned DEFAULT NULL,
  `fromTerrID` smallint(5) unsigned DEFAULT NULL,
  `viaConvoy` enum('No','Yes') DEFAULT NULL,
  `criteria` enum('Success','Dislodged','Hold') DEFAULT NULL,
  `legal` enum('No','Yes') DEFAULT NULL,
  PRIMARY KEY (`testID`,`terrID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `wD_DATCOrders` VALUES (101,1,'Fleet',53,'Move',45,NULL,'No','Hold','No'),(102,1,'Army',3,'Move',59,NULL,'No','Hold','No'),(103,4,'Fleet',37,'Move',41,NULL,'No','Hold','No'),(104,4,'Fleet',37,'Move',37,NULL,'No','Hold','No'),(105,1,'Fleet',53,'Convoy',4,4,'No','Hold','No'),(105,1,'Army',4,'Move',4,NULL,'No','Dislodged','No'),(105,1,'Army',3,'Support move',4,4,'No','Hold','No'),(105,4,'Fleet',6,'Move',4,NULL,'No','Success','Yes'),(105,4,'Army',5,'Support move',4,6,'No','Hold','Yes'),(107,1,'Fleet',6,'Move',44,NULL,'No','Hold','No'),(107,1,'Fleet',53,'Convoy',44,6,'No','Hold','No'),(108,3,'Army',15,'Move',73,NULL,'No','Success','Yes'),(108,3,'Army',70,'Support move',73,15,'No','Hold','Yes'),(108,5,'Fleet',73,'Support hold',73,NULL,'No','Dislodged','No'),(109,3,'Fleet',12,'Move',15,NULL,'No','Hold','No'),(110,5,'Army',15,'Hold',NULL,NULL,'No','Hold','Yes'),(110,3,'Fleet',12,'Support move',15,16,'No','Hold','No'),(110,3,'Army',16,'Move',15,NULL,'No','Hold','Yes'),(111,5,'Army',72,'Move',70,NULL,'No','Hold','Yes'),(111,3,'Army',15,'Move',70,NULL,'No','Hold','Yes'),(112,5,'Army',72,'Move',70,NULL,'No','Hold','Yes'),(112,4,'Army',41,'Move',70,NULL,'No','Hold','Yes'),(112,3,'Army',15,'Move',70,NULL,'No','Hold','Yes'),(201,2,'Fleet',7,'Move',8,NULL,'No','Hold','No'),(202,2,'Fleet',50,'Move',8,NULL,'No','Hold','No'),(204,2,'Fleet',50,'Move',76,NULL,'No','Hold','Yes'),(205,2,'Fleet',49,'Move',63,NULL,'No','Hold','Yes'),(205,2,'Fleet',76,'Support move',63,49,'No','Hold','No'),(205,3,'Fleet',63,'Hold',NULL,NULL,'No','Hold','Yes'),(206,1,'Fleet',59,'Support move',61,58,'No','Hold','Yes'),(206,1,'Fleet',58,'Move',61,NULL,'No','Success','Yes'),(206,2,'Fleet',76,'Support hold',61,NULL,'No','Hold','Yes'),(206,2,'Fleet',61,'Hold',NULL,NULL,'No','Dislodged','Yes'),(206,3,'Fleet',63,'Move',77,NULL,'No','Hold','Yes'),(213,6,'Fleet',81,'Move',22,NULL,'No','Hold','Yes'),(213,6,'Fleet',22,'Move',80,NULL,'No','Hold','Yes'),(301,6,'Fleet',24,'Move',22,NULL,'No','Success','Yes'),(301,6,'Army',22,'Move',23,NULL,'No','Success','Yes'),(301,6,'Army',23,'Move',24,NULL,'No','Success','Yes'),(302,6,'Fleet',24,'Move',22,NULL,'No','Success','Yes'),(302,6,'Army',22,'Move',23,NULL,'No','Success','Yes'),(302,6,'Army',23,'Move',24,NULL,'No','Success','Yes'),(302,6,'Army',20,'Support move',22,24,'No','Hold','Yes'),(303,6,'Fleet',24,'Move',22,NULL,'No','Hold','Yes'),(303,6,'Army',22,'Move',23,NULL,'No','Hold','Yes'),(303,6,'Army',23,'Move',24,NULL,'No','Hold','Yes'),(303,6,'Army',20,'Move',22,NULL,'No','Hold','Yes'),(304,5,'Army',73,'Move',19,NULL,'No','Success','Yes'),(304,5,'Army',19,'Move',20,NULL,'No','Success','Yes'),(304,6,'Army',20,'Move',73,NULL,'Yes','Success','Yes'),(304,6,'Fleet',67,'Convoy',73,20,'No','Hold','Yes'),(304,6,'Fleet',65,'Convoy',73,20,'No','Hold','Yes'),(304,6,'Fleet',66,'Convoy',73,20,'No','Hold','Yes'),(304,3,'Fleet',11,'Move',65,NULL,'No','Hold','Yes'),(305,5,'Army',73,'Move',19,NULL,'No','Hold','Yes'),(305,5,'Army',19,'Move',20,NULL,'No','Hold','Yes'),(305,6,'Army',20,'Move',73,NULL,'Yes','Hold','Yes'),(305,6,'Fleet',67,'Convoy',73,20,'No','Hold','Yes'),(305,6,'Fleet',65,'Convoy',73,20,'No','Dislodged','Yes'),(305,6,'Fleet',66,'Convoy',73,20,'No','Hold','Yes'),(305,3,'Fleet',11,'Move',65,NULL,'No','Hold','Yes'),(305,3,'Fleet',10,'Support move',65,11,'No','Hold','Yes'),(306,1,'Fleet',53,'Convoy',44,6,'No','Success','Yes'),(306,1,'Army',6,'Move',44,NULL,'Yes','Hold','Yes'),(306,2,'Fleet',60,'Convoy',6,44,'No','Success','Yes'),(306,2,'Army',44,'Move',6,NULL,'Yes','Hold','Yes'),(307,1,'Fleet',53,'Convoy',44,6,'No','Hold','Yes'),(307,1,'Army',6,'Move',44,NULL,'Yes','Hold','Yes'),(307,2,'Fleet',60,'Convoy',6,44,'No','Hold','Yes'),(307,2,'Army',44,'Move',6,NULL,'Yes','Hold','Yes'),(307,2,'Army',48,'Move',44,NULL,'No','Hold','Yes'),(401,5,'Fleet',66,'Support move',15,73,'No','Hold','Yes'),(401,5,'Army',73,'Move',15,NULL,'No','Hold','Yes'),(401,3,'Army',15,'Hold',NULL,NULL,'No','Hold','Yes'),(401,3,'Army',70,'Support hold',15,NULL,'No','Hold','Yes'),(402,5,'Fleet',66,'Support move',15,73,'No','Hold','Yes'),(402,5,'Army',73,'Move',15,NULL,'No','Success','Yes'),(402,5,'Army',72,'Move',70,NULL,'No','Hold','Yes'),(402,3,'Army',15,'Hold',NULL,NULL,'No','Dislodged','Yes'),(402,3,'Army',70,'Support hold',15,NULL,'No','Hold','Yes'),(403,5,'Fleet',66,'Support move',15,73,'No','Hold','Yes'),(403,5,'Army',73,'Move',15,NULL,'No','Hold','Yes'),(403,3,'Army',15,'Hold',NULL,NULL,'No','Hold','Yes'),(403,3,'Fleet',65,'Move',66,NULL,'No','Hold','Yes'),(404,4,'Army',38,'Support hold',37,NULL,'No','Hold','Yes'),(404,4,'Fleet',37,'Support hold',38,NULL,'No','Hold','Yes'),(404,7,'Fleet',56,'Support move',38,39,'No','Hold','Yes'),(404,7,'Army',39,'Move',38,NULL,'No','Hold','Yes'),(405,4,'Army',38,'Support move',40,41,'No','Hold','Yes'),(405,4,'Fleet',37,'Support hold',38,NULL,'No','Hold','Yes'),(405,4,'Army',41,'Move',40,NULL,'No','Hold','Yes'),(405,7,'Fleet',56,'Support move',38,39,'No','Hold','Yes'),(405,7,'Army',39,'Move',38,NULL,'No','Hold','Yes'),(406,4,'Army',38,'Move',34,NULL,'Yes','Success','Yes'),(406,4,'Fleet',56,'Convoy',34,38,'No','Success','Yes'),(406,4,'Fleet',39,'Support hold',56,NULL,'No','Hold','Yes'),(406,7,'Fleet',30,'Move',56,NULL,'No','Hold','Yes'),(406,7,'Fleet',57,'Support move',56,30,'No','Hold','Yes'),(407,4,'Fleet',56,'Move',34,NULL,'No','Dislodged','Yes'),(407,4,'Fleet',39,'Support hold',56,NULL,'No','Hold','Yes'),(407,7,'Fleet',30,'Move',56,NULL,'No','Hold','Yes'),(407,7,'Fleet',57,'Support move',56,30,'No','Hold','Yes'),(407,7,'Army',33,'Move',34,NULL,'No','Hold','Yes'),(408,5,'Fleet',65,'Hold',NULL,NULL,'No','Hold','Yes'),(408,5,'Army',19,'Support move',17,18,'No','Hold','Yes'),(408,5,'Army',18,'Move',17,NULL,'No','Success','Yes'),(408,6,'Army',17,'Move',11,NULL,'Yes','Dislodged','Yes'),(408,6,'Army',20,'Support hold',17,NULL,'No','Hold','Yes'),(409,3,'Army',15,'Move',73,NULL,'No','Success','Yes'),(409,3,'Army',70,'Support move',73,15,'No','Hold','Yes'),(409,5,'Army',18,'Support move',19,73,'No','Hold','Yes'),(409,5,'Army',73,'Hold',NULL,NULL,'No','Dislodged','Yes'),(410,4,'Army',38,'Hold',NULL,NULL,'No','Hold','Yes'),(410,4,'Fleet',37,'Move',38,NULL,'No','Hold','Yes'),(410,4,'Army',41,'Support move',38,37,'No','Hold','Yes'),(411,4,'Army',38,'Move',39,NULL,'No','Hold','Yes'),(411,4,'Fleet',37,'Move',38,NULL,'No','Hold','Yes'),(411,4,'Army',41,'Support move',38,37,'No','Hold','Yes'),(411,7,'Army',29,'Move',39,NULL,'No','Hold','Yes'),(412,5,'Fleet',73,'Hold',NULL,NULL,'No','Hold','Yes'),(412,5,'Army',72,'Support move',73,15,'No','Hold','Yes'),(412,3,'Army',15,'Move',73,NULL,'No','Hold','Yes'),(413,5,'Fleet',73,'Move',66,NULL,'No','Hold','Yes'),(413,5,'Army',72,'Support move',73,15,'No','Hold','Yes'),(413,3,'Army',15,'Move',73,NULL,'No','Hold','Yes'),(413,3,'Fleet',16,'Move',66,NULL,'No','Hold','Yes'),(414,5,'Fleet',73,'Hold',NULL,NULL,'No','Dislodged','Yes'),(414,5,'Army',72,'Support move',73,15,'No','Hold','Yes'),(414,3,'Army',15,'Move',73,NULL,'No','Success','Yes'),(414,3,'Army',70,'Support move',73,15,'No','Hold','Yes'),(414,3,'Fleet',66,'Support move',73,15,'No','Hold','Yes'),(415,7,'Fleet',22,'Support move',24,69,'No','Hold','Yes'),(415,7,'Fleet',69,'Move',24,NULL,'No','Success','Yes'),(415,6,'Fleet',24,'Move',22,NULL,'No','Dislodged','Yes'),(416,1,'Army',6,'Hold',NULL,NULL,'No','Dislodged','Yes'),(416,1,'Fleet',53,'Convoy',6,44,'No','Hold','Yes'),(416,2,'Fleet',60,'Support move',6,44,'No','Hold','Yes'),(416,2,'Army',44,'Move',6,NULL,'Yes','Success','Yes'),(417,7,'Fleet',22,'Support move',24,69,'No','Dislodged','Yes'),(417,7,'Fleet',69,'Move',24,NULL,'No','Hold','Yes'),(417,6,'Fleet',24,'Move',22,NULL,'No','Success','Yes'),(417,6,'Army',23,'Support move',22,24,'No','Hold','Yes'),(417,6,'Army',25,'Move',24,NULL,'No','Hold','Yes'),(418,7,'Fleet',22,'Support move',24,69,'No','Hold','Yes'),(418,7,'Fleet',69,'Move',24,NULL,'No','Success','Yes'),(418,7,'Army',20,'Support hold',22,NULL,'No','Hold','Yes'),(418,6,'Fleet',24,'Move',22,NULL,'No','Dislodged','Yes'),(418,6,'Army',23,'Support move',22,24,'No','Hold','Yes'),(418,6,'Army',25,'Move',24,NULL,'No','Hold','Yes'),(419,7,'Fleet',22,'Support move',24,69,'No','Hold','Yes'),(419,7,'Fleet',69,'Move',24,NULL,'No','Success','Yes'),(419,7,'Army',23,'Support move',22,24,'No','Hold','Yes'),(419,6,'Fleet',24,'Move',22,NULL,'No','Dislodged','Yes'),(420,1,'Fleet',6,'Support move',60,53,'No','Hold','Yes'),(420,1,'Fleet',53,'Move',60,NULL,'No','Success','Yes'),(420,1,'Army',4,'Move',6,NULL,'No','Hold','Yes'),(420,2,'Fleet',60,'Hold',NULL,NULL,'No','Dislodged','Yes'),(421,5,'Fleet',73,'Hold',NULL,NULL,'No','Hold','Yes'),(421,3,'Army',15,'Move',73,NULL,'No','Hold','Yes'),(421,3,'Army',70,'Support move',73,15,'No','Hold','Yes'),(421,4,'Army',41,'Move',70,NULL,'No','Dislodged','Yes'),(421,7,'Army',40,'Move',41,NULL,'No','Hold','Yes'),(421,7,'Army',38,'Support move',41,40,'No','Hold','Yes'),(422,4,'Fleet',37,'Move',41,NULL,'No','Dislodged','No'),(422,4,'Army',48,'Support move',41,37,'No','Hold','No'),(422,7,'Army',41,'Move',37,NULL,'No','Success','Yes'),(422,7,'Army',38,'Support move',37,41,'No','Hold','Yes'),(423,3,'Fleet',63,'Move',77,NULL,'No','Success','Yes'),(423,3,'Fleet',62,'Support move',8,63,'No','Hold','Yes'),(423,2,'Fleet',76,'Move',63,NULL,'No','Dislodged','No'),(423,2,'Fleet',49,'Support move',63,8,'No','Hold','No'),(424,2,'Army',49,'Move',63,NULL,'No','Hold','No'),(424,2,'Fleet',77,'Support move',63,49,'No','Hold','No'),(424,3,'Fleet',63,'Hold',NULL,NULL,'No','Dislodged','Yes'),(424,6,'Fleet',64,'Support move',63,62,'No','Hold','Yes'),(424,6,'Fleet',62,'Move',63,NULL,'No','Success','Yes'),(425,4,'Army',38,'Support hold',39,NULL,'No','Hold','Yes'),(425,4,'Fleet',37,'Support hold',38,NULL,'No','Hold','Yes'),(425,7,'Fleet',56,'Support move',38,39,'No','Hold','Yes'),(425,7,'Army',39,'Move',38,NULL,'No','Hold','Yes'),(426,4,'Army',38,'Support move',40,39,'No','Hold','Yes'),(426,4,'Fleet',37,'Support hold',38,NULL,'No','Hold','Yes'),(426,7,'Fleet',56,'Support move',38,39,'No','Hold','Yes'),(426,7,'Army',39,'Move',38,NULL,'No','Hold','Yes'),(427,1,'Fleet',34,'Move',56,NULL,'No','Hold','Yes'),(427,1,'Fleet',36,'Support move',56,34,'No','Hold','Yes'),(427,4,'Army',38,'Hold',NULL,NULL,'No','Hold','Yes'),(427,7,'Fleet',56,'Convoy',30,38,'No','Hold','Yes'),(427,7,'Fleet',39,'Support hold',56,NULL,'No','Hold','Yes'),(428,5,'Army',74,'Support hold',21,NULL,'No','Hold','Yes'),(428,7,'Fleet',21,'Move',43,NULL,'No','Hold','No'),(428,6,'Fleet',69,'Move',21,NULL,'No','Hold','Yes'),(428,6,'Army',20,'Support move',21,69,'No','Hold','Yes'),(429,5,'Army',74,'Support hold',21,NULL,'No','Hold','Yes'),(429,7,'Fleet',21,'Move',81,NULL,'No','Hold','No'),(429,6,'Fleet',69,'Move',21,NULL,'No','Hold','Yes'),(429,6,'Army',20,'Support move',21,69,'No','Hold','Yes'),(430,3,'Fleet',67,'Support hold',22,NULL,'No','Hold','Yes'),(430,7,'Fleet',22,'Move',20,NULL,'No','Hold','No'),(430,6,'Fleet',69,'Move',22,NULL,'No','Hold','Yes'),(430,6,'Army',20,'Support move',22,69,'No','Hold','Yes'),(431,5,'Army',21,'Move',25,NULL,'Yes','Hold','Yes'),(431,6,'Fleet',69,'Support move',25,21,'No','Hold','No'),(432,1,'Fleet',2,'Support move',4,3,'No','Hold','Yes'),(432,1,'Army',3,'Move',4,NULL,'No','Hold','Yes'),(432,2,'Fleet',6,'Support hold',4,NULL,'No','Hold','Yes'),(432,4,'Army',4,'Move',43,NULL,'No','Hold','Yes'),(433,5,'Army',19,'Move',74,NULL,'No','Success','Yes'),(433,5,'Army',72,'Move',74,NULL,'No','Hold','Yes'),(433,7,'Army',75,'Support move',74,19,'No','Hold','Yes'),(433,6,'Army',20,'Move',19,NULL,'No','Success','Yes'),(434,4,'Army',38,'Move',39,NULL,'No','Success','Yes'),(434,4,'Army',40,'Support move',39,38,'No','Hold','Yes'),(434,4,'Fleet',56,'Support move',39,38,'No','Hold','Yes'),(434,3,'Army',39,'Support move',39,30,'No','Dislodged','No'),(434,7,'Army',29,'Support move',39,30,'No','Hold','Yes'),(434,7,'Army',30,'Move',39,NULL,'No','Hold','Yes'),(501,4,'Army',38,'Move',39,NULL,'No','Success','Yes'),(501,4,'Fleet',37,'Move',38,NULL,'No','Success','Yes'),(501,4,'Army',40,'Support move',39,38,'No','Hold','Yes'),(501,7,'Army',39,'Move',38,NULL,'No','Dislodged','Yes'),(502,4,'Army',38,'Move',37,NULL,'No','Hold','Yes'),(502,4,'Fleet',37,'Move',38,NULL,'No','Hold','Yes'),(502,4,'Army',41,'Support move',37,38,'No','Hold','Yes'),(503,4,'Army',38,'Move',37,NULL,'No','Hold','Yes'),(503,4,'Army',41,'Support move',38,37,'No','Hold','Yes'),(503,1,'Fleet',37,'Move',38,NULL,'No','Hold','Yes'),(504,4,'Fleet',43,'Move',53,NULL,'No','Hold','Yes'),(504,4,'Fleet',55,'Support move',53,43,'No','Hold','Yes'),(504,4,'Fleet',54,'Support move',53,43,'No','Hold','Yes'),(504,2,'Fleet',53,'Move',43,NULL,'No','Hold','Yes'),(504,2,'Fleet',44,'Support move',43,53,'No','Hold','Yes'),(504,1,'Fleet',2,'Support move',53,52,'No','Hold','Yes'),(504,1,'Fleet',4,'Support move',53,52,'No','Hold','Yes'),(504,1,'Fleet',52,'Move',53,NULL,'No','Hold','Yes'),(504,5,'Army',37,'Support move',43,42,'No','Hold','Yes'),(504,5,'Army',42,'Move',43,NULL,'No','Hold','Yes'),(505,4,'Fleet',43,'Move',53,NULL,'No','Hold','Yes'),(505,4,'Fleet',55,'Support move',53,43,'No','Hold','Yes'),(505,4,'Fleet',54,'Support move',53,43,'No','Hold','Yes'),(505,2,'Fleet',53,'Move',43,NULL,'No','Dislodged','Yes'),(505,2,'Fleet',44,'Support move',43,53,'No','Hold','Yes'),(505,1,'Fleet',2,'Support move',53,52,'No','Hold','Yes'),(505,1,'Fleet',4,'Support move',53,52,'No','Hold','Yes'),(505,1,'Fleet',52,'Move',53,NULL,'No','Hold','Yes'),(505,1,'Fleet',6,'Support move',53,52,'No','Hold','Yes'),(505,5,'Army',37,'Support move',43,42,'No','Hold','Yes'),(505,5,'Army',42,'Move',43,NULL,'No','Hold','Yes'),(506,4,'Fleet',43,'Move',53,NULL,'No','Hold','Yes'),(506,4,'Fleet',55,'Support move',53,43,'No','Hold','Yes'),(506,2,'Fleet',53,'Move',43,NULL,'No','Hold','Yes'),(506,2,'Fleet',44,'Support move',43,53,'No','Hold','Yes'),(506,2,'Fleet',60,'Support move',53,43,'No','Hold','Yes'),(506,5,'Army',37,'Support move',43,42,'No','Hold','Yes'),(506,5,'Army',42,'Move',43,NULL,'No','Hold','Yes'),(507,1,'Fleet',53,'Hold',NULL,NULL,'No','Hold','Yes'),(507,1,'Fleet',4,'Support move',53,35,'No','Hold','Yes'),(507,4,'Fleet',43,'Support move',53,55,'No','Hold','Yes'),(507,4,'Fleet',55,'Move',53,NULL,'No','Hold','Yes'),(507,7,'Fleet',54,'Support move',53,35,'No','Hold','Yes'),(507,7,'Fleet',35,'Move',53,NULL,'No','Hold','Yes'),(508,1,'Fleet',53,'Move',35,NULL,'No','Hold','Yes'),(508,1,'Fleet',4,'Support move',53,35,'No','Hold','Yes'),(508,4,'Fleet',43,'Support move',53,55,'No','Hold','Yes'),(508,4,'Fleet',55,'Move',53,NULL,'No','Hold','Yes'),(508,7,'Fleet',54,'Support move',53,35,'No','Hold','Yes'),(508,7,'Fleet',35,'Move',53,NULL,'No','Hold','Yes'),(509,1,'Fleet',53,'Move',52,NULL,'No','Success','Yes'),(509,1,'Fleet',4,'Support move',53,35,'No','Hold','Yes'),(509,4,'Fleet',43,'Support move',53,55,'No','Hold','Yes'),(509,4,'Fleet',55,'Move',53,NULL,'No','Hold','Yes'),(509,7,'Fleet',54,'Support move',53,35,'No','Hold','Yes'),(509,7,'Fleet',35,'Move',53,NULL,'No','Success','Yes'),(510,1,'Fleet',53,'Move',36,NULL,'No','Hold','Yes'),(510,1,'Fleet',4,'Support move',53,35,'No','Hold','Yes'),(510,4,'Fleet',43,'Support move',53,55,'No','Hold','Yes'),(510,4,'Fleet',55,'Move',53,NULL,'No','Hold','Yes'),(510,4,'Fleet',36,'Move',55,NULL,'No','Hold','Yes'),(510,7,'Fleet',54,'Support move',53,35,'No','Hold','Yes'),(510,7,'Fleet',35,'Move',53,NULL,'No','Hold','Yes'),(511,2,'Army',8,'Move',7,NULL,'Yes','Success','Yes'),(511,2,'Fleet',61,'Convoy',7,8,'No','Hold','Yes'),(511,2,'Fleet',63,'Support move',8,7,'No','Hold','Yes'),(511,4,'Army',49,'Support move',8,50,'No','Hold','Yes'),(511,4,'Army',50,'Move',8,NULL,'No','Hold','Yes'),(511,3,'Fleet',7,'Move',76,NULL,'No','Success','Yes'),(511,3,'Fleet',62,'Support move',8,7,'No','Hold','Yes'),(512,5,'Army',74,'Move',21,NULL,'No','Hold','Yes'),(512,5,'Army',19,'Support move',74,72,'No','Hold','Yes'),(512,3,'Army',72,'Move',74,NULL,'No','Hold','Yes'),(512,7,'Army',75,'Move',74,NULL,'No','Hold','Yes'),(512,7,'Army',21,'Support move',74,75,'No','Hold','Yes'),(513,1,'Fleet',2,'Support move',53,4,'No','Hold','Yes'),(513,1,'Fleet',4,'Move',53,NULL,'No','Hold','Yes'),(513,2,'Fleet',44,'Move',53,NULL,'No','Hold','Yes'),(513,2,'Fleet',60,'Support move',53,44,'No','Hold','Yes'),(513,4,'Fleet',53,'Hold',NULL,NULL,'No','Hold','Yes'),(513,7,'Fleet',52,'Move',53,NULL,'No','Hold','Yes'),(513,7,'Fleet',35,'Support move',53,52,'No','Hold','Yes'),(514,1,'Army',3,'Move',2,NULL,'No','Hold','Yes'),(514,7,'Fleet',2,'Move',3,NULL,'No','Hold','No'),(515,1,'Fleet',43,'Support move',37,42,'No','Hold','Yes'),(515,1,'Army',42,'Move',37,NULL,'No','Hold','Yes'),(515,2,'Army',37,'Move',38,NULL,'No','Hold','Yes'),(515,2,'Army',41,'Support move',38,37,'No','Hold','Yes'),(515,2,'Army',40,'Support move',38,37,'No','Hold','Yes'),(515,4,'Army',38,'Move',37,NULL,'No','Hold','Yes'),(515,4,'Fleet',36,'Support move',37,38,'No','Hold','Yes'),(515,4,'Fleet',55,'Support move',37,38,'No','Hold','Yes'),(515,7,'Fleet',56,'Support move',38,39,'No','Hold','Yes'),(515,7,'Army',39,'Move',38,NULL,'No','Hold','Yes'),(601,6,'Army',17,'Move',27,NULL,'No','Hold','No'),(601,6,'Fleet',67,'Convoy',27,17,'No','Hold','No'),(601,6,'Fleet',22,'Convoy',27,17,'No','Hold','No'),(601,6,'Fleet',69,'Convoy',27,17,'No','Hold','No'),(602,1,'Fleet',60,'Convoy',46,6,'No','Hold','Yes'),(602,1,'Army',6,'Move',46,NULL,'Yes','Hold','Yes'),(602,2,'Army',47,'Move',46,NULL,'No','Hold','Yes'),(603,1,'Fleet',60,'Convoy',46,6,'No','Hold','Yes'),(603,1,'Army',6,'Move',46,NULL,'Yes','Success','Yes'),(603,1,'Fleet',61,'Support move',46,6,'No','Hold','Yes'),(603,2,'Army',47,'Move',46,NULL,'No','Hold','Yes'),(604,1,'Fleet',53,'Convoy',43,6,'No','Hold','Yes'),(604,1,'Army',6,'Move',43,NULL,'Yes','Success','Yes'),(604,4,'Fleet',54,'Move',53,NULL,'No','Hold','Yes'),(605,1,'Fleet',53,'Convoy',43,6,'No','Hold','Yes'),(605,1,'Army',6,'Move',43,NULL,'Yes','Success','Yes'),(605,2,'Fleet',60,'Move',53,NULL,'No','Hold','Yes'),(605,2,'Fleet',44,'Support move',53,60,'No','Hold','Yes'),(605,4,'Fleet',54,'Move',53,NULL,'No','Hold','Yes'),(605,4,'Fleet',36,'Support move',53,54,'No','Hold','Yes'),(606,1,'Fleet',53,'Convoy',43,6,'No','Dislodged','Yes'),(606,1,'Army',6,'Move',43,NULL,'Yes','Hold','Yes'),(606,4,'Army',43,'Support hold',44,NULL,'No','Hold','Yes'),(606,4,'Army',44,'Support hold',43,NULL,'No','Hold','Yes'),(606,4,'Fleet',55,'Support move',53,54,'No','Hold','Yes'),(606,4,'Fleet',54,'Move',53,NULL,'No','Hold','Yes'),(606,2,'Army',45,'Move',44,NULL,'No','Hold','Yes'),(606,2,'Army',48,'Support move',44,45,'No','Hold','Yes'),(608,1,'Fleet',53,'Convoy',43,6,'No','Dislodged','Yes'),(608,1,'Army',6,'Move',43,NULL,'Yes','Hold','Yes'),(608,4,'Fleet',55,'Support move',53,54,'No','Hold','Yes'),(608,4,'Fleet',54,'Move',53,NULL,'No','Hold','Yes'),(608,4,'Army',44,'Move',43,NULL,'No','Success','Yes'),(609,1,'Fleet',60,'Convoy',44,6,'No','Dislodged','Yes'),(609,1,'Fleet',53,'Convoy',44,6,'No','Hold','Yes'),(609,1,'Army',6,'Move',44,NULL,'Yes','Success','Yes'),(609,2,'Fleet',46,'Support move',60,61,'No','Hold','Yes'),(609,2,'Fleet',61,'Move',60,NULL,'No','Success','Yes'),(610,1,'Fleet',53,'Convoy',44,6,'No','Hold','Yes'),(610,1,'Army',6,'Move',44,NULL,'Yes','Success','Yes'),(610,4,'Fleet',60,'Convoy',44,6,'No','Dislodged','Yes'),(610,2,'Fleet',46,'Support move',60,61,'No','Hold','Yes'),(610,2,'Fleet',61,'Move',60,NULL,'No','Success','Yes'),(611,1,'Army',6,'Move',44,NULL,'Yes','Success','Yes'),(611,4,'Fleet',60,'Convoy',44,6,'No','Dislodged','Yes'),(611,7,'Fleet',53,'Convoy',44,6,'No','Hold','Yes'),(611,2,'Fleet',46,'Support move',60,61,'No','Hold','Yes'),(611,2,'Fleet',61,'Move',60,NULL,'No','Success','Yes'),(612,1,'Fleet',60,'Convoy',44,6,'No','Hold','Yes'),(612,1,'Army',6,'Move',44,NULL,'Yes','Success','Yes'),(612,1,'Fleet',59,'Convoy',44,6,'No','Dislodged','No'),(612,2,'Fleet',58,'Support move',59,61,'No','Hold','Yes'),(612,2,'Fleet',61,'Move',59,NULL,'No','Success','Yes'),(613,1,'Army',6,'Move',44,NULL,'Yes','Success','Yes'),(613,1,'Fleet',53,'Convoy',44,6,'No','Dislodged','Yes'),(613,2,'Fleet',60,'Convoy',44,6,'No','Hold','Yes'),(613,4,'Fleet',43,'Support move',53,36,'No','Hold','Yes'),(613,4,'Fleet',36,'Move',53,NULL,'No','Success','Yes'),(614,1,'Fleet',6,'Support move',60,5,'No','Hold','Yes'),(614,1,'Fleet',5,'Move',60,NULL,'No','Success','Yes'),(614,2,'Army',46,'Move',6,NULL,'Yes','Hold','Yes'),(614,2,'Fleet',60,'Convoy',6,46,'No','Dislodged','Yes'),(615,1,'Fleet',6,'Support move',60,5,'No','Hold','Yes'),(615,1,'Fleet',5,'Move',60,NULL,'No','Hold','Yes'),(615,2,'Army',46,'Move',6,NULL,'Yes','Hold','Yes'),(615,2,'Fleet',60,'Convoy',6,46,'No','Dislodged','Yes'),(615,3,'Fleet',59,'Convoy',5,9,'No','Hold','Yes'),(615,3,'Fleet',61,'Convoy',5,9,'No','Hold','Yes'),(615,3,'Army',9,'Move',5,NULL,'Yes','Success','Yes'),(616,1,'Fleet',6,'Support move',60,5,'No','Hold','Yes'),(616,1,'Fleet',5,'Move',60,NULL,'No','Hold','Yes'),(616,2,'Army',46,'Move',6,NULL,'Yes','Hold','Yes'),(616,2,'Fleet',60,'Convoy',6,46,'No','Hold','Yes'),(616,4,'Fleet',53,'Support move',60,44,'No','Hold','Yes'),(616,4,'Fleet',44,'Move',60,NULL,'No','Hold','Yes'),(617,1,'Fleet',6,'Support move',60,5,'No','Hold','Yes'),(617,1,'Fleet',5,'Move',60,NULL,'No','Hold','Yes'),(617,2,'Army',46,'Move',6,NULL,'Yes','Hold','Yes'),(617,2,'Fleet',60,'Convoy',6,46,'No','Hold','Yes'),(617,2,'Fleet',4,'Support move',6,46,'No','Hold','Yes'),(617,4,'Fleet',53,'Support move',60,44,'No','Hold','Yes'),(617,4,'Fleet',44,'Move',60,NULL,'No','Hold','Yes'),(618,1,'Fleet',53,'Convoy',44,6,'No','Hold','Yes'),(618,1,'Army',6,'Move',44,NULL,'Yes','Hold','Yes'),(618,1,'Fleet',60,'Support move',44,6,'No','Hold','Yes'),(618,2,'Fleet',44,'Support hold',53,NULL,'No','Hold','Yes'),(618,4,'Fleet',55,'Support move',53,54,'No','Hold','Yes'),(618,4,'Fleet',54,'Move',53,NULL,'No','Hold','Yes'),(619,2,'Army',10,'Move',11,NULL,'Yes','Hold','Yes'),(619,2,'Fleet',64,'Convoy',11,10,'No','Hold','Yes'),(619,2,'Fleet',65,'Convoy',11,10,'No','Hold','Yes'),(619,3,'Fleet',11,'Support move',64,12,'No','Hold','Yes'),(619,3,'Fleet',12,'Move',64,NULL,'No','Hold','Yes'),(620,2,'Army',10,'Move',11,NULL,'Yes','Hold','Yes'),(620,2,'Fleet',64,'Convoy',11,10,'No','Hold','Yes'),(620,3,'Fleet',11,'Support hold',65,NULL,'No','Hold','Yes'),(620,3,'Fleet',65,'Convoy',11,10,'No','Dislodged','Yes'),(620,6,'Fleet',67,'Support move',65,68,'No','Hold','Yes'),(620,6,'Fleet',68,'Move',65,NULL,'No','Success','Yes'),(621,7,'Army',2,'Support move',1,35,'No','Hold','Yes'),(621,7,'Fleet',52,'Convoy',1,35,'No','Hold','Yes'),(621,7,'Army',35,'Move',1,NULL,'Yes','Hold','Yes'),(621,2,'Fleet',59,'Support move',58,61,'No','Hold','Yes'),(621,2,'Fleet',61,'Move',58,NULL,'No','Success','Yes'),(621,1,'Army',3,'Move',1,NULL,'Yes','Hold','Yes'),(621,1,'Fleet',58,'Convoy',1,3,'No','Dislodged','Yes'),(621,1,'Fleet',1,'Support hold',58,NULL,'No','Dislodged','Yes'),(622,1,'Fleet',2,'Move',53,NULL,'No','Success','Yes'),(622,1,'Fleet',6,'Support move',53,2,'No','Hold','Yes'),(622,2,'Army',46,'Move',6,NULL,'Yes','Hold','Yes'),(622,2,'Fleet',60,'Convoy',6,46,'No','Dislodged','Yes'),(622,4,'Fleet',44,'Support move',60,45,'No','Hold','Yes'),(622,4,'Fleet',45,'Move',60,NULL,'No','Hold','Yes'),(622,7,'Army',35,'Move',44,NULL,'Yes','Hold','Yes'),(622,7,'Fleet',53,'Convoy',44,35,'No','Dislodged','Yes'),(623,1,'Fleet',2,'Move',53,NULL,'No','Hold','Yes'),(623,1,'Fleet',4,'Support move',53,2,'No','Hold','Yes'),(623,2,'Army',46,'Move',6,NULL,'Yes','Hold','Yes'),(623,2,'Fleet',60,'Convoy',6,46,'No','Hold','Yes'),(623,4,'Fleet',44,'Support hold',60,NULL,'No','Hold','Yes'),(623,4,'Fleet',6,'Support hold',53,NULL,'No','Hold','Yes'),(623,3,'Fleet',61,'Move',60,NULL,'No','Hold','Yes'),(623,3,'Fleet',59,'Support move',60,61,'No','Hold','Yes'),(623,7,'Army',35,'Move',44,NULL,'Yes','Hold','Yes'),(623,7,'Fleet',53,'Convoy',44,35,'No','Hold','Yes'),(624,1,'Fleet',2,'Move',53,NULL,'No','Hold','Yes'),(624,1,'Fleet',6,'Support move',53,2,'No','Hold','Yes'),(624,1,'Fleet',59,'Move',60,NULL,'No','Hold','Yes'),(624,1,'Fleet',61,'Support move',60,59,'No','Hold','Yes'),(624,2,'Army',46,'Move',6,NULL,'Yes','Hold','Yes'),(624,2,'Fleet',60,'Convoy',6,46,'No','Hold','Yes'),(624,2,'Fleet',44,'Support hold',60,NULL,'No','Hold','Yes'),(624,7,'Army',35,'Move',44,NULL,'Yes','Hold','Yes'),(624,7,'Fleet',53,'Convoy',44,35,'No','Dislodged','Yes'),(701,1,'Army',35,'Move',34,NULL,'Yes','Success','Yes'),(701,1,'Fleet',54,'Convoy',34,35,'No','Hold','Yes'),(701,7,'Army',34,'Move',35,NULL,'No','Success','Yes'),(702,1,'Army',35,'Move',34,NULL,'No','Hold','Yes'),(702,7,'Fleet',34,'Move',35,NULL,'No','Hold','Yes'),(702,4,'Fleet',54,'Convoy',34,35,'No','Hold','Yes'),(703,2,'Fleet',46,'Move',60,NULL,'No','Hold','Yes'),(703,2,'Army',45,'Move',44,NULL,'No','Success','Yes'),(703,2,'Army',48,'Support move',44,45,'No','Hold','Yes'),(703,2,'Fleet',61,'Support move',60,46,'No','Hold','Yes'),(703,1,'Fleet',60,'Convoy',44,45,'No','Dislodged','Yes'),(704,2,'Fleet',46,'Move',60,NULL,'No','Hold','Yes'),(704,2,'Army',45,'Move',44,NULL,'No','Success','Yes'),(704,2,'Army',48,'Support move',44,45,'No','Hold','Yes'),(704,2,'Fleet',61,'Support move',60,46,'No','Hold','Yes'),(704,1,'Fleet',60,'Convoy',44,45,'No','Dislodged','Yes'),(704,1,'Army',44,'Move',45,NULL,'No','Dislodged','Yes'),(705,3,'Army',12,'Move',16,NULL,'No','Success','Yes'),(705,3,'Fleet',64,'Convoy',12,16,'No','Hold','Yes'),(705,6,'Army',16,'Move',12,NULL,'No','Success','Yes'),(705,6,'Fleet',65,'Convoy',12,16,'No','Hold','Yes'),(706,1,'Army',3,'Move',2,NULL,'No','Success','Yes'),(706,1,'Fleet',60,'Convoy',2,3,'No','Hold','Yes'),(706,4,'Army',2,'Move',3,NULL,'No','Success','Yes'),(706,2,'Fleet',59,'Hold',NULL,NULL,'No','Hold','Yes'),(706,2,'Fleet',53,'Hold',NULL,NULL,'No','Hold','Yes'),(706,7,'Fleet',52,'Convoy',2,3,'No','Hold','Yes'),(706,7,'Fleet',58,'Convoy',2,3,'No','Hold','Yes'),(707,1,'Fleet',54,'Convoy',35,34,'No','Hold','Yes'),(707,1,'Fleet',35,'Move',34,NULL,'No','Hold','Yes'),(707,7,'Army',34,'Move',35,NULL,'No','Hold','Yes'),(707,7,'Fleet',57,'Convoy',35,34,'No','Hold','No'),(708,2,'Army',44,'Move',43,NULL,'Yes','Success','Yes'),(708,1,'Fleet',53,'Move',55,NULL,'No','Hold','Yes'),(708,1,'Army',43,'Move',37,NULL,'No','Hold','Yes'),(709,1,'Army',35,'Move',34,NULL,'No','Success','Yes'),(709,1,'Fleet',54,'Convoy',34,35,'No','Hold','Yes'),(709,1,'Fleet',33,'Support move',34,35,'No','Hold','Yes'),(709,7,'Army',34,'Move',35,NULL,'No','Success','Yes'),(710,1,'Army',35,'Move',34,NULL,'Yes','Success','Yes'),(710,1,'Fleet',36,'Support move',34,35,'No','Hold','Yes'),(710,1,'Fleet',33,'Support move',34,35,'No','Hold','Yes'),(710,4,'Fleet',54,'Convoy',34,35,'No','Hold','Yes'),(710,7,'Army',34,'Move',35,NULL,'No','Dislodged','Yes'),(710,7,'Fleet',51,'Support move',35,34,'No','Hold','Yes'),(710,2,'Fleet',52,'Move',35,NULL,'No','Hold','Yes'),(710,2,'Fleet',53,'Support move',35,52,'No','Hold','Yes'),(711,1,'Fleet',35,'Support move',54,53,'No','Hold','Yes'),(711,1,'Fleet',53,'Move',54,NULL,'No','Success','Yes'),(711,7,'Army',34,'Move',35,NULL,'Yes','Hold','Yes'),(711,7,'Fleet',54,'Convoy',35,34,'No','Dislodged','Yes'),(711,7,'Fleet',51,'Support move',35,34,'No','Hold','Yes'),(712,1,'Army',3,'Move',2,NULL,'Yes','Success','Yes'),(712,1,'Fleet',58,'Convoy',2,3,'No','Hold','Yes'),(712,1,'Fleet',52,'Convoy',2,3,'No','Hold','Yes'),(712,4,'Army',2,'Move',3,NULL,'Yes','Success','Yes'),(712,4,'Fleet',53,'Convoy',3,2,'No','Hold','Yes'),(712,4,'Fleet',60,'Convoy',3,2,'No','Hold','Yes'),(712,4,'Fleet',59,'Convoy',3,2,'No','Hold','Yes'),(713,5,'Fleet',66,'Convoy',15,73,'No','Hold','Yes'),(713,5,'Army',73,'Move',15,NULL,'Yes','Dislodged','Yes'),(713,3,'Army',15,'Support move',73,18,'No','Hold','Yes'),(713,3,'Fleet',18,'Move',73,NULL,'No','Success','Yes'),(714,1,'Army',35,'Move',34,NULL,'No','Success','Yes'),(714,1,'Fleet',36,'Support move',34,35,'No','Hold','Yes'),(714,1,'Fleet',33,'Support move',34,35,'No','Hold','Yes'),(714,2,'Fleet',52,'Move',35,NULL,'No','Hold','Yes'),(714,2,'Fleet',53,'Support move',35,52,'No','Hold','Yes'),(714,4,'Fleet',54,'Convoy',35,34,'No','Hold','Yes'),(714,7,'Army',34,'Move',35,NULL,'Yes','Dislodged','Yes'),(714,7,'Fleet',51,'Support move',35,34,'No','Hold','Yes'),(715,1,'Fleet',53,'Convoy',44,6,'No','Hold','Yes'),(715,1,'Army',43,'Support move',44,6,'No','Hold','Yes'),(715,1,'Army',4,'Move',6,NULL,'No','Hold','Yes'),(715,1,'Army',6,'Move',44,NULL,'Yes','Success','Yes'),(715,2,'Fleet',60,'Convoy',6,44,'No','Hold','Yes'),(715,2,'Army',44,'Move',6,NULL,'Yes','Dislodged','Yes'),(716,1,'Army',35,'Move',34,NULL,'No','Success','Yes'),(716,1,'Army',36,'Support move',34,35,'No','Hold','Yes'),(716,1,'Fleet',56,'Support move',34,35,'No','Hold','Yes'),(716,1,'Fleet',53,'Move',35,NULL,'No','Hold','Yes'),(716,7,'Army',34,'Move',35,NULL,'Yes','Success','Yes'),(716,7,'Fleet',54,'Convoy',35,34,'No','Hold','Yes'),(716,7,'Fleet',52,'Support move',35,34,'No','Hold','Yes'),(717,1,'Army',35,'Move',34,NULL,'Yes','Success','Yes'),(717,1,'Army',36,'Support move',34,35,'No','Hold','Yes'),(717,1,'Fleet',56,'Support move',34,35,'No','Hold','Yes'),(717,1,'Fleet',54,'Convoy',34,35,'No','Hold','Yes'),(717,1,'Fleet',53,'Move',35,NULL,'No','Hold','Yes'),(717,7,'Army',34,'Move',35,NULL,'No','Success','Yes'),(717,7,'Fleet',52,'Support move',35,34,'No','Hold','Yes'),(718,1,'Fleet',53,'Convoy',44,6,'No','Hold','Yes'),(718,1,'Army',43,'Support move',44,6,'No','Hold','Yes'),(718,1,'Army',4,'Move',6,NULL,'No','Hold','Yes'),(718,1,'Army',6,'Move',44,NULL,'Yes','Success','Yes'),(718,1,'Army',42,'Support move',44,6,'No','Hold','Yes'),(718,2,'Fleet',60,'Convoy',6,44,'No','Hold','Yes'),(718,2,'Army',44,'Move',6,NULL,'Yes','Success','Yes'),(718,2,'Army',5,'Support move',6,44,'No','Hold','Yes'),(801,3,'Army',11,'Hold',NULL,NULL,'No','Hold','Yes'),(802,3,'Army',11,'Move',12,NULL,'No','Success','Yes'),(803,3,'Army',15,'Move',12,NULL,'No','Success','Yes'),(803,3,'Army',13,'Support move',12,15,'No','Hold','Yes'),(803,5,'Army',12,'Hold',NULL,NULL,'No','Dislodged','Yes'),(804,3,'Army',15,'Move',12,NULL,'No','Hold','Yes'),(804,3,'Army',13,'Support move',12,15,'No','Hold','Yes'),(804,5,'Army',12,'Hold',NULL,NULL,'No','Hold','Yes'),(804,5,'Fleet',64,'Support hold',12,NULL,'No','Hold','Yes'),(805,3,'Army',15,'Move',10,NULL,'Yes','Success','Yes'),(805,3,'Fleet',66,'Convoy',10,15,'No','Hold','Yes'),(805,3,'Fleet',65,'Convoy',10,15,'No','Hold','Yes'),(806,3,'Army',11,'Move',12,NULL,'No','Hold','Yes'),(806,5,'Army',12,'Hold',NULL,NULL,'No','Hold','Yes'),(807,3,'Army',15,'Move',16,NULL,'No','Hold','Yes'),(807,5,'Fleet',65,'Move',16,NULL,'No','Hold','Yes'),(808,3,'Fleet',15,'Hold',NULL,NULL,'No','Dislodged','Yes'),(808,3,'Army',12,'Support hold',15,NULL,'No','Hold','Yes'),(808,5,'Fleet',73,'Move',15,NULL,'No','Success','Yes'),(808,5,'Army',70,'Support move',15,73,'No','Hold','Yes'),(808,5,'Army',14,'Support move',15,73,'No','Hold','Yes'),(809,3,'Fleet',15,'Hold',NULL,NULL,'No','Hold','Yes'),(809,3,'Army',12,'Support hold',15,NULL,'No','Hold','Yes'),(809,3,'Fleet',16,'Support hold',15,NULL,'No','Hold','Yes'),(809,5,'Fleet',73,'Move',15,NULL,'No','Hold','Yes'),(809,5,'Army',70,'Support move',15,73,'No','Hold','Yes'),(809,5,'Army',14,'Support move',15,73,'No','Hold','Yes'),(810,3,'Fleet',15,'Hold',NULL,NULL,'No','Hold','Yes'),(810,3,'Army',12,'Support hold',15,NULL,'No','Hold','Yes'),(810,5,'Fleet',73,'Move',15,NULL,'No','Hold','Yes'),(810,5,'Army',70,'Support move',15,73,'No','Hold','Yes'),(810,5,'Army',14,'Support move',15,73,'No','Hold','Yes'),(810,4,'Army',41,'Move',70,NULL,'No','Hold','Yes'),(811,3,'Fleet',15,'Hold',NULL,NULL,'No','Dislodged','Yes'),(811,3,'Army',12,'Support hold',15,NULL,'No','Hold','Yes'),(811,5,'Fleet',73,'Move',15,NULL,'No','Success','Yes'),(811,5,'Army',70,'Support move',15,73,'No','Hold','Yes'),(811,5,'Army',14,'Support move',15,73,'No','Hold','Yes'),(811,4,'Army',41,'Move',70,NULL,'No','Hold','Yes'),(811,6,'Fleet',64,'Move',12,NULL,'No','Hold','Yes'),(812,7,'Fleet',30,'Move',56,NULL,'No','Hold','Yes'),(812,4,'Army',38,'Move',34,NULL,'Yes','Success','Yes'),(812,4,'Fleet',39,'Support hold',56,NULL,'No','Hold','Yes'),(812,4,'Fleet',56,'Convoy',34,38,'No','Success','Yes'),(812,7,'Fleet',57,'Support move',56,30,'No','Hold','Yes'),(901,1,'Fleet',51,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',52,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',53,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',54,'Convoy',32,26,'No','Hold','No'),(901,1,'Fleet',55,'Convoy',32,26,'No','Hold','No'),(901,1,'Fleet',56,'Convoy',32,26,'No','Hold','No'),(901,1,'Fleet',57,'Convoy',32,26,'No','Hold','No'),(901,1,'Fleet',58,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',59,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',60,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',61,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',62,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',63,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',64,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',65,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',66,'Convoy',32,26,'No','Hold','No'),(901,1,'Fleet',67,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',68,'Convoy',32,26,'No','Hold','Yes'),(901,1,'Fleet',69,'Convoy',32,26,'No','Hold','No'),(901,2,'Army',1,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',2,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',3,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',4,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',5,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',6,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',7,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',8,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',9,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',10,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',11,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',12,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',13,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',14,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',15,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',16,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',17,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',18,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',20,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',21,'Move',32,NULL,'Yes','Hold','No'),(901,2,'Army',22,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',23,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',24,'Move',32,NULL,'Yes','Hold','No'),(901,2,'Army',25,'Move',32,NULL,'Yes','Hold','No'),(901,2,'Army',26,'Move',32,NULL,'Yes','Success','Yes'),(901,2,'Army',27,'Move',32,NULL,'Yes','Hold','No'),(901,2,'Army',34,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',36,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',37,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',38,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',39,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',43,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',44,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',45,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',46,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',49,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',50,'Move',32,NULL,'Yes','Hold','Yes'),(901,2,'Army',73,'Move',32,NULL,'Yes','Hold','Yes');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_ForumMessages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `toID` int(10) unsigned NOT NULL,
  `fromUserID` mediumint(8) unsigned NOT NULL,
  `timeSent` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `subject` varchar(100) NOT NULL,
  `type` enum('ThreadStart','ThreadReply') NOT NULL,
  `replies` smallint(5) unsigned NOT NULL,
  `latestReplySent` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `latest` (`timeSent`),
  KEY `threadReplies` (`type`,`toID`,`timeSent`),
  KEY `latestReplySent` (`latestReplySent`),
  KEY `profileLinks` (`type`,`fromUserID`,`timeSent`),
  KEY `type` (`type`,`latestReplySent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_GameMessages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timeSent` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `turn` smallint(5) unsigned NOT NULL,
  `toCountryID` tinyint(3) unsigned NOT NULL,
  `fromCountryID` tinyint(3) unsigned NOT NULL,
  `gameID` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `toMember` (`gameID`,`toCountryID`),
  KEY `fromMember` (`gameID`,`fromCountryID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Games` (
  `variantID` tinyint(3) unsigned NOT NULL,
  `id` mediumint(5) unsigned NOT NULL AUTO_INCREMENT,
  `turn` smallint(5) unsigned NOT NULL DEFAULT '0',
  `phase` enum('Finished','Pre-game','Diplomacy','Retreats','Builds') NOT NULL DEFAULT 'Pre-game',
  `processTime` int(10) unsigned DEFAULT NULL,
  `pot` smallint(5) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `gameOver` enum('No','Won','Drawn') NOT NULL DEFAULT 'No',
  `processStatus` enum('Not-processing','Processing','Crashed','Paused') NOT NULL DEFAULT 'Not-processing',
  `password` varbinary(16) DEFAULT NULL,
  `potType` enum('Winner-takes-all','Points-per-supply-center') NOT NULL,
  `pauseTimeRemaining` mediumint(8) unsigned DEFAULT NULL,
  `minimumBet` smallint(5) unsigned DEFAULT NULL,
  `phaseMinutes` smallint(5) unsigned NOT NULL DEFAULT '1440',
  `anon` enum('Yes','No') NOT NULL DEFAULT 'No',
  `pressType` enum('Regular','PublicPressOnly','NoPress') NOT NULL DEFAULT 'Regular',
  `attempts` smallint(5) unsigned NOT NULL DEFAULT '0',
  `missingPlayerPolicy` enum('Normal','Strict') NOT NULL DEFAULT 'Normal',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gname` (`name`),
  KEY `processStatus` (`processStatus`,`processTime`),
  KEY `minimumBet` (`minimumBet`),
  KEY `turn` (`turn`),
  KEY `phase` (`phase`),
  KEY `pot` (`pot`),
  KEY `password` (`password`),
  KEY `potType` (`potType`,`turn`),
  KEY `potType_2` (`potType`,`id`),
  KEY `potType_3` (`potType`,`pot`),
  KEY `phase_2` (`phase`,`turn`),
  KEY `phase_3` (`phase`,`minimumBet`),
  KEY `phase_4` (`phase`,`id`),
  KEY `phase_5` (`phase`,`pot`),
  KEY `phase_6` (`phase`,`password`),
  KEY `phaseMinutes` (`phaseMinutes`),
  KEY `phase_7` (`phase`,`phaseMinutes`),
  KEY `anon` (`anon`),
  KEY `pressType` (`pressType`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Members` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `userID` mediumint(8) unsigned NOT NULL,
  `gameID` mediumint(8) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `status` enum('Playing','Defeated','Left','Won','Drawn','Survived','Resigned') NOT NULL DEFAULT 'Playing',
  `timeLoggedIn` int(10) unsigned NOT NULL,
  `bet` mediumint(8) unsigned NOT NULL,
  `missedPhases` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `newMessagesFrom` set('0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59','60','61','62','63') NOT NULL,
  `supplyCenterNo` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `unitNo` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `votes` set('Draw','Pause','Cancel') NOT NULL,
  `pointsWon` mediumint(8) unsigned DEFAULT NULL,
  `gameMessagesSent` mediumint(8) unsigned DEFAULT NULL,
  `orderStatus` set('None','Saved','Completed','Ready') NOT NULL DEFAULT 'None',
  PRIMARY KEY (`id`),
  KEY `gid` (`gameID`),
  KEY `playingCount` (`status`,`userID`),
  KEY `uid` (`userID`,`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Misc` (
  `name` enum('Version','Hits','Panic','Notice','Maintenance','LastProcessTime','GamesNew','GamesActive','GamesFinished','RankingPlayers','OnlinePlayers','ActivePlayers','TotalPlayers','ErrorLogs','GamesPaused','GamesOpen','GamesCrashed','LastModAction','ForumThreads','ThreadActiveThreshold','ThreadAliveThreshold','GameFeaturedThreshold') NOT NULL,
  `value` int(10) unsigned NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `wD_Misc` VALUES ('Hits',0),('Version',100),('Panic',0),('Notice',0),('Maintenance',0),('LastProcessTime',0),('GamesNew',0),('GamesActive',0),('GamesFinished',0),('RankingPlayers',0),('TotalPlayers',0),('ErrorLogs',0),('GamesPaused',0),('GamesCrashed',0),('LastModAction',0),('OnlinePlayers',0),('GamesOpen',0),('ActivePlayers',0),('ForumThreads',0),('ThreadActiveThreshold',0),('ThreadAliveThreshold',0),('GameFeaturedThreshold',0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_ModeratorNotes` (
  `linkIDType` enum('Game','User') COLLATE utf8_bin NOT NULL,
  `linkID` mediumint(8) unsigned NOT NULL,
  `type` enum('Report','PrivateNote','PublicNote') COLLATE utf8_bin NOT NULL,
  `fromUserID` mediumint(9) NOT NULL,
  `note` text COLLATE utf8_bin NOT NULL,
  `timeSent` int(10) unsigned NOT NULL,
  KEY `linkIDType` (`linkIDType`,`linkID`,`timeSent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Moves` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gameID` mediumint(8) unsigned NOT NULL,
  `orderID` int(10) unsigned NOT NULL,
  `unitID` int(10) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `moveType` enum('Hold','Move','Support hold','Support move','Convoy','Retreat','Disband','Build Army','Build Fleet','Wait','Destroy') NOT NULL,
  `terrID` smallint(5) unsigned NOT NULL,
  `toTerrID` smallint(5) unsigned DEFAULT NULL,
  `fromTerrID` smallint(5) unsigned DEFAULT NULL,
  `viaConvoy` enum('No','Yes') NOT NULL DEFAULT 'No',
  `success` enum('No','Yes','Undecided') NOT NULL DEFAULT 'Undecided',
  `dislodged` enum('No','Yes','Undecided') NOT NULL DEFAULT 'Undecided',
  `path` enum('No','Yes','Undecided') NOT NULL DEFAULT 'Undecided',
  PRIMARY KEY (`id`),
  KEY `unitID` (`unitID`),
  KEY `orderID` (`orderID`),
  KEY `gameID` (`gameID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_MovesArchive` (
  `gameID` mediumint(8) unsigned NOT NULL,
  `turn` smallint(5) unsigned NOT NULL,
  `terrID` smallint(5) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `unitType` enum('Army','Fleet') DEFAULT NULL,
  `success` enum('Yes','No') NOT NULL,
  `dislodged` enum('Yes','No') NOT NULL DEFAULT 'No',
  `type` enum('Hold','Move','Support hold','Support move','Convoy','Retreat','Disband','Build Army','Build Fleet','Wait','Destroy') NOT NULL,
  `toTerrID` smallint(5) unsigned DEFAULT NULL,
  `fromTerrID` smallint(5) unsigned DEFAULT NULL,
  `viaConvoy` enum('No','Yes') NOT NULL DEFAULT 'No',
  KEY `Map` (`gameID`,`turn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Notices` (
  `toUserID` mediumint(8) unsigned NOT NULL,
  `fromID` mediumint(8) unsigned NOT NULL,
  `type` enum('PM','Game','User') NOT NULL,
  `keep` enum('Yes','No') NOT NULL,
  `private` enum('Yes','No') NOT NULL,
  `text` text NOT NULL,
  `linkName` varchar(100) NOT NULL,
  `linkID` mediumint(8) unsigned DEFAULT NULL,
  `timeSent` int(10) unsigned NOT NULL,
  KEY `homePageIndex` (`toUserID`,`timeSent`),
  KEY `deleteIndex` (`keep`,`timeSent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gameID` mediumint(8) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `type` enum('Hold','Move','Support hold','Support move','Convoy','Retreat','Disband','Build Army','Build Fleet','Wait','Destroy') NOT NULL,
  `unitID` int(10) unsigned DEFAULT NULL,
  `toTerrID` smallint(5) unsigned DEFAULT NULL,
  `fromTerrID` smallint(5) unsigned DEFAULT NULL,
  `viaConvoy` enum('No','Yes') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unitID` (`unitID`),
  KEY `gameID` (`gameID`,`countryID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_PointsTransactions` (
  `type` enum('Supplement','Bet','Won','Returned','Trigger') NOT NULL,
  `userID` mediumint(9) NOT NULL,
  `gameID` mediumint(9) DEFAULT NULL,
  `memberID` mediumint(9) DEFAULT NULL,
  `points` mediumint(9) NOT NULL,
  KEY `userID` (`userID`),
  KEY `gameID` (`gameID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Sessions` (
  `userID` mediumint(8) unsigned NOT NULL,
  `lastRequest` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hits` smallint(5) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `userAgent` binary(2) NOT NULL,
  `cookieCode` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userID`),
  KEY `lastrequesttime` (`lastRequest`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_TerrStatus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `terrID` smallint(5) unsigned NOT NULL,
  `occupiedFromTerrID` smallint(5) unsigned DEFAULT NULL,
  `standoff` enum('No','Yes') NOT NULL DEFAULT 'No',
  `gameID` mediumint(8) unsigned NOT NULL,
  `occupyingUnitID` int(10) unsigned DEFAULT NULL,
  `retreatingUnitID` int(10) unsigned DEFAULT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gameID` (`gameID`,`terrID`),
  KEY `retreatingUnitID` (`retreatingUnitID`),
  KEY `occupyingUnitID` (`occupyingUnitID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_TerrStatusArchive` (
  `terrID` smallint(5) unsigned NOT NULL,
  `turn` smallint(5) unsigned NOT NULL,
  `standoff` enum('No','Yes') NOT NULL DEFAULT 'No',
  `gameID` mediumint(8) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  KEY `Map` (`gameID`,`turn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Territories` (
  `mapID` tinyint(3) unsigned NOT NULL,
  `id` smallint(5) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `type` enum('Sea','Land','Coast') NOT NULL,
  `supply` enum('No','Yes') NOT NULL,
  `mapX` smallint(5) unsigned NOT NULL,
  `mapY` smallint(5) unsigned NOT NULL,
  `smallMapX` smallint(5) unsigned NOT NULL,
  `smallMapY` smallint(5) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `coast` enum('No','Parent','Child') NOT NULL,
  `coastParentID` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`mapID`,`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_UnitDestroyIndex` (
  `mapID` tinyint(3) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `terrID` smallint(5) unsigned NOT NULL,
  `unitType` enum('Army','Fleet') NOT NULL,
  `destroyIndex` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`mapID`,`countryID`,`terrID`,`unitType`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Units` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('Army','Fleet') NOT NULL,
  `terrID` smallint(5) unsigned NOT NULL,
  `countryID` tinyint(3) unsigned NOT NULL,
  `gameID` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gameID` (`gameID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wD_Users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `email` varchar(90) NOT NULL,
  `points` mediumint(8) unsigned NOT NULL DEFAULT '100',
  `comment` text,
  `homepage` text,
  `hideEmail` enum('No','Yes') NOT NULL,
  `timeJoined` int(10) unsigned NOT NULL,
  `locale` enum('English') NOT NULL,
  `timeLastSessionEnded` int(10) unsigned NOT NULL,
  `lastMessageIDViewed` int(10) unsigned NOT NULL DEFAULT '0',
  `password` binary(16) NOT NULL,
  `type` set('Banned','Guest','System','User','Moderator','Admin','Donator') NOT NULL DEFAULT 'User',
  `notifications` set('PrivateMessage','GameMessage','Unfinalized','GameUpdate') NOT NULL DEFAULT '',
  `ChanceEngland` float NOT NULL DEFAULT '0.142857',
  `ChanceFrance` float NOT NULL DEFAULT '0.142857',
  `ChanceItaly` float NOT NULL DEFAULT '0.142857',
  `ChanceGermany` float NOT NULL DEFAULT '0.142857',
  `ChanceAustria` float NOT NULL DEFAULT '0.142857',
  `ChanceRussia` float NOT NULL DEFAULT '0.142857',
  `ChanceTurkey` float NOT NULL DEFAULT '0.142857',
  `muteReports` enum('No','Yes') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uname` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `points` (`points`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `wD_Users` VALUES (1,'Guest','guest@nomail.com',0,'','','Yes',1154508107,'English',1154508107,0,'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0','Guest,System','',0.142857,0.142857,0.142857,0.142857,0.142857,0.142857,0.142857,'Yes'),(2,'GameMaster','gamemaster@nomail.com',140,'','','Yes',1154508107,'English',1154508107,0,'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0','System','',0.142857,0.142857,0.142857,0.142857,0.142857,0.142857,0.142857,'No'),(3,'Civil Disorder Germany','civil1@nomail.com',50,'','','Yes',1154508107,'English',1154508107,0,'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0','System','',0.122,0.244,0.012,0.01,0.122,0.244,0.244,'No'),(4,'Civil Disorder Italy','civil2@nomail.com',0,'','','Yes',1154508107,'English',1154508107,0,'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0','System','',0.196,0.196,0.01,0.01,0.196,0.196,0.196,'No');

CREATE TABLE `wD_MuteUser` (
	`userID` mediumint(8) unsigned NOT NULL,
	`muteUserID` mediumint(8) unsigned NOT NULL,
	PRIMARY KEY (`userID`,`muteUserID`)
) ENGINE=MyISAM;

CREATE TABLE `wD_MuteCountry` (
	`userID` MEDIUMINT UNSIGNED NOT NULL ,
	`gameID` MEDIUMINT UNSIGNED NOT NULL ,
	`muteCountryID` TINYINT UNSIGNED NOT NULL,
	PRIMARY KEY ( `userID` , `gameID` , `muteCountryID` )
) ENGINE=MYISAM ;

UPDATE wD_Misc SET `value`=101 WHERE `name`='Version';

ALTER TABLE `wD_Users` CHANGE `type` `type` SET( 'Banned', 'Guest', 'System', 'User', 'Moderator', 'Admin', 'Donator', 'DonatorBronze', 'DonatorSilver', 'DonatorGold', 'DonatorPlatinum' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'User';

UPDATE wD_Misc SET `value`=102 WHERE `name`='Version';

CREATE TABLE IF NOT EXISTS `wD_LikePost` (
  `userID` mediumint(8) unsigned NOT NULL,
  `likeMessageID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userID`,`likeMessageID`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `wD_MuteThread` (
  `userID` mediumint(8) unsigned NOT NULL,
  `muteThreadID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userID`,`muteThreadID`)
) ENGINE=MyISAM;

ALTER TABLE `wD_LikePost` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `wD_MuteThread` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `wD_MuteUser` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;
ALTER TABLE `wD_MuteCountry` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE wD_Misc SET `value`=103 WHERE `name`='Version';

CREATE TABLE `wD_Silences` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT UNSIGNED NULL ,
	`postID` INT UNSIGNED NULL ,
	`moderatorUserID` MEDIUMINT UNSIGNED NOT NULL ,
	`enabled` BIT( 1 ) NOT NULL DEFAULT 1,
	`startTime` BIGINT UNSIGNED NOT NULL,
	`length` smallint UNSIGNED NOT NULL DEFAULT 7 ,
	`reason` VARCHAR( 150 ) NOT NULL ,
	PRIMARY KEY ( `id` ) ,
	INDEX ( `userID` ) ,
	INDEX ( `postID` )
) ENGINE = InnoDB ;

ALTER TABLE `wD_ForumMessages`
ADD `silenceID` INT UNSIGNED NULL DEFAULT NULL ;

ALTER TABLE `wD_Users`
ADD `silenceID` INT UNSIGNED NULL DEFAULT NULL ;

ALTER TABLE `wD_Users`
CHANGE `type` `type` SET(
	'Banned', 'Guest', 'System', 'User', 'Moderator',
	'Admin', 'Donator', 'DonatorBronze', 'DonatorSilver',
	'DonatorGold', 'DonatorPlatinum', 'ForumModerator'
) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'User';

ALTER TABLE `wD_PointsTransactions` CHANGE `type` `type` ENUM( 'Supplement', 'Bet', 'Won', 'Returned', 'Trigger', 'Correction' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `wD_ForumMessages`  ADD `likeCount` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0';

UPDATE wD_ForumMessages fm
INNER JOIN (
	SELECT f.id, COUNT(*) as likeCount
	FROM wD_ForumMessages f
	INNER JOIN wD_LikePost lp ON f.id = lp.likeMessageID
	GROUP BY f.id
) l ON l.id = fm.id
SET fm.likeCount = l.likeCount;

ALTER TABLE `wD_Members` CHANGE `newMessagesFrom` `newMessagesFrom` SET( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60', '61', '62', '63' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `wD_Members` CHANGE `votes` `votes` set('Draw','Pause','Cancel') NOT NULL DEFAULT '';
ALTER TABLE `wD_Members` CHANGE `countryID` `countryID` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `wD_Games`  ADD `directorUserID` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `wD_Backup_Games`  ADD `directorUserID` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `wD_Backup_Games` CHANGE `missingPlayerPolicy` `missingPlayerPolicy` ENUM( 'Normal', 'Strict', 'Wait' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Normal';
ALTER TABLE `wD_Games` CHANGE `missingPlayerPolicy` `missingPlayerPolicy` ENUM( 'Normal', 'Strict', 'Wait' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Normal';

CREATE TABLE `wD_VariantData` (
  `variantID` tinyint(3) unsigned NOT NULL,
  `gameID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `systemToken` int(10) unsigned NOT NULL DEFAULT '0',
  `typeID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `offset` int(10) unsigned NOT NULL DEFAULT '0',
  `val_int` int(11) NOT NULL DEFAULT '0',
  `val_float` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `wD_VariantData` ADD PRIMARY KEY ( `variantID` , `gameID`, `systemToken` , `typeID` , `userID` , `offset` ) ;

INSERT INTO wD_VariantData (variantID, systemToken, userID, `offset`, val_float )
SELECT 1, 948379409, u.id, 1, ChanceEngland
FROM wD_Users u
WHERE NOT ChanceEngland = 0.142857
UNION SELECT 1, 948379409, u.id, 2, ChanceFrance
FROM wD_Users u
WHERE NOT ChanceFrance = 0.142857
UNION SELECT 1, 948379409, u.id, 3, ChanceItaly
FROM wD_Users u
WHERE NOT ChanceItaly = 0.142857
UNION SELECT 1, 948379409, u.id, 4, ChanceGermany
FROM wD_Users u
WHERE NOT ChanceGermany = 0.142857
UNION SELECT 1, 948379409, u.id, 5, ChanceAustria
FROM wD_Users u
WHERE NOT ChanceAustria = 0.142857
UNION SELECT 1, 948379409, u.id, 6, ChanceRussia
FROM wD_Users u
WHERE NOT ChanceRussia = 0.142857
UNION SELECT 1, 948379409, u.id, 7, ChanceTurkey
FROM wD_Users u
WHERE NOT ChanceTurkey = 0.142857;

ALTER TABLE `wD_Users` ADD COLUMN `cdCount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `nmrCount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `cdTakenCount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `phaseCount` int(10) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `gameCount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `reliabilityRating` double NOT NULL DEFAULT '1',
  ADD COLUMN `deletedCDs` int(11) DEFAULT '0';

CREATE TABLE IF NOT EXISTS `wD_NMRs` (
	  `gameID` mediumint(5) unsigned NOT NULL,
	  `userID` mediumint(8) unsigned NOT NULL,
	  `countryID` tinyint(3) unsigned NOT NULL,
	  `turn` smallint(5) unsigned NOT NULL,
	  `bet` smallint(5) unsigned NOT NULL,
	  `SCCount` smallint(5) unsigned NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `wD_NMRs`
  ADD KEY `gameID` (`gameID`,`userID`), ADD KEY `userID` (`userID`);

ALTER TABLE wD_CivilDisorders ADD COLUMN forcedByMod BOOLEAN DEFAULT 0;

ALTER TABLE wD_Games ADD drawType enum('draw-votes-public','draw-votes-hidden') NOT NULL DEFAULT 'draw-votes-public';
ALTER TABLE wD_Backup_Games ADD drawType enum('draw-votes-public','draw-votes-hidden') NOT NULL DEFAULT 'draw-votes-public';

 CREATE TABLE `wD_WatchedGames` (
	  `userID` mediumint(8) unsigned NOT NULL,
	  `gameID` mediumint(8) unsigned NOT NULL,
	  KEY `gid` (`gameID`),
	  KEY `uid` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE wD_Games ADD `minimumReliabilityRating` tinyint(3) unsigned NOT NULL DEFAULT '0';
ALTER TABLE wD_Backup_Games ADD `minimumReliabilityRating` tinyint(3) unsigned NOT NULL DEFAULT '0';

CREATE TABLE `wD_UserOptions` (
	  `userID` mediumint(8) unsigned NOT NULL,
	  `colourblind` enum('No','Protanope','Deuteranope','Tritanope') NOT NULL DEFAULT 'No',
	  `displayUpcomingLive` enum('No','Yes') NOT NULL DEFAULT 'Yes',
	  `showMoves` enum('No','Yes') NOT NULL DEFAULT 'Yes',
	  KEY `uid` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER table wD_UnitDestroyIndex MODIFY destroyIndex smallint;

ALTER TABLE wD_Games CHANGE `potType` `potType` enum('Winner-takes-all','Points-per-supply-center','Unranked','Sum-of-squares') NOT NULL;
ALTER TABLE wD_Backup_Games CHANGE `potType` `potType` enum('Winner-takes-all','Points-per-supply-center','Unranked','Sum-of-squares') NOT NULL;

ALTER TABLE wD_Backup_Games CHANGE `pressType` `pressType` enum('Regular','PublicPressOnly','NoPress','RulebookPress') NOT NULL DEFAULT 'Regular';
ALTER TABLE wD_Games CHANGE `pressType` `pressType` enum('Regular','PublicPressOnly','NoPress','RulebookPress') NOT NULL DEFAULT 'Regular';

ALTER TABLE `wD_Users` ADD `tempBan` int(10) unsigned;

CREATE TABLE `wD_UserConnections` (
`userID` mediumint(8) unsigned NOT NULL UNIQUE,
`modLastCheckedBy` mediumint(8) unsigned,
`modLastCheckedOn` int(10) unsigned,
`matchesLastUpdatedOn` int(10) unsigned,
`countMatchedIPUsers` mediumint(8) unsigned NOT NULL DEFAULT 0,
`countMatchedCookieUsers` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `wD_Members` ADD `hideNotifications` boolean DEFAULT false;
ALTER TABLE `wD_Backup_Members` ADD `hideNotifications` boolean DEFAULT false;

ALTER TABLE `wD_UserOptions` ADD `orderSort` enum('No Sort','Alphabetical','Convoys Last') NOT NULL DEFAULT 'Convoys Last';

ALTER TABLE `wD_Users` ADD `emergencyPauseDate` int(10) unsigned Default 0;

CREATE TABLE IF NOT EXISTS `wD_MissedTurns` (
	  `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	  `gameID` mediumint(5) unsigned NOT NULL,
	  `userID` mediumint(8) unsigned NOT NULL,
	  `countryID` tinyint(3) unsigned NOT NULL,
	  `turn` smallint(5) unsigned NOT NULL,
	  `bet` smallint(5) unsigned NOT NULL,
	  `SCCount` smallint(5) unsigned NOT NULL,
	  `forcedByMod` BOOLEAN DEFAULT 0,
	  `systemExcused` BOOLEAN DEFAULT 0,
	  `modExcused` BOOLEAN DEFAULT 0,
	  `turnDateTime` int(10) unsigned,
	  `modExcusedReason` text,
	  `samePeriodExcused` BOOLEAN DEFAULT 0,
	  KEY `missedPerUserPerDate` (`userID`,`turnDateTime`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `wD_Members` ADD `excusedMissedTurns` int(10) unsigned DEFAULT 1;
ALTER TABLE `wD_Games` ADD `excusedMissedTurns` int(10) unsigned DEFAULT 1;
ALTER TABLE `wD_Users` ADD `yearlyPhaseCount` mediumint(8) unsigned DEFAULT 0;
ALTER TABLE `wD_Backup_Members` ADD `excusedMissedTurns` int(10) unsigned DEFAULT 1;
ALTER TABLE `wD_Backup_Games` ADD `excusedMissedTurns` int(10) unsigned DEFAULT 1;

 CREATE TABLE IF NOT EXISTS `wD_TurnDate` (
	  `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	  `gameID` mediumint(5) unsigned NOT NULL,
	  `userID` mediumint(8) unsigned NOT NULL,
	  `countryID` tinyint(3) unsigned NOT NULL,
	  `turn` smallint(5) unsigned NOT NULL,
	  `turnDateTime` int(10) unsigned,
	  KEY `turnsByDate` (`userID`,`turnDateTime`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `wD_Config` (
  `name` enum('Notice','Panic','Maintenance') NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO wD_Config VALUES ('Notice','Default server-wide notice message.'),('Panic','Game processing has been paused and user registration has been disabled while a problem is resolved.'),('Maintenance','Server is in maintenance mode; only admins can fully interact with the server.'),('ServerOffline','');

ALTER TABLE `wD_Games` MODIFY `pot` MEDIUMINT(8);
ALTER TABLE `wD_Games` MODIFY `minimumBet` MEDIUMINT(8);
ALTER TABLE `wD_Backup_Games` MODIFY `pot` MEDIUMINT(8);
ALTER TABLE `wD_Backup_Games` MODIFY `minimumBet` MEDIUMINT(8);

UPDATE `wD_Misc` SET `value` = '153' WHERE `name` = 'Version';

ALTER TABLE `wD_Users` ADD `tempBanReason` text;   

UPDATE `wD_Misc` SET `value` = '154' WHERE `name` = 'Version';

CREATE TABLE `wD_Tournaments` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`name` VARCHAR( 150 ) NOT NULL,
`description` TEXT,
`status` enum('PreStart','Registration','Active', 'Finished') NOT NULL,
`minRR` tinyint (3) unsigned, 
`year` SMALLINT(4) unsigned,
`totalRounds` tinyint(3) unsigned,
`forumThreadLink` VARCHAR(150),
`externalLink` VARCHAR(300),
`directorID` mediumint(8) unsigned NOT NULL,
`coDirectorID` mediumint(8) unsigned NOT NULL,
`firstPlace` mediumint(8) unsigned NOT NULL,
`secondPlace` mediumint(8) unsigned NOT NULL,
`thirdPlace` mediumint(8) unsigned NOT NULL,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_TournamentGames` (
`tournamentID` mediumint(8) unsigned NOT NULL,
`gameID` mediumint(8) unsigned NOT NULL,
`round` tinyint(3) unsigned,
INDEX ( `tournamentID` ),
INDEX ( `gameID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_TournamentParticipants` (
`tournamentID` mediumint(8) unsigned NOT NULL,
`userID` mediumint(8) unsigned NOT NULL,
`status` enum('Applied','Accepted','Rejected', 'Left') NOT NULL,
INDEX ( `tournamentID` ),
INDEX ( `userID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_TournamentSpectators` (
`tournamentID` mediumint(8) unsigned NOT NULL,
`userID` mediumint(8) unsigned NOT NULL,
INDEX ( `tournamentID` ),
INDEX ( `userID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_TournamentScoring` (
`tournamentID` mediumint(8) unsigned NOT NULL,
`userID` mediumint(8) unsigned NOT NULL,
`round` tinyint(3) unsigned,
`score` FLOAT,
INDEX ( `tournamentID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_ApiKeys` (
    `apiKey` varchar(80) NOT NULL UNIQUE,
    `userID` mediumint(8) unsigned NOT NULL DEFAULT 0,
    CONSTRAINT `wD_ApiKeys_wD_Users_id_fk` FOREIGN KEY (`userID`) REFERENCES `wD_Users` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) DEFAULT CHARSET=utf8;
CREATE index `wD_ApiKeys_userID_index` ON `wD_ApiKeys` (`userID`);
ALTER TABLE `wD_ApiKeys` ADD CONSTRAINT `wD_ApiKeys_pk` PRIMARY KEY (`apiKey`);

CREATE TABLE `wD_ApiPermissions` (
    `userID` mediumint(8) unsigned NOT NULL UNIQUE DEFAULT 0,
    `getStateOfAllGames` enum('No', 'Yes') DEFAULT 'No' NOT NULL,
    `submitOrdersForUserInCD` enum('No', 'Yes') DEFAULT 'No' NOT NULL,
    `listGamesWithPlayersInCD` enum('No', 'Yes') DEFAULT 'No' NOT NULL,
    CONSTRAINT `wD_ApiPermissions_wD_Users_id_fk` FOREIGN KEY (`userID`) REFERENCES `wD_Users` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
);
ALTER TABLE `wD_ApiPermissions` ADD CONSTRAINT `wD_ApiPermissions_pk` PRIMARY KEY (`userID`);

ALTER TABLE `wD_MissedTurns` ADD `liveGame` BOOLEAN DEFAULT 0;  

update wD_MissedTurns m inner join wD_Games g on g.id = m.gameID set m.liveGame = 1 where g.phaseMinutes < 61 and g.id is not null;

ALTER TABLE `wD_UserOptions` ADD `darkMode` enum('Yes','No') NOT NULL DEFAULT 'No';

CREATE TABLE `wD_VariantInfo` (
  `variantID` smallint(4) unsigned NOT NULL,
  `mapID` smallint(4) unsigned NOT NULL,
  `supplyCenterTarget` smallint(4) unsigned NOT NULL,
  `supplyCenterCount` smallint(4) unsigned NOT NULL,
  `countryCount` smallint(4) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `fullName` varchar(50) NOT NULL,
  `description` varchar(500) NOT NULL,
  `author` varchar(50) NOT NULL,
  `adapter` varchar(50),
  `version` varchar(10),
  `codeVersion` varchar(10),
  `homepage` varchar(100),
  `countriesList` varchar(800) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `wD_Games` ADD `finishTime` int(10) unsigned DEFAULT NULL;

ALTER TABLE `wD_Backup_Games` ADD `finishTime` int(10) unsigned DEFAULT NULL;

ALTER TABLE `wD_Users`
CHANGE `type` `type` SET(
	'Banned', 'Guest', 'System', 'User', 'Moderator',
	'Admin', 'Donator', 'DonatorBronze', 'DonatorSilver',
	'DonatorGold', 'DonatorPlatinum', 'ForumModerator', 'FtfTD',
	'DonatorAdamantium', 'DonatorService', 'DonatorOwner', 'Bot'
) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'User';

ALTER TABLE `wD_Members` MODIFY `votes` set('Draw','Pause','Cancel','Concede');

UPDATE `wD_Misc` SET `value` = '161' WHERE `name` = 'Version';

ALTER TABLE `wD_Games` ADD COLUMN `playerTypes` enum('Members', 'Mixed', 'MemberVsBots') DEFAULT 'Members' NOT NULL;
ALTER TABLE `wD_Backup_Games` ADD COLUMN `playerTypes` enum('Members', 'Mixed', 'MemberVsBots') DEFAULT 'Members' NOT NULL;

ALTER TABLE `wD_Games`
ADD COLUMN `nextPhaseMinutes` int(10) UNSIGNED DEFAULT 0 NOT NULL AFTER `phaseMinutes`,
ADD COLUMN `phaseSwitchPeriod` int(10) DEFAULT -1 AFTER `nextPhaseMinutes`,
ADD COLUMN `startTime` int(10) UNSIGNED DEFAULT NULL;

UPDATE `wD_Games`
SET `nextPhaseMinutes` = `phaseMinutes`
WHERE `nextPhaseMinutes` = 0;

ALTER TABLE `wD_Backup_Games`
ADD COLUMN `nextPhaseMinutes` int(10) UNSIGNED DEFAULT 0 NOT NULL AFTER `phaseMinutes`,
ADD COLUMN `phaseSwitchPeriod` int(10) DEFAULT -1 AFTER `nextPhaseMinutes`,
ADD COLUMN `startTime` int(10) UNSIGNED DEFAULT NULL;

UPDATE `wD_Backup_Games`
SET `nextPhaseMinutes` = `phaseMinutes`
WHERE `nextPhaseMinutes` = 0;

CREATE TABLE `wD_UsernameHistory` (
  `userID` mediumint(8) NOT NULL,
  `oldUsername` varchar(30) NOT NULL,
  `newUsername` varchar(30) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `reason` varchar(50) NOT NULL,
  `changedBy` varchar(30) NOT NULL
);

CREATE TABLE `wD_EmailHistory` (
  `userID` mediumint(8) NOT NULL,
  `oldEmail` varchar(90) NOT NULL,
  `newEmail` varchar(90) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `reason` varchar(50) NOT NULL,
  `changedBy` varchar(30) NOT NULL
);

ALTER TABLE `wD_Users`
CHANGE `type` `type` SET(
	'Banned', 'Guest', 'System', 'User', 'Moderator',
	'Admin', 'Donator', 'DonatorBronze', 'DonatorSilver',
	'DonatorGold', 'DonatorPlatinum', 'ForumModerator', 'FtfTD',
	'DonatorAdamantium', 'DonatorService', 'DonatorOwner', 'Bot', 'SeniorMod'
) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'User';

CREATE TABLE `wD_GhostRatings` (
`userID` mediumint(8) unsigned NOT NULL,
`categoryID` mediumint(8) unsigned NOT NULL,
`rating` FLOAT,
`peakRating` FLOAT,
`yearMonth` mediumint(6) unsigned NOT NULL,
INDEX ( `userID` ),
INDEX ( `categoryID` ),
INDEX ( `yearMonth` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_GhostRatingsHistory` (
`userID` mediumint(8) unsigned NOT NULL,
`categoryID` mediumint(8) unsigned NOT NULL,
`yearMonth` mediumint(6) unsigned NOT NULL,
`rating` FLOAT,
INDEX ( `userID` ),
INDEX ( `categoryID` ),
INDEX ( `yearMonth` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_GhostRatingsBackup` (
`userID` mediumint(8) unsigned NOT NULL,
`categoryID` mediumint(8) unsigned NOT NULL,
`gameID` mediumint(8) unsigned NOT NULL,
`adjustment` FLOAT,
`timeFinished` int(10) unsigned NOT NULL,
INDEX ( `userID`),
INDEX ( `categoryID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `wD_Games`
ADD COLUMN `grCalculated` INT NOT NULL DEFAULT 0,
ADD INDEX ( `grCalculated`);

ALTER TABLE `wD_Backup_Games`
ADD COLUMN `grCalculated` INT NOT NULL DEFAULT 0,
ADD INDEX (`grCalculated`);

/**************************************** Version 1.67 ****************************************/

UPDATE `wD_Misc` SET `value` = '167' WHERE `name` = 'Version';

ALTER TABLE `wD_GameMessages` ADD `phaseMarker` ENUM('Finished','Pre-game','Diplomacy','Retreats','Builds') NULL DEFAULT NULL AFTER `gameID`; 
ALTER TABLE `wD_Backup_GameMessages` ADD `phaseMarker` ENUM('Finished','Pre-game','Diplomacy','Retreats','Builds') NULL DEFAULT NULL AFTER `gameID`; 

ALTER TABLE `wD_Users` ADD `optInFeatures` int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `tempBanReason`;

/**************************************** Version 1.68 ****************************************/

UPDATE `wD_Misc` SET `value` = '168' WHERE `name` = 'Version';
CREATE TABLE `wD_PaypalIPN` ( 
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
 `userID` int(10) unsigned NOT NULL, 
 `email` varchar(250) NOT NULL, 
 `value` float NOT NULL, 
 `currency` varchar(10) NOT NULL, 
 `status` varchar(100) NOT NULL, 
 `receivedTime` bigint(20) unsigned NOT NULL, 
 PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `wD_GameMessages_Redacted` (                                                                                                                                             
  `id` bigint(20) unsigned NOT NULL,                                                                                                                                                  
  `timeSent` int(10) unsigned NOT NULL,                                                                                                                                               
  `message` text NOT NULL,                                                                                                                                                            
  `turn` smallint(5) unsigned NOT NULL,                                                                                                                                               
  `toCountryID` tinyint(3) unsigned NOT NULL,                                                                                                                                         
  `fromCountryID` tinyint(3) unsigned NOT NULL,                                                                                                                                       
  `gameID` mediumint(8) unsigned NOT NULL,                                                                                                                                            
  `phaseMarker` enum('Finished','Pre-game','Diplomacy','Retreats','Builds') DEFAULT NULL,                                                                                             
  PRIMARY KEY (`id`) USING BTREE,                                                                                                                                                     
  KEY `toMember` (`gameID`,`toCountryID`) USING BTREE,                                                                                                                                
  KEY `fromMember` (`gameID`,`fromCountryID`) USING BTREE                                                                                                                             
) ENGINE=InnoDB DEFAULT CHARSET=utf8;                  

/**************************************** Version 1.69 ****************************************/

ALTER TABLE `wD_Misc` CHANGE `value` `value` BIGINT(10) UNSIGNED NOT NULL; 
UPDATE `wD_Misc` SET `value` = '169' WHERE `name` = 'Version';

-- Group tags for quick flagging of relationships between players without needing to link to a bunch of tables
ALTER TABLE `wD_Users` ADD COLUMN `groupTag` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `wD_Members` ADD COLUMN `groupTag` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `wD_Backup_Members` ADD COLUMN `groupTag` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;

-- New browser fingerprinting for multi account detection
ALTER TABLE `wD_AccessLog` ADD COLUMN `browserFingerprint` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `wD_Sessions` ADD COLUMN `browserFingerprint` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 

-- Optimizing the updating of phases per year for a user
ALTER TABLE `wD_TurnDate` ADD `isInReliabilityPeriod` BOOLEAN NULL DEFAULT FALSE AFTER `id`; 
ALTER TABLE `wD_TurnDate` CHANGE `turnDateTime` `turnDateTime` INT(10) UNSIGNED NOT NULL; 

ALTER TABLE `wD_TurnDate` 
    ADD INDEX `indUsersInReliabilityPeriod` (`isInReliabilityPeriod`, `userID`) USING BTREE,
	ADD INDEX `indTimestamp` (`turnDateTime`) USING BTREE,
	ADD INDEX `indIncludedInReliabilityPeriod` (`isInReliabilityPeriod`, `turnDateTime`) USING BTREE;

    
UPDATE wD_TurnDate SET isInReliabilityPeriod = 1 WHERE turnDateTime > UNIX_TIMESTAMP() - 365*24*60*60;

-- A new type of notice that a user is suspected and should respond
ALTER TABLE `wD_Notices` CHANGE `type` `type` ENUM('PM','Game','User','Group') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

-- A new type of forum message to facilitate discussions of user relationships
ALTER TABLE `wD_ForumMessages` CHANGE `type` `type` ENUM('ThreadStart','ThreadReply','GroupDiscussion') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

-- A table to allow users to register relationships between each other
CREATE TABLE `wD_Groups` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(15) NOT NULL,
	`type` ENUM('Person','Family','School','Work','Other','Unknown') NOT NULL,
	`isActive` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	`gameID` MEDIUMINT(8) UNSIGNED NULL,
	`display` SET('Profile','Usertag','AnonGames','Moderators') NOT NULL DEFAULT '',
	`timeCreated` BIGINT(20) UNSIGNED NOT NULL,
	`ownerUserID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`ownerCountryID` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`description` VARCHAR(2000) NULL DEFAULT NULL,
	`moderatorNotes` VARCHAR(2000) NULL DEFAULT NULL,
	`timeChanged` BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `indGroupsLastChanged` (`timeChanged`) USING BTREE
)
ENGINE=InnoDB
;

CREATE TABLE `wD_GroupUsers` (
	`userID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`countryID` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`groupID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`isActive` TINYINT(1) NOT NULL,
	`userWeighting` TINYINT(1) NOT NULL,
	`ownerWeighting` TINYINT(1) NOT NULL,
	`modWeighting` TINYINT(1) NOT NULL,
	`modUserID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	`timeChanged` BIGINT(20) UNSIGNED NOT NULL,
	`timeCreated` BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY (`userID`, `groupID`, `isActive`) USING BTREE,
	UNIQUE INDEX `groupUsersByGroup` (`userID`, `groupID`, `isActive`) USING BTREE,
	INDEX `groupUsersChanged` (`timeChanged`) USING BTREE
)
ENGINE=InnoDB
;

-- A table to allow users to link to external authentication providers, to help users verify their accounts are real and they aren't multi accounters
CREATE TABLE `wD_UserOpenIDLinks` (
  `userID` mediumint(8) UNSIGNED NOT NULL,
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

ALTER TABLE `wD_UserOpenIDLinks` ADD PRIMARY KEY (`userID`,`source`);

-- Reliability no longer depends on bot only games
DELETE d FROM wD_TurnDate d INNER JOIN wD_Games g ON g.id = d.gameID WHERE g.playerTypes = 'MemberVsBots';
DELETE d FROM wD_MissedTurns d INNER JOIN wD_Games g ON g.id = d.gameID WHERE g.playerTypes = 'MemberVsBots';

-- Default RR used to be 1, change to 100
 ALTER TABLE wD_Users CHANGE `reliabilityRating` `reliabilityRating` double NOT NULL DEFAULT '100';

-- Period tracking to allow quick detection of changes in RRs when a missed turn moves over a threshold from e.g. being under a week old to over a week old
 ALTER TABLE wD_MissedTurns ADD COLUMN reliabilityPeriod TINYINT NULL DEFAULT -1;

 /**************************************** Version 1.70 ****************************************/
 
UPDATE `wD_Misc` SET `value` = '170' WHERE `name` = 'Version';

ALTER TABLE `wD_Misc` CHANGE `value` `value` BIGINT(10) UNSIGNED NOT NULL;

/**************************************** Version 1.71 ****************************************/

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

ALTER TABLE `wD_Users`
  ADD COLUMN `mobileCountryCode` mediumint(8) UNSIGNED DEFAULT NULL,
  ADD COLUMN `mobileNumber` bigint(20) UNSIGNED DEFAULT NULL,
  ADD COLUMN `isMobileValidated` bit(1) NOT NULL DEFAULT b'0';
