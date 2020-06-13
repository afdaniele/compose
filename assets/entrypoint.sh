#!/usr/bin/env bash

# constants
DEFAULT_HTTP_PORT=80
DEFAULT_HTTPS_PORT=443

# set umask
umask 0002

# check volume
mountpoint -q "${COMPOSE_USERDATA_DIR}"
if [ $? -ne 0 ]; then
  echo "WARNING: The path '${COMPOSE_USERDATA_DIR}' is not a VOLUME. All the changes will be deleted with the container."
fi
COMPOSE_USERDATA_DIR=$(realpath -s "${COMPOSE_USERDATA_DIR}")

# from this point on, if something goes wrong, exit
set -e

# get GID of the compose dir
GID=$(stat -c %g "${COMPOSE_USERDATA_DIR}")
GNAME='compose'
# check if we have a group with that ID already
if [ ! "$(getent group "${GID}")" ]; then
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

# check if a custom HTTP_PORT was given
if [ "${HTTP_PORT}" != "${DEFAULT_HTTP_PORT}" ]; then
  echo "Configuring \\compose\\ to be served on custom HTTP port ${HTTP_PORT}."
  sed -i "s/*:${DEFAULT_HTTP_PORT}/*:${HTTP_PORT}/" /etc/apache2/sites-available/000-default.conf
  sed -i "s/Listen ${DEFAULT_HTTP_PORT}/Listen ${HTTP_PORT}/" /etc/apache2/ports.conf
fi

# check if a custom HTTPS_PORT was given
if [ "${HTTPS_PORT}" != "${DEFAULT_HTTPS_PORT}" ]; then
  echo "Configuring \\compose\\ to be served on custom HTTPS port ${HTTPS_PORT}."
  sed -i "s/*:${DEFAULT_HTTPS_PORT}/*:${HTTPS_PORT}/" /etc/apache2/sites-available/000-default-ssl.conf
  sed -i "s/Listen ${DEFAULT_HTTPS_PORT}/Listen ${HTTPS_PORT}/" /etc/apache2/ports.conf
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

# define termination function (triggered on docker stop)
compose_terminate() {
  # send SIGINT signal to monitored process
  kill -INT $(pgrep -P $$) 2> /dev/null
}

# register termination function against the signals SIGINT, SIGTERM, and SIGKILL
trap compose_terminate SIGINT
trap compose_terminate SIGTERM

# launch daemon process (inherited from the PHP image)
echo "Launching Apache..."
apache2-foreground &

# wait for the process to die or be killed
set +e
wait &> /dev/null
set -e

# done
echo "Bye bye!"
