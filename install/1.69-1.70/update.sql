
ALTER TABLE `wD_Misc` CHANGE `value` `value` BIGINT(10) UNSIGNED NOT NULL; 
UPDATE `wD_Misc` SET `value` = '169' WHERE `name` = 'Version';
