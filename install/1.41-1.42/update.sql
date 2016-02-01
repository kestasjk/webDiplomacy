ALTER TABLE wD_Backup_Games CHANGE `pressType` `pressType` enum('Regular','PublicPressOnly','NoPress','RulebookPress') NOT NULL DEFAULT 'Regular';
ALTER TABLE wD_Games CHANGE `pressType` `pressType` enum('Regular','PublicPressOnly','NoPress','RulebookPress') NOT NULL DEFAULT 'Regular';

UPDATE `wD_Misc` SET `value` = '142' WHERE `name` = 'Version';
