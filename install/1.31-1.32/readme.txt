Changelog
---------
- Corrected TRANSACTION ISOLATION LEVEL setting syntax issue to work with MySQL 5.6
- Added finished game cancellation feature to admin CP
- Optimized forum likes functionality to use fewer database resources and prevent deadlocks
- Added admin action to re-sync forum post likes with the user tracked like records
- Support for MySQL STRICT_TRANS_TABLES,STRICT_ALL_TABLES modes (which is the default for certain MySQL installs) (still not fully tested)
- Admin action to allow games to be cancelled after being finished, for cases of cheating
- Register page fix

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Copy the new code over the old code
- View and test the updated site
- Turn off maintenance mode