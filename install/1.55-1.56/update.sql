UPDATE `wD_Misc` SET `value` = '156' WHERE `name` = 'Version';

ALTER TABLE `wD_UserOptions` ADD `darkMode` enum('Yes','No') NOT NULL DEFAULT 'No';
