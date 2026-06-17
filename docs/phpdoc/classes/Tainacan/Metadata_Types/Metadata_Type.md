# Metadata_Type


Class TainacanMetadatumType

***

* Full name: `\Tainacan\Metadata_Types\Metadata_Type`
* This class is an **Abstract class**

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Metadata_Type {
        -primitive_type : string
        -repository : bool|Repository
        -options : array
        -default_options : array
        -errors : mixed
        -core : mixed
        -related_mapped_prop : mixed
        -component : string
        -form_component : bool|string
        -name : mixed
        -description : mixed
        -preview_template : string
        -sortable : mixed
        +__construct()
        +validate(item_metadata)
        +get_related_mapped_prop()
        +set_related_mapped_prop(related_mapped_prop)
        +get_validation_errors()
        +get_primitive_type()
        +set_primitive_type(primitive_type)
        +get_errors()
        +get_component()
        +set_component(component)
        +get_form_component()
        +set_form_component(form_component)
        +get_preview_template()
        +set_preview_template(preview_template)
        +get_name()
        +set_name(name)
        +get_description()
        +set_description(description)
        +add_error(msg)
        +set_options(options)
        +set_default_options(options)
        +get_options()
        +get_option(key)
        +get_options_as_html()
        +get_form_labels()
        +form()
        +validate_options(metadatum)
        +get_core()
        +set_core(core)
        +get_repository()
        +set_repository(repository)
        +get_sortable()
        +set_sortable(sortable)
        +get_slug()
        +_toArray()
    }
```

## Properties

### primitive_type

Indicates the type of variable that this metadatum type handles.

```php
private string $primitive_type
```

This is used to relate Metadatum types and filter types, so we know which filter types
will be available to be used for each metadatum based on its Metadatum Type

For instance, the Filter Type "input text" may be used to search in any metadatum that has
a Metadatum Type with a string primitive type.

***

### repository

When primitive type points to an entity, such as item or term, this indidicates which repository to use
to fetch that entity

```php
private bool|\Tainacan\Repositories\Repository $repository
```

default is false: no repository

***

### options

Array of options specific to this metadatum type. Stored in metadata_type_options property of the Metadatum object

```php
private array $options
```

***

### default_options

The default values for the metadatum type options array

```php
private array $default_options
```

***

### errors

```php
private $errors
```

***

### core

Indicates whether this is a core Metadatum Type or not

```php
private $core
```

Core metadatum types are used by Title, Description and Author metadata. These metadata:
* Can only be used once, they belong to the repository and cannot be deleted
* Its values are saved in th wp_post table, and not as post_meta

***

### related_mapped_prop

Used by core metadatum types to indicate where it should be saved

```php
private $related_mapped_prop
```

***

### component

The name of the web component used by this metadatum type

```php
private string $component
```

***

### form_component

The name of the web component used by the Form

```php
private bool|string $form_component
```

***

### name

The Metadata type name. Metadata type classes must set an internationalized string for this property

```php
private $name
```

***

### description

The Metadata type description. Metadata type classes must set an internationalized string for this property

```php
private $description
```

***

### preview_template

The html template featuring a preview of how this metadata type componenet

```php
private string $preview_template
```

***

### sortable

Indicates whether this metadata type will generate metadata that should be available as sorting options in the items list UI.

```php
private $sortable
```

***

## Methods

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
