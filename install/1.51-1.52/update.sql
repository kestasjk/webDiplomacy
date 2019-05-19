UPDATE `wD_Misc` SET `value` = '152' WHERE `name` = 'Version';

ALTER TABLE `wD_Games` MODIFY `pot` MEDIUMINT(8);
ALTER TABLE `wD_Games` MODIFY `minimumBet` MEDIUMINT(8);
ALTER TABLE `wD_Backup_Games` MODIFY `pot` MEDIUMINT(8);
ALTER TABLE `wD_Backup_Games` MODIFY `minimumBet` MEDIUMINT(8);
