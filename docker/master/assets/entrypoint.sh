#!/usr/bin/env bash

# change the ownership of the code
chown www-data:www-data -R /var/www/html/public_html

# launch daemon process (inherited from the image nimmis/apache-php7)
/my_init
