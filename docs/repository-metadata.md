# Metadata

A metadatum is a post of type `tainacan-metadatum`. It describes one field: its name, its metadata type, and whether a value is required or can repeat. The shared routine is in the [internal API](internal-api.md). The repository is `tainacan_metadata()`.

Values entered for an item are a different entity. See [Item metadata](repository-item-metadata.md).

## Create

`set_collection()` binds the metadatum to a collection. `set_metadata_type()` takes the metadata type class name. `set_status('publish')` makes it available.

```php
$metadatum = new \Tainacan\Entities\Metadatum();
$metadatum->set_name( 'Technique' );
$metadatum->set_status( 'publish' );
$metadatum->set_collection( $collection );
$metadatum->set_metadata_type( 'Tainacan\Metadata_Types\Text' );

if ( $metadatum->validate() ) {
	$metadatum = tainacan_metadata()->insert( $metadatum );
}
```

`set_required('yes')` and `set_multiple('yes')` turn those options on. The accepted values are `yes` and `no`. A taxonomy metadatum uses `Tainacan\Metadata_Types\Taxonomy` and `set_metadata_type_options()` with a `taxonomy_id`. See [Taxonomies](repository-taxonomies.md).

Creating a collection also creates the core title and description metadata. `get_core_title_metadatum()` and `get_core_description_metadatum()` return them for a collection.

## Read

`fetch_by_collection( $collection )` returns the metadata for that collection, in the collection order, including metadata inherited from a parent collection and from the repository. `fetch()` and `fetch_one()` follow the [internal API](internal-api.md#reading) and do not apply that inheritance.

```php
$metadata = tainacan_metadata()->fetch_by_collection( $collection );
```

`fetch_ids()` and `fetch_ids_by_collection()` return IDs. `fetch_by_metadata_section()` limits the list to one section.

## Metadata sections

A metadata section is a post of type `tainacan-metasection`. It groups metadata on the item form. The repository is `tainacan_metadata_sections()`.

Every collection has a default section. It is not stored as a post. Its slug is `default_section`, and `get_default_section( $collection )` builds the object. Metadata with no other section belong to it.

```php
$section = new \Tainacan\Entities\Metadata_Section();
$section->set_name( 'Physical description' );
$section->set_status( 'publish' );
$section->set_collection( $collection );

if ( $section->validate() ) {
	$section = tainacan_metadata_sections()->insert( $section );
	$metadatum->set_metadata_section_id( $section->get_id() );

	if ( $metadatum->validate() ) {
		$metadatum = tainacan_metadata()->insert( $metadatum );
	}
}
```

`fetch_by_collection( $collection )` returns the sections of that collection, including sections inherited from a parent. `add_metadata()` and `get_metadata_object_list()` attach and list metadata on a saved section.

## Repository metadata

A repository metadatum uses the collection id `default`, returned by `tainacan_metadata()->get_default_metadata_attribute()`. Pass that value to `set_collection_id()`. `fetch_by_collection()` includes those metadata in every collection.

## Class reference

[Metadata repository](phpdoc/classes/Tainacan/Repositories/Metadata.md) · [Metadatum](phpdoc/classes/Tainacan/Entities/Metadatum.md)

[Sections repository](phpdoc/classes/Tainacan/Repositories/Metadata_Sections.md) · [Metadata section](phpdoc/classes/Tainacan/Entities/Metadata_Section.md)

[Internal API](internal-api.md)
