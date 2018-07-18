# \\compose\\ in Docker

**\\compose\\** is also available as a Docker image.


## Build your own image using Dockerfile

You can download our Docker files from (here)[https://github.com/afdaniele/compose/tree/master/docker].


## Pre-built Docker images

These are the images that you can pull from your Docker console.

| Version   | Image tag                     | OS Host               | Web Server    | PHP   |
| ----------|-------------------------------|-----------------------|---------------|-------|
| v0.9      | afdaniele/compose:xenial0.9   | Ubuntu 16.04.4 LTS    | Apache 2      | 7.0   |


## Run image with \\compose\\ outside the container (suggested)

Run

`docker run -d -p <PORT>:80 -e "BASEURL=<URL>:<PORT>" -v <COMPOSE_ROOT_HOST>:/var/www/html/public_html afdaniele/<COMPOSE_IMAGE_ID>`

**NOTE:** If `<PORT>` is `80`, you can use `"BASEURL=<URL>"` instead of `"BASEURL=<URL>:<PORT>"`.


## Run image with \\compose\\ inside the container

**NOTE:** All the changes made in **\\compose\\** will be saved inside the container. If you delete the container without committing, all the changes will be lost.

Run

`docker run -d -p <PORT>:80 -e "BASEURL=<URL>:<PORT>" afdaniele/<COMPOSE_IMAGE_ID>`

**NOTE:** If `<PORT>` is `80`, you can use `"BASEURL=<URL>"` instead of `"BASEURL=<URL>:<PORT>"`.
