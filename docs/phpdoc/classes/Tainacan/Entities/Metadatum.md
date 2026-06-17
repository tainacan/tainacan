# Metadatum


Represents a Tainacan Metadatum entity.

Metadata definitions that specify the structure and validation
rules for item metadata within collections.

***

* Full name: `\Tainacan\Entities\Metadatum`
* Parent class: [`\Tainacan\Entities\Entity`](./Entity)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Metadatum {
        #name : mixed
        +enabled_for_collection : mixed
        +post_type : mixed
        #capability_type : string
        #repository : string
        +__toString()
        +set_display(display)
        +get_display()
        +get_allow_advanced_search()
        +set_allow_advanced_search(allow_advanced_search)
        +get_name()
        +get_slug()
        +get_order()
        +get_parent()
        +get_description()
        +get_description_bellow_name()
        +get_placeholder()
        +get_required()
        +get_multiple()
        +get_cardinality()
        +get_collection_key()
        +get_default_value()
        +get_metadata_type_object()
        +get_metadata_type()
        +get_metadata_type_options()
        +get_accept_suggestion()
        +get_exposer_mapping()
        +get_semantic_uri()
        +get_metadata_section_id()
        +set_name(value)
        +set_slug(value)
        +set_order(value)
        +set_parent(value)
        +set_description(value)
        +set_description_bellow_name(value)
        +set_placeholder(value)
        +set_required(value)
        +set_multiple(value)
        +set_cardinality(value)
        +set_collection_key(value)
        +set_default_value(value)
        +set_metadata_type(value)
        +set_accept_suggestion(value)
        +set_metadata_type_options(value)
        +set_exposer_mapping(value)
        +set_semantic_uri(value)
        +set_metadata_section_id(value)
        +get_enabled_for_collection()
        +set_enabled_for_collection(value)
        +is_multiple()
        +is_collection_key()
        +is_required()
        +is_repository_level()
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
    Entity <|-- Metadatum
    Metadatum ..> Metadatum
```

## Properties

### name

```php
protected $name
```

***

### slug

```php
protected $slug
```

***

### order

```php
protected $order
```

***

### parent

```php
protected $parent
```

***

### description

```php
protected $description
```

***

### description_bellow_name

```php
protected $description_bellow_name
```

***

### placeholder

```php
protected $placeholder
```

***

### required

```php
protected $required
```

***

### multiple

```php
protected $multiple
```

***

### display

```php
protected $display
```

***

### allow_advanced_search

```php
protected $allow_advanced_search
```

***

### cardinality

```php
protected $cardinality
```

***

### collection_key

```php
protected $collection_key
```

***

### default_value

```php
protected $default_value
```

***

### metadata_type

```php
protected $metadata_type
```

***

### metadata_type_options

```php
protected $metadata_type_options
```

***

### metadata_section_id

```php
protected $metadata_section_id
```

***

### enabled_for_collection

```php
public $enabled_for_collection
```

***

### post_type

The WordPress post type for storing this entity.

```php
public static string|false $post_type
```

Set to false if not using WordPress post types.

* This property is **static**.

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

## Methods

### __toString

```php
public __toString(): mixed
```

***

### set_display

```php
public set_display(mixed $display): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$display` | **mixed** |             |

***

### get_display

```php
public get_display(): mixed
```

***

### get_allow_advanced_search

```php
public get_allow_advanced_search(): string
```

***

### set_allow_advanced_search

```php
public set_allow_advanced_search(string $allow_advanced_search): void
```

**Parameters:**

| Parameter                | Type       | Description |
|--------------------------|------------|-------------|
| `$allow_advanced_search` | **string** |             |

***

### get_name

Return the metadatum name

```php
public get_name(): string
```

***

### get_slug

Get metadatum slug

```php
public get_slug(): string
```

***

### get_order

Return the metadatum order type

```php
public get_order(): string
```

***

### get_parent

Return the parent ID

```php
public get_parent(): string
```

***

### get_description

Return the metadatum description

```php
public get_description(): string
```

***

### get_description_bellow_name

Return the metadatum description_bellow_name

```php
public get_description_bellow_name(): string
```

***

### get_placeholder

Return the metadatum placeholder

```php
public get_placeholder(): string
```

***

### get_required

Return if is a required metadatum

```php
public get_required(): bool
```

***

### get_multiple

Return if is a multiple metadatum

```php
public get_multiple(): bool
```

***

### get_cardinality

Return the cardinality

```php
public get_cardinality(): string
```

***

### get_collection_key

Return if metadatum is key

```php
public get_collection_key(): bool
```

***

### get_default_value

Return the metadatum default value

```php
public get_default_value(): mixed
```

***

### get_metadata_type_object

Return the an object child of \Tainacan\metadatum_Types\Metadata_Type with options

```php
public get_metadata_type_object(): \Tainacan\Metadata_Types\Metadata_Type|object
```

**Return Value:**

The metadatum type class with filled options

***

### get_metadata_type

Return the class name for the metadatum type

```php
public get_metadata_type(): string
```

**Return Value:**

The

***

### get_metadata_type_options

Return the actual options for the current metadatum type

```php
public get_metadata_type_options(): array
```

**Return Value:**

Configurations for the metadatum type object

***

### get_accept_suggestion

Return true if this metadatum allow community suggestions, false otherwise

```php
public get_accept_suggestion(): bool
```

***

### get_exposer_mapping

Return array of exposer mapping configurations

```php
public get_exposer_mapping(): array
```

***

### get_semantic_uri

Return the semantic_uri

```php
public get_semantic_uri(): string
```

***

### get_metadata_section_id

Return the metadata_section_id

```php
public get_metadata_section_id(): string
```

***

### set_name

Set the metadatum name

```php
public set_name(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_slug

Set the metadatum slug

```php
public set_slug(mixed $value): void
```

If you dont set the metadatum slug, it will be set automatically based on the name and
following WordPress default behavior of creating slugs for posts.

If you set the slug for an existing one, WordPress will append a number at the end of in order
to make it unique (e.g slug-1, slug-2)

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_order

Set manually the order of the metadatum

```php
public set_order(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_parent

Set the metadatum parent ID

```php
public set_parent(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_description

Set metadatum description

```php
public set_description(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_description_bellow_name

Set metadatum description_bellow_name

```php
public set_description_bellow_name(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_placeholder

Set metadatum placeholder

```php
public set_placeholder(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_required

Allow the metadatum be required

```php
public set_required(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_multiple

Allow multiple metadata

```php
public set_multiple(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_cardinality

The number of  possible metadata

```php
public set_cardinality(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_collection_key

Define if the value is key on the collection

```php
public set_collection_key(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_default_value

Set default value

```php
public set_default_value(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_metadata_type

set the metadatum type class name

```php
public set_metadata_type(string|\Tainacan\Metadata_Types\Metadata_Type $value): mixed
```

**Parameters:**

| Parameter | Type                                               | Description                           |
|-----------|----------------------------------------------------|---------------------------------------|
| `$value`  | **string\|\Tainacan\Metadata_Types\Metadata_Type** | The name of the class or the instance |

***

### set_accept_suggestion

Set if this metadatum allow community suggestions

```php
public set_accept_suggestion(bool $value): mixed
```

**Parameters:**

| Parameter | Type     | Description |
|-----------|----------|-------------|
| `$value`  | **bool** |             |

***

### set_metadata_type_options

Set Metadatum type options

```php
public set_metadata_type_options(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_exposer_mapping

Set exposers mapping configuration for this metadatum

```php
public set_exposer_mapping(array $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **array** |             |

***

### set_semantic_uri

Set Semantic URI for the metadatum

```php
public set_semantic_uri(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_metadata_section_id

Set metadatum section ID for the metadatum

```php
public set_metadata_section_id(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### get_enabled_for_collection

Transient property used to store the status of the metadatum for a particular collection

```php
public get_enabled_for_collection(): mixed
```

Used by the API to tell front end when a metadatum is disabled

***

### set_enabled_for_collection

```php
public set_enabled_for_collection(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### is_multiple

Return true if is multiple, else return false

```php
public is_multiple(): bool
```

***

### is_collection_key

Return true if is collection key, else return false

```php
public is_collection_key(): bool
```

***

### is_required

Return true if is required, else return false

```php
public is_required(): bool
```

***

### is_repository_level

Return true if is repository level, else return false

```php
public is_repository_level(): bool
```

***

### validate

{@inheritdoc }

```php
public validate(): bool
```

Also validates the metadatum, calling the validate_options callback of the Metadatum Type

**Return Value:**

valid or not

**Throws:**

- [`Exception`](../../Exception)

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

### get_collection_id

```php
public get_collection_id(): int
```

**Return Value:**

collection item ID

***

### get_collection

Return Collection from relation

```php
public get_collection(): \Tainacan\Entities\Collection|null
```

**Return Value:**

Return Collection or null on errors

***

### set_collection_id

Set collection ID

```php
public set_collection_id(int $value): mixed
```

**Parameters:**

| Parameter | Type    | Description |
|-----------|---------|-------------|
| `$value`  | **int** |             |

***

### set_collection

set collection object and id

```php
public set_collection(\Tainacan\Entities\Collection $collection): mixed
```

**Parameters:**

| Parameter     | Type                              | Description |
|---------------|-----------------------------------|-------------|
| `$collection` | **\Tainacan\Entities\Collection** |             |

***
