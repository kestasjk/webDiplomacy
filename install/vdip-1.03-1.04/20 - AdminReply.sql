ALTER TABLE `wD_ModForumMessages` ADD `adminReply` enum('Yes','No') CHARACTER SET utf8 NOT NULL DEFAULT 'No';
ALTER TABLE `wD_ModForumMessages` ADD `status` enum('New','Open','Resolved') CHARACTER SET utf8 NOT NULL DEFAULT 'New';
