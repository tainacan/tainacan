# Collection


Represents a Tainacan Collection entity.

Collections are the main organizational units in Tainacan, containing
items and their associated metadata, filters, and display settings.

***

* Full name: `\Tainacan\Entities\Collection`
* Parent class: [`\Tainacan\Entities\Entity`](./Entity)

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Collection {
        #diplay_name : mixed
        +post_type : string
        #repository : string
        +db_identifier_prefix : string
        +db_identifier_sufix : string
        +__toString()
        +_toArray()
        +register_collection_item_post_type()
        +get_items_capabilities()
        +get_capabilities()
        +get_attachments(exclude)
        +get_author_name()
        +get_thumbnail()
        +get_header_image()
        +set__thumbnail_id(id)
        +get__thumbnail_id()
        +get_modification_date()
        +get_creation_date()
        +get_author_id()
        +get_url()
        +get_name()
        +get_slug()
        +get_order()
        +get_parent()
        +get_description()
        +get_default_order()
        +get_default_orderby()
        +get_default_displayed_metadata()
        +get_default_view_mode()
        +get_enabled_view_modes()
        +get_metadata_order()
        +get_metadata_section_order()
        +get_enable_cover_page()
        +get_header_image_id()
        +is_cover_page_enabled()
        +get_cover_page_id()
        +get_filters_order()
        +get_db_identifier()
        +get_metadata()
        +get_filters()
        +get_core_metadata()
        +get_core_title_metadatum()
        +get_core_description_metadatum()
        +get_comment_status()
        +get_allow_comments()
        +get_allow_item_slug_editing()
        +get_allow_item_author_editing()
        +get_submission_anonymous_user()
        +get_submission_default_status()
        +get_allows_submission()
        +get_hide_items_thumbnail_on_lists()
        +get_submission_use_recaptcha()
        +get_default_metadata_section_properties()
        +get_item_enabled_document_types()
        +get_item_publication_label()
        +get_item_document_label()
        +get_item_thumbnail_label()
        +get_item_enable_thumbnail()
        +get_item_attachment_label()
        +get_item_enable_attachments()
        +get_item_enable_metadata_focus_mode()
        +get_item_enable_metadata_required_filter()
        +get_item_enable_metadata_searchbar()
        +get_item_enable_metadata_collapses()
        +get_item_enable_metadata_enumeration()
        +set_name(value)
        +set_slug(value)
        +set_order(value)
        +set_parent(value)
        +set_description(value)
        +set_default_order(value)
        +set_default_orderby(value)
        +set_default_displayed_metadata(value)
        +set_default_view_mode(value)
        +set_enabled_view_modes(value)
        +set_metadata_order(value)
        +set_metadata_section_order(value)
        +set_filters_order(value)
        +set_enable_cover_page(value)
        +set_cover_page_id(value)
        +set_header_image_id(value)
        +set_comment_status(value)
        +set_allow_comments(value)
        +set_allow_item_author_editing(value)
        +set_allow_item_slug_editing(value)
        +set_submission_anonymous_user(value)
        +set_submission_default_status(value)
        +set_allows_submission(value)
        +set_hide_items_thumbnail_on_lists(value)
        +set_submission_use_recaptcha(value)
        +set_default_metadata_section_properties(value)
        +set_item_enabled_document_types(value)
        +set_item_publication_label(value)
        +set_item_document_label(value)
        +set_item_thumbnail_label(value)
        +set_item_enable_thumbnail(value)
        +set_item_attachment_label(value)
        +set_item_enable_attachments(value)
        +set_item_enable_metadata_focus_mode(value)
        +set_item_enable_metadata_required_filter(value)
        +set_item_enable_metadata_searchbar(value)
        +set_item_enable_metadata_collapses(value)
        +set_item_enable_metadata_enumeration(value)
        +validate()
        +user_can(capability, user)
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
    Collection ..> Collection
    Entity ..> Collection
    Entity ..> Entity
    Entity <|-- Collection
```

## Properties

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

### name

```php
protected $name
```

***

### slug

```php
protected $slug
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

### description

```php
protected $description
```

***

### default_order

```php
protected $default_order
```

***

### default_orderby

```php
protected $default_orderby
```

***

### columns

```php
protected $columns
```

***

### default_view_mode

```php
protected $default_view_mode
```

***

### enabled_view_modes

```php
protected $enabled_view_modes
```

***

### metadata_order

```php
protected $metadata_order
```

***

### metadata_section_order

```php
protected $metadata_section_order
```

***

### filters_order

```php
protected $filters_order
```

***

### enable_cover_page

```php
protected $enable_cover_page
```

***

### cover_page_id

```php
protected $cover_page_id
```

***

### header_image_id

```php
protected $header_image_id
```

***

### header_image

```php
protected $header_image
```

***

### comment_status

```php
protected $comment_status
```

***

### allow_comments

```php
protected $allow_comments
```

***

### allow_item_author_editing

```php
protected $allow_item_author_editing
```

***

### allow_item_slug_editing

```php
protected $allow_item_slug_editing
```

***

### allows_submission

```php
protected $allows_submission
```

***

### hide_items_thumbnail_on_lists

```php
protected $hide_items_thumbnail_on_lists
```

***

### submission_anonymous_user

```php
protected $submission_anonymous_user
```

***

### submission_default_status

```php
protected $submission_default_status
```

***

### submission_use_recaptcha

```php
protected $submission_use_recaptcha
```

***

### item_enabled_document_types

```php
protected $item_enabled_document_types
```

***

### item_publication_label

```php
protected $item_publication_label
```

***

### item_document_label

```php
protected $item_document_label
```

***

### item_thumbnail_label

```php
protected $item_thumbnail_label
```

***

### item_enable_thubmnail

```php
protected $item_enable_thubmnail
```

***

### item_attachment_label

```php
protected $item_attachment_label
```

***

### item_enable_attachments

```php
protected $item_enable_attachments
```

***

### item_enable_metadata_focus_mode

```php
protected $item_enable_metadata_focus_mode
```

***

### item_enable_metadata_required_filter

```php
protected $item_enable_metadata_required_filter
```

***

### item_enable_metadata_searchbar

```php
protected $item_enable_metadata_searchbar
```

***

### item_enable_metadata_collapses

```php
protected $item_enable_metadata_collapses
```

***

### item_enable_metadata_enumeration

```php
protected $item_enable_metadata_enumeration
```

***

### post_type

The WordPress post type for storing this entity.

```php
public static string $post_type
```

* This property is **static**.

**See Also:**

* \Tainacan\Entities\Entity::post_type

***

### repository

The repository instance for this entity.

```php
protected string $repository
```

**See Also:**

* \Tainacan\Entities\Entity::repository

***

### db_identifier_prefix

Prefix used to create the db_identifier

```php
public static string $db_identifier_prefix
```

* This property is **static**.

***

### db_identifier_sufix

sufix used to create the db_identifier

```php
public static string $db_identifier_sufix
```

* This property is **static**.

***

## Methods

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

### register_collection_item_post_type

Register the post type for this collection

```php
public register_collection_item_post_type(): \WP_Post_Type|\WP_Error
```

Each collection is a post type, and each item inside a collection is a post of this post type

This method register the post type for a collection, so that items can be created.

***

### get_items_capabilities

Get the capabilities list for the post type of the items of this collection

```php
public get_items_capabilities(): object
```

This method is usefull for getting the capabilities of the collection's items post type
regardless if it has been already registered or not.

**Return Value:**

Object with all the capabilities as member variables.

***

### get_capabilities

Get the capabilities list for the post type of the entity

```php
public get_capabilities(): object
```

**Return Value:**

Object with all the capabilities as member variables.

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

### get_thumbnail

```php
public get_thumbnail(): array
```

***

### get_header_image

```php
public get_header_image(): false|string
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

### get_name

Get collection name

```php
public get_name(): string
```

***

### get_slug

Get collection slug

```php
public get_slug(): string
```

***

### get_order

Get collection order

```php
public get_order(): int
```

***

### get_parent

Get collection parent ID

```php
public get_parent(): int
```

***

### get_description

Get collection description

```php
public get_description(): string
```

***

### get_default_order

Get collection default order

```php
public get_default_order(): string
```

***

### get_default_orderby

Get collection default orderby

```php
public get_default_orderby(): string
```

***

### get_default_displayed_metadata

Get collection columns option

```php
public get_default_displayed_metadata(): string
```

***

### get_default_view_mode

Get collection default_view_mode option

```php
public get_default_view_mode(): string
```

***

### get_enabled_view_modes

Get collection enabled_view_modes option

```php
public get_enabled_view_modes(): string
```

***

### get_metadata_order

Get collection metadata ordination

```php
public get_metadata_order(): object|string
```

***

### get_metadata_section_order

Get collection metadata section ordination

```php
public get_metadata_section_order(): array|object|string
```

***

### get_enable_cover_page

Get enable cover page attribute

```php
public get_enable_cover_page(): string
```

***

### get_header_image_id

Get Header Image ID attribute

```php
public get_header_image_id(): string
```

***

### is_cover_page_enabled

Return true if enabled cover page is set to yes

```php
public is_cover_page_enabled(): bool
```

***

### get_cover_page_id

Get enable cover page attribute

```php
public get_cover_page_id(): string
```

***

### get_filters_order

Get collection filters ordination

```php
public get_filters_order(): string
```

***

### get_db_identifier

Get collection DB identifier

```php
public get_db_identifier(): string
```

This identifier is used to register the collection post type and never changes, even if you change the name and the slug of the collection.

***

### get_metadata

Get collection metadatum.

```php
public get_metadata(): mixed
```

Returns an array of \Entity\Metadatum objects, representing all the metadatum of the collection.

**Throws:**

- [`Exception`](../../Exception)

***

### get_filters

Get collection filters.

```php
public get_filters(): mixed
```

Returns an array of \Entity\Filter objects, representing all the filters of the collection.

**Throws:**

- [`Exception`](../../Exception)

***

### get_core_metadata

Get the two core metadata of the collection (title and description)

```php
public get_core_metadata(): mixed
```

***

### get_core_title_metadatum

Get the Core Title Metadatum for this collection

```php
public get_core_title_metadatum(): \Tainacan\Entities\Metadatum
```

**Return Value:**

The Core Title Metadatum

***

### get_core_description_metadatum

Get the Core Description Metadatum for this collection

```php
public get_core_description_metadatum(): \Tainacan\Entities\Metadatum
```

**Return Value:**

The Core Description Metadatum

***

### get_comment_status

Checks if comments are allowed for the current Collection.

```php
public get_comment_status(): string
```

**Return Value:**

"open"|"closed"

***

### get_allow_comments

Checks if comments are allowed for the current Collection Items.

```php
public get_allow_comments(): bool
```

***

### get_allow_item_slug_editing

Checks if changing the item slug is allowed for the current Collection Items.

```php
public get_allow_item_slug_editing(): bool
```

***

### get_allow_item_author_editing

Checks if changing the item authorship is allowed for the current Collection Items.

```php
public get_allow_item_author_editing(): bool
```

***

### get_submission_anonymous_user

Get enable submission with anonymous user

```php
public get_submission_anonymous_user(): string
```

**Return Value:**

"yes"|"no"

***

### get_submission_default_status

Get default submission status

```php
public get_submission_default_status(): string
```

***

### get_allows_submission

Checks if submission items are allowed for the current collection.

```php
public get_allows_submission(): string
```

**Return Value:**

"yes"|"no"

***

### get_hide_items_thumbnail_on_lists

Get the state of $hide_items_thumbnail_on_lists to decide if it should always display item thumbnails, even being placeholders

```php
public get_hide_items_thumbnail_on_lists(): string
```

***

### get_submission_use_recaptcha

Checks if submission items use a recaptcha.

```php
public get_submission_use_recaptcha(): string
```

**Return Value:**

"yes"|"no"

***

### get_default_metadata_section_properties

Get the default metadata section properties.

```php
public get_default_metadata_section_properties(): void
```

***

### get_item_enabled_document_types

Get the enabled document types for this collection.

```php
public get_item_enabled_document_types(): array
```

**Return Value:**

The enabled document types.

***

### get_item_publication_label

Get the label for the section in this collection.

```php
public get_item_publication_label(): string
```

**Return Value:**

The label for the publishing section.

***

### get_item_document_label

Get the label for the document section in this collection.

```php
public get_item_document_label(): string
```

**Return Value:**

The label for the section.

***

### get_item_thumbnail_label

Get the label for the thumbnail section in this collection.

```php
public get_item_thumbnail_label(): string
```

**Return Value:**

The label for the thumbnail section.

***

### get_item_enable_thumbnail

Check if thumbnail are enabled for this collection.

```php
public get_item_enable_thumbnail(): string
```

**Return Value:**

'yes' if thumbnail are enabled, 'no' otherwise.

***

### get_item_attachment_label

Get the label for the attachment section in this collection.

```php
public get_item_attachment_label(): string
```

**Return Value:**

The label for the attachment section.

***

### get_item_enable_attachments

Check if attachments are enabled for this collection.

```php
public get_item_enable_attachments(): string
```

**Return Value:**

'yes' if attachments are enabled, 'no' otherwise.

***

### get_item_enable_metadata_focus_mode

Check if metadata focus mode is enabled for this collection.

```php
public get_item_enable_metadata_focus_mode(): string
```

**Return Value:**

'yes' if metadata focus mode is enabled, 'no' otherwise.

***

### get_item_enable_metadata_required_filter

Check if metadata required filter is enabled for this collection.

```php
public get_item_enable_metadata_required_filter(): string
```

**Return Value:**

'yes' if metadata required filter is enabled, 'no' otherwise.

***

### get_item_enable_metadata_searchbar

Check if metadata search bar is enabled for this collection.

```php
public get_item_enable_metadata_searchbar(): string
```

**Return Value:**

'yes' if metadata search bar is enabled, 'no' otherwise.

***

### get_item_enable_metadata_collapses

Check if metadata collapses are enabled for this collection.

```php
public get_item_enable_metadata_collapses(): bool
```

**Return Value:**

'yes' if metadata collapses are enabled, 'no' otherwise.

***

### get_item_enable_metadata_enumeration

Check if metadata and metadata section should be enumerated in the edition form.

```php
public get_item_enable_metadata_enumeration(): bool
```

**Return Value:**

'yes' if metadata are enumerated, 'no' otherwise.

***

### set_name

Set the collection name

```php
public set_name(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_slug

Set the collection slug

```php
public set_slug(mixed $value): void
```

If you dont set the collection slug, it will be set automatically based on the name and
following WordPress default behavior of creating slugs for posts.

If you set the slug for an existing one, WordPress will append a number at the end of in order
to make it unique (e.g slug-1, slug-2)

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_order

Set collection order

```php
public set_order(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_parent

Set collection parent ID

```php
public set_parent(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_description

Set collection description

```php
public set_description(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_default_order

Set collection default order option

```php
public set_default_order(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_default_orderby

Set collection default_orderby option

```php
public set_default_orderby(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_default_displayed_metadata

Set collection columns option

```php
public set_default_displayed_metadata(array $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **array** |             |

***

### set_default_view_mode

Set collection default_view_mode option

```php
public set_default_view_mode(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_enabled_view_modes

Set collection enabled_view_modes option

```php
public set_enabled_view_modes(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_metadata_order

Set collection metadata ordination

```php
public set_metadata_order(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_metadata_section_order

Set collection metadata section ordination

```php
public set_metadata_section_order(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_filters_order

Set collection filters ordination

```php
public set_filters_order(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_enable_cover_page

Set enable cover page attribute

```php
public set_enable_cover_page(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_cover_page_id

Set cover page ID

```php
public set_cover_page_id(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_header_image_id

Set Header Image ID

```php
public set_header_image_id(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_comment_status

Sets if comments are allowed for the current Collection.

```php
public set_comment_status(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description             |
|-----------|-----------|-------------------------|
| `$value`  | **mixed** | string "open"\|"closed" |

***

### set_allow_comments

Sets if comments are allowed for the current Collection Items.

```php
public set_allow_comments(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** | bool        |

***

### set_allow_item_author_editing

Sets if changind the author is allowed for the current Collection Items.

```php
public set_allow_item_author_editing(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** | bool        |

***

### set_allow_item_slug_editing

Sets if changind the item url is allowed for the current Collection Items.

```php
public set_allow_item_slug_editing(mixed $value): mixed
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** | bool        |

***

### set_submission_anonymous_user

Set enable submission with anonymous user

```php
public set_submission_anonymous_user(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_submission_default_status

Set default submission status

```php
public set_submission_default_status(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_allows_submission

Set if submission items are allowes for the current collection.

```php
public set_allows_submission(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_hide_items_thumbnail_on_lists

Set the state of $hide_items_thumbnail_on_lists

```php
public set_hide_items_thumbnail_on_lists(string $value): void
```

**Parameters:**

| Parameter | Type       | Description |
|-----------|------------|-------------|
| `$value`  | **string** |             |

***

### set_submission_use_recaptcha

Set if submission items are use recaptcha.

```php
public set_submission_use_recaptcha(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_default_metadata_section_properties

Set the default metadata section properties.

```php
public set_default_metadata_section_properties(mixed $value): void
```

**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***

### set_item_enabled_document_types

Set the enabled document types for this collection.

```php
public set_item_enabled_document_types(array $value): void
```

**Parameters:**

| Parameter | Type      | Description                 |
|-----------|-----------|-----------------------------|
| `$value`  | **array** | The enabled document types. |

***

### set_item_publication_label

Set the label for the item publication section in this collection.

```php
public set_item_publication_label(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                            |
|-----------|------------|----------------------------------------|
| `$value`  | **string** | The label for the publication section. |

***

### set_item_document_label

Set the label for the document section in this collection.

```php
public set_item_document_label(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                         |
|-----------|------------|-------------------------------------|
| `$value`  | **string** | The label for the document section. |

***

### set_item_thumbnail_label

Set the label for the thumbnail section in this collection.

```php
public set_item_thumbnail_label(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                          |
|-----------|------------|--------------------------------------|
| `$value`  | **string** | The label for the thumbnail section. |

***

### set_item_enable_thumbnail

Enable or disable thumbnail for this collection.

```php
public set_item_enable_thumbnail(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                                 |
|-----------|------------|---------------------------------------------|
| `$value`  | **string** | 'yes' to enable thumbnail, 'no' to disable. |

***

### set_item_attachment_label

Set the label for the attachment section in this collection.

```php
public set_item_attachment_label(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                           |
|-----------|------------|---------------------------------------|
| `$value`  | **string** | The label for the attachment section. |

***

### set_item_enable_attachments

Enable or disable attachments for this collection.

```php
public set_item_enable_attachments(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                                   |
|-----------|------------|-----------------------------------------------|
| `$value`  | **string** | 'yes' to enable attachments, 'no' to disable. |

***

### set_item_enable_metadata_focus_mode

Enable or disable metadata focus mode for this collection.

```php
public set_item_enable_metadata_focus_mode(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                                           |
|-----------|------------|-------------------------------------------------------|
| `$value`  | **string** | 'yes' to enable metadata focus mode, 'no' to disable. |

***

### set_item_enable_metadata_required_filter

Enable or disable metadata required filter for this collection.

```php
public set_item_enable_metadata_required_filter(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                                                |
|-----------|------------|------------------------------------------------------------|
| `$value`  | **string** | 'yes' to enable metadata required filter, 'no' to disable. |

***

### set_item_enable_metadata_searchbar

Enable or disable metadata search bar for this collection.

```php
public set_item_enable_metadata_searchbar(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                                           |
|-----------|------------|-------------------------------------------------------|
| `$value`  | **string** | 'yes' to enable metadata search bar, 'no' to disable. |

***

### set_item_enable_metadata_collapses

Enable or disable metadata collapses for this collection.

```php
public set_item_enable_metadata_collapses(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                                          |
|-----------|------------|------------------------------------------------------|
| `$value`  | **string** | 'yes' to enable metadata collapses, 'no' to disable. |

***

### set_item_enable_metadata_enumeration

Enable or disable metadata and metadata sections enumeration for the item edition form this collection.

```php
public set_item_enable_metadata_enumeration(string $value): void
```

**Parameters:**

| Parameter | Type       | Description                                            |
|-----------|------------|--------------------------------------------------------|
| `$value`  | **string** | 'yes' to enable metadata enumeration, 'no' to disable. |

***

### validate

Validate Collection

```php
public validate(): bool
```

***

### user_can

Checks if an user have permission on any of the collections capabilities
defined in Tainacan\Roles class.

```php
public user_can(string $capability, int|\WP_User|null $user = null): void
```

It applies to all capabilities with 'collection' scope starting with 'tnc_col_'

Usage: use only the suffix of the capability (after tnc_col_%%d_). For example, to check if user can
tnc_col_%%d_read_private_items for this collection, simply call:
$collection->user_can('read_private_items');

**Parameters:**

| Parameter     | Type                    | Description                                              |
|---------------|-------------------------|----------------------------------------------------------|
| `$capability` | **string**              | The capability to be checked.                            |
| `$user`       | **int\|\WP_User\|null** | the user for capability check, null for the current user |

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
