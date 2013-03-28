if [ '$VERSION' = '1.2.10' ]; then 
    wget http://pecl.php.net/get/mongo-1.2.10.tgz
    tar -xzf mongo-1.2.10.tgz
    sh -c "cd mongo-1.2.10 && phpize && ./configure && sudo make install"
    echo "extension=mongo.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
elif [ '$VERSION' = '1.3.6' ]; then 
    wget http://pecl.php.net/get/mongo-1.3.6.tgz
    tar -xzf mongo-1.3.6.tgz
    sh -c "cd mongo-1.3.6 && phpize && ./configure && sudo make install"
    echo "extension=mongo.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
elif [ '$VERSION' = '1.0.11' ]; then 
    wget http://pecl.php.net/get/mongo-1.0.11.tgz
    tar -xzf mongo-1.0.11.tgz
    sh -c "cd mongo-1.0.11 && phpize && ./configure && sudo make install"
    echo "extension=mongo.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
fi