
# Filters Repository

## Main Methods

These are the most used methods of this repository. For a complete list see [the repository file](../src/classes/repositories/class-tainacan-filters.php).


### fetch()


fetch filter based on ID or WP_Query args

Filters are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Filter object.  But if the post is not found or
does not match the entity post type, it will return an empty array

@param array $args WP_Query args || int $args the filter id
@param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

@return \WP_Query|Array an instance of wp query OR array of entities;
 

### fetch_one()


Fetch one Entity based on query args.

Note: Does not work with Item_Metadata Repository

@param array $args Query Args as expected by fetch

@return false|\Tainacan\Entities The entity or false if none was found
 

### fetch_ids()


fetch filters IDs based on WP_Query args

to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

@param array $args WP_Query args || int $args the item id

@return Array array of IDs;
@throws \Exception
 

### fetch_by_collection()


fetch filters by collection, searches all filters available

@param Entities\Collection $collection
@param array $args WP_Query args plus disabled_metadata
@param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

@return array Entities\Metadatum
@throws \Exception
 

### fetch_ids_by_collection()


fetch filters IDs by collection, considering inheritance

@param Entities\Collection|int $collection object or ID
@param array $args WP_Query args plus disabled_metadata

@return array List of metadata IDs
@throws \Exception
 

### insert()



@param \Tainacan\Entities\Entity $obj

@return \Tainacan\Entities\Entity | bool
@throws \Exception
 

### update()



### delete()


@param $filter_id

@return Entities\Filter
 

### trash()


@param $filter_id

@return mixed|Entities\Filter
 

## Usage 

```PHP
$repository = \Tainacan\Repositories\Filters::get_instance();
```

## Entity Properties 

These are the Entity attributes for this repository. The Entity class is at [classes/entities folder](../src/classes/entities/class-tainacan-filter.php)

Property | Description | Slug | Getter | Setter | Stored as
--- | --- | --- | --- | --- | --- 
Status|Status|status|`$entity->get_status()`|`$entity->set_status()`|post_status
ID|Unique identifier|id|`$entity->get_id()`|`$entity->set_id()`|ID
Name|Name of the filter|name|`$entity->get_name()`|`$entity->set_name()`|post_title
Order|Filter order. This metadata is used if filters were manually ordered.|order|`$entity->get_order()`|`$entity->set_order()`|menu_order
Description|The filter description|description|`$entity->get_description()`|`$entity->set_description()`|post_content
Filter type options|The filter type options|filter_type_options|`$entity->get_filter_type_options()`|`$entity->set_filter_type_options()`|meta
Type|The filter type|filter_type|`$entity->get_filter_type()`|`$entity->set_filter_type()`|meta
Collection|The collection ID|collection_id|`$entity->get_collection_id()`|`$entity->set_collection_id()`|meta
Color|Filter color|color|`$entity->get_color()`|`$entity->set_color()`|meta
Metadata|Filter metadata|metadatum|`$entity->get_metadatum()`|`$entity->set_metadatum()`|meta
Max of options|The max number of options to be showed in filter sidebar.|max_options|`$entity->get_max_options()`|`$entity->set_max_options()`|meta

### Entity usage


Create new

```PHP
$entity = new \Tainacan\Entities\Filter();
```

Get existing by ID
```PHP
$repository = \Tainacan\Repositories\Filters::get_instance();
$entity = $repository->fetch(12);
echo 'My ID is ' . $entity->get_id(); // 12
```

