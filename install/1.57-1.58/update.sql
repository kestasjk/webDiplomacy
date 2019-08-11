UPDATE `wD_Misc` SET `value` = '158' WHERE `name` = 'Version';

CREATE TABLE `wD_VariantInfo` (
  `variantID` smallint(4) unsigned NOT NULL,
  `mapID` smallint(4) unsigned NOT NULL,
  `supplyCenterTarget` smallint(4) unsigned NOT NULL,
  `supplyCenterCount` smallint(4) unsigned NOT NULL,
  `countryCount` smallint(4) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `fullName` varchar(50) NOT NULL,
  `description` varchar(500) NOT NULL,
  `author` varchar(50) NOT NULL,
  `adapter` varchar(50),
  `version` varchar(10),
  `codeVersion` varchar(10),
  `homepage` varchar(100),
  `countriesList` varchar(800) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `wD_Games` ADD `finishTime` int(10) unsigned DEFAULT NULL;

UPDATE `wD_Games` g SET `finishTime` = (SELECT MAX(n.timeSent) FROM `wD_Notices` n WHERE n.type = 'Game' AND n.linkID = g.id) WHERE (SELECT COUNT(1) FROM `wD_Notices` n1 WHERE n1.linkID = g.id AND `type` = 'Game') > 0 AND `gameOver` <> 'No';

UPDATE `wD_Games` SET `finishTime` = `processTime` WHERE `finishTime` IS NULL AND `gameOver` <> 'No';

ALTER TABLE `wD__Backup_Games` ADD `finishTime` int(10) unsigned DEFAULT NULL;

UPDATE `wD__Backup_Games` g SET `finishTime` = (SELECT MAX(n.timeSent) FROM `wD_Notices` n WHERE n.type = 'Game' AND n.linkID = g.id) WHERE (SELECT COUNT(1) FROM `wD_Notices` n1 WHERE n1.linkID = g.id AND `type` = 'Game') > 0 AND `gameOver` <> 'No';

UPDATE `wD_Backup_Games` SET `finishTime` = `processTime` WHERE `finishTime` IS NULL AND `gameOver` <> 'No';
