# Item Metadata

## Item Metadata Repository

<!-- BEGIN DOC-COMMENT H3 src/classes/repositories/class-tainacan-item-metadata.php -->
### `if ( $item_metadata->get_field()->get_parent() > 0 && is_null($item_metadata->get_meta_id()) )`

When we are adding a field that is child of another, this means it is inside a compound field 

In that case, if the Item_Metadata object is not set with a meta_id, it means we want to create a new one and not update an existing. This is the case of a multiple compound field. 


### `public function delete($item_metadata)`


**Returns:** mixed|void

### `public function add_compound_value(Entities\Item_Metadata_Entity $item_metadata, $meta_id)`




**Returns:** `null|ind` — the meta id of the created compound metadata

### `public function fetch($object, $output = null )`

Fetch Item Field objects related to an Item 


**Parameters:**

* `$object` — Entities\Item

**Returns:** array

### `public function get_value(Entities\Item_Metadata_Entity $item_metadata)`

Get the value for a Item field. 


**Parameters:**

* `$item_metadata` — Entities\Item_Metadata_Entity

**Returns:** mixed

### `private function extract_compound_value(array $ids, Entities\Item $item, $compund_meta_id)`

Transforms the array saved as meta_value with the IDs of post_meta saved as a value for compound fields and converts it into an array of Item Metadatada Entitites 


**Parameters:**

* `$ids` — array — The array of post_meta ids
* `$item` — Entities\Item — The item this post_meta is related to
* `$compund_meta_id` — int — the meta_id of the parent compound metadata

**Returns:** `array` — An array of Item_Metadata_Entity objects

### `public function update( $object, $new_values = null )`


**Returns:** mixed

### `public function suggest($item_metadata)`

Suggest a value to be inserted as a item Field value, return a pending log 
**Parameters:**

* `$item_metadata` — Entities\Item_Metadata_Entity

**Returns:** Entities\Log

<!-- END DOC-COMMENT -->

## Item Metadata Entity

<!-- BEGIN DOC-COMMENT H3 src/classes/entities/class-tainacan-item-metadata-entity.php -->
### `class Item_Metadata_Entity extends Entity`

Represents the Item Field Entity 


### `protected $repository = 'Item_Metadata'`

{@inheritDoc} 
### `function __construct(Item $item, Field $field, $meta_id = null, $parent_meta_id = null)`




**Parameters:**

* `$item` — Item — Item Entity
* `$field` — Field — Field Entity
* `$meta_id` — int — ID for a specific meta row

### `function set_item(Item $item)`

Define the item 


**Parameters:**

* `$item` — Item

**Returns:** void

### `function set_value($value)`

Define the field value 


**Parameters:**

* `|` — [integer — string] $value

**Returns:** void

### `function set_field(Field $field)`

Define the field 


**Parameters:**

* `$field` — Field

**Returns:** void

### `function set_meta_id($meta_id)`

Set the specific meta ID for this metadata. 

When this value is set, get_value() will use it to fetch the value from the post_meta table, instead of considering the item and field IDs 


**Parameters:**

* `$meta_id` — int — the ID of a specifica post_meta row

### `function set_parent_meta_id($parent_meta_id)`

Set parent_meta_id. Used when a item_metadata is inside a compound Field 

When you have a multiple compound field, this indicates of which instace of the value this item_metadata is attached to 


**Parameters:**

* `$parent_meta_id` — [type] — [description]

### `function get_item()`

Return the item 


**Returns:** Item

### `function get_field()`

Return the field 


**Returns:** Field

### `function get_meta_id()`

Return the meta_id 


**Returns:** Field

### `function get_parent_meta_id()`

Return the meta_id 


**Returns:** Field

### `function get_value()`

Return the field value 


**Returns:** `string` — | integer

### `function is_multiple()`

Return true if field is multiple, else return false 


**Returns:** boolean

### `function is_collection_key()`

Return true if field is key 


**Returns:** boolean

### `function is_required()`

Return true if field is required 


**Returns:** boolean

### `function validate()`

Validate attributes 


**Returns:** boolean

<!-- END DOC-COMMENT -->