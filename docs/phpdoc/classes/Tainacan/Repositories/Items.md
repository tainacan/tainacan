# Items


Repository for managing Tainacan items.

Handles all database operations for items including creation,
updates, deletion, and querying with proper validation and logging.

***

* Full name: `\Tainacan\Repositories\Items`
* Parent class: [`\Tainacan\Repositories\Repository`](./Repository)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Items {
        +entities_type : mixed
        -fetching_from_collections : mixed
        #init()
        #_get_map()
        +get_cpt_labels()
        +register_post_type()
        +insert(item)
        +fetch(args, collections, output)
        +fetch_ids(args, collections)
        +_filter_where(where, wp_query)
        +update(object, new_values)
        +generate_index_content(item)
        +get_thumbnail_id_from_document(item)
        +hook_api_updated_item(updated_item, attributes)
        +hook_comments_open(comments_open, post_id)
        +map_meta_cap(caps, cap, user_id, args)
        -get_related_items_by_collection(item, collection, metadata, args)
        +fetch_related_items(item, args)
        -parse_relationship_metaquery(args)
        +posts_where_relationship_metaquery(where)
        -parse_core_metadata_for_advanced_search(args, collections)
    }
    class Repository {
        +entities_type : string
        #use_logs : bool
        #logs_repository : Logs
        -map : array
        +disable_logs()
        +enable_logs()
        +get_enabled_logs()
        #__construct()
        +init_objects()
        +get_map()
        +get_name()
        +insert(obj)
        +insert_metadata(obj, prop, diffs)
        +maybe_add_slashes(value)
        +fetch_output(WP_Query, output)
        +parse_fetch_args(args)
        +get_default_properties(map)
        +get_mapped_property(entity, prop)
        +$get_collections_db_identifiers()
        +$get_entity_by_post(post)
        +$get_entity_by_post_type(post_type, post)
        +$get_repository(entity)
        +fetch_one(args)
        +trash(entity)
        +delete(entity, permanent)
        +can_edit(entity, user)
        +can_read(entity, user)
        +can_delete(entity, user)
        +can_publish(entity, user)
        +unique_multidimensional_array(array, key)
        -insert_thumbnail(obj, diffs)
        +get_descendants_ids(id, depth)
        +get_capabilities()
        #sanitize_value(content)
    }
    Repository ..> Items
    Repository ..> Repository
    Repository <|-- Items
```

## Properties

### entities_type

The entity type this repository manages.

```php
public string $entities_type
```

***

### fetching_from_collections

```php
private $fetching_from_collections
```

***

## Methods

### init

```php
protected init(): mixed
```

***

### _get_map

return properties map

```php
protected _get_map(): array
```

**Return Value:**

properties map array, format like:
  'id'             => [
    'map'        => 'ID',
    'title'       => __('ID', 'tainacan'),
    'type'       => 'integer',
    'description'=> __('Unique identifier', 'tainacan'),
    'validation' => v::numeric(),
],
'name'           =>  [
    'map'        => 'post_title',
    'title'       => __('Name', 'tainacan'),
    'type'       => 'string',
    'description'=> __('Name of the collection', 'tainacan'),
    'validation' => v::stringType(),
    'default'     => ''
],
'slug'           =>  [
    'map'        => 'post_name',
    'title'       => __('Slug', 'tainacan'),
    'type'       => 'string',
    'description'=> __('A unique and sanitized string representation of the collection, used to build the collection URL', 'tainacan'),
    'validation' => v::stringType(),
],

***

### get_cpt_labels

Get generic labels for the custom post types created for each collection

```php
public get_cpt_labels(): array
```

**Return Value:**

Labels in the format expected by register_post_type()

**See Also:**

* \Tainacan\Entities\Collection::register_collection_item_post_type()

***

### register_post_type

Register each Item post_type
{@inheritDoc}

```php
public register_post_type(): mixed
```

**See Also:**

* \Tainacan\Repositories\Repository::register_post_type()

***

### insert

```php
public insert(mixed $item): \Tainacan\Entities\Entity|bool
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$item`   | **mixed** |             |

***

### fetch

fetch items based on ID or WP_Query args

```php
public fetch(array $args = [], array $collections = [], string $output = null): \WP_Query|array|\Tainacan\Entities\Item
```

Items are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Item object.  But if the post is not found or
does not match the entity post type, it will return an empty array

The second paramater specifies from which collections item should be fetched.
You can pass the Collection ID or object, or an Array of IDs or collection objects

**Parameters:**

| Parameter      | Type       | Description                                                                                                                |
|----------------|------------|----------------------------------------------------------------------------------------------------------------------------|
| `$args`        | **array**  | WP_Query args \|\| int $args the item id                                                                                   |
| `$collections` | **array**  | Array Entities\Collection \|\| Array int collections IDs \|\| int collection id \|\| Entities\Collection collection object |
| `$output`      | **string** | The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)                     |

**Return Value:**

an instance of wp query OR array of entities OR a Item;

***

### fetch_ids

fetch items IDs based on WP_Query args

```php
public fetch_ids(array $args = [], array $collections = []): array
```

to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

The second paramater specifies from which collections item should be fetched.
You can pass the Collection ID or object, or an Array of IDs or collection objects

**Parameters:**

| Parameter      | Type      | Description                                                                                                                |
|----------------|-----------|----------------------------------------------------------------------------------------------------------------------------|
| `$args`        | **array** | WP_Query args \|\| int $args the item id                                                                                   |
| `$collections` | **array** | Array Entities\Collection \|\| Array int collections IDs \|\| int collection id \|\| Entities\Collection collection object |

**Return Value:**

array of IDs;

***

### _filter_where

When querying posts without explictly asking for a post_status, WordPress will
check current user capabilities and return posts user can read based on read_private_posts capabilities.

```php
public _filter_where(string $where, \WP_Query $wp_query): string
```

However, when querying for multiple post types, WordPress does not handle a per post type permission check. It either
return only public posts or all private posts if read_private_multiple_post_types cap is present.

This hook fixes this, modifying the where clause.

**Parameters:**

| Parameter   | Type          | Description       |
|-------------|---------------|-------------------|
| `$where`    | **string**    | the wehere clause |
| `$wp_query` | **\WP_Query** |                   |

**Return Value:**

The modified where clause

***

### update

```php
public update(mixed $object, mixed $new_values = null): mixed
```

**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$object`     | **mixed** |             |
| `$new_values` | **mixed** |             |

***

### generate_index_content

generate a content of document to index.

```php
public generate_index_content(\Tainacan\Entities\Item $item): bool
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** | The item    |

***

### get_thumbnail_id_from_document

Get a default thumbnail ID from the item document.

```php
public get_thumbnail_id_from_document(\Tainacan\Entities\Item $item): int|null
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** | The item    |

**Return Value:**

The thumbnail ID or null if it was not possible to find a thumbnail

***

### hook_api_updated_item

When updating an item document, set a default thumbnail to the item if it does not have one yet

```php
public hook_api_updated_item(\Tainacan\Entities\Item $updated_item, array $attributes): void
```

**Parameters:**

| Parameter       | Type                        | Description                    |
|-----------------|-----------------------------|--------------------------------|
| `$updated_item` | **\Tainacan\Entities\Item** |                                |
| `$attributes`   | **array**                   | The paramaters sent to the API |

***

### hook_comments_open

Return if comment are open for this item (post_id) and the collection too

```php
public hook_comments_open(bool $comments_open, int $post_id): bool
```

**Parameters:**

| Parameter        | Type     | Description |
|------------------|----------|-------------|
| `$comments_open` | **bool** |             |
| `$post_id`       | **int**  | Item id     |

***

### map_meta_cap

Filter to handle special permissions

```php
public map_meta_cap(mixed $caps, mixed $cap, mixed $user_id, mixed $args): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$caps`    | **mixed** |             |
| `$cap`     | **mixed** |             |
| `$user_id` | **mixed** |             |
| `$args`    | **mixed** |             |

**See Also:**

* https://developer.wordpress.org/reference/hooks/map_meta_cap/

***

### get_related_items_by_collection

```php
private get_related_items_by_collection(mixed $item, mixed $collection, mixed $metadata, mixed $args = []): mixed
```

**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$item`       | **mixed** |             |
| `$collection` | **mixed** |             |
| `$metadata`   | **mixed** |             |
| `$args`       | **mixed** |             |

***

### fetch_related_items

```php
public fetch_related_items(mixed $item, mixed $args = []): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$item`   | **mixed** |             |
| `$args`   | **mixed** |             |

***

### parse_relationship_metaquery

```php
private parse_relationship_metaquery(mixed $args): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$args`   | **mixed** |             |

***

### posts_where_relationship_metaquery

```php
public posts_where_relationship_metaquery(mixed $where): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$where`  | **mixed** |             |

***

### parse_core_metadata_for_advanced_search

checks if there is `tainacan_core_title` or `tainacan_core_description` as a key for a meta_query,
and replaces the ids of the metadata referring to `title_core` and `description_core`.

```php
private parse_core_metadata_for_advanced_search(array $args, array $collections = []): mixed
```

**Parameters:**

| Parameter      | Type      | Description                          |
|----------------|-----------|--------------------------------------|
| `$args`        | **array** | WP_Query args                        |
| `$collections` | **array** | Array \Taainacan\Entities\Collection |

***

## Inherited methods

### disable_logs

Disables creation of logs while inserting and updating entities.

```php
public disable_logs(): void
```

***

### enable_logs

Enables creation of logs while inserting and updating entities.

```php
public enable_logs(): void
```

***

### get_enabled_logs

Gets whether creation of logs while inserting and updating entities is enabled.

```php
public get_enabled_logs(): bool
```

**Return Value:**

True if logging is enabled, false otherwise.

***

### __construct

```php
private __construct(): mixed
```

***

### init_objects

```php
public init_objects(): mixed
```

***

### _get_map

return properties map

```php
protected _get_map(): array
```

* This method is **abstract**.
**Return Value:**

properties map array, format like:
  'id'             => [
    'map'        => 'ID',
    'title'       => __('ID', 'tainacan'),
    'type'       => 'integer',
    'description'=> __('Unique identifier', 'tainacan'),
    'validation' => v::numeric(),
],
'name'           =>  [
    'map'        => 'post_title',
    'title'       => __('Name', 'tainacan'),
    'type'       => 'string',
    'description'=> __('Name of the collection', 'tainacan'),
    'validation' => v::stringType(),
    'default'     => ''
],
'slug'           =>  [
    'map'        => 'post_name',
    'title'       => __('Slug', 'tainacan'),
    'type'       => 'string',
    'description'=> __('A unique and sanitized string representation of the collection, used to build the collection URL', 'tainacan'),
    'validation' => v::stringType(),
],

***

### get_map

```php
public get_map(): mixed
```

***

### get_name

Return repository name

```php
public get_name(): string
```

**Return Value:**

The repository name

***

### insert

```php
public insert(\Tainacan\Entities\Entity $obj): \Tainacan\Entities\Entity|bool
```

**Parameters:**

| Parameter | Type                          | Description |
|-----------|-------------------------------|-------------|
| `$obj`    | **\Tainacan\Entities\Entity** |             |

**Throws:**

- [`Exception`](../../Exception)

***

### insert_metadata

Insert object property stored as postmeta into the database

```php
public insert_metadata(\Tainacan\Entities $obj, string $prop, mixed $diffs): null|false
```

**Parameters:**

| Parameter | Type                   | Description                                                 |
|-----------|------------------------|-------------------------------------------------------------|
| `$obj`    | **\Tainacan\Entities** | The entity object                                           |
| `$prop`   | **string**             | the property name, as declared in the map of the repository |
| `$diffs`  | **mixed**              |                                                             |

**Return Value:**

on error

***

### maybe_add_slashes

```php
public maybe_add_slashes(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### fetch_output

Prepare the output for the fetch() methods.

```php
public fetch_output(\WP_Query $WP_Query, string $output = 'WP_Query'): array|\WP_Query
```

Possible outputs are:
WP_Query (default) - returns the WP_Object itself
OBJECT - return an Array of Tainacan\Entities

**Parameters:**

| Parameter   | Type          | Description                                                                           |
|-------------|---------------|---------------------------------------------------------------------------------------|
| `$WP_Query` | **\WP_Query** |                                                                                       |
| `$output`   | **string**    | `WP_Query` for a single WP_Query object or `OBJECT` for an array of Tainacan\Entities |

***

### parse_fetch_args

Maps repository mapped properties to WP_Query arguments.

```php
public parse_fetch_args(array $args = []): array
```

This allows to build fetch arguments using both WP_Query arguments
and the mapped properties for the repository.

For example, you can use any of the following methods to browse collections by name:
$TainacanCollections->fetch(['title' => 'test']);
$TainacanCollections->fetch(['name' => 'test']);

The property `name` is transformed into the native WordPress property `post_title`. (actually only title for query purpouses)

Example 2, this also works with properties mapped to postmeta. The following methods are the same:
$TainacanMetadatas->fetch(['required' => 'yes']);
$TainacanMetadatas->fetch(['meta_query' => [
    [
        'key' => 'required',
        'value' => 'yes'
    ]
]]);

**Parameters:**

| Parameter | Type      | Description   |
|-----------|-----------|---------------|
| `$args`   | **array** | [description] |

**Return Value:**

$args new $args array with mapped properties

***

### get_default_properties

Return default properties

```php
public get_default_properties(array $map): array
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$map`    | **array** |             |

***

### get_mapped_property

return the value for a mapped property from database

```php
public get_mapped_property(\Tainacan\Entities\Entity $entity, string $prop): mixed
```

**Parameters:**

| Parameter | Type                          | Description    |
|-----------|-------------------------------|----------------|
| `$entity` | **\Tainacan\Entities\Entity** |                |
| `$prop`   | **string**                    | id of property |

**Return Value:**

property value

***

### get_collections_db_identifiers

Return array of collections db identifiers

```php
public static get_collections_db_identifiers(): array[]
```

* This method is **static**.
***

### get_entity_by_post

```php
public static get_entity_by_post(int|\WP_Post $post): \Tainacan\Entities\Entity|bool
```

* This method is **static**.
**Parameters:**

| Parameter | Type              | Description |
|-----------|-------------------|-------------|
| `$post`   | **int\|\WP_Post** | \|Entity    |

**Throws:**

- [`Exception`](../../Exception)

***

### get_entity_by_post_type

```php
public static get_entity_by_post_type(string $post_type, int|\WP_Post $post): \Tainacan\Entities\Entity|bool
```

* This method is **static**.
**Parameters:**

| Parameter    | Type              | Description                                                    |
|--------------|-------------------|----------------------------------------------------------------|
| `$post_type` | **string**        |                                                                |
| `$post`      | **int\|\WP_Post** | optional post ID or WordPress post data for creation of Entity |

**Return Value:**

the entity for post_type, with data if $post is given or false

**Throws:**

- [`Exception`](../../Exception)

***

### get_repository

Return Entity's Repository

```php
public static get_repository(\Tainacan\Entities\Entity $entity): \Tainacan\Repositories\Repository|bool
```

* This method is **static**.
**Parameters:**

| Parameter | Type                          | Description |
|-----------|-------------------------------|-------------|
| `$entity` | **\Tainacan\Entities\Entity** |             |

**Return Value:**

return the entity Repository or false

***

### fetch_one

Fetch one Entity based on query args.

```php
public fetch_one(array $args): false|\Tainacan\Entities
```

Note: Does not work with Item_Metadata Repository

**Parameters:**

| Parameter | Type      | Description                     |
|-----------|-----------|---------------------------------|
| `$args`   | **array** | Query Args as expected by fetch |

**Return Value:**

The entity or false if none was found

***

### trash

Shortcut to delete($entity, false)

```php
public trash(\Tainacan\Entities\Entity $entity): mixed|\Tainacan\Entities\Entity
```

**Parameters:**

| Parameter | Type                          | Description |
|-----------|-------------------------------|-------------|
| `$entity` | **\Tainacan\Entities\Entity** |             |

**Return Value:**

@see https://developer.wordpress.org/reference/functions/wp_delete_post/

***

### delete

```php
public delete(\Tainacan\Entities\Entity $entity, bool $permanent = true): mixed|\Tainacan\Entities\Entity
```

**Parameters:**

| Parameter    | Type                          | Description                                                         |
|--------------|-------------------------------|---------------------------------------------------------------------|
| `$entity`    | **\Tainacan\Entities\Entity** |                                                                     |
| `$permanent` | **bool**                      | If false, sendo to trash, if true, permanently delete. Default true |

**Return Value:**

@see https://developer.wordpress.org/reference/functions/wp_delete_post/

***

### fetch

```php
public fetch(mixed $args, mixed $output = null): mixed
```

* This method is **abstract**.
**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$args`   | **mixed** |             |
| `$output` | **mixed** |             |

***

### update

```php
public update(mixed $object, mixed $new_values = null): mixed
```

* This method is **abstract**.
**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$object`     | **mixed** |             |
| `$new_values` | **mixed** |             |

***

### register_post_type

```php
public register_post_type(): mixed
```

* This method is **abstract**.
***

### can_edit

Check if $user can edit/create a entity

```php
public can_edit(\Tainacan\Entities\Entity $entity, int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                          | Description                          |
|-----------|-------------------------------|--------------------------------------|
| `$entity` | **\Tainacan\Entities\Entity** |                                      |
| `$user`   | **int\|\WP_User\|null**       | default is null for the current user |

**Throws:**

- [`Exception`](../../Exception)

***

### can_read

Check if $user can read the entity

```php
public can_read(\Tainacan\Entities\Entity $entity, int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                          | Description                          |
|-----------|-------------------------------|--------------------------------------|
| `$entity` | **\Tainacan\Entities\Entity** |                                      |
| `$user`   | **int\|\WP_User\|null**       | default is null for the current user |

**Throws:**

- [`Exception`](../../Exception)

***

### can_delete

Check if $user can delete the entity

```php
public can_delete(\Tainacan\Entities\Entity $entity, int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                          | Description                          |
|-----------|-------------------------------|--------------------------------------|
| `$entity` | **\Tainacan\Entities\Entity** |                                      |
| `$user`   | **int\|\WP_User\|null**       | default is null for the current user |

**Throws:**

- [`Exception`](../../Exception)

***

### can_publish

Check if $user can publish entity

```php
public can_publish(\Tainacan\Entities\Entity $entity, int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                          | Description                          |
|-----------|-------------------------------|--------------------------------------|
| `$entity` | **\Tainacan\Entities\Entity** |                                      |
| `$user`   | **int\|\WP_User\|null**       | default is null for the current user |

**Throws:**

- [`Exception`](../../Exception)

***

### unique_multidimensional_array

Removes duplicates from multidimensional array

```php
public unique_multidimensional_array(mixed $array, mixed $key): array
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$array`  | **mixed** |             |
| `$key`    | **mixed** |             |

***

### get_descendants_ids

Get IDs for all children, grand children till the depth parameter is reached

```php
public get_descendants_ids(int|\Tainacan\Entities\Entity $id, bool|int $depth = false): array
```

**Parameters:**

| Parameter | Type                               | Description                                                            |
|-----------|------------------------------------|------------------------------------------------------------------------|
| `$id`     | **int\|\Tainacan\Entities\Entity** | The Entity ID or object                                                |
| `$depth`  | **bool\|int**                      | The maximum depth to llok for descendants. default is false = no limit |

**Return Value:**

Array of IDs

***

### get_capabilities

Get the capabilities list for the post type of the entity

```php
public get_capabilities(): object
```

**Return Value:**

Object with all the capabilities as member variables.

***

### sanitize_value

```php
protected sanitize_value(mixed $content): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$content` | **mixed** |             |

***

### get_instance

```php
public static get_instance(): mixed
```

* This method is **static**.
***
