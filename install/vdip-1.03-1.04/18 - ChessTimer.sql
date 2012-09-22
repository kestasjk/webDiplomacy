ALTER TABLE `wD_Members` ADD `chessTime` smallint(5) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `wD_Games`   ADD `chessTime` smallint(5) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `wD_Games`   ADD `lastProcessed` int(10) unsigned NOT NULL DEFAULT '0';
