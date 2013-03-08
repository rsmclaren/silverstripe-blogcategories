silverstripe-blogcategories
===========================

Adds a manageable list of categories to the SilverStripe blog Module

extract and put in the root of your SilverStripe install.

Requires the SilverStripe Blog Module to be installed

Requires SilverStripe v3.0.2+

## Usage

By default adding new blog categories is limited to the BlogTree class. You can modify
this functionality in your _config.php to limit it to the BlogHolder class instead
using the following code.

    Config::inst()->update('BlogCategory', 'limit_to_holder', true);

### Tagcloud

With the `BlogCategoryCloud` class, you can make weighted "tag clouds"
linked to their respective categories. Getters are already
included in the `BlogEntry` and `BlogHolder` extensions,
so in those page types you can simply call `$BlogCategoryCloud`
in a template.

The cloud shows the 10 most popular categories by default.
There's also a dedicated view with all tags, which you can link
to via `$BlogCategoriesMoreLink`.
