ALTER TABLE `wD_Games` ADD `variantID` TINYINT UNSIGNED NOT NULL FIRST;
UPDATE wD_Games SET variantID=1;

ALTER TABLE wD_Territories DROP variantID;
ALTER TABLE wD_Borders DROP variantID;
ALTER TABLE wD_CoastalBorders DROP variantID;

ALTER TABLE `wD_Territories` ADD `mapID` TINYINT UNSIGNED NOT NULL FIRST ,
	ADD `id` SMALLINT UNSIGNED NOT NULL AFTER `mapID`,
	ADD `name` VARCHAR( 120 ) NOT NULL AFTER `id`;
UPDATE wD_Territories SET mapID=1,id=terr+0,name=terr;
ALTER TABLE `wD_Territories` DROP `terr` ;

ALTER TABLE `wD_Borders` ADD `mapID` TINYINT UNSIGNED NOT NULL FIRST, 
	ADD `fromTerrID` SMALLINT UNSIGNED NOT NULL AFTER `fromTerr`,
	ADD `toTerrID` SMALLINT UNSIGNED NOT NULL AFTER `toTerr`;
ALTER TABLE `wD_CoastalBorders` ADD `mapID` TINYINT UNSIGNED NOT NULL FIRST, 
	ADD `fromTerrID` SMALLINT UNSIGNED NOT NULL AFTER `fromTerr`,
	ADD `toTerrID` SMALLINT UNSIGNED NOT NULL AFTER `toTerr`;

UPDATE wD_Borders SET mapID=1, fromTerrID=fromTerr+0, toTerrID=toTerr+0;
UPDATE wD_CoastalBorders SET mapID=1, fromTerrID=fromTerr+0, toTerrID=toTerr+0;

ALTER TABLE `wD_Borders` DROP fromTerr, DROP toTerr;
ALTER TABLE `wD_CoastalBorders` DROP fromTerr, DROP toTerr;


ALTER TABLE `wD_TerrStatus` ADD `terrID` SMALLINT UNSIGNED NOT NULL AFTER terr;
UPDATE wD_TerrStatus SET terrID=terr+0;
ALTER TABLE `wD_TerrStatus` DROP INDEX `gameID`;
ALTER TABLE `wD_TerrStatus` DROP `terr` ;
ALTER TABLE `wD_TerrStatus` ADD UNIQUE (`gameID` ,`terrID`);

ALTER TABLE `wD_TerrStatus` 
	ADD `occupiedFromTerrID` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER occupiedFromTerr;
UPDATE wD_TerrStatus SET occupiedFromTerrID=occupiedFromTerr+0;
ALTER TABLE `wD_TerrStatus` DROP `occupiedFromTerr` ;
 
ALTER TABLE `wD_TerrStatusArchive` ADD `terrID` SMALLINT UNSIGNED NOT NULL AFTER terr;
UPDATE wD_TerrStatusArchive SET terrID=terr+0;
ALTER TABLE `wD_TerrStatusArchive` DROP `terr` ;
 
ALTER TABLE `wD_MovesArchive` ADD `terrID` SMALLINT UNSIGNED NOT NULL AFTER terr,
	ADD `toTerrID` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER toTerr,
	ADD `fromTerrID` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER fromTerr;
UPDATE wD_MovesArchive SET terrID=terr+0, toTerrID=toTerr+0, fromTerrID=fromTerr+0;
ALTER TABLE `wD_MovesArchive` DROP `terr`, DROP toTerr, DROP fromTerr ;

ALTER TABLE `wD_Moves` ADD `terrID` SMALLINT UNSIGNED NOT NULL AFTER terr,
	ADD `toTerrID` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER toTerr,
	ADD `fromTerrID` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER fromTerr;
UPDATE wD_Moves SET terrID=terr+0, toTerrID=toTerr+0, fromTerrID=fromTerr+0;
ALTER TABLE `wD_Moves` DROP `terr`, DROP toTerr, DROP fromTerr ;

ALTER TABLE `wD_DATCOrders` ADD `terrID` SMALLINT UNSIGNED NOT NULL AFTER terr,
	ADD `toTerrID` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER toTerr,
	ADD `fromTerrID` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER fromTerr;
UPDATE wD_DATCOrders SET terrID=terr+0, toTerrID=toTerr+0, fromTerrID=fromTerr+0;
ALTER TABLE `wD_DATCOrders` DROP PRIMARY KEY;
ALTER TABLE `wD_DATCOrders` DROP `terr`, DROP toTerr, DROP fromTerr ;
ALTER TABLE `wD_DATCOrders` ADD PRIMARY KEY ( `testID` , `terrID` );

ALTER TABLE `wD_UnitDestroyIndex` 
	ADD mapID TINYINT UNSIGNED NOT NULL FIRST ,
	ADD `terrID` SMALLINT UNSIGNED NOT NULL AFTER terr;
UPDATE wD_UnitDestroyIndex SET mapID=1, terrID=terr+0;
ALTER TABLE `wD_UnitDestroyIndex` DROP PRIMARY KEY ;
ALTER TABLE `wD_UnitDestroyIndex` DROP `terr` ;
 
ALTER TABLE `wD_Units` 
	ADD `terrID` SMALLINT UNSIGNED NOT NULL AFTER terr;
UPDATE wD_Units SET terrID=terr+0;
ALTER TABLE `wD_Units` DROP `terr` ;

ALTER TABLE `wD_Orders`  
	ADD `fromTerrID` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER `fromTerr`,
	ADD `toTerrID` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER `toTerr`;
UPDATE wD_Orders SET fromTerrID=fromTerr+0, toTerrID=toTerr+0;
ALTER TABLE `wD_Orders` DROP fromTerr, DROP toTerr;

DROP TABLE wD_Backup_GameMessages, wD_Backup_Games, wD_Backup_Units, wD_Backup_Members, wD_Backup_MovesArchive, 
	wD_Backup_Orders, wD_Backup_TerrStatus, wD_Backup_TerrStatusArchive;


ALTER TABLE `wD_Borders` ADD INDEX `fromTo` ( `fromTerrID` , `toTerrID` ), ADD INDEX `toFrom` ( `toTerrID` , `fromTerrID` );
ALTER TABLE `wD_CoastalBorders` ADD INDEX `fromTo` ( `fromTerrID` , `toTerrID` ), ADD INDEX `toFrom` ( `toTerrID` , `fromTerrID` );
ALTER TABLE `wD_Territories` ADD PRIMARY KEY ( `mapID` , `id` );

ALTER TABLE `wD_Orders` DROP INDEX `unitID_2`, DROP INDEX gameID;
ALTER TABLE wD_GameMessages DROP INDEX toMember, DROP INDEX fromMember;

ALTER TABLE wD_CivilDisorders ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_CivilDisorders SET countryID=country+0;
ALTER TABLE wD_CivilDisorders DROP `country` ;

ALTER TABLE wD_DATCOrders ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_DATCOrders SET countryID=country+0;
ALTER TABLE wD_DATCOrders DROP `country` ;

ALTER TABLE wD_GameMessages ADD `toCountryID` TINYINT UNSIGNED NOT NULL AFTER toCountry, ADD `fromCountryID` TINYINT UNSIGNED NOT NULL AFTER fromCountry;
UPDATE wD_GameMessages SET toCountryID=toCountry-1, fromCountryID=fromCountry-1;
ALTER TABLE wD_GameMessages DROP toCountry, DROP fromCountry ;

ALTER TABLE wD_Members ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_Members SET countryID=country-1;
ALTER TABLE wD_Members DROP `country` ;

ALTER TABLE wD_Moves ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_Moves SET countryID=country+0;
ALTER TABLE wD_Moves DROP `country` ;

ALTER TABLE wD_MovesArchive ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_MovesArchive SET countryID=country+0;
ALTER TABLE wD_MovesArchive DROP `country` ;

ALTER TABLE wD_Orders ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_Orders SET countryID=country+0;
ALTER TABLE wD_Orders DROP `country` ;

ALTER TABLE wD_Territories ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_Territories SET countryID=country-1;
ALTER TABLE wD_Territories DROP `country` ;

ALTER TABLE wD_TerrStatus ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_TerrStatus SET countryID=country-1;
ALTER TABLE wD_TerrStatus DROP `country` ;

ALTER TABLE wD_TerrStatusArchive ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_TerrStatusArchive SET countryID=country-1;
ALTER TABLE wD_TerrStatusArchive DROP `country` ;

ALTER TABLE wD_UnitDestroyIndex ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_UnitDestroyIndex SET countryID=country+0;
ALTER TABLE wD_UnitDestroyIndex DROP `country` ;

ALTER TABLE wD_Units ADD `countryID` TINYINT UNSIGNED NOT NULL AFTER country;
UPDATE wD_Units SET countryID=country+0;
ALTER TABLE wD_Units DROP `country` ;


ALTER TABLE `wD_Members` CHANGE `newMessagesFrom` `newMessagesFrom` SET( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60', '61', '62', '63' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;


ALTER TABLE `wD_UnitDestroyIndex` ADD PRIMARY KEY ( `mapID` , `countryID` , `terrID`, `unitType` );
ALTER TABLE `wD_Orders` ADD INDEX ( gameID, countryID );

-- TODO: Add timeSent to these indexes to improve efficiency?
ALTER TABLE wD_GameMessages ADD INDEX toMember ( gameID, toCountryID ), ADD INDEX fromMember ( gameID, fromCountryID );


ALTER TABLE `wD_Territories` ADD `coastParentID` SMALLINT UNSIGNED NOT NULL;
UPDATE wD_Territories SET coastParentID = id;
UPDATE wD_Territories a INNER JOIN wD_Territories b ON ( REPLACE(REPLACE(a.name, ' (North Coast)', ''), ' (South Coast)', '')=b.name ) SET a.coastParentID=b.id;
ALTER TABLE `wD_Territories` CHANGE `coast` `coast` ENUM( 'No', 'Parent', 'Child' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
UPDATE wD_Territories SET coast='Child' WHERE NOT (coast='No' OR coast='Parent');

ALTER TABLE `wD_Games` ADD `phaseText` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
UPDATE wD_Games SET phaseText=IF(phase='Unit-placing','Builds',phase);
ALTER TABLE `wD_Games` CHANGE `phase` `phase` ENUM( 'Finished', 'Pre-game', 'Diplomacy', 'Retreats', 'Builds' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Pre-game';
UPDATE wD_Games SET phase=phaseText;
ALTER TABLE `wD_Games` DROP `phaseText`;
ALTER TABLE wD_Games ADD `missingPlayerPolicy` enum('Normal','Strict') NOT NULL DEFAULT 'Normal';
  
  
CREATE TABLE wD_Backup_GameMessages SELECT * FROM wD_GameMessages LIMIT 0;
CREATE TABLE wD_Backup_Games SELECT * FROM wD_Games LIMIT 0;
CREATE TABLE wD_Backup_Members SELECT * FROM wD_Members LIMIT 0;
CREATE TABLE wD_Backup_MovesArchive SELECT * FROM wD_MovesArchive LIMIT 0;
CREATE TABLE wD_Backup_Orders SELECT * FROM wD_Orders LIMIT 0;
CREATE TABLE wD_Backup_TerrStatus SELECT * FROM wD_TerrStatus LIMIT 0;
CREATE TABLE wD_Backup_TerrStatusArchive SELECT * FROM wD_TerrStatusArchive LIMIT 0;
CREATE TABLE wD_Backup_Units SELECT * FROM wD_Units LIMIT 0;

ALTER TABLE `wD_DATC` ADD `variantID` TINYINT UNSIGNED NOT NULL AFTER `testID`;
UPDATE wD_DATC SET variantID=1;

DELETE FROM wD_DATC WHERE testID>=800;
DELETE FROM wD_DATCOrders WHERE testID>=800;

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (801, 1, 'wD.Intro.1', 'webDiplomacy introduction page example scenarios: Hold.', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(801, 3, 'Army', 11, 'Hold', NULL, NULL, 'No', 'Hold', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (802, 1, 'wD.Intro.2', 'webDiplomacy introduction page example scenarios: Move.', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(802, 3, 'Army', 11, 'Move', 12, NULL, 'No', 'Success', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (803, 1, 'wD.Intro.3', 'webDiplomacy introduction page example scenarios: Support move.', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(803, 3, 'Army', 15, 'Move', 12, NULL, 'No', 'Success', 'Yes' ),
(803, 3, 'Army', 13, 'Support Move', 12, 15, 'No', 'Hold', 'Yes' ),
(803, 5, 'Army', 12, 'Hold', NULL, NULL, 'No', 'Dislodged', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (804, 1, 'wD.Intro.4', 'webDiplomacy introduction page example scenarios: Support move vs support hold.', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(804, 3, 'Army', 15, 'Move', 12, NULL, 'No', 'Hold', 'Yes' ),
(804, 3, 'Army', 13, 'Support Move', 12, 15, 'No', 'Hold', 'Yes' ),
(804, 5, 'Army', 12, 'Hold', NULL, NULL, 'No', 'Hold', 'Yes' ),
(804, 5, 'Fleet', 64, 'Support hold', 12, NULL, 'No', 'Hold', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (805, 1, 'wD.Intro.5', 'webDiplomacy introduction page example scenarios: Convoy.', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(805, 3, 'Army', 15, 'Move', 10, NULL, 'Yes', 'Success', 'Yes' ),
(805, 3, 'Fleet', 66, 'Convoy', 10, 15, 'No', 'Hold', 'Yes' ),
(805, 3, 'Fleet', 65, 'Convoy', 10, 15, 'No', 'Hold', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (806, 1, 'wD.Intro.6', 'webDiplomacy introduction page example scenarios: Defend.', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(806, 3, 'Army', 11, 'Move', 12, NULL, 'No', 'Hold', 'Yes' ),
(806, 5, 'Army', 12, 'Hold', NULL, NULL, 'No', 'Hold', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (807, 1, 'wD.Intro.7', 'webDiplomacy introduction page example scenarios: Bounce.', 'NotPassed');
INSERT INTO wD_DATCOrders
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(807, 3, 'Army', 15, 'Move', 16, NULL, 'No', 'Hold', 'Yes' ),
(807, 5, 'Fleet', 65, 'Move', 16, NULL, 'No', 'Hold', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (808, 1, 'wD.Intro.8', 'webDiplomacy introduction page example scenarios: Support moves.', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(808, 3, 'Fleet', 15, 'Hold', NULL, NULL, 'No', 'Dislodged', 'Yes' ),
(808, 3, 'Army', 12, 'Support hold', 15, NULL, 'No', 'Hold', 'Yes' ),
(808, 5, 'Fleet', 73, 'Move', 15, NULL, 'No', 'Success', 'Yes' ),
(808, 5, 'Army', 70, 'Support move', 15, 73, 'No', 'Hold', 'Yes' ),
(808, 5, 'Army', 14, 'Support move', 15, 73, 'No', 'Hold', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (809, 1, 'wD.Intro.9', 'webDiplomacy introduction page example scenarios: Support moves vs support holds deadlock.', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(809, 3, 'Fleet', 15, 'Hold', NULL, NULL, 'No', 'Hold', 'Yes' ),
(809, 3, 'Army', 12, 'Support hold', 15, NULL, 'No', 'Hold', 'Yes' ),
(809, 3, 'Fleet', 16, 'Support hold', 15, NULL, 'No', 'Hold', 'Yes' ),
(809, 5, 'Fleet', 73, 'Move', 15, NULL, 'No', 'Hold', 'Yes' ),
(809, 5, 'Army', 70, 'Support move', 15, 73, 'No', 'Hold', 'Yes' ),
(809, 5, 'Army', 14, 'Support move', 15, 73, 'No', 'Hold', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (810, 1, 'wD.Intro.10', 'webDiplomacy introduction page example scenarios: Support move attacked', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(810, 3, 'Fleet', 15, 'Hold', NULL, NULL, 'No', 'Hold', 'Yes' ),
(810, 3, 'Army', 12, 'Support hold', 15, NULL, 'No', 'Hold', 'Yes' ),
(810, 5, 'Fleet', 73, 'Move', 15, NULL, 'No', 'Hold', 'Yes' ),
(810, 5, 'Army', 70, 'Support move', 15, 73, 'No', 'Hold', 'Yes' ),
(810, 5, 'Army', 14, 'Support move', 15, 73, 'No', 'Hold', 'Yes' ),
(810, 4, 'Army', 41, 'Move', 70, NULL, 'No', 'Hold', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (811, 1, 'wD.Intro.11', 'webDiplomacy introduction page example scenarios: Support hold and move attacked', 'NotPassed');
INSERT INTO wD_DATCOrders 
(testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) VALUES
(811, 3, 'Fleet', 15, 'Hold', NULL, NULL, 'No', 'Dislodged', 'Yes' ),
(811, 3, 'Army', 12, 'Support hold', 15, NULL, 'No', 'Hold', 'Yes' ),
(811, 5, 'Fleet', 73, 'Move', 15, NULL, 'No', 'Success', 'Yes' ),
(811, 5, 'Army', 70, 'Support move', 15, 73, 'No', 'Hold', 'Yes' ),
(811, 5, 'Army', 14, 'Support move', 15, 73, 'No', 'Hold', 'Yes' ),
(811, 4, 'Army', 41, 'Move', 70, NULL, 'No', 'Hold', 'Yes' ),
(811, 6, 'Fleet', 64, 'Move', 12, NULL, 'No', 'Hold', 'Yes' );

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (812, 1, 'wD.Intro.12', 'webDiplomacy introduction page example scenarios: Complex scenario 1', 'NotPassed');
INSERT INTO wD_DATCOrders ( testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal)
SELECT 812, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal FROM wD_DATCOrders WHERE testID=406;

INSERT INTO wD_DATC (testID, variantID, testName, testDesc, status) VALUES (901, 1, 'wD.Test.1', 'Testing the maximum sized convoy for this map.', 'NotPassed');
INSERT INTO wD_DATCOrders (testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) SELECT 901, 1, 'Fleet', id, 'Convoy', 32, 26, 'No', 'Hold', IF(name='Black Sea' OR name='Adriatic Sea' OR name='Baltic Sea' OR name='Gulf of Bothnia' OR name='Heligoland Bight' OR name='Skagerrack','No','Yes') FROM wD_Territories WHERE mapID=1 AND type='Sea';
INSERT INTO wD_DATCOrders (testID, countryID, unitType, terrID, moveType, toTerrID, fromTerrID, viaConvoy, criteria, legal) SELECT 901, 2, 'Army', id, 'Move', 32, NULL, 'Yes', IF(name='Syria','Success','Hold'), IF(name='Rumania' OR name='Ankara' OR name='Armenia' OR name='Sevastopol' OR name='St. Petersburg','No','Yes') FROM wD_Territories WHERE mapID=1 AND type='Coast' AND NOT coast='Child' AND NOT name IN ('St. Petersburg','Livonia','Finland','Norway');

UPDATE wD_DATC SET status='NotPassed' WHERE status='Passed';

DELETE FROM wD_Territories;
DELETE FROM wD_Borders;
DELETE FROM wD_CoastalBorders;
DELETE FROM wD_UnitDestroyIndex;


UPDATE wD_Misc SET `value`=1 WHERE `name`='Maintenance';

UPDATE wD_Misc SET `value`=97 WHERE `name`='Version';