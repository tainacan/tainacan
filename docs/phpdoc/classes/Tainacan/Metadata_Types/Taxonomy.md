# Taxonomy


Class TainacanMetadatumType

***

* Full name: `\Tainacan\Metadata_Types\Taxonomy`
* Parent class: [`\Tainacan\Metadata_Types\Metadata_Type`](./Metadata_Type)

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

## Methods

### __construct

```php
public __construct(): mixed
```

***

### get_form_labels

allow i18n from messages

```php
public get_form_labels(): mixed
```

***

### get_options_as_html

Gets print-ready version of the options list in html

```php
public get_options_as_html(): string
```

Checks if at least one option exists, otherwise return an empty string

**Return Value:**

An html content with labels and values for the options or an empty string

***

### validate_options

Validates the options Array

```php
public validate_options(\Tainacan\Entities\Metadatum $metadatum): true|array
```

This method should be declared by each metadatum type sub classes

**Parameters:**

| Parameter    | Type                             | Description                                   |
|--------------|----------------------------------|-----------------------------------------------|
| `$metadatum` | **\Tainacan\Entities\Metadatum** | The metadatum object that is beeing validated |

**Return Value:**

True if optinos are valid. If invalid, returns an array where keys are the metadatum keys and values are error messages.

***

### validate

Validate item based on metadatum type taxonomies options

```php
public validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata): bool
```

**Parameters:**

| Parameter        | Type                                        | Description |
|------------------|---------------------------------------------|-------------|
| `$item_metadata` | **\Tainacan\Entities\Item_Metadata_Entity** |             |

**Return Value:**

Valid or not

***

### get_value_as_html

Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as an html string

```php
public get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata): string
```

**Parameters:**

| Parameter        | Type                                        | Description |
|------------------|---------------------------------------------|-------------|
| `$item_metadata` | **\Tainacan\Entities\Item_Metadata_Entity** |             |

**Return Value:**

The HTML representation of the value, containing one or multiple terms, separated by comma, linked to term page

***

### get_term_hierarchy_html

```php
private get_term_hierarchy_html(\Tainacan\Entities\Term $term, \Tainacan\Entities\Item $item = null): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$term`   | **\Tainacan\Entities\Term** |             |
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### term_to_html

```php
private term_to_html(mixed $term, \Tainacan\Entities\Item $item = null): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$term`   | **mixed**                   |             |
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### _toArray

```php
public _toArray(): mixed
```

***

### get_collection_children

```php
public get_collection_children(mixed $parent_id): mixed
```

**Parameters:**

| Parameter    | Type      | Description |
|--------------|-----------|-------------|
| `$parent_id` | **mixed** |             |

***

### get_taxonomy

Get related taxonomy object

```php
public get_taxonomy(): \Tainacan\Entities\Taxonomy|false
```

**Return Value:**

The Taxonomy object or false

***

## Inherited methods

### __construct

```php
public __construct(): mixed
```

***

### validate

```php
public validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata): mixed
```

**Parameters:**

| Parameter        | Type                                        | Description |
|------------------|---------------------------------------------|-------------|
| `$item_metadata` | **\Tainacan\Entities\Item_Metadata_Entity** |             |

***

### get_related_mapped_prop

```php
public get_related_mapped_prop(): mixed
```

***

### set_related_mapped_prop

```php
public set_related_mapped_prop(mixed $related_mapped_prop): mixed
```

**Parameters:**

| Parameter              | Type      | Description |
|------------------------|-----------|-------------|
| `$related_mapped_prop` | **mixed** |             |

***

### get_validation_errors

```php
public get_validation_errors(): mixed
```

***

### get_primitive_type

```php
public get_primitive_type(): mixed
```

***

### set_primitive_type

```php
public set_primitive_type(mixed $primitive_type): mixed
```

**Parameters:**

| Parameter         | Type      | Description |
|-------------------|-----------|-------------|
| `$primitive_type` | **mixed** |             |

***

### get_errors

```php
public get_errors(): mixed
```

***

### get_component

```php
public get_component(): mixed
```

***

### set_component

```php
public set_component(mixed $component): mixed
```

**Parameters:**

| Parameter    | Type      | Description |
|--------------|-----------|-------------|
| `$component` | **mixed** |             |

***

### get_form_component

```php
public get_form_component(): mixed
```

***

### set_form_component

```php
public set_form_component(mixed $form_component): mixed
```

**Parameters:**

| Parameter         | Type      | Description |
|-------------------|-----------|-------------|
| `$form_component` | **mixed** |             |

***

### get_preview_template

```php
public get_preview_template(): mixed
```

***

### set_preview_template

```php
public set_preview_template(mixed $preview_template): mixed
```

**Parameters:**

| Parameter           | Type      | Description |
|---------------------|-----------|-------------|
| `$preview_template` | **mixed** |             |

***

### get_name

```php
public get_name(): mixed
```

***

### set_name

```php
public set_name(mixed $name): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$name`   | **mixed** |             |

***

### get_description

```php
public get_description(): mixed
```

***

### set_description

```php
public set_description(mixed $description): mixed
```

**Parameters:**

| Parameter      | Type      | Description |
|----------------|-----------|-------------|
| `$description` | **mixed** |             |

***

### add_error

```php
public add_error(mixed $msg): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$msg`    | **mixed** |             |

***

### set_options

```php
public set_options(mixed $options): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$options` | **mixed** |             |

***

### set_default_options

```php
public set_default_options(array $options): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$options` | **array** |             |

***

### get_options

Gets the options for this metadatum types, including default values for options
that were not set yet.

```php
public get_options(): array
```

**Return Value:**

Metadatum type options

***

### get_option

Gets one option from the options array.

```php
public get_option(string $key): mixed
```

Checks if option exist or if it have a default value. Otherwise return an empty string

**Parameters:**

| Parameter | Type       | Description        |
|-----------|------------|--------------------|
| `$key`    | **string** | the desired option |

**Return Value:**

the option value, the default value or an empty string

***

### get_options_as_html

Gets print-ready version of the options list in html

```php
public get_options_as_html(): string
```

Checks if at least one option exists, otherwise return an empty string

**Return Value:**

An html content with labels and values for the options or an empty string

***

### get_form_labels

allow i18n from messages

```php
public get_form_labels(): mixed
```

***

### form

generate the metadata for this metadatum type

```php
public form(): mixed
```

***

### validate_options

Validates the options Array

```php
public validate_options(\Tainacan\Entities\Metadatum $metadatum): true|array
```

This method should be declared by each metadatum type sub classes

**Parameters:**

| Parameter    | Type                             | Description                                   |
|--------------|----------------------------------|-----------------------------------------------|
| `$metadatum` | **\Tainacan\Entities\Metadatum** | The metadatum object that is beeing validated |

**Return Value:**

True if optinos are valid. If invalid, returns an array where keys are the metadatum keys and values are error messages.

***

### get_core

```php
public get_core(): mixed
```

***

### set_core

```php
public set_core(mixed $core): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$core`   | **mixed** |             |

***

### get_repository

```php
public get_repository(): mixed
```

***

### set_repository

```php
public set_repository(mixed $repository): mixed
```

**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$repository` | **mixed** |             |

***

### get_sortable

```php
public get_sortable(): mixed
```

***

### set_sortable

```php
public set_sortable(mixed $sortable): mixed
```

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$sortable` | **mixed** |             |

***

### get_slug

Gets a slug based on the class name to represent the metadata type

```php
public get_slug(): mixed
```

***

### _toArray

```php
public _toArray(): mixed
```

***
