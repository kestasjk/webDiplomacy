UPDATE `wD_Misc` SET `value` = '146' WHERE `name` = 'Version';

ALTER TABLE `wD_Members` ADD `hideNotifications` boolean DEFAULT false;
