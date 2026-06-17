# Background_Process_Heartbeat


***

* Full name: `\Tainacan\Background_Process_Heartbeat`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Background_Process_Heartbeat {
        -init()
        +bg_process_feedback(response, data)
    }
```

## Methods

### init

```php
private init(): mixed
```

***

### bg_process_feedback

Receive Heartbeat data and response.

```php
public bg_process_feedback(array $response, array $data): mixed
```

Processes data received via a Heartbeat request, and returns additional data to pass back to the front end.

**Parameters:**

| Parameter   | Type      | Description                                        |
|-------------|-----------|----------------------------------------------------|
| `$response` | **array** | Heartbeat response data to pass back to front end. |
| `$data`     | **array** | Data received from the front end (unslashed).      |

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
