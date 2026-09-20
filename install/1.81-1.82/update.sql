UPDATE `wD_Misc` SET `value` = '182' WHERE `name` = 'Version';

-- Fix the wD.Test.3 DATC test data, which has never been able to run.
--
-- The test (added with the self-dislodgement/paradox adjudicator fix in 1.71-1.72) never got as far
-- as adjudicating, because the DATC harness enters its orders through the real order interface and
-- two of its rows describe orders that interface will not accept:
--
-- 1. The Turkish fleet in Rumania was ordered to Bulgaria (20). Bulgaria is a coast parent, so a
--    fleet has to be given a coast; from Rumania only Bulgaria (North Coast), 80, is reachable.
--    wD.Test.2 already gets this right for its own fleet move into Bulgaria.
-- 2. The Italian fleet supporting Constantinople -> Black Sea was placed in Ankara (24), which the
--    Turkish army in the same test already occupies. datcGame::loadUnits() inserts one unit per row,
--    so that put two units in Ankara, and every join in the harness matches units on terrID alone,
--    which then applies both orders to both units. Armenia (25) is the free territory next to the
--    Black Sea, so the support is moved there.
--
-- An order that the interface rejects is never submitted (form.js only posts complete orders), so it
-- stays as processOrderDiplomacy::create() inserted it, a Hold, and the test reports the given order
-- not matching the received one.
UPDATE wD_DATCOrders SET toTerrID = 80 WHERE testID = 903 AND terrID = 21 AND countryID = 7 AND moveType = 'Move';
UPDATE wD_DATCOrders SET terrID = 25 WHERE testID = 903 AND terrID = 24 AND countryID = 3 AND moveType = 'Support move';

-- Precomputed Ghost Ratings ranks.
--
-- A profile ran a COUNT of everyone rated above the user once for each GR category, and the hall of
-- fame two more over peakRating, on every view. wD_GhostRatings had no index on rating or peakRating
-- at all, and the Redis cache in User::cachedRankQuery() sat behind keys that included the viewer's
-- own rating, which is near enough unique per user, so it never hit. The ranks now live on the row,
-- refreshed every half hour by libBackgroundTasks::updateGhostRatingRanks(). The indexes serve that
-- refresh, and the count still used for a rating first recorded since the last one.
ALTER TABLE `wD_GhostRatings`
    ADD COLUMN `ratingRank` MEDIUMINT UNSIGNED NULL DEFAULT NULL,
    ADD COLUMN `peakRank` MEDIUMINT UNSIGNED NULL DEFAULT NULL,
    ADD COLUMN `peakRankActive` MEDIUMINT UNSIGNED NULL DEFAULT NULL,
    ADD INDEX `categoryRating` (`categoryID`, `rating`),
    ADD INDEX `categoryPeakRating` (`categoryID`, `peakRating`);

-- The first fill; peakRankActive covers only users seen in the last six months, matching the hall of
-- fame's "active" lists, and is NULL for everyone else.
UPDATE wD_GhostRatings g
INNER JOIN (
    SELECT userID, categoryID,
        RANK() OVER ( PARTITION BY categoryID ORDER BY rating DESC ) AS ratingRank,
        RANK() OVER ( PARTITION BY categoryID ORDER BY peakRating DESC ) AS peakRank
    FROM wD_GhostRatings
) r ON ( r.userID = g.userID AND r.categoryID = g.categoryID )
LEFT JOIN (
    SELECT a.userID, a.categoryID,
        RANK() OVER ( PARTITION BY a.categoryID ORDER BY a.peakRating DESC ) AS peakRankActive
    FROM wD_GhostRatings a
    INNER JOIN wD_Users u ON ( u.id = a.userID )
    WHERE u.timeLastSessionEnded > UNIX_TIMESTAMP() - 15552000
) ra ON ( ra.userID = g.userID AND ra.categoryID = g.categoryID )
SET g.ratingRank = r.ratingRank,
    g.peakRank = r.peakRank,
    g.peakRankActive = ra.peakRankActive;

-- If phpBB is installed: cached forum like counts.
--
-- The Post Love extension counted likes given and likes received for the poster of every post row it
-- rendered, the second of those by joining the likes table against the whole posts table.
-- webdip_like_count was added for this in 1.71-1.72, but its backfill was two UPDATE statements run
-- into each other with no separator, so it never ran and the column stayed NULL; the daily recount
-- meant to go with it was disabled in gamemaster.php for the same reason and has now been removed.
-- The extension keeps both counts itself as likes are toggled, and recounts after a post or user is
-- permanently deleted (see contrib/phpBB3-files/ext/anavaro/postlove).
ALTER TABLE `phpbb_users`
    ADD COLUMN IF NOT EXISTS `webdip_like_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `webdip_user_id`,
    ADD COLUMN IF NOT EXISTS `webdip_like_given_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `webdip_like_count`;

-- 1.71-1.72 created webdip_like_count as NULLable with no default, which would have made every
-- increment NULL
ALTER TABLE `phpbb_users`
    MODIFY COLUMN `webdip_like_count` INT UNSIGNED NOT NULL DEFAULT 0;

UPDATE phpbb_users u
LEFT JOIN (
    SELECT p.poster_id, COUNT(*) AS likes
    FROM phpbb_posts p
    INNER JOIN phpbb_posts_likes l ON l.post_id = p.post_id
    GROUP BY p.poster_id
) x ON x.poster_id = u.user_id
SET u.webdip_like_count = COALESCE(x.likes, 0);

UPDATE phpbb_users u
LEFT JOIN (
    SELECT l.user_id, COUNT(*) AS likes
    FROM phpbb_posts_likes l
    GROUP BY l.user_id
) x ON x.user_id = u.user_id
SET u.webdip_like_given_count = COALESCE(x.likes, 0);
