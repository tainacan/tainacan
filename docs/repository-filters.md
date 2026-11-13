# Filters

A filter is a post of type `tainacan-filter`. It exposes one metadatum in the faceted search of a collection. The shared routine is in the [internal API](internal-api.md). The repository is `tainacan_filters()`.

## Create

`set_collection()` and `set_metadatum()` bind the filter. `set_filter_type()` takes the filter type class name. The type must support the primitive type of that metadatum, or `validate()` fails. A text metadatum accepts `Tainacan\Filter_Types\Selectbox`.

```php
$filter = new \Tainacan\Entities\Filter();
$filter->set_name( 'Technique' );
$filter->set_status( 'publish' );
$filter->set_collection( $collection );
$filter->set_metadatum( $metadatum );
$filter->set_filter_type( 'Tainacan\Filter_Types\Selectbox' );

if ( $filter->validate() ) {
	$filter = tainacan_filters()->insert( $filter );
}
```

`fetch_filter_types()` lists the registered filter type classes. `register_filter_type()` adds one from a plugin.

## Read

`fetch_by_collection( $collection )` returns the filters of that collection, in collection order, including filters inherited from a parent collection and from the repository.

```php
$filters = tainacan_filters()->fetch_by_collection( $collection );
```

`fetch()` without the collection follows the [internal API](internal-api.md#reading).

## Class reference

[Repository](phpdoc/classes/Tainacan/Repositories/Filters.md) · [Entity](phpdoc/classes/Tainacan/Entities/Filter.md) · [Internal API](internal-api.md)
