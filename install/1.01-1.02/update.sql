
ALTER TABLE `wD_Users` CHANGE `type` `type` SET( 'Banned', 'Guest', 'System', 'User', 'Moderator', 'Admin', 'Donator', 'DonatorBronze', 'DonatorSilver', 'DonatorGold', 'DonatorPlatinum' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'User';

UPDATE wD_Misc SET `value`=102 WHERE `name`='Version';
