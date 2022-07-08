UPDATE `wD_Misc` SET `value` = '170' WHERE `name` = 'Version';

ALTER TABLE `wD_Misc` CHANGE `value` `value` BIGINT(10) UNSIGNED NOT NULL; 
