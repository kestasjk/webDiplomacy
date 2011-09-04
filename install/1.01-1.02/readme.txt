This release adds gold/silver/bronze donor icons.

Changelog
---------
- images/icons/gold|silver|bronze.png
- User::user_profile() altered
- libHTML::gold|silver|bronze()
- admin/adminActions.php : makeDonatorBronze|Silver|Gold

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Copy the new code over the old code
- View and test the updated site
- Turn off maintenance mode