UPDATE `wD_Misc` SET `value` = '167' WHERE `name` = 'Version';

ALTER TABLE `wD_GameMessages` ADD `phaseMarker` ENUM('Finished','Pre-game','Diplomacy','Retreats','Builds') NULL DEFAULT NULL AFTER `gameID`; 
ALTER TABLE `wD_Backup_GameMessages` ADD `phaseMarker` ENUM('Finished','Pre-game','Diplomacy','Retreats','Builds') NULL DEFAULT NULL AFTER `gameID`; 

ALTER TABLE `wD_Users` ADD `optInFeatures` int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `tempBanReason`;