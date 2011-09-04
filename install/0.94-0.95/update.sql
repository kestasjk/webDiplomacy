DELETE FROM wD_Moves;
ALTER TABLE `wD_Moves` ADD `gameID` MEDIUMINT UNSIGNED NOT NULL AFTER `id` ;
ALTER TABLE `wD_Moves` ADD INDEX ( `gameID` );
ALTER TABLE `wD_Moves` ENGINE = MYISAM;
ALTER TABLE `wD_Games` ADD `attempts` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `wD_Backup_Games` ADD `attempts` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
DROP TABLE `wD_GameMasterQueue` ;

ALTER TABLE `wD_Users` DROP `gmtOffset`;
UPDATE wD_Games SET processTime=1 WHERE NOT processStatus='Paused' AND processTime IS NULL;
UPDATE wD_Games SET pauseTimeRemaining=NULL WHERE NOT processStatus='Paused' AND NOT pauseTimeRemaining IS NULL;
UPDATE wD_Games SET pauseTimeRemaining=1 WHERE processStatus='Paused' AND pauseTimeRemaining IS NULL;
UPDATE wD_Games SET processTime=NULL WHERE processStatus='Paused' AND NOT processTime IS NULL;
 
UPDATE wD_Misc SET `value`=95 WHERE `name`='Version';