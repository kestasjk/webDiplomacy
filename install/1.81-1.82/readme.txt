Changelog
---------

- Fixed the wD.Test.3 DATC test data (testID 903), which could never run: its Turkish fleet in
  Rumania was ordered to Bulgaria without a coast, and its second Italian supporting fleet was
  placed on top of the Turkish army in Ankara. See the comments in update.sql.

- Ghost Ratings ranks are now columns on wD_GhostRatings (ratingRank, peakRank, peakRankActive),
  refreshed every half hour by the gamemaster, with indexes on (categoryID, rating) and
  (categoryID, peakRating). Profiles and the hall of fame read them instead of counting every
  player rated above the viewer on each page view behind a Redis cache that could never hit.

- The cached forum like counts on phpbb_users are fixed and extended: webdip_like_count is now
  NOT NULL and actually filled (its 1.71-1.72 backfill was invalid SQL and never ran), and
  webdip_like_given_count is added beside it. The Post Love extension keeps both up to date as
  likes are toggled rather than counting them for every post row it renders.
