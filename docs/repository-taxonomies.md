# Taxonomies

A taxonomy is a post of type `tainacan-taxonomy`. It is a vocabulary shared by one or more collections. Its entries are [terms](repository-terms.md). The shared routine is in the [internal API](internal-api.md). The repository is `tainacan_taxonomies()`.

## Create

`set_name()` is the vocabulary name. `insert()` registers the WordPress taxonomy `tnc_tax_{id}`.

```php
$taxonomy = new \Tainacan\Entities\Taxonomy();
$taxonomy->set_name( 'Genre' );
$taxonomy->set_status( 'publish' );

if ( $taxonomy->validate() ) {
	$taxonomy = tainacan_taxonomies()->insert( $taxonomy );
}
```

A collection uses the vocabulary through a metadatum of type `Tainacan\Metadata_Types\Taxonomy` whose `taxonomy_id` option is this taxonomy's ID. Saving that metadatum records the collection on the taxonomy. See [Metadata](repository-metadata.md#create).

`hierarchical` and `allow_insert` default to `yes`. Pass `no` to `set_hierarchical()` to forbid parent terms, or to `set_allow_insert()` to forbid creating terms while editing an item.

## Read

`fetch_by_collection( $collection )` returns the taxonomies used by taxonomy metadata in that collection, including inherited metadata.

```php
$taxonomies = tainacan_taxonomies()->fetch_by_collection( $collection );
```

`fetch_by_db_identifier()` loads a taxonomy from its WordPress slug, such as `tnc_tax_12`. `get_db_identifier()` on the entity returns that slug.

## Class reference

[Repository](phpdoc/classes/Tainacan/Repositories/Taxonomies.md) · [Entity](phpdoc/classes/Tainacan/Entities/Taxonomy.md) · [Internal API](internal-api.md)
