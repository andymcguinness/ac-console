#! /bin/bash

echo "Installing ac_cli...";

if [ ! -f ~/.active_collab ];
then
    echo "Creating new config file."
    touch ~/.active_collab
    echo "ac_url = " >> ~/.active_collab
    echo "ac_token = " >> ~/.active_collab
    echo "projects[]" >> ~/.active_collab
fi

if [ ! -f composer.phar ];
then
    curl http://getcomposer.org/installer | php
    php composer.phar install
fi

cp ac.php /usr/local/bin/ac
chmod +x /usr/local/bin/ac

echo "Finished installing."
