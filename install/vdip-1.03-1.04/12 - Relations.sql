ALTER TABLE `wD_Users` ADD `RLGroup` mediumint(8) unsigned default '0';
ALTER TABLE `wD_ModeratorNotes` MODIFY `linkIDType` ENUM( 'Game', 'User', 'RLGroup' );
