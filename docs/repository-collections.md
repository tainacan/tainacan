# Collections Repository

## Main Methods

These are the most used methods of this repository. For a complete list see [the repository file](https://github.com/tainacan/tainacan/master/develop/src/classes/repositories/class-tainacan-collections.php).


### fetch()


fetch collection based on ID or WP_Query args

Collections are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Collection object.  But if the post is not found or
does not match the entity post type, it will return an empty array

@param array $args WP_Query args || int $args the collection id
@param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

@return \WP_Query|Array an instance of wp query OR array of entities;
 

### fetch_one()


Fetch one Entity based on query args.

Note: Does not work with Item_Metadata Repository

@param array $args Query Args as expected by fetch

@return false|\Tainacan\Entities The entity or false if none was found
 

### insert()


@param \Tainacan\Entities\Collection $collection

@return \Tainacan\Entities\Collection
{@inheritDoc}
@see \Tainacan\Repositories\Repository::insert()
 

### update()



### delete()


@param $collection_id

@return mixed|Collection
 

### trash()


@param $collection_id

@return mixed|Collection
 

## Usage 

```php
$repository = \Tainacan\Repositories\Collections::get_instance();
```

## Entity Properties 

These are the Entity attributes for this repository. The Entity class is at [classes/entities folder](../src/classes/entities/class-tainacan-collection.php)

Property | Description | Slug | Getter | Setter | Stored as
--- | --- | --- | --- | --- | --- 
Status|The current situation of the post|status|`$entity->get_status()`|`$entity->set_status()`|post_status
ID|Unique identifier|id|`$entity->get_id()`|`$entity->set_id()`|ID
Name|The title of the collection|name|`$entity->get_name()`|`$entity->set_name()`|post_title
Author ID|The collection author's user ID (numeric string)|author_id|`$entity->get_author_id()`|`$entity->set_author_id()`|post_author
Creation Date|The collection creation date|creation_date|`$entity->get_creation_date()`|`$entity->set_creation_date()`|post_date
Modification Date|The collection modification date|modification_date|`$entity->get_modification_date()`|`$entity->set_modification_date()`|post_modified
Order|Collection order. This metadata is used if collections are manually ordered.|order|`$entity->get_order()`|`$entity->set_order()`|order
Parent Collection|Original collection from which features are inherited|parent|`$entity->get_parent()`|`$entity->set_parent()`|post_parent
Description|An introductory text describing the collection|description|`$entity->get_description()`|`$entity->set_description()`|post_content
Slug|An unique and sanitized string representation of the collection, used to build the collection URL. It must not contain any special characters or spaces.|slug|`$entity->get_slug()`|`$entity->set_slug()`|post_name
Default Order metadata|Default property items in this collections will be ordered by|default_orderby|`$entity->get_default_orderby()`|`$entity->set_default_orderby()`|meta
Default order|Default order for items in this collection. ASC or DESC|default_order|`$entity->get_default_order()`|`$entity->set_default_order()`|meta
Default Displayed Metadata|List of collection properties that will be displayed in the table view|default_displayed_metadata|`$entity->get_default_displayed_metadata()`|`$entity->set_default_displayed_metadata()`|meta
Default view mode|Collection default visualization mode|default_view_mode|`$entity->get_default_view_mode()`|`$entity->set_default_view_mode()`|meta
Enabled view modes|Which visualization modes will be available for the public to choose from|enabled_view_modes|`$entity->get_enabled_view_modes()`|`$entity->set_enabled_view_modes()`|meta
Ordination metadata|Collection metadata ordination|metadata_order|`$entity->get_metadata_order()`|`$entity->set_metadata_order()`|meta
Ordination filters|Collection filters ordination|filters_order|`$entity->get_filters_order()`|`$entity->set_filters_order()`|meta
Enable Cover Page|To use this page as the home page of this collection|enable_cover_page|`$entity->get_enable_cover_page()`|`$entity->set_enable_cover_page()`|meta
Cover Page ID|If enabled, this custom page will be used as cover for this collection, instead of default items list.|cover_page_id|`$entity->get_cover_page_id()`|`$entity->set_cover_page_id()`|meta
Header Image|The image to be used in collection header|header_image_id|`$entity->get_header_image_id()`|`$entity->set_header_image_id()`|meta
Moderators|To assign users as Moderators of this collection|moderators_ids|`$entity->get_moderators_ids()`|`$entity->set_moderators_ids()`|meta_multi
Thumbnail|Squared reduced-size version of a picture that helps recognizing and organizing files|_thumbnail_id|`$entity->get__thumbnail_id()`|`$entity->set__thumbnail_id()`|meta
Comment Status|Collection comment status: "open" means comments are allowed, "closed" means comments are not allowed.|comment_status|`$entity->get_comment_status()`|`$entity->set_comment_status()`|comment_status
Allow Items Comments|Collection items comment status: "open" means comments are allowed, "closed" means comments are not allowed.|allow_comments|`$entity->get_allow_comments()`|`$entity->set_allow_comments()`|meta

### Entity usage


Create new

```php
$entity = new \Tainacan\Entities\Collection();
```

Get existing by ID
```php
$repository = \Tainacan\Repositories\Collections::get_instance();
$entity = $repository->fetch(12);
echo 'My ID is ' . $entity->get_id(); // 12
```

