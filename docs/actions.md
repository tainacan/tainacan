# Tainacan Actions

WordPress actions are hooks that allow you to execute custom code at specific points during the WordPress lifecycle. In Tainacan, actions enable developers to extend or modify plugin behavior without altering core files. To use an action, attach your custom function to a specific hook using the `add_action()` function:

```php
add_action( 'tainacan-some-action', 'your_callback_function', 10, 2 );
function your_callback_function( $arg1, $arg2 ) {
    // Your custom code here
}
```

Refer to the list below for available Tainacan actions and their usage.


## `tainacan-enqueue-roles-scripts` <!-- {docsify-ignore} -->


Source: [class-tainacan-roles.php](https://github.com/tainacan/tainacan/blob/master/src/views/roles/class-tainacan-roles.php), [line 57](https://github.com/tainacan/tainacan/blob/master/src/views/roles/class-tainacan-roles.php#L57-L57)

---------------------------------
<br>

## `tainacan-register-vuejs-plugin` <!-- {docsify-ignore} -->

*Class Plugins_Hooks*


Argument | Type | Description
-------- | ---- | -----------
`$this` |  | 

Source: [class-tainacan-plugin-hooks.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-plugin-hooks.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-plugin-hooks.php#L7-L24)

---------------------------------
<br>

## `tainacan-register-admin-hooks` <!-- {docsify-ignore} -->


Source: [class-tainacan-admin-hooks.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-admin-hooks.php), [line 17](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-admin-hooks.php#L17-L17)

---------------------------------
<br>

## `tainacan-register-vuejs-component` <!-- {docsify-ignore} -->

*Class Components_Hooks*


Argument | Type | Description
-------- | ---- | -----------
`$this` |  | 

Source: [class-tainacan-component-hooks.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-component-hooks.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-component-hooks.php#L7-L27)

---------------------------------
<br>

## `tainacan-register-metadata-type` <!-- {docsify-ignore} -->

*Class MetadataTypeHelper*


Argument | Type | Description
-------- | ---- | -----------
`$this` |  | 

Source: [class-tainacan-metadata-type-helper.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/metadata-type-helper/class-tainacan-metadata-type-helper.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/metadata-type-helper/class-tainacan-metadata-type-helper.php#L7-L40)

---------------------------------
<br>

## `tainacan-register-filter-type` <!-- {docsify-ignore} -->

*Class FilterTypeHelper*


Argument | Type | Description
-------- | ---- | -----------
`$this` |  | 

Source: [class-tainacan-filter-type-helper.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/filter-type-helper/class-tainacan-filter-type-helper.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/filter-type-helper/class-tainacan-filter-type-helper.php#L7-L41)

---------------------------------
<br>

## `tainacan-enqueue-admin-scripts` <!-- {docsify-ignore} -->


Source: [class-tainacan-admin.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/class-tainacan-admin.php), [line 301](https://github.com/tainacan/tainacan/blob/master/src/views/admin/class-tainacan-admin.php#L301-L301)

---------------------------------
<br>

## `tainacan-dashboard-before-cards` <!-- {docsify-ignore} -->


Source: [page.php](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php), [line 49](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php#L49-L49)

---------------------------------
<br>

## `tainacan-dashboard-after-cards` <!-- {docsify-ignore} -->


Source: [page.php](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php), [line 62](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php#L62-L62)

---------------------------------
<br>

## `tainacan-bulk-edit-set-status` <!-- {docsify-ignore} -->

*Sets the status to all items in the current group*


Argument | Type | Description
-------- | ---- | -----------
`$value` |  | 
`$this->get_id()` |  | 
`$select_q` |  | 
`$query` |  | 

Source: [class-tainacan-bulk-edit.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-bulk-edit.php), [line 223](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-bulk-edit.php#L223-L249)

---------------------------------
<br>

## `tainacan-register-exposers` <!-- {docsify-ignore} -->

*Load exposers classes*


Argument | Type | Description
-------- | ---- | -----------
`$this` |  | 

Source: [class-tainacan-exposers-handler.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-exposers-handler.php), [line 8](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-exposers-handler.php#L8-L47)

---------------------------------
<br>

## `tainacan-pre-insert` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$obj` | `\Tainacan\Entities\Entity` | 

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 154](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L154-L173)

---------------------------------
<br>

## `tainacan-pre-insert-{$obj_post_type}` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$obj` | `\Tainacan\Entities\Entity` | 

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 154](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L154-L176)

---------------------------------
<br>

## `tainacan-pre-insert-{$obj_post_type}` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$obj` | `\Tainacan\Entities\Entity` | 

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 154](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L154-L209)

---------------------------------
<br>

## `tainacan-insert` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$obj` | `\Tainacan\Entities\Entity` | 
`$diffs` |  | 
`$is_update` |  | 

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 154](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L154-L238)

---------------------------------
<br>

## `tainacan-insert-{$obj_post_type}` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$obj` | `\Tainacan\Entities\Entity` | 

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 154](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L154-L240)

---------------------------------
<br>

## `tainacan-pre-delete` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$entity` | `\Tainacan\Entities\Entity` | 
`$permanent` | `bool` | If false, sendo to trash, if true, permanently delete. Default true

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 670](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L670-L679)

---------------------------------
<br>

## `tainacan-pre-delete-{$post_type}` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$entity` | `\Tainacan\Entities\Entity` | 
`$permanent` | `bool` | If false, sendo to trash, if true, permanently delete. Default true

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 670](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L670-L680)

---------------------------------
<br>

## `tainacan-deleted` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$entity` | `\Tainacan\Entities\Entity` | 
`$permanent` | `bool` | If false, sendo to trash, if true, permanently delete. Default true

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 670](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L670-L695)

---------------------------------
<br>

## `tainacan-deleted-{$post_type}` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$entity` | `\Tainacan\Entities\Entity` | 
`$permanent` | `bool` | If false, sendo to trash, if true, permanently delete. Default true

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 670](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L670-L696)

---------------------------------
<br>

## `register_filter_types` <!-- {docsify-ignore} -->

*fetch all registered filter type classes*

Possible outputs are:
CLASS (default) - returns the Class name of of filter types registered
NAME - return an Array of the names of filter types registered


Source: [class-tainacan-filters.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-filters.php), [line 305](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-filters.php#L305-L319)

---------------------------------
<br>

## `tainacan-pre-insert` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$term` | `\Tainacan\Entities\Entity` | 

Source: [class-tainacan-terms.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php), [line 112](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php#L112-L124)

---------------------------------
<br>

## `tainacan-pre-insert-term` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$term` | `\Tainacan\Entities\Entity` | 

Source: [class-tainacan-terms.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php), [line 112](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php#L112-L125)

---------------------------------
<br>

## `tainacan-insert` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$new_entity` |  | 

Source: [class-tainacan-terms.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php), [line 112](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php#L112-L172)

---------------------------------
<br>

## `tainacan-insert-term` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$new_entity` |  | 

Source: [class-tainacan-terms.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php), [line 112](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php#L112-L173)

---------------------------------
<br>

## `tainacan-pre-delete` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$deleted` |  | 
`$permanent` | `bool` | this parameter is not used by Terms repository. Delete is always permanent

Source: [class-tainacan-terms.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php), [line 281](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php#L281-L292)

---------------------------------
<br>

## `tainacan-pre-delete-term` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$deleted` |  | 
`$permanent` | `bool` | this parameter is not used by Terms repository. Delete is always permanent

Source: [class-tainacan-terms.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php), [line 281](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php#L281-L293)

---------------------------------
<br>

## `tainacan-deleted` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$deleted` |  | 
`$permanent` | `bool` | this parameter is not used by Terms repository. Delete is always permanent

Source: [class-tainacan-terms.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php), [line 281](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php#L281-L298)

---------------------------------
<br>

## `tainacan-deleted-term` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$deleted` |  | 
`$permanent` | `bool` | this parameter is not used by Terms repository. Delete is always permanent

Source: [class-tainacan-terms.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php), [line 281](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php#L281-L299)

---------------------------------
<br>

## `tainacan-pre-insert` <!-- {docsify-ignore} -->

*Persist a Log entity into the custom wp_tainacan_logs table.*

Uses $wpdb->insert() with explicit format specifiers so all values
go through wpdb's internal prepare(), preventing SQL injection.
Serializable fields (old_value, new_value) are passed through
maybe_serialize() before storage.


Argument | Type | Description
-------- | ---- | -----------
`$obj` | `\Tainacan\Entities\Log` | 

Source: [class-tainacan-logs.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php), [line 453](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php#L453-L472)

---------------------------------
<br>

## `tainacan-pre-insert-{$post_type}` <!-- {docsify-ignore} -->

*Persist a Log entity into the custom wp_tainacan_logs table.*

Uses $wpdb->insert() with explicit format specifiers so all values
go through wpdb's internal prepare(), preventing SQL injection.
Serializable fields (old_value, new_value) are passed through
maybe_serialize() before storage.


Argument | Type | Description
-------- | ---- | -----------
`$obj` | `\Tainacan\Entities\Log` | 

Source: [class-tainacan-logs.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php), [line 453](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php#L453-L474)

---------------------------------
<br>

## `tainacan-insert` <!-- {docsify-ignore} -->

*Persist a Log entity into the custom wp_tainacan_logs table.*

Uses $wpdb->insert() with explicit format specifiers so all values
go through wpdb's internal prepare(), preventing SQL injection.
Serializable fields (old_value, new_value) are passed through
maybe_serialize() before storage.


Argument | Type | Description
-------- | ---- | -----------
`$obj` | `\Tainacan\Entities\Log` | 
`[]` |  | 
`false` |  | 

Source: [class-tainacan-logs.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php), [line 453](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php#L453-L519)

---------------------------------
<br>

## `tainacan-insert-{$post_type}` <!-- {docsify-ignore} -->

*Persist a Log entity into the custom wp_tainacan_logs table.*

Uses $wpdb->insert() with explicit format specifiers so all values
go through wpdb's internal prepare(), preventing SQL injection.
Serializable fields (old_value, new_value) are passed through
maybe_serialize() before storage.


Argument | Type | Description
-------- | ---- | -----------
`$obj` | `\Tainacan\Entities\Log` | 

Source: [class-tainacan-logs.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php), [line 453](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php#L453-L520)

---------------------------------
<br>

## `tainacan-pre-insert` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | 

Source: [class-tainacan-item-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php), [line 24](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php#L24-L37)

---------------------------------
<br>

## `tainacan-pre-insert-Item_Metadata_Entity` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | 

Source: [class-tainacan-item-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php), [line 24](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php#L24-L38)

---------------------------------
<br>

## `tainacan-insert` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | 

Source: [class-tainacan-item-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php), [line 24](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php#L24-L116)

---------------------------------
<br>

## `tainacan-insert-Item_Metadata_Entity` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | 

Source: [class-tainacan-item-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php), [line 24](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php#L24-L117)

---------------------------------
<br>

## `tainacan-pre-delete` <!-- {docsify-ignore} -->

*Repository for managing Tainacan item metadata.*

Handles all database operations for item metadata including creation,
updates, deletion, and querying with proper validation and logging.


Argument | Type | Description
-------- | ---- | -----------
`$item_metadata` |  | 
`true` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-item-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php#L9-L267)

---------------------------------
<br>

## `tainacan-pre-delete-Item_Metadata_Entity` <!-- {docsify-ignore} -->

*Repository for managing Tainacan item metadata.*

Handles all database operations for item metadata including creation,
updates, deletion, and querying with proper validation and logging.


Argument | Type | Description
-------- | ---- | -----------
`$item_metadata` |  | 
`true` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-item-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php#L9-L268)

---------------------------------
<br>

## `tainacan-deleted` <!-- {docsify-ignore} -->

*Repository for managing Tainacan item metadata.*

Handles all database operations for item metadata including creation,
updates, deletion, and querying with proper validation and logging.


Argument | Type | Description
-------- | ---- | -----------
`$item_metadata` |  | 
`true` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-item-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php#L9-L281)

---------------------------------
<br>

## `tainacan-deleted-Item_Metadata_Entity` <!-- {docsify-ignore} -->

*Repository for managing Tainacan item metadata.*

Handles all database operations for item metadata including creation,
updates, deletion, and querying with proper validation and logging.


Argument | Type | Description
-------- | ---- | -----------
`$item_metadata` |  | 
`true` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-item-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-item-metadata.php#L9-L282)

---------------------------------
<br>

## `register_metadata_types` <!-- {docsify-ignore} -->

*fetch all registered metadatum type classes*

Possible outputs are:
CLASS (default) - returns the Class name of of metadatum types registered
NAME - return an Array of the names of metadatum types registered


Source: [class-tainacan-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php), [line 794](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php#L794-L808)

---------------------------------
<br>

## `tainacan-taxonomy-removed-from-collection` <!-- {docsify-ignore} -->

*Triggers hooks when saving a Taxonomy Metadatum, indicating wich taxonomy was added or removed from a collection.*

This is used by Taxonomies repository to update the collections_ids property of the taxonomy as
a metadatum type taxonomy is inserted or removed


Argument | Type | Description
-------- | ---- | -----------
`$this->current_taxonomy` |  | 
`$collection` |  | 

Source: [class-tainacan-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php), [line 1665](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php#L1665-L1687)

---------------------------------
<br>

## `tainacan-taxonomy-added-to-collection` <!-- {docsify-ignore} -->

*Triggers hooks when saving a Taxonomy Metadatum, indicating wich taxonomy was added or removed from a collection.*

This is used by Taxonomies repository to update the collections_ids property of the taxonomy as
a metadatum type taxonomy is inserted or removed


Argument | Type | Description
-------- | ---- | -----------
`$new_tax` |  | 
`$collection` |  | 

Source: [class-tainacan-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php), [line 1665](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php#L1665-L1691)

---------------------------------
<br>

## `tainacan-taxonomy-removed-from-collection` <!-- {docsify-ignore} -->

*Repository for managing Tainacan metadata definitions.*

Handles all database operations for metadata including creation,
updates, deletion, and querying with proper validation and logging.


Argument | Type | Description
-------- | ---- | -----------
`$removed_tax` |  | 
`$collection` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php), [line 10](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php#L10-L1742)

---------------------------------
<br>

## `tainacan-api-item-updated` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$updated_item` |  | 
`$attributes` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 1002](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L1002-L1033)

---------------------------------
<br>

## `tainacan-api-item-duplicated` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$item` |  | 
`$new_item` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 1142](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L1142-L1234)

---------------------------------
<br>

## `tainacan-submission-item-finish` <!-- {docsify-ignore} -->

*REST API controller for managing Tainacan items.*

Handles all REST API endpoints for item operations including
creation, updates, deletion, and querying of items within collections.


Argument | Type | Description
-------- | ---- | -----------
`$item` |  | 
`$request` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 11](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L11-L1560)

---------------------------------
<br>

## `tainacan-oai-response` <!-- {docsify-ignore} -->

*Fires after the OAI-PMH XML response is built, before it is sent.*

Lets a plugin cache the body or record observability data.


Argument | Type | Description
-------- | ---- | -----------
`$xml_string` | `string` | The response body.
`$verb` | `string` | The requested verb.
`$params` | `array` | The request parameters.
`$from_cache` | `bool` | Whether the body was produced by a short-circuit filter.

Source: [class-tainacan-rest-oaipmh-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-oaipmh-controller.php), [line 509](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-oaipmh-controller.php#L509-L518)

---------------------------------
<br>

## `tainacan-api-collection-created` <!-- {docsify-ignore} -->

*Receive a JSON with the structure of a Collection and return, in case of success insert
a Collection object in JSON*


Argument | Type | Description
-------- | ---- | -----------
`$response` |  | 
`$request` | `\WP_REST_Request` | 

Source: [class-tainacan-rest-collections-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-collections-controller.php), [line 472](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-collections-controller.php#L472-L502)

---------------------------------
<br>

## `tainacan-register-mappers` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$this` |  | 

Source: [class-tainacan-mappers-handler.php](https://github.com/tainacan/tainacan/blob/master/src/classes/mappers/class-tainacan-mappers-handler.php), [line 46](https://github.com/tainacan/tainacan/blob/master/src/classes/mappers/class-tainacan-mappers-handler.php#L46-L46)

---------------------------------
<br>

## `tainacan-upload-folder-renamed` <!-- {docsify-ignore} -->

*When an item or collection is saved, it checks if the status was changed and
if the items upload directory must be renamed to add or remove the
private folder prefix*

TODO: when deleting an item or collection, the folder must be deleted. However this is challenging because
we need to build the path with information that may not be available after the deletion.


Argument | Type | Description
-------- | ---- | -----------
`$check_folder` |  | 
`$folder` |  | 

Source: [class-tainacan-private-files.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-private-files.php), [line 325](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-private-files.php#L325-L384)

---------------------------------
<br>

## `tainacan-upload-folder-renamed` <!-- {docsify-ignore} -->

*Rename all folders from items after a bulk edit operation move their statuses*

TODO: In the upcoming bulk edit refactor this must be handled as there are performance issues


Argument | Type | Description
-------- | ---- | -----------
`$found[0]` |  | 
`$target` |  | 

Source: [class-tainacan-private-files.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-private-files.php), [line 405](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-private-files.php#L405-L436)

---------------------------------
<br>

## `tainacan-pre-insert-attachment` <!-- {docsify-ignore} -->

*Insert an attachment from an URL address.*


Argument | Type | Description
-------- | ---- | -----------
`$blob` | `\Tainacan\blob` | bitstream of the attachment
`$filename` | `string` | The filename that will be created
`$post_id` | `int` | (optional) the post this attachement should be attached to. empty for none

Source: [class-tainacan-media.php](https://github.com/tainacan/tainacan/blob/master/src/classes/media-helper/class-tainacan-media.php), [line 395](https://github.com/tainacan/tainacan/blob/master/src/classes/media-helper/class-tainacan-media.php#L395-L405)

---------------------------------
<br>

## `tainacan-post-insert-attachment` <!-- {docsify-ignore} -->

*Insert an attachment from an URL address.*


Argument | Type | Description
-------- | ---- | -----------
`$attach_id` |  | 
`$attach_data` |  | 
`$post_id` | `int` | (optional) the post this attachement should be attached to. empty for none

Source: [class-tainacan-media.php](https://github.com/tainacan/tainacan/blob/master/src/classes/media-helper/class-tainacan-media.php), [line 395](https://github.com/tainacan/tainacan/blob/master/src/classes/media-helper/class-tainacan-media.php#L395-L449)

---------------------------------
<br>

## `tainacan-register-generic_process` <!-- {docsify-ignore} -->


Source: [class-tainacan-generic-handler.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/generic-process/class-tainacan-generic-handler.php), [line 34](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/generic-process/class-tainacan-generic-handler.php#L34-L34)

---------------------------------
<br>

## `tainacan-register-importers` <!-- {docsify-ignore} -->


Source: [class-tainacan-importer-handler.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/importer/class-tainacan-importer-handler.php), [line 83](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/importer/class-tainacan-importer-handler.php#L83-L83)

---------------------------------
<br>

## `tainacan-register-exporters` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$this` |  | 

Source: [class-tainacan-exporter-handler.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/exporter/class-tainacan-exporter-handler.php), [line 52](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/exporter/class-tainacan-exporter-handler.php#L52-L52)

---------------------------------
<br>

<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

