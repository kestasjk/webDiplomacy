FROM phpdockerio/php:8.4-fpm
WORKDIR "/application"

RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        php8.4-gd \ 
        php8.4-memcached \ 
        php8.4-redis \ 
        php8.4-mysql \ 
        php8.4-xdebug \
        redis \
        nano \
        less \
        net-tools \
        iputils-ping \
        git \ 
        wget \
        mariadb-client; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
