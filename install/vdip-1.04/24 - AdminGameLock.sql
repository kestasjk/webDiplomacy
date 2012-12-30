ALTER TABLE `wD_Games` ADD `adminLock` enum('Yes','No') CHARACTER SET utf8 NOT NULL DEFAULT 'No';
ALTER TABLE `wD_Backup_Games` ADD `adminLock` enum('Yes','No') CHARACTER SET utf8 NOT NULL DEFAULT 'No';
