Changelog
---------
- New game "Director" feature; moderators can set a regular user as the "Director" for a certain game,
	and that user will then be able to perform certain admin operations on that game. 

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Copy the new code over the old code
- View and test the updated site
- Turn off maintenance mode