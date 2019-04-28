# Packages

Functionalities in **\\compose\\** are provided by installable packages.
A package can add new

- Pages
- API End-points
- User types
- Configurable parameters
- Custom Javascript libraries
- Custom CSS Stylesheets
- Data (both public and private)

Remember to check out the list of
[Important directories and terminology](index#important-directories-and-terminology)
if you get confused while reading this documentation.


## Table of Contents

[toc]


## Packages setup

Installed packages are stored under `/system/packages/`.
Each package has a dedicated folder under `/system/packages/` that contains all the
files relative to that package.
An out-of-the-box instance of **\\compose\\** has only one folder under `/system/packages/`
and it is `core`, containing the `core` package.
For the remainder of this page we will refer to the directory containing a generic package
as `PACKAGE_ROOT` (e.g., `/system/packages/core/`).


### Package ID

Each package in **\\compose\\** is identified by a unique package ID.
The ID of a package is the name of the directory containing the package itself under
`/system/packages/`. For example, the package contained under `/system/packages/example/`
will have package ID `example`.


### PACKAGE_ROOT structure

The `PACKAGE_ROOT` directory of a package has the following structure:

```treeview
PACKAGE_ROOT&frasl;
├── configuration&frasl;
│   ├── metadata.json
│   └── configuration.json
├── js&frasl;
│   └── ...
├── css&frasl;
│   └── ...
├── data&frasl;
│   ├── public&frasl;
│   │   └── ...
│   └── private&frasl;
│       └── ...
├── modules&frasl;
│   └── ...
├── pages&frasl;
│   ├── <page_id>&frasl;
│   │   ├── metadata.json
│   │   ├── index.php
│   │   └── ...
│   └── ...
├── metadata.json
└── VERSION
```

### Minimal package

Not all the folders under `/system/packages/` will be considered as packages.
A **\\compose\\** package must have at least a metadata file `metadata.json`
in the main level of the package complying with the
[Package Metadata Requirements](standards#package-metadata-requirements).
In other words, **\\compose\\** will recognize `example` as a package
if the file `/system/packages/example/metadata.json` exists and obeys the
template defined by the [Package Metadata Requirements](standards#package-metadata-requirements)
document. Visit the page [Create new package](new-package)
to learn more about how to create a new package in **\\compose\\**.


## Pages

Packages in **\\compose\\** can define new pages.
A page in **\\compose\\** will be rendered in the [Page Canvas](html-layout#page-canvas)
and shown in the menu located in both the Nav Bar at the top (header) and the dropdown
menu at the bottom of the page (footer).

Package-specific pages are stored under `PACKAGE_ROOT/pages/`.
Each page in **\\compose\\** is identified by a page ID.
The ID of a page is the name of the directory containing the page itself under
`PACKAGE_ROOT/pages/`. For example, the page contained under
`PACKAGE_ROOT/pages/test_page/` will have page ID `test_page`.

Not all the folders under `PACKAGE_ROOT/pages/` will be considered as pages.
A **\\compose\\** page must have at least a metadata file `metadata.json`
in the main level of the page folder complying with the
[Page Metadata Requirements](standards#page-metadata-requirements).
In other words, **\\compose\\** will recognize `test_page` as a page
if the file `PACKAGE_ROOT/pages/test_page/metadata.json` exists and obeys the
template defined by the [Page Metadata Requirements](standards#page-metadata-requirements)
document. Visit the page [Create new page](new-page)
to learn more about how to create a new page in **\\compose\\**.


## API End-points

If you have not done it already, we suggest you to become familiar with
[RESTful API](https://restfulapi.net/)
and [Web API](https://en.wikipedia.org/wiki/Web_API) before reading this
section.

In **\\compose\\**, packages can define new API end-points.
An API end-point can be created just by adding 2 files to your package,
a metadata file describing the name and the type of each argument that your
end-point can receive from the user and an executor file, which is a PHP
file containing the function to call when a user calls the end-point.
Visit the page [Create new API end-point](FAKELINK#create-new-api-end-point)
if you want to learn more about how to create new API end-points in
**\\compose\\**.

New API end-points are stored in `PACKAGE_ROOT/modules/api/<api_version>/api-services/`,
to which we will refer as `API_SERVICES_ROOT` for the remainder of this page.
The directory `API_SERVICES_ROOT` contains two folders, namely `specifications` and
`executors`. At any time, there must be a complete 1-to-1 mapping between the
files in `specifications` and those in `executors`. If you want to define a new API service,
for example `new_service`, you need to create two files, namely
`API_SERVICES_ROOT/specifications/new_service.json` and
`API_SERVICES_ROOT/executors/new_service.php`.
Visit the page [Create new API end-point](FAKELINK#create-new-api-end-point)
if you want to learn more about the content of these files.


## User types

**\\compose\\** provides 4 user types available by default.
It is also possible to add package-specific user types. For example, if you
are creating a package for your company, you may want to create a user type
for each role in your company, so that your application can distinguish
between the "manager" and the "secretary" and show them different data.
Visit the page [Register new user type](FAKELINK#register-new-user-type)
if you want to learn more about how to register new user types in **\\compose\\**.


## Configurable parameters

**\\compose\\** provides an easy way to add configurable parameters to our
packages. Configurable parameters are defined in the file
`PACKAGE_ROOT/configuration/metadata.json`, their values are stored in
`PACKAGE_ROOT/configuration/configuration.json`.

The file `metadata.json` is created when the package is defined and
becomes part of the package source code. **\\compose\\** will only read
from this file, never write to it.
Conversely, the file `configuration.json` is created and maintained
by **\\compose\\**, it should never be included in the source code
of the package. The values stored in `configuration.json` are computed
by fusing default values from the file `metadata.json` and
and the custom values set using the page **Settings**.


## Custom Javascript libraries

Packages in **\\compose\\** can have their own JavaScript libraries.
JavaScript libraries must be stored in `PACKAGE_ROOT/js/`.

You can include a package-specific JavaScript file in your code by using the function
[getJSscriptURL()][getJSscriptURL-documentation-link] exported by
the `Core` class. This function returns a URL that you can use as the attribute `src` of
your `script` tag.

For example, you can include the JavaScript file `my_js_file.js` provided by the package
`my_package` by writing

```php
<script
    src="<?php echo \system\classes\Core::getJSscriptURL('my_js_file.js', 'my_package') ?>"
    type="text/javascript">
</script>
```


## Custom CSS Stylesheets

Packages in **\\compose\\** can have their own CSS stylesheets.
CSS stylesheets must be stored in `PACKAGE_ROOT/css/`.

You can include a package-specific CSS stylesheet in your code by using the function
[getCSSstylesheetURL()][getCSSstylesheetURL-documentation-link] exported by
the `Core` class. This function returns a URL that you can use as the attribute `href` of
your `link` tag.

For example, you can include the CSS stylesheet `my_style.css` provided by the package
`my_package` by writing

```php
<link
    href="<?php echo \system\classes\Core::getCSSstylesheetURL('my_style.css', 'my_package') ?>"
    rel="stylesheet"
>
```


## Data

A package can store two types of data:

- Public (downloadable)
- Private


### Public data

The public data of a package is stored under `PACKAGE_ROOT/data/public/`.

Let `my_package` be a package that contains the image `my_image.jpg` in its public
data directory (i.e., `PACKAGE_ROOT/data/public/my_image.jpg`).
This file will be accessible at the URL
`http://SERVER_HOSTNAME/data/my_package/my_image.jpg`.

NOTE: Neither **\\compose\\** nor the packages have access control over this data.
This means that everybody who has access to your application can download it.
Make sure you don't use it to store sensible information.


### Private data

The private data of a package is stored under `PACKAGE_ROOT/data/private/`.
This is usually sensible information and the public does not have
access to it. Packages have exclusive control over private data and usually
neither the administrator nor the user is asked to manually intervene on it.

**\\compose\\**, for example, uses this directory to store the database
of users.

If you are developing a package and need to store sensible data, you can
use this directory. **\\compose\\** provides a **Database** API that you
can use to read and write private data. Visit the page
[Database API](database-api) to learn more about it.

NOTE: Packages within the same instance of **\\compose\\** can access each
others private data.


[getJSscriptURL-documentation-link]: http://compose.afdaniele.com/documentation/classsystem_1_1classes_1_1_core.html#abf8818b9689322325d35a9a85debefda
[getCSSstylesheetURL-documentation-link]: http://compose.afdaniele.com/documentation/classsystem_1_1classes_1_1_core.html#aced2ad53122efd8874920fe01562557b

<!-- END -->
