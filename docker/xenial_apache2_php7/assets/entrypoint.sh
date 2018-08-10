#!/usr/bin/env bash

# install or update compose
git -C /var/www/html pull origin $COMPOSE_VERSION || git clone --depth 1 -b $COMPOSE_VERSION https://github.com/afdaniele/compose.git /var/www/html

# change the ownership of the code
chown www-data:www-data -R /var/www/html/public_html

# launch daemon process (inherited from the image nimmis/apache-php7)
/my_init
