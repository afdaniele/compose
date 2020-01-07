#!/usr/bin/env bash

# constants
ssldir_apache="/var/www/ssl-apache"

# set umask
umask 0002

# check volume
mountpoint -q ${COMPOSE_USERDATA_DIR}
if [ $? -ne 0 ]; then
  echo "WARNING: The path '${COMPOSE_USERDATA_DIR}' is not a VOLUME. All the changes will be deleted with the container."
fi
COMPOSE_USERDATA_DIR=$(realpath -s ${COMPOSE_USERDATA_DIR})

# from this point on, if something goes wrong, exit
set -e

# get GID of the compose dir
GID=$(stat -c %g ${COMPOSE_USERDATA_DIR})
GNAME=$(stat -c %G ${COMPOSE_USERDATA_DIR})
# check if we have a group with that ID already
if [ ! $(getent group ${GNAME}) ]; then
  # create group
  groupadd --gid ${GID} compose
  # add user www-data to group
  usermod -aG compose www-data
fi

# check if SSL is enabled and the keys are provided
if [ "${SSL}" == "1" ]; then
  echo "Enabling SSL..."
  # enable SSL website
  a2ensite 000-default-ssl
  # check files
  if [[ ! -e "${SSL_CERTFILE}" ]]; then
    echo "The certificate file [${SSL_CERTFILE}] is invalid or it is a broken symlink."
    echo "Exiting..."
    exit 1
  fi
  if [[ ! -e "${SSL_KEYFILE}" ]]; then
    echo "The certificate key [${SSL_KEYFILE}] is invalid or it is a broken symlink."
    echo "Exiting..."
    exit 2
  fi
  echo "Done!"
fi

# launch daemon process (inherited from the image arm32v7/php)
echo "Launching Apache..."
apache2-foreground
echo "Launching PHP..."
docker-php-entrypoint
echo "Bye bye!"
