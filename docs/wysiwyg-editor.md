# WYSIWYG editor

Tainacan uses the standard textarea by default. You can make the Tainacan WYSIWYG editor available by defining the following constant in `wp-config.php`, before the line that loads WordPress:

```php
define( 'TAINACAN_ALLOW_WYSIWYG_EDITOR', true );
```

When the constant is defined as the boolean `true`, the following description fields load the Tainacan WYSIWYG editor:

- Collection description
- Term description
- Taxonomy description
- Metadata section description
- Metadata description
- Filter description
- Item text-document content

It also adds a **Use rich text editor** switch to the metadata-type options for:

- Core Description metadata
- Textarea (long text) metadata

The switch defaults to `no`, so enabling the constant does not change existing metadata fields. Set it to `yes` for each metadata that should display the WYSIWYG editor in item editing forms. It appears in the relevant metadata-type options section; for a long-text metadata in Portuguese, this is **Opções do tipo de metadado Texto longo**.

When the constant is not defined, or has any other value, Tainacan does not load the WYSIWYG module, hides this per-metadata switch, and uses the original textarea even when a metadata was previously configured to use rich text. Its saved setting is retained, ready to be used again when the constant is re-enabled.

This is a global configuration option. Reload the Tainacan admin page after changing the constant.
