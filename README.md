# \\compose\\  -  A lightweight web-based CMS

**\\compose\\** is a CMS (Content Management System) platform written in PHP that
provides functionalities for fast-developing web applications on Linux servers.

Born to be modular, **\\compose\\** is built around the concept of installable
packages. The built-in Core package is responsible for managing the
third-party packages and allows us to install, remove, update, enable/disable
packages and single components such as API End-points or Pages directly from
the browser.

<!-- REF to Bootstrap v3.3.1 -->
Features:
- Built-in support for different types of users;
- 4 types of users supported by default: `guest`, `user`, `supervisor`, `administrator`;
- Web-based package manager;
- Web-based pages manager;
- Web-based RESTful API end-points manager;
- Built-in support for Google Sign-In OAuth 2.0 authentication;
- Built-in HTTP RESTful API service;
- Error handler;
- Graphics based on [Bootstrap (v3.3.1)](https://getbootstrap.com/docs/3.3/getting-started/);
- *and many many more...*


## Architecture

You can use **\\compose\\** out-of-the-box, though there is not much to see or
do until you create or install packages. **\\compose\\** is designed to be
modular with a built-in `core` package that provides all the functionalities needed for
managing additional packages and their components. A `Configuration` class provides
functionalities for managing settings for both the `core` package and other
additional packages.

The [RESTful](https://restfulapi.net/) API module in **\\compose\\** offers an easy
way for packages to export API end-points accessible via HTTP.


## Documentation

Check the [Full Documentation](http://compose.afdaniele.com/docs/latest/) if you want to
learn more about **\\compose\\**


## \\compose\\ in Docker

**\\compose\\** is also available as a Docker image.


### Build your own image using Dockerfile

You can download our Docker files from (here)[https://github.com/afdaniele/compose/tree/master/docker].
To build a Docker image from one of our Docker files run:

`docker build -t <image_name>:<image_tag> --build-arg VERSION=<github_compose_tag_name> .`

**NOTE:** `<github_compose_tag_name>` can be any (GitHub release)[https://github.com/afdaniele/compose/releases],
or `master` for the development version.


### Pre-built Docker images

You can find the list of pre-built images on
(Docker Hub)[https://cloud.docker.com/repository/docker/afdaniele/compose/tags].


### Run image with \\compose\\ outside the container (suggested)

Run

`docker run -itd -p <PORT>:80 -v <COMPOSE_ROOT_HOST_DIR>:/var/www/html afdaniele/compose:<IMAGE_TAG>`

**NOTE:** `<COMPOSE_ROOT_HOST_DIR>` is the path to the directory on the host that contains the root of the `\compose\` repository. This path should contain a `public_html` dir.


### Run image with \\compose\\ inside the container

**NOTE:** All the changes made in **\\compose\\** will be saved inside the container. If you delete the container without committing, all the changes will be lost.

Run

`docker run -d -p <PORT>:80 afdaniele/compose:<IMAGE_TAG>`
