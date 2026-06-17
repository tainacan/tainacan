# REST_Controller


Abstract base class for Tainacan REST API controllers.

Provides common functionality for all Tainacan REST API endpoints including
object filtering, attribute handling, and standard WordPress REST API integration.

***

* Full name: `\Tainacan\API\REST_Controller`
* Parent class: [`WP_REST_Controller`](../../WP_REST_Controller)
* This class is an **Abstract class**

## Class Diagram

```mermaid
classDiagram
    direction TB
    class REST_Controller {
        +__construct()
        #filter_object_by_attributes(object, attributes)
        #prepare_item_for_updating(object, new_values)
        #prepare_filters(request)
        +add_support_to_tax_query_like(args)
        #sanitize_value(value)
        -prepare_meta(mapped, request, query, mapped_v, args)
        #contains_array(array, query)
        +get_fetch_only_param()
        +get_wp_query_params()
        #get_meta_queries_params()
        +get_repository_schema(repository)
        +get_permissions_schema()
        +get_base_properties_schema()
        +get_list_schema()
    }
```

## Methods

### __construct

Constructor for the REST_Controller class.

```php
public __construct(): mixed
```

Sets up the namespace and registers routes and filters.

***

### filter_object_by_attributes

Filters an object by specified attributes.

```php
protected filter_object_by_attributes(mixed $object, string|array $attributes): array
```

**Parameters:**

| Parameter     | Type              | Description                                       |
|---------------|-------------------|---------------------------------------------------|
| `$object`     | **mixed**         | The object to filter.                             |
| `$attributes` | **string\|array** | The attributes to include in the filtered result. |

**Return Value:**

Filtered object data.

***

### prepare_item_for_updating

Prepares an item for updating with new values.

```php
protected prepare_item_for_updating(mixed $object, array $new_values): \Tainacan\Entities\Entity
```

**Parameters:**

| Parameter     | Type      | Description                      |
|---------------|-----------|----------------------------------|
| `$object`     | **mixed** | The object to update.            |
| `$new_values` | **array** | New values to set on the object. |

**Return Value:**

The updated entity.

***

### prepare_filters

```php
protected prepare_filters(mixed $request): array
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

**Throws:**

- [`Exception`](../../Exception)

***

### add_support_to_tax_query_like

```php
public add_support_to_tax_query_like(mixed $args): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$args`   | **mixed** |             |

***

### sanitize_value

```php
protected sanitize_value(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### prepare_meta

```php
private prepare_meta(mixed $mapped, mixed $request, mixed $query, mixed $mapped_v, mixed $args): mixed
```

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$mapped`   | **mixed** |             |
| `$request`  | **mixed** |             |
| `$query`    | **mixed** |             |
| `$mapped_v` | **mixed** |             |
| `$args`     | **mixed** |             |

**Throws:**

- [`Exception`](../../Exception)

***

### contains_array

```php
protected contains_array(mixed $array, mixed $query): bool
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$array`  | **mixed** |             |
| `$query`  | **mixed** |             |

***

### get_fetch_only_param

Return the fetch_only param

```php
public get_fetch_only_param(): array|void
```

***

### get_wp_query_params

Return the common params

```php
public get_wp_query_params(): array|void
```

***

### get_meta_queries_params

Return the common meta, date and tax queries params

```php
protected get_meta_queries_params(): array
```

***

### get_repository_schema

```php
public get_repository_schema(\Tainacan\Repositories\Repository $repository): mixed
```

**Parameters:**

| Parameter     | Type                                  | Description |
|---------------|---------------------------------------|-------------|
| `$repository` | **\Tainacan\Repositories\Repository** |             |

***

### get_permissions_schema

```php
public get_permissions_schema(): mixed
```

***

### get_base_properties_schema

```php
public get_base_properties_schema(): mixed
```

***

### get_schema

```php
protected get_schema(): mixed
```

* This method is **abstract**.
***

### get_list_schema

```php
public get_list_schema(): mixed
```

***
