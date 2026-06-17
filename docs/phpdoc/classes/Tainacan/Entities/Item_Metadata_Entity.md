# Item_Metadata_Entity


Represents a Tainacan Item Metadata Entity.

Represents the relationship between items and their metadata values,
storing the actual data content for specific metadata fields.

***

* Full name: `\Tainacan\Entities\Item_Metadata_Entity`
* Parent class: [`\Tainacan\Entities\Entity`](./Entity)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Item_Metadata_Entity {
        #post_type : mixed
        #repository : string
        #item : mixed
        +__construct(item, metadatum, meta_id, parent_meta_id)
        +get_multivalue_prefix()
        +get_multivalue_suffix()
        +get_multivalue_separator()
        +get_value_as_html()
        +get_value_as_string()
        +get_value_as_array()
        +_toArray(formatted_values, cascade)
        +set_item(item)
        +set_value(value)
        +set_metadatum(metadatum)
        +set_meta_id(meta_id)
        +set_parent_meta_id(parent_meta_id)
        +get_item()
        +get_metadatum()
        +get_meta_id()
        +get_parent_meta_id()
        +get_value()
        +has_value()
        +is_multiple()
        +is_collection_key()
        +is_required()
        +validate()
    }
    class Entity {
        #repository : Repository
        -errors : array
        #post_type : string|false
        #capability_type : string|false
        +WP_Post : WP_Post
        -validated : bool
        +cap : object
        +__construct(which)
        +get_repository()
        +get_date_i18n(date)
        +get_mapped_property(prop)
        #set_mapped_property(prop, value)
        +set(prop, value)
        +get(prop)
        +set_status(value)
        +validate()
        +validate_prop(prop)
        +get_errors()
        +$get_post_type()
        +$get_capability_type()
        +get_status()
        +get_db_identifier()
        +get_id()
        +add_error(type, message)
        +reset_errors()
        +get_validated()
        #set_validated(value)
        #set_as_valid()
        +_toArray()
        +_toJson()
        +can_read(user)
        +can_edit(user)
        +can_delete(user)
        +can_publish(user)
        +get_capabilities()
        +diff(which)
    }
    Entity ..> Entity
    Entity <|-- Item_Metadata_Entity
    Item_Metadata_Entity ..> Item_Metadata_Entity
```

## Properties

### post_type

The WordPress post type for storing this entity.

```php
protected static string|false $post_type
```

Set to false if not using WordPress post types.

* This property is **static**.

***

### repository

The repository instance for this entity.

```php
protected string $repository
```

**See Also:**

* \Tainacan\Entities\Entity::repository

***

### item

```php
protected $item
```

***

### metadatum

```php
protected $metadatum
```

***

### parent_meta_id

```php
protected $parent_meta_id
```

***

### meta_id

```php
protected $meta_id
```

***

### has_value

```php
protected $has_value
```

***

### value

```php
protected $value
```

***

## Methods

### __construct

Create an instance of Entity

```php
public __construct(\Tainacan\Entities\Item $item = null, \Tainacan\Entities\Metadatum $metadatum = null, int $meta_id = null, mixed $parent_meta_id = null): mixed
```

**Parameters:**

| Parameter         | Type                             | Description                |
|-------------------|----------------------------------|----------------------------|
| `$item`           | **\Tainacan\Entities\Item**      | Item Entity                |
| `$metadatum`      | **\Tainacan\Entities\Metadatum** | Metadatum Entity           |
| `$meta_id`        | **int**                          | ID for a specific meta row |
| `$parent_meta_id` | **mixed**                        |                            |

***

### get_multivalue_prefix

Gets the string used before each value when concatenating multiple values
to display item metadata value as html or string

```php
public get_multivalue_prefix(): string
```

***

### get_multivalue_suffix

Gets the string used after each value when concatenating multiple values
to display item metadata value as html or string

```php
public get_multivalue_suffix(): string
```

***

### get_multivalue_separator

Gets the string used in between each value when concatenating multiple values
to display item metadata value as html or string

```php
public get_multivalue_separator(): string
```

***

### get_value_as_html

Get the value as a HTML string, with markup and links

```php
public get_value_as_html(): string
```

***

### get_value_as_string

Get the value as a plain text string

```php
public get_value_as_string(): string
```

***

### get_value_as_array

Get value as an array

```php
public get_value_as_array(): mixed
```

***

### _toArray

Convert the object to an Array

```php
public _toArray(bool $formatted_values = true, bool $cascade = false): array
```

**Parameters:**

| Parameter           | Type     | Description                                                                 |
|---------------------|----------|-----------------------------------------------------------------------------|
| `$formatted_values` | **bool** | Whether to add or not values formatted as html and string to the response   |
| `$cascade`          | **bool** | Whether to add or not Item and Metadatum Entities as arrays to the response |

**Return Value:**

the representation of this object as an array

***

### set_item

Define the item

```php
public set_item(\Tainacan\Entities\Item $item = null): void
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### set_value

Define the metadatum value

```php
public set_value(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_metadatum

Define the metadatum

```php
public set_metadatum(\Tainacan\Entities\Metadatum $metadatum = null): void
```

**Parameters:**

| Parameter    | Type                             | Description |
|--------------|----------------------------------|-------------|
| `$metadatum` | **\Tainacan\Entities\Metadatum** |             |

***

### set_meta_id

Set the specific meta ID for this metadata.

```php
public set_meta_id(int $meta_id): mixed
```

When this value is set, get_value() will use it to fetch the value from
the post_meta table, instead of considering the item and metadatum IDs

**Parameters:**

| Parameter  | Type    | Description                         |
|------------|---------|-------------------------------------|
| `$meta_id` | **int** | the ID of a specifica post_meta row |

***

### set_parent_meta_id

Set parent_meta_id. Used when a item_metadata is inside a compound Metadatum

```php
public set_parent_meta_id(mixed $parent_meta_id): mixed
```

When you have a multiple compound metadatum, this indicates of which instace of the value this item_metadata is attached to

**Parameters:**

| Parameter         | Type      | Description |
|-------------------|-----------|-------------|
| `$parent_meta_id` | **mixed** |             |

***

### get_item

Return the item

```php
public get_item(): \Tainacan\Entities\Item
```

***

### get_metadatum

Return the metadatum

```php
public get_metadatum(): \Tainacan\Entities\Metadatum
```

***

### get_meta_id

Return the meta_id

```php
public get_meta_id(): \Tainacan\Entities\Metadatum
```

***

### get_parent_meta_id

Return the meta_id

```php
public get_parent_meta_id(): \Tainacan\Entities\Metadatum
```

***

### get_value

Return the metadatum value

```php
public get_value(): string|int|array
```

***

### has_value

Check whether the item has a value stored in the database or not

```php
public has_value(): bool
```

***

### is_multiple

Return true if metadatum is multiple, else return false

```php
public is_multiple(): bool
```

***

### is_collection_key

Return true if metadatum is key

```php
public is_collection_key(): bool
```

***

### is_required

Return true if metadatum is required

```php
public is_required(): bool
```

***

### validate

Returns whether metadata value is valid

```php
public validate(): bool
```

***

## Inherited methods

### __construct

Create an instance of Entity

```php
public __construct(mixed $which): mixed
```

If ID or WP Post is passed, it retrieves the object from the database

Attention: If the ID or Post provided do not match the Entity post type, an Exception will be thrown

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$which`  | **mixed** |             |

**Throws:**

- [`Exception`](../../Exception)

***

### get_repository

```php
public get_repository(): mixed
```

***

### get_date_i18n

```php
public get_date_i18n(mixed $date): string
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$date`   | **mixed** |             |

***

### get_mapped_property

return the value for a mapped property

```php
public get_mapped_property(string $prop): mixed
```

**Parameters:**

| Parameter | Type       | Description    |
|-----------|------------|----------------|
| `$prop`   | **string** | id of property |

**Return Value:**

property value

***

### set_mapped_property

set the value of a mapped property

```php
protected set_mapped_property(string $prop, mixed $value): mixed
```

This is a protected method. If you want to set an entity prop
using the prop name dynamically, use the set() method

**Parameters:**

| Parameter | Type       | Description            |
|-----------|------------|------------------------|
| `$prop`   | **string** | id of the property     |
| `$value`  | **mixed**  | the value to be setted |

***

### set

set the value property

```php
public set(string $prop, mixed $value): null|mixed
```

**Parameters:**

| Parameter | Type       | Description            |
|-----------|------------|------------------------|
| `$prop`   | **string** | id of the property     |
| `$value`  | **mixed**  | the value to be setted |

**Return Value:**

Null on failure, the value that was set on success

***

### get

get the value property

```php
public get(string $prop): null|mixed
```

**Parameters:**

| Parameter | Type       | Description        |
|-----------|------------|--------------------|
| `$prop`   | **string** | id of the property |

**Return Value:**

Null on failure, the value that was set on success

***

### set_status

set the status of the entity

```php
public set_status(string $value): mixed
```

**Parameters:**

| Parameter | Type       | Description |
|-----------|------------|-------------|
| `$value`  | **string** |             |

***

### validate

Validate the class values/properties, to be used before insert/save/update

```php
public validate(): bool
```

If Entity is not valid, validation error messages are available via get_errors() method

***

### validate_prop

Validate a single property

```php
public validate_prop(string $prop): bool
```

**Parameters:**

| Parameter | Type       | Description                       |
|-----------|------------|-----------------------------------|
| `$prop`   | **string** | id of the property to be validate |

***

### get_errors

```php
public get_errors(): mixed
```

***

### get_post_type

```php
public static get_post_type(): mixed
```

* This method is **static**.
***

### get_capability_type

```php
public static get_capability_type(): mixed
```

* This method is **static**.
***

### get_status

```php
public get_status(): mixed
```

***

### get_db_identifier

Get entity DB identifier

```php
public get_db_identifier(): string
```

This identifier is used to register the entity on database, ex.: post_type

***

### get_id

Get the entity ID

```php
public get_id(): int
```

***

### add_error

```php
public add_error(mixed $type, mixed $message): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$type`    | **mixed** |             |
| `$message` | **mixed** |             |

***

### reset_errors

Clear the errors array

```php
public reset_errors(): mixed
```

***

### get_validated

```php
public get_validated(): mixed
```

***

### set_validated

```php
protected set_validated(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_as_valid

```php
protected set_as_valid(): mixed
```

***

### _toArray

```php
public _toArray(): mixed
```

***

### _toJson

```php
public _toJson(): mixed
```

***

### can_read

Return if user can read this entity

```php
public can_read(int|\WP_User $user = null): bool
```

**Parameters:**

| Parameter | Type              | Description |
|-----------|-------------------|-------------|
| `$user`   | **int\|\WP_User** |             |

***

### can_edit

Return if user can edit this entity

```php
public can_edit(int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                    | Description                                              |
|-----------|-------------------------|----------------------------------------------------------|
| `$user`   | **int\|\WP_User\|null** | the user for capability check, null for the current user |

***

### can_delete

Return if user can delete this entity

```php
public can_delete(int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                    | Description                                              |
|-----------|-------------------------|----------------------------------------------------------|
| `$user`   | **int\|\WP_User\|null** | the user for capability check, null for the current user |

***

### can_publish

Return if user can publish this entity

```php
public can_publish(int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                    | Description                                              |
|-----------|-------------------------|----------------------------------------------------------|
| `$user`   | **int\|\WP_User\|null** | the user for capability check, null for the current user |

***

### get_capabilities

Get the capabilities list for the post type of the entity

```php
public get_capabilities(): object
```

**Return Value:**

Object with all the capabilities as member variables.

***

### diff

Compare this entity props with self old values or with $which other entity

```php
public diff(\Tainacan\Entities\Entity|int|\WP_Post $which): array
```

**Parameters:**

| Parameter | Type                                         | Description                                             |
|-----------|----------------------------------------------|---------------------------------------------------------|
| `$which`  | **\Tainacan\Entities\Entity\|int\|\WP_Post** | default ($which = 0) to self compare with stored entity |

***
