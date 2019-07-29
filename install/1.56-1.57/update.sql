UPDATE `wD_Misc` SET `value` = '157' WHERE `name` = 'Version';

ALTER TABLE `wD_UserOptions` ADD `darkMode` enum('Yes','No') NOT NULL DEFAULT 'No';