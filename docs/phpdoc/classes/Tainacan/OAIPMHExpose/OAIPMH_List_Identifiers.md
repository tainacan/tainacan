# OAIPMH_List_Identifiers


Support Dublin Core Mapping
http://purl.org/dc/elements/1.1/

***

* Full name: `\Tainacan\OAIPMHExpose\OAIPMH_List_Identifiers`
* Parent class: [`\Tainacan\OAIPMHExpose\OAIPMH_Expose`](./OAIPMH_Expose)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class OAIPMH_List_Identifiers {
        #working_node : mixed
        +errors : mixed
        +xml_creater : mixed
        +restoken : mixed
        +expirationdatetime : mixed
        +num_rows : mixed
        +cursor : mixed
        +deliveredrecords : mixed
        +from : mixed
        +until : mixed
        +sets : mixed
        +metadataPrefix : mixed
        +__construct()
        +list_collections()
        +get_items()
        +list_identifiers(data)
        +initiate_variables(data)
        +verify_resumption_token(numRows)
        +add_resumption_token_xml(numRows)
    }
    class OAIPMH_Expose {
        +identifyResponse : mixed
        +deletedRecord : mixed
        +adminEmail : mixed
        +compression : mixed
        +expirationdatetime : mixed
        +delimiter : mixed
        +show_identifier : mixed
        +SETS : mixed
        +METADATAFORMATS : mixed
        +supported_formats : mixed
        +MAXRECORDS : mixed
        +CONTENT_TYPE : mixed
        +charset : mixed
        +xmlescaped : mixed
        +text : mixed
        +code : mixed
        +token_valid : mixed
        +token_prefix : mixed
        #collection_repository : mixed
        #item_repository : mixed
        #repositoryIdentifier : mixed
        +config()
        +is_valid_uri(url)
        +is_valid_attrb(attrb)
        +formatDatestamp(datestamp)
        +checkDateFormat(date)
        +prepare_set_names()
        +oai_exit(args, errors)
        #create_token_dir()
        +get_token()
        +createResumToken(cursor, from, until, sets, metadataPrefix)
        +readResumToken(resumptionToken)
        +oai_error(code, argument, value)
        +get_metadata_formats(item_id)
    }
    OAIPMH_Expose <|-- OAIPMH_List_Identifiers
```

## Properties

### working_node

```php
protected $working_node
```

***

### errors

```php
public $errors
```

***

### xml_creater

```php
public $xml_creater
```

***

### restoken

```php
public $restoken
```

***

### expirationdatetime

```php
public $expirationdatetime
```

***

### num_rows

```php
public $num_rows
```

***

### cursor

```php
public $cursor
```

***

### deliveredrecords

```php
public $deliveredrecords
```

***

### from

```php
public $from
```

***

### until

```php
public $until
```

***

### sets

```php
public $sets
```

***

### metadataPrefix

```php
public $metadataPrefix
```

***

## Methods

### __construct

```php
public __construct(): mixed
```

***

### list_collections

```php
public list_collections(): array
```

**Throws:**

- [`Exception`](../../Exception)

***

### get_items

main method return the items, filtered or not filtered

```php
public get_items(): array
```

***

### list_identifiers

```php
public list_identifiers(mixed $data): \Tainacan\OAIPMHExpose\mostra
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$data`   | **mixed** |             |

**Return Value:**

o xml do list record desejado

***

### initiate_variables

```php
public initiate_variables(mixed $data): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$data`   | **mixed** |             |

***

### verify_resumption_token

```php
public verify_resumption_token(mixed $numRows): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$numRows` | **mixed** |             |

***

### add_resumption_token_xml

```php
public add_resumption_token_xml(mixed $numRows): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$numRows` | **mixed** |             |

***

## Inherited methods

### config

```php
public config(): mixed
```

***

### is_valid_uri

```php
public is_valid_uri(mixed $url): bool
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$url`    | **mixed** |             |

***

### is_valid_attrb

```php
public is_valid_attrb(mixed $attrb): false|int
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$attrb`  | **mixed** |             |

***

### formatDatestamp

```php
public formatDatestamp(mixed $datestamp): false|string
```

**Parameters:**

| Parameter    | Type      | Description |
|--------------|-----------|-------------|
| `$datestamp` | **mixed** |             |

***

### checkDateFormat

```php
public checkDateFormat(mixed $date): bool|false|string
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$date`   | **mixed** |             |

***

### prepare_set_names

```php
public prepare_set_names(): array
```

***

### oai_exit

```php
public oai_exit(mixed $args, mixed $errors): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$args`   | **mixed** |             |
| `$errors` | **mixed** |             |

***

### create_token_dir

```php
protected create_token_dir(): bool|string
```

***

### get_token

Generate a string based on the current Unix timestamp in microseconds for creating resumToken file name.

```php
public get_token(): mixed
```

***

### createResumToken

Create a token file.

```php
public createResumToken(mixed $cursor, mixed $from, mixed $until, mixed $sets, mixed $metadataPrefix): mixed
```

It has three parts which is separated by '#': cursor, extension of query, metadataPrefix.
Called by listrecords.php.

**Parameters:**

| Parameter         | Type      | Description |
|-------------------|-----------|-------------|
| `$cursor`         | **mixed** |             |
| `$from`           | **mixed** |             |
| `$until`          | **mixed** |             |
| `$sets`           | **mixed** |             |
| `$metadataPrefix` | **mixed** |             |

***

### readResumToken

Read a saved ResumToken

```php
public readResumToken(mixed $resumptionToken): mixed
```

**Parameters:**

| Parameter          | Type      | Description |
|--------------------|-----------|-------------|
| `$resumptionToken` | **mixed** |             |

***

### oai_error

utility funciton to mapping error codes to readable messages

```php
public oai_error(mixed $code, mixed $argument = '', mixed $value = ''): mixed
```

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$code`     | **mixed** |             |
| `$argument` | **mixed** |             |
| `$value`    | **mixed** |             |

***

### get_metadata_formats

function get_metadata_formats

```php
public get_metadata_formats(int $item_id = null): bool
```

**Parameters:**

| Parameter  | Type    | Description |
|------------|---------|-------------|
| `$item_id` | **int** |             |

***
