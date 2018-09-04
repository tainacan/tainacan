# Templates

Notes for a future documentation

Tainacan uses the ordinary WordPress template hierarchy for its templates. It adds additional templates in the hierarchy

* `tainacan/single-items.php` - Used in the single template for any item of any collection
* `tainacan/arhive-items.php` - Used in the list of items of any collection (e.g. both archive-tnc_col_4_item.php and archive-tnc_col_5_item.php)
* `tainacan/archive-taxonomy.php` - Used as a template for all Tainacan Taxonomies (list items based on a term)
* `tainacan/archive-repository.php` - Used in the `/items/` URL, which is added by the Tainacan plugin, and lists all items of all collections

Since each collection is a new custom post type, these templates are usefull to create a template that will be used by all collections.

Nevertheless, you are still able to create more specific templates, using the standard WordPress hierarchy.

Examples:

A template for single items in the collection with id 4:

`single-tnc_col_4_item.php`

A template for a single specific item:

`single-tnc_col_4_item-item-name.php`

A template for the list of items of the collection with id 4

`archive-tnc_col_4_item.php`

A template for a specific taxonomy

`taxonomy-tnc_tax_123.php`

A teplate for a specific term

`taxonomy-tnc_tax_123-term-name.php`