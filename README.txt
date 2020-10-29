webDiplomacy readme, for webmasters
----------------------------------------
=> Note to players
=> requirements
=> Installing
=> Updating
=> Maintenance
=> Security
=> Developing
=> Help


Note to players
---------------
webDiplomacy doesn't have an install wizard which checks and installs everything for you, the 
code is made available mainly for developers who want to create unique webDiplomacy servers 
(e.g. with unique translations or rule variants), or work on the official code.
If your only experience of PHP and MySQL is installing phpBB, say, you may have trouble with 
this software.

If you just want to play with friends try to find an existing webDiplomacy server and set up 
a private game there. (http://webdiplomacy.net/ is the official server.)


requirements
------------
- PHP 5.2 to 7.0. 7.1+ is not yet supported. webDip runs on PHP 7.0
- MySQL 5, with support for MyISAM, InnoDB, and memory tables
- The GD 2 PHP extension, with FreeType support
- Ability to send e-mail from the server (Access to an SMTP server or sendmail)
- Quite a bit of disk space (depending on the expected size of the server; if 
	your hosting space is measured in MB you may not have enough)
- Quite a bit of processing power (if you've only got an account with an oversold 
	shared-hosting company you may have problems; webDiplomacy probably uses more 
	resources per user than, say, phpBB)
- Ability to set up a crontab to fetch a web-page every 5 minutes or so, to run 
	the game processing / server maintenance script


Installing
----------
=> Database scripts
Run install/install.sql to set up the initial data-set, you can run this in 
phpMyAdmin's "Import" tab, if you don't have shell access.

Currently, you will also need to run all database update scripts, found in
install/1.00-1.01/update.sql through to install/1.34-1.35/update.sql. As an alternative,
you can run install/FullInstall/fullInstall.sql instead of the individual version SQL files.

Note that webDiplomacy is incompatible with MySQL's strict mode, so if STRICT_ALL_TABLES 
or STRICT_TRANS_TABLES are set in the sql_mode, then you will have errors when
you run the gamemaster, the DATC tests or send PMs. To fix this, ensure that sql_mode
does not contain either strict mode.

=> Config
Edit config.sample.php to work with your setup, being very careful to read the warnings 
about security issues. The salts/secrets, errorlog/orderlog directories, can all
leave your server wide open if you don't set them right. Rename to config.php when ready.

=> Log-on
Once you've set config.php up you can use the random gameMasterSecret you entered
to authenticate as the admin. First create a user via the registration page, then 
once logged on go to gamemaster.php?gameMasterSecret=[yoursecret] .
 
It will give you admin rights, then refresh the page as admin to run the gamemaster 
script for the first time, which will initialize various stats and maintenance 
processes. (This only works for the first user that does it, any other 
admins/moderators have to be set via the admin control-panel.)

Go to the Admin CP via the menu, find the "Toggle Maintenance Mode" action and 
run it, preventing others from using the server up while you're testing it.

=> Test
Once that's set up you should go to Help->DATC. With Maintenance mode on it will 
show a screen which can run through the DATC tests, which provides an easy way to 
test that the installation was successful. Click Batch-test and it'll run through
all the tests one by one. If maps are being generated successfully then everything
is probably going to work. (Batch-testing the DATC tests may have problems in IE,
try Firefox/Chrome/Safari until this is fixed.)

=> Open up
Once you've looked around, posted a test message etc, and double-checked your 
config.php file for security issues, you can disable Maintenance mode via the 
admin CP to allow regular users to access the installation.

=> Start a processing cycle
Now you need to set the system up so that games are automatically processed. This 
means running gamemaster.php?gameMasterSecret=[yoursecret] every 5 minutes or so 
from an automated script, via cron for example. Here is an example crontab:

For example here is my cron line:
*/5    *       *       *       *       /usr/bin/wget -O - 'http://webdiplomacy.net/gamemaster.php?gameMasterSecret=12345' >/dev/null 2>&1

'*/5    *       *       *       *' sets the times the script should run (every 5 minutes)
'/usr/bin/wget' is the program which downloads the script thus running it.
'http://webdiplomacy.net/gamemaster.php?gameMasterSecret=12345' is the gamemaster URL
'-O - ' specifies to output to standard output
'>/dev/null 2>&1' specifies that the standard output should be discarded (i.e. just run the 
page without saving the results)

=> Check
Once you're seeing the Last process time at the bottom of the page staying within 5 
minutes of the current time the background processing is working, and everything should 
be up and running.

Troubleshooting Installation
----------------------------

If you're having issues with access to the CP Admin, make sure you have a user created,
and that the user in the `wD_Users` table has the following `type`:

System,User,Moderator,Admin

Then load the gamemaster page above again, and it should work as expected.

Updating
--------
If updating from an older webDiplomacy version check the install/ folder subdirectories. Each 
update should come with a readme. You may have to update through multiple versions.

Remember to take backups! There aren't a large number of people testing these update scripts, 
unlike phpBB updates or other scripts you may be familiar with, so bugs and tweaking are to be 
expected as part of the updating process.


Maintenance
-----------
Be sure to subscribe to the http://sf.net/projects/phpdiplomacy mailing list, to receive 
emails when updates are released, as they'll probably contain bugfixes.

There are a bunch of variables at the bottom that indicate the general health of the server, 
whether the background-processor is running , whether there are any crashed games, etc. It's 
best to keep an eye on these.

=> Cleaning up
The mapstore folder can be deleted every so often if it's getting too large, however if 
space isn't a concern the directory structure is tree like and will scale to any reasonable 
number of games, and caching maps saves lots of CPU-time.

Access logs in the database also need to be cleaned periodically, the admin CP will wipe all 
but the last 30 days of logs.

If you have order-logging enabled the orderlog directory will fill up quickly, and can be 
wiped regularly. If players aren't complaining that they entered different orders there's 
no point logging orders to prove them wrong.

If error logging is enabled logs can accumulate, but they should be handled and removed as they come.

Game messages will probably take up the largest part of database storage. If keeping messages 
from old games is less important than using little space they can be deleted for old games 
occasionally (especially gamemaster auto-messages which also make a large part of the 
GameMessages table)

TerrStatusArchive and MovesArchive are the other two large tables; they contain data used to 
draw old maps, and can be periodically wiped for old games if archived game history isn't 
needed.

Other than that check the admin CP status lists every so often which should highlight any problems 
which are auto-detected.


Security
--------
First off you must make sure you have read the warnings in the config comments very carefully. 
The software won't try and detect silly mistakes that expose errorlogs/orderlogs to everyone. 
If people can access errorlogs they may be able to see database passwords and other sensitive 
info. If they can access the orderlogs they will know the moves others are entering.

Security is taken seriously in the code; no security holes have yet been found in it, and 
nowhere are there dynamic require_once statements or evals, or any of the common PHP security traps, 
but this has to be supplemented by setting strict permissions and using secure passwords. 

After installation everything can be and should be set to read-only except for the errorlog, 
accesslog, and mapstore directories, which should be set so that nothing within them can be 
executed (e.g. by using a .htaccess file for Apache).

The admin, board, gamemaster, gamepanel, gamesearch, install, lib, locales, map, objects, and 
register folders are all not required to be accessible from the web, and so access to these from
the web server should be restricted (e.g. by using a .htaccess file for Apache).

You may want to monitor webDiplomacy's resource use; it doesn't do this automatically and it
may be using 


Developing
----------
Check http://webdiplomacy.net/developers.php for the most up-to-date developer documentation.

Be aware of the obligations of the AGPL software license we use: *If you change webDiplomacy you 
must be prepared to share any changes you make. If that doesn't fit your requirements do not use 
this software!* 


Help
----
If you have any questions you can get help at the developer forum at http://forum.webdiplomacy.net/


Kestas Kuliukas - 30/08/09
