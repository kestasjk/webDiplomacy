ALTER TABLE `wD_Backup_Games` CHANGE `missingPlayerPolicy` `missingPlayerPolicy` ENUM( 'Normal', 'Strict', 'Wait' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Normal';
ALTER TABLE `wD_Games` CHANGE `missingPlayerPolicy` `missingPlayerPolicy` ENUM( 'Normal', 'Strict', 'Wait' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Normal';

UPDATE `wD_Misc` SET `value` = '134' WHERE `name` = 'Version';
