UPDATE `wD_Misc` SET `value` = '146' WHERE `name` = 'Version';

ALTER TABLE `wD_Members` ADD `hideNotifications` boolean DEFAULT false;
ALTER TABLE `wD_Backup_Members` ADD `hideNotifications` boolean DEFAULT false;
