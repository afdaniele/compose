# **\\compose\\**  -  A lightweight web-based CMS

**\\compose\\** is a CMS (Content Management System) platform written in PHP that
provides functionalities for the fast development of web applications on Linux
servers.


## Table of Contents

- [Home](index)
    - [Introduction](#introduction)
    - [Architecture](#architecture)
    - [Important directories and terminology](#important-directories-and-terminology)

- Get Started
    - Installation
        - [Install using Docker (recommended)](setup-docker)
        - [Install natively](setup)
    - [First setup](first-setup)
    - [Developer Mode](developer-mode)
    - [Package Store](package-store)
    - [Packages](packages)
    - [Create new package](new-package)
    - [Create new page](new-page)

- Beginners
    - [URL structure](url-structure)
    - [HTML Layout](html-layout)
    - [Settings](settings)
    - [Code flow](code-flow)
    - [Core package](FAKELINK#core-package)
    - [Users](FAKELINK#users)
    - [Install packages](FAKELINK#install-packages)

- [Libraries](libraries)
    - [Javascript libraries](libraries#javascript-libraries)
    - [CSS libraries](libraries#css-libraries)

- Intermediate
    - [Create new API end-point](FAKELINK#create-new-api-end-point)
    - [Package configuration](FAKELINK#package-configuration)
    - [Show alert](FAKELINK#show-alert)
    - [Show error page](FAKELINK#show-error-page)
    - [Database API](FAKELINK#database-api)

- Advanced
    - [URL Rewrite](url-rewrite)
    - [Register new user type](FAKELINK#register-new-user-type)
    - [Plugin Renderers](FAKELINK#plugin-renderers)

- [Standards](standards)
    - [Package Metadata Requirements](standards#package-metadata-requirements)
    - [Page Metadata Requirements](standards#page-metadata-requirements)
    - [Configuration Metadata Requirements](standards#configuration-metadata-requirements)

- Code Reference (PHP Classes)
    - [\system\classes\Core](classsystem_1_1classes_1_1_core)
    - [\system\classes\Configuration](classsystem_1_1classes_1_1_configuration)
    - [\system\classes\EditableConfiguration](classsystem_1_1classes_1_1_editable_configuration)
    - [\system\classes\Database](classsystem_1_1classes_1_1_database)

- [Troubleshooting](troubleshooting)


## Introduction

Born to be modular, **\\compose\\** is built around the concept of installable
packages. The built-in Core package is responsible for managing the
third-party packages and allows us to install, remove, update, enable/disable
packages and single components such as API End-points or Pages directly from
the browser.

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

The following image shows a block-diagram of the architecture of **\\compose\\**.

![center](images/compose-block-diagram.svg =50%x100%)

You can use **\\compose\\** out-of-the-box, though there is not much to see or
do until you create or install packages. **\\compose\\** is designed to be
modular with a built-in `core` package that provides all the functionalities needed for
managing additional packages and their components. Visit the page
[Core API Documentation](FAKELINK#core-api-documentation) to learn about the built-in `Core`
class and its functionalities.

The `Configuration` class provides comes with the `core` package as well and provides
functionalities for managing settings for both the `core` package and other
additional packages. Visit the page [Package configuration](FAKELINK#package-configuration)
if you want to learn more about how **\\compose\\** handles package-specific
configuration files.

The [RESTful](https://restfulapi.net/) API module in **\\compose\\** offers an easy
way for packages to export API end-points accessible via HTTP.
Visit the page [Create new API end-point](FAKELINK#create-new-api-end-point) to learn more about
how **\\compose\\** handles package-specific API end-points.


## Important directories and terminology

The image below shows the path to the source code of a page with ID `my_page`,
contained in a package with ID `my_package`, within an instance of **\\compose\\**
installed in `/var/www/html/`.

![center](images/directories.svg =90%x100%)

We use the following placeholders throughout the documentation to indicate
specific paths in your machine.
Some of them will not make sense to you right now. Just keep in mind that
you can always come back here and check them out if you get confused while
reading through the documentation.

- `COMPOSE_ROOT`: The absolute path to the directory containing the source code
of **\\compose\\** (e.g., `/var/www/html/`). This path must contain the folder
`public_html` that comes with **\\compose\\**.
- `PACKAGES_DIR`: Defined as `COMPOSE_ROOT/public_html/system/packages/`.
This is the directory that contains all the installed packages.
- `PACKAGE_ROOT`: This is the directory containing the source code of a single package.
This directory is relative to a specific package, and it is usually clear from the
context which package we are referring to. The `PACKAGE_ROOT` of the package
`my_package` is `PACKAGES_DIR/my_package/`.
- `PAGES_DIR`: Defined as `PACKAGE_ROOT/pages/`.
This is the directory inside a `PACKAGE_ROOT` that contains all the
pages of a package. Similarly to `PACKAGE_ROOT`, it makes sense to talk about
`PAGES_DIR` only in the context of a given package.
- `PAGE_ROOT`: This is the directory containing the source code of a single page.
This directory is relative to a specific package and page, and it is usually
clear from the context which package and page we are referring to.
The `PAGE_ROOT` of the page `my_page` in the package `my_package` is
`PAGES_DIR/my_page/` (or equivalently,
`PACKAGE_ROOT/pages/my_page/`, `PACKAGES_DIR/my_package/pages/my_page/`).
