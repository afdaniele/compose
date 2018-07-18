# Base image is afdaniele/compose:devel_xenial_apache2_php7
FROM afdaniele/compose:devel_xenial_apache2_php7

# install doxygen
RUN apt-get update && apt-get install -y \
    doxygen \
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*
