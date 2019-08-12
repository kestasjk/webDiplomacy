UPDATE `wD_Misc` SET `value` = '159' WHERE `name` = 'Version';

ALTER TABLE `wD_Users`
CHANGE `type` `type` SET(
	'Banned', 'Guest', 'System', 'User', 'Moderator',
	'Admin', 'Donator', 'DonatorBronze', 'DonatorSilver',
	'DonatorGold', 'DonatorPlatinum', 'ForumModerator', 'FtfTD',
	'DonatorAdamantium', 'DonatorService', 'DonatorOwner', 'Bot'
) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'User';
