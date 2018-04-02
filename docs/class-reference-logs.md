# Logs

## Logs Repository

<!-- BEGIN DOC-COMMENT H3 src/classes/repositories/class-tainacan-logs.php -->
### `class Logs extends Repository`

Implement a Logs system 


### `public function register_post_type()`



{@inheritDoc} 
### `public function fetch($args = [], $output = null)`

fetch logs based on ID or WP_Query args 

Logs are stored as posts. Check WP_Query docs to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/) You can also use a mapped property, such as name and description, as an argument and it will be mapped to the appropriate WP_Query argument 


**Parameters:**

* `$args` — array — WP_Query args || int $args the log id
* `$output` — string — The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

**Returns:** `\WP_Query|Array` — an instance of wp query OR array of entities;

### `public function log_inserts($new_value, $value = null)`

Insert a log when a new entity is inserted 
**Parameters:**

* `$new_value` — Entity
* `$value` — Entity

**Returns:** `Entities\Log` — new created log

### `public function approve($log)`




**Parameters:**

* `$log` — Entities\Log

**Returns:** `Entities\Entity|boolean` — return insert/update valeu or false

### `$value = $log->get_value()`


<!-- END DOC-COMMENT -->

## Log Entity

<!-- BEGIN DOC-COMMENT H3 src/classes/entities/class-tainacan-log.php -->
### `class Log extends Entity`

Represents entity Log 


### `protected $repository = 'Logs'`

{@inheritDoc} 
### `function get_title()`

Return the Log title 


**Returns:** string

### `function get_order()`

Return the log order type 


**Returns:** string

### `function get_parent()`

Retun the parent ID 


**Returns:** integer

### `function get_description()`

Return the Log description 


**Returns:** string

### `function get_blog_id()`

Return the ID of blog 


**Returns:** integer

### `function get_user_id()`

Return User Id of who make the action 


**Returns:** `int` — User Id of logged action

### `public function get_value()`

Get value of log entry 


**Parameters:**

* `$value` — mixed

**Returns:** void

### `public function get_old_value()`

Get old value of log entry object 


**Parameters:**

* `$value` — mixed

**Returns:** void

### `function set_title($value)`

Set log tittle 


**Parameters:**

* `$value` — string

**Returns:** void

### `function set_order($value)`

Define the order type 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `function set_parent($value)`

Define the parent ID 


**Parameters:**

* `$value` — [integer]

**Returns:** void

### `function set_description($value)`

Define the Log description 


**Parameters:**

* `$value` — [string]

**Returns:** void

### `protected function set_user_id($value = 0)`

Define the user ID of log entry 


**Parameters:**

* `$value` — [integer]

**Returns:** void

### `protected function set_blog_id($value = 0)`

Define the blog ID of log entry 


**Parameters:**

* `$value` — [integer]

**Returns:** void

### `protected function set_value($value = null)`

Define the value of log entry 


**Parameters:**

* `$value` — [mixed]

**Returns:** void

### `protected function set_old_value($value = null)`

Set old value of log entry 


**Parameters:**

* `$value` — [mixed]

**Returns:** void

### `public static function create($msn = false, $desc = '', $new_value = null, $old_value = null, $status = 'publish')`




**Parameters:**

* `$msn` — boolean|string
* `$desc` — string
* `$new_value` — mixed
* `$old_value` — mixed
* `$status` — string — 'publish', 'private' or 'pending'

**Returns:** \Tainacan\Entities\Log

### `public function approve()`

{@inheritDoc} 
<!-- END DOC-COMMENT -->