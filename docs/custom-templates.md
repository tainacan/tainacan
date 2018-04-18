# Templates

Notes for a future documentation

Tainacan uses the ordinary WordPress template hierarchy for its templates. It adds only 2 additional templates in your hierarchy

* `tainacan/single-items.php` - Used in the single template for any item of any collection
* `tainacan/arhive-items.php` - Used in the list of items of any collection

Since each collection is a new custom post type, these 2 new templates are usefull to create a template that will be used by all collections.

Nevertheless, you are still able to create more specific templates, using the standar WordPress hierarchy.

Examples:

A template for single items in the collection with id 4:

`single-tnc_col_4_item.php`

A template for a single specific item:

`single-tnc_col_4_item-item-name.php`

A template for the list of items of the collection with id 4

`archive-tnc_col_4_item.php`