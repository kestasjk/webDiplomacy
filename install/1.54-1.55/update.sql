UPDATE `wD_Misc` SET `value` = '155' WHERE `name` = 'Version';

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
