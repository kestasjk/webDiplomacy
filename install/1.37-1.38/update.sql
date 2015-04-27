CREATE TABLE `wD_UserOptions` (
	  `userID` mediumint(8) unsigned NOT NULL,
	  `colourblind` enum('No','Protanope','Deuteranope','Tritanope') NOT NULL DEFAULT 'No',
	  `displayUpcomingLive` enum('No','Yes') NOT NULL DEFAULT 'Yes',
	  `showMoves` enum('No','Yes') NOT NULL DEFAULT 'Yes',
	  KEY `uid` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=644527 DEFAULT CHARSET=utf8;

UPDATE `wD_Misc` SET `value` = '138' WHERE `name` = 'Version';
