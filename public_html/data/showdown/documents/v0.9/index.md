# **\\compose\\**  -  A lightweight web-based CMS

**\\compose\\** is a CMS (Content Management System) platform written in PHP that
provides functionalities for fast-developing web applications on Linux servers.


## Table of Contents

- [Home](index)
    - [Introduction](#introduction)
    - [Architecture](#architecture)

- [Install using Docker (recommended)](setup-docker)

- [Install natively](setup)
    - [Prepare environment](setup#prepare-environment)
    - [Install \\compose\\](setup#install-compose)
    - [Test](setup#test)
    - [Configure](setup#configure)

- [First setup](first-setup)

- [Libraries](libraries)
    - [Javascript libraries](libraries#javascript-libraries)
    - [CSS libraries](libraries#css-libraries)

- Beginners
    - [URL structure](url-structure)
    - [HTML Layout](html-layout)
    - [Settings](settings)
    - [Code flow](FAKELINK#code-flow)
    - [Packages](packages)
    - [Pages](FAKELINK#pages)
    - [Core package](FAKELINK#core-package)
    - [Users](FAKELINK#users)
    - [Install packages](FAKELINK#install-packages)

- Intermediate
    - [Create new package](FAKELINK#create-new-package)
    - [Create new page](FAKELINK#create-new-page)
    - [Create new API end-point](FAKELINK#create-new-api-end-point)
    - [Package configuration](FAKELINK#package-configuration)
    - [Show alert](FAKELINK#show-alert)
    - [Show error page](FAKELINK#show-error-page)

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

- [Troubleshooting](troubleshooting)


## Introduction

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
