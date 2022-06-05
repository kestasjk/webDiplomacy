UPDATE `wD_Misc` SET `value` = '168' WHERE `name` = 'Version';

CREATE TABLE `wD_PaypalIPN` ( 
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
 `userId` int(10) unsigned NOT NULL, 
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
