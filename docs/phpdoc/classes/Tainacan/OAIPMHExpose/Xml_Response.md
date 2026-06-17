# Xml_Response


Generate an XML response to a request if no error has occured

This is the class to further develop to suits a publication need

***

* Full name: `\Tainacan\OAIPMHExpose\Xml_Response`
* Parent class: [`\Tainacan\OAIPMHExpose\Xml_Create`](./Xml_Create)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Xml_Response {
        +verbNode : mixed
        #verb : mixed
        +__construct(par_array)
        +add2_verbNode(nodeName, value)
        +create_record()
        +create_header(identifier, timestamp, ands_class, add_to_node, is_deleted)
        +create_metadata(mom_record_node)
        +create_resumpToken(token, expirationdatetime, num_rows, cursor)
    }
    class Xml_Create {
        +doc : mixed
        +__construct(par_array)
        +addChild(mom_node, name, value)
        +addChildDC(mom_node, name, value)
        +create_request(par_array)
        +display()
    }
    Xml_Create <|-- Xml_Response
```

## Properties

### verbNode

```php
public $verbNode
```

***

### verb

```php
protected $verb
```

***

## Methods

### __construct

Constructs an Xml_Create object.

```php
public __construct(mixed $par_array): mixed
```

**Parameters:**

| Parameter    | Type      | Description                                                                                      |
|--------------|-----------|--------------------------------------------------------------------------------------------------|
| `$par_array` | **mixed** | Type: array.
  Array of request parameters for creating an ANDS_XML object.
\see create_request. |

***

### add2_verbNode

Add direct child nodes to verb node (OAI-PMH), e.g. response to ListMetadataFormats.

```php
public add2_verbNode(mixed $nodeName, mixed $value = null): mixed
```

Different verbs can have different required child nodes.
 \see create_record, create_header
\see http://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm.

\param $nodeName Type: string. The name of appending node.
\param $value Type: string. The content of appending node.

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$nodeName` | **mixed** |             |
| `$value`    | **mixed** |             |

***

### create_record

Create an empty \<record\> node. Other nodes will be appended to it later.

```php
public create_record(): mixed
```

***

### create_header

Headers are enclosed inside of \<record\> to the query of ListRecords, ListIdentifiers and etc.

```php
public create_header(mixed $identifier, mixed $timestamp, mixed $ands_class, mixed $add_to_node = null, mixed $is_deleted = false): mixed
```

\param $identifier Type: string. The identifier string for node \<identifier\>.
\param $timestamp Type: timestamp. Timestapme in UTC format for node \<datastamp\>.
\param $ands_class Type: mix. Can be an array or just a string. Content of \<setSpec\>.
\param $add_to_node Type: DOMElement. Default value is null.
In normal cases, $add_to_node is the \<record\> node created previously. When it is null, the newly created header node is attatched to $this->verbNode.
Otherwise it will be attatched to the desired node defined in $add_to_node.

**Parameters:**

| Parameter      | Type      | Description |
|----------------|-----------|-------------|
| `$identifier`  | **mixed** |             |
| `$timestamp`   | **mixed** |             |
| `$ands_class`  | **mixed** |             |
| `$add_to_node` | **mixed** |             |
| `$is_deleted`  | **mixed** |             |

***

### create_metadata

Create metadata node for holding metadata. This is always added to \<record\> node.

```php
public create_metadata(mixed $mom_record_node): mixed
```

\param $mom_record_node DOMElement. A node acts as the parent node.

**Parameters:**

| Parameter          | Type      | Description |
|--------------------|-----------|-------------|
| `$mom_record_node` | **mixed** |             |

***

### create_resumpToken

If there are too many records request could not finished a resumpToken is generated to let harvester know

```php
public create_resumpToken(mixed $token, mixed $expirationdatetime, mixed $num_rows, mixed $cursor = null): mixed
```

\param $token Type: string. A random number created somewhere?
\param $expirationdatetime Type: string. A string representing time.
\param $num_rows Type: integer. Number of records retrieved.
\param $cursor Type: string. Cursor can be used for database to retrieve next time.

**Parameters:**

| Parameter             | Type      | Description |
|-----------------------|-----------|-------------|
| `$token`              | **mixed** |             |
| `$expirationdatetime` | **mixed** |             |
| `$num_rows`           | **mixed** |             |
| `$cursor`             | **mixed** |             |

***

## Inherited methods

### __construct

Constructs an Xml_Create object.

```php
public __construct(mixed $par_array): mixed
```

**Parameters:**

| Parameter    | Type      | Description                                                                                      |
|--------------|-----------|--------------------------------------------------------------------------------------------------|
| `$par_array` | **mixed** | Type: array.
  Array of request parameters for creating an ANDS_XML object.
\see create_request. |

***

### addChild

Add a child node to a parent node on a XML Doc: a worker function.

```php
public addChild(mixed $mom_node, mixed $name, mixed $value = ''): \Tainacan\OAIPMHExpose\DOMElement
```

**Parameters:**

| Parameter   | Type      | Description                                                   |
|-------------|-----------|---------------------------------------------------------------|
| `$mom_node` | **mixed** | 
Type: DOMNode. The target node.                              |
| `$name`     | **mixed** | 
Type: string. The name of child nade is being added          |
| `$value`    | **mixed** | 
Type: string. Text for the adding node if it is a text node. |

**Return Value:**

$added_node
The newly created node, can be used for further expansion.
If no further expansion is expected, return value can be igored.

***

### addChildDC

Add a child node to a parent node on a XML Doc: a worker function.

```php
public addChildDC(mixed $mom_node, mixed $name, mixed $value = ''): \Tainacan\OAIPMHExpose\DOMElement
```

**Parameters:**

| Parameter   | Type      | Description                                                   |
|-------------|-----------|---------------------------------------------------------------|
| `$mom_node` | **mixed** | 
Type: DOMNode. The target node.                              |
| `$name`     | **mixed** | 
Type: string. The name of child nade is being added          |
| `$value`    | **mixed** | 
Type: string. Text for the adding node if it is a text node. |

**Return Value:**

$added_node
The newly created node, can be used for further expansion.
If no further expansion is expected, return value can be igored.

***

### create_request

Create an OAI request node.

```php
public create_request(mixed $par_array): mixed
```

**Parameters:**

| Parameter    | Type      | Description                                                                                                                                                                                                   |
|--------------|-----------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$par_array` | **mixed** | Type: array
  The attributes of a request node. They describe the verb of the request and other associated parameters used in the request.
Keys of the array define attributes, and values are their content. |

***

### display

Display a doc in a readable, well-formatted way for display or saving

```php
public display(): mixed
```

***
