# Plugin_Hooks


Class Plugins_Hooks

***

* Full name: `\Tainacan\Plugin_Hooks`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Plugin_Hooks {
        -registered_plugin : mixed
        -init()
        +register_plugin()
        +register_vuejs_plugin(handle, script_path, args)
        +get_registered_plugin()
        +get_plugin(handle)
    }
```

## Properties

### registered_plugin

Stores external vue plugin available to be used in Tainacan

```php
private $registered_plugin
```

***

## Methods

### init

```php
private init(): mixed
```

***

### register_plugin

```php
public register_plugin(): mixed
```

***

### register_vuejs_plugin

Register a new vuejs plugin

```php
public register_vuejs_plugin(string $handle, string $script_path, array|string $args = []): mixed
```

**Parameters:**

| Parameter      | Type              | Description                           |
|----------------|-------------------|---------------------------------------|
| `$handle`      | **string**        | name of the plugin. Should be unique. |
| `$script_path` | **string**        | path of file plugin                   |
| `$args`        | **array\|string** |                                       |

***

### get_registered_plugin

Get a list of all registered plugin

```php
public get_registered_plugin(): array
```

**Return Value:**

The list of registered plugin

***

### get_plugin

Get one specific plugin by its slug

```php
public get_plugin(string $handle): array|false
```

**Parameters:**

| Parameter | Type       | Description        |
|-----------|------------|--------------------|
| `$handle` | **string** | Name of the plugin |

**Return Value:**

The plugin definition or false if it is not found

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
