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
GNAME='compose'
# check if we have a group with that ID already
if [ ! $(getent group ${GID}) ]; then
  echo "Creating a group 'compose' with GID:${GID} for the user www-data"
  # create group
  groupadd --gid ${GID} ${GNAME}
else
  GROUP_STR=$(getent group ${GID})
  readarray -d : -t strarr <<< "$GROUP_STR"
  GNAME="${strarr[0]}"
  echo "A group with GID:${GID} (i.e., ${GNAME}) already exists. Reusing it."
fi

# add user www-data to group
echo "Adding user www-data to the group ${GNAME} (GID:${GID})."
usermod -aG ${GNAME} www-data

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


# define termination function (triggered on docker stop)
compose_terminate() {
  # send SIGINT signal to monitored process
  kill -INT `pgrep -P $$` 2> /dev/null
}

# register termination function against the signals SIGINT, SIGTERM, and SIGKILL
trap compose_terminate SIGINT
trap compose_terminate SIGTERM
trap compose_terminate SIGKILL

# launch daemon process (inherited from the image arm32v7/php)
echo "Launching Apache..."
apache2-foreground &

# wait for the process to die or be killed
set +e
wait &> /dev/null
set -e

# done
echo "Bye bye!"
