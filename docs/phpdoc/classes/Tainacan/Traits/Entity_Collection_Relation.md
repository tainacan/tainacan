# Entity_Collection_Relation


Defines Collection and Items relation

***

* Full name: `\Tainacan\Traits\Entity_Collection_Relation`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Entity_Collection_Relation {
        #collection : mixed
        +get_collection_id()
        +get_collection()
        +set_collection_id(value)
        +set_collection(collection)
    }
```

## Properties

### collection

```php
protected $collection
```

***

## Methods

### get_collection_id

```php
public get_collection_id(): int
```

**Return Value:**

collection item ID

***
### get_collection

Return Collection from relation

```php
public get_collection(): \Tainacan\Entities\Collection|null
```

**Return Value:**

Return Collection or null on errors

***
### set_collection_id

Set collection ID

```php
public set_collection_id(int $value): mixed
```

**Parameters:**

| Parameter | Type    | Description |
|-----------|---------|-------------|
| `$value`  | **int** |             |

***
### set_collection

set collection object and id

```php
public set_collection(\Tainacan\Entities\Collection $collection): mixed
```

**Parameters:**

| Parameter     | Type                              | Description |
|---------------|-----------------------------------|-------------|
| `$collection` | **\Tainacan\Entities\Collection** |             |

***
