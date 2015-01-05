Changelog
---------
-- Added a column to wD_CivilDisorders to determine whether the CD was forced by a mod
-- Added a column to wD_Users to count CDs that have been retaken by users

Updating
--------
- Take a backup of the database <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Run fixCDs.php (located in this directory) from the command line, in the root directory of your webdip install
- Update the site code
- View and test the updated site
- Turn off maintenance mode
