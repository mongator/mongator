#!/bin/bash
printf "\n"| pecl install acp
printf "\n"| pecl install memcached

wget https://github.com/nicolasff/phpredis/archive/master.zip
unzip master.zip
sh -c "cd phpredis-master && phpize && ./configure && sudo make install"
echo "extension=redis.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`