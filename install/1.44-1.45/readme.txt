Changelog
---------
* Adding table wD_UserConnections to hold a single record for all new users and all users checked via the advanced access mod tool.
* This table will eventually be inserted into for each new user and will be checked to either insert or update every time a mod 
* accesses a user via the multi checker. This will allow other mods to see how recently a user has been checked. 
* as well as allowing automation of new user checking. This table can eventually be used for future development to 
* completely automate cheating detection of users.

Updating
--------
- 