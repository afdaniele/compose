# Base image is nimmis/apache-php7 (specify tag 16.04 once available)
FROM nimmis/apache-php7:latest

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
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*

# set default COMPOSE_BASE_URL
ENV BASEURL="localhost:8080"

# replace default app with compose
RUN rm -rf /var/www/html
RUN git clone --depth 1 -b v0.9 https://github.com/afdaniele/compose.git /var/www/html

# create configuration file
RUN cp /var/www/html/public_html/system/config/configuration.default.php /var/www/html/public_html/system/config/configuration.php

# enable mod rewrite
RUN a2enmod rewrite

# update website configuration file
ADD assets/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# add symlink to public_html in home directory for convenience
RUN ln -s /var/www/html/public_html /root/public_html

# configure entrypoint
ADD assets/entrypoint.sh /root/entrypoint.sh
RUN ["chmod", "+x", "/root/entrypoint.sh"]
ENTRYPOINT ["/root/entrypoint.sh"]
