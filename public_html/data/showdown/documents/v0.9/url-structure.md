# URL Structure

URLs in **\\compose\\** have the following structure:

```http
http(s):// <BASE> / <PAGE> / <ACTION> / <ARG1> / <ARG2> ? <QUERY_STRING>
```

For example, the URL

```http
http://compose.afdaniele.com/docs/latest/url-structure
```

has

- **BASE** = compose.afdaniele.com
- **PAGE** = docs
- **ACTION** = latest
- **ARG1** = url-structure
- **ARG2** = NONE
- **QUERY_STRING** = NONE

A detailed description of each part of the URL follows.


## Table of Contents

[toc]


## Base

The **BASE** of the URL is **mandatory** and contains the hostname of the server hosting **\\compose\\**.
Its value is set in the file `/system/config/configuration.php` as `$BASE_URL`.
The value of **BASE** can be retrieved by accessing the parameter `Configuration::$BASE`.

WARN: The value of `$BASE_URL` in `/system/config/configuration.php` must end with a slash symbol `/`.


## Page

The second argument of the URL selects the **PAGE**. Its value must match the page ID
of one of the pages installed on **\\compose\\**.
The value of **PAGE** can be retrieved by accessing the parameter `Configuration::$PAGE`.
**PAGE** is optional; when it is not provided or it does not match any known page ID,
the user is redirected to a default page.

The ID of all the pages installed can be found in the section *Pages*, page
*Settings* of your installation of **\\compose\\**. You can find an example of list
in the section [Settings->Pages](settings#pages).

NOTE: Further details about the page ID will be
provided later in the documentation (section [Packages->Pages](packages#pages)).

NOTE: Default pages will be discussed later in
the documentation (section [Settings->Default pages](settings#default-pages)).


## Action

The third argument of the URL identifies an **ACTION** to execute on the page.
Unlike **PAGE**, **\\compose\\** does not actively validates the value of **ACTION**. This means that
it is responsibility of the selected page to ignore invalid actions. **ACTION** is optional and
its value can be retrieved by accessing the parameter `Configuration::$ACTION`.


## First and Second Argument

**\\compose\\** allows the presence of two more arguments in the URL, namely **ARG1** and **ARG2**.
They are both optional and as for the **ACTION** argument, it is responsibility of the selected page
to validate the value of these arguments.
The values of **ARG1** and **ARG2** can be retrieved by accessing the parameters `Configuration::$ARG1`
and `Configuration::$ARG2` respectively.


## Query String

The [**Query String**](https://en.wikipedia.org/wiki/Query_string) is the part of the URL containing
data that does not fit into the URL schema defined above. It has the classic form
```
argument1=value1&argument2=value2&...
```

Query strings can be useful, for example, when you want to pass data to a page. Let `my_registration`
be a page that receives info about a user and adds it to a database. A good URL would be

```http
http://www.my_website.com/my_registration?first=Albert&last=Einstein&born=1879&title=Physicist
```
