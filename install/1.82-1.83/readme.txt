Changelog
---------

- The game listings no longer count games on every view. gamelistings.php read the number of new,
  active and finished games (and, for logged out visitors, joinable games) from its own COUNT
  queries behind a Redis cache; the finished one alone walked over a million index entries. Those
  numbers are already counted every seven minutes by the gamemaster and kept in wD_Misc, which
  every page has loaded already, so the page now reads them from there.

  The wD_Misc counts leave out bot games, which is what the listings have always shown and what
  GamesActive already did. The home page and footer figures for starting, joinable, active and
  finished games follow that.

- The gamemaster was recomputing those counts on most cycles rather than every seven minutes: the
  block that runs them set wD_Misc.LastStatsUpdate without writing it, so the timestamp only
  reached the database when a later task in the same run happened to write wD_Misc.

- Game listing queries ask for phases and player types as IN lists rather than with <>, and
  wD_Games has a new (playerTypes, phase, pot) index to serve them, so a tab reads the games it is
  listing instead of every game in that phase.

- The forum's likes list (postlove/{user_id}) was the heaviest query on the server: posts joined to
  topics and likes with "(p.poster_id = x OR pl.user_id = x)", an OR across two tables that no
  index can serve, run twice per view because the row count was fetched a row at a time and added
  up in PHP. The likes received and the likes given are now looked up separately, each on an index,
  and UNIONed. phpbb_posts_likes gets an index on user_id, which it had no way to be read by.

- libCache::dir() no longer throws when two requests create the same cache directory at the same
  moment; it rechecks for the directory after a failed mkdir().
