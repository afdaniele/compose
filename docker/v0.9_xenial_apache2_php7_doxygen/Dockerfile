# Base image is afdaniele/compose:v0.9_xenial_apache2_php7
FROM afdaniele/compose:v0.9_xenial_apache2_php7

# install doxygen
RUN apt-get update && apt-get install -y \
    doxygen \
    # clean the apt cache
    && rm -rf /var/lib/apt/lists/*
