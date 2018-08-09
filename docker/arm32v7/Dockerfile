# Base image is arm32v7/php:7.0.31-apache-stretch
FROM arm32v7/php:7.0.31-apache-stretch

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
    # php libraries
    # <empty> \
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*

# set default COMPOSE_BASE_URL
ENV BASEURL="localhost:9090"

# remove pre-installed app
RUN rm -rf /var/www/html

# enable mod rewrite
RUN a2enmod rewrite

# update website configuration file
ADD assets/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# add symlink to public_html in home directory for convenience
RUN ln -s /var/www/html/public_html /root/public_html

# configure entrypoint
ADD assets/entrypoint.sh /root/entrypoint.sh
RUN ["chmod", "+x", "/root/entrypoint.sh"]

# install compose
RUN git clone --depth 1 -b $COMPOSE_VERSION https://github.com/afdaniele/compose.git /var/www/html

# create configuration file
RUN cp /var/www/html/public_html/system/config/configuration.default.php /var/www/html/public_html/system/config/configuration.php

# create a volume for compose
VOLUME /var/www/html

# configure CMD and ENTRYPOINT
ENTRYPOINT ["/root/entrypoint.sh"]
