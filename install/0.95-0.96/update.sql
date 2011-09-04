ALTER TABLE wD_Members ADD `orderStatus` set('None','Saved','Completed','Ready') NOT NULL default 'None';
ALTER TABLE wD_Backup_Members ADD `orderStatus` set('None','Saved','Completed','Ready') NOT NULL default 'None', DROP `finalized`, DROP `movesReceived`;

UPDATE wD_Members m INNER JOIN wD_Orders o ON ( o.gameID = m.gameID AND o.country = m.country ) SET m.orderStatus='';
UPDATE wD_Members SET orderStatus=CONCAT(orderStatus,',Saved') WHERE movesReceived='Yes';
UPDATE wD_Members SET orderStatus=CONCAT(orderStatus,',Completed,Ready') WHERE finalized='Yes';

ALTER TABLE wD_Members DROP finalized, DROP movesReceived;

UPDATE wD_Games SET processTime = ( SELECT AVG(timeSent) FROM wD_GameMessages WHERE gameID = id) WHERE phase='Finished';
UPDATE wD_Games SET processTime = 0 WHERE phase='Finished' AND processTime IS NULL;
UPDATE wD_Games SET processTime = NULL WHERE processStatus='Paused';
UPDATE wD_Games SET pauseTimeRemaining = NULL WHERE NOT processStatus='Paused';

UPDATE wD_Users SET lastMessageIDViewed=(SELECT MAX(id) FROM wD_ForumMessages);

UPDATE wD_ForumMessages SET latestReplySent = id;
CREATE TABLE fIDs SELECT toID, MAX(b.id) as max FROM wD_ForumMessages b WHERE b.type='ThreadReply' GROUP BY toID;
UPDATE wD_ForumMessages a INNER JOIN fIDs f ON ( a.id = f.toID ) 
SET a.latestReplySent = f.max 
WHERE a.type='ThreadStart';
DROP TABLE fIDs;


ALTER TABLE `wD_Backup_Members` DROP INDEX `gid`, DROP INDEX `playingCount`, DROP INDEX `uid`, CHANGE `id` `id` MEDIUMINT( 8 ) UNSIGNED NOT NULL;
ALTER TABLE `wD_Backup_Members` DROP PRIMARY KEY ;


UPDATE wD_Notices SET linkURL = REPLACE( REPLACE( REPLACE( linkURL, 'board.php?gameID=', '' ) , 'profile.php?userID=', '' ) , '#message', '' ) ;
ALTER TABLE `wD_Notices` ADD `linkID` MEDIUMINT UNSIGNED NULL DEFAULT NULL AFTER `linkName` ;
UPDATE wD_Notices SET linkID = linkURL;
ALTER TABLE `wD_Notices` DROP linkURL;

UPDATE wD_Misc SET `value`=96 WHERE `name`='Version';