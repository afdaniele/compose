# Settings

Once you completed the [First Setup](first-setup) of **\\compose\\**, you will be
able to access all the administration tools that **\\compose\\** has to offer.

The most important tool is the **Settings** page. It looks like the one shown in the
image below,

![center](images/settings/standard.jpg =80%x100%)

The settings are broken into *7* categories:
**General**,
**Packages**,
**Pages**,
**API End-points**,
**User roles**,
**Cache**,
**Codebase**.


# Table of Contents

[toc]


## Section: General

**General** is the section containing all the settings exported by the *core* package
of **\\compose\\**. This section shows all the parameters defined in the file
`/system/packages/core/configuration/metadata.json`.

These parameters include *Maintenance mode*, *Developer mode*, etc.


## Section: Packages

The **Packages** section contains the list of packages currently installed
in **\\compose\\**.

![center](images/settings/packages.jpg =80%x100%)

You can Enable/Disable packages from the **Packages** section.
When a package is disabled, all its pages are disabled as well.
The image above shows an instance of **\\compose\\** with *2* packages installed,
*github* and *portainer*.


## Section: Pages

The **Pages** section contains the list of pages currently installed
in **\\compose\\**.

![center](images/settings/pages.jpg =80%x100%)

You can Enable/Disable pages from the **Pages** section.
The image above shows the pages from the *core* package, plus pages from
two external packages *github* and *portainer*.


## Section: API End-points

The **API End-points** section contains the list of API End-points exported by the
packages currently installed in **\\compose\\**.
You can Enable/Disable API End-points from this section.

![center](images/settings/api_endpoints.jpg =80%x100%)


## Section: User roles

In **\\compose\\**, users are assigned roles. An administrator, for example, is a user
with the role *administrator* assigned.
Each package can declare (register) its own roles and assign them to the users.
**\\compose\\** needs to keep track of such roles for two reasons. First,
to decide to which page a user should be redirected when no page is requested.
Second, to control which user can access which pages.

Visit the page [Create new page](new-page) to learn more about how to use user
roles to define access privileges.

The **User roles** section shows the list of all user types (roles)
registered on **\\compose\\**. The following image shows an instance of
**\\compose\\** on which the package *duckietown* defines *4* custom
user roles.

![center](images/settings/user_roles.jpg =80%x100%)


## Section: Cache

This section is not always visible.
If you don't see it, it means that the cache is disabled.
You can enable it from the **General** section described above.

The **Cache** section shows the status of the cache, the Hits/Misses ratio,
number of entries, memory usage, and let us clear the cache manually.

![center](images/settings/cache.jpg =80%x100%)


## Section: Codebase

This section shows information about the current version of **\\compose\\**,
the origin of the source code, etc. The details contained in this section are
useful if you encounter a problem and you want to report it.


## Package-specific settings

Each package installed in **\\compose\\** that defines customizable parameters
will have its own section in the Settings page.

The image below shows an example of package-specific settings section for the
package `portainer`.

![center](images/settings/package_specific.jpg =80%x100%)

This is all done for us by **\\compose\\**, all we need to
do is define our parameters in the file `PACKAGE_ROOT/configuration/metadata.json`
of our package, and **\\compose\\** will take care of creating this settings
section, type-check the new values, store them into the corresponding `json`
file, etc.

Check out the sections
[Packages->Configurable parameters](packages#configurable-parameters)
and
[Create new package->Configurable parameters](new-package#configurable-parameters)
of the documentation to learn more about configurable parameters.
