# tainacan_maybe_unserialize_array

Decode a stored list/order value into an array without instantiating PHP objects.

Collection order fields and filter/metadata type options are stored via
update_post_meta(), which PHP-serializes arrays. Readers historically
called unserialize() with no allowed_classes, which is a PHP object
injection sink if a serialized object string is stored.

This helper:
- returns arrays as-is
- recovers PHP-serialized arrays
- never instantiates objects (allowed_classes => false)

***

* Full name: `tainacan_maybe_unserialize_array`
* Defined in: `classes/tainacan-utils.php`

## Parameters

| Parameter | Type      | Description                                   |
|-----------|-----------|-----------------------------------------------|
| `$value`  | **mixed** | Raw value from an entity getter or post meta. |

## Return Value

**array**
