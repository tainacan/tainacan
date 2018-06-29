# Metadata

## Metadatum Repository

<!-- BEGIN DOC-COMMENT H3 src/classes/repositories/class-tainacan-metadata.php -->
### `class Metadata extends Repository`

Class Metadata 


### `protected function __construct()`

Register specific hooks for metadatum repository 


### `public function get_cpt_labels()`

Get the labels for the custom post type of this repository 
**Returns:** `array` — Labels in the format expected by register_post_type()

### `public function get_default_metadata_attribute()`

constant used in default metadatum in attribute collection_id 


**Returns:** `string` — the value of constant

### `public function register_field_type( $class_name )`

register metadatum types class on array of types 


**Parameters:**

* `string` — $class_name — | object The class name or the instance

### `public function unregister_field_type( $class_name )`

register metadatum types class on array of types 


**Parameters:**

* `string` — $class_name — | object The class name or the instance

### `public function fetch( $args, $output = null )`

fetch metadatum based on ID or WP_Query args 

metadatum are stored as posts. Check WP_Query docs to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/) You can also use a mapped property, such as name and description, as an argument and it will be mapped to the appropriate WP_Query argument 


**Parameters:**

* `$args` — array — WP_Query args || int $args the metadatum id
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `Entities\Metadatum|\WP_Query|Array` — an instance of wp query OR array of entities;

### `public function fetch_by_collection(Entities\Collection $collection, $args = [], $output = null)`

fetch metadatum by collection, searches all metadatum available 


**Parameters:**

* `$collection` — Entities\Collection
* `$args` — array — WP_Query args plus disabled_fields
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `array` — Entities\Metadatum

### `public function order_result( $result, Entities\Collection $collection, $include_disabled = false )`

Ordinate the result from fetch response if $collection has an ordination, metadata not ordinated appear on the end of the list 




**Parameters:**

* `Response` — $result — from method fetch
* `$collection` — Entities\Collection
* `$include_disabled` — bool — Wether to include disabled metadata in the results or not

**Returns:** `array` — or WP_Query ordinate

### `public function insert($metadatum)`

{@inheritDoc} 
**Parameters:**

* `$metadatum` — \Tainacan\Entities\Metadatum

**Returns:** \Tainacan\Entities\Metadatum

### `public function update($object, $new_values = null)`


**Returns:** mixed|string|Entities\Entity

### `public function fetch_field_types( $output = 'CLASS')`

fetch all registered metadatum type classes 

Possible outputs are: CLASS (default) - returns the Class name of of metadatum types registered NAME - return an Array of the names of metadatum types registered 


**Parameters:**

* `string` — $output — CLASS | NAME

**Returns:** `array` — of Entities\Metadatum_Types\Metadatum_Type classes path name

### `public function register_core_fields( Entities\Collection $collection )`


**Parameters:**

* `$collection` — Entities\Collection

**Returns:** bool

### `public function disable_delete_core_fields( $before, $post )`

block user from remove core metadata 


**Parameters:**

* `wordpress` — $before — pass a null value
* `the` — $post — post which is moving to trash

**Returns:** null/bool

### `public function force_delete_core_fields( $before, $post, $force_delete )`

block user from remove core metadata ( if use wp_delete_post) 


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

create a metadatum entity and insert by an associative array ( attribute => value ) 


**Parameters:**

* `$data` — Array — the array of attributes to insert a metadatum

**Returns:** `int` — the metadatum id inserted

### `public function fetch_all_field_values($collection_id, $metadatum_id)`

Fetch all values of a metadatum from a collection in all it collection items 


**Returns:** array|null|object

### `private function pre_update_taxonomy_field($metadatum)`

Stores the value of the taxonomy_id option to use on update_taxonomy_field method. 




### `private function update_taxonomy_field($metadatum)`

Triggers hooks when saving a Taxonomy Metadatum, indicating wich taxonomy was added or removed from a collection. 

This is used by Taxonomies repository to update the collections_ids property of the taxonomy as a metadatum type taxonomy is inserted or removed 


**Parameters:**

* `$metadatum` — [type] — [description]

**Returns:** `[type]` — [description]

<!-- END DOC-COMMENT -->

## Metadatum Entity

<!-- BEGIN DOC-COMMENT H3 src/classes/entities/class-tainacan-metadatum.php -->
### `class Metadatum extends Entity`

Represents the Entity Metadatum 


### `protected $repository = 'Metadata'`

{@inheritDoc} 
### `function get_name()`

Return the metadatum name 


**Returns:** string

### `function get_slug()`

Get metadatum slug 


**Returns:** string

### `function get_order()`

Return the metadatum order type 


**Returns:** string

### `function get_parent()`

Return the parent ID 


**Returns:** string

### `function get_description()`

Return the metadatum description 


**Returns:** string

### `function get_required()`

Return if is a required metadatum 


**Returns:** boolean

### `function get_multiple()`

Return if is a multiple metadatum 


**Returns:** boolean

### `function get_cardinality()`

Return the cardinality 


**Returns:** string

### `function get_collection_key()`

Return if metadatum is key 


**Returns:** boolean

### `function get_mask()`

Return the mask 


**Returns:** string

### `function get_default_value()`

Return the metadatum default value 


**Returns:** `string` — || integer

### `function get_field_type_object()`

Return the an object child of \Tainacan\Metadatum_Types\Metadatum_Type with options 


**Returns:** `\Tainacan\Metadatum_Types\Metadatum_Type` — The metadatum type class with filled options

### `function get_field_type()`

Return the class name for the metadatum type 


**Returns:** `string` — The

### `function get_field_type_options()`

Return the actual options for the current metadatum type 


**Returns:** `array` — Configurations for the metadatum type object

### `function set_name($value)`

Set the metadatum name 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function get_accept_suggestion()`

Return true if this metadatum allow community suggestions, false otherwise 
**Returns:** bool

### `function set_slug($value)`

Set the metadatum slug 

If you dont set the metadatum slug, it will be set automatically based on the name and following WordPress default behavior of creating slugs for posts. 

If you set the slug for an existing one, WordPress will append a number at the end of in order to make it unique (e.g slug-1, slug-2) 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_order($value)`

Set manually the order of the metadatum 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_parent($value)`

Set the metadatum parent ID 


**Parameters:**

* `$value` — [integer] — The ID from parent

**Returns:** void

### `function set_description($value)`

Set metadatum description 


**Parameters:**

* `$value` — [string] — The text description

**Returns:** void

### `function set_required( $value )`

Allow the metadatum be required 


**Parameters:**

* `$value` — [boolean]

**Returns:** void

### `function set_multiple( $value )`

Allow multiple metadata 


**Parameters:**

* `$value` — [boolean]

**Returns:** void

### `function set_cardinality( $value )`

The number of  possible metadata 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_collection_key( $value )`

Define if the value is key on the collection 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_mask( $value )`

Set mask for the metadatum 


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

set the metadatum type class name 


**Parameters:**

* `|` — string — \Tainacan\Metadatum_Types\Metadatum_Type $value The name of the class or the instance

### `function set_accept_suggestion( $value )`

Set if this metadatum allow community suggestions 
**Parameters:**

* `$value` — bool

### `function set_field_type_options( $value )`

Set Metadatum type options 


**Parameters:**

* `||` — [string — integer] $value

**Returns:** void

### `public function get_enabled_for_collection()`

Transient property used to store the status of the metadatum for a particular collection 

Used by the API to tell front end when a metadatum is disabled 




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

Also validates the metadatum, calling the validate_options callback of the Metadatum Type 


**Returns:** `bool` — valid or not

<!-- END DOC-COMMENT -->