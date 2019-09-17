#!/usr/bin/env bash

# check volume
mountpoint -q $COMPOSE_DIR
if [ $? -ne 0 ]; then
    echo "WARNING: The path '$COMPOSE_DIR' is not a VOLUME. All the changes will be deleted with the container."
fi

# change the ownership of the code
chown www-data:www-data -R $COMPOSE_DIR/public_html

# launch daemon process (inherited from the image arm32v7/php)
apache2-foreground
docker-php-entrypoint
