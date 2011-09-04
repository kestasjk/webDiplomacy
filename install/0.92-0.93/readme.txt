In 0.93 some fixes to points system problems, and some additional safety checks and assertions.

Changelog
---------
- Games are backed up before being deleted on cancellation
- Extra points allocation details are logged
- An bug with points allocation was fixed
- Some resource use checks to limit people using too many server resources

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Unpack the new code, copy config.sample.php to config.php, and enter the correct config details from the old config.php
- Move the old code into a folder which can't be accessed from the web
- Copy the new code where the old code used to be
- Import update.sql
- View and test the updated site
- Turn maintenance mode off