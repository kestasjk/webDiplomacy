0.95 fixes game locking to work better with live games, and improves cacheing capabilities.
With live games instant processing is more efficient and makes games more playable, and 
improved cacheing and cacheing capability help further take the load off the server.

Changelog
---------
- The moves table, used for temporary move storage during adjudication, can now be used by many games at once.
- There is a generalized 'cache/' directory, which can be wiped at any time but which reduces database queries significantly.
- The mapstore/ map cache folder is now in cache/games/
- mapList.js is no longer created to index cached maps, as the benefit of knowing which maps are cached is negated by
	having to frequently reindex the list on every map request.
- global/timer.js is now in javascript/timehandler.js , and takes care of all timestamps and time countdowns.
- Users no longer need to give timezone information; JavaScript applies their timezone automatically.
- Online user icons are hidden or displayed using JavaScript and a JSON list of logged on user-IDs, allowing a wider range of
	HTML to be cached.
- Some JavaScript bugs ironed out

Updating
--------
- Take a backup (database and files) <-- Important!
- Create a 'cache/' folder with the same permissions as 'mapstore/', and delete 'mapstore/'
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Unpack the new code, copy config.sample.php to config.php, and enter the correct config details from the old config.php
- Move the old code into a folder which can't be accessed from the web
- Run update.sql
- Copy the new code where the old code used to be
- View and test the updated site
- Turn maintenance mode off