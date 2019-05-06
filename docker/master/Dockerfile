# Base image is nimmis/apache-php7 (specify tag 16.04 once available)
FROM nimmis/apache-php7:latest

# constants
ENV APP_DIR /var/www
ENV COMPOSE_DIR ${APP_DIR}/html
ENV SSL_DIR ${APP_DIR}/ssl
ENV COMPOSE_URL https://github.com/afdaniele/compose.git
ENV COMPOSE_PACKAGES_DIR "$COMPOSE_DIR/public_html/system/packages"
ENV COMPOSE_VERSION master

# install dependencies, then clean the apt cache
RUN apt-get update \
  && apt-get install -y \
    # system utilities (used by user)
    nano \
    wget \
    python3 \
    # system utilities (used by compose)
    git \
    # php libraries
    php7.0-mbstring \
    php7.0-xml \
    php-apcu \
    # python libraries
    python3-requests \
    python3-toposort \
    # clean the apt cache
  && rm -rf /var/lib/apt/lists/*

# create container for SSL certificates
RUN mkdir -p $SSL_DIR

# remove pre-installed app
RUN rm -rf $COMPOSE_DIR

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
RUN git clone --depth 1 $COMPOSE_URL $COMPOSE_DIR

# configure entrypoint
ADD assets/entrypoint.sh /root/entrypoint.sh
ENTRYPOINT ["/root/entrypoint.sh"]

# configure HTTP port
EXPOSE 80/tcp
