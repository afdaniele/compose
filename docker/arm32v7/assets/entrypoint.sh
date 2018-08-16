#!/usr/bin/env bash

# install/update compose
/root/install_compose.sh

# change the ownership of the code
chown www-data:www-data -R $COMPOSE_DIR/public_html

# launch daemon process (inherited from the image arm32v7/php)
apache2-foreground
docker-php-entrypoint
