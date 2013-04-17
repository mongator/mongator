#!/bin/bash
wget https://github.com/nicolasff/phpredis/archive/master.zip
unzip master.zip
sh -c "cd phpredis-master && phpize && ./configure && sudo make install"