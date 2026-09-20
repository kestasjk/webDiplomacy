UPDATE `wD_Misc` SET `value` = '183' WHERE `name` = 'Version';

-- Game listings and the cached game counts.
--
-- Every gamelistings.php tab, and the counts miscUpdate::game() keeps in wD_Misc, ask for games of a
-- phase that are not bot games. Written as "playerTypes <> 'MemberVsBots'" that can't be indexed, so
-- listing the finished games read all 1.45 million of them and sorted the lot to show twenty, and
-- counting them walked the whole phase index every few seconds. Both are now equality lists over
-- this index instead: (playerTypes, phase) narrows to the games being listed and pot orders them,
-- which is what the listings sort on by default.
ALTER TABLE `wD_Games` ADD INDEX `playerTypesPhasePot` (`playerTypes`, `phase`, `pot`);

-- If phpBB is installed: an index on who gave each like.
--
-- phpbb_posts_likes is created by the Post Love extension with PRIMARY KEY (post_id, user_id) and
-- nothing else, so there was no way to look up the likes a user has given without reading the table.
-- The likes list (postlove/{user_id}) needs exactly that; see the comment in
-- contrib/phpBB3-files/ext/anavaro/postlove/controller/lovelist.php.
ALTER TABLE `phpbb_posts_likes` ADD INDEX IF NOT EXISTS `user_id` (`user_id`);
