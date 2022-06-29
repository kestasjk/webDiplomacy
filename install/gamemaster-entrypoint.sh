#!/bin/bash

HOME=/application

sleep 10
if mysql -u webdiplomacy -h mariadb -P 3306 --password=mypassword123 webdiplomacy -e "SELECT COUNT(id) FROM wD_Users;" ; then
  echo "DB was already created"
else
  mysql -u webdiplomacy -h mariadb -P 3306 --password=mypassword123 < $HOME/install/FullInstall/fullInstall.sql
  mysql -u webdiplomacy -h mariadb -P 3306 --password=mypassword123 webdiplomacy < $HOME/install/seeds.sql
  echo "DB created"
  # the next lines are related to permissions, I'm not sure why we need them, I think because php-fpm doesn't have the right config
  mkdir $HOME/cache
  mkdir $HOME/variants/ColdWar/cache
  cd $HOME
  find . -name "cache" -exec chmod a+rwx {} \;
fi
if [ ! -x /usr/bin/wget ]; then
  apt-get update && apt-get install -y wget
fi
sleep 10
while true; do
  wget 'http://webserver/gamemaster.php?gameMasterSecret=' -O /dev/null >> /tmp/gamemaster.log 2>&1
  sleep 5
done