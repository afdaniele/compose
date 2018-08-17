#!/usr/bin/env bash

if [ ! -f "$COMPOSE_DIR/.git/config" ]; then
    # install compose
    echo "\\compose\\ not found. Installing... "
    git clone --depth 1 -b $COMPOSE_VERSION $COMPOSE_URL $COMPOSE_DIR
fi

# change the ownership of the code
chown www-data:www-data -R $COMPOSE_DIR/public_html

# launch daemon process (inherited from the image arm32v7/php)
apache2-foreground
docker-php-entrypoint
