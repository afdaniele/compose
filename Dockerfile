ARG ARCH=amd64
ARG PHP_VERSION=7.0
ARG OS_FAMILY=ubuntu
ARG OS_DISTRO=jammy
ARG COMPOSE_VERSION=stable
ARG GIT_REF=heads

FROM ${ARCH}/${OS_FAMILY}:${OS_DISTRO}

# recover arguments
ARG ARCH
ARG PHP_VERSION
ARG OS_DISTRO
ARG GIT_REF
ARG GIT_SHA
ARG COMPOSE_VERSION

# configure environment: system & libraries
ENV ARCH=${ARCH} \
    PHP_VERSION=${PHP_VERSION} \
    OS_DISTRO=${OS_DISTRO}

# configure environment: \compose\
ENV APP_DIR="/var/www"
ENV SSL_DIR="${APP_DIR}/ssl"
ENV COMPOSE_DIR="${APP_DIR}/html" \
    COMPOSE_URL="https://github.com/afdaniele/compose.git" \
    COMPOSE_USERDATA_DIR="/user-data" \
    HTTP_PORT=80 \
    HTTPS_PORT=443 \
    SSL_CERTFILE="${SSL_DIR}/certfile.pem" \
    SSL_KEYFILE="${SSL_DIR}/privkey.pem" \
    QEMU_EXECVE=1 \
    DEBIAN_FRONTEND=noninteractive

# copy QEMU
COPY ./assets/qemu/${ARCH}/ /usr/bin/

# install apt dependencies
COPY ./dependencies-apt.txt /tmp/dependencies-apt.txt
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    $(awk -F: '/^[^#]/ { print $1 }' /tmp/dependencies-apt.txt | uniq) \
  && rm -rf /var/lib/apt/lists/*

# PHP modules
RUN add-apt-repository -y ppa:ondrej/php && \
    add-apt-repository -y ppa:nginx/stable && \
    apt-get install --no-install-recommends --yes \
        nginx \
        php7.0-apcu \
        php7.0-cli \
        php7.0-fpm \
        php7.0-mysql \
        php7.0-curl \
        php7.0-memcached \
        php7.0-gd \
        php7.0-mcrypt \
        php7.0-tidy \
        php7.0-bcmath \
        php7.0-zip \
        php7.0-xml \
        php7.0-soap \
        php7.0-mbstring

# configure php-fpm
RUN sed -i 's/\;date\.timezone\ =/date\.timezone\ =\ America\/New_York/g' /etc/php/7.0/fpm/php.ini && \
    sed -i 's/\;error_log\ =\ syslog/error_log\ =\ syslog/g' /etc/php/7.0/fpm/php.ini && \
    sed -i 's/\;clear_env\ =\ no/clear_env\ =\ no/g' /etc/php/7.0/fpm/pool.d/www.conf

# install python dependencies
RUN pip3 install \
  run-and-retry \
  compose-cms>=1.0.5

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
    mkdir -p "${COMPOSE_DIR}" "${COMPOSE_USERDATA_DIR}" && \
    chown -R www-data:www-data "${APP_DIR}" "${COMPOSE_USERDATA_DIR}"

# copy nginx configuration file
ADD assets/etc/nginx/default /etc/nginx/sites-available/default

# copy SHA of the current commit. This has two effects:
# - stores the SHA of the commit from which the image was built
# - correct the issue with docker cache due to git clone command below
RUN echo "${GIT_SHA}" >> /compose.builder.version.sha
ENV COMPOSE_VERSION=${COMPOSE_VERSION} \
    COMPOSE_GIT_SHA=${GIT_SHA}

# switch to simple user
USER www-data

# install \compose\
RUN rretry \
  --min 40 \
  --max 120 \
  --tries 3 \
  --on-retry "rm -rf ${COMPOSE_DIR}" \
  --verbose \
  -- \
    git clone -b stable "${COMPOSE_URL}" "${COMPOSE_DIR}"

# fetch tags and checkout the wanted version
RUN git -C "${COMPOSE_DIR}" fetch --tags && \
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
