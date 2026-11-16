<?php

namespace Tainacan\Abilities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * The declarative table of Tainacan abilities.
 *
 * One row per ability. A row names a REST route instead of transcribing its
 * argument schema: the route is resolved at execution time by
 * rest_do_request(), which means the existing endpoints stay the single source
 * of truth for validation. Hand-copied JSON schemas drift within one release, and
 * a schema that rejects a legitimate Tainacan query argument is worse than a
 * permissive one.
 *
 * Row keys:
 * - label, description (required by the Abilities API)
 * - category (must be registered in Abilities_Categories)
 * - route, method (REST target; method defaults to GET)
 * - route_params (input keys substituted into `{placeholders}`)
 * - body_defaults (constant body values for non-GET; caller input wins)
 * - input_schema, output_schema (output omitted unless precise)
 * - annotations (readonly, destructive, idempotent)
 * - required_capability (floor only; the route's own permission still runs)
 *
 * Rows can be added by plugins through the `tainacan_register_abilities` filter.
 *
 * @since 1.5.0
 */
class Abilities_Registry {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Ability definitions keyed by registration name.
	 *
	 * @return array<string, array>
	 */
	public function get_definitions() {
		$root = '/' . TAINACAN_REST_NAMESPACE;

		$read = [
			'readonly'    => true,
			'destructive' => false,
			'idempotent'  => true,
		];

		$create = [
			'readonly'    => false,
			'destructive' => false,
			'idempotent'  => false,
		];

		$update = [
			'readonly'    => false,
			'destructive' => false,
			'idempotent'  => true,
		];

		$delete = [
			'readonly'    => false,
			'destructive' => true,
			'idempotent'  => true,
		];

		$definitions = [

			/*
			 * ---------------------------------------------------------------
			 * Structure: read-only introspection.
			 * ---------------------------------------------------------------
			 */
			'tainacan/metadata-types-list' => [
				'label'               => __( 'List metadata types', 'tainacan' ),
				'description'         => __( 'Returns every metadata type registered in this Tainacan installation, with its attributes. Use this before creating metadata so the type name is correct.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/metadata-types',
				'method'              => 'GET',
				'input_schema'        => $this->object_schema( [] ),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			/*
			 * ---------------------------------------------------------------
			 * Collections.
			 * ---------------------------------------------------------------
			 */
			'tainacan/collection-list' => [
				'label'               => __( 'List collections', 'tainacan' ),
				'description'         => __( 'Lists Tainacan collections. Use this first to discover collection IDs before touching items.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/collections',
				'method'              => 'GET',
				'input_schema'        => $this->list_schema(),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/collection-get' => [
				'label'               => __( 'Get a collection', 'tainacan' ),
				'description'         => __( 'Returns one collection by ID, including its metadata order and settings.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/collections/{collection_id}',
				'method'              => 'GET',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->object_schema(
					[ 'collection_id' => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ) ],
					[ 'collection_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/collection-create' => [
				'label'               => __( 'Create a collection', 'tainacan' ),
				'description'         => __( 'Creates a Tainacan collection. Only the name is required; metadata definitions are added separately.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/collections',
				'method'              => 'POST',
				'input_schema'        => $this->object_schema(
					[
						'name'        => [
							'type'        => 'string',
							'description' => __( 'Collection name.', 'tainacan' ),
						],
						'description' => [
							'type'        => 'string',
							'description' => __( 'Collection description.', 'tainacan' ),
						],
						'status'      => $this->status_schema( __( 'Collection status.', 'tainacan' ) ),
						'slug'        => [
							'type'        => 'string',
							'description' => __( 'Optional URL slug.', 'tainacan' ),
						],
					],
					[ 'name' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/collection-update' => [
				'label'               => __( 'Update a collection', 'tainacan' ),
				'description'         => __( 'Updates an existing collection. Send only the fields that change.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/collections/{collection_id}',
				'method'              => 'PATCH',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->object_schema(
					[
						'collection_id' => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ),
						'name'          => [
							'type'        => 'string',
							'description' => __( 'Collection name.', 'tainacan' ),
						],
						'description'   => [
							'type'        => 'string',
							'description' => __( 'Collection description.', 'tainacan' ),
						],
						'status'        => $this->status_schema( __( 'Collection status.', 'tainacan' ) ),
					],
					[ 'collection_id' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			'tainacan/collection-delete' => [
				'label'               => __( 'Delete a collection', 'tainacan' ),
				'description'         => __( 'Deletes a collection. Destructive: items and metadata definitions belonging to it are affected.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/collections/{collection_id}',
				'method'              => 'DELETE',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->object_schema(
					[ 'collection_id' => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ) ],
					[ 'collection_id' ]
				),
				'annotations'         => $delete,
				'required_capability' => 'read',
			],

			/*
			 * ---------------------------------------------------------------
			 * Items.
			 * ---------------------------------------------------------------
			 */
			'tainacan/item-list' => [
				'label'               => __( 'List items', 'tainacan' ),
				'description'         => __( 'Lists items across the repository, or filtered to one collection with collection_id. Supports search and pagination.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/items',
				'method'              => 'GET',
				'input_schema'        => $this->list_schema(
					[ 'collection_id' => $this->id_schema( __( 'Restrict to a collection.', 'tainacan' ) ) ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/item-list-in-collection' => [
				'label'               => __( 'List items in a collection', 'tainacan' ),
				'description'         => __( 'Lists items belonging to one collection. Use this instead of item-list when the collection is known.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/collection/{collection_id}/items',
				'method'              => 'GET',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->list_schema(
					[ 'collection_id' => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ) ],
					[ 'collection_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/item-get' => [
				'label'               => __( 'Get an item', 'tainacan' ),
				'description'         => __( 'Returns one item by ID, including its document reference and metadata.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/items/{item_id}',
				'method'              => 'GET',
				'route_params'        => [ 'item_id' ],
				'input_schema'        => $this->object_schema(
					[ 'item_id' => $this->id_schema( __( 'Item ID.', 'tainacan' ) ) ],
					[ 'item_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/item-create' => [
				'label'               => __( 'Create an item', 'tainacan' ),
				'description'         => __( 'Creates an item in a collection. Set status to draft to stage it without publishing. Metadata values are set separately with item-metadata-set.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/collection/{collection_id}/items',
				'method'              => 'POST',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->item_write_schema(
					[ 'collection_id' => $this->id_schema( __( 'Collection that will own the item.', 'tainacan' ) ) ],
					[ 'collection_id', 'title' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/item-update' => [
				'label'               => __( 'Update an item', 'tainacan' ),
				'description'         => __( 'Updates an existing item. Send only the fields that change.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/items/{item_id}',
				'method'              => 'PATCH',
				'route_params'        => [ 'item_id' ],
				'input_schema'        => $this->item_write_schema(
					[ 'item_id' => $this->id_schema( __( 'Item ID.', 'tainacan' ) ) ],
					[ 'item_id' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			'tainacan/item-delete' => [
				'label'               => __( 'Delete an item', 'tainacan' ),
				'description'         => __( 'Moves an item to the trash by default. Set permanently to true to delete it beyond recovery. Trashing already removes it from public listings.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/items/{item_id}',
				'method'              => 'DELETE',
				'route_params'        => [ 'item_id' ],
				'body_defaults'       => [ 'permanently' => false ],
				'input_schema'        => $this->object_schema(
					[
						'item_id'     => $this->id_schema( __( 'Item ID.', 'tainacan' ) ),
						'permanently' => [
							'type'        => 'boolean',
							'description' => __( 'Delete beyond recovery instead of trashing. Defaults to false.', 'tainacan' ),
						],
					],
					[ 'item_id' ]
				),
				'annotations'         => $delete,
				'required_capability' => 'read',
			],

			'tainacan/item-publish' => [
				'label'               => __( 'Publish an item', 'tainacan' ),
				'description'         => __( 'Publishes an item, making it visible according to the collection visibility rules.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/items/{item_id}',
				'method'              => 'PATCH',
				'route_params'        => [ 'item_id' ],
				'body_defaults'       => [ 'status' => 'publish' ],
				'input_schema'        => $this->object_schema(
					[ 'item_id' => $this->id_schema( __( 'Item ID.', 'tainacan' ) ) ],
					[ 'item_id' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			'tainacan/item-unpublish' => [
				'label'               => __( 'Unpublish an item', 'tainacan' ),
				'description'         => __( 'Moves an item back to draft, removing it from public listings without deleting it.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/items/{item_id}',
				'method'              => 'PATCH',
				'route_params'        => [ 'item_id' ],
				'body_defaults'       => [ 'status' => 'draft' ],
				'input_schema'        => $this->object_schema(
					[ 'item_id' => $this->id_schema( __( 'Item ID.', 'tainacan' ) ) ],
					[ 'item_id' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			/*
			 * ---------------------------------------------------------------
			 * Item metadata values. Note the singular "item" in these routes.
			 * ---------------------------------------------------------------
			 */
			'tainacan/item-metadata-list' => [
				'label'               => __( 'List item metadata values', 'tainacan' ),
				'description'         => __( 'Returns the metadata values of an item, including each metadatum definition alongside its value.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/item/{item_id}/metadata',
				'method'              => 'GET',
				'route_params'        => [ 'item_id' ],
				'input_schema'        => $this->object_schema(
					[ 'item_id' => $this->id_schema( __( 'Item ID.', 'tainacan' ) ) ],
					[ 'item_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/item-metadata-set' => [
				'label'               => __( 'Set an item metadata value', 'tainacan' ),
				'description'         => __( 'Sets the value of one metadatum for one item. The payload key is "values" and accepts a scalar or an array, depending on whether the metadatum allows multiple values.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/item/{item_id}/metadata/{metadatum_id}',
				'method'              => 'PATCH',
				'route_params'        => [ 'item_id', 'metadatum_id' ],
				'input_schema'        => $this->object_schema(
					[
						'item_id'        => $this->id_schema( __( 'Item ID.', 'tainacan' ) ),
						'metadatum_id'   => $this->id_schema( __( 'Metadatum ID.', 'tainacan' ) ),
						'values'         => [
							/*
							 * A real type list is required: the Abilities API validates
							 * input against rest_validate_value_from_schema(), which reads
							 * $schema['type'] and errors on a schema that only has a
							 * description.
							 */
							'type'        => [ 'string', 'number', 'integer', 'boolean', 'array' ],
							'description' => __( 'The value or values to store. A scalar for single-valued metadata, an array for multi-valued metadata.', 'tainacan' ),
						],
						'parent_meta_id' => [
							'type'        => 'integer',
							'description' => __( 'Parent metadata ID, for compound metadata types.', 'tainacan' ),
						],
					],
					[ 'item_id', 'metadatum_id', 'values' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			/*
				* ---------------------------------------------------------------
				* Metadata definitions.
				* ---------------------------------------------------------------
				*/
			'tainacan/metadata-list' => [
				'label'               => __( 'List metadata of a collection', 'tainacan' ),
				'description'         => __( 'Lists the metadata definitions of one collection, in display order. This is what tells you which metadatum_id to use when setting values.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/collection/{collection_id}/metadata',
				'method'              => 'GET',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->object_schema(
					[ 'collection_id' => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ) ],
					[ 'collection_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/metadata-get' => [
				'label'               => __( 'Get a metadata definition', 'tainacan' ),
				'description'         => __( 'Returns one metadata definition, including its type and options.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/metadata/{metadatum_id}',
				'method'              => 'GET',
				'route_params'        => [ 'metadatum_id' ],
				'input_schema'        => $this->object_schema(
					[ 'metadatum_id' => $this->id_schema( __( 'Metadatum ID.', 'tainacan' ) ) ],
					[ 'metadatum_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/metadata-create' => [
				'label'               => __( 'Create a metadata definition', 'tainacan' ),
				'description'         => __( 'Adds a metadata definition to a collection. Use metadata-types-list first to pick a valid metadata_type, for example Tainacan\\Metadata_Types\\Text.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/collection/{collection_id}/metadata',
				'method'              => 'POST',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->structure_schema(
					[
						'collection_id' => $this->id_schema( __( 'Collection that will own the metadata.', 'tainacan' ) ),
						'metadata_type' => [
							'type'        => 'string',
							'description' => __( 'Metadata type class, for example Tainacan\\Metadata_Types\\Text.', 'tainacan' ),
						],
						'multiple'      => [
							'type'        => 'boolean',
							'description' => __( 'Whether the metadata accepts multiple values.', 'tainacan' ),
						],
						'required'      => [
							'type'        => 'boolean',
							'description' => __( 'Whether a value is mandatory for items.', 'tainacan' ),
						],
					],
					[ 'collection_id', 'name', 'metadata_type' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/metadata-update' => [
				'label'               => __( 'Update a metadata definition', 'tainacan' ),
				'description'         => __( 'Updates a metadata definition. Send only the fields that change.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/metadata/{metadatum_id}',
				'method'              => 'PATCH',
				'route_params'        => [ 'metadatum_id' ],
				'input_schema'        => $this->structure_schema(
					[ 'metadatum_id' => $this->id_schema( __( 'Metadatum ID.', 'tainacan' ) ) ],
					[ 'metadatum_id' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			'tainacan/metadata-delete' => [
				'label'               => __( 'Delete a metadata definition', 'tainacan' ),
				'description'         => __( 'Deletes a metadata definition. Destructive: item values stored under it are affected.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/metadata/{metadatum_id}',
				'method'              => 'DELETE',
				'route_params'        => [ 'metadatum_id' ],
				'input_schema'        => $this->object_schema(
					[ 'metadatum_id' => $this->id_schema( __( 'Metadatum ID.', 'tainacan' ) ) ],
					[ 'metadatum_id' ]
				),
				'annotations'         => $delete,
				'required_capability' => 'read',
			],

			/*
				* ---------------------------------------------------------------
				* Metadata sections.
				* ---------------------------------------------------------------
				*/
			'tainacan/metadata-section-list' => [
				'label'               => __( 'List metadata sections of a collection', 'tainacan' ),
				'description'         => __( 'Lists the metadata sections of a collection. Sections group metadata in the item form.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/collection/{collection_id}/metadata-sections',
				'method'              => 'GET',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->object_schema(
					[ 'collection_id' => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ) ],
					[ 'collection_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/metadata-section-create' => [
				'label'               => __( 'Create a metadata section', 'tainacan' ),
				'description'         => __( 'Creates a metadata section in a collection.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/collection/{collection_id}/metadata-sections',
				'method'              => 'POST',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->structure_schema(
					[ 'collection_id' => $this->id_schema( __( 'Collection that will own the section.', 'tainacan' ) ) ],
					[ 'collection_id', 'name' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/metadata-section-update' => [
				'label'               => __( 'Update a metadata section', 'tainacan' ),
				'description'         => __( 'Updates a metadata section of a collection.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/collection/{collection_id}/metadata-sections/{metadata_section_id}',
				'method'              => 'PATCH',
				'route_params'        => [ 'collection_id', 'metadata_section_id' ],
				'input_schema'        => $this->structure_schema(
					[
						'collection_id'       => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ),
						'metadata_section_id' => $this->id_schema( __( 'Metadata section ID.', 'tainacan' ) ),
					],
					[ 'collection_id', 'metadata_section_id' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			'tainacan/metadata-section-delete' => [
				'label'               => __( 'Delete a metadata section', 'tainacan' ),
				'description'         => __( 'Deletes a metadata section from a collection.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/collection/{collection_id}/metadata-sections/{metadata_section_id}',
				'method'              => 'DELETE',
				'route_params'        => [ 'collection_id', 'metadata_section_id' ],
				'input_schema'        => $this->object_schema(
					[
						'collection_id'       => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ),
						'metadata_section_id' => $this->id_schema( __( 'Metadata section ID.', 'tainacan' ) ),
					],
					[ 'collection_id', 'metadata_section_id' ]
				),
				'annotations'         => $delete,
				'required_capability' => 'read',
			],

			/*
				* ---------------------------------------------------------------
				* Taxonomies and terms.
				* ---------------------------------------------------------------
				*/
			'tainacan/taxonomy-list' => [
				'label'               => __( 'List taxonomies', 'tainacan' ),
				'description'         => __( 'Lists Tainacan taxonomies. Use this before creating terms.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/taxonomies',
				'method'              => 'GET',
				'input_schema'        => $this->list_schema(),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/taxonomy-get' => [
				'label'               => __( 'Get a taxonomy', 'tainacan' ),
				'description'         => __( 'Returns one taxonomy by ID.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/taxonomies/{taxonomy_id}',
				'method'              => 'GET',
				'route_params'        => [ 'taxonomy_id' ],
				'input_schema'        => $this->object_schema(
					[ 'taxonomy_id' => $this->id_schema( __( 'Taxonomy ID.', 'tainacan' ) ) ],
					[ 'taxonomy_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/taxonomy-create' => [
				'label'               => __( 'Create a taxonomy', 'tainacan' ),
				'description'         => __( 'Creates a taxonomy. Set allow_ui_creation to true if curators may add terms from the item form.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/taxonomies',
				'method'              => 'POST',
				'input_schema'        => $this->structure_schema(
					[
						'allow_ui_creation' => [
							'type'        => 'boolean',
							'description' => __( 'Whether new terms can be created from the interface.', 'tainacan' ),
						],
					],
					[ 'name' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/taxonomy-delete' => [
				'label'               => __( 'Delete a taxonomy', 'tainacan' ),
				'description'         => __( 'Deletes a taxonomy. Destructive: its terms and any metadata bound to it are affected.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/taxonomies/{taxonomy_id}',
				'method'              => 'DELETE',
				'route_params'        => [ 'taxonomy_id' ],
				'input_schema'        => $this->object_schema(
					[ 'taxonomy_id' => $this->id_schema( __( 'Taxonomy ID.', 'tainacan' ) ) ],
					[ 'taxonomy_id' ]
				),
				'annotations'         => $delete,
				'required_capability' => 'read',
			],

			'tainacan/term-list' => [
				'label'               => __( 'List terms of a taxonomy', 'tainacan' ),
				'description'         => __( 'Lists the terms of one taxonomy. Supports search and pagination.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/taxonomy/{taxonomy_id}/terms',
				'method'              => 'GET',
				'route_params'        => [ 'taxonomy_id' ],
				'input_schema'        => $this->list_schema(
					[ 'taxonomy_id' => $this->id_schema( __( 'Taxonomy ID.', 'tainacan' ) ) ],
					[ 'taxonomy_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/term-get' => [
				'label'               => __( 'Get a term', 'tainacan' ),
				'description'         => __( 'Returns one term of a taxonomy.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/taxonomy/{taxonomy_id}/terms/{term_id}',
				'method'              => 'GET',
				'route_params'        => [ 'taxonomy_id', 'term_id' ],
				'input_schema'        => $this->object_schema(
					[
						'taxonomy_id' => $this->id_schema( __( 'Taxonomy ID.', 'tainacan' ) ),
						'term_id'     => $this->id_schema( __( 'Term ID.', 'tainacan' ) ),
					],
					[ 'taxonomy_id', 'term_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/term-create' => [
				'label'               => __( 'Create a term', 'tainacan' ),
				'description'         => __( 'Creates one term in a taxonomy. Use term-bulk-create for many at once.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/taxonomy/{taxonomy_id}/terms',
				'method'              => 'POST',
				'route_params'        => [ 'taxonomy_id' ],
				'input_schema'        => $this->structure_schema(
					[
						'taxonomy_id' => $this->id_schema( __( 'Taxonomy that will own the term.', 'tainacan' ) ),
						'parent'      => [
							'type'        => 'integer',
							'description' => __( 'Parent term ID, for hierarchical taxonomies.', 'tainacan' ),
						],
					],
					[ 'taxonomy_id', 'name' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/term-delete' => [
				'label'               => __( 'Delete a term', 'tainacan' ),
				'description'         => __( 'Deletes a term from a taxonomy. Destructive: items using the term lose that value.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/taxonomy/{taxonomy_id}/terms/{term_id}',
				'method'              => 'DELETE',
				'route_params'        => [ 'taxonomy_id', 'term_id' ],
				'input_schema'        => $this->object_schema(
					[
						'taxonomy_id' => $this->id_schema( __( 'Taxonomy ID.', 'tainacan' ) ),
						'term_id'     => $this->id_schema( __( 'Term ID.', 'tainacan' ) ),
					],
					[ 'taxonomy_id', 'term_id' ]
				),
				'annotations'         => $delete,
				'required_capability' => 'read',
			],

			/*
				* ---------------------------------------------------------------
				* Filters and facets.
				* ---------------------------------------------------------------
				*/
			'tainacan/filter-types-list' => [
				'label'               => __( 'List filter types', 'tainacan' ),
				'description'         => __( 'Returns every filter type registered in this installation. Use this before creating a filter so filter_type is valid.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/filter-types',
				'method'              => 'GET',
				'input_schema'        => $this->object_schema( [] ),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/filter-list' => [
				'label'               => __( 'List filters of a collection', 'tainacan' ),
				'description'         => __( 'Lists the filters of one collection. Filters drive faceted search.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/collection/{collection_id}/filters',
				'method'              => 'GET',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->list_schema(
					[ 'collection_id' => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ) ],
					[ 'collection_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/filter-get' => [
				'label'               => __( 'Get a filter', 'tainacan' ),
				'description'         => __( 'Returns one filter by ID, including its type and options.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/filters/{filter_id}',
				'method'              => 'GET',
				'route_params'        => [ 'filter_id' ],
				'input_schema'        => $this->object_schema(
					[ 'filter_id' => $this->id_schema( __( 'Filter ID.', 'tainacan' ) ) ],
					[ 'filter_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/filter-create' => [
				'label'               => __( 'Create a filter', 'tainacan' ),
				'description'         => __( 'Creates a filter over a metadatum in a collection. Attributes nest under "filter", while metadatum_id and filter_type sit at the top level because that is where the controller reads them. Use filter-types-list to pick a valid type.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/collection/{collection_id}/filters',
				'method'              => 'POST',
				'route_params'        => [ 'collection_id' ],
				'input_schema'        => $this->object_schema(
					[
						'collection_id' => $this->id_schema( __( 'Collection that will own the filter.', 'tainacan' ) ),
						'metadatum_id'  => $this->id_schema( __( 'Metadatum the filter applies to. Top level, not inside "filter".', 'tainacan' ) ),
						'filter'        => [
							'type'        => 'object',
							'description' => __( 'Filter attributes: name, description, status.', 'tainacan' ),
						],
						'filter_type'   => [
							'type'        => 'string',
							'description' => __( 'Filter type class, for example Tainacan\\Filter_Types\\Checkbox.', 'tainacan' ),
						],
					],
					[ 'collection_id', 'metadatum_id', 'filter', 'filter_type' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/filter-delete' => [
				'label'               => __( 'Delete a filter', 'tainacan' ),
				'description'         => __( 'Deletes a filter. Destructive: faceted search loses that dimension.', 'tainacan' ),
				'category'            => 'tainacan-structure',
				'route'               => $root . '/filters/{filter_id}',
				'method'              => 'DELETE',
				'route_params'        => [ 'filter_id' ],
				'input_schema'        => $this->object_schema(
					[ 'filter_id' => $this->id_schema( __( 'Filter ID.', 'tainacan' ) ) ],
					[ 'filter_id' ]
				),
				'annotations'         => $delete,
				'required_capability' => 'read',
			],

			'tainacan/facet-get' => [
				'label'               => __( 'Get facets for a metadatum in a collection', 'tainacan' ),
				'description'         => __( 'Returns the facet values and counts for one metadatum inside a collection, which is what a faceted search UI shows.', 'tainacan' ),
				'category'            => 'tainacan-search',
				'route'               => $root . '/collection/{collection_id}/facets/{metadatum_id}',
				'method'              => 'GET',
				'route_params'        => [ 'collection_id', 'metadatum_id' ],
				'input_schema'        => $this->object_schema(
					[
						'collection_id' => $this->id_schema( __( 'Collection ID.', 'tainacan' ) ),
						'metadatum_id'  => $this->id_schema( __( 'Metadatum ID.', 'tainacan' ) ),
						'search'        => [
							'type'        => 'string',
							'description' => __( 'Restrict facet counts to a search term.', 'tainacan' ),
						],
					],
					[ 'collection_id', 'metadatum_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			/*
				* ---------------------------------------------------------------
				* Media and ingest.
				* ---------------------------------------------------------------
				*/
			'tainacan/attachment-upload' => [
				'label'               => __( 'Upload an attachment', 'tainacan' ),
				'description'         => __( 'Uploads a file and returns its attachment ID and URL. Provide content_base64 or a url. The returned ID can be bound to an item with item-set-document.', 'tainacan' ),
				'category'            => 'tainacan-media',
				'callback'            => [ Ability_Media::get_instance(), 'upload' ],
				'input_schema'        => $this->object_schema(
					[
						'filename'       => [
							'type'        => 'string',
							'description' => __( 'File name including extension, for example document.pdf.', 'tainacan' ),
						],
						'content_base64' => [
							'type'        => 'string',
							'description' => __( 'Base64 encoded file content. Provide this or url.', 'tainacan' ),
						],
						'url'            => [
							'type'        => 'string',
							'description' => __( 'URL to fetch the file from. Provide this or content_base64.', 'tainacan' ),
						],
						'title'          => [
							'type'        => 'string',
							'description' => __( 'Attachment title. Defaults to the filename without extension.', 'tainacan' ),
						],
						'description'    => [
							'type'        => 'string',
							'description' => __( 'Attachment description.', 'tainacan' ),
						],
						'alt_text'       => [
							'type'        => 'string',
							'description' => __( 'Alt text, for accessibility and image search.', 'tainacan' ),
						],
					],
					[ 'filename' ]
				),
				'annotations'         => $create,
				'required_capability' => 'upload_files',
			],

			'tainacan/attachment-get' => [
				'label'               => __( 'Get an attachment', 'tainacan' ),
				'description'         => __( 'Returns the URL, mime type, title, alt text and size of an attachment.', 'tainacan' ),
				'category'            => 'tainacan-media',
				'callback'            => [ Ability_Media::get_instance(), 'get' ],
				'input_schema'        => $this->object_schema(
					[ 'attachment_id' => $this->id_schema( __( 'Attachment ID.', 'tainacan' ) ) ],
					[ 'attachment_id' ]
				),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/item-set-document' => [
				'label'               => __( 'Set an item document', 'tainacan' ),
				'description'         => __( 'Binds a document to an item, either an attachment ID or an external URL. Set document_type to attachment for an uploaded file, url for an external link, or empty to remove the document.', 'tainacan' ),
				'category'            => 'tainacan-media',
				'route'               => $root . '/items/{item_id}',
				'method'              => 'PATCH',
				'route_params'        => [ 'item_id' ],
				'input_schema'        => $this->object_schema(
					[
						'item_id'       => $this->id_schema( __( 'Item ID.', 'tainacan' ) ),
						'document'      => [
							/*
							 * The items route declares document as a string, so an integer
							 * attachment ID is rejected by the REST arg schema before the
							 * controller ever runs. Send the ID as a string.
							 */
							'type'        => 'string',
							'description' => __( 'Attachment ID as a string, or a URL, depending on document_type.', 'tainacan' ),
						],
						'document_type' => [
							'type'        => 'string',
							'enum'        => [ 'attachment', 'url', 'empty' ],
							'description' => __( 'How the document is stored.', 'tainacan' ),
						],
					],
					[ 'item_id', 'document_type' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			'tainacan/item-extract-document-content' => [
				'label'               => __( 'Extract an item document text', 'tainacan' ),
				'description'         => __( 'Runs text extraction for the item document and stores the result on the item. PDFs are supported by default; the same text feeds keyword search and semantic indexing.', 'tainacan' ),
				'category'            => 'tainacan-media',
				'route'               => $root . '/items/{item_id}/document-content-index/extract',
				'method'              => 'GET',
				'route_params'        => [ 'item_id' ],
				'input_schema'        => $this->object_schema(
					[ 'item_id' => $this->id_schema( __( 'Item ID.', 'tainacan' ) ) ],
					[ 'item_id' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			/*
				* ---------------------------------------------------------------
				* Workflows. These compose the abilities above rather than
				* re-implementing their steps.
				* ---------------------------------------------------------------
				*/
			'tainacan/collection-scaffold' => [
				'label'               => __( 'Scaffold a collection', 'tainacan' ),
				'description'         => __( 'Creates a collection and its metadata definitions in one call. The fastest way to stand up a new repository structure. On a partial failure it returns the collection ID already created, so nothing is duplicated by a retry.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'callback'            => [ Ability_Workflows::get_instance(), 'scaffold' ],
				'input_schema'        => $this->object_schema(
					[
						'name'        => [
							'type'        => 'string',
							'description' => __( 'Collection name.', 'tainacan' ),
						],
						'description' => [
							'type'        => 'string',
							'description' => __( 'Collection description.', 'tainacan' ),
						],
						'status'      => $this->status_schema( __( 'Collection status.', 'tainacan' ) ),
						'metadata'    => [
							'type'        => 'array',
							'description' => __( 'Metadata definitions to create, each with name, metadata_type and optionally description, multiple and required.', 'tainacan' ),
							'items'       => [
								'type'       => 'object',
								'properties' => [
									'name'          => [ 'type' => 'string' ],
									'metadata_type' => [ 'type' => 'string' ],
									'description'   => [ 'type' => 'string' ],
									'multiple'      => [ 'type' => 'boolean' ],
									'required'      => [ 'type' => 'boolean' ],
								],
								'required'   => [ 'name', 'metadata_type' ],
							],
						],
					],
					[ 'name' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/item-create-with-metadata' => [
				'label'               => __( 'Create an item with metadata', 'tainacan' ),
				'description'         => __( 'Creates an item, sets its metadata values and optionally binds a document, in one call. Returns which metadata entries failed instead of aborting the whole item.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'callback'            => [ Ability_Workflows::get_instance(), 'create_item_with_metadata' ],
				'input_schema'        => $this->object_schema(
					[
						'collection_id'          => $this->id_schema( __( 'Collection that will own the item.', 'tainacan' ) ),
						'title'                  => [
							'type'        => 'string',
							'description' => __( 'Item title.', 'tainacan' ),
						],
						'description'            => [
							'type'        => 'string',
							'description' => __( 'Item description.', 'tainacan' ),
						],
						'status'                 => $this->status_schema( __( 'Item status.', 'tainacan' ) ),
						'document_attachment_id' => $this->id_schema( __( 'Optional attachment ID to bind as the document.', 'tainacan' ) ),
						'metadata'               => [
							'type'        => 'array',
							'description' => __( 'Metadata values, each with metadatum_id and values.', 'tainacan' ),
							'items'       => [
								'type'       => 'object',
								'properties' => [
									'metadatum_id' => [ 'type' => 'integer' ],
									'values'       => [ 'type' => [ 'string', 'number', 'integer', 'boolean', 'array' ] ],
								],
								'required'   => [ 'metadatum_id' ],
							],
						],
					],
					[ 'collection_id', 'title' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/batch-items-create' => [
				'label'               => __( 'Create many items', 'tainacan' ),
				'description'         => __( 'Creates several items in one call, each with optional metadata and document. Reports failures per index so a retry only re-sends what did not land. Capped to keep a single call from timing out.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'callback'            => [ Ability_Workflows::get_instance(), 'batch_items' ],
				'input_schema'        => $this->object_schema(
					[
						'collection_id' => $this->id_schema( __( 'Collection that will own the items.', 'tainacan' ) ),
						'items'         => [
							'type'        => 'array',
							'description' => __( 'Item specifications: title, optional description, status, document_attachment_id and metadata.', 'tainacan' ),
							/*
							 * title is deliberately NOT required here. Marking it required
							 * would let one malformed row reject the whole batch before the
							 * callback runs. Instead each row fails on its own and is
							 * reported by index, so 499 good items still land.
							 */
							'items'       => [
								'type'       => 'object',
								'properties' => [
									'title'                  => [ 'type' => 'string' ],
									'description'            => [ 'type' => 'string' ],
									'status'                 => [ 'type' => 'string' ],
									'document_attachment_id' => [ 'type' => 'integer' ],
									'metadata'               => [ 'type' => 'array' ],
								],
							],
						],
					],
					[ 'collection_id', 'items' ]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/bulk-set-status' => [
				'label'               => __( 'Set status of many items', 'tainacan' ),
				'description'         => __( 'Applies one status operation to a list of items: publish, draft, or trash. Reports per-item failures. Trash is destructive but recoverable, unlike the permanently flag on item-delete.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'callback'            => [ Ability_Workflows::get_instance(), 'bulk_set_status' ],
				'input_schema'        => $this->object_schema(
					[
						'operation' => [
							'type'        => 'string',
							'enum'        => [ 'publish', 'draft', 'trash' ],
							'description' => __( 'Status operation to apply to every item.', 'tainacan' ),
						],
						'item_ids'  => [
							'type'        => 'array',
							'description' => __( 'Item IDs to update.', 'tainacan' ),
							'items'       => [ 'type' => 'integer' ],
						],
					],
					[ 'operation', 'item_ids' ]
				),
				'annotations'         => [
					'readonly'    => false,
					'destructive' => true,
					'idempotent'  => true,
				],
				'required_capability' => 'read',
			],

			/*
				* ---------------------------------------------------------------
				* Escape hatch.
				* ---------------------------------------------------------------
				*/
			'tainacan/rest-request' => [
				'label'               => __( 'Request any Tainacan REST route', 'tainacan' ),
				'description'         => __( 'Calls a Tainacan REST route directly, for endpoints without a dedicated ability. Only routes under tainacan/v2 are reachable, and the route checks the current user itself, so this cannot do more than the caller could do over HTTP. Prefer a dedicated ability when one exists: those describe their arguments.', 'tainacan' ),
				'category'            => 'tainacan',
				'callback'            => [ Ability_Bridge::get_instance(), 'escape_hatch' ],
				'input_schema'        => $this->object_schema(
					[
						'route'  => [
							'type'        => 'string',
							'description' => __( 'Route such as tainacan/v2/reports/repository/summary. The leading slash is optional.', 'tainacan' ),
						],
						'method' => [
							'type'        => 'string',
							'enum'        => [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ],
							'description' => __( 'HTTP method, GET by default.', 'tainacan' ),
						],
						'params' => [
							'type'        => 'object',
							'description' => __( 'Query parameters for GET, body parameters otherwise.', 'tainacan' ),
						],
					],
					[ 'route' ]
				),
				'annotations'         => [
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => false,
				],
				'required_capability' => 'read',
			],

			/*
				* ---------------------------------------------------------------
				* Import and export sessions.
				*
				* These are the primitives of a longer dance: list the available
				* importers, create a session, point it at a source, inspect the
				* mapping, then run it. The session payload is passed through and
				* validated by the controller, because the accepted options depend on
				* the importer or exporter chosen.
				* ---------------------------------------------------------------
				*/
			'tainacan/importer-list' => [
				'label'               => __( 'List available importers', 'tainacan' ),
				'description'         => __( 'Returns the importer types this installation offers. Use it before creating an import session so the importer slug is valid.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/importers/available',
				'method'              => 'GET',
				'input_schema'        => $this->object_schema( [] ),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/importer-session-create' => [
				'label'               => __( 'Create an import session', 'tainacan' ),
				'description'         => __( 'Creates an import session. Send the importer slug and its options; the imported controller validates them because they vary per importer. Returns the session id used by the other import abilities.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/importers/session',
				'method'              => 'POST',
				'input_schema'        => $this->object_schema(
					[
						'importer'      => [
							'type'        => 'string',
							'description' => __( 'Importer class or slug, as returned by importer-list.', 'tainacan' ),
						],
						'collection_id' => $this->id_schema( __( 'Collection the imported items belong to.', 'tainacan' ) ),
					]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/importer-session-update' => [
				'label'               => __( 'Configure an import session', 'tainacan' ),
				'description'         => __( 'Updates an import session, for example to set the source file or the mapping before running it.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/importers/session/{session_id}',
				'method'              => 'PATCH',
				'route_params'        => [ 'session_id' ],
				'input_schema'        => $this->object_schema(
					[ 'session_id' => $this->session_schema( __( 'Import session id, a hex string.', 'tainacan' ) ) ],
					[ 'session_id' ]
				),
				'annotations'         => $update,
				'required_capability' => 'read',
			],

			'tainacan/importer-session-run' => [
				'label'               => __( 'Run an import session', 'tainacan' ),
				'description'         => __( 'Starts the import. The work runs in the background, so poll the session or the background processes before assuming it finished. A process stuck in waiting means the loopback URL is not reachable.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/importers/session/{session_id}/run',
				'method'              => 'POST',
				'route_params'        => [ 'session_id' ],
				'input_schema'        => $this->object_schema(
					[ 'session_id' => $this->session_schema( __( 'Import session id, a hex string.', 'tainacan' ) ) ],
					[ 'session_id' ]
				),
				'annotations'         => [
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => false,
				],
				'required_capability' => 'read',
			],

			'tainacan/exporter-list' => [
				'label'               => __( 'List available exporters', 'tainacan' ),
				'description'         => __( 'Returns the exporter types this installation offers. Use it before creating an export session.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/exporters/available',
				'method'              => 'GET',
				'input_schema'        => $this->object_schema( [] ),
				'annotations'         => $read,
				'required_capability' => 'read',
			],

			'tainacan/exporter-session-create' => [
				'label'               => __( 'Create an export session', 'tainacan' ),
				'description'         => __( 'Creates an export session. Send the exporter slug and its options; the controller validates them because they vary per exporter.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/exporters/session',
				'method'              => 'POST',
				'input_schema'        => $this->object_schema(
					[
						'exporter'      => [
							'type'        => 'string',
							'description' => __( 'Exporter class or slug, as returned by exporter-list.', 'tainacan' ),
						],
						'collection_id' => $this->id_schema( __( 'Collection to export.', 'tainacan' ) ),
					]
				),
				'annotations'         => $create,
				'required_capability' => 'read',
			],

			'tainacan/exporter-session-run' => [
				'label'               => __( 'Run an export session', 'tainacan' ),
				'description'         => __( 'Starts the export. The work runs in the background; poll the session before assuming the file exists.', 'tainacan' ),
				'category'            => 'tainacan-content',
				'route'               => $root . '/exporters/session/{session_id}/run',
				'method'              => 'POST',
				'route_params'        => [ 'session_id' ],
				'input_schema'        => $this->object_schema(
					[ 'session_id' => $this->session_schema( __( 'Export session id, a hex string.', 'tainacan' ) ) ],
					[ 'session_id' ]
				),
				'annotations'         => [
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => false,
				],
				'required_capability' => 'read',
			],
		];

		/**
		 * Filters the Tainacan ability definitions.
		 *
		 * @param array<string, array> $definitions Ability definitions keyed by registration name.
		 */
		return apply_filters( 'tainacan_register_abilities', $definitions );
	}

	/**
	 * Register every definition with the Abilities API.
	 *
	 * Must run on `wp_abilities_api_init`.
	 *
	 * @return void
	 */
	public function register_all() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		foreach ( $this->get_definitions() as $name => $definition ) {
			wp_register_ability( $name, $this->build_args( $definition ) );
		}
	}

	/**
	 * Turn a definition row into the argument array wp_register_ability() expects.
	 *
	 * @param array $definition A single row from get_definitions().
	 * @return array
	 */
	public function build_args( array $definition ) {
		$capability = isset( $definition['required_capability'] )
			? (string) $definition['required_capability']
			: 'read';

		$args = [
			'label'               => isset( $definition['label'] ) ? (string) $definition['label'] : '',
			'description'         => isset( $definition['description'] ) ? (string) $definition['description'] : '',
			'category'            => isset( $definition['category'] ) ? (string) $definition['category'] : '',
			'input_schema'        => isset( $definition['input_schema'] ) && is_array( $definition['input_schema'] )
				? $definition['input_schema']
				: $this->object_schema( [] ),
			'execute_callback'    => $this->build_execute_callback( $definition ),
			'permission_callback' => function () use ( $capability ) {
				/*
				 * This is a floor, not the gate. The route's own
				 * permission_callback still runs inside rest_do_request(), so
				 * per-collection checks and Tainacan\Roles capabilities apply.
				 */
				return current_user_can( $capability );
			},
			'meta'                => $this->build_meta( $definition ),
		];

		/*
		 * output_schema is intentionally omitted unless a row provides one.
		 * The Abilities API validates the callback's return value against it, and
		 * an imprecise schema for a rich REST payload produces false failures.
		 */
		if ( isset( $definition['output_schema'] ) && is_array( $definition['output_schema'] ) ) {
			$args['output_schema'] = $definition['output_schema'];
		}

		return $args;
	}

	/**
	 * Resolve the callback for a row.
	 *
	 * Most abilities delegate to a REST route through the bridge. A few, such as
	 * the attachment upload, have no route to delegate to and carry their own
	 * callable instead.
	 *
	 * @param array $definition A single row from get_definitions().
	 * @return callable
	 */
	private function build_execute_callback( array $definition ) {
		if ( isset( $definition['callback'] ) && is_callable( $definition['callback'] ) ) {
			return $definition['callback'];
		}

		return function ( $input = [] ) use ( $definition ) {
			return Ability_Bridge::get_instance()->execute( $definition, $input );
		};
	}

	/**
	 * Build the meta array: public exposure, MCP type and annotations.
	 *
	 * @param array $definition A single row from get_definitions().
	 * @return array
	 */
	private function build_meta( array $definition ) {
		$annotations = isset( $definition['annotations'] ) && is_array( $definition['annotations'] )
			? $definition['annotations']
			: [];

		/*
		 * Defaults are deliberately conservative. Claiming readonly for an ability
		 * that writes is the one annotation error that could make a client
		 * auto-approve a destructive call, so an unannotated row is described as
		 * the least safe thing it might be: not readonly, not destructive, not
		 * idempotent. Rows that are genuinely reads must say so.
		 */
		return [
			// Without this, the ability exists but no client, MCP included, sees it.
			'public'      => true,
			'mcp'         => [
				'type' => 'tool',
			],
			'annotations' => [
				'readonly'    => isset( $annotations['readonly'] ) ? (bool) $annotations['readonly'] : false,
				'destructive' => isset( $annotations['destructive'] ) ? (bool) $annotations['destructive'] : false,
				'idempotent'  => isset( $annotations['idempotent'] ) ? (bool) $annotations['idempotent'] : false,
			],
		];
	}

	/**
	 * A permissive object schema. Unknown keys are allowed on purpose: the REST
	 * route validates what it understands and ignores the rest.
	 *
	 * @param array $properties Property definitions.
	 * @param array $required   Required property names.
	 * @return array
	 */
	private function object_schema( array $properties, array $required = [] ) {
		$schema = [
			/*
			 * null is part of the type list on purpose. The Abilities REST run
			 * endpoint passes whatever the client sent straight through, and a
			 * client that omits input entirely sends null. With a strict "object"
			 * type that call fails with "input is not of type object" before the
			 * callback runs, which would make every zero-argument ability
			 * uncallable from such a client.
			 */
			'type'                 => [ 'object', 'null' ],
			'properties'           => $properties,
			'additionalProperties' => true,
		];

		if ( ! empty( $required ) ) {
			$schema['required'] = array_values( $required );
		}

		return $schema;
	}

	/**
	 * Schema for structural entities that share name, description and status:
	 * metadata definitions, sections, taxonomies, terms and filters.
	 *
	 * @param array $extra Additional properties merged in.
	 * @param array $required Required property names.
	 * @return array
	 */
	private function structure_schema( array $extra = [], array $required = [] ) {
		$properties = array_merge(
			[
				'name'        => [
					'type'        => 'string',
					'description' => __( 'Display name.', 'tainacan' ),
				],
				'description' => [
					'type'        => 'string',
					'description' => __( 'Optional description.', 'tainacan' ),
				],
				'status'      => $this->status_schema( __( 'Object status.', 'tainacan' ) ),
			],
			$extra
		);

		return $this->object_schema( $properties, $required );
	}

	/**
	 * Schema for list endpoints: filters plus pagination.
	 *
	 * @param array $extra Additional properties merged in.
	 * @param array $required Required property names.
	 * @return array
	 */
	private function list_schema( array $extra = [], array $required = [] ) {
		$properties = array_merge(
			[
				'search'   => [
					'type'        => 'string',
					'description' => __( 'Free-text search term.', 'tainacan' ),
				],
				'page'     => [
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'Page number, starting at 1.', 'tainacan' ),
				],
				'per_page' => [
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'Items per page.', 'tainacan' ),
				],
				'order'    => [
					'type'        => 'string',
					'enum'        => [ 'ASC', 'DESC' ],
					'description' => __( 'Sort direction.', 'tainacan' ),
				],
				'orderby'  => [
					'type'        => 'string',
					'description' => __( 'Field to sort by.', 'tainacan' ),
				],
				'status'   => $this->status_schema( __( 'Filter by post status.', 'tainacan' ) ),
			],
			$extra
		);

		return $this->object_schema( $properties, $required );
	}

	/**
	 * Schema for item create and update payloads.
	 *
	 * @param array $extra Additional properties merged in.
	 * @param array $required Required property names.
	 * @return array
	 */
	private function item_write_schema( array $extra = [], array $required = [] ) {
		$properties = array_merge(
			[
				'title'       => [
					'type'        => 'string',
					'description' => __( 'Item title.', 'tainacan' ),
				],
				'description' => [
					'type'        => 'string',
					'description' => __( 'Item description.', 'tainacan' ),
				],
				'status'      => $this->status_schema( __( 'Item status: publish makes it public, draft stages it.', 'tainacan' ) ),
				'document'    => [
					'description' => __( 'Document reference: an attachment ID or a URL, depending on document_type.', 'tainacan' ),
				],
				'document_type' => [
					'type'        => 'string',
					'description' => __( 'How the document is stored, for example attachment, url or empty.', 'tainacan' ),
				],
			],
			$extra
		);

		return $this->object_schema( $properties, $required );
	}

	/**
	 * Post status schema shared by list filters and write payloads.
	 *
	 * @param string $description Field description.
	 * @return array
	 */
	private function status_schema( $description ) {
		return [
			'type'        => 'string',
			'enum'        => [ 'publish', 'draft', 'private', 'pending', 'trash' ],
			'description' => $description,
		];
	}

	/**
	 * Integer identifier schema.
	 *
	 * @param string $description Field description.
	 * @return array
	 */
	private function id_schema( $description ) {
		return [
			'type'        => 'integer',
			'minimum'     => 1,
			'description' => $description,
		];
	}

	/**
	 * Import and export sessions are identified by a hex string, not an integer.
	 *
	 * @param string $description Field description.
	 * @return array
	 */
	private function session_schema( $description ) {
		return [
			'type'        => 'string',
			'description' => $description,
		];
	}
}
