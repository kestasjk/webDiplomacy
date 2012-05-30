UPDATE wD_Users SET rlGroup = '0' WHERE rlGroup = NULL;
ALTER TABLE `wD_Users` CHANGE `rlGroup` `rlGroup` MEDIUMINT( 8 ) NOT NULL default '0';
