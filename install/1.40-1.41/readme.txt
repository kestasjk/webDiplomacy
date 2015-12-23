Changelog
---------
* Two new scoring methods, sum of squares and unranked games

Updating
--------
- Take a backup of the database <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Update the site code
- View and test the updated site
- Turn off maintenance mode
- rebuild unit destroy indexes (via admin CP) for larger variants

