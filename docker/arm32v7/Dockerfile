# Base image is arm32v7/php:7.0.31-apache-stretch
FROM arm32v7/php:7.0.31-apache-stretch

# install qemu
ENV QEMU_EXECVE 1
COPY assets/qemu/* /usr/bin/

# constants
ENV COMPOSE_DIR /var/www/html
ENV COMPOSE_URL https://github.com/afdaniele/compose.git
ENV COMPOSE_PACKAGES_DIR "$COMPOSE_DIR/public_html/system/packages"
ENV COMPOSE_VERSION master

# enable ARM
RUN [ "cross-build-start" ]

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

# enable mod rewrite
RUN cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/

# update website configuration file
ADD assets/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# install \compose\
RUN git clone --depth 1 $COMPOSE_URL $COMPOSE_DIR

# copy entrypoint
ADD assets/entrypoint.sh /root/entrypoint.sh
RUN chmod +x /root/entrypoint.sh

# disable ARM
RUN [ "cross-build-end" ]

# configure entrypoint
ENTRYPOINT ["/root/entrypoint.sh"]

# configure HTTP port
EXPOSE 80/tcp
