# Metadata_Section


Represents a Tainacan Metadata Section entity.

Metadata sections organize metadata fields into logical groups
within collections, improving the user interface and organization.

***

* Full name: `\Tainacan\Entities\Metadata_Section`
* Parent class: [`\Tainacan\Entities\Entity`](./Entity)

## Class Diagram

```mermaid
classDiagram
    direction TB
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
    class Metadata_Section {
        +post_type : mixed
        +default_section_slug : mixed
        #name : mixed
        #repository : string
        +enabled_for_collection : mixed
        +__toString()
        +get_id()
        +get_name()
        +get_slug()
        +get_description()
        +get_description_bellow_name()
        +get_metadata_object_list(args)
        +get_is_conditional_section()
        +get_conditional_section_rules()
        +is_conditional_section()
        +set_name(value)
        +set_slug(value)
        +set_description(value)
        +set_description_bellow_name(value)
        +set_is_conditional_section(value)
        +set_conditional_section_rules(value)
        +get_enabled_for_collection()
        +set_enabled_for_collection(value)
        +validate()
    }
    Entity ..> Entity
    Entity <|-- Metadata_Section
```

## Properties

### post_type

The WordPress post type for storing this entity.

```php
public static string|false $post_type
```

Set to false if not using WordPress post types.

* This property is **static**.

***

### default_section_slug

```php
public static $default_section_slug
```

* This property is **static**.

***

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

### is_conditional_section

```php
protected $is_conditional_section
```

***

### conditional_section_rules

```php
protected $conditional_section_rules
```

***

### repository

The repository instance for this entity.

```php
protected string $repository
```

**See Also:**

* \Tainacan\Entities\Entity::repository

***

### enabled_for_collection

```php
public $enabled_for_collection
```

***

## Methods

### __toString

```php
public __toString(): mixed
```

***

### get_id

Get the entity ID

```php
public get_id(): int
```

***

### get_name

Return the metadata section name

```php
public get_name(): string
```

***

### get_slug

Get metadata section slug

```php
public get_slug(): string
```

***

### get_description

Return the metadata section description

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

### get_metadata_object_list

Return the metadata_list of section

```php
public get_metadata_object_list(mixed $args = []): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$args`   | **mixed** |             |

***

### get_is_conditional_section

Get if section is conditional.

```php
public get_is_conditional_section(): string
```

**Return Value:**

"yes"|"no"

***

### get_conditional_section_rules

get the rules of the conditional section

```php
public get_conditional_section_rules(): array|object
```

***

### is_conditional_section

Checks if section is conditional

```php
public is_conditional_section(): bool
```

***

### set_name

Set the metadata section name

```php
public set_name(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_slug

Set the metadata section slug

```php
public set_slug(mixed $value): void
```

If you dont set the metadata slug, it will be set automatically based on the name and
following WordPress default behavior of creating slugs for posts.

If you set the slug for an existing one, WordPress will append a number at the end of in order
to make it unique (e.g slug-1, slug-2)

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_description

Set metadata section description

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

### set_is_conditional_section

set if section is conditional.

```php
public set_is_conditional_section(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_conditional_section_rules

set the rules of the conditional section

```php
public set_conditional_section_rules(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### get_enabled_for_collection

Transient property used to store the status of the metadatum section for a particular collection

```php
public get_enabled_for_collection(): mixed
```

Used by the API to tell front end when a metadatum section is disabled

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

### validate

{@inheritdoc }

```php
public validate(): bool
```

Also validates the metadata, calling the validate_options callback of the Metadatum Type

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
