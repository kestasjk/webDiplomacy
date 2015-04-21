ALTER TABLE wD_Games ADD drawType enum('draw-votes-public','draw-votes-hidden') NOT NULL DEFAULT 'draw-votes-public';
ALTER TABLE wD_Backup_Games ADD drawType enum('draw-votes-public','draw-votes-hidden') NOT NULL DEFAULT 'draw-votes-public';

UPDATE `wD_Misc` SET `value` = '137' WHERE `name` = 'Version';
