# Items

## Items Repository

<!-- BEGIN DOC-COMMENT H3 src/classes/repositories/class-tainacan-items.php -->
### `public function get_cpt_labels()`

Get generic labels for the custom post types created for each collection 


**Returns:** `array` — Labels in the format expected by register_post_type()

### `public function register_post_type()`

Register each Item post_type {@inheritDoc} 


### `public function fetch( $args = [], $collections = [], $output = null )`

fetch items based on ID or WP_Query args 

Items are stored as posts. Check WP_Query docs to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/) You can also use a mapped property, such as name and description, as an argument and it will be mapped to the appropriate WP_Query argument 

The second paramater specifies from which collections item should be fetched. You can pass the Collection ID or object, or an Array of IDs or collection objects 


**Parameters:**

* `$args` — array — WP_Query args || int $args the item id
* `$collections` — array — Array Entities\Collection || Array int collections IDs || int collection id || Entities\Collection collection object
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `\WP_Query|Array` — an instance of wp query OR array of entities;

### `if ( ! isset( $args['post_status'] ) )`

If no specific status is defined in the query, WordPress will fetch public items and private items for users withe the correct permission. 

If a collection is private, it must have the same behavior, despite its items are public or not. 


### `public function fetch_ids( $args = [], $collections = [] )`

fetch items IDs based on WP_Query args 

to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/) You can also use a mapped property, such as name and description, as an argument and it will be mapped to the appropriate WP_Query argument 

The second paramater specifies from which collections item should be fetched. You can pass the Collection ID or object, or an Array of IDs or collection objects 


**Parameters:**

* `$args` — array — WP_Query args || int $args the item id
* `$collections` — array — Array Entities\Collection || Array int collections IDs || int collection id || Entities\Collection collection object

**Returns:** `Array` — array of IDs;

### `public function delete( $args )`


**Parameters:**

* `(` — $args — is a array like [post_id, [is_permanently => bool]] )

**Returns:** mixed|Entities\Item

### `public function title_in_posts_where( $where, $wp_query )`

allow wp query filter post by array of titles 


**Returns:** string

### `public function content_in_posts_where( $where, $wp_query )`

allow wp query filter post by array of content 


**Returns:** string

<!-- END DOC-COMMENT -->

## Item Entity

<!-- BEGIN DOC-COMMENT H3 src/classes/entities/class-tainacan-item.php -->
### `class Item extends Entity`

Represents the Entity Item 


### `protected $repository = 'Items'`

{@inheritDoc} 
### `function __construct( $which = 0 )`

{@inheritDoc} 


### `function set_terms( $value )`


### `function get_terms()`


**Returns:** mixed|null

### `function get_attachments()`


**Returns:** array

### `function get_author_name()`


**Returns:** string

### `function get_featured_image()`


**Returns:** false|string

### `function set_featured_img_id( $id )`


### `function get_featured_img_id()`


**Returns:** int|string

### `function get_modification_date()`


**Returns:** mixed|null

### `function get_creation_date()`


**Returns:** mixed|null

### `function get_author_id()`


**Returns:** mixed|null

### `function get_url()`


**Returns:** mixed|null

### `function get_id()`

Return the item ID 


**Returns:** integer

### `function get_title()`

Return the item title 


**Returns:** string

### `function get_order()`

Return the item order type 


**Returns:** string

### `function get_parent()`

Return the parent ID 


**Returns:** integer

### `function get_description()`

Return the item description 


**Returns:** string

### `public function get_db_identifier()`



{@inheritDoc} 
### `public function get_capabilities()`

Use especial Item capabilities {@inheritDoc} 


### `function set_title( $value )`

Define the title 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_order( $value )`

Define the order type 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_parent( $value )`

Define the parent ID 


**Parameters:**

* `$value` — [integer]

**Returns:** void

### `function set_description( $value )`

Define the description 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function get_fields()`

Return a List of ItemMetadata objects 

It will return all fields associeated with the collection this item is part of. 

If the item already has a value for any of the fields, it will be available. 


**Returns:** `array` — Array of ItemMetadata objects

### `protected function set_cap()`

set meta cap object 


### `function validate()`



{@inheritDoc} 
### `public function validate_core_fields()`

{@inheritDoc} 
<!-- END DOC-COMMENT -->