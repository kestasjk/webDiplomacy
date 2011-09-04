This is a bug-fix release, resolving several bugs (mostly minor severity) and adding a few tweaks, to tie down the 
large set of changes from the previous release.
Also there is a new reporting feature to allow users to draw moderators' attention to games/users, a new config.php 
setting allowing owners of other webDiplomacy installations to add FAQ questions regarding their specific installation
without having to edit the faq.php file itself.
The forum now indicates which thread a user has replied to using a star icon next to the thread title, and finally 
almost all page headings now have a title-bar with the title and description of the page.

Changelog
---------
- A few extra requested mod features have been added
- Map drawing has been improved;
	- A typo in the map territory names has been fixed
	- Large territory-name overlays are now loaded and unloaded when needed, saving memory
	- The red cross marking failed orders now displays correctly without distortion
	- A GD line-drawing library function is now used for most line-drawing instead of a custom polygon-based 
	function, improving performance
	- Borders are drawn around map clips/thumbnails
	- Cache-disabling flags have been made less chaotic
	- A case where archived pre-game maps would show units in their current positions has been fixed
- Several generic-variant-code bugs have been fixed
- A few installer problems have been fixed
- Some incorrect territory links in the World variant, and a bug in the BuildAnywhere variant, have been fixed
- The variant author utility for exporting map data has had a bug fixed
- The World variant now uses the typical variant install.sql format rather than the previous custom install code, 
	which had some link problems.
- The World variant has been updated with the latest data (thanks gilgatex)
- Users can report games/users to moderators, who will see the reports listed
- Moderators can add notes, public or private, and disable a user's reporting ability
- Config::$faq variable added to allow extra questions & answers to be added to the FAQ.
- New CSS and HTML to show a title-bar and description-bar on many pages.
- When posting a forum message a JSON array of all threadIDs the current user has posted to is saved, and this is 
	used to draw an icon on threads which the user has responded to.

Updating
--------
- NOTE: People who installed 0.97 from a fresh install may want to wipe their installation and redo it.
	There was a problem with the supplied install.sql which made primary keys start from the official server's 
	number. If you are updating from a fresh 0.97 install you need to alter these auto-increment values to suit 
	your server manually. Sorry for the inconvenience.

- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Unpack the new code, copy config.sample.php to config.php, and enter the correct config details from the old config.php
- Move the old code into a folder which can't be accessed from the web
- Run update.sql
- Copy the new code where the old code used to be
- Delete variants/*/cache/*
- View and test the updated site
- (Optional: Reset the DATC tests and rerun them, to update the test images with the new map code)
- Turn off maintenance mode