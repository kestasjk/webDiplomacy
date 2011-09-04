This release has a few bug fixes which have accumulated, plus a feature addition for allowing players to mute each other.

Changelog
---------
- Users can mute other users and countries, preventing their messages from being displayed and preventing them from receiving
	messages from that user/country.

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Copy the new code over the old code
- View and test the updated site
- Turn off maintenance mode