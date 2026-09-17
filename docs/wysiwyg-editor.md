# WYSIWYG editor

Description fields use the standard Tainacan textarea by default. You can opt in to the Tainacan WYSIWYG editor by defining the following constant in `wp-config.php`, before the line that loads WordPress:

```php
define( 'TAINACAN_ALLOW_WYSIWYG_EDITOR', true );
```

When the constant is defined as the boolean `true`, the following fields load the Tainacan WYSIWYG editor:

- Collection description
- Term description
- Taxonomy description
- Metadata section description
- Metadata description
- Filter description
- Item text-document content

When it is not defined, or has any other value, Tainacan keeps the original textareas and does not load the WYSIWYG module.

This is a global configuration option. Reload the Tainacan admin page after changing the constant.
