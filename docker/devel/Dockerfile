# Base image is nimmis/apache-php7 (specify tag 16.04 once available)
FROM afdaniele/compose:master_xenial_apache2_php7

#=======> DEVELOPMENT only tools

# install dependencies, then clean the apt cache
RUN apt-get update && apt-get install -y \
    php-xdebug \
    graphviz \
    doxygen \
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*

# configure xDebug
RUN echo $'xdebug.remote_enable = 1;
xdebug.remote_host = localhost;
xdebug.remote_port = 9000;
xdebug.profiler_enable = 1;
xdebug.profiler_output_name = xdebug.out.%t;
xdebug.profiler_output_dir = /tmp;
xdebug.profiler_enable_trigger = 1' > /etc/php/7.0/mods-available/xdebug.ini

# install WebGrind to visualize the profiler data
git clone https://github.com/jokkedk/webgrind.git /var/www/html/public_html/webgrind/

# copy APCu monitor script
ADD assets/apc.php /var/www/html/public_html/
