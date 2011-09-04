Changelog
---------
In 0.91 some new game customization options have been added by jayp:
- Anonymous games
- Alternative messaging rules (Public messaging only, no messaging)
- Some bug fixes in Admin CP and the Leave button
- Improved cache control
- In short(<=15 mins) games, Retreat and Builds phases are processed right away when everyone finalizes their moves.
- The next-phase countdown timer counts down with JavaScript
- Game times can now range from 5 minutes to 10 days
- WTA enabled for everyone

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