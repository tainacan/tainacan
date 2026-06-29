
# Items Repository

## Main Methods

These are the most used methods of this repository. For a complete list see [the repository file](https://github.com/tainacan/tainacan/tree/master/src/classes/repositories/class-tainacan-items.php).


### fetch()


fetch items based on ID or WP_Query args

Items are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Item object.  But if the post is not found or
does not match the entity post type, it will return an empty array

The second parameter specifies from which collections item should be fetched.
You can pass the Collection ID or object, or an Array of IDs or collection objects

@param array $args WP_Query args || int $args the item id
@param array $collections Array Entities\Collection || Array int collections IDs || int collection id || Entities\Collection collection object
@param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

@return \WP_Query|Array|Item an instance of wp query OR array of entities OR an Item;
 

### fetch_one()


Fetch one Entity based on query args.

Note: Does not work with Item_Metadata Repository

@param array $args Query Args as expected by fetch

@return false|\Tainacan\Entities The entity or false if none was found
 

### fetch_ids()


fetch items IDs based on WP_Query args

to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

The second parameter specifies from which collection items should be fetched.
You can pass the Collection ID or object, or an Array of IDs or collection objects

@param array $args WP_Query args || int $args the item id
@param array $collections Array Entities\Collection || Array int collections IDs || int collection id || Entities\Collection collection object

@return Array array of IDs;
 

### get_thumbnail_id_from_document()


Get a default thumbnail ID from the item document.

@param  Entities\Item $item The item

@return int|null           The thumbnail ID or null if it was not possible to find a thumbnail
 

### insert()



### update()



### delete()


@param $item_id

@return mixed|Item
 

### trash()


@param $item_id

@return mixed|Item
 

## Usage 

```php
$repository = \Tainacan\Repositories\Items::get_instance();
```

## Entity Properties 

These are the Entity attributes for this repository. The Entity class is at [classes/entities folder](../src/classes/entities/class-tainacan-item.php)

Property | Description | Slug | Getter | Setter | Stored as
--- | --- | --- | --- | --- | --- 
Status|The posts status|status|`$entity->get_status()`|`$entity->set_status()`|post_status
ID|Unique identifier|id|`$entity->get_id()`|`$entity->set_id()`|ID
Title|Title of the item|title|`$entity->get_title()`|`$entity->set_title()`|post_title
Description|The item description|description|`$entity->get_description()`|`$entity->set_description()`|post_content
Collection|The collection ID|collection_id|`$entity->get_collection_id()`|`$entity->set_collection_id()`|meta
Author|The item author's user ID (numeric string)|author_id|`$entity->get_author_id()`|`$entity->set_author_id()`|post_author
Creation Date|The item creation date|creation_date|`$entity->get_creation_date()`|`$entity->set_creation_date()`|post_date
Modification Date|The item modification date|modification_date|`$entity->get_modification_date()`|`$entity->set_modification_date()`|post_modified
Term IDs|The item term IDs|terms|`$entity->get_terms()`|`$entity->set_terms()`|terms
Document Type|The document type, can be a local attachment, an external URL or a text|document_type|`$entity->get_document_type()`|`$entity->set_document_type()`|meta
Document|The document itself. An ID in case of attachment, an URL in case of link or a text in the case of text.|document|`$entity->get_document()`|`$entity->set_document()`|meta
Thumbnail|Squared reduced-size version of a picture that helps recognizing and organizing files|_thumbnail_id|`$entity->get__thumbnail_id()`|`$entity->set__thumbnail_id()`|meta
Comment Status|Item comment status: "open" means comments are allowed, "closed" means comments are not allowed.|comment_status|`$entity->get_comment_status()`|`$entity->set_comment_status()`|comment_status

### Entity usage


Create new

```php
$entity = new \Tainacan\Entities\Item();
```

Get existing by ID
```php
$repository = \Tainacan\Repositories\Items::get_instance();
$entity = $repository->fetch(12);
echo 'My ID is ' . $entity->get_id(); // 12
```

