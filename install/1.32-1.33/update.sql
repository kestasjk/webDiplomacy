ALTER TABLE `wD_Games`  ADD `directorUserID` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `wD_Backup_Games`  ADD `directorUserID` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0';

UPDATE `wD_Misc` SET `value` = '133' WHERE `name` = 'Version';
