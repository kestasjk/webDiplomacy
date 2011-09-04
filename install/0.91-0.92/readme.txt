In 0.92 some fixes for 0.91 changes, and some improvements to the admin CP

Changelog
---------
- Fixed problem involving processing games within the board script, which causes problems. 
	Now the user is given permission to run the gamemaster script itself via a time-limited token, so games are processed immidiately but are
	processed in the normal way.  
- Better organization for the admin CP; functions are grouped according to whether they're game/user/other related.
- The admin CP can be given a global ID to use, user/game or both, which are entered into the appropriate fields automatically.
- The admin CP automatically prints the game board/user profile link when a game/user ID is submitted.
- Some tweaks to gamemaster preventing rare cases of wiped games stuck in process-queue loops.
- Moderators can rearrange players into different countries, for use in tournaments
- Moderators can change the press/game-messaging settings
- The profile page can now search for users, given an ID number/username/e-mail address.
- The home page times now have a border to distinguish them.
- The home page no longer displays whether anonymous users are online.
- Moderators can now see anonymous players that they're not in the same game as.
- The home page now has a section for notices, that allows PMs to be responded to more easily.
- PMs now escape correctly, and have message->HTML substitution like the forum (gameID=123 becomes a link etc)
- Order archives are now displayed using a more traditional format, and more succinctly
- Fixed problem of pause votes resulting in null values for process time, without setting the process-status to Paused 
- Large map territory names now loaded from an image and pasted onto map, as with small map
- Switching in and out of user accounts made more seamless for admin users
- Anonymous-game message timestamps have the last 12 bits taken off, to prevent people comparing message times to who's online 

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