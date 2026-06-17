# REST_Reports_Controller


REST API controller for managing Tainacan reports.

Handles all REST API endpoints for report operations including
report generation, data analysis, and statistical reporting.

***

* Full name: `\Tainacan\API\EndPoints\REST_Reports_Controller`
* Parent class: [`\Tainacan\API\REST_Controller`](../REST_Controller)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class REST_Reports_Controller {
        -taxonomy_repository : mixed
        -metadatum_repository : mixed
        -collections_repository : mixed
        -prefix_transient_cahce : mixed
        +__construct()
        +init_objects()
        +register_routes()
        +reports_permissions_check(request)
        +get_collections(request)
        +get_summary(request)
        +get_taxonomies_list(request)
        +get_taxonomy(request)
        +get_metadata(request)
        +get_stats_collection_metadata(request)
        -query_item_metadata_distribution(meta_ids, collection_post_type)
        -query_count_used_taxononomies()
        +get_activities(request)
        -get_activities_general(collection_id, interval)
        -get_activities_general_by_user(collection_id, interval)
        -get_activities_users(collection_id)
        -get_cache_object(key, request)
        -set_cache_object(key, data)
        +get_endpoint_args_for_item_schema(method)
        +get_metadata_schema()
        +get_collection_schema()
        +get_collection_metadatum_schema()
        +get_collection_summary_schema()
        +get_taxonomy_schema()
        +get_taxonomy_terms_schema()
        +get_summary_schema()
        +get_activities_schema()
        +get_schema()
    }
```

## Properties

### taxonomy_repository

```php
private $taxonomy_repository
```

***

### metadatum_repository

```php
private $metadatum_repository
```

***

### collections_repository

```php
private $collections_repository
```

***

### prefix_transient_cahce

```php
private $prefix_transient_cahce
```

***

## Methods

### __construct

Constructor for the REST_Controller class.

```php
public __construct(): mixed
```

Sets up the namespace and registers routes and filters.

***

### init_objects

```php
public init_objects(): mixed
```

***

### register_routes

```php
public register_routes(): mixed
```

**Throws:**

- [`Exception`](../../../Exception)

***

### reports_permissions_check

```php
public reports_permissions_check(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### get_collections

```php
public get_collections(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### get_summary

```php
public get_summary(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### get_taxonomies_list

```php
public get_taxonomies_list(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### get_taxonomy

```php
public get_taxonomy(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### get_metadata

```php
public get_metadata(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### get_stats_collection_metadata

```php
public get_stats_collection_metadata(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### query_item_metadata_distribution

```php
private query_item_metadata_distribution(mixed $meta_ids, mixed $collection_post_type): mixed
```

**Parameters:**

| Parameter               | Type      | Description |
|-------------------------|-----------|-------------|
| `$meta_ids`             | **mixed** |             |
| `$collection_post_type` | **mixed** |             |

***

### query_count_used_taxononomies

```php
private query_count_used_taxononomies(): mixed
```

***

### get_activities

```php
public get_activities(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### get_activities_general

```php
private get_activities_general(mixed $collection_id = false, mixed $interval = false): mixed
```

**Parameters:**

| Parameter        | Type      | Description |
|------------------|-----------|-------------|
| `$collection_id` | **mixed** |             |
| `$interval`      | **mixed** |             |

***

### get_activities_general_by_user

```php
private get_activities_general_by_user(mixed $collection_id = false, mixed $interval = false): mixed
```

**Parameters:**

| Parameter        | Type      | Description |
|------------------|-----------|-------------|
| `$collection_id` | **mixed** |             |
| `$interval`      | **mixed** |             |

***

### get_activities_users

```php
private get_activities_users(mixed $collection_id = false): mixed
```

**Parameters:**

| Parameter        | Type      | Description |
|------------------|-----------|-------------|
| `$collection_id` | **mixed** |             |

***

### get_cache_object

```php
private get_cache_object(mixed $key, mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$key`     | **mixed** |             |
| `$request` | **mixed** |             |

***

### set_cache_object

```php
private set_cache_object(mixed $key, mixed $data): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$key`    | **mixed** |             |
| `$data`   | **mixed** |             |

***

### get_endpoint_args_for_item_schema

```php
public get_endpoint_args_for_item_schema(string $method = null): array|mixed
```

**Parameters:**

| Parameter | Type       | Description |
|-----------|------------|-------------|
| `$method` | **string** |             |

***

### get_metadata_schema

```php
public get_metadata_schema(): mixed
```

***

### get_collection_schema

```php
public get_collection_schema(): mixed
```

***

### get_collection_metadatum_schema

```php
public get_collection_metadatum_schema(): mixed
```

***

### get_collection_summary_schema

```php
public get_collection_summary_schema(): mixed
```

***

### get_taxonomy_schema

```php
public get_taxonomy_schema(): mixed
```

***

### get_taxonomy_terms_schema

```php
public get_taxonomy_terms_schema(): mixed
```

***

### get_summary_schema

```php
public get_summary_schema(): mixed
```

***

### get_activities_schema

```php
public get_activities_schema(): mixed
```

***

### get_schema

```php
public get_schema(): mixed
```

***

## Inherited methods

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

- [`Exception`](../../../Exception)

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
