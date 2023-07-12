ARG ARCH=amd64
ARG PHP_VERSION=8.1.1
ARG OS_DISTRO=bullseye
ARG COMPOSE_VERSION=stable

FROM ${ARCH}/php:${PHP_VERSION}-apache-${OS_DISTRO}

# recover arguments
ARG ARCH
ARG PHP_VERSION
ARG OS_DISTRO
ARG COMPOSE_VERSION

# configure environment: system & libraries
ENV ARCH=${ARCH} \
    PHP_VERSION=${PHP_VERSION} \
    OS_DISTRO=${OS_DISTRO} \
    QEMU_EXECVE=1

# configure environment: \compose\
ENV APP_DIR="/var/www"
ENV SSL_DIR="${APP_DIR}/ssl"
ENV COMPOSE_DIR="${APP_DIR}/html" \
    COMPOSE_URL="https://github.com/afdaniele/compose" \
    COMPOSE_USERDATA_DIR="/user-data" \
    HTTP_PORT=80 \
    HTTPS_PORT=443 \
    SSL_CERTFILE="${SSL_DIR}/certfile.pem" \
    SSL_KEYFILE="${SSL_DIR}/privkey.pem"

# copy QEMU
COPY ./assets/qemu/${ARCH}/ /usr/bin/

# install apt dependencies
COPY ./dependencies-apt.txt /tmp/dependencies-apt.txt
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    $(awk -F: '/^[^#]/ { print $1 }' /tmp/dependencies-apt.txt | uniq) \
  && rm -rf /var/lib/apt/lists/*

# install apcu
RUN pecl channel-update pecl.php.net \
  && pecl install apcu

# install composer
RUN cd /tmp/ && \
    wget https://getcomposer.org/installer && \
    php ./installer && \
    mv ./composer.phar /usr/local/bin/composer && \
    rm ./installer

# configure apcu
COPY assets/usr/local/etc/php/conf.d/apcu.ini /usr/local/etc/php/conf.d/

# configure PHP errors logging
COPY assets/usr/local/etc/php/conf.d/log_errors.ini /usr/local/etc/php/conf.d/

# remove pre-installed app
RUN rm -rf "${APP_DIR}" && \
    mkdir -p "${COMPOSE_DIR}" && \
    mkdir -p "${COMPOSE_USERDATA_DIR}" && \
    chown -R www-data:www-data "${APP_DIR}" && \
    chown -R www-data:www-data "${COMPOSE_USERDATA_DIR}"

# enable modules: rewrite, ssl
RUN a2enmod rewrite && \
    a2enmod ssl

# update website configuration file
COPY assets/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY assets/etc/apache2/sites-available/000-default-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf

# by default, we enable HTTP website and disable HTTPS
RUN a2ensite 000-default && \
    a2dissite 000-default-ssl

# switch to simple user
USER www-data

# install python library
RUN pip3 install compose-cms==1.0.5

# install \compose\
ADD --chown=www-data:www-data . "${COMPOSE_DIR}"

# fetch tags and checkout the wanted version
RUN git -C "${COMPOSE_DIR}" remote set-url origin "${COMPOSE_URL}" && \
    git -C "${COMPOSE_DIR}" fetch --tags && \
    git -C "${COMPOSE_DIR}" checkout "${COMPOSE_VERSION}"

# switch back to root
USER root

# configure entrypoint
COPY assets/entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# configure health check
HEALTHCHECK \
  --interval=30s \
  --timeout=8s \
  CMD \
    curl --fail "http://localhost:${HTTP_PORT}/script.php?script=healthcheck" > /dev/null 2>&1 \
    || \
    exit 1

# configure HTTP/HTTPS port
EXPOSE ${HTTP_PORT}/tcp
EXPOSE ${HTTPS_PORT}/tcp
