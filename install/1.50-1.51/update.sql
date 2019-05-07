UPDATE `wD_Misc` SET `value` = '151' WHERE `name` = 'Version';

CREATE TABLE `wD_Config` (
  `name` enum('Notice','Panic','Maintenance') NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

INSERT INTO wD_Config VALUES ('Notice','Default server-wide notice message.'),('Panic','Game processing has been paused and user registration has been disabled while a problem is resolved.'),('Maintenance','Server is in maintenance mode; only admins can fully interact with the server.'),('ServerOffline','');
