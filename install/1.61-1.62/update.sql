UPDATE `wD_Misc` SET `value` = '162' WHERE `name` = 'Version';

ALTER TABLE `wD_Games` ADD COLUMN `allowBotCDOrdering` enum('Yes', 'No') DEFAULT 'No' NOT NULL;
ALTER TABLE `wD_Backup_Games` ADD COLUMN `allowBotCDOrdering` enum('Yes', 'No') DEFAULT 'No' NOT NULL;

ALTER TABLE `wD_ApiPermissions` ADD COLUMN `canReplaceUsersInCD` enum('Yes', 'No') DEFAULT 'No' NOT NULL;
UPDATE `wD_ApiPermissions` SET `canReplaceUsersInCD`=`submitOrdersForUserInCD` WHERE 1;
ALTER TABLE `wD_ApiPermissions` DROP COLUMN `getStateOfAllGames`;
ALTER TABLE `wD_ApiPermissions` DROP COLUMN `submitOrdersForUserInCD`;
ALTER TABLE `wD_ApiPermissions` DROP COLUMN `listGamesWithPlayersInCD`;
