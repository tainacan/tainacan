# Filter_Type


Class TainacanFilterType

***

* Full name: `\Tainacan\Filter_Types\Filter_Type`
* This class is an **Abstract class**

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Filter_Type {
        -supported_types : mixed
        -options : array
        -default_options : array
        -name : string
        -component : string
        -script : string
        -form_component : bool|string
        -preview_template : string
        #use_max_options : mixed
        #use_input_placeholder : mixed
        +__construct()
        +form()
        +set_name(name)
        +get_name()
        +get_supported_types()
        +set_supported_types(supported_types)
        +get_component()
        +set_preview_template(preview_template)
        +get_preview_template()
        +_toArray()
        +set_options(options)
        +set_default_options(options)
        +validate_options(filter)
        +set_component(component)
        +set_script(script)
        +get_options()
        +set_use_max_options(use_max_options)
        +get_use_max_options()
        +set_use_input_placeholder(use_input_placeholder)
        +get_use_input_placeholder()
        +get_option(key)
        +get_form_labels()
        +get_form_component()
        +get_script()
        +set_form_component(form_component)
        +get_filter_type()
    }
```

## Properties

### supported_types

```php
private $supported_types
```

***

### options

Array of options specific to this filter type. Stored in filter_type_options property of the Filter object

```php
private array $options
```

***

### default_options

The default values for the filter type options array

```php
private array $default_options
```

***

### name

The name of the filter type that will be rendered on labels

```php
private string $name
```

***

### component

The name of the web component used by this filter type

```php
private string $component
```

***

### script

The content of the js script associated to the vue component for extra filters

```php
private string $script
```

***

### form_component

The name of the web component used by the Form

```php
private bool|string $form_component
```

***

### preview_template

The html template featuring a preview of how this metadata type componenet

```php
private string $preview_template
```

***

### use_max_options

Defines if the filter type should use the max options

```php
protected $use_max_options
```

***

### use_input_placeholder

Defines if the filter type should provide a placeholder for the input field

```php
protected $use_input_placeholder
```

***

## Methods

### __construct

```php
public __construct(): mixed
```

***

### form

generate the metadata for this metadatum type

```php
public form(): mixed
```

***

### set_name

```php
public set_name(string $name): mixed
```

**Parameters:**

| Parameter | Type       | Description         |
|-----------|------------|---------------------|
| `$name`   | **string** | for the filter type |

***

### get_name

```php
public get_name(): string
```

***

### get_supported_types

```php
public get_supported_types(): array
```

**Return Value:**

Supported types by the filter

***

### set_supported_types

specifies the types supported for the filter

```php
public set_supported_types(array $supported_types): mixed
```

**Parameters:**

| Parameter          | Type      | Description         |
|--------------------|-----------|---------------------|
| `$supported_types` | **array** | the types supported |

***

### get_component

```php
public get_component(): mixed
```

***

### set_preview_template

specifies the preview template for the filter type

```php
public set_preview_template(string $preview_template): mixed
```

**Parameters:**

| Parameter           | Type       | Description         |
|---------------------|------------|---------------------|
| `$preview_template` | **string** | for the filter type |

***

### get_preview_template

```php
public get_preview_template(): string
```

***

### _toArray

```php
public _toArray(): array
```

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

### validate_options

Validates the options Array

```php
public validate_options(\Tainacan\Entities\Filter $filter): true|array
```

This method should be declared by each filter type sub classes

**Parameters:**

| Parameter | Type                          | Description                                   |
|-----------|-------------------------------|-----------------------------------------------|
| `$filter` | **\Tainacan\Entities\Filter** | The metadatum object that is beeing validated |

**Return Value:**

True if options are valid. If invalid, returns an array where keys are the metadatum keys and values are error messages.

**Throws:**

- [`Exception`](../../Exception)

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

### set_script

```php
public set_script(string $script): mixed
```

**Parameters:**

| Parameter | Type       | Description |
|-----------|------------|-------------|
| `$script` | **string** |             |

***

### get_options

Gets the options for this filter types including default values for options
that were not set yet.

```php
public get_options(): array
```

**Return Value:**

Filter type options

***

### set_use_max_options

```php
public set_use_max_options(mixed $use_max_options): mixed
```

**Parameters:**

| Parameter          | Type      | Description |
|--------------------|-----------|-------------|
| `$use_max_options` | **mixed** |             |

***

### get_use_max_options

```php
public get_use_max_options(): mixed
```

***

### set_use_input_placeholder

```php
public set_use_input_placeholder(mixed $use_input_placeholder): mixed
```

**Parameters:**

| Parameter                | Type      | Description |
|--------------------------|-----------|-------------|
| `$use_input_placeholder` | **mixed** |             |

***

### get_use_input_placeholder

```php
public get_use_input_placeholder(): mixed
```

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

### get_form_labels

allow i18n from messages

```php
public get_form_labels(): mixed
```

***

### get_form_component

```php
public get_form_component(): string
```

***

### get_script

```php
public get_script(): string
```

***

### set_form_component

```php
public set_form_component(mixed $form_component): mixed
```

**Parameters:**

| Parameter         | Type      | Description                                                |
|-------------------|-----------|------------------------------------------------------------|
| `$form_component` | **mixed** | The web component that will render the filter options form |

***

### get_filter_type

```php
public get_filter_type(): mixed
```

***
