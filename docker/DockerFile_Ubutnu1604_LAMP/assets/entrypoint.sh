#!/usr/bin/env bash

# update $BASE_URL and $MOBILE_BASE_URL in system/config
perl -p -i -e 's/\$BASE_URL.*;/\$BASE_URL = "http:\/\/$ENV{BASEURL}\/";/g' /var/www/html/public_html/system/config/configuration.default.php
perl -p -i -e 's/\$MOBILE_BASE_URL.*;/\$MOBILE_BASE_URL = "http:\/\/$ENV{BASEURL}\/";/g' /var/www/html/public_html/system/config/configuration.default.php

# change the ownership of the code
chown www-data:www-data -R /var/www/html/public_html

# launch daemon process (inherited from image nimmis/apache-php7)
/my_init
