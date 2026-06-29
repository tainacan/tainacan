
# Taxonomies Repository

## Main Methods

These are the most used methods of this repository. For a complete list see [the repository file](https://github.com/tainacan/tainacan/tree/master/src/classes/repositories/class-tainacan-taxonomies.php).


### fetch()


fetch taxonomies based on ID or WP_Query args

Taxonomies are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Taxonomy object.  But if the post is not found or
does not match the entity post type, it will return an empty array

@param array $args WP_Query args | int $args the taxonomy id
@param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

@return \WP_Query|Array an instance of wp query OR array of entities;
 

### fetch_one()


Fetch one Entity based on query args.

Note: Does not work with Item_Metadata Repository

@param array $args Query Args as expected by fetch

@return false|\Tainacan\Entities The entity or false if none was found
 

### fetch_by_collection()


fetch taxonomies by collection, considering inheritance

@param Entities\Collection $collection
@param array $args WP_Query args plus disabled_metadata
@param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

@return array Entities\Metadatum
@throws \Exception
 

### term_exists()


* Check if a term already exists 
*
* @param Entities\Taxonomy $taxonomy The taxonomy object where to look for terms
* @param string $term_name The term name 
* @param int|null $parent The ID of the parent term to look for children or null to look for terms in any hierarchical position. Default is null 
* @param bool $return_term whether to return the term object if it exists. default is to false 
* 
* @return bool|WP_Term return boolean indicating if the term exists. If $return_term is true and term exists, return WP_Term object 


### insert()


@param Entities\Taxonomy $taxonomy

@return Entities\Entity
 

### update()



### delete()



### trash()


@param $taxonomy_id

@return mixed|Entities\Taxonomy
 

## Usage 

```php
$repository = \Tainacan\Repositories\Taxonomies::get_instance();
```

## Entity Properties 

These are the Entity attributes for this repository. The Entity class is at [classes/entities folder](../src/classes/entities/class-tainacan-taxonomy.php)

Property | Description | Slug | Getter | Setter | Stored as
--- | --- | --- | --- | --- | --- 
Status|Status|status|`$entity->get_status()`|`$entity->set_status()`|post_status
ID|Unique identifier|id|`$entity->get_id()`|`$entity->set_id()`|ID
Name|Name of the taxonomy|name|`$entity->get_name()`|`$entity->set_name()`|post_title
Description|The taxonomy description|description|`$entity->get_description()`|`$entity->set_description()`|post_content
Slug|The taxonomy slug|slug|`$entity->get_slug()`|`$entity->set_slug()`|post_name
Allow insert|Allow/Deny the creation of new terms in the taxonomy|allow_insert|`$entity->get_allow_insert()`|`$entity->set_allow_insert()`|meta
Enabled for post types|Also enable this taxonomy for other WordPress post types|enabled_post_types|`$entity->get_enabled_post_types()`|`$entity->set_enabled_post_types()`|meta_multi
Collections|The IDs of collection where the taxonomy is used|collections_ids|`$entity->get_collections_ids()`|`$entity->set_collections_ids()`|meta_multi

### Entity usage


Create new

```php
$entity = new \Tainacan\Entities\Taxonomy();
```

Get existing by ID
```php
$repository = \Tainacan\Repositories\Taxonomies::get_instance();
$entity = $repository->fetch(12);
echo 'My ID is ' . $entity->get_id(); // 12
```

