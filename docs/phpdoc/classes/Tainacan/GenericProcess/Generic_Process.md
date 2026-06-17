# Generic_Process


***

* Full name: `\Tainacan\GenericProcess\Generic_Process`
* This class is an **Abstract class**

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
```

## Properties

### id

The ID for this process session

```php
protected \Tainacan\GenericProcess\identifier $id
```

When creating a new process session via API, an id is returned and used to access this
process instance

***

### steps

Declares what are the steps the process will run, in the right order.

```php
protected array $steps
```

By default, there is only one step, and the callback is the main_process method

Child classes may declare as many steps as they want and can keep this default step to use
this method for process. But it is optional.

***

### transients

Transients are used to store temporary data to be used across multiple requests

```php
protected array $transients
```

Add and remove transient data using add_transient() and delete_transient() methods

Transients can be strings, numbers or arrays. Avoid storing objects.

***

### abort

Whether to abort process execution.

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

List of attributes saved in DB, used to reconstruct the object.

```php
protected array $array_attributes
```

This property needs to be overwritten.

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
