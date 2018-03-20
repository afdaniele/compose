# URL Rewrite

**\\compose\\** allows you to define package-specific rules for rewriting URLs.
URL Rewriting in **\\compose\\** is performed using
[Regular Expressions](https://en.wikipedia.org/wiki/Regular_expression) (RegEx).

If you need an introduction to URL Rewrite techniques, you can read
the article [URL Rewriting for Beginners](https://www.addedbytes.com/blog/url-rewriting-for-beginners).

You can specify rules for rewriting URLs in the file `metadata.json` in the
main level of your package. An example of metadata file with one rule
defined is the following:

```json
{
    "name" : "Example",
    "description" : "This is an example package",
    "dependencies" : {
        "system-packages" : [],
        "packages" : []
    },
    "url_rewrite" : {
        "images_rule" : {
            "pattern" : "/^\\/data\\/images\\/(.+)$/",
            "replace" : "/public_data/jpegs/$1"
        }
    }
}
```

Let's focus on the section of the metadata file that is relative to the URL rewriting rules.

```json
{
    "url_rewrite" : {
        "images_rule" : {
            "pattern" : "/^\\/data\\/images\\/(.+)$/",
            "replace" : "/public_data/jpegs/$1"
        }
    }
}
```

In this simple case we are defining only one rule with ID `images_rule` that
rewrites all the URIs that match the RegEx `pattern` by using the template string `replace`.
Specifically, any URI of the form `/data/images/*` will be redirected (rewritten)
to, thus served by the web server as `/public_data/jpegs/*` where the portion of the
string identified by the asterisk in the pattern will be copied to the replace the
asterisk in the replace template string.

For example, the URI
```
/data/images/example.jpg
```

will be rewritten as
```
/public_data/jpegs/example.jpg
```

and then passed to the web server that will serve the request and return the image to you.


NOTE: The RegEx `pattern` must be a PHP regular expression in
[preg_quote](http://php.net/manual/en/function.preg-quote.php) format.
