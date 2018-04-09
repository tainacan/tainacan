# Fields

## Field Repository

<!-- BEGIN DOC-COMMENT H3 src/classes/repositories/class-tainacan-fields.php -->
### `class Fields extends Repository`

Class Fields 


### `protected function __construct()`

Register specific hooks for field repository 


### `public function get_cpt_labels()`

Get the labels for the custom post type of this repository 
**Returns:** `array` — Labels in the format expected by register_post_type()

### `public function get_default_metadata_attribute()`

constant used in default field in attribute collection_id 


**Returns:** `string` — the value of constant

### `public function register_field_type( $class_name )`

register field types class on array of types 


**Parameters:**

* `string` — $class_name — | object The class name or the instance

### `public function unregister_field_type( $class_name )`

register field types class on array of types 


**Parameters:**

* `string` — $class_name — | object The class name or the instance

### `public function fetch( $args, $output = null )`

fetch field based on ID or WP_Query args 

field are stored as posts. Check WP_Query docs to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/) You can also use a mapped property, such as name and description, as an argument and it will be mapped to the appropriate WP_Query argument 


**Parameters:**

* `$args` — array — WP_Query args || int $args the field id
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `Entities\Field|\WP_Query|Array` — an instance of wp query OR array of entities;

### `public function fetch_by_collection(Entities\Collection $collection, $args = [], $output = null)`

fetch field by collection, searches all field available 


**Parameters:**

* `$collection` — Entities\Collection
* `$args` — array — WP_Query args plus disabled_fields
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `array` — Entities\Field

### `public function order_result( $result, Entities\Collection $collection, $include_disabled = false )`

Ordinate the result from fetch response if $collection has an ordination, fields not ordinated appear on the end of the list 




**Parameters:**

* `Response` — $result — from method fetch
* `$collection` — Entities\Collection
* `$include_disabled` — bool — Wether to include disabled fields in the results or not

**Returns:** `array` — or WP_Query ordinate

### `public function insert($field)`

{@inheritDoc} 
**Parameters:**

* `$field` — \Tainacan\Entities\Field

**Returns:** \Tainacan\Entities\Field

### `public function update($object, $new_values = null)`


**Returns:** mixed|string|Entities\Entity

### `public function fetch_field_types( $output = 'CLASS')`

fetch all registered field type classes 

Possible outputs are: CLASS (default) - returns the Class name of of field types registered NAME - return an Array of the names of field types registered 


**Parameters:**

* `string` — $output — CLASS | NAME

**Returns:** `array` — of Entities\Field_Types\Field_Type classes path name

### `public function register_core_fields( Entities\Collection $collection )`


**Parameters:**

* `$collection` — Entities\Collection

**Returns:** bool

### `public function disable_delete_core_fields( $before, $post )`

block user from remove core fields 


**Parameters:**

* `wordpress` — $before — pass a null value
* `the` — $post — post which is moving to trash

**Returns:** null/bool

### `public function force_delete_core_fields( $before, $post, $force_delete )`

block user from remove core fields ( if use wp_delete_post) 


**Parameters:**

* `wordpress` — $before — pass a null value
* `the` — $post — post which is deleting
* `a` — $force_delete — boolean that force the deleting

**Returns:** `null` — /bool

### `public function get_core_fields( Entities\Collection $collection )`

returns all core items from a specific collection 


**Parameters:**

* `$collection` — Entities\Collection

**Returns:** Array|\WP_Query

### `public function insert_array_field( $data )`

create a field entity and insert by an associative array ( attribute => value ) 


**Parameters:**

* `$data` — Array — the array of attributes to insert a field

**Returns:** `int` — the field id inserted

### `public function fetch_all_field_values($collection_id, $field_id)`

Fetch all values of a field from a collection in all it collection items 


**Returns:** array|null|object

### `private function pre_update_category_field($field)`

Stores the value of the taxonomy_id option to use on update_category_field method. 




### `private function update_category_field($field)`

Triggers hooks when saving a Category Field, indicating wich taxonomy was added or removed from a collection. 

This is used by Taxonomies repository to update the collections_ids property of the taxonomy as a field type category is inserted or removed 


**Parameters:**

* `$field` — [type] — [description]

**Returns:** `[type]` — [description]

<!-- END DOC-COMMENT -->

## Field Entity

<!-- BEGIN DOC-COMMENT H3 src/classes/entities/class-tainacan-field.php -->
### `class Field extends Entity`

Represents the Entity Field 


### `protected $repository = 'Fields'`

{@inheritDoc} 
### `function get_name()`

Return the field name 


**Returns:** string

### `function get_slug()`

Get field slug 


**Returns:** string

### `function get_order()`

Return the field order type 


**Returns:** string

### `function get_parent()`

Return the parent ID 


**Returns:** string

### `function get_description()`

Return the field description 


**Returns:** string

### `function get_required()`

Return if is a required field 


**Returns:** boolean

### `function get_multiple()`

Return if is a multiple field 


**Returns:** boolean

### `function get_cardinality()`

Return the cardinality 


**Returns:** string

### `function get_collection_key()`

Return if field is key 


**Returns:** boolean

### `function get_mask()`

Return the mask 


**Returns:** string

### `function get_default_value()`

Return the field default value 


**Returns:** `string` — || integer

### `function get_field_type_object()`

Return the an object child of \Tainacan\Field_Types\Field_Type with options 


**Returns:** `\Tainacan\Field_Types\Field_Type` — The field type class with filled options

### `function get_field_type()`

Return the class name for the field type 


**Returns:** `string` — The

### `function get_field_type_options()`

Return the actual options for the current field type 


**Returns:** `array` — Configurations for the field type object

### `function set_name($value)`

Set the field name 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function get_accept_suggestion()`

Return true if this field allow community suggestions, false otherwise 
**Returns:** bool

### `function set_slug($value)`

Set the field slug 

If you dont set the field slug, it will be set automatically based on the name and following WordPress default behavior of creating slugs for posts. 

If you set the slug for an existing one, WordPress will append a number at the end of in order to make it unique (e.g slug-1, slug-2) 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_order($value)`

Set manually the order of the field 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_parent($value)`

Set the field parent ID 


**Parameters:**

* `$value` — [integer] — The ID from parent

**Returns:** void

### `function set_description($value)`

Set field description 


**Parameters:**

* `$value` — [string] — The text description

**Returns:** void

### `function set_required( $value )`

Allow the field be required 


**Parameters:**

* `$value` — [boolean]

**Returns:** void

### `function set_multiple( $value )`

Allow multiple fields 


**Parameters:**

* `$value` — [boolean]

**Returns:** void

### `function set_cardinality( $value )`

The number of  possible fields 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_collection_key( $value )`

Define if the value is key on the collection 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_mask( $value )`

Set mask for the field 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_privacy( $value )`

Set privacy 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_default_value( $value )`

Set default value 


**Parameters:**

* `||` — [string — integer] $value

**Returns:** void

### `public function set_field_type( $value )`

set the field type class name 


**Parameters:**

* `|` — string — \Tainacan\Field_Types\Field_Type $value The name of the class or the instance

### `function set_accept_suggestion( $value )`

Set if this field allow community suggestions 
**Parameters:**

* `$value` — bool

### `function set_field_type_options( $value )`

Set Field type options 


**Parameters:**

* `||` — [string — integer] $value

**Returns:** void

### `public function get_enabled_for_collection()`

Transient property used to store the status of the field for a particular collection 

Used by the API to tell front end when a field is disabled 




### `function is_multiple()`

Return true if is multiple, else return false 


**Returns:** boolean

### `function is_collection_key()`

Return true if is collection key, else return false 


**Returns:** boolean

### `function is_required()`

Return true if is required, else return false 


**Returns:** boolean

### `public function validate()`

{@inheritdoc } 

Also validates the field, calling the validate_options callback of the Field Type 


**Returns:** `bool` — valid or not

<!-- END DOC-COMMENT -->