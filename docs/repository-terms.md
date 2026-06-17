
# Terms Repository

## Main Methods

These are the most used methods of this repository. For a complete list see [the repository file](https://github.com/tainacan/tainacan/tree/master/src/classes/repositories/class-tainacan-terms.php).


### fetch()


fetch terms based on ID or get terms args

Terms are stored as WordPress regular terms. Check (@see https://developer.wordpress.org/reference/functions/get_terms/) get_terms() docs
to learn all args accepted in the $args parameter

The second parameter specifies from which taxonomies terms should be fetched.
You can pass the Taxonomy ID or object, or an Array of IDs or taxonomies objects

@param array $args WP_Query args || int $args the term id
@param array $taxonomies Array Entities\Taxonomy || Array int terms IDs || int collection id || Entities\Taxonomy taxonomy object

@return array of Entities\Term objects || Entities\Term
 

### fetch_one()


Fetch one Entity based on query args.

Note: Does not work with Item_Metadata Repository

@param array $args Query Args as expected by fetch

@return false|\Tainacan\Entities The entity or false if none was found
 

### insert()


@param Entities\Entity $term

@return Entities\Entity|Entities\Term
@throws \Exception
 

### update()



### delete()


@param Array $delete_args has ['term_id', 'taxonomy']

@return bool|int|mixed|\WP_Error
 

### trash()


@param $term_id

@return mixed|void
 

## Usage 

```php
$repository = \Tainacan\Repositories\Terms::get_instance();
```

## Entity Properties 

These are the Entity attributes for this repository. The Entity class is at [classes/entities folder](../src/classes/entities/class-tainacan-term.php)

Property | Description | Slug | Getter | Setter | Stored as
--- | --- | --- | --- | --- | --- 
Status|Status|status|`$entity->get_status()`|`$entity->set_status()`|post_status
ID|Unique identifier|term_id|`$entity->get_term_id()`|`$entity->set_term_id()`|term_id
Name|Name of the term|name|`$entity->get_name()`|`$entity->set_name()`|name
Parent|The parent of the term|parent|`$entity->get_parent()`|`$entity->set_parent()`|parent
Description|The term description|description|`$entity->get_description()`|`$entity->set_description()`|description
Taxonomy|The term taxonomy|taxonomy|`$entity->get_taxonomy()`|`$entity->set_taxonomy()`|taxonomy
User|The term creator|user|`$entity->get_user()`|`$entity->set_user()`|termmeta
Header Image|The image to be used in term header|header_image_id|`$entity->get_header_image_id()`|`$entity->set_header_image_id()`|termmeta
Hide empty|Hide empty terms|hide_empty|`$entity->get_hide_empty()`|`$entity->set_hide_empty()`|hide_empty

### Entity usage


Create new

```php
$entity = new \Tainacan\Entities\Term();
```

Get existing by ID
```php
$repository = \Tainacan\Repositories\Terms::get_instance();
$entity = $repository->fetch(12);
echo 'My ID is ' . $entity->get_id(); // 12
```

