
# Metadata Repository

## Main Methods

These are the most used methods of this repository. For a complete list see [the repository file](https://github.com/tainacan/tainacan/tree/master/src/classes/repositories/class-tainacan-metadata.php).


### fetch()


fetch metadatum based on ID or WP_Query args

metadatum are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Metadatum object.  But if the post is not found or
does not match the entity post type, it will return an empty array

@param array $args WP_Query args || int $args the metadatum id
@param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

@return Entities\Metadatum|\WP_Query|Array an instance of wp query OR array of entities;
@throws \Exception
 

### fetch_one()


Fetch one Entity based on query args.

Note: Does not work with Item_Metadata Repository

@param array $args Query Args as expected by fetch

@return false|\Tainacan\Entities The entity or false if none was found
 

### fetch_ids()


fetch metadata IDs based on WP_Query args

to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

@param array $args WP_Query args || int $args the item id

@return Array array of IDs;
@throws \Exception
 

### fetch_by_collection()


fetch metadatum by collection, considering inheritance and order

@param Entities\Collection $collection
@param array $args WP_Query args plus disabled_metadata
@param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)

@return array Entities\Metadatum
@throws \Exception
 

### fetch_ids_by_collection()


fetch metadata IDs by collection, considering inheritance

@param Entities\Collection|int $collection object or ID
@param array $args WP_Query args plus disabled_metadata

@return array List of metadata IDs
@throws \Exception
 

### fetch_metadata_types()


fetch all registered metadatum type classes

Possible outputs are:
CLASS (default) - returns the Class name of metadatum types registered
NAME - return an Array of the names of metadatum types registered

@param $output string CLASS | NAME

@return array of Entities\Metadata_Types\Metadata_Type classes pathname
 

### get_core_metadata()


returns all core metadata from a specific collection

@param Entities\Collection $collection

@return Array|\WP_Query
@throws \Exception
 

### get_core_title_metadatum()


Get the Core Title Metadatum for a collection

@param Entities\Collection $collection

@return \Tainacan\Entities\Metadatum The Core Title Metadatum
@throws \Exception
 

### get_core_description_metadatum()


Get the Core Description Metadatum for a collection

@param Entities\Collection $collection

@return \Tainacan\Entities\Metadatum The Core Description Metadatum
@throws \Exception
 

### fetch_all_metadatum_values()


 Return all possible values for a metadatum 
 
 Each metadata is a label with the metadatum name and the value.
 
 If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in the 'metadata' argument, it returns only one metadata, otherwise
 it returns all metadata
 
 @param int $metadatum_id The ID of the metadata to fetch values from
 @param array|string $args {
     Optional. Array or string of arguments.
 
 @type mixed $collection_idThe collection ID you want to consider or null for all collections. If a collection is set
 then only values applied to items in this collection will be returned
 
     @type int $numberThe number of values to return (for pagination). Default empty (unlimited)
 
     @type int $offsetThe offset (for pagination). Default 0
 
     @type array|bool $items_filterArray in the same format used in @see \Tainacan\Repositories\Items::fetch(). It will filter the results to only return values used in the items inside this criteria. If false, it will return all values, even unused ones. Defatul [] (all items)
 
     @type array $includeArray if ids to be included in the result. Default [] (nothing)
 
     @type array $searchString to search. It will only return values that has this string. Default '' (nothing)
 
     @type array $parent_idUsed by taxonomy metadata. The ID of the parent term to retrieve terms from. Default 0 
 
*     @type bool $count_itemsInclude the count of items that can be found in each value (uses $items_filter as well). Default false
*
*     @type string   $last_termThe last term returned when using a elasticsearch for calculates the facet.
 
 }
 
 @return array        Array with the total number of values found. The total number of pages with the current number and the results with id and label for each value. Terms also include parent, taxonomy, and number of children.
  

### insert()


@param \Tainacan\Entities\Metadatum $metadatum

@return \Tainacan\Entities\Metadatum
{@inheritDoc}
@see \Tainacan\Repositories\Repository::insert()
 

### update()


@param $object
@param $new_values

@return mixed|string|Entities\Entity
@throws \Exception
 

### delete()


@param $metadatum_id

@return mixed|void
@throws \Exception
 

### trash()


@param $metadatum_id

@return mixed|Entities\Metadatum
@throws \Exception
 

## Usage 

```php
$repository = \Tainacan\Repositories\Metadata::get_instance();
```

## Entity Properties 

These are the Entity attributes for this repository. The Entity class is at [classes/entities folder](../src/classes/entities/class-tainacan-metadatum.php)

Property | Description | Slug | Getter | Setter | Stored as
--- | --- | --- | --- | --- | --- 
Status|Status|status|`$entity->get_status()`|`$entity->set_status()`|post_status
ID|Unique identifier|id|`$entity->get_id()`|`$entity->set_id()`|ID
Name|Name of the metadata|name|`$entity->get_name()`|`$entity->set_name()`|post_title
Slug|A unique and santized string representation of the metadata|slug|`$entity->get_slug()`|`$entity->set_slug()`|post_name
Order|Metadata order. This metadata will be used if collections were manually ordered.|order|`$entity->get_order()`|`$entity->set_order()`|menu_order
Parent|Parent metadata|parent|`$entity->get_parent()`|`$entity->set_parent()`|post_parent
Description|The metadata description|description|`$entity->get_description()`|`$entity->set_description()`|post_content
Type|The metadata type|metadata_type|`$entity->get_metadata_type()`|`$entity->set_metadata_type()`|meta
Required|The metadata is required. All items in this collection must fill this field|required|`$entity->get_required()`|`$entity->set_required()`|meta
Unique value|Metadata value should be unique accross all items in this collection|collection_key|`$entity->get_collection_key()`|`$entity->set_collection_key()`|meta
Multiple|Allow items to have more than one value for this metadatum|multiple|`$entity->get_multiple()`|`$entity->set_multiple()`|meta
Cardinality|Number of multiples possible metadata|cardinality|`$entity->get_cardinality()`|`$entity->set_cardinality()`|meta
Mask|The mask to be used in the metadata|mask|`$entity->get_mask()`|`$entity->set_mask()`|meta
Default value|The default value for the metadata|default_value|`$entity->get_default_value()`|`$entity->set_default_value()`|meta
Metadata type options|Specific options for metadata type|metadata_type_options|`$entity->get_metadata_type_options()`|`$entity->set_metadata_type_options()`|meta
Collection|The collection ID|collection_id|`$entity->get_collection_id()`|`$entity->set_collection_id()`|meta
Metadata Value Accepts Suggestions|Allow community to suggest different values for the metadata|accept_suggestion|`$entity->get_accept_suggestion()`|`$entity->set_accept_suggestion()`|meta
Relationship metadata mapping|The metadata mapping options. Metadata can be configured to match another type of data distribution.|exposer_mapping|`$entity->get_exposer_mapping()`|`$entity->set_exposer_mapping()`|meta
Display|Display by default on listing or do not display or never display.|display|`$entity->get_display()`|`$entity->set_display()`|meta
The semantic metadatum description URI|The semantic metadatum description URI like: https://schema.org/URL|semantic_uri|`$entity->get_semantic_uri()`|`$entity->set_semantic_uri()`|meta

### Entity usage


Create new

```php
$entity = new \Tainacan\Entities\Metadatum();
```

Get existing by ID
```php
$repository = \Tainacan\Repositories\Metadata::get_instance();
$entity = $repository->fetch(12);
echo 'My ID is ' . $entity->get_id(); // 12
```

