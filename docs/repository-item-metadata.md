# Item metadata

An item metadata entity is the value of one metadatum for one item. It is not a post. The value is stored as post meta, as a term relationship, or on the item itself, depending on the metadata type. The repository is `tainacan_item_metadata()`.

The metadatum definition is a separate entity. See [Metadata](repository-metadata.md).

## Save a value

Load the item and the metadatum, then build the value. The constructor and `set_value()` change memory only. `insert()` writes. The metadatum ID is the field definition, not a value row. `fetch()` returns an empty array when the record is missing or not readable. The `return` lines belong in a function. Creating the records, and assigning a taxonomy, are in the [examples](internal-api.md#examples).

```php
// Load the item and the field definition. Nothing is written yet.
$item = tainacan_items()->fetch( $item_id );
$metadatum = tainacan_metadata()->fetch( $metadatum_id );

if ( ! $item instanceof \Tainacan\Entities\Item || ! $metadatum instanceof \Tainacan\Entities\Metadatum ) {
	return;
}

// insert() does not check capabilities.
if ( ! $item->can_edit() ) {
	return;
}

// Bind the two records and set the value in memory.
$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $item, $metadatum );
$item_metadata->set_value( 'Oil' );

// validate() must pass before insert() stores the value.
if ( $item_metadata->validate() ) {
	$item_metadata = tainacan_item_metadata()->insert( $item_metadata );
}
```

Saving the core title or description metadatum also updates the item post. `get_value()` reads the stored value back, including those core properties.

`delete_metadata()` removes the stored value for that item and metadatum.

## Read values

`fetch( $item )` returns one item metadata entity for every metadatum of the item's collection, in collection order. The list includes inherited metadata. Empty values are included, so the caller can tell which fields exist.

```php
$values = tainacan_item_metadata()->fetch( $item );

foreach ( $values as $item_metadata ) {
	$label = $item_metadata->get_metadatum()->get_name();
	$value = $item_metadata->get_value();
}
```

To list definitions without values, use `tainacan_metadata()->fetch_by_collection()`. See [Metadata](repository-metadata.md#read).

## Multiple values and terms

When the metadatum has `set_multiple('yes')`, the value is an array. `insert()` replaces the previous set of values.

```php
$item_metadata->set_value( array( 'Oil', 'Tempera' ) );
```

A taxonomy metadatum stores term IDs. Pass one ID, or an array of IDs when the metadatum accepts multiple values. The terms must belong to the taxonomy configured on that metadatum. Creating the terms and assigning one is in [Setting a taxonomy value](internal-api.md#setting-a-taxonomy-value). See also [Terms](repository-terms.md).

## Class reference

[Repository](phpdoc/classes/Tainacan/Repositories/Item_Metadata.md) · [Entity](phpdoc/classes/Tainacan/Entities/Item_Metadata_Entity.md) · [Internal API](internal-api.md)
