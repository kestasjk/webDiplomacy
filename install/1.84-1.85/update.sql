UPDATE `wD_Misc` SET `value` = '185' WHERE `name` = 'Version';

-- Primary keys for the tables that had none and hold FLOAT columns.
--
-- With row-based binlogs, an UPDATE or DELETE on a table without a primary or unique key is replayed by
-- finding a row whose every column matches the logged before-image. mysqldump writes FLOATs to 6
-- significant digits, so after restoring a dump the FLOATs can differ from the live ones in their last
-- bits, the replay can't find the row, and point-in-time recovery stops (ERROR 1032). With a primary key
-- the row is found by its key and the logged after-image writes the exact value back. Found by a test
-- restore of the weekly dump plus binlogs on 2026-11-21.
--
-- Each key is the identity the code already treats as unique, and none of them has duplicates in the
-- live data. On InnoDB these are online changes: ALGORITHM=INPLACE, LOCK=NONE makes MariaDB refuse,
-- rather than lock the table, if it ever can't do one without blocking reads and writes.

-- One rating per user per category: ghostratings/calculations.php inserts only after checking
-- (categoryID, userID) and moves yearMonth forward on the same row.
ALTER TABLE `wD_GhostRatings` ADD PRIMARY KEY (`userID`, `categoryID`), ALGORITHM=INPLACE, LOCK=NONE;

-- One rating per user per category per month: a new month inserts a row, the same month updates it.
ALTER TABLE `wD_GhostRatingsHistory` ADD PRIMARY KEY (`userID`, `categoryID`, `yearMonth`), ALGORITHM=INPLACE, LOCK=NONE;

-- One score per player per round of a tournament. round is always given, so it can be NOT NULL.
ALTER TABLE `wD_TournamentScoring` MODIFY `round` tinyint(3) unsigned NOT NULL,
    ADD PRIMARY KEY (`tournamentID`, `userID`, `round`), ALGORITHM=INPLACE, LOCK=NONE;

-- One backed-up copy per game: processGame::backupGame() deletes a game's backup rows before copying it.
ALTER TABLE `wD_Backup_Games` ADD PRIMARY KEY (`id`), ALGORITHM=INPLACE, LOCK=NONE;

-- wD_GhostRatingsBackup keeps no key: it is a log the code only ever appends to (and it has some duplicate
-- rows), so a replay never has to find one of its rows.
