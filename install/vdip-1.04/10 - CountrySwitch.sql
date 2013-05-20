CREATE TABLE `wD_CountrySwitch` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`gameID` INT UNSIGNED NULL ,
	`fromID` INT UNSIGNED NULL ,
	`toID` INT UNSIGNED NULL ,
	`status` set('Send','Active','Rejected','Canceled','Returned','ClaimedBack') CHARACTER SET utf8 NOT NULL,
	PRIMARY KEY ( `id` )
) ENGINE = MyISAM DEFAULT CHARSET=latin1;

