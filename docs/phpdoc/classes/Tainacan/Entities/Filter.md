# Filter


Represents a Tainacan Filter entity.

Filters define search and filtering capabilities for collections,
allowing users to narrow down item results based on metadata criteria.

***

* Full name: `\Tainacan\Entities\Filter`
* Parent class: [`\Tainacan\Entities\Entity`](./Entity)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Filter {
        #name : mixed
        +post_type : mixed
        +enabled_for_collection : mixed
        #repository : string
        +__toString()
        +_toArray()
        +get_name()
        +get_description()
        +get_placeholder()
        +get_order()
        +get_max_options()
        +set_max_options(max_options)
        +get_metadatum_id()
        +get_metadatum()
        +get_filter_type_object()
        +get_filter_type()
        +get_filter_type_options()
        +get_begin_with_filter_collapsed()
        +get_display_in_repository_level_lists()
        +get_description_bellow_name()
        +set_name(value)
        +set_order(value)
        +set_description(value)
        +set_placeholder(value)
        +set_metadatum(value)
        +set_metadatum_id(value)
        +set_filter_type(value)
        +set_begin_with_filter_collapsed(begin_with_filter_collapsed)
        +set_display_in_repository_level_lists(display_in_repository_level_lists)
        +get_enabled_for_collection()
        +set_enabled_for_collection(value)
        +set_description_bellow_name(value)
        +validate()
        +set_filter_type_options(value)
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
    Entity <|-- Filter
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

### order

```php
protected $order
```

***

### metadatum

```php
protected $metadatum
```

***

### metadatum_id

```php
protected $metadatum_id
```

***

### max_options

```php
protected $max_options
```

***

### filter_type

```php
protected $filter_type
```

***

### filter_type_options

```php
protected $filter_type_options
```

***

### begin_with_filter_collapsed

```php
protected $begin_with_filter_collapsed
```

***

### display_in_repository_level_lists

```php
protected $display_in_repository_level_lists
```

***

### description_bellow_name

```php
protected $description_bellow_name
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

### enabled_for_collection

```php
public $enabled_for_collection
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

## Methods

### __toString

```php
public __toString(): mixed
```

***

### _toArray

```php
public _toArray(): array
```

**Throws:**

- [`Exception`](../../Exception)

***

### get_name

Return the filter name

```php
public get_name(): string
```

***

### get_description

```php
public get_description(): mixed|null
```

***

### get_placeholder

```php
public get_placeholder(): mixed|null
```

***

### get_order

Return the filter order type

```php
public get_order(): string
```

***

### get_max_options

Return max number of options to be showed

```php
public get_max_options(): mixed|null
```

***

### set_max_options

Set max number of options to be showed

```php
public set_max_options(mixed $max_options): mixed
```

**Parameters:**

| Parameter      | Type      | Description |
|----------------|-----------|-------------|
| `$max_options` | **mixed** |             |

***

### get_metadatum_id

Return the metadatum ID

```php
public get_metadatum_id(): int
```

**Return Value:**

Metadatum ID

***

### get_metadatum

Return the metadatum object

```php
public get_metadatum(): \Tainacan\Entities\Metadatum|null
```

**Throws:**

- [`Exception`](../../Exception)

***

### get_filter_type_object

Return the an object child of \Tainacan\Filter_Types\Filter_Type with options

```php
public get_filter_type_object(): \Tainacan\Filter_Types\Filter_Type
```

**Return Value:**

The filter type class with filled options

***

### get_filter_type

Return the class name for the filter type

```php
public get_filter_type(): string
```

**Return Value:**

The

***

### get_filter_type_options

Return the actual options for the current filter type

```php
public get_filter_type_options(): array
```

**Return Value:**

Configurations for the filter type object

***

### get_begin_with_filter_collapsed

Return 'yes' or 'no' to the option of begining the filter collapsed

```php
public get_begin_with_filter_collapsed(): string
```

***

### get_display_in_repository_level_lists

Return 'yes' or 'no' to the option of display in repository level lists

```php
public get_display_in_repository_level_lists(): string
```

***

### get_description_bellow_name

Return the filter description_bellow_name

```php
public get_description_bellow_name(): string
```

***

### set_name

Define the filter name

```php
public set_name(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_order

Define the filter order type

```php
public set_order(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_description

Define the filter description

```php
public set_description(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_placeholder

Define the filter placeholder

```php
public set_placeholder(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_metadatum

Define the filter metadatum passing an object

```php
public set_metadatum(\Tainacan\Entities\Metadatum $value): void
```

**Parameters:**

| Parameter | Type                             | Description |
|-----------|----------------------------------|-------------|
| `$value`  | **\Tainacan\Entities\Metadatum** |             |

***

### set_metadatum_id

Define the filter metadatum passing an ID

```php
public set_metadatum_id(int $value): void
```

**Parameters:**

| Parameter | Type    | Description      |
|-----------|---------|------------------|
| `$value`  | **int** | the metadatum ID |

***

### set_filter_type

Save the filter type class name

```php
public set_filter_type(string|\Tainacan\Filter_Types\Filter_Type $value): mixed
```

**Parameters:**

| Parameter | Type                                           | Description                           |
|-----------|------------------------------------------------|---------------------------------------|
| `$value`  | **string\|\Tainacan\Filter_Types\Filter_Type** | The name of the class or the instance |

***

### set_begin_with_filter_collapsed

Tells if filter should begin collapsed, not loading facets

```php
public set_begin_with_filter_collapsed(string $begin_with_filter_collapsed): mixed
```

**Parameters:**

| Parameter                      | Type       | Description |
|--------------------------------|------------|-------------|
| `$begin_with_filter_collapsed` | **string** |             |

***

### set_display_in_repository_level_lists

Tells if filter should appear in repository level lists, even belonging to a collection

```php
public set_display_in_repository_level_lists(string $display_in_repository_level_lists): mixed
```

**Parameters:**

| Parameter                            | Type       | Description |
|--------------------------------------|------------|-------------|
| `$display_in_repository_level_lists` | **string** |             |

***

### get_enabled_for_collection

Transient property used to store the status of the filter for a particular collection

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

### set_description_bellow_name

Set filter description_bellow_name

```php
public set_description_bellow_name(mixed $value): void
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

Also validates the metadatum, calling the validate_options callback of the Metadatum Type

**Return Value:**

valid or not

**Throws:**

- [`Exception`](../../Exception)

***

### set_filter_type_options

Set Filter type options

```php
public set_filter_type_options(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

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
