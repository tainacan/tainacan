# Items

An item is a WordPress post. Its post type is the collection identifier `tnc_col_{id}_item`, created when the collection is saved. The shared routine is in the [internal API](internal-api.md). The repository is `tainacan_items()`.

The title and the description are properties of the item. Other field values are [item metadata](repository-item-metadata.md).

## Create

`set_collection()` is required. `set_title()` sets the item title. Publishing runs the item validation, which includes required metadata and the document when the collection requires one.

```php
$item = new \Tainacan\Entities\Item();
$item->set_title( 'View of Delft' );
$item->set_status( 'publish' );
$item->set_collection( $collection );

if ( $item->validate() ) {
	$item = tainacan_items()->insert( $item );
}
```

The same title and description exist as core metadata. Saving them through `set_title()` and `set_description()` writes the post. Saving them through [item metadata](repository-item-metadata.md) updates the post as well.

## Read

The second argument of `fetch()` is the collection: an ID, a collection entity, or a list of either. Omit it to search every collection.

```php
$items = tainacan_items()->fetch(
	array(
		'title' => 'View of Delft',
	),
	$collection,
	'OBJECT'
);
```

`fetch( $id )` still takes only the item ID. `fetch_ids()` returns IDs for the same arguments. `fetch_related_items()` lists items that point at this one through a relationship metadatum.

## Document

The document is the main file, link, or text of the item. `set_document_type()` accepts `attachment`, `url`, or `text`. `set_document()` is the attachment ID, the URL, or the text.

```php
$item->set_document_type( 'url' );
$item->set_document( 'https://example.org/view-of-delft.jpg' );
```

## Class reference

[Repository](phpdoc/classes/Tainacan/Repositories/Items.md) · [Entity](phpdoc/classes/Tainacan/Entities/Item.md) · [Internal API](internal-api.md)
