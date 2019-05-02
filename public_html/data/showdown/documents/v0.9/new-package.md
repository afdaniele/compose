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

TODO: Available soon!
Linked from:<br/>- [settings#package-specific-settings](settings#package-specific-settings).


## Publish your package

Now that we have a new package, we want all our friends to be able to use it.
Packages in **\\compose\\** are shared using `git`. Each package goes in
a separate git repository. The package store in **\\compose\\** installs
new packages by cloning the corresponding repository in `PACKAGES_DIR`.

In order for your package to be accessible via the package store, your
package has to be **public** and hosted on a **public index**.<br/>
**\\compose\\** supports both [GitHub](https://www.github.com) and
[BitBucket](https://www.bitbucket.org).

Login on GitHub or BitBucket and create a public repository.

NOTE: It is not mandatory that the repository name follows a
specific naming convention, but we suggest using the format
**compose-pkg-PACKAGE**, where you replace **PACKAGE** with the
package ID.

In this example, we will assume that the repository name is
**compose-pkg-my\_package**, it is hosted on GitHub, and it is owned
by the user **my\_username**. The URL to your repository would in this case be
`https://github.com/my_username/compose-pkg-my_package`.

Open a terminal, move to the directory `PACKAGE_ROOT` of the package
that you want to publish (`PACKAGES_DIR/my_package` in this case), and run the
following commands,

```plain
git init
git remote add origin https://github.com/my_username/compose-pkg-my_package
git add ./*
git commit -m "first commit"
git push origin master
```

This will tell **git** that:
- this package is now a repository (`git init`);
- the remote repository is hosted at a certain URL (`git remote add origin ...`);
- every file in the package should be included in the repository (`git add/commit ...`);
- we want to transfer our files to the remote repository (`git push ...`);

Your package is now ready to be used by other people.<br/>But, how do we let people know
about this new awesome package? The answer is: **\\compose\\ Package Store**.

The package store is a tool used to discover and install packages that are
publicly available on the internet.
The package store gets the list of packages from a public registry hosted on GitHub
by the repository
[afdaniele/compose-assets-store](https://github.com/afdaniele/compose-assets-store/).

Now that we have a public package, we need to add it to the registry so that everybody
can find it in the package store. For security reasons, we don't let everybody
modify the registry, instead, we ask developers to submit a change (pull) request,
that we will approve and propagate to the public registry.

The procedure for doing this is quite simple, and it comprises of three steps.
1. we make a copy of the registry;
2. we add our new package to it;
3. we submit the new registry for approval;

### Step 1:

Go to
[https://github.com/afdaniele/compose-assets-store](https://github.com/afdaniele/compose-assets-store/)
and click on the
<span class="keystroke"> <i class="fa fa-code-fork" aria-hidden="true"></i> Fork</span>
button in the top-right corner of the page.

This will create a copy of the registry in your personal account.
Follow the instructions and you will be redirected to a page with a URL
that looks like the following.

```plain
https://github.com/USERNAME/compose-assets-store
```

where `USERNAME` will be replaced with your GitHub username.


### Step 2:

Now that we have our copy of the registry, we can add our package.
Open a terminal and run the following commands (remember to replace
`USERNAME` with your GitHub username),

```plain
cd ~/
git clone https://github.com/USERNAME/compose-assets-store
cd compose-assets-store/
```

Use your preferred text-editor to modify the file `./index` contained in
this directory.
Move to the bottom of the file and add the following block:

```yaml
  - id: PACKAGE_ID
    name: "PACKAGE_NAME"
    git_provider: GIT_PROVIDER
    git_owner: GIT_OWNER
    git_repository: compose-pkg-PACKAGE_ID
    git_branch: master
    icon: "images/_default.png"
    description: "PACKAGE_DESCRIPTION"
    dependencies: []
```

Replace the following placeholders in the block above with the right
information about your package.

- `PACKAGE_ID`: the ID of your package (e.g., `my_package`);
- `PACKAGE_NAME`: the name of your package as set in the file `PACKAGE_ROOT/metadata.json`;
- `GIT_PROVIDER`: this field can have two values, `github.com` or `bitbucket.org`.
  It indicates which websites hosts your package repository;
- `GIT_OWNER`: your username on GitHub or BitBucket;
- `PACKAGE_DESCRIPTION`: the description of your package as set in the
  file `PACKAGE_ROOT/metadata.json`;

Save the changes to the file `index` and run the following commands:

```plain
git commit -m "added new package" index
git push origin
```

### Step 3:

Now that we updated our copy of the registry, we can submit the change
for approval. Open the URL
[https://github.com/USERNAME/compose-assets-store](#),
click on the button that reads
<span class="keystroke"><i class="fa fa-code-fork" aria-hidden="true"></i> Pull Request</span>,
and follow the instructions to create a **Pull Request**.

We will review your Pull Request and apply your changes to the public registry
as soon as possible.
