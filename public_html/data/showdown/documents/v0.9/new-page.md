# Create new page

This page will guide you through how to create a new page in **\\compose\\**.

For the sake of ease, we will create a new page called `my_page` in the empty package
created in the section [Create new package](new-package) of the documentation.

NOTE: Page IDs in the same instance of **\\compose\\** must be unique.
This means that you cannot have two pages with the same IDs installed at the same
time. Make sure you chose descriptive names for your pages. A good name could
be `create-new-account`. A bad name could be `home` or `info`.


## Prerequisites

This section relies extensively on special directories and
terminology explained in the section
[Important directories and terminology](index#important-directories-and-terminology).
Please, take your time to check it out if you haven't done it already.
The most important ones are the following:
- `SERVER_HOSTNAME`: This is the hostname of the server hosting **\\compose\\** (or `localhost`);
- `PACKAGE_ROOT`: Defined as `PACKAGES_DIR/my_package/`;
- `PAGES_DIR`: Defined as `PACKAGE_ROOT/pages/`;
- `PAGE_ROOT`: Defined as `PAGES_DIR/my_page`;


## (Optional) Developer Package

Similarly to the tutorial [Create new package](new-package), you can skip
this section and use the **Developer** package that you can install from
the **Package Store** instead. The Developer package
can help you create new packages and pages from your browser.
Feel free to check it out.


## Where does my new page go?

Pages in **\\compose\\** are allowed to exist only enclosed by packages.
The package that contains a page is also the owner of that page.
This means that if you disable a package from the page **Settings** of
**\\compose\\**, all its pages will be disabled as well.

Your new page goes inside the directory `PAGES_DIR` of your package.
In this case, the directory would be `PACKAGE_ROOT/pages/my_page/`.
Open a terminal, move to `PACKAGE_ROOT` and run the following commands,

```plain
mkdir -p ./pages/my_page
cd ./pages/my_page
```

We just created the directory `PAGE_ROOT` for our new page `my_page`.
The command above also creates `PAGES_DIR` if it didn't exist.
This is still not a page for **\\compose\\**. In fact, we still need to
tell **\\compose\\** that this directory is in fact a new page.
We can do this by creating a new JSON file inside `PAGE_ROOT` called
`metadata.json`. Feel free to use any text-editor to do this. The minimal
page metadata file contains the following fields:

```json
{
  "name": "My Page",
  "description": "A test page",
  "menu_entry": {
    "order": -1,
    "icon": {
      "class": "fa",
      "name": "file"
    }
  },
  "access_level": [
    "administrator"
  ]
}
```

Similarly to a metadata file for a package, the fields `name` and
`description` in `metadata.json` contain the name and a description
of the page. The value you put in the field `name` will be used to
create a button in the navbar and another one in the footer menu of
**\\compose\\**.

### Menu Entry

The `menu_entry` block, describes how your page will be shown on the
navbar.

The field `order` indicates the position of the page in the
list of pages in the navbar. The order is from left to right, smaller
numbers first. In other words, the page with the smallest number in
the order field will be the left-most in the navbar.

The `icon` sub-block, tells **\\compose\\** which icon to use for
your page. The field `class` is used to select an icon library among
two supported choices:
`fa` (Font Awesome v4.7.0), or
`glyphicon` (Bootstrap Glyphicon v3.4).

If you select `fa`, you can pick an icon from
<a href="https://fontawesome.com/v4.7.0/icons/" target="\_blank">this page</a>.
The field `value` takes the name of the icon reported right next to the
icon.

If you select `glyphicon`, you can pick an icon from
<a href="https://getbootstrap.com/docs/3.4/components/#glyphicons" target="\_blank">this page</a>.
The field `value` in this case, takes the name of the icon that follows the
prefix `glyphicon-`. For example, for the icon `glyphicon glyphicon-user`, you will
set `value: "user"`.


### Access level

**\\compose\\** automatically hides pages from users who have not the rights
to access them. The way we tell **\\compose\\** who has access to what, is
through the `access_level` list.

**\\compose\\** comes with *4* user types built-in:
`guest`, `user`, `supervisor`, `administrator`.
Packages can define their own user types (also called user roles).
This list can contain built-in user types, like `administrator`,
or package-specific user roles.

Package-specific user roles are referenced as `PACKAGE:ROLE`.
For example, if the package `office` defines
a new user type `manager`, we can tell **\\compose\\** to give
all the managers access to our page by adding the string
`office:manager` to the `access_level` list.


## Test the new page

You can now open your browser and navigate to `http://SERVER_HOSTNAME/`.
Login if requested and you will notice that a new icon is now
present on the navbar. If everything goes as planned, it should look
like the following,

![center](images/new_page/navbar_icon.png =14%x100%)

You can now click on the icon, and you will see a completely empty
page. Do you see all that empty space? Well, that is your canvas now.
**\\compose\\** is designed to provide support to the developer
without interfering with their needs for space.

Let's put some content in that page.


## Page code

Now that we defined the aesthetics of our page, we need to define its content.
The content of a page is contained in a PHP file named `index.php`
located in the `PAGE_ROOT` directory.
Open a terminal, move to `PAGE_ROOT` (`PACKAGE_ROOT/pages/my_page/` in this case)
and run the following command,

```plain
echo "It works." >> index.php
```

You can now go back to the browser, and open your page `My Page` again
(or refresh it, if it is already open). You will see the text `It works.`
popping up on the top-left corner of the page canvas.

Congratulations, you just created your first page.
Feel free to create more pages, experiment with new icons, navbar order,
etc.
