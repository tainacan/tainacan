# Xml


Generate a Csv formated response

***

* Full name: `\Tainacan\Exposers\Xml`
* Parent class: [`\Tainacan\Exposers\Exposer`](./Exposer)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Exposer {
        #mappers : mixed
        +accept_no_mapper : mixed
        +slug : mixed
        -name : mixed
        -description : mixed
        #set_name(name)
        #set_description(description)
        +get_name()
        +get_description()
        +_toArray()
        +get_mappers()
    }
    class Xml {
        #extension : string
        +slug : mixed
        +name : mixed
        +rest_request_after_callbacks(response, handler, request)
        #array_to_xml(data, xml_data, namespace)
    }
    Exposer <|-- Xml
```

## Properties

### extension

{@inheritdoc}

```php
protected string $extension
```

**See Also:**

* \Tainacan\Exposers\Types\Type::extension

***

### slug

```php
public $slug
```

***

### name

```php
public $name
```

***

## Methods

### rest_request_after_callbacks

Change response after api callbacks

```php
public rest_request_after_callbacks(mixed $response, mixed $handler, mixed $request): \WP_REST_Response
```

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$response` | **mixed** |             |
| `$handler`  | **mixed** |             |
| `$request`  | **mixed** |             |

**See Also:**

* \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()

***

### array_to_xml

Convert Array to Xml

```php
protected array_to_xml(array $data, \SimpleXMLElement $xml_data, mixed $namespace = null): \SimpleXMLElement
```

**Parameters:**

| Parameter    | Type                  | Description |
|--------------|-----------------------|-------------|
| `$data`      | **array**             |             |
| `$xml_data`  | **\SimpleXMLElement** |             |
| `$namespace` | **mixed**             |             |

***

## Inherited methods

### set_name

Returns i18n exposer name

```php
protected set_name(mixed $name): string
```

Must be implemented by Exposer class

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$name`   | **mixed** |             |

***

### set_description

Sets i18n exposer description

```php
protected set_description(mixed $description): string
```

**Parameters:**

| Parameter      | Type      | Description |
|----------------|-----------|-------------|
| `$description` | **mixed** |             |

***

### get_name

Gets the exposer name

```php
public get_name(): string
```

**Return Value:**

exposer name

***

### get_description

Gets the exposer description

```php
public get_description(): string
```

**Return Value:**

exposer description

***

### _toArray

return exposer object as an array

```php
public _toArray(): array
```

***

### rest_request_after_callbacks

Change response after api callbacks

```php
public rest_request_after_callbacks(\WP_REST_Response $response, \WP_REST_Server $handler, \WP_REST_Request $request): \WP_REST_Response
```

* This method is **abstract**.
**Parameters:**

| Parameter   | Type                  | Description |
|-------------|-----------------------|-------------|
| `$response` | **\WP_REST_Response** |             |
| `$handler`  | **\WP_REST_Server**   |             |
| `$request`  | **\WP_REST_Request**  |             |

***

### get_mappers

Return list of supported mappers for this type

```php
public get_mappers(): array
```

**Return Value:**

List of mappers

***
