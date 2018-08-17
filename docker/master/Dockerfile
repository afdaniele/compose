# Base image is nimmis/apache-php7 (specify tag 16.04 once available)
FROM nimmis/apache-php7:latest

# constants
ENV COMPOSE_DIR /var/www/html
ENV COMPOSE_URL https://github.com/afdaniele/compose.git
ENV COMPOSE_PACKAGES_DIR "$COMPOSE_DIR/public_html/system/packages"
ENV COMPOSE_VERSION master

# update apt lists and install system libraries, then clean the apt cache
RUN apt-get update && apt-get install -y \
    apt-utils \
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*

# install dependencies, then clean the apt cache
RUN apt-get update && apt-get install -y \
    # system utilities (used by user)
    nano \
    # system utilities (used by compose)
    git \
    # php libraries
    php7.0-mbstring \
    php7.0-xml \
    php-apcu \
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*

# remove pre-installed app
RUN rm -rf $COMPOSE_DIR

# enable mod rewrite
RUN a2enmod rewrite

# update website configuration file
ADD assets/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# install \compose\
RUN git clone --depth 1 $COMPOSE_URL $COMPOSE_DIR

# configure entrypoint
ADD assets/entrypoint.sh /root/entrypoint.sh
RUN ["chmod", "+x", "/root/entrypoint.sh"]
ENTRYPOINT ["/root/entrypoint.sh"]

# configure HTTP port
EXPOSE 80/tcp
