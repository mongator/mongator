#!/bin/bash
printf "\n"| pecl install apc

wget https://github.com/nicolasff/phpredis/archive/master.zip
unzip master.zip
sh -c "cd phpredis-master && phpize && ./configure && sudo make install"
echo "extension=redis.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
echo "extension=memcached.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`