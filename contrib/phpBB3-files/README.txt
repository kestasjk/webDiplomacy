phpBB3 integration
------------------
2017-03-15

webDiplomacy is able to use phpBB3 as an external forum, if the basic built-in forum
is inadequate. The files in this folder need to be merged with a vanilla phpBB3 
installation.

1. Download phpBB3, and unzip it to contrib/phpBB3/

2. Navigate to this URL on your webDiplomacy installation, and begin the phpBB
	install process.
	
3. Install phpBB to the same database as your webdiplomacy installation. If security
	is a concern you can use a different user account for the phpBB install, and 
	give phpBB read-only access to the wD_Users table only.
	
4. Run phpBB3-files/install.sql (this script assumes you installed phpBB with the 
	standard phpbb_ table prefix).
	This will add a lookup column to associate phpBB users with webDiplomacy users.

5. Once installed copy the styles/AllanStyle-SUBSILVER folder into phpBB3/styles/AllanStyle-SUBSILVER
	Then navigate to the phpBB admin control panel, go to styles, install this new style 
	and set it as the default style, then disable the previous default style.
	This should give the forum a style which fits in with webDiplomacy.
	
6. Disable user registration in the phpBB control panel.

7. Replace phpBB3/config/default/container/services_auth.yml with the corresponding 
	file in phpBB3-files.

8. Copy phpbb/auth/provider/webdip.php into phpBB3/phpbb/auth/provider/webdip.php .

9. Go to the phpBB control panel and wipe the cache. (The bottom-most option)

10. Open adminSetup.sql ; change the webdip_user_id = 10 to your own webDiplomacy user ID.
	This will ensure that you still authenticate as the forum administrator even now that 
	you can only log into the phpBB installation using webDiplomacy accounts.

11. Check that your user account authentication is in sync with your webDiplomacy installation.
	You should find that logging on / off as a user in webDiplomacy logs you on / off in phpBB3,
	and a phpBB3 user account will be automatically created for any authenticated 
	webdiplomacy user who accesses the forum.
	
12. Go to your config.php and add 
	public static $customForumURL = 'contrib/phpBB3/';
	This will cause the forum links to instead direct to the phpBB3 installation.



Note that admin permissions will not automatically carry over; any extra permissions 
required for moderators will have to be set up separately.
