# Filters

## Filter Repository

<!-- BEGIN DOC-COMMENT H3 src/classes/repositories/class-tainacan-filters.php -->
### `public function delete($args)`


**Parameters:**

* `$args` — array

**Returns:** Entities\Filter

### `public function fetch($args = [], $output = null)`

fetch filter based on ID or WP_Query args 

Filters are stored as posts. Check WP_Query docs to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/) You can also use a mapped property, such as name and description, as an argument and it will be mapped to the appropriate WP_Query argument 


**Parameters:**

* `$args` — array — WP_Query args || int $args the filter id
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `\WP_Query|Array` — an instance of wp query OR array of entities;

### `public function register_filter_type( $class_name )`

register field types class on array of types 


**Parameters:**

* `string` — $class_name — | object The class name or the instance

### `public function deregister_filter_type( $class_name )`

register field types class on array of types 


**Parameters:**

* `string` — $class_name — | object The class name or the instance

### `public function fetch_filter_types( $output = 'CLASS')`

fetch all registered filter type classes 

Possible outputs are: CLASS (default) - returns the Class name of of filter types registered NAME - return an Array of the names of filter types registered 


**Parameters:**

* `string` — $output — CLASS | NAME

**Returns:** `array` — of Entities\Filter_Types\Filter_Type classes path name

### `public function fetch_supported_filter_types($types)`

fetch only supported filters for the type specified 


**Parameters:**

* `string` — ( — || array )  $types Primitve types of field ( float, string, int)

**Returns:** `array` — Filters supported by the primitive types passed in $types

### `public function fetch_by_collection(Entities\Collection $collection, $args = [], $output = null)`

fetch filters by collection, searches all filters available 


**Parameters:**

* `$collection` — Entities\Collection
* `$args` — array — WP_Query args plus disabled_fields
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `Array` — Entities\Field

### `public function order_result( $result, Entities\Collection $collection )`

Ordinate the result from fetch response if $collection has an ordination, filters not ordinated appear on the end of the list 




**Parameters:**

* `Response` — $result — from method fetch
* `$collection` — Entities\Collection

**Returns:** `array` — or WP_Query ordinate

<!-- END DOC-COMMENT -->

## Filter Entity

<!-- BEGIN DOC-COMMENT H3 src/classes/entities/class-tainacan-filter.php -->
### `class Filter extends Entity`

Represents the entity Filter 


### `protected $repository = 'Filters'`

{@inheritDoc} 
### `public function _toArray()`


**Returns:** array

### `function get_name()`

Return the filter name 


**Returns:** string

### `function get_description()`


**Returns:** mixed|null

### `function get_order()`

Return the filter order type 


**Returns:** string

### `function get_color()`

Return the filter color 


**Returns:** string

### `function get_field()`

Return the field 


**Returns:** Field

### `function get_filter_type_object()`

Return the an object child of \Tainacan\Filter_Types\Filter_Type with options 


**Returns:** `\Tainacan\Filter_Types\Filter_Type` — The filter type class with filled options

### `function get_filter_type()`

Return the class name for the filter type 


**Returns:** `string` — The

### `function get_filter_options()`

Return the actual options for the current filter type 


**Returns:** `array` — Configurations for the filter type object

### `function set_name($value)`

Define the filter name 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_order($value)`

Define the filter order type 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_description($value)`

Define the filter description 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_color( $value )`

Define the filter color 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_field( $value )`

Define the filter field 


**Returns:** void

### `public function set_filter_type($value)`

Save the filter type class name 


**Parameters:**

* `|` — string — \Tainacan\Filter_Types\Filter_Type $value The name of the class or the instance

### `public function validate()`

{@inheritdoc } 

Also validates the field, calling the validate_options callback of the Field Type 


**Returns:** `bool` — valid or not

<!-- END DOC-COMMENT -->