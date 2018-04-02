# Taxonomies

## Taxonomies Repository

<!-- BEGIN DOC-COMMENT H3 src/classes/repositories/class-tainacan-taxonomies.php -->
### `class Taxonomies extends Repository`

Class Tainacan_Taxonomies 


### `public function get_cpt_labels()`

Get the labels for the custom post type of this repository 
**Returns:** `array` — Labels in the format expected by register_post_type()

### `public function insert($taxonomy)`


**Parameters:**

* `$taxonomy` — Entities\Taxonomy

**Returns:** Entities\Entity

### `public function fetch( $args = [], $output = null )`

fetch taxonomies based on ID or WP_Query args 

Taxonomies are stored as posts. Check WP_Query docs to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/) You can also use a mapped property, such as name and description, as an argument and it will be mapped to the appropriate WP_Query argument 


**Parameters:**

* `$args` — array — WP_Query args | int $args the taxonomy id
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `\WP_Query|Array` — an instance of wp query OR array of entities;

<!-- END DOC-COMMENT -->

## Taxonomy Entity

<!-- BEGIN DOC-COMMENT H3 src/classes/entities/class-tainacan-taxonomy.php -->
### `class Taxonomy extends Entity`

Represents the Entity Taxonomy 


### `protected static $post_type = 'tainacan-taxonomy'`

{@inheritDoc} 
### `protected static $capability_type = ['tainacan-taxonomy', 'tainacan-taxonomies']`

{@inheritDoc} 
### `protected $repository = 'Taxonomies'`

{@inheritDoc} 
### `function register_taxonomy()`

Register the taxonomy 


**Returns:** bool

### `function get_name()`

Return the name 


**Returns:** string

### `function get_description()`

Return the description 


**Returns:** string

### `function get_allow_insert()`

Return true if allow insert or false if not allow insert 


**Returns:** boolean

### `function get_slug()`

Return the slug 


**Returns:** string

### `function get_db_identifier()`

Return the DB ID 


**Returns:** bool|string

### `function set_name($value)`

Define the name of taxonomy 


**Parameters:**

* `$value` — [string]

### `function set_slug($value)`

Define the slug 


**Parameters:**

* `$value` — [string]

### `function set_description($value)`

Define the description 


**Parameters:**

* `$value` — [string]

### `function set_allow_insert($value)`

Define if allow insert or not 


**Parameters:**

* `$value` — [boolean]

### `function validate()`

Validate Taxonomy 


**Returns:** bool

<!-- END DOC-COMMENT -->