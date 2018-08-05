#!/usr/bin/env bash

# create configuration.php if it does not exist
cp -n /var/www/html/public_html/system/config/configuration.default.php /var/www/html/public_html/system/config/configuration.php

# update $BASE_URL and $MOBILE_BASE_URL in system/config
perl -p -i -e 's/\$BASE_URL.*;/\$BASE_URL = "http:\/\/$ENV{BASEURL}\/";/g' /var/www/html/public_html/system/config/configuration.php
perl -p -i -e 's/\$MOBILE_BASE_URL.*;/\$MOBILE_BASE_URL = "http:\/\/$ENV{BASEURL}\/";/g' /var/www/html/public_html/system/config/configuration.php

# change the ownership of the code
chown www-data:www-data -R /var/www/html/public_html

# launch daemon process (inherited from the image arm32v7/php)
apache2-foreground
docker-php-entrypoint
