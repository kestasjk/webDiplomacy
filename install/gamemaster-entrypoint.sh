#!/bin/sh

HOME=/application

cd $HOME

export TESTENV=asdf

if [ ! -d vendor ]; then
  echo "ERROR: vendor directory not found; please run composer update in the source directory"
  
else

  echo "Erase old cache data"
  rm -rf cache/*

  echo "Make sure all cache folders writable"
  # Make sure the cache and datc folders are writable, this is v slow in hyper-v docker with a large cache, so first clear the cache
  find . -name "cache" -exec chmod a+rwx {} \;
  find . -name "datc" -exec chmod a+rwx {} \;
  find . -name "variants" -exec chmod a+rwx {} \;

  echo "Make sure config present"
  # If no config has been set up use the sample, which is compatible with docker
  if [ ! -f config.php ]; then
    cp config.sample.php config.php
  fi

  echo "Start PHP server"
  # Fork the FPM server
  /usr/sbin/php-fpm8.4 -O &

  echo "Waiting for DB to be available"
  res=1
  while [ "$res" -ne 0 ]; do
    sleep 1;
    echo "SELECT 1" | mysql --connect-timeout=1 -u webdiplomacy -h webdiplomacy-db -P 3306 --password=mypassword123 webdiplomacy
    res=$?
    echo -n "."
  done

  echo "Checking if DB installed"
  if mysql -u webdiplomacy -h webdiplomacy-db -P 3306 --password=mypassword123 webdiplomacy -e "SHOW TABLES;" | grep -q 'w[Dd]_[Uu]ser' ; then
    echo "DB installed"
  else
    echo "DB not installed, erasing existing variant/dact data and installing new DB"
    rm -rf datc/maps/*.*

    find variants | grep 'cache/.*\..*$' | ( while read a; do rm -v $a; done )
    echo "Make sure all variant cache folders exist"
    ls variants/*/variant.php | sed -e 's/variant.php//' | (while read v; do mkdir "$v""cache"; done)
    
    mysql -u webdiplomacy -h webdiplomacy-db -P 3306 --password=mypassword123 webdiplomacy < $HOME/install/FullInstall/fullInstall.sql
    mysqlResult=$?
    if [ $mysqlResult -ne 0 ]; then
      echo "mysql on fullInstall.sql returned $mysqlResult"
    fi
    mysql -u webdiplomacy -h webdiplomacy-db -P 3306 --password=mypassword123 webdiplomacy < $HOME/install/createBotAccounts.sql
    mysqlResult=$?
    if [ $mysqlResult -ne 0 ]; then
      echo "mysql on createBotAccounts.sql returned $mysqlResult , which indicates the install script failed. Check the logs, recreate the database and rerun gamemaster-entrypoint.sh from the php-fpm container"
    fi
    echo "DB created"
  fi

  sleep 2

  echo "Start gamemaster"
  while true; do
    find . -name "cache" -exec chown -R www-data:www-data {} \;
    gameMasterSecret='' QUERY_STRING='' wget -O - http://webserver/gamemaster.php?gameMasterSecret= > /dev/null 2>&1
    #php -f $HOME/gamemaster.php
    sleep 5
    echo -n "."
  done

fi
