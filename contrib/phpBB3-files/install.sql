
ALTER TABLE `phpbb_users` ADD `webdip_user_id` INT(0) UNSIGNED NULL AFTER `user_reminded_time`;

ALTER TABLE `phpbb_users` ADD INDEX(`webdip_user_id`);