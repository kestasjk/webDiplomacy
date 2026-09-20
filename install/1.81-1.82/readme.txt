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

- Config::$apiConfig is replaced by Config::$botVariantIDs, holding just the variant IDs bots can
  play (1, 15, 23). config.php can be updated at leisure: libVariant::botVariantIDs() reads the old
  $apiConfig['variantIDs'] when the new name isn't there. Its other keys are gone with the code that
  read them - "enabled" 404'd the whole API, which is now how the site's own board talks to the
  server rather than a bots-only thing; "restrictToGameIDs" would have to list every game on the
  site to mean anything; "noPressOnly" had not been read by anything for some time.

- Browsers now report their own errors and timings. Script errors, unhandled promise rejections and
  React errors from the game board are sent to the new client/error API route and written to the
  error log directory beside the server's own errors, with the same de-duplication, so they show up
  in the admin error list. Timings go to client/metrics and appear on status.php under "Reported by
  browsers" as METRICS_CLIENT_* counters. Both routes accept a logged-out browser and are rate
  limited per address. Nothing new is stored in the database.
