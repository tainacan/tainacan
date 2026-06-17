# Cli_Move_Attachments


Handles WP-CLI commands for Tainacan attachment migration operations.

Provides command-line interface for migrating attachments to the new
directory structure introduced in Tainacan version 0.11.

***

* Full name: `\Tainacan\Cli_Move_Attachments`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Cli_Move_Attachments {
        -collections : mixed
        -documents : mixed
        +__invoke(args, assoc_args)
        -is_item_attachment(att)
        -is_document(attachment_id)
        -get_collection(id)
    }
```

## Properties

### collections

```php
private $collections
```

***

### documents

```php
private $documents
```

***

## Methods

### __invoke

Moves the items documents and attachments to a $collection_id/$item_id directory structure

```php
public __invoke(mixed $args, mixed $assoc_args): mixed
```

This is only to be used to update the structure of intallations made before version 0.11 of Tainacan, when
this structure was implemented.

See (URL to docs) for more information

## OPTIONS

[--dry-run]
: Look for attachments but don't move them, just output a report

**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$args`       | **mixed** |             |
| `$assoc_args` | **mixed** |             |

***

### is_item_attachment

```php
private is_item_attachment(mixed $att): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$att`    | **mixed** |             |

***

### is_document

```php
private is_document(mixed $attachment_id): mixed
```

**Parameters:**

| Parameter        | Type      | Description |
|------------------|-----------|-------------|
| `$attachment_id` | **mixed** |             |

***

### get_collection

```php
private get_collection(mixed $id): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$id`     | **mixed** |             |

***
