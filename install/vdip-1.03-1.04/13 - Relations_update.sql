ALTER TABLE `wD_Users` CHANGE `RLGroup` `rlGroup` MEDIUMINT( 8 );
ALTER TABLE `wD_ModeratorNotes` CHANGE `linkIDType` `linkIDType` ENUM( 'Game', 'User', 'rlGroup' ) ;
