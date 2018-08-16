# Base image is arm32v7/php:7.0.31-apache-stretch
FROM arm32v7/php:7.0.31-apache-stretch

# constants
ENV COMPOSE_DIR /var/www/html
ENV COMPOSE_URL https://github.com/afdaniele/compose.git
ENV COMPOSE_PACKAGES_DIR "$COMPOSE_DIR/public_html/system/packages"

# arguments
ARG VERSION
RUN test -n "$VERSION"
ENV COMPOSE_VERSION $VERSION

# update apt lists and install system libraries, then clean the apt cache
RUN apt-get update && apt-get install -y \
    apt-utils \
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*

# install dependencies, then clean the apt cache
RUN apt-get update && apt-get install -y \
    # system utilities (used by user)
    nano \
    python \
    # system utilities (used by compose)
    git \
    net-tools \
    # php libraries
    # <empty> \
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*

# remove pre-installed app
RUN rm -rf $COMPOSE_DIR
RUN rmdir $COMPOSE_DIR

# enable mod rewrite
RUN a2enmod rewrite

# update website configuration file
ADD assets/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# copy install script
ADD assets/install_compose.sh /root/install_compose.sh
RUN ["chmod", "+x", "/root/install_compose.sh"]

# configure entrypoint
ADD assets/entrypoint.sh /root/entrypoint.sh
RUN ["chmod", "+x", "/root/entrypoint.sh"]
ENTRYPOINT ["/root/entrypoint.sh"]
