# Async_Request


Abstract base class for asynchronous requests.

Provides the foundation for handling asynchronous HTTP requests
in WordPress, typically used for background processing tasks.

***

* Full name: `\Tainacan\Async_Request`
* This class is an **Abstract class**

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Async_Request {
        #prefix : string
        #action : string
        #identifier : mixed
        #data : array
        +__construct()
        +data(data)
        +dispatch()
        #get_query_args()
        #get_query_url()
        #get_post_args()
        +maybe_handle()
    }
```

## Properties

### prefix

Request prefix for identifying the async request.

```php
protected string $prefix
```

***

### action

Action name for the async request.

```php
protected string $action
```

***

### identifier

Unique identifier for this request.

```php
protected mixed $identifier
```

***

### data

Data to be sent with the async request.

```php
protected array $data
```

***

## Methods

### __construct

Constructor for the Async_Request class.

```php
public __construct(): mixed
```

Initializes the async request with a unique identifier.

***

### data

Set data used during the request

```php
public data(array $data): $this
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$data`   | **array** | Data.       |

***

### dispatch

Dispatch the async request

```php
public dispatch(): array|\Tainacan\WP_Error
```

***

### get_query_args

Get query args

```php
protected get_query_args(): array
```

***

### get_query_url

Get query URL

```php
protected get_query_url(): string
```

***

### get_post_args

Get post args

```php
protected get_post_args(): array
```

***

### maybe_handle

Maybe handle

```php
public maybe_handle(): mixed
```

Check for correct nonce and pass to handler.

***

### handle

Handle

```php
protected handle(): mixed
```

Override this method to perform any actions required
during the async request.

* This method is **abstract**.
***
