# Create new package

This page will guide you through how to create a new package in **\\compose\\**.

For the sake of ease, we will create a new package called `my_package` in this
section. We will use this name multiple times, but it is not a reserved name,
feel free to replace it with whatever you prefer. Also, remember that **\\compose\\**
comes with only one package pre-installed called `core`. Therefore, you cannot
use this name.


## Prerequisites

It is important that you are familiar with the concepts explained in the
section [Packages](packages) of the documentation. In particular, it is
important to be familiar with the structure of a package, as explained
in [Packages->PACKAGE_ROOT structure](packages#package_root-structure).

Also, this section relies extensively on special directories and
terminology explained in the section
[Important directories and terminology](index#important-directories-and-terminology).
Please, take your time to check it out if you haven't done it already.
The most important ones for this tutorial are the following:
- `SERVER_HOSTNAME`: This is the hostname of the server hosting **\\compose\\** (or `localhost`);
- `COMPOSE_ROOT`: Directory where **\\compose\\** is installed;
- `PACKAGES_DIR`: Defined as `COMPOSE_ROOT/public_html/system/packages/`;
- `PACKAGE_ROOT`: Defined as `PACKAGES_DIR/my_package/`;


## (Optional) Developer Package

One of the main goals of **\\compose\\** is that of being a user-friendly
CMS in the sense that everybody who develops with it, always knows what is
happening under the hood.
In this spirit, we are not going to tell you to copy a bunch of files in a new
place and some magic will happen. Instead, we will guide you through the
creation of each single file you need to define a new package, and in the process
we will explain why we designed it to be that way.

If you don't feel like learning new stuff or you just want to skip ahead, there
is a **Developer** package on the **Package Store** that you can install, which
will help you create new packages and pages from your browser.
Feel free to check it out.


## Where does my new package go?

In order to create a new package `my_package`, we need to create its directory
inside `PACKAGES_DIR`.<br/>
Open a terminal, move to `PACKAGES_DIR` and run the following commands,

```plain
mkdir my_package
cd my_package
```

We just created the directory `PACKAGE_ROOT` for our new package `my_package`.
This is still not a package for **\\compose\\**. In fact, we still need to
tell **\\compose\\** that this directory is in fact a new package.
We can do this by creating a new JSON file inside `PACKAGE_ROOT` called
`metadata.json`. Feel free to use any text-editor to do this. The minimal
package metadata file contains the following fields:

<pre>
{
  "name": "My Package",
  "description": "A test package"
}
</pre>

It is very important to distinguish between the ID of a package and its name.
The name of the directory containing the package defines its **ID**
(`my_package` in this case),
the value of the field `name` in the file `metadata.json`
(*My Package* in this case) defines its **Name**.

Packages in **\\compose\\** are always referenced by their IDs, never by their
names. The name of a package can change overnight and everything is expected
to keep working fine, its ID must remain unchanged.

You can now copy the text from the snippet above into your newly created
`metadata.json` file.


## Test the new package

You can now open your browser and navigate to `http://SERVER_HOSTNAME/`.
Login if requested and go to the **Settings** page, section **Packages**.
You will see your new package in the list.
If you don't see it, make sure that the cache is disabled by opening
the tab **General** in the **Settings** page. Remember to keep the cache
disabled during development.

If all you want to do next is create a page for your new package, you can
skip to the next section [Create new page](new-page).

Keep reading this page if you want to learn more about what you can do
with your new package in **\\compose\\**.


## Metadata file

We saw above that we need two fields in the metadata file to define a new package.
Even though the minimal configuration for a package is small, the metadata file
supports the definition of quite a few parameters. We will see them in the next
sections.

(Optional): Check out the page
[Package Metadata Requirements](standards#package-metadata-requirements)
to learn more about the package metadata files.


## Configurable parameters

TODO: linked from [settings#package-specific-settings](settings#package-specific-settings).


## Publish your package

TODO: Available soon!
