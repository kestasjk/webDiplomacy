UPDATE `wD_Misc` SET `value` = '148' WHERE `name` = 'Version';

ALTER TABLE `wD_Users` ADD `emergencyPauseDate` int(10) unsigned Default 0;
