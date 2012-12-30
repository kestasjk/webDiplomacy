CREATE TABLE `wD_AccessLogAdvanced` (
  `userID` mediumint(8) unsigned NOT NULL,
  `request` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` int(10) unsigned NOT NULL,
  `action` enum('LogOn','LogOff','Board') CHARACTER SET utf8 NOT NULL DEFAULT 'LogOn',
  `memberID` mediumint(8) unsigned NOT NULL,
  KEY `userID` (`userID`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
