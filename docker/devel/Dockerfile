# Base image
FROM afdaniele/compose:latest

#=======> DEVELOPMENT only tools

# install dependencies, then clean the apt cache
RUN apt-get update && apt-get install -y \
    php-xdebug \
    graphviz \
    doxygen \
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*

# configure xDebug
RUN echo $'xdebug.remote_enable = 1; \
    xdebug.remote_host = localhost; \
    xdebug.remote_port = 9000; \
    xdebug.profiler_enable = 1; \
    xdebug.profiler_output_name = xdebug.out.%t; \
    xdebug.profiler_output_dir = /tmp; \
    xdebug.profiler_enable_trigger = 1' > /etc/php/7.0/mods-available/xdebug.ini

# install WebGrind to visualize the profiler data
RUN git clone https://github.com/jokkedk/webgrind.git $COMPOSE_DIR/public_html/webgrind/

# copy phpinfo script
ADD assets/phpinfo.php $COMPOSE_DIR/public_html/

# copy APCu monitor script
ADD assets/apc.php $COMPOSE_DIR/public_html/
