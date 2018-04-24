Changelog
---------
- Added wD_VariantData table for storing per-variant information
- Moved wD_Users country allocation chances for Classic variant into new table
- Added new class for the manipulating wD_VariantData table
- Made Classic weighted random country allocation code use new data store
- Replaced pre-game adjudicators purely random allocator with weighted random country allocation from Classic

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Copy the new code over the old code
- View and test the updated site
- Turn off maintenance mode