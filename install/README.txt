webDiplomacy readme, for webmasters
----------------------------------------
=> Note to players
=> Requirements
=> Installing
=> Updating
=> Maintenance
=> Security


Note to players
---------------
webDiplomacy doesn't have an install wizard which checks and installs everything for you, the 
code is made available mainly for developers who want to create unique webDiplomacy servers 
(e.g. with unique translations or rule variants), or work on the official code.
If your only experience of PHP and MySQL is installing phpBB, say, you may have trouble with 
this software.

If you just want to play with friends try to find an existing webDiplomacy server and set up 
a private game there. (http://webdiplomacy.net/ is the official server.)


Requirements
------------
Although webDiplomacy has been able to work on a variety of systems and is installed on a 
number of sites we can only guarantee that it will work when installed as per the below
procedure:

Docker
------
The install guide below is the guide for setting up a production system. For development purposes
it is highly recommended to use the Docker Compose image in /docker-compose.yml along with the
guide within that file, which makes the process much, much easier.

Installing
----------
This script is the installation guide for installing on Ubuntu 20.04. It is oriented towards
quickly getting started on a fresh Ubuntu install, e.g. a WSL/Hyper-V VM. 
# Starting from a fresh (minimal) Ubuntu 20.04 install in a sandbox VM:

# Get system up to date etc
apt update
apt upgrade

# Set up firewall for remote access if necessary:
ufw allow from any to any port ssh
ufw allow from any to any port http
ufw allow from any to any port https
ufw enable

# Install admin tools
apt install -y net-tools openssh-server git

# At this point recommend moving from the desktop terminal to an SSH session, now that SSH is installed and accessible, for easier copy & paste etc.
ifconfig # get the IP of the VM

# Install webDip requirements
apt install -y php7.4 apache2 mariadb-server memcached php7.4-gd php7.4-curl php7.4-memcached php7.4-mysql 

cd /var/www/html/
rm index.html
git clone https://github.com/kestasjk/webDiplomacy.git .
echo "CREATE DATABASE webdiplomacy" | mysql
cat install/FullInstall/fullInstall.sql | mysql webdiplomacy;

cp config.sample.php config.php

# Generate random secrets:
hash=`head /dev/random | md5sum | sed -e 's/[^0-9a-f]//g'`
echo "CREATE USER 'webdiplomacy'@'localhost' IDENTIFIED BY '$hash'" | mysql
echo "GRANT ALL PRIVILEGES ON webdiplomacy.* TO 'webdiplomacy'@'localhost'" | mysql
sed -i config.php -e "s/database_password='mypassword123'/database_password='$hash'/" 

hash=`head /dev/random | md5sum | sed -e 's/[^0-9a-f]//g'`
sed -i config.php -e "s/salt=''/salt='$hash'/"
hash=`head /dev/random | md5sum | sed -e 's/[^0-9a-f]//g'`
sed -i config.php -e "s/secret=''/secret='$hash'/"
hash=`head /dev/random | md5sum | sed -e 's/[^0-9a-f]//g'`
sed -i config.php -e "s/jsonSecret=''/jsonSecret='$hash'/"
hash=`head /dev/random | md5sum | sed -e 's/[^0-9a-f]//g'`
sed -i config.php -e "s/gameMasterSecret=''/gameMasterSecret='$hash'/"
# Create a script to run the gamemaster:
echo "$$ > runGamemaster.pid; while [ $$ -eq `cat runGamemaster.pid` ]; do wget 'http://localhost/gamemaster.php?gameMasterSecret="$hash"' -O /dev/null; sleep 5; done" > /var/www/runGamemaster.sh


# Set e-mail to be output to the browser instead of sent to allow e-mail free registration:
sed -i config.php -e 's/"UseDebug" => false/"UseDebug" => true/'

# Allow error messages to show:
sed -i /etc/php/7.4/apache2/php.ini -e 's/display_errors = Off/display_errors = On/'
sed -i /etc/php/7.4/apache2/php.ini -e 's/display_startup_errors = Off/display_startup_errors = On/'

# Change owner from root to www
chown -R www-data .
chgrp -R www-data .

service apache2 restart

# ifconfig to get the IP of the VM

# Register an account, and use the link that will be shown directly rather than e-mailed to validate the e-mail.
http://172.26.151.74/register.php

# If you see a database version code version mismatch run the update scripts to get to the current version:
cat install/1.66-1.67/update.sql | mysql webdiplomacy

# Note: The validation link may start with https, which you may need to change to http if apache is not set up for SSL.

# Once user is created assign admin privileges:
echo "UPDATE wD_Users SET type='User,Moderator,Admin' WHERE type='User';" | mysql webdiplomacy

# After refreshing the Mod CP will be available; turn on maintenance mode to prevent other users getting in, and to enable the DATC test cases.

# Go to /datc.php and click Batch all to start running through all test cases.

# To run the gamemaster in the background:
sh /var/www/runGamemaster.sh

# New webdiplomacy installation up and running




# This will create an extra user account with a _ at the end, copied from the first account. Run as many times as needed:
INSERT INTO wD_Users (username, email, points,comment,homepage,hideEmail,timeJoined,locale,timeLastSessionEnded,lastMessageIDViewed,password,type,notifications,ChanceEngland,ChanceFrance,ChanceItaly,ChanceGermany,ChanceAustria,ChanceRussia,ChanceTurkey,muteReports,silenceID,cdCount,nmrCount,cdTakenCount,phaseCount,gameCount,reliabilityRating,deletedCDs,tempBan,emergencyPauseDate,yearlyPhaseCount,tempBanReason)
SELECT CONCAT(username,'_') username, CONCAT(email,'_') email, points,comment,homepage,hideEmail,timeJoined,locale,timeLastSessionEnded,lastMessageIDViewed,password,type,notifications,ChanceEngland,ChanceFrance,ChanceItaly,ChanceGermany,ChanceAustria,ChanceRussia,ChanceTurkey,muteReports,silenceID,cdCount,nmrCount,cdTakenCount,phaseCount,gameCount,reliabilityRating,deletedCDs,tempBan,emergencyPauseDate,yearlyPhaseCount,tempBanReason FROM wD_Users u INNER JOIN (SELECT MAX(id) id FROM wD_Users) lastUser ON lastUser.id = u.id;

# Jump to any user account created, to quickly add users to a game:
http://172.26.151.74/board.php?gameID=2&auid=11



# Setting up SSL, if required:
# Set the real hostname:
#nano -w /etc/hostname 
#apt-get install python3-certbot-apache
#certbot


Updating
--------
If updating from an older webDiplomacy version check the install/ folder subdirectories. Each 
update should come with a readme, and if required an update.sql script. You may have to update 
through multiple versions.

Remember to take backups! There aren't a large number of people testing these update scripts, 
unlike phpBB updates or other scripts you may be familiar with, so bugs and tweaking are to be 
expected as part of the updating process.


Maintenance
-----------
When logged on as an administrator there are a bunch of variables at the bottom that indicate 
the general health of the server, whether the background-processor is running , whether there
are any crashed games, etc. It's best to keep an eye on these.


Security
--------
First off you must make sure you have read the warnings in the config comments very carefully. 
The software won't try and detect silly mistakes that expose errorlogs/orderlogs to everyone. 
If people can access errorlogs they may be able to see database passwords and other sensitive 
info. If they can access the orderlogs they will know the moves others are entering.

Security is taken seriously in the code; no major security holes have yet been found in it, 
but this has to be supplemented by setting strict permissions and using secure passwords. 

After installation everything can be and should be set to read-only except for the errorlog, 
accesslog, and mapstore directories, which should be set so that nothing within them can be 
executed (e.g. by using a .htaccess file for Apache).

The admin, board, gamemaster, gamepanel, gamesearch, install, lib, locales, map, objects, and 
register folders are all not required to be accessible from the web, and so access to these from
the web server should be restricted (e.g. by using a .htaccess file for Apache).


Memcached is also required to use the system, and it should be firewalled off as it does not 
have authentication built in.


Kestas Kuliukas - 2022-02-12
