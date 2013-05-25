ALTER TABLE `wD_Users` ADD `pointNClick` enum('Yes','No') CHARACTER SET utf8 NOT NULL DEFAULT 'No';
UPDATE `wD_vDipMisc` SET `value` = '33' WHERE `name` = 'Version';
