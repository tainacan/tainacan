# Exporter_Handler


***

* Full name: `\Tainacan\Exporter_Handler`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Exporter_Handler {
        #bg_exporter : Background_Exporter
        -registered_exporters : mixed
        -init()
        +register_exporters()
        +enqueue_scripts()
        +register_exporter(exporter)
        +add_to_queue(exporter_object)
        +unregister_exporter(slug)
        +get_registered_exporters()
        +get_exporter(slug)
        +get_exporter_by_object(exporter_object)
        +initialize_exporter(slug)
        +save_exporter_instance(exporter)
        +get_exporter_instance_by_session_id(session_id)
        +delete_exporter_instance(exporter)
    }
```

## Properties

### bg_exporter

bg_exporter

```php
protected \Tainacan\Background_Exporter $bg_exporter
```

***

### registered_exporters

```php
private $registered_exporters
```

***

## Methods

### init

```php
private init(): mixed
```

***

### register_exporters

```php
public register_exporters(): mixed
```

***

### enqueue_scripts

```php
public enqueue_scripts(): mixed
```

***

### register_exporter

```php
public register_exporter(array $exporter): mixed
```

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$exporter` | **array** |             |

***

### add_to_queue

```php
public add_to_queue(\Tainacan\Exporter\Exporter $exporter_object): mixed
```

**Parameters:**

| Parameter          | Type                            | Description |
|--------------------|---------------------------------|-------------|
| `$exporter_object` | **\Tainacan\Exporter\Exporter** |             |

***

### unregister_exporter

```php
public unregister_exporter(mixed $slug): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$slug`   | **mixed** |             |

***

### get_registered_exporters

```php
public get_registered_exporters(): mixed
```

***

### get_exporter

```php
public get_exporter(mixed $slug): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$slug`   | **mixed** |             |

***

### get_exporter_by_object

```php
public get_exporter_by_object(\Tainacan\exporter\Exporter $exporter_object): mixed
```

**Parameters:**

| Parameter          | Type                            | Description |
|--------------------|---------------------------------|-------------|
| `$exporter_object` | **\Tainacan\exporter\Exporter** |             |

***

### initialize_exporter

```php
public initialize_exporter(mixed $slug): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$slug`   | **mixed** |             |

***

### save_exporter_instance

Save exporter instance to the database

```php
public save_exporter_instance(\Tainacan\Tainacan\Exporter\Exporter $exporter): void
```

**Parameters:**

| Parameter   | Type                                     | Description         |
|-------------|------------------------------------------|---------------------|
| `$exporter` | **\Tainacan\Tainacan\Exporter\Exporter** | The Importer object |

***

### get_exporter_instance_by_session_id

Retrieves an Importer instance from the database based on its session_id

```php
public get_exporter_instance_by_session_id(string $session_id): \Tainacan\Exporter\Exporter|false
```

**Parameters:**

| Parameter     | Type       | Description     |
|---------------|------------|-----------------|
| `$session_id` | **string** | The Importer ID |

**Return Value:**

The Importer object, if found. False otherwise

***

### delete_exporter_instance

Deletes this exporter instance from the database

```php
public delete_exporter_instance(\Tainacan\Tainacan\Exporter\Exporter $exporter): bool
```

**Parameters:**

| Parameter   | Type                                     | Description         |
|-------------|------------------------------------------|---------------------|
| `$exporter` | **\Tainacan\Tainacan\Exporter\Exporter** | The Importer object |

**Return Value:**

True, if exporter is successfully deleted. False on failure.

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
