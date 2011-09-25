A couple of features to make the forum less chaotic for large installations.

Changelog
---------
- The ability to mute specific threads
- Users can like posts, and the number of likes are displayed next to the post, and the total is displayed on the user's profile page
- Timestamps added to mutes and likes, to allow the data to be used more effectively in the future
- Mod status icons are no longer displayed

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Copy the new code over the old code
- View and test the updated site
- Turn off maintenance mode