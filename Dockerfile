ARG ARCH=amd64
ARG PHP_VERSION=7.0.31
ARG OS_DISTRO=stretch

FROM ${ARCH}/php:${PHP_VERSION}-apache-${OS_DISTRO}

# recover arguments
ARG ARCH
ARG PHP_VERSION
ARG OS_DISTRO

# configure environment: system & libraries
ENV ARCH=${ARCH}
ENV PHP_VERSION=${PHP_VERSION}
ENV OS_DISTRO=${OS_DISTRO}

# configure environment: \compose\
ENV APP_DIR "/var/www"
ENV COMPOSE_DIR "${APP_DIR}/html"
ENV COMPOSE_URL "https://github.com/afdaniele/compose.git"
ENV COMPOSE_PACKAGES_DIR "${COMPOSE_DIR}/public_html/system/packages"
ENV COMPOSE_VERSION "master"
ENV COMPOSE_HTTP_PORT 80
ENV COMPOSE_HTTPS_PORT 443
ENV SSL_DIR "${APP_DIR}/ssl"
ENV SSL_CERTFILE "${SSL_DIR}/certfile.pem"
ENV SSL_KEYFILE "${SSL_DIR}/privkey.pem"

# install dependencies, then clean the apt cache
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    # system utilities (used by user)
    nano \
    wget \
    python3 \
    # system utilities (used by compose)
    git \
    net-tools \
    # python libraries
    python3-requests \
    python3-toposort \
    python3-pip \
    # php libraries
    # <empty> \
  # clean the apt cache
  && rm -rf /var/lib/apt/lists/*

# remove pre-installed app
RUN rm -rf "${COMPOSE_DIR}"

# enable mod rewrite
RUN a2enmod rewrite

# enable mod ssl
RUN a2enmod ssl

# update website configuration file
ADD assets/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
ADD assets/etc/apache2/sites-available/000-default-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf

# enable HTTP website
RUN a2ensite 000-default

# disable (default) HTTPS website
RUN a2dissite 000-default-ssl

# install \compose\
RUN git clone "${COMPOSE_URL}" "${COMPOSE_DIR}"

# configure entrypoint
ADD assets/entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# configure health check
HEALTHCHECK \
  --interval=10s \
  --timeout=8s \
  CMD \
    curl --fail 'http://localhost/script.php?script=healthcheck' \
    || \
    exit 1

# configure HTTP/HTTPS port
EXPOSE ${COMPOSE_HTTP_PORT}/tcp
EXPOSE ${COMPOSE_HTTPS_PORT}/tcp