# Inbcm_Archive


***

* Full name: `\Tainacan\Mappers\Inbcm_Archive`
* Parent class: [`\Tainacan\Mappers\Mapper`](./Mapper)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Mapper {
        +slug : mixed
        +name : mixed
        +allow_extra_metadata : mixed
        +context_url : mixed
        +type : mixed
        +metadata : array
        +prefix : mixed
        +sufix : mixed
        +header : mixed
        +show_ui : mixed
        +_toArray()
        +get_url(meta_slug)
        +__construct()
    }
    class Inbcm_Archive {
        +slug : mixed
        +name : mixed
        +allow_extra_metadata : mixed
        +__construct()
    }
    Mapper <|-- Inbcm_Archive
```

## Properties

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

### allow_extra_metadata

```php
public $allow_extra_metadata
```

***

## Methods

### __construct

```php
public __construct(): mixed
```

***

## Inherited methods

### _toArray

```php
public _toArray(): mixed
```

***

### get_url

Gets the semantic URL for a given metadatum of this mapper.

```php
public get_url(string $meta_slug): string
```

Basically it identifies the property prefix and replace it with the URL of that prefix

**Parameters:**

| Parameter    | Type       | Description                                                         |
|--------------|------------|---------------------------------------------------------------------|
| `$meta_slug` | **string** | The slug of the metadata present in this mapper to get the URL from |

**Return Value:**

The semantic URL for this metadata. Empty string in case of failure

***

### __construct

```php
public __construct(): mixed
```

***
