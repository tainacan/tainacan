# REST_Items_Controller


REST API controller for managing Tainacan items.

Handles all REST API endpoints for item operations including
creation, updates, deletion, and querying of items within collections.

***

* Full name: `\Tainacan\API\EndPoints\REST_Items_Controller`
* Parent class: [`\Tainacan\API\REST_Controller`](../REST_Controller)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class REST_Items_Controller {
        -items_repository : mixed
        -item : mixed
        -item_metadata : mixed
        -collections_repository : mixed
        -metadatum_repository : mixed
        -terms_repository : mixed
        -filters_repository : mixed
        -taxonomies_repository : mixed
        -new_terms_ids : mixed
        +__construct()
        +init_objects()
        +register_routes()
        -add_metadata_to_item(item_object, item_array, args)
        +get_context_edit(item)
        +prepare_item_for_response(item, request)
        +get_item(request)
        +get_item_attachments(request)
        -prepare_filters_arguments(args, collection_id, ignore_filter_arguments)
        +get_items(request)
        +get_item_permissions_check(request)
        +get_item_attachments_permissions_check(request)
        +get_items_permissions_check(request)
        -get_items_permissions_check_for_taxonomy(taxonomies)
        +prepare_item_for_database(request)
        +create_item(request)
        +create_item_permissions_check(request)
        +delete_item(request)
        +delete_item_permissions_check(request)
        +update_item(request)
        +update_item_permissions_check(request)
        +duplicate_item(request)
        -submission_item_metadata(item_metadata, request)
        -submission_process_terms(value, taxonomy)
        -submission_rollback_new_terms()
        +submission_item(request)
        +submission_item_finish(request)
        +submission_item_permissions_check(request)
        -submission_item_check_recaptcha(request)
        +get_endpoint_args_for_item_schema(method)
        +get_wp_query_params()
        +process_request_filters(args)
        +get_attachments_schema()
        +get_schema()
    }
```

## Properties

### items_repository

```php
private $items_repository
```

***

### item

```php
private $item
```

***

### item_metadata

```php
private $item_metadata
```

***

### collections_repository

```php
private $collections_repository
```

***

### metadatum_repository

```php
private $metadatum_repository
```

***

### terms_repository

```php
private $terms_repository
```

***

### filters_repository

```php
private $filters_repository
```

***

### taxonomies_repository

```php
private $taxonomies_repository
```

***

### new_terms_ids

```php
private $new_terms_ids
```

***

## Methods

### __construct

REST_Items_Controller constructor.

```php
public __construct(): mixed
```

Define the namespace, rest base and instantiate your attributes.

***

### init_objects

Initialize objects after post_type register

```php
public init_objects(): mixed
```

***

### register_routes

Register items routes, and their endpoints

```php
public register_routes(): mixed
```

***

### add_metadata_to_item

```php
private add_metadata_to_item(mixed $item_object, mixed $item_array, array $args = []): mixed
```

**Parameters:**

| Parameter      | Type      | Description |
|----------------|-----------|-------------|
| `$item_object` | **mixed** |             |
| `$item_array`  | **mixed** |             |
| `$args`        | **array** |             |

***

### get_context_edit

```php
public get_context_edit(\Tainacan\Entities\Item $item): array
```

**Parameters:**

| Parameter | Type                        | Description |
|-----------|-----------------------------|-------------|
| `$item`   | **\Tainacan\Entities\Item** | \|int       |

***

### prepare_item_for_response

```php
public prepare_item_for_response(mixed $item, \WP_REST_Request $request): mixed|string|void|\WP_Error|\WP_REST_Response
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$item`    | **mixed**            |             |
| `$request` | **\WP_REST_Request** |             |

***

### get_item

```php
public get_item(\WP_REST_Request $request): \WP_Error|\WP_REST_Response
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### get_item_attachments

```php
public get_item_attachments(\WP_REST_Request $request): \WP_Error|\WP_REST_Response
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### prepare_filters_arguments

```php
private prepare_filters_arguments(array $args, mixed $collection_id = false, mixed $ignore_filter_arguments = []): array
```

**Parameters:**

| Parameter                  | Type      | Description                 |
|----------------------------|-----------|-----------------------------|
| `$args`                    | **array** | — array of query arguments. |
| `$collection_id`           | **mixed** |                             |
| `$ignore_filter_arguments` | **mixed** |                             |

**Throws:**

- [`Exception`](../../../Exception)

***

### get_items

```php
public get_items(\WP_REST_Request $request): \WP_Error|\WP_REST_Response
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

**Throws:**

- [`Exception`](../../../Exception)

***

### get_item_permissions_check

```php
public get_item_permissions_check(\WP_REST_Request $request): bool|\WP_Error
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### get_item_attachments_permissions_check

```php
public get_item_attachments_permissions_check(\WP_REST_Request $request): bool|\WP_Error
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### get_items_permissions_check

```php
public get_items_permissions_check(\WP_REST_Request $request): bool|\WP_Error
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### get_items_permissions_check_for_taxonomy

```php
private get_items_permissions_check_for_taxonomy(mixed $taxonomies): mixed
```

**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$taxonomies` | **mixed** |             |

***

### prepare_item_for_database

```php
public prepare_item_for_database(\WP_REST_Request $request): object|\Tainacan\Entities\Item|\WP_Error
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

**Throws:**

- [`Exception`](../../../Exception)

***

### create_item

```php
public create_item(\WP_REST_Request $request): \WP_Error|\WP_REST_Response
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

**Throws:**

- [`Exception`](../../../Exception)

***

### create_item_permissions_check

```php
public create_item_permissions_check(\WP_REST_Request $request): bool|\WP_Error
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

**Throws:**

- [`Exception`](../../../Exception)

***

### delete_item

```php
public delete_item(\WP_REST_Request $request): \WP_Error|\WP_REST_Response
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### delete_item_permissions_check

```php
public delete_item_permissions_check(\WP_REST_Request $request): bool|\WP_Error
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

**Throws:**

- [`Exception`](../../../Exception)

***

### update_item

```php
public update_item(\WP_REST_Request $request): \WP_Error|\WP_REST_Response
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### update_item_permissions_check

```php
public update_item_permissions_check(\WP_REST_Request $request): bool|\WP_Error
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

**Throws:**

- [`Exception`](../../../Exception)

***

### duplicate_item

```php
public duplicate_item(\WP_REST_Request $request): \WP_Error|\WP_REST_Response
```

**Parameters:**

| Parameter  | Type                 | Description |
|------------|----------------------|-------------|
| `$request` | **\WP_REST_Request** |             |

***

### submission_item_metadata

```php
private submission_item_metadata(\Tainacan\Entities\Item_Metadata_Entity& $item_metadata, mixed $request): mixed
```

**Parameters:**

| Parameter        | Type                                        | Description |
|------------------|---------------------------------------------|-------------|
| `$item_metadata` | **\Tainacan\Entities\Item_Metadata_Entity** |             |
| `$request`       | **mixed**                                   |             |

***

### submission_process_terms

```php
private submission_process_terms(mixed $value, mixed $taxonomy): mixed
```

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$value`    | **mixed** |             |
| `$taxonomy` | **mixed** |             |

***

### submission_rollback_new_terms

```php
private submission_rollback_new_terms(): mixed
```

***

### submission_item

```php
public submission_item(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### submission_item_finish

```php
public submission_item_finish(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### submission_item_permissions_check

```php
public submission_item_permissions_check(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

***

### submission_item_check_recaptcha

```php
private submission_item_check_recaptcha(mixed $request): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$request` | **mixed** |             |

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

### get_wp_query_params

Return the common params

```php
public get_wp_query_params(): array|void
```

***

### process_request_filters

```php
public process_request_filters(mixed $args): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$args`   | **mixed** |             |

***

### get_attachments_schema

```php
public get_attachments_schema(): mixed
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
