# Item


Represents a Tainacan Item entity.

Items are the main content entities in Tainacan, containing
metadata values, attachments, and relationships within collections.

***

* Full name: `\Tainacan\Entities\Item`
* Parent class: [`\Tainacan\Entities\Entity`](./Entity)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Item {
        #terms : mixed
        #repository : string
        +__construct(which)
        +__toString()
        +_toArray()
        +set_terms(value)
        +get_terms()
        +get_attachments(exclude)
        +get_author_name()
        +get_author_login()
        +get_thumbnail()
        +get_thumbnail_blurhash()
        +set__thumbnail_id(id)
        +get__thumbnail_id()
        +get_modification_date()
        +get_creation_date()
        +get_author_id()
        +get_url()
        +get_id()
        +get_title()
        +get_slug()
        +get_order()
        +get_parent()
        +get_description()
        +get_document_type()
        +get_document_options()
        +get_document_mimetype()
        +get_document()
        +get_db_identifier()
        +get_capabilities()
        +get_comment_status()
        +set_title(value)
        +set_slug(value)
        +set_order(value)
        +set_creation_date(value)
        +set_parent(value)
        +set_document_type(value)
        +set_document_options(value)
        +set_document(value)
        +set_description(value)
        +set_author_id(author_id)
        +get_metadata(args)
        #set_cap()
        +set_comment_status(value)
        +validate()
        +validate_core_metadata()
        +_toHtml()
        +get_metadata_as_html(args)
        +get_item_metadatum_as_html(item_metadatum, args, metadatum_index)
        +get_document_as_html(img_size)
        +get_attachment_as_html(attachment, img_size)
        +get_edit_url()
        +get_document_download_url()
        +get_related_items(args)
        +get_metadata_sections_as_html(args)
        +get_metadata_section_as_html(metadata_section, args, section_index)
    }
    class Entity {
        #repository : Repository
        -errors : array
        #post_type : string|false
        #capability_type : string|false
        +WP_Post : WP_Post
        -validated : bool
        +cap : object
        +__construct(which)
        +get_repository()
        +get_date_i18n(date)
        +get_mapped_property(prop)
        #set_mapped_property(prop, value)
        +set(prop, value)
        +get(prop)
        +set_status(value)
        +validate()
        +validate_prop(prop)
        +get_errors()
        +$get_post_type()
        +$get_capability_type()
        +get_status()
        +get_db_identifier()
        +get_id()
        +add_error(type, message)
        +reset_errors()
        +get_validated()
        #set_validated(value)
        #set_as_valid()
        +_toArray()
        +_toJson()
        +can_read(user)
        +can_edit(user)
        +can_delete(user)
        +can_publish(user)
        +get_capabilities()
        +diff(which)
    }
    Entity ..> Entity
    Entity ..> Item
    Entity <|-- Item
```

## Properties

### terms

```php
protected $terms
```

***

### diplay_name

```php
protected $diplay_name
```

***

### full

```php
protected $full
```

***

### _thumbnail_id

```php
protected $_thumbnail_id
```

***

### modification_date

```php
protected $modification_date
```

***

### creation_date

```php
protected $creation_date
```

***

### author_id

```php
protected $author_id
```

***

### url

```php
protected $url
```

***

### id

```php
protected $id
```

***

### slug

```php
protected $slug
```

***

### title

```php
protected $title
```

***

### order

```php
protected $order
```

***

### parent

```php
protected $parent
```

***

### decription

```php
protected $decription
```

***

### document_type

```php
protected $document_type
```

***

### document

```php
protected $document
```

***

### document_options

```php
protected $document_options
```

***

### collection_id

```php
protected $collection_id
```

***

### repository

The repository instance for this entity.

```php
protected string $repository
```

**See Also:**

* \Tainacan\Entities\Entity::repository

***

## Methods

### __construct

Create an instance of Entity

```php
public __construct(mixed $which): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$which`  | **mixed** |             |

***

### __toString

```php
public __toString(): mixed
```

***

### _toArray

```php
public _toArray(): mixed
```

***

### set_terms

```php
public set_terms(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### get_terms

```php
public get_terms(): mixed|null
```

***

### get_attachments

```php
public get_attachments(null $exclude = null): array
```

**Parameters:**

| Parameter  | Type     | Description |
|------------|----------|-------------|
| `$exclude` | **null** |             |

***

### get_author_name

```php
public get_author_name(): string
```

***

### get_author_login

```php
public get_author_login(): string
```

***

### get_thumbnail

Gets the thumbnail list of files

```php
public get_thumbnail(): array
```

Each size is represented as an array in the format returned by

**See Also:**

* https://developer.wordpress.org/reference/functions/wp_get_attachment_image_src/

***

### get_thumbnail_blurhash

```php
public get_thumbnail_blurhash(): mixed
```

***

### set__thumbnail_id

```php
public set__thumbnail_id(mixed $id): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$id`     | **mixed** |             |

***

### get__thumbnail_id

```php
public get__thumbnail_id(): int|string
```

***

### get_modification_date

```php
public get_modification_date(): mixed|null
```

***

### get_creation_date

```php
public get_creation_date(): mixed|null
```

***

### get_author_id

```php
public get_author_id(): mixed|null
```

***

### get_url

```php
public get_url(): mixed|null
```

***

### get_id

Return the item ID

```php
public get_id(): int
```

***

### get_title

Return the item title

```php
public get_title(): string
```

***

### get_slug

Get item slug

```php
public get_slug(): string
```

***

### get_order

Return the item order type

```php
public get_order(): string
```

***

### get_parent

Return the parent ID

```php
public get_parent(): int
```

***

### get_description

Return the item description

```php
public get_description(): string
```

***

### get_document_type

Return the item document type

```php
public get_document_type(): string
```

***

### get_document_options

Return the item document options

```php
public get_document_options(): string
```

***

### get_document_mimetype

Return the document mimetype

```php
public get_document_mimetype(): string
```

***

### get_document

Return the item document

```php
public get_document(): string
```

***

### get_db_identifier

Get entity DB identifier

```php
public get_db_identifier(): string
```

**See Also:**

* \Tainacan\Entities\Entity::get_db_identifier()

***

### get_capabilities

Use especial Item capabilities
{@inheritDoc}

```php
public get_capabilities(): object
```

**Return Value:**

Object with all the capabilities as member variables.

**See Also:**

* \Tainacan\Entities\Entity::get_capabilities()

***

### get_comment_status

Checks if comments are allowed for the current Collection.

```php
public get_comment_status(): string
```

**Return Value:**

"open"|"closed"

***

### set_title

Define the title

```php
public set_title(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_slug

Set the item slug

```php
public set_slug(mixed $value): void
```

If you dont set the item slug, it will be set automatically based on the name and
following WordPress default behavior of creating slugs for posts.

If you set the slug for an existing one, WordPress will append a number at the end of in order
to make it unique (e.g slug-1, slug-2)

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_order

Define the order type

```php
public set_order(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_creation_date

Define the creation date

```php
public set_creation_date(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_parent

Define the parent ID

```php
public set_parent(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_document_type

Define the document type

```php
public set_document_type(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_document_options

Define the document options

```php
public set_document_options(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_document

Define the document

```php
public set_document(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_description

Define the description

```php
public set_description(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_author_id

Define the author id

```php
public set_author_id(mixed $author_id): void
```

**Parameters:**

| Parameter    | Type      | Description |
|--------------|-----------|-------------|
| `$author_id` | **mixed** |             |

***

### get_metadata

Return a List of ItemMetadata objects

```php
public get_metadata(mixed $args = []): array
```

It will return all metadata associeated with the collection this item is part of.

If the item already has a value for any of the metadata, it will be available.

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$args`   | **mixed** |             |

**Return Value:**

Array of ItemMetadata objects

***

### set_cap

set meta cap object

```php
protected set_cap(): mixed
```

***

### set_comment_status

Sets if comments are allowed for the current Item.

```php
public set_comment_status(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description             |
|-----------|-----------|-------------------------|
| `$value`  | **mixed** | string "open"\|"closed" |

***

### validate

Validate the class values/properties, to be used before insert/save/update

```php
public validate(): bool
```

**See Also:**

* \Tainacan\Entities\Entity::validate()

***

### validate_core_metadata

{@inheritDoc}

```php
public validate_core_metadata(): mixed
```

**See Also:**

* \Tainacan\Entities\Entity::validate()

***

### _toHtml

```php
public _toHtml(): mixed
```

***

### get_metadata_as_html

Return the item metadata as a HTML string to be used as output.

```php
public get_metadata_as_html(array|string $args = array()): string
```

Each metadata is a label with the metadatum name and the value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in the 'metadata' argument, it returns only one metadata, otherwise
it returns all metadata

**Parameters:**

| Parameter | Type              | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
|-----------|-------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$args`   | **array\|string** | {
    Optional. Array or string of arguments.

	   @type mixed		 $metadata					Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata

    @type array		 $metadata__in				Array of metadata IDs or Slugs to be retrieved. Default none

    @type array		 $metadata__not_in			Array of metadata IDs (slugs not accepted) to excluded. Default none

    @type bool		 $exclude_title				Exclude the Core Title Metadata from result. Default false

    @type bool		 $exclude_description		Exclude the Core Description Metadata from result. Default false

    @type bool		 $exclude_core				Exclude Core Metadata (title and description) from result. Default false

    @type bool        $hide_empty                Whether to hide or not metadata the item has no value to
                                                 Default: true
    @type string      $empty_value_message       Message string to display if $hide_empty is false and there is not metadata value.
                                                 Default: ''
    @type bool        $display_slug_as_class     Show metadata slug as a class in the div before the metadata block
                                                 Default: false
    @type string      $before                    String to be added before each metadata block
                                                 Default '<div class="metadata-type-$type">' where $type is the metadata type slug
    @type string      $after		                String to be added after each metadata block
                                                 Default '</div>'
    @type string      $before_title              String to be added before each metadata title
                                                 Default '<h3>'
    @type string      $after_title               String to be added after each metadata title
                                                 Default '</h3>'
    @type string      $before_value              String to be added before each metadata value
                                                 Default '<p>'
    @type string      $after_value               String to be added after each metadata value
                                                 Default '</p>'
} |

**Return Value:**

The HTML output

**Throws:**

- [`Exception`](../../Exception)

***

### get_item_metadatum_as_html

Return a single item metadata as a HTML string to be used as output.

```php
public get_item_metadatum_as_html(object $item_metadatum, array|string $args = array(), mixed $metadatum_index = null): string
```

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function

**Parameters:**

| Parameter          | Type              | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
|--------------------|-------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$item_metadatum`  | **object**        | The Item Metadatum object                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| `$args`            | **array\|string** | {
    Optional. Array or string of arguments.

    @type bool        $hide_empty                Whether to hide or not metadata the item has no value to
                                                 Default: true
    @type string      $empty_value_message       Message string to display if $hide_empty is false and there is not metadata value.
                                                 Default: ''
    @type bool        $display_slug_as_class     Show metadata slug as a class in the div before the metadata block
                                                 Default: false
    @type string      $before                    String to be added before each metadata block
                                                 Default '<div class="metadata-type-$type">' where $type is the metadata type slug
    @type string      $after		                String to be added after each metadata block
                                                 Default '</div>'
    @type string      $before_title              String to be added before each metadata title
                                                 Default '<h3>'
    @type string      $after_title               String to be added after each metadata title
                                                 Default '</h3>'
    @type string      $before_value              String to be added before each metadata value
                                                 Default '<p>'
    @type string      $after_value               String to be added after each metadata value
                                                 Default '</p>'
} |
| `$metadatum_index` | **mixed**         |                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |

**Return Value:**

The HTML output

***

### get_document_as_html

Gets the document as a html. May be a text, link, iframe, image, audio.

```php
public get_document_as_html(mixed $img_size = 'large'): mixed
```

..

**Parameters:**

| Parameter   | Type      | Description |
|-------------|-----------|-------------|
| `$img_size` | **mixed** |             |

***

### get_attachment_as_html

Gets the attachment as a html. May be an iframe, image, audio.

```php
public get_attachment_as_html(mixed $attachment, mixed $img_size = 'large'): mixed
```

..

**Parameters:**

| Parameter     | Type      | Description |
|---------------|-----------|-------------|
| `$attachment` | **mixed** |             |
| `$img_size`   | **mixed** |             |

***

### get_edit_url

Gets the url to the edit page for this item

```php
public get_edit_url(): mixed
```

***

### get_document_download_url

Gets the Document url of this item

```php
public get_document_download_url(): mixed
```

***

### get_related_items

Return related items withs the item

```php
public get_related_items(mixed $args = []): array
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$args`   | **mixed** |             |

***

### get_metadata_sections_as_html

Return the item metadata sections as a HTML string to be used as output.

```php
public get_metadata_sections_as_html(array|string $args = array()): string
```

Each metadata section is a label with the list of its metadata name and value.

If an ID, a slug or a Tainacan\Entities\Metadata_Section object is passed in the 'metadata_section' argument, it returns only one metadata section, otherwise
it returns all metadata section

**Parameters:**

| Parameter | Type              | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
|-----------|-------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$args`   | **array\|string** | {
    Optional. Array or string of arguments.

	   @type mixed		 $metadata_section				Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata_sections

    @type array		 $metadata_sections__in			Array of metadata_sections IDs or Slugs to be retrieved. Default none

    @type array		 $metadata_sections__not_in		Array of metadata_sections IDs (slugs not accepted) to excluded. Default none

    @type bool		 $hide_name						Do not display the Metadata Section name. Default false

    @type bool		 $hide_description				Do not display the Metadata Section description. Default true

    @type bool        $hide_empty                	Whether to hide or not metadata sections if there are no metadata list or they are empty
                                                 	Default: true
    @type string      $empty_metadata_list_message 	Message string to display if $hide_empty is false and there is not metadata section metadata list.
                                                 	Default: ''
    @type string      $before                    	String to be added before each metadata section block
                                                 	Default '<section class="metadata-section-slug-$slug" id="$id">'
    @type string      $after		                	String to be added after each metadata section block
                                                 	Default '</section>'
    @type string      $before_name              		String to be added before each metadata section name
                                                 	Default '<h2 id="metadata-section-$slug">'
    @type string      $after_name               		String to be added after each metadata section name
                                                 	Default '</h2>'
	   @type string      $before_description            String to be added before each metadata section description
                                                 	Default '<p>'
    @type string      $after_description             String to be added after each metadata section description
                                                 	Default '</p>'
    @type string      $before_metadata_list      	String to be added before each metadata section inner metadata list
                                                 	Default '<div class="metadata-section__metadata-list" aria-labelledby="metadata-section-$slug">'
    @type string      $after_metadata_list       	String to be added after each metadata section inner metadata list
                                                 	Default '</div>'
   @type array		$metadata_list_args				Arguments to be passed to the get_metadata_as_html function when calling section metadata
} |

**Return Value:**

The HTML output

***

### get_metadata_section_as_html

Return a single item metadata section as a HTML string to be used as output.

```php
public get_metadata_section_as_html(\Tainacan\Entities\Metadata_Section $metadata_section, array|string $args = array(), int $section_index = null): string
```

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function

**Parameters:**

| Parameter           | Type                                    | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
|---------------------|-----------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$metadata_section` | **\Tainacan\Entities\Metadata_Section** | The Metadata Section object                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| `$args`             | **array\|string**                       | {
    Optional. Array or string of arguments.

    @type bool		 $hide_name						Do not display the Metadata Section name. Default false

    @type bool		 $hide_description				Do not display the Metadata Section description. Default true

    @type bool        $hide_empty                	Whether to hide or not metadata sections if there are no metadata list or they are empty
                                                 	Default: true
    @type string      $empty_metadata_list_message 	Message string to display if $hide_empty is false and there is not metadata section metadata list.
                                                 	Default: ''
    @type string      $before                    	String to be added before each metadata section block
                                                 	Default '<section class="metadata-section-slug-$slug" id="$id">'
    @type string      $after		                	String to be added after each metadata section block
                                                 	Default '</section>'
    @type string      $before_name              		String to be added before each metadata section name
                                                 	Default '<h2 id="metadata-section-$slug">'
    @type string      $after_name               		String to be added after each metadata section name
                                                 	Default '</h2>'
	   @type string      $before_description            String to be added before each metadata section description
                                                 	Default '<p>'
    @type string      $after_description             String to be added after each metadata section description
                                                 	Default '</p>'
    @type string      $before_metadata_list      	String to be added before each metadata section inner metadata list
                                                 	Default '<div class="metadata-section__metadata-list" aria-labelledby="metadata-section-$slug">'
    @type string      $after_metadata_list       	String to be added after each metadata section inner metadata list
                                                 	Default '</div>'

   @type array		$metadata_list_args				Arguments to be passed to the get_metadata_as_html function when calling section metadata
} |
| `$section_index`    | **int**                                 | The Metadata Section index, if passed from an array                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |

**Return Value:**

The HTML output

***

## Inherited methods

### __construct

Create an instance of Entity

```php
public __construct(mixed $which): mixed
```

If ID or WP Post is passed, it retrieves the object from the database

Attention: If the ID or Post provided do not match the Entity post type, an Exception will be thrown

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$which`  | **mixed** |             |

**Throws:**

- [`Exception`](../../Exception)

***

### get_repository

```php
public get_repository(): mixed
```

***

### get_date_i18n

```php
public get_date_i18n(mixed $date): string
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$date`   | **mixed** |             |

***

### get_mapped_property

return the value for a mapped property

```php
public get_mapped_property(string $prop): mixed
```

**Parameters:**

| Parameter | Type       | Description    |
|-----------|------------|----------------|
| `$prop`   | **string** | id of property |

**Return Value:**

property value

***

### set_mapped_property

set the value of a mapped property

```php
protected set_mapped_property(string $prop, mixed $value): mixed
```

This is a protected method. If you want to set an entity prop
using the prop name dynamically, use the set() method

**Parameters:**

| Parameter | Type       | Description            |
|-----------|------------|------------------------|
| `$prop`   | **string** | id of the property     |
| `$value`  | **mixed**  | the value to be setted |

***

### set

set the value property

```php
public set(string $prop, mixed $value): null|mixed
```

**Parameters:**

| Parameter | Type       | Description            |
|-----------|------------|------------------------|
| `$prop`   | **string** | id of the property     |
| `$value`  | **mixed**  | the value to be setted |

**Return Value:**

Null on failure, the value that was set on success

***

### get

get the value property

```php
public get(string $prop): null|mixed
```

**Parameters:**

| Parameter | Type       | Description        |
|-----------|------------|--------------------|
| `$prop`   | **string** | id of the property |

**Return Value:**

Null on failure, the value that was set on success

***

### set_status

set the status of the entity

```php
public set_status(string $value): mixed
```

**Parameters:**

| Parameter | Type       | Description |
|-----------|------------|-------------|
| `$value`  | **string** |             |

***

### validate

Validate the class values/properties, to be used before insert/save/update

```php
public validate(): bool
```

If Entity is not valid, validation error messages are available via get_errors() method

***

### validate_prop

Validate a single property

```php
public validate_prop(string $prop): bool
```

**Parameters:**

| Parameter | Type       | Description                       |
|-----------|------------|-----------------------------------|
| `$prop`   | **string** | id of the property to be validate |

***

### get_errors

```php
public get_errors(): mixed
```

***

### get_post_type

```php
public static get_post_type(): mixed
```

* This method is **static**.
***

### get_capability_type

```php
public static get_capability_type(): mixed
```

* This method is **static**.
***

### get_status

```php
public get_status(): mixed
```

***

### get_db_identifier

Get entity DB identifier

```php
public get_db_identifier(): string
```

This identifier is used to register the entity on database, ex.: post_type

***

### get_id

Get the entity ID

```php
public get_id(): int
```

***

### add_error

```php
public add_error(mixed $type, mixed $message): mixed
```

**Parameters:**

| Parameter  | Type      | Description |
|------------|-----------|-------------|
| `$type`    | **mixed** |             |
| `$message` | **mixed** |             |

***

### reset_errors

Clear the errors array

```php
public reset_errors(): mixed
```

***

### get_validated

```php
public get_validated(): mixed
```

***

### set_validated

```php
protected set_validated(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_as_valid

```php
protected set_as_valid(): mixed
```

***

### _toArray

```php
public _toArray(): mixed
```

***

### _toJson

```php
public _toJson(): mixed
```

***

### can_read

Return if user can read this entity

```php
public can_read(int|\WP_User $user = null): bool
```

**Parameters:**

| Parameter | Type              | Description |
|-----------|-------------------|-------------|
| `$user`   | **int\|\WP_User** |             |

***

### can_edit

Return if user can edit this entity

```php
public can_edit(int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                    | Description                                              |
|-----------|-------------------------|----------------------------------------------------------|
| `$user`   | **int\|\WP_User\|null** | the user for capability check, null for the current user |

***

### can_delete

Return if user can delete this entity

```php
public can_delete(int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                    | Description                                              |
|-----------|-------------------------|----------------------------------------------------------|
| `$user`   | **int\|\WP_User\|null** | the user for capability check, null for the current user |

***

### can_publish

Return if user can publish this entity

```php
public can_publish(int|\WP_User|null $user = null): bool
```

**Parameters:**

| Parameter | Type                    | Description                                              |
|-----------|-------------------------|----------------------------------------------------------|
| `$user`   | **int\|\WP_User\|null** | the user for capability check, null for the current user |

***

### get_capabilities

Get the capabilities list for the post type of the entity

```php
public get_capabilities(): object
```

**Return Value:**

Object with all the capabilities as member variables.

***

### diff

Compare this entity props with self old values or with $which other entity

```php
public diff(\Tainacan\Entities\Entity|int|\WP_Post $which): array
```

**Parameters:**

| Parameter | Type                                         | Description                                             |
|-----------|----------------------------------------------|---------------------------------------------------------|
| `$which`  | **\Tainacan\Entities\Entity\|int\|\WP_Post** | default ($which = 0) to self compare with stored entity |

***

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
