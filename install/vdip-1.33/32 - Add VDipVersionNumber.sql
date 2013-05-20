CREATE TABLE `wD_vDipMisc` (
  `name` enum('Version') NOT NULL,
  `value` int(10) unsigned NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `wD_vDipMisc` VALUES ('Version',32)