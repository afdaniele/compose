#!/usr/bin/env bash

# constants
ssldir_apache="/var/www/ssl-apache"

# check volume
mountpoint -q ${COMPOSE_DIR}
if [ $? -ne 0 ]; then
  echo "WARNING: The path '${COMPOSE_DIR}' is not a VOLUME. All the changes will be deleted with the container."
fi
COMPOSE_DIR=$(realpath -s ${COMPOSE_DIR})

# from this point on, if something goes wrong, exit
set -e

# change the ownership of the code
echo "Giving ownership of the code to the user 'www-data'..."
chown www-data:www-data -R ${COMPOSE_DIR}/public_html
echo "Done!"

# check if SSL is enabled and the keys are provided
if [ "${SSL}" == "1" ]; then
  echo "Enabling SSL..."
  # create directory for apache to get the ssl files
  mkdir -p "${ssldir_apache}"
  # enable SSL website
  a2ensite 000-default-ssl
  # check if SSL_CERTFILE was passed
  if [ -z ${SSL_CERTFILE+x} ]; then
    echo "The environment variable [SSL_CERTFILE] is required"
    echo "Exiting..."
    exit 1
  fi
  # check if SSL_KEYFILE was passed
  if [ -z ${SSL_KEYFILE+x} ]; then
    echo "The environment variable [SSL_KEYFILE] is required"
    echo "Exiting..."
    exit 2
  fi
  # check values
  if [[ ! -e "${SSL_CERTFILE}" ]]; then
    echo "The certificate file [${SSL_CERTFILE}] is invalid or it is a broken symlink."
    echo "Exiting..."
    exit 3
  fi
  if [[ ! -e "${SSL_KEYFILE}" ]]; then
    echo "The certificate key [${SSL_KEYFILE}] is invalid or it is a broken symlink."
    echo "Exiting..."
    exit 4
  fi
  # create symlink for apache
  _SSL_CERTFILE=`readlink -f "${SSL_CERTFILE}"`
  _SSL_KEYFILE=`readlink -f "${SSL_KEYFILE}"`
  ln -s ${_SSL_CERTFILE} ${ssldir_apache}/fullchain.pem
  ln -s ${_SSL_KEYFILE} ${ssldir_apache}/privkey.pem
  echo "Done!"
fi

# launch daemon process (inherited from the image nimmis/apache-php7)
echo "Launching Apache..."
/my_init
echo "Bye bye!"
