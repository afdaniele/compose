# \compose\ - A lightweight web-based CMS.

![](https://gyazo.com/eb5c5741b6a9a16c692170a41a49c858.png | height=22)

The \compose\ platform is written in PHP and provides functionalities
for fast-developing web-based applications on Linux servers.

Born to be modular, \compose\ is built around the concept of installable
packages. The built-in Core package is responsible for managing the 
third-party packages and allows us to install, remove, update, enable/disable
packages directly from the browser.

Features:
- Built-in support for different types of users;
- 4 types of users supported by default: `guest`, `user`, `supervisor`, `administrator`;
- Web-based package manager;
- Web-based pages manager;
- Web-based API manager;
- Built-in support for Google Sign-In OAuth 2.0 authentication;
- Built-in HTTP RESTful API;
- many more


## Core package

The Core package provides the following functionalities:
- PHP Framework for developing your own application (package)
- Packages management
- Pages management
- RESTful API service module
- API services management
- Users management
- Automatic documentation generation for open-source projects (optional)


## Packages

Functionalities in \compose\ are provided by installable packages.
Each package can add new pages to the platform, new Core functionalities,
new API services, its own configuration scheme.
All these functionalities are defined within a package in JSON files. 
Once a package is installed, all the new functionalities will be handled 
seamlessly by the Core module. The new pages will be instantly available,
the API services ready to be served, etc.

\compose\ is a powerful tool, so let's take our time and go through all 
its functionalities. Let's start by looking at the simplest configuration
of \compose\, where no packages are installed.

//TODO: show image here

// explain all the pages here and show them as well.

// valid name for a package contains only [a-z0-9]+


### Custom images

A package can contain additional images. Images within a container must be stored in
the folder `./images/`. An image introduced by a package is accessible via the URL
  `http://![your_website]/data/image.php?package=![package_name]&image=![filename_with_extension]`
  
 For example, if the package `server` exports the image `disk_full.png`, the URL of the image
 will be 
   `http://![your_website]/data/image.php?package=server&image=disk_full.png`
 




// Everything from here on has to be checked and updated


## Setup

### Fast setup

You can setup the platform using the script provided
by running

```bash
sh ./setup.sh
```

**Note:** The setup script needs sudo permissions to give
the ownership of certain configuration files to the Apache
server.


### Step-by-Step setup

- Give Apache write access to the configuration file
```bash
chmod 664 public_html/system/config/configuration.json
sudo chgrp www-data public_html/system/config/configuration.json
```


- Give Apache write access to the users files
```bash
chmod 664 public_html/system/users/*
sudo chgrp www-data public_html/system/users/*
```
