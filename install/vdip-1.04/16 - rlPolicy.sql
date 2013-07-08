ALTER TABLE `wD_Games` ADD `rlPolicy` enum('None','Strict','Friends') CHARACTER SET utf8 NOT NULL DEFAULT 'None';
ALTER TABLE `wD_Backup_Games` ADD `rlPolicy` enum('None','Strict','Friends') CHARACTER SET utf8 NOT NULL DEFAULT 'None';
UPDATE wD_Games SET rlPolicy = 'Strict' WHERE anon = 'Yes' AND phase = 'Pre-game';
