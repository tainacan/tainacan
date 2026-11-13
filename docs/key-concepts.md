# Key concepts

The product vocabulary (what a collection, an item, or a taxonomy is for someone using the repository) is in [General concepts](/general-concepts). This page maps those words onto WordPress storage and the PHP repositories. Creating and reading the records is the [internal API](internal-api.md). The REST API, the admin application, and background processing are in [Architecture](architecture.md).

## Storage

| Concept | Stored as | Repository |
| --- | --- | --- |
| Collection | post type `tainacan-collection` | `tainacan_collections()` |
| Metadatum | post type `tainacan-metadatum` | `tainacan_metadata()` |
| Metadata section | post type `tainacan-metasection` | `tainacan_metadata_sections()` |
| Item | post type `tnc_col_{id}_item` | `tainacan_items()` |
| Item metadata value | post meta, a term relationship, or a core item property | `tainacan_item_metadata()` |
| Filter | post type `tainacan-filter` | `tainacan_filters()` |
| Taxonomy | post type `tainacan-taxonomy` | `tainacan_taxonomies()` |
| Term | a term in the taxonomy `tnc_tax_{id}` | `tainacan_terms()` |

`{id}` is the collection ID or the taxonomy ID. Saving a collection registers `tnc_col_{id}_item`. Saving a taxonomy registers `tnc_tax_{id}`.

## Relationships

An item belongs to one collection. The collection is the item's post type.

A metadatum is the field definition. The value of that field for one item is a separate object, `Item_Metadata_Entity`, written through `tainacan_item_metadata()`. The title and the description are both properties of the item and core metadata.

A metadatum with collection id `default` is repository metadata. `tainacan_metadata()->fetch_by_collection()` includes it in every collection. A child collection inherits its parent's metadata and filters and can add its own.

A taxonomy is shared. A collection uses it through a metadatum of type `Tainacan\Metadata_Types\Taxonomy`. The value of that metadatum is one or more term IDs.

A filter is a faceted-search control bound to one metadatum. It is not the query that loads items. Item queries go through `tainacan_items()->fetch()`.

## Extension points

A metadata type is the PHP class that defines one kind of field, such as `Tainacan\Metadata_Types\Text`. A filter type is the PHP class that defines one search control, such as `Tainacan\Filter_Types\Selectbox`. Each has a web component for the admin interface. Plugins register further types. See [Creating a metadata type](creating-metadata-type.md) and [Creating a filter type](creating-filters-type.md).
