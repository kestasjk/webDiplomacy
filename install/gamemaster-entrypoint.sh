#!/bin/sh

HOME=/application

cd $HOME

echo "Make sure all cache folders exist"
mkdir $HOME/cache
ls $HOME/variants/*/variant.php | sed -e 's/variant.php//' | (while read v; do mkdir "$v""/cache"; done)

echo "Make sure all cache folders writable"
# Make sure the cache folders are writable
find . -name "cache" -exec chmod a+rwx {} \;

echo "Make sure config present"
# If no config has been set up use the sample, which is compatible with docker
if [ ! -f config.php ]; then
  cp config.sample.php config.php
fi

echo "Start PHP server"
# Fork the FPM server
/usr/sbin/php-fpm7.4 -O &

sleep 2

echo "Checking if DB installed"
if mysql -u webdiplomacy -h mariadb -P 3306 --password=mypassword123 webdiplomacy -e "SHOW TABLES;" | grep -q 'w[Dd]_[Uu]ser' ; then
  echo "DB installed"
else
  echo "DB not installed, installing new DB"
  mysql -u webdiplomacy -h mariadb -P 3306 --password=mypassword123 webdiplomacy < $HOME/install/FullInstall/fullInstall.sql
  mysql -u webdiplomacy -h mariadb -P 3306 --password=mypassword123 webdiplomacy < $HOME/install/createBotAccounts.sql
  echo "DB created"
fi

sleep 2

echo "Start gamemaster"
while true; do
  gameMasterSecret='' QUERY_STRING='' php -f $HOME/gamemaster.php > /dev/null 2>&1
  sleep 5
  echo -n "."
done
