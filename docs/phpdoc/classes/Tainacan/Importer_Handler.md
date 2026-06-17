# Importer_Handler


***

* Full name: `\Tainacan\Importer_Handler`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Importer_Handler {
        #bg_importer : Background_Importer
        -registered_importers : mixed
        -init()
        +register_importers()
        +add_to_queue(importer_object)
        +register_importer(importer)
        +unregister_importer(importer_slug)
        +get_registered_importers()
        +get_importer(importer_slug)
        +get_importer_by_object(importer_object)
        +initialize_importer(importer_slug)
        +save_importer_instance(importer)
        +get_importer_instance_by_session_id(session_id)
        +delete_importer_instance(importer)
    }
```

## Properties

### bg_importer

bg_importer

```php
protected \Tainacan\Background_Importer $bg_importer
```

***

### registered_importers

```php
private $registered_importers
```

***

## Methods

### init

```php
private init(): mixed
```

***

### register_importers

```php
public register_importers(): mixed
```

***

### add_to_queue

```php
public add_to_queue(\Tainacan\Importer\Importer $importer_object): mixed
```

**Parameters:**

| Parameter          | Type                            | Description |
|--------------------|---------------------------------|-------------|
| `$importer_object` | **\Tainacan\Importer\Importer** |             |

***

### register_importer

Register Importer

```php
public register_importer(array $importer): mixed
```

**Parameters:**

| Parameter   | Type      | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
|-------------|-----------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$importer` | **array** | {
Required. Array or string of arguments describing the importer

@type string		 $name					The name of the importer. e.g. 'Example Importer'
@type string		 $slug					A unique slug for the importer. e.g. 'This is an example importer description'
@type string		 $description			The importer description. e.g. 'example-importer'
@type string		 $class_name			The Importer Class. e.g. '\Tainacan\Importer\Test_Importer'
@type bool		 $manual_mapping		Whether Tainacan must present the user with an interface to manually map
								the metadata from the source to the target collection.

								If set to true, Importer Class must implement the method
								get_source_metadata() to return the metadatum found in the source.

								Note that this will only work when importing items to one single collection.

@type bool		 $manual_collection		Whether Tainacan will let the user choose a destination collection.

								If set to true, the API endpoints will handle Collection creation and will assign it to
								the importer object using add_collection() method.

								Otherwise, the child importer class must create the collections and add them to the collections property also
								using add_collection() |

***

### unregister_importer

```php
public unregister_importer(mixed $importer_slug): mixed
```

**Parameters:**

| Parameter        | Type      | Description |
|------------------|-----------|-------------|
| `$importer_slug` | **mixed** |             |

***

### get_registered_importers

```php
public get_registered_importers(): mixed
```

***

### get_importer

```php
public get_importer(mixed $importer_slug): mixed
```

**Parameters:**

| Parameter        | Type      | Description |
|------------------|-----------|-------------|
| `$importer_slug` | **mixed** |             |

***

### get_importer_by_object

```php
public get_importer_by_object(\Tainacan\Importer\Importer $importer_object): mixed
```

**Parameters:**

| Parameter          | Type                            | Description |
|--------------------|---------------------------------|-------------|
| `$importer_object` | **\Tainacan\Importer\Importer** |             |

***

### initialize_importer

```php
public initialize_importer(mixed $importer_slug): mixed
```

**Parameters:**

| Parameter        | Type      | Description |
|------------------|-----------|-------------|
| `$importer_slug` | **mixed** |             |

***

### save_importer_instance

Save importer instance to the database

```php
public save_importer_instance(\Tainacan\Tainacan\Importer\Importer $importer): void
```

**Parameters:**

| Parameter   | Type                                     | Description         |
|-------------|------------------------------------------|---------------------|
| `$importer` | **\Tainacan\Tainacan\Importer\Importer** | The Importer object |

***

### get_importer_instance_by_session_id

Retrieves an Importer instance from the database based on its session_id

```php
public get_importer_instance_by_session_id(string $session_id): \Tainacan\Importer\Importer|false
```

**Parameters:**

| Parameter     | Type       | Description     |
|---------------|------------|-----------------|
| `$session_id` | **string** | The Importer ID |

**Return Value:**

The Importer object, if found. False otherwise

***

### delete_importer_instance

Deletes this importer instance from the database

```php
public delete_importer_instance(\Tainacan\Tainacan\Importer\Importer $importer): bool
```

**Parameters:**

| Parameter   | Type                                     | Description         |
|-------------|------------------------------------------|---------------------|
| `$importer` | **\Tainacan\Tainacan\Importer\Importer** | The Importer object |

**Return Value:**

True, if importer is successfully deleted. False on failure.

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
