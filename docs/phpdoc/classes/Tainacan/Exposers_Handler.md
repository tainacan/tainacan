# Exposers_Handler


Load exposers classes

***

* Full name: `\Tainacan\Exposers_Handler`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Exposers_Handler {
        #exposers : mixed
        -instance : mixed
        -request : mixed
        +$get_instance()
        +__construct()
        +init()
        +register_exposer(class_name)
        +unregister_exposer(class_name)
        +check_class_name(class_name, root, prefix)
        +is_tainacan_request(request)
        +$request_has_url_param(request)
        +rest_request_after_callbacks(response, handler, request)
        +exposer_exists(exposer)
        +$request_has_exposer(request)
        +get_exposers(output)
        +filter_check_items_request(response, request)
        +$get_exposer_urls(base_url)
    }
```

## Constants

| Constant     | Visibility | Type | Value     |
|--------------|------------|------|-----------|
| `TYPE_PARAM` | public     |      | 'exposer' |

## Properties

### exposers

```php
protected $exposers
```

***

### instance

```php
private static $instance
```

* This property is **static**.

***

### request

```php
private static $request
```

* This property is **static**.

***

## Methods

### get_instance

```php
public static get_instance(): mixed
```

* This method is **static**.
***

### __construct

```php
public __construct(): mixed
```

***

### init

```php
public init(): mixed
```

***

### register_exposer

register exposers type

```php
public register_exposer(mixed $class_name): mixed
```

**Parameters:**

| Parameter     | Type      | Description                                     |
|---------------|-----------|-------------------------------------------------|
| `$class_name` | **mixed** | string \| object The class name or the instance |

***

### unregister_exposer

unregister exposers type

```php
public unregister_exposer(mixed $class_name): mixed
```

**Parameters:**

| Parameter     | Type      | Description                                     |
|---------------|-----------|-------------------------------------------------|
| `$class_name` | **mixed** | string \| object The class name or the instance |

***

### check_class_name

Return namespaced class name

```php
public check_class_name(string $class_name, bool $root = false, string $prefix = 'TainacanExposer\'): string
```

**Parameters:**

| Parameter     | Type       | Description |
|---------------|------------|-------------|
| `$class_name` | **string** |             |
| `$root`       | **bool**   |             |
| `$prefix`     | **string** |             |

***

### is_tainacan_request

check if is a tainacan request

```php
public is_tainacan_request(\WP_REST_Request $request): bool
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### request_has_url_param

check if query came from url

```php
public static request_has_url_param(\WP_REST_Request $request): mixed
```

* This method is **static**.
**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### rest_request_after_callbacks

adapt request response to exposer type

```php
public rest_request_after_callbacks(\WP_REST_Response $response, \WP_REST_Server $handler, \WP_REST_Request $request): \WP_REST_Response
```

**Parameters:**

| Parameter   | Type                  | Description |
|-------------|-----------------------|-------------|
| `$response` | **\WP_REST_Response** |             |
| `$handler`  | **\WP_REST_Server**   |             |
| `$request`  | **\WP_REST_Request**  |             |

***

### exposer_exists

Return if exposer is registered

```php
public exposer_exists(string $exposer): bool
```

**Parameters:**

| Parameter  | Type       | Description |
|------------|------------|-------------|
| `$exposer` | **string** |             |

***

### request_has_exposer

Return Exposer if request has exposer, false otherwise

```php
public static request_has_exposer(\WP_REST_Request $request): \Tainacan\Exposers\Exposer|bool
```

* This method is **static**.
**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

**Return Value:**

false

***

### get_exposers

Return list of registered exposers

```php
public get_exposers(string $output = ARRAY_N): array
```

**Parameters:**

| Parameter | Type       | Description                      |
|-----------|------------|----------------------------------|
| `$output` | **string** | output format, ARRAY_N or OBJECT |

**Return Value:**

of slug or array of \Tainacan\Exposers\Exposer

***

### filter_check_items_request

Filters Items request
and checks if current exposer (if any) supports this mapper.

```php
public filter_check_items_request(mixed $response, mixed $request): mixed
```

If it does not, return 404

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$response` | **mixed** |             |
| `$request`  | **mixed** |             |

***

### get_exposer_urls

```php
public static get_exposer_urls(string $base_url = ''): string|string[][]
```

* This method is **static**.
**Parameters:**

| Parameter   | Type       | Description                            |
|-------------|------------|----------------------------------------|
| `$base_url` | **string** | url base for exposer parameters append |

***
