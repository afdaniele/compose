#!/usr/bin/env bash

if [ ! -f "$COMPOSE_DIR/.git/config" ]; then
    # install compose
    echo "\\compose\\ not found. Installing... "
    git clone --depth 1 -b $COMPOSE_VERSION $COMPOSE_URL $COMPOSE_DIR
    # change the ownership of the code
    chown www-data:www-data -R $COMPOSE_DIR/public_html
else
    # update compose
    echo "\\compose\\ found. Updating... "
    git -C $COMPOSE_DIR pull origin $COMPOSE_VERSION
fi
