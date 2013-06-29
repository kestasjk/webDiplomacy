Changelog
---------
- Changed the default time that the server will go without processing before stopping processing to 12 minutes. (2 attempts) 
- Added an option for NMR behavior, giving a setting that will make the game wait indefinitely for a missing player.
- Added pagination to game chat archive screens
- Fixed bug where users attempt to join a non-joinable game and get an incorrect error message

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Copy the new code over the old code
- View and test the updated site
- Turn off maintenance mode