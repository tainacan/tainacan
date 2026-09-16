# WYSIWYG editor

The collection-description form uses the standard Tainacan textarea by default. You can opt in to the Tainacan WYSIWYG editor by defining the following constant in `wp-config.php`, before the line that loads WordPress:

```php
define( 'TAINACAN_ALLOW_WYSIWYG_EDITOR', true );
```

When the constant is defined as the boolean `true`, the collection-description field loads the Tainacan WYSIWYG editor. When it is not defined, or has any other value, Tainacan keeps the original four-row textarea and does not load the WYSIWYG module.

This is a global configuration option. Reload the Tainacan admin page after changing the constant.
