# Bulk_Edit_Process


***

* Full name: `\Tainacan\GenericProcess\Bulk_Edit_Process`
* Parent class: [`\Tainacan\GenericProcess\Generic_Process`](./Generic_Process)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Generic_Process {
        #id : identifier
        #steps : array
        #transients : array
        #abort : bool
        #current_step : mixed
        #in_step_count : mixed
        #log : mixed
        #error_log : mixed
        #array_attributes : array
        +__construct(attributes)
        +get_id()
        +get_current_step()
        +set_current_step(value)
        +get_in_step_count()
        +set_in_step_count(value)
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
        +finished()
        +run()
    }
    class Bulk_Edit_Process {
        -meta_key : mixed
        -group_id : mixed
        -items_repository : mixed
        -metadatum_repository : mixed
        -item_metadata_repository : mixed
        -bulk_edit_data : mixed
        +__construct(attributes)
        +init_objects()
        +create_bulk_edit(params)
        +save_options(value)
        +get_options()
        +set_group_id(group_id)
        +get_group_id()
        +get_output()
        -get_bulk_edit_collection_name()
        -get_changed_metadata()
        +set_bulk_edit_data(bulk_edit_data)
        +get_bulk_edit_data()
        -bulk_list_remove_item(item)
        +add_control_metadata()
        -get_total_items()
        -bulk_list_get_item(count)
        +main_process()
        +finished()
        -save_item_metadata(item_metadata, item)
        -clear_value(item)
        -set_value(item)
        -get_parent_meta_id(item, metadatum)
        -add_value(item)
        -copy_value(item)
        -remove_value(item)
        -replace_value(item)
        -trash_items(item)
        -untrash_items(item)
        -delete_items(item)
        -set_status(item)
        -set_comment_status(item)
        -set_author_id(item)
    }
    Generic_Process <|-- Bulk_Edit_Process
```

## Properties

### meta_key

```php
private $meta_key
```

***

### group_id

```php
private $group_id
```

***

### items_repository

```php
private $items_repository
```

***

### metadatum_repository

```php
private $metadatum_repository
```

***

### item_metadata_repository

```php
private $item_metadata_repository
```

***

### bulk_edit_data

```php
private $bulk_edit_data
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

### init_objects

```php
public init_objects(): mixed
```

***

### create_bulk_edit

```php
public create_bulk_edit(mixed $params): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$params` | **mixed** |             |

***

### save_options

```php
public save_options(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### get_options

```php
public get_options(): mixed
```

***

### set_group_id

```php
public set_group_id(mixed $group_id): mixed
```

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$group_id` | **mixed** |             |

***

### get_group_id

```php
public get_group_id(): mixed
```

***

### get_output

```php
public get_output(): mixed
```

***

### get_bulk_edit_collection_name

```php
private get_bulk_edit_collection_name(): mixed
```

***

### get_changed_metadata

```php
private get_changed_metadata(): mixed
```

***

### set_bulk_edit_data

```php
public set_bulk_edit_data(mixed $bulk_edit_data = false): mixed
```

**Parameters:**

| Parameter         | Type      | Description |
|-------------------|-----------|-------------|
| `$bulk_edit_data` | **mixed** |             |

***

### get_bulk_edit_data

```php
public get_bulk_edit_data(): mixed
```

***

### bulk_list_remove_item

```php
private bulk_list_remove_item(mixed $item): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$item`   | **mixed** |             |

***

### add_control_metadata

```php
public add_control_metadata(): mixed
```

***

### get_total_items

```php
private get_total_items(): mixed
```

***

### bulk_list_get_item

```php
private bulk_list_get_item(mixed $count): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$count`  | **mixed** |             |

***

### main_process

```php
public main_process(): mixed
```

***

### finished

```php
public finished(): mixed
```

***

### save_item_metadata

```php
private save_item_metadata(\Tainacan\Entities\Item_Metadata_Entity $item_metadata, \Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter        | Type                                        | Description |
|------------------|---------------------------------------------|-------------|
| `$item_metadata` | **\Tainacan\Entities\Item_Metadata_Entity** |             |
| `$item`          | **\Tainacan\Entities\Item**                 |             |

***

### clear_value

```php
private clear_value(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### set_value

```php
private set_value(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### get_parent_meta_id

```php
private get_parent_meta_id(mixed $item, mixed $metadatum): mixed
```

**Parameters:**

| Parameter    | Type      | Description |
|--------------|-----------|-------------|
| `$item`      | **mixed** |             |
| `$metadatum` | **mixed** |             |

***

### add_value

```php
private add_value(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### copy_value

```php
private copy_value(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### remove_value

```php
private remove_value(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### replace_value

```php
private replace_value(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### trash_items

```php
private trash_items(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### untrash_items

```php
private untrash_items(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### delete_items

```php
private delete_items(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### set_status

```php
private set_status(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### set_comment_status

```php
private set_comment_status(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

### set_author_id

```php
private set_author_id(\Tainacan\Entities\Item $item): mixed
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** |             |

***

## Inherited methods

### __construct

```php
public __construct(mixed $attributes = array()): mixed
```

**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$attributes` | **mixed** |             |

***

### get_id

```php
public get_id(): string
```

**Return Value:**

the process unique id

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

***

### get_progress_value

Gets the current value to build the progress bar and give feedback to the user
on the background process that is running the process.

```php
public get_progress_value(): mixed
```

It does so by comparing the "size" attribute with the $in_step_count class attribute
where size indicates the total size of iterations the step will take and $this->in_step_count
is the current iteration.


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

### finished

```php
public finished(): mixed
```

***

### run

runs one iteration

```php
public run(): mixed
```

***
