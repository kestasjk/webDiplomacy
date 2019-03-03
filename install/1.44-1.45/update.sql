UPDATE `wD_Misc` SET `value` = '145' WHERE `name` = 'Version';

CREATE TABLE `wD_UserConnections` (
`userID` mediumint(8) unsigned NOT NULL UNIQUE,
`modLastCheckedBy` mediumint(8) unsigned,
`modLastCheckedOn` int(10) unsigned,
`matchesLastUpdatedOn` int(10) unsigned,
`countMatchedIPUsers` mediumint(8) unsigned NOT NULL DEFAULT 0,
`countMatchedCookieUsers` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;