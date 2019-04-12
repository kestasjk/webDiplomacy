UPDATE `wD_Misc` SET `value` = '147' WHERE `name` = 'Version';

ALTER TABLE `wD_UserOptions` ADD `orderSort` enum('No Sort','Alphabetical','Convoys Last') NOT NULL DEFAULT 'Convoys Last';
