# Component_Hooks


Class Components_Hooks

***

* Full name: `\Tainacan\Component_Hooks`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Component_Hooks {
        -registered_component : mixed
        -init()
        +register_component()
        +register_vuejs_component(handle, script_path, args)
        +get_registered_component()
        +get_component(handle)
    }
```

## Properties

### registered_component

Stores external component type available to be used in Tainacan

```php
private $registered_component
```

***

## Methods

### init

```php
private init(): mixed
```

***

### register_component

```php
public register_component(): mixed
```

***

### register_vuejs_component

Register a new vuejs component

```php
public register_vuejs_component(string $handle, string $script_path, array|string $args = []): mixed
```

**Parameters:**

| Parameter      | Type              | Description                              |
|----------------|-------------------|------------------------------------------|
| `$handle`      | **string**        | name of the component. Should be unique. |
| `$script_path` | **string**        | path of file component                   |
| `$args`        | **array\|string** |                                          |

***

### get_registered_component

Get a list of all registered component

```php
public get_registered_component(): array
```

**Return Value:**

The list of registered component

***

### get_component

Get one specific component by its slug

```php
public get_component(string $handle): array|false
```

**Parameters:**

| Parameter | Type       | Description           |
|-----------|------------|-----------------------|
| `$handle` | **string** | Name of the component |

**Return Value:**

The component definition or false if it is not found

***

## Inherited methods

### get_instance

```php
public static get_instance(): mixed
```

* This method is **static**.
***

### __construct

```php
private __construct(): mixed
```

***
