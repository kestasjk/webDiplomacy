0.94 adds client-side order generation and updates some map code, to fix bugs, reduce 
server load, prepare for future variants and translations, and make things quicker for users.

Changelog
---------
- The prototype and scriptaculous libraries are included in contrib/ .
- Orders are now generated using JavaScript in the browser, the server now only validates.
	- "Update" is now "Save", "Finalize" is now "Ready".
	- Save/Ready buttons and order drop-downs will lock when they cannot be manipulated.
	- The DATC tests have been updated to test against the new JavaScript code.
	- Submission of orders occurs without loading game/member information, reducing queries/update and row locks.
	- Coordinate info for future point-and-click orders and client-side map display is included.
- The large map uses an image overlay for territory names, rather than printing names via GD.
- Glitches in drawing maps with fleets in North/South coasts have been fixed.
- Minor additions to admin/mod interfaces

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Unpack the new code, copy config.sample.php to config.php, and enter the correct config details from the old config.php
- Move the old code into a folder which can't be accessed from the web
- Copy the new code where the old code used to be
- View and test the updated site
- Turn maintenance mode off