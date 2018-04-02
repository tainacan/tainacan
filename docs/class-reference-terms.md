# Terms

## Terms Repository

<!-- BEGIN DOC-COMMENT H3 src/classes/repositories/class-tainacan-terms.php -->
### `class Terms extends Repository`

Class Tainacan_Terms 
### `public function insert($term)`


**Parameters:**

* `$term` — Entities\Entity

**Returns:** Entities\Entity|Entities\Term

### `public function fetch( $args = [], $taxonomies = [])`

fetch terms based on ID or get terms args 

Terms are stored as WordPress regular terms. Check (@see https://developer.wordpress.org/reference/functions/get_terms/) get_terms() docs to learn all args accepted in the $args parameter 

The second paramater specifies from which taxonomies terms should be fetched. You can pass the Taxonomy ID or object, or an Array of IDs or taxonomies objects 


**Parameters:**

* `$args` — array — WP_Query args || int $args the term id
* `$taxonomies` — array — Array Entities\Taxonomy || Array int terms IDs || int collection id || Entities\Taxonomy taxonomy object

**Returns:** `array` — of Entities\Term objects || Entities\Term

<!-- END DOC-COMMENT -->

## Term Entity

<!-- BEGIN DOC-COMMENT H3 src/classes/entities/class-tainacan-term.php -->
### `class Term extends Entity`

Represents the Entity Term 
### `protected $repository = 'Terms'`

{@inheritDoc} 
### `function __construct($which = 0, $taxonomy = false )`

Term constructor. 


**Parameters:**

* `$which` — int
* `$taxonomy` — string

### `function get_id()`

Return the unique identifier 


**Returns:** integer

### `function get_name()`

Return the name 


**Returns:** string

### `function get_parent()`

Return the parent ID 


**Returns:** integer

### `function get_description()`

Return the description 


**Returns:** string

### `function get_user()`

Return the user ID 


**Returns:** integer

### `function get_taxonomy()`

Return the taxonomy 


**Returns:** integer

### `function set_name($value)`

Define the name 


**Parameters:**

* `$value` — [string]

### `function set_parent($value)`

Define the parent ID 


**Parameters:**

* `$value` — [integer]

### `function set_description($value)`

Define the description 


**Parameters:**

* `$value` — [string]

### `function set_user($value)`

Define the user associated 


**Parameters:**

* `$value` — [integer]

### `function set_taxonomy($value)`

Define the taxonomy associated 


**Parameters:**

* `$value` — [integer]

<!-- END DOC-COMMENT -->