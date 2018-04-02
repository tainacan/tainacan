# Collections

## Collection Repository

<!-- BEGIN DOC-COMMENT H3 src/classes/repositories/class-tainacan-collections.php -->
### `protected function __construct()`

Collections constructor. 


### `public function get_map()`

{@inheritDoc} 
### `public function get_cpt_labels()`

Get the labels for the custom post type of this repository 


**Returns:** `array` — Labels in the format expected by register_post_type()

### `public function insert( $collection )`

{@inheritDoc} 
**Parameters:**

* `$collection` — \Tainacan\Entities\Collection

**Returns:** \Tainacan\Entities\Collection

### `public function delete( $args )`


**Parameters:**

* `(` — $args — is a array like [post_id, [is_permanently => bool]] )

**Returns:** mixed|Collection

### `public function fetch( $args = [], $output = null )`

fetch collection based on ID or WP_Query args 

Collections are stored as posts. Check WP_Query docs to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/) You can also use a mapped property, such as name and description, as an argument and it will be mapped to the appropriate WP_Query argument 


**Parameters:**

* `$args` — array — WP_Query args || int $args the collection id
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `\WP_Query|Array` — an instance of wp query OR array of entities;

### `public function map_meta_cap( $caps, $cap, $user_id, $args )`

Filter to handle special permissions 


<!-- END DOC-COMMENT -->

## Collection Entity

<!-- BEGIN DOC-COMMENT H3 src/classes/entities/class-tainacan-collection.php -->
### `function validate()`

Validate Collection 


**Returns:** bool

<!-- END DOC-COMMENT -->