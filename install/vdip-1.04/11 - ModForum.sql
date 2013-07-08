ALTER TABLE `wD_Users` ADD `lastModMessageIDViewed` int(10) unsigned NOT NULL DEFAULT '0';
UPDATE wD_Users SET lastModMessageIDViewed=(SELECT MAX(id) FROM wD_ModForumMessages);
