# Exporter


***

* Full name: `\Tainacan\Exporter\Exporter`
* This class is an **Abstract class**

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Exporter {
        #id : identifier
        #options : array
        #default_options : array
        #steps : array
        #collections : array
        -output_files : mixed
        -mapping_accept : mixed
        -send_email : mixed
        #mapping_list : mixed
        +mapping_selected : mixed
        #accept_no_mapping : mixed
        #transients : array
        #abort : bool
        #current_step : mixed
        #in_step_count : mixed
        #current_collection : mixed
        #current_collection_item : mixed
        #log : mixed
        #error_log : mixed
        #array_attributes : array
        +__construct(attributes)
        +add_collection(collection)
        +remove_collection(col_id)
        +update_collection(index, collection_definition)
        +update_current_collection(collection_definition)
        +next_item()
        +next_collection()
        +get_id()
        +get_current_step()
        +set_current_step(value)
        +get_in_step_count()
        +set_in_step_count(value)
        +get_current_collection()
        +set_current_collection(value)
        +get_current_collection_item()
        +get_step_length_items()
        +set_current_collection_item(value)
        +get_collections()
        +set_collections(value)
        +get_current_collection_object()
        +get_options()
        +set_options(options)
        +get_option(key)
        #set_default_options(options)
        +set_steps(steps)
        +get_steps()
        #get_transients()
        #set_transients(data)
        +add_transient(key, data)
        +delete_transient(key)
        +get_transient(key)
        +get_log()
        +get_error_log()
        +add_log(message)
        +add_error_log(message)
        +is_finished()
        #cancel_abort()
        #abort()
        +get_abort()
        +get_progress_label()
        +get_progress_value()
        #set_current_step_total(value)
        #set_step_total(step, value)
        #next_step()
        +_to_Array(short)
        +get_current_mapper()
        +options_form()
        +process_collections()
        -process_header(current_collection_item, collection_definition)
        +output_header()
        -process_footer(current_collection_item, collection_definition)
        +output_footer()
        -get_items(index, collection_definition)
        -map_item_metadata(item)
        +add_new_file(key)
        +append_to_file(key, data)
        +set_accepted_mapping_methods(method, default_mapping, list)
        +set_mapping_selected(mapping_selected)
        +get_mapping_selected()
        +set_send_email(email)
        +get_send_email()
        +get_output()
        +finished()
        +begin_exporter()
        +end_exporter()
        -set_output_files(output_files)
        #get_output_files()
        +run()
    }
```

## Properties

### id

The ID for this importer/exporter session

```php
protected \Tainacan\Exporter\identifier $id
```

When creating a new importer/exporter session via API, an id is returned and used to access this
importer/exporter instance. This is temporarly saved in the database and discarded after the bg process is triggered

***

### options

Stores the options for the importer/exporter. Each importer/exporter might use this property to save
their own specific option

```php
protected array $options
```

***

### default_options

Stores the default options for the importer/exporter options

```php
protected array $default_options
```

***

### steps

Declares what are the steps the importer/exporter will run, in the right order.

```php
protected array $steps
```

By default, there is only one step, and the callback is the process_collections method
that process items for the collections in the collections array.

Child classes may declare as many steps as they want and can keep this default step to use
this method for importer/exporter the items. But it is optional.

***

### collections

This array holds the structure that the default step 'process_collections' will handle.

```php
protected array $collections
```

Its an array of the target collections, with their IDs, an identifier from the source, the total number of items to be importer/exporter, the mapping array
from the source structure to the ID of the metadata in tainacan

The format of the map is an array where the keys are the metadata IDs of the destination collection and the
values are the identifier from the source. This could be an ID or a string or whatever the importer/exporter finds appropriate to handle

The source_id can be anyhting you like, that helps you relate this collection to your source.

Example of the structure of this propery for one collection:
0 => [
	'id' => 12,
	'total_items' => 1234,
],

use add_collection() and remove_collection() to interact with thiis array.

***

### output_files

```php
private $output_files
```

***

### mapping_accept

```php
private $mapping_accept
```

***

### send_email

```php
private $send_email
```

***

### mapping_list

```php
protected $mapping_list
```

***

### mapping_selected

```php
public $mapping_selected
```

***

### accept_no_mapping

```php
protected $accept_no_mapping
```

***

### transients

Transients is used to store temporary data to be used accross multiple requests

```php
protected array $transients
```

Add and remove transient data using add_transient() and delete_transient() methods

Transients can be strings, numbers or arrays. Avoid storing objects.

***

### abort

Whether to abort importer/exporter execution.

```php
protected bool $abort
```

***

### current_step

```php
protected $current_step
```

***

### in_step_count

```php
protected $in_step_count
```

***

### current_collection

```php
protected $current_collection
```

***

### current_collection_item

```php
protected $current_collection_item
```

***

### log

```php
protected $log
```

***

### error_log

```php
protected $error_log
```

***

### array_attributes

List of attributes that are saved in DB and that are used to
reconstruct the object, this property need be overwrite in custom import/export.

```php
protected array $array_attributes
```

***

## Methods

### __construct

```php
public __construct(mixed $attributes = array()): mixed
```

**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$attributes` | **mixed** |             |

***

### add_collection

```php
public add_collection(array $collection): mixed
```

**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$collection` | **array** |             |

***

### remove_collection

```php
public remove_collection(mixed $col_id): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$col_id` | **mixed** |             |

***

### update_collection

```php
public update_collection(mixed $index, mixed $collection_definition): mixed
```

**Parameters:**

| Parameter                | Type      | Description |
|--------------------------|-----------|-------------|
| `$index`                 | **mixed** |             |
| `$collection_definition` | **mixed** |             |

***

### update_current_collection

```php
public update_current_collection(mixed $collection_definition): mixed
```

**Parameters:**

| Parameter                | Type      | Description |
|--------------------------|-----------|-------------|
| `$collection_definition` | **mixed** |             |

***

### next_item

```php
public next_item(): mixed
```

***

### next_collection

```php
public next_collection(): mixed
```

***

### get_id

```php
public get_id(): string
```

***

### get_current_step

```php
public get_current_step(): mixed
```

***

### set_current_step

```php
public set_current_step(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### get_in_step_count

```php
public get_in_step_count(): mixed
```

***

### set_in_step_count

```php
public set_in_step_count(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### get_current_collection

```php
public get_current_collection(): mixed
```

***

### set_current_collection

```php
public set_current_collection(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### get_current_collection_item

```php
public get_current_collection_item(): mixed
```

***

### get_step_length_items

```php
public get_step_length_items(): mixed
```

***

### set_current_collection_item

```php
public set_current_collection_item(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### get_collections

```php
public get_collections(): mixed
```

***

### set_collections

```php
public set_collections(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### get_current_collection_object

```php
public get_current_collection_object(): mixed
```

***

### get_options

Gets the options for this import/export, including default values for options
that were not set yet.

```php
public get_options(): array
```

**Return Value:**

import/export options

***

### set_options

Set the options array

```php
public set_options(array $options): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$options` | **array** |             |

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

### set_default_options

Set the default options values.

```php
protected set_default_options(array $options): mixed
```

Must be called from the __construct method of the child import/export class to set default values.

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$options` | **array** |             |

***

### set_steps

```php
public set_steps(mixed $steps): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$steps`  | **mixed** |             |

***

### get_steps

```php
public get_steps(): mixed
```

***

### get_transients

```php
protected get_transients(): mixed
```

***

### set_transients

```php
protected set_transients(array $data): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$data`   | **array** |             |

***

### add_transient

```php
public add_transient(mixed $key, mixed $data): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$key`    | **mixed** |             |
| `$data`   | **mixed** |             |

***

### delete_transient

```php
public delete_transient(mixed $key): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$key`    | **mixed** |             |

***

### get_transient

```php
public get_transient(mixed $key): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$key`    | **mixed** |             |

***

### get_log

```php
public get_log(): mixed
```

***

### get_error_log

```php
public get_error_log(): mixed
```

***

### add_log

```php
public add_log(mixed $message): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$message` | **mixed** |             |

***

### add_error_log

```php
public add_error_log(mixed $message): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$message` | **mixed** |             |

***

### is_finished

```php
public is_finished(): mixed
```

***

### cancel_abort

Cancel Scheduled abortion at the end of run()

```php
protected cancel_abort(): void
```

***

### abort

Schedule importer abortion at the end of run()

```php
protected abort(): void
```

***

### get_abort

Return whether importer should abort execution or not

```php
public get_abort(): bool
```

***

### get_progress_label

Gets the current label to be displayed below the progress bar to give
feedback to the user.

```php
public get_progress_label(): string
```

It automatically gets the attribute progress_label from the current step running.

Importers/Exporters may change this label whenever they want

***

### get_progress_value

Gets the current value to build the progress bar and give feedback to the user
on the background process that is running the importer.

```php
public get_progress_value(): mixed
```

It does so by comparing the "size" attribute with the $in_step_count class attribute
where size indicates the total size of iterations the step will take and $this->in_step_count
is the current iteration.

For the step with "process_items" as a callback, this method will look for the the $this->collections array
and sum the value of all "total_items" attributes of each collection. Then it will look for
$this->get_current_collection and $this->set_current_collection_item to calculate the progress.

The value must be from 0 to 100

If a negative value is passed, it is assumed that the progress is unknown

***

### set_current_step_total

Sets the total attribute for the current step

```php
protected set_current_step_total(mixed $value): mixed
```

The "total" attribute of a step indicates the number of iterations this step will take to complete.

The iteration is counted using $this->in_step_count attribute, and comparing the two values gives us
the current progress of the process.

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_step_total

Sets the total attribute for a given step

```php
protected set_step_total(mixed $step, mixed $value): mixed
```

The "total" attribute of a step indicates the number of iterations this step will take to complete.

The iteration is counted using $this->in_step_count attribute, and comparing the two values gives us
the current progress of the process.

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$step`   | **mixed** |             |
| `$value`  | **mixed** |             |

***

### next_step

```php
protected next_step(): mixed
```

***

### _to_Array

```php
public _to_Array(mixed $short = false): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$short`  | **mixed** |             |

***

### get_current_mapper

Gets the current mapper object, if one was chosen by the user, false Otherwise

```php
public get_current_mapper(): mixed
```

***

### options_form

Method implemented by child importer/exporter to return the HTML of the Options Form to be rendered in the Importer page

```php
public options_form(): mixed
```

***

### process_collections

Default methods to Export
process an item from the collections queue

```php
public process_collections(): mixed
```

***

### process_item

```php
public process_item(mixed $item, mixed $metadata): mixed
```

* This method is **abstract**.
**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$item`     | **mixed** |             |
| `$metadata` | **mixed** |             |

***

### process_header

```php
private process_header(mixed $current_collection_item, mixed $collection_definition): mixed
```

**Parameters:**

| Parameter                  | Type      | Description |
|----------------------------|-----------|-------------|
| `$current_collection_item` | **mixed** |             |
| `$collection_definition`   | **mixed** |             |

***

### output_header

```php
public output_header(): mixed
```

***

### process_footer

```php
private process_footer(mixed $current_collection_item, mixed $collection_definition): mixed
```

**Parameters:**

| Parameter                  | Type      | Description |
|----------------------------|-----------|-------------|
| `$current_collection_item` | **mixed** |             |
| `$collection_definition`   | **mixed** |             |

***

### output_footer

```php
public output_footer(): mixed
```

***

### get_items

```php
private get_items(mixed $index, mixed $collection_definition): mixed
```

**Parameters:**

| Parameter                | Type      | Description |
|--------------------------|-----------|-------------|
| `$index`                 | **mixed** |             |
| `$collection_definition` | **mixed** |             |

***

### map_item_metadata

Gets an Item as input and return an array of ItemMetadataObjects
If a mapper is selected, the array keys will be the slugs of the metadata
declared by the mapper, in the same order.

```php
private map_item_metadata(\Tainacan\Entities\Item $item): mixed
```

Note that if one of the metadata is not mapped, this array item will be null

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### add_new_file

```php
public add_new_file(mixed $key): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$key`    | **mixed** |             |

***

### append_to_file

Append content to a file. Create the file if it does not exist

```php
public append_to_file(string $key, string $data): mixed
```

**Parameters:**

| Parameter | Type       | Description                                                                                                 |
|-----------|------------|-------------------------------------------------------------------------------------------------------------|
| `$key`    | **string** | The file identifier. (it is the name of the file, with extension, and will be prefixed with the process ID) |
| `$data`   | **string** | The content to be appended to the file                                                                      |

***

### set_accepted_mapping_methods

Method called by Exporters classes to set accepted mapping method

```php
public set_accepted_mapping_methods(string $method, string $default_mapping = '', array $list = []): mixed
```

**Parameters:**

| Parameter          | Type       | Description                                                                                                           |
|--------------------|------------|-----------------------------------------------------------------------------------------------------------------------|
| `$method`          | **string** | THe accepted methods. any or list. If list, Exporter must also inform
default mapper and the list of accepted mappers |
| `$default_mapping` | **string** | The default mapping method. Required if list is chosen                                                                |
| `$list`            | **array**  | List of accepted mapping methods                                                                                      |

***

### set_mapping_selected

```php
public set_mapping_selected(mixed $mapping_selected): mixed
```

**Parameters:**

| Parameter           | Type      | Description |
|---------------------|-----------|-------------|
| `$mapping_selected` | **mixed** |             |

***

### get_mapping_selected

```php
public get_mapping_selected(): mixed
```

***

### set_send_email

```php
public set_send_email(mixed $email): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$email`  | **mixed** |             |

***

### get_send_email

```php
public get_send_email(): mixed
```

***

### get_output

```php
public get_output(): mixed
```

***

### finished

```php
public finished(): mixed
```

***

### begin_exporter

```php
public begin_exporter(): mixed
```

***

### end_exporter

```php
public end_exporter(): mixed
```

***

### set_output_files

```php
private set_output_files(mixed $output_files): mixed
```

**Parameters:**

| Parameter       | Type      | Description |
|-----------------|-----------|-------------|
| `$output_files` | **mixed** |             |

***

### get_output_files

```php
protected get_output_files(): mixed
```

***

### run

runs one iteration

```php
public run(): mixed
```

***
