#!/usr/bin/env bash

# change the ownership of the code
chown www-data:www-data -R /var/www/html/public_html

# launch daemon process (inherited from the image arm32v7/php)
apache2-foreground
docker-php-entrypoint
