# Media


Handles media functionality for Tainacan.

Provides methods for managing images, attachments, and media-related features
including custom image sizes, attachment pages, and content indexing.

***

* Full name: `\Tainacan\Media`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Media {
        -file_name : string|null
        -attachment_html_url_base : string
        +content_index_meta : string
        +content_index_last : string
        -THROW_EXCPTION_ON_FATAL_ERROR : mixed
        #init()
        +add_image_sizes()
        +add_image_sizes_to_admin(sizes)
        +add_attachment_page_rewrite_rule()
        +add_css()
        +attachment_page_add_var(vars)
        -flush_buffers()
        -get_file_name_from_url(url)
        +insert_attachment_from_url(url, post_id)
        +insert_attachment_from_file(filename, post_id)
        +save_remote_file(url)
        +insert_attachment_from_blob(blob, filename, post_id)
        +get_mime_content_type(filename)
        +get_pdf_cover(filepath)
        +shutdown_function()
        +index_pdf_content(file, item_id)
        +get_attachment_html_url(attachment_id)
        +attachment_page()
        +get_default_image_blurhash()
        +get_image_blurhash(file_path, width, height)
    }
```

## Properties

### file_name

Current file name being processed.

```php
private static string|null $file_name
```

* This property is **static**.

***

### attachment_html_url_base

Base URL slug for attachment HTML pages.

```php
private string $attachment_html_url_base
```

***

### content_index_meta

Meta key for document content indexing.

```php
public static string $content_index_meta
```

* This property is **static**.

***

### content_index_last

Meta key for document content last index metadata.

```php
public static string $content_index_last
```

* This property is **static**.

***

### THROW_EXCPTION_ON_FATAL_ERROR

```php
private $THROW_EXCPTION_ON_FATAL_ERROR
```

***

## Methods

### init

Initializes the media functionality.

```php
protected init(): void
```

Sets up rewrite rules, query vars, and image sizes for Tainacan media handling.

***

### add_image_sizes

Registers custom image sizes for Tainacan.

```php
public add_image_sizes(): void
```

***

### add_image_sizes_to_admin

Adds custom image sizes to the admin interface.

```php
public add_image_sizes_to_admin(array $sizes): array
```

**Parameters:**

| Parameter | Type      | Description                  |
|-----------|-----------|------------------------------|
| `$sizes`  | **array** | Existing image size options. |

**Return Value:**

Modified image size options.

***

### add_attachment_page_rewrite_rule

Adds rewrite rule for attachment HTML pages.

```php
public add_attachment_page_rewrite_rule(): void
```

***

### add_css

```php
public add_css(): mixed
```

***

### attachment_page_add_var

```php
public attachment_page_add_var(mixed $vars): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$vars`   | **mixed** |             |

***

### flush_buffers

```php
private flush_buffers(): mixed
```

***

### get_file_name_from_url

```php
private get_file_name_from_url(mixed $url): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$url`    | **mixed** |             |

***

### insert_attachment_from_url

Insert an attachment from an URL address.

```php
public insert_attachment_from_url(string $url, int $post_id = null): int|false
```

**Parameters:**

| Parameter  | Type       | Description                                                                |
|------------|------------|----------------------------------------------------------------------------|
| `$url`     | **string** |                                                                            |
| `$post_id` | **int**    | (optional) the post this attachement should be attached to. empty for none |

**Return Value:**

Attachment ID. False on failure

***

### insert_attachment_from_file

Insert an attachment from a local file.

```php
public insert_attachment_from_file(string $filename, int $post_id = null): int|false
```

**Parameters:**

| Parameter   | Type       | Description                                                                |
|-------------|------------|----------------------------------------------------------------------------|
| `$filename` | **string** | The path to the file                                                       |
| `$post_id`  | **int**    | (optional) the post this attachement should be attached to. empty for none |

**Return Value:**

Attachment ID. False on failure

***

### save_remote_file

Avoid memory overflow problems with large files (Exceeded maximum memory limit of PHP)

```php
public save_remote_file(mixed $url): string
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$url`    | **mixed** |             |

**Return Value:**

the file path

***

### insert_attachment_from_blob

Insert an attachment from an URL address.

```php
public insert_attachment_from_blob(\Tainacan\blob $blob, string $filename, int $post_id = null): int|false
```

**Parameters:**

| Parameter   | Type               | Description                                                                |
|-------------|--------------------|----------------------------------------------------------------------------|
| `$blob`     | **\Tainacan\blob** | bitstream of the attachment                                                |
| `$filename` | **string**         | The filename that will be created                                          |
| `$post_id`  | **int**            | (optional) the post this attachement should be attached to. empty for none |

**Return Value:**

Attachment ID. False on failure

***

### get_mime_content_type

Add support to get mime type content even when mime_content_type function is not available

```php
public get_mime_content_type(string $filename): string
```

**Parameters:**

| Parameter   | Type       | Description                          |
|-------------|------------|--------------------------------------|
| `$filename` | **string** | The file name to check the mime type |

**Return Value:**

mime type           @see \mime_content_type()

***

### get_pdf_cover

Extract an image from the first page of a pdf file

```php
public get_pdf_cover(string $filepath): \Tainacan\blob
```

**Parameters:**

| Parameter   | Type       | Description                    |
|-------------|------------|--------------------------------|
| `$filepath` | **string** | The pdf filepath in the server |

**Return Value:**

bitstream of the image in jpg format

***

### shutdown_function

```php
public shutdown_function(): mixed
```

***

### index_pdf_content

```php
public index_pdf_content(mixed $file, mixed $item_id): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$file`    | **mixed** |             |
| `$item_id` | **mixed** |             |

***

### get_attachment_html_url

```php
public get_attachment_html_url(mixed $attachment_id): mixed
```

**Parameters:**

| Parameter        | Type      | Description |
|------------------|-----------|-------------|
| `$attachment_id` | **mixed** |             |

***

### attachment_page

```php
public attachment_page(): mixed
```

***

### get_default_image_blurhash

```php
public get_default_image_blurhash(): mixed
```

***

### get_image_blurhash

```php
public get_image_blurhash(mixed $file_path, mixed $width, mixed $height): mixed
```

**Parameters:**

| Parameter    | Type      | Description |
|--------------|-----------|-------------|
| `$file_path` | **mixed** |             |
| `$width`     | **mixed** |             |
| `$height`    | **mixed** |             |

***

## Inherited methods

### get_instance

```php
public static get_instance(): mixed
```

* This method is **static**.
***

### __construct

```php
private __construct(): mixed
```

***
