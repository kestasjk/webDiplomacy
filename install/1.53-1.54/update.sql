UPDATE `wD_Misc` SET `value` = '154' WHERE `name` = 'Version';

CREATE TABLE `wD_Tournaments` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`name` VARCHAR( 150 ) NOT NULL,
`description` TEXT,
`status` enum('PreStart','Registration','Active', 'Finished') NOT NULL,
`minRR` tinyint (3) unsigned, 
`year` SMALLINT(4) unsigned,
`totalRounds` tinyint(3) unsigned,
`forumThreadLink` VARCHAR(150),
`externalLink` VARCHAR(300),
`directorID` mediumint(8) unsigned NOT NULL,
`coDirectorID` mediumint(8) unsigned NOT NULL,
`firstPlace` mediumint(8) unsigned NOT NULL,
`secondPlace` mediumint(8) unsigned NOT NULL,
`thirdPlace` mediumint(8) unsigned NOT NULL,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_TournamentGames` (
`tournamentID` mediumint(8) unsigned NOT NULL,
`gameID` mediumint(8) unsigned NOT NULL,
`round` tinyint(3) unsigned,
INDEX ( `tournamentID` ),
INDEX ( `gameID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_TournamentParticipants` (
`tournamentID` mediumint(8) unsigned NOT NULL,
`userID` mediumint(8) unsigned NOT NULL,
`status` enum('Applied','Accepted','Rejected', 'Left') NOT NULL,
INDEX ( `tournamentID` ),
INDEX ( `userID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_TournamentSpectators` (
`tournamentID` mediumint(8) unsigned NOT NULL,
`userID` mediumint(8) unsigned NOT NULL,
INDEX ( `tournamentID` ),
INDEX ( `userID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `wD_TournamentScoring` (
`tournamentID` mediumint(8) unsigned NOT NULL,
`userID` mediumint(8) unsigned NOT NULL,
`round` tinyint(3) unsigned,
`score` FLOAT,
INDEX ( `tournamentID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;