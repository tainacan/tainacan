# Collections

A collection is a post of type `tainacan-collection`. It owns the items, metadata, filters, and metadata sections of one group. The shared routine (validate, insert, fetch) is in the [internal API](internal-api.md). The repository is `tainacan_collections()`.

## Create

`set_name()` and `set_status()` are the properties required for a public collection. `insert()` registers the item post type `tnc_col_{id}_item` and the core title and description metadata for that collection.

```php
$collection = new \Tainacan\Entities\Collection();
$collection->set_name( 'Paintings' );
$collection->set_status( 'publish' );

if ( $collection->validate() ) {
	$collection = tainacan_collections()->insert( $collection );
}
```

`set_parent()` points at another collection. The child collection inherits that collection's metadata and filters and can add its own. Pass the parent ID.

## Read

`fetch( $id )` returns one collection. `fetch( $args, 'OBJECT' )` returns a list. Arguments follow the [internal API](internal-api.md#reading).

```php
$collections = tainacan_collections()->fetch(
	array(
		'name' => 'Paintings',
	),
	'OBJECT'
);
```

`fetch_by_db_identifier()` loads the collection from an item post type such as `tnc_col_23_item`. `get_id_by_db_identifier()` returns only the ID. Both are documented on the repository class.

## Class reference

Getters, setters, and the storage map are on the class pages.

[Repository](phpdoc/classes/Tainacan/Repositories/Collections.md) · [Entity](phpdoc/classes/Tainacan/Entities/Collection.md) · [Internal API](internal-api.md)
