UPDATE `wD_Misc` SET `value` = '161' WHERE `name` = 'Version';

ALTER TABLE `wD_Games` ADD COLUMN `playerTypes` enum('Members', 'Mixed', 'MemberVsBots') DEFAULT 'Members' NOT NULL;
ALTER TABLE `wD_Backup_Games` ADD COLUMN `playerTypes` enum('Members', 'Mixed', 'MemberVsBots') DEFAULT 'Members' NOT NULL;