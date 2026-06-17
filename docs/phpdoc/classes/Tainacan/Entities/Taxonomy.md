# Taxonomy


Represents a Tainacan Taxonomy entity.

Taxonomies define hierarchical classification systems for organizing
and categorizing items within Tainacan collections.

***

* Full name: `\Tainacan\Entities\Taxonomy`
* Parent class: [`\Tainacan\Entities\Entity`](./Entity)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Taxonomy {
        #name : mixed
        +post_type : string
        #capability_type : string
        #repository : string
        +db_identifier_prefix : string
        +__toString()
        +tainacan_register_taxonomy()
        +get_capabilities()
        +get_name()
        +get_description()
        +get_allow_insert()
        +get_hierarchical()
        +get_slug()
        +get_enabled_post_types()
        +get_db_identifier()
        +set_name(value)
        +set_slug(value)
        +set_description(value)
        +set_allow_insert(value)
        +set_hierarchical(value)
        +set_enabled_post_types(value)
        +validate()
        +term_exists(term_name, parent, return_term)
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
    Entity <|-- Taxonomy
```

## Properties

### name

```php
protected $name
```

***

### description

```php
protected $description
```

***

### allow_insert

```php
protected $allow_insert
```

***

### hierarchical

```php
protected $hierarchical
```

***

### slug

```php
protected $slug
```

***

### post_type

The WordPress post type for storing this entity.

```php
public static string $post_type
```

* This property is **static**.

**See Also:**

* \Tainacan\Entities\Entity::post_type

***

### capability_type

The WordPress capability type for this entity.

```php
protected static string $capability_type
```

* This property is **static**.

**See Also:**

* \Tainacan\Entities\Entity::capability_type

***

### repository

The repository instance for this entity.

```php
protected string $repository
```

**See Also:**

* \Tainacan\Entities\Entity::repository

***

### db_identifier_prefix

Prefix used to create the db_identifier

```php
public static string $db_identifier_prefix
```

* This property is **static**.

***

## Methods

### __toString

```php
public __toString(): mixed
```

***

### tainacan_register_taxonomy

Register the taxonomy

```php
public tainacan_register_taxonomy(): bool
```

***

### get_capabilities

Get the capabilities list for the post type of the entity

```php
public get_capabilities(): object
```

**Return Value:**

Object with all the capabilities as member variables.

***

### get_name

Return the name

```php
public get_name(): string
```

***

### get_description

Return the description

```php
public get_description(): string
```

***

### get_allow_insert

Return true if allow insert or false if not allow insert

```php
public get_allow_insert(): bool
```

***

### get_hierarchical

Return 'yes' if terms hierarchy is allowd and 'no' otherwise

```php
public get_hierarchical(): bool
```

***

### get_slug

Return the slug

```php
public get_slug(): string
```

***

### get_enabled_post_types

Return the enabled post types

```php
public get_enabled_post_types(): array
```

***

### get_db_identifier

Return the DB ID

```php
public get_db_identifier(): bool|string
```

***

### set_name

Define the name of taxonomy

```php
public set_name(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_slug

Define the slug

```php
public set_slug(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_description

Define the description

```php
public set_description(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_allow_insert

Define if allow insert or not

```php
public set_allow_insert(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_hierarchical

Define if hierarchical is 'yes' or 'no'

```php
public set_hierarchical(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_enabled_post_types

Sets enabled post types

```php
public set_enabled_post_types(array $value): mixed
```

**Parameters:**

| Parameter | Type      | Description               |
|-----------|-----------|---------------------------|
| `$value`  | **array** | array of post types slugs |

***

### validate

Validate Taxonomy

```php
public validate(): bool
```

***

### term_exists

Check if a term already exists

```php
public term_exists(string $term_name, int|null $parent = null, bool $return_term = false): bool|\Tainacan\Entities\WP_Term
```

**Parameters:**

| Parameter      | Type          | Description                                                                                                            |
|----------------|---------------|------------------------------------------------------------------------------------------------------------------------|
| `$term_name`   | **string**    | The term name                                                                                                          |
| `$parent`      | **int\|null** | The ID of the parent term to look for children or null to look for terms in any hierarchical position. Default is null |
| `$return_term` | **bool**      | whether to return the term object if it exists. default is to false                                                    |

**Return Value:**

return boolean indicating if term exists. If $return_term is true and term exists, return WP_Term object

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

### get_collections_ids

```php
public get_collections_ids(): mixed
```

***

### get_collections

```php
public get_collections(): mixed
```

***

### set_collections_ids

```php
public set_collections_ids(array $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **array** |             |

***

### set_collections

```php
public set_collections(array $collections): mixed
```

**Parameters:**

| Parameter      | Type      | Description |
|----------------|-----------|-------------|
| `$collections` | **array** |             |

***

### add_collection_id

```php
public add_collection_id(mixed $new_collection_id): mixed
```

**Parameters:**

| Parameter            | Type      | Description |
|----------------------|-----------|-------------|
| `$new_collection_id` | **mixed** |             |

***

### remove_collection_id

```php
public remove_collection_id(mixed $collection_id): mixed
```

**Parameters:**

| Parameter        | Type      | Description |
|------------------|-----------|-------------|
| `$collection_id` | **mixed** |             |

***
