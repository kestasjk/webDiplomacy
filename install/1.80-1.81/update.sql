UPDATE `wD_Misc` SET `value` = '181' WHERE `name` = 'Version';

-- Web Push (PWA) notification subscriptions. One row per browser push endpoint; a user with
-- several devices/browsers has several rows. endpointHash is md5(endpoint), giving a fixed-width
-- unique key (endpoint URLs can exceed the utf8 index length limit). The unique key is global,
-- not per-user, so a shared browser is rebound to whichever user subscribed most recently.
CREATE TABLE `wD_PushSubscriptions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `userID` mediumint(8) unsigned NOT NULL,
  `endpointHash` char(32) NOT NULL,
  `endpoint` text NOT NULL,
  `p256dh` varchar(255) NOT NULL,
  `auth` varchar(64) NOT NULL,
  `userAgent` varchar(255) NOT NULL DEFAULT '',
  `timeCreated` int(10) unsigned NOT NULL,
  `timeLastUsed` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `endpointHash` (`endpointHash`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
