0.96 improves stability for games by allowing extra checks before processing, making up for 
any downtime automatically detected and allowing rules regarding people who don't submit orders 
(e.g. whether to continue anyway or always wait).
Cacheing is also improved, with forum message icons now displaying dynamically, allowing forum 
HTML to be cached.

Changelog
---------
- Updated code which checks last process times and freezes game automatically to work with live games
- JavaScript/CSS tweaks, improving efficiency and removing minor bugs
- Removed game search delay period
- User online icon added to private messages
- Individual forum messages are marked as having been read, and the new-message-icons are shown accordingly

Updating
--------
- Make sure you have already updated to 0.95, which wasn't released; you need to go through 0.94-0.95 before updating to 0.96.
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Unpack the new code, copy config.sample.php to config.php, and enter the correct config details from the old config.php
- Move the old code into a folder which can't be accessed from the web
- Run update.sql
- Copy the new code where the old code used to be
- View and test the updated site
- Run gamemaster.php to refresh a forum value which has changed
- Turn maintenance mode off