/*ALTER TABLE `wD_Users` ADD `activeHours` CHAR(24) NOT NULL DEFAULT '000000000000000000000000' AFTER `points`;*/
/*ALTER TABLE `wD_Members` ADD `privateKey` SMALLINT UNSIGNED NOT NULL;*/
/*UPDATE wD_Members m INNER JOIN wD_Users u ON u.id=m.userID SET m.privateKey=CAST(MOD(CONV(SUBSTRING(HEX(u.password) FROM 1 FOR 4),16,10)+16,CONV('EFFFFFFFF',16,10)) AS UNSIGNED) ;*/

CREATE TABLE `wD_ModeratorNotes` (
`linkIDType` ENUM( 'Game', 'User' ) NOT NULL ,
`linkID` MEDIUMINT UNSIGNED NOT NULL ,
`type` ENUM( 'Report', 'PrivateNote', 'PublicNote' ) NOT NULL ,
`fromUserID` MEDIUMINT NOT NULL ,
`note` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
`timeSent` INT UNSIGNED NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_bin;

ALTER TABLE `wD_ModeratorNotes` ADD INDEX ( `linkIDType` , `linkID` , `timeSent` );

ALTER TABLE `wD_Users` ADD `muteReports` ENUM( 'No', 'Yes' ) NOT NULL DEFAULT 'No';

UPDATE wD_Misc SET `value`=98 WHERE `name`='Version';
