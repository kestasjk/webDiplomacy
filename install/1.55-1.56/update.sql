UPDATE `wD_Misc` SET `value` = '156' WHERE `name` = 'Version';

ALTER TABLE `wD_UserOptions` ADD `cssOption` enum('lightMode','darkMode') NOT NULL DEFAULT 'lightMode';
