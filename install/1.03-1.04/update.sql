CREATE TABLE `wD_Silences` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT UNSIGNED NULL ,
	`postID` INT UNSIGNED NULL ,
	`moderatorUserID` MEDIUMINT UNSIGNED NOT NULL ,
	`enabled` BIT( 1 ) NOT NULL DEFAULT 1,
	`startTime` BIGINT UNSIGNED NOT NULL,
	`length` smallint UNSIGNED NOT NULL DEFAULT 7 ,
	`reason` VARCHAR( 150 ) NOT NULL ,
	PRIMARY KEY ( `id` ) ,
	INDEX ( `userID` ) ,
	INDEX ( `postID` )
) ENGINE = InnoDB ;


ALTER TABLE `wD_ForumMessages` 
ADD `silenceID` INT UNSIGNED NULL DEFAULT NULL ;


ALTER TABLE `wD_Users` 
ADD `silenceID` INT UNSIGNED NULL DEFAULT NULL ;


ALTER TABLE `wD_Users` 
CHANGE `type` `type` SET( 
	'Banned', 'Guest', 'System', 'User', 'Moderator', 
	'Admin', 'Donator', 'DonatorBronze', 'DonatorSilver', 
	'DonatorGold', 'DonatorPlatinum', 'ForumModerator' 
) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'User';


UPDATE `wD_Misc` SET `value` = '104' WHERE `name` = 'Version';