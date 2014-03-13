silverstripe-blogcategories
===========================

Adds a manageable list of categories to the SilverStripe blog Module

extract and put in the root of your SilverStripe install.

Requires the SilverStripe Blog Module to be installed

Requires SilverStripe v3.1+

Use the 3.0 branch for 3.0 compatibility.

## Usage

By default adding new blog categories is limited to the BlogHolder class. You can modify
this functionality in your config.yml or _config.php to limit it to the BlogTree class instead
using the following code.

In _config.php

    Config::inst()->update('BlogCategory', 'limit_to_holder', false);
	
in config.yml

    BlogCategory:
	  limit_to_holder:  false

### Tagcloud

With the `BlogCategoryCloud` class, you can make weighted "tag clouds"
linked to their respective categories. Getters are already
included in the `BlogEntry` and `BlogHolder` extensions,
so in those page types you can simply call `$BlogCategoryCloud`
in a template.

The cloud shows the 10 most popular categories by default.
There's also a dedicated view with all tags, which you can link
to via `$BlogCategoriesMoreLink`.

CMS Admins can also easily add a category cloud by using the provided
category cloud widget
