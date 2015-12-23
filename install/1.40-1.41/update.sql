ALTER TABLE wD_Games CHANGE `potType` `potType` enum('Winner-takes-all','Points-per-supply-center','Unranked','Sum-of-squares') NOT NULL;
ALTER TABLE wD_Backup_Games CHANGE `potType` `potType` enum('Winner-takes-all','Points-per-supply-center','Unranked','Sum-of-squares') NOT NULL;

UPDATE `wD_Misc` SET `value` = '141' WHERE `name` = 'Version';
