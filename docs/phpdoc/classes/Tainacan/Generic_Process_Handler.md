# Generic_Process_Handler


***

* Full name: `\Tainacan\Generic_Process_Handler`

## Properties

### bg_process

bg_process

```php
protected \Tainacan\Background_Generic_Process $bg_process
```

***

### registered_process

```php
private $registered_process
```

***

## Methods

### init

```php
private init(): mixed
```

***

### register_generic_processes

```php
public register_generic_processes(): mixed
```

***

### register_generic_process

```php
public register_generic_process(array $process): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$process` | **array** |             |

***

### add_to_queue

```php
public add_to_queue(\Tainacan\GenericProcess\Generic_Process $process_object): mixed
```

**Parameters:**

| Parameter         | Type                                         | Description |
|-------------------|----------------------------------------------|-------------|
| `$process_object` | **\Tainacan\GenericProcess\Generic_Process** |             |

***

### unregister_generic_process

```php
public unregister_generic_process(mixed $slug): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$slug`   | **mixed** |             |

***

### get_registered_generic_process

```php
public get_registered_generic_process(): mixed
```

***

### get_generic_process

```php
public get_generic_process(mixed $slug): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$slug`   | **mixed** |             |

***

### get_generic_process_by_object

```php
public get_generic_process_by_object(\Tainacan\GenericProcess\Generic_Process $process_object): mixed
```

**Parameters:**

| Parameter         | Type                                         | Description |
|-------------------|----------------------------------------------|-------------|
| `$process_object` | **\Tainacan\GenericProcess\Generic_Process** |             |

***

### initialize_generic_process

```php
public initialize_generic_process(mixed $slug): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$slug`   | **mixed** |             |

***

### save_process_instance

Save process instance to the database

```php
public save_process_instance(\Tainacan\Tainacan\GenericProcess\Generic_Process $process): void
```

**Parameters:**

| Parameter  | Type                                                  | Description        |
|------------|-------------------------------------------------------|--------------------|
| `$process` | **\Tainacan\Tainacan\GenericProcess\Generic_Process** | The process object |

***

### get_process_instance_by_session_id

Retrieves an Process instance from the database based on its session_id

```php
public get_process_instance_by_session_id(string $session_id): \Tainacan\Tainacan\GenericProcess\Generic_Process|false
```

**Parameters:**

| Parameter     | Type       | Description    |
|---------------|------------|----------------|
| `$session_id` | **string** | The Process ID |

**Return Value:**

The Process object, if found. False otherwise

***

### delete_process_instance

Deletes this process instance from the database

```php
public delete_process_instance(\Tainacan\Tainacan\GenericProcess\Generic_Process $process): bool
```

**Parameters:**

| Parameter  | Type                                                  | Description        |
|------------|-------------------------------------------------------|--------------------|
| `$process` | **\Tainacan\Tainacan\GenericProcess\Generic_Process** | The Process object |

**Return Value:**

True, if process is successfully deleted. False on failure.

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
