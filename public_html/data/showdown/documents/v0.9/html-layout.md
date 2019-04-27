# HTML Layout

**\\compose\\** seeks to facilitate the development of web applications
with particular emphasis on the deployment of new functionalities rather than
the development of fancy, highly-customizable graphics interfaces.
<!-- REF to Bootstrap v3.3.1 -->
For this reason, **\\compose\\** features a standardized graphic interface based
on [Bootstrap v3.3.1](https://getbootstrap.com/docs/3.3/getting-started/).


## Table of Contents

[toc]


## HTML Layout

The following image shows the layout of the HTML document provided by **\\compose\\**.

![center](images/html-layout/html-layout.svg =80%x100%)

A detailed description of all the parts identified in the image above follows.


## Nav Bar

The Navigation Bar (Nav Bar) provides an intuitive and easy way to navigate contents in **\\compose\\**.
It is a fixed header that remains visible at any time with the content of the page scrolling underneath
it. The following image shows the NavBar shown to a user with administration privileges.

![center](images/html-layout/navbar.jpg =90%x100%)

The navbar presents a customizable logo on the left-hand side and a menu on the right-hand side.
The menu contains a button for each page. Buttons in the Nav Bar are automatically added/removed
by **\\compose\\** when a page is installed/uninstalled (in general the package the page belongs to)
or when a page is enabled/disabled from the *Settings* page.

### Responsive behavior

There is no limit to the number of packages that you can install on the same instance of **\\compose\\**,
hence no limit to the number of pages. Nevertheless, the Nav Bar cannot accommodate more than a certain
number of pages that depends on the width of the browser viewport (i.e., the size of the browser window).

To overcome this limitation, **\\compose\\** employs a responsive Nav Bar, in which buttons will gradually
move from the main NavBar to a dropdown menu as the viewport gets smaller and smaller. The following
image shows an example of this behavior, with 2 buttons out of 5 already moved to the dropdown menu and
3 still on the default menu.

![center](images/html-layout/navbar-responsive.jpg =70%x100%)

The order with which buttons are moved from the main menu to the dropdown is defined by the parameter
`menu_entry > responsive > priority` defined in the metadata file of each page
(see [Page Metadata Requirements](standards#page-metadata-requirements) for further details).


## Page Container

The page container is a `<div>` element that hosts both *page alerts* and *page contents*.
It constraints the [Page Canvas](#page-canvas) to a width of `970px` that guarantees optimal
visibility on a broad variety of desktop and mobile devices.

It is possible to relax this constraint simply by adding the following CSS style snippet
to the source code of your pages.

```html
<style type="text/css">
body > #page_container{
    min-width: 100%;
}
</style>
```

Change the value of `min-width` above to any value you prefer.

WARN: Changing the width of the page container might have negative consequences on the user experience
on mobile devices. Use this option with caution.


### Alert container

<!-- REF to openAlert() function  -->
**\\compose\\** makes it easy for developers to show important messages to the user.
An alert can be created by calling the JavaScript function
[`openAlert(type, message)`](openAlert-documentation-link).
It is possible to create four different types of alerts, namely **Success** (`type='success'`),
**Information** (`type='info'`), **Warning** (`type='warning'`), and **Error** (`type='danger'`).
The argument `message` supports HTML code.
The image below shows one example for each type of alert, in the same order as listed above.

![center](images/html-layout/alerts-example.jpg =80%x100%)


### Page Canvas

The page canvas is a `<div>` element that hosts the content of the current page.
Let `PAGE_ROOT` be the main level of the folder containing the current page (e.g.,
`PAGE_ROOT=/system/packages/my_package/pages/my_page/`), the page canvas will
contain everything that is rendered by the file `PAGE_ROOT/index.php`.

Visit the document [Create new page](new-page) to learn more
about how to create a new page.


## Footer

**\\compose\\** features a fixed footer used to show information about current user,
current version of **\\compose\\**, and a dropdown menu with the pages currently installed.
The image below shows an example.

![center](images/html-layout/user-footer.jpg =70%x100%)

Guest users will see a different (simpler) footer.

![center](images/html-layout/guest-footer.jpg =70%x100%)


<!-- LINKS -->
[openAlert-documentation-link]: http://compose.afdaniele.com/documentation/compose_8js.html#a89e8e767b208961b3ab78f6d9c39355d
