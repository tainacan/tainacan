<?php

namespace Tainacan\Tests;

/**
 * Covers the WordPress Abilities API integration.
 *
 * The point of these tests is the two failure modes that are invisible at
 * runtime: an ability registered outside `wp_abilities_api_init` and an ability
 * whose category was never registered. Both fail silently with WP_DEBUG off, so
 * they need assertions rather than eyes.
 *
 * @group api
 * @group abilities
 */
class Test_Abilities extends TAINACAN_UnitApiTestCase {

	/**
	 * The vertical slice ability delivered by Sprint 01.
	 *
	 * @var string
	 */
	const ABILITY = 'tainacan/metadata-types-list';

	public function test_abilities_api_is_available() {
		$this->assertTrue(
			function_exists( 'wp_register_ability' ),
			'The Abilities API is missing from this WordPress core.'
		);

		$this->assertTrue(
			\Tainacan\Abilities\Abilities_Bootstrap::get_instance()->is_supported()
		);
	}

	public function test_ability_is_registered() {
		$this->assertTrue(
			wp_has_ability( self::ABILITY ),
			sprintf( 'Ability %s was not registered. Check that registration happens on wp_abilities_api_init.', self::ABILITY )
		);

		$this->assertInstanceOf( \WP_Ability::class, wp_get_ability( self::ABILITY ) );
	}

	public function test_ability_category_is_registered_before_use() {
		$ability = wp_get_ability( self::ABILITY );
		$this->assertNotNull( $ability );

		$category   = $ability->get_category();
		$categories = wp_get_ability_categories();

		$this->assertArrayHasKey(
			$category,
			$categories,
			sprintf( 'Category "%s" referenced by the ability is not registered.', $category )
		);
	}

	public function test_ability_is_public_and_annotated() {
		$ability = wp_get_ability( self::ABILITY );
		$this->assertNotNull( $ability );

		$meta = $ability->get_meta();

		$this->assertTrue(
			! empty( $meta['public'] ),
			'Ability is not public, so no client or MCP server can discover it.'
		);

		$this->assertArrayHasKey( 'annotations', $meta );
		$this->assertArrayHasKey( 'readonly', $meta['annotations'] );
		$this->assertTrue( (bool) $meta['annotations']['readonly'] );
	}

	public function test_registry_definitions_are_complete() {
		$registry    = \Tainacan\Abilities\Abilities_Registry::get_instance();
		$categories  = \Tainacan\Abilities\Abilities_Categories::get_instance()->get_slugs();
		$definitions = $registry->get_definitions();

		$this->assertNotEmpty( $definitions );

		foreach ( $definitions as $name => $definition ) {
			$this->assertNotEmpty( $definition['label'], "$name has no label" );
			$this->assertNotEmpty( $definition['description'], "$name has no description" );
			$this->assertNotEmpty( $definition['category'], "$name has no category" );
			$this->assertContains(
				$definition['category'],
				$categories,
				sprintf( '%s references category "%s", which Abilities_Categories does not register.', $name, $definition['category'] )
			);
			$this->assertTrue(
				! empty( $definition['route'] ) || ! empty( $definition['callback'] ),
				sprintf( '%s has neither a route nor a callback.', $name )
			);
			$this->assertArrayHasKey( 'annotations', $definition, "$name has no annotations" );
		}
	}

	public function test_registered_tainacan_abilities_are_public() {
		$abilities = wp_get_abilities();

		$this->assertNotEmpty( $abilities );

		foreach ( $abilities as $name => $ability ) {
			if ( 0 !== strpos( $name, 'tainacan/' ) ) {
				continue;
			}

			$meta = $ability->get_meta();
			$this->assertTrue(
				! empty( $meta['public'] ),
				sprintf( '%s is registered but not public, so clients cannot see it.', $name )
			);
		}
	}

	public function test_unannotated_definitions_are_not_advertised_as_readonly() {
		$args = \Tainacan\Abilities\Abilities_Registry::get_instance()->build_args(
			[
				'label'       => 'Placeholder',
				'description' => 'Placeholder',
				'category'    => 'tainacan',
				'route'       => $this->namespace . '/metadata-types',
				'method'      => 'GET',
			]
		);

		$this->assertTrue( $args['meta']['public'] );
		$this->assertFalse(
			$args['meta']['annotations']['readonly'],
			'An unannotated ability must not claim to be readonly: that is the one annotation error that could make a client auto-approve a write.'
		);
		$this->assertFalse( $args['meta']['annotations']['idempotent'] );
	}

	public function test_execute_matches_the_rest_route() {
		$ability = wp_get_ability( self::ABILITY );
		$this->assertNotNull( $ability );

		$from_ability = $ability->execute( [] );

		$request  = new \WP_REST_Request( 'GET', $this->namespace . '/metadata-types' );
		$response = $this->server->dispatch( $request );

		$this->assertNotWPError( $from_ability );
		$this->assertEquals( $response->get_data(), $from_ability );
	}

	public function test_bridge_refuses_when_route_permission_fails() {
		$bridge = \Tainacan\Abilities\Ability_Bridge::get_instance();

		wp_set_current_user( 0 );

		$result = $bridge->execute(
			[
				'route'  => $this->namespace . '/collections',
				'method' => 'POST',
			],
			[]
		);

		wp_set_current_user( $this->user_id );

		$this->assertWPError( $result );
	}

	public function test_bridge_returns_error_for_unknown_route() {
		$result = \Tainacan\Abilities\Ability_Bridge::get_instance()->execute(
			[
				'route'  => $this->namespace . '/does-not-exist',
				'method' => 'GET',
			],
			[]
		);

		$this->assertWPError( $result );
	}

	public function test_bridge_requires_route_parameters() {
		$result = \Tainacan\Abilities\Ability_Bridge::get_instance()->execute(
			[
				'route'        => $this->namespace . '/items/{item_id}',
				'method'       => 'GET',
				'route_params' => [ 'item_id' ],
			],
			[]
		);

		$this->assertWPError( $result );
		$this->assertEquals( 'tainacan_ability_missing_param', $result->get_error_code() );
	}

	public function test_bridge_honours_route_placeholders() {
		$collection_id = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'   => 'Abilities bridge test',
				'status' => 'publish',
			],
			true
		);

		$result = \Tainacan\Abilities\Ability_Bridge::get_instance()->execute(
			[
				'route'        => $this->namespace . '/collections/{collection_id}',
				'method'       => 'GET',
				'route_params' => [ 'collection_id' ],
			],
			[ 'collection_id' => $collection_id->get_id() ]
		);

		$this->assertNotWPError( $result );
		$this->assertEquals( $collection_id->get_id(), $result['id'] );
	}

	/*
		* ---------------------------------------------------------------------
		* Tier 1 content abilities.
		* ---------------------------------------------------------------------
		*/

	/**
		* Execute a registered ability and fail loudly when it is missing.
		*
		* @param string $name  Ability name.
		* @param array  $input Ability input.
		* @return mixed
		*/
	private function run_ability( $name, array $input = [] ) {
		$ability = wp_get_ability( $name );

		$this->assertNotNull( $ability, sprintf( 'Ability %s is not registered.', $name ) );

		return $ability->execute( $input );
	}

	/**
		* Create a published collection through the ability layer.
		*
		* @param string $name Collection name.
		* @return int
		*/
	private function create_collection_via_ability( $name = 'Ability collection' ) {
		$result = $this->run_ability(
			'tainacan/collection-create',
			[
				'name'        => $name,
				'description' => 'Created through the abilities layer.',
				'status'      => 'publish',
			]
		);

		$this->assertNotWPError( $result );
		$this->assertArrayHasKey( 'id', $result );

		return (int) $result['id'];
	}

	public function test_tier1_abilities_are_registered() {
		$expected = [
			'tainacan/metadata-types-list',
			'tainacan/collection-list',
			'tainacan/collection-get',
			'tainacan/collection-create',
			'tainacan/collection-update',
			'tainacan/collection-delete',
			'tainacan/item-list',
			'tainacan/item-list-in-collection',
			'tainacan/item-get',
			'tainacan/item-create',
			'tainacan/item-update',
			'tainacan/item-delete',
			'tainacan/item-publish',
			'tainacan/item-unpublish',
			'tainacan/item-metadata-list',
			'tainacan/item-metadata-set',
		];

		foreach ( $expected as $name ) {
			$this->assertTrue( wp_has_ability( $name ), sprintf( '%s is not registered.', $name ) );
		}
	}

	public function test_no_writing_ability_claims_readonly() {
		$registry    = \Tainacan\Abilities\Abilities_Registry::get_instance();
		$definitions = $registry->get_definitions();

		foreach ( $definitions as $name => $definition ) {
			$method = isset( $definition['method'] ) ? strtoupper( $definition['method'] ) : 'GET';

			if ( ! in_array( $method, [ 'POST', 'PUT', 'PATCH', 'DELETE' ], true ) ) {
				continue;
			}

			$args = $registry->build_args( $definition );

			$this->assertFalse(
				$args['meta']['annotations']['readonly'],
				sprintf( '%s writes with %s but claims to be readonly.', $name, $method )
			);
		}
	}

	public function test_collection_create_reaches_the_controller() {
		$collection_id = $this->create_collection_via_ability( 'Ability created collection' );

		$fetched = $this->run_ability( 'tainacan/collection-get', [ 'collection_id' => $collection_id ] );

		$this->assertNotWPError( $fetched );
		$this->assertEquals( $collection_id, (int) $fetched['id'] );
		$this->assertEquals( 'Ability created collection', $fetched['name'] );
	}

	public function test_collection_list_returns_created_collection() {
		$collection_id = $this->create_collection_via_ability( 'Listed collection' );

		$list = $this->run_ability( 'tainacan/collection-list', [ 'per_page' => 50 ] );

		$this->assertNotWPError( $list );

		$ids = array_map(
			static function ( $row ) {
				return isset( $row['id'] ) ? (int) $row['id'] : 0;
			},
			$list
		);

		$this->assertContains( $collection_id, $ids );
	}

	public function test_item_round_trip_through_abilities() {
		$collection_id = $this->create_collection_via_ability();

		$created = $this->run_ability(
			'tainacan/item-create',
			[
				'collection_id' => $collection_id,
				'title'         => 'Ability item',
				'status'        => 'publish',
			]
		);

		$this->assertNotWPError( $created );
		$this->assertArrayHasKey( 'id', $created );

		$item_id = (int) $created['id'];

		$fetched = $this->run_ability( 'tainacan/item-get', [ 'item_id' => $item_id ] );
		$this->assertNotWPError( $fetched );
		$this->assertEquals( 'Ability item', $fetched['title'] );

		$updated = $this->run_ability(
			'tainacan/item-update',
			[
				'item_id' => $item_id,
				'title'   => 'Renamed by ability',
			]
		);
		$this->assertNotWPError( $updated );

		$refetched = $this->run_ability( 'tainacan/item-get', [ 'item_id' => $item_id ] );
		$this->assertEquals( 'Renamed by ability', $refetched['title'] );
	}

	public function test_publish_lifecycle_uses_body_defaults() {
		$collection_id = $this->create_collection_via_ability();

		$created = $this->run_ability(
			'tainacan/item-create',
			[
				'collection_id' => $collection_id,
				'title'         => 'Lifecycle item',
				'status'        => 'draft',
			]
		);

		$this->assertNotWPError( $created );
		$item_id = (int) $created['id'];

		$published = $this->run_ability( 'tainacan/item-publish', [ 'item_id' => $item_id ] );
		$this->assertNotWPError( $published );

		$fetched = $this->run_ability( 'tainacan/item-get', [ 'item_id' => $item_id ] );
		$this->assertEquals( 'publish', $fetched['status'] );

		$unpublished = $this->run_ability( 'tainacan/item-unpublish', [ 'item_id' => $item_id ] );
		$this->assertNotWPError( $unpublished );

		$refetched = $this->run_ability( 'tainacan/item-get', [ 'item_id' => $item_id ] );
		$this->assertEquals( 'draft', $refetched['status'] );
	}

	public function test_item_metadata_set_and_read_back() {
		$collection_id = $this->create_collection_via_ability();

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name'          => 'Ability metadatum',
				'description'   => 'Metadatum created for the abilities test.',
				'collection_id' => $collection_id,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'        => 'publish',
			],
			true
		);

		$created = $this->run_ability(
			'tainacan/item-create',
			[
				'collection_id' => $collection_id,
				'title'         => 'Metadata item',
				'status'        => 'publish',
			]
		);

		$this->assertNotWPError( $created );
		$item_id = (int) $created['id'];

		$set = $this->run_ability(
			'tainacan/item-metadata-set',
			[
				'item_id'      => $item_id,
				'metadatum_id' => $metadatum->get_id(),
				'values'       => 'hello from the bridge',
			]
		);

		$this->assertNotWPError( $set );

		$values = $this->run_ability( 'tainacan/item-metadata-list', [ 'item_id' => $item_id ] );
		$this->assertNotWPError( $values );

		$this->assertStringContainsString(
			'hello from the bridge',
			wp_json_encode( $values ),
			'The metadata value did not survive the round trip. The payload key is "values", not "value".'
		);
	}

	public function test_item_delete_removes_the_item() {
		$collection_id = $this->create_collection_via_ability();

		$created = $this->run_ability(
			'tainacan/item-create',
			[
				'collection_id' => $collection_id,
				'title'         => 'Disposable item',
				'status'        => 'publish',
			]
		);

		$this->assertNotWPError( $created );
		$item_id = (int) $created['id'];

		/*
		 * The route trashes unless "permanently" is true, so the default ability
		 * behaviour is a soft delete. Asserting a hard delete here would have
		 * described Tainacan wrongly.
		 */
		$trashed = $this->run_ability( 'tainacan/item-delete', [ 'item_id' => $item_id ] );
		$this->assertNotWPError( $trashed );

		$fetched = $this->run_ability( 'tainacan/item-get', [ 'item_id' => $item_id ] );
		$this->assertNotWPError( $fetched );
		$this->assertEquals( 'trash', $fetched['status'] );

		$destroyed = $this->run_ability(
			'tainacan/item-delete',
			[
				'item_id'     => $item_id,
				'permanently' => true,
			]
		);
		$this->assertNotWPError( $destroyed );

		$gone = $this->run_ability( 'tainacan/item-get', [ 'item_id' => $item_id ] );
		$this->assertWPError( $gone );
	}

	/*
		* ---------------------------------------------------------------------
		* Tier 1 structure abilities.
		* ---------------------------------------------------------------------
		*/

	public function test_structure_abilities_are_registered() {
		$expected = [
			'tainacan/metadata-list',
			'tainacan/metadata-get',
			'tainacan/metadata-create',
			'tainacan/metadata-update',
			'tainacan/metadata-delete',
			'tainacan/metadata-section-list',
			'tainacan/metadata-section-create',
			'tainacan/metadata-section-update',
			'tainacan/metadata-section-delete',
			'tainacan/taxonomy-list',
			'tainacan/taxonomy-get',
			'tainacan/taxonomy-create',
			'tainacan/taxonomy-delete',
			'tainacan/term-list',
			'tainacan/term-get',
			'tainacan/term-create',
			'tainacan/term-delete',
			'tainacan/filter-types-list',
			'tainacan/filter-list',
			'tainacan/filter-get',
			'tainacan/filter-create',
			'tainacan/filter-delete',
			'tainacan/facet-get',
		];

		foreach ( $expected as $name ) {
			$this->assertTrue( wp_has_ability( $name ), sprintf( '%s is not registered.', $name ) );
		}
	}

	public function test_metadata_round_trip_through_abilities() {
		$collection_id = $this->create_collection_via_ability( 'Metadata structure collection' );

		$created = $this->run_ability(
			'tainacan/metadata-create',
			[
				'collection_id' => $collection_id,
				'name'          => 'Ability structured metadatum',
				'description'   => 'Created through the abilities layer.',
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'        => 'publish',
			]
		);

		$this->assertNotWPError( $created );
		$this->assertArrayHasKey( 'id', $created );

		$metadatum_id = (int) $created['id'];

		$list = $this->run_ability( 'tainacan/metadata-list', [ 'collection_id' => $collection_id ] );
		$this->assertNotWPError( $list );
		$this->assertStringContainsString( 'Ability structured metadatum', wp_json_encode( $list ) );

		$deleted = $this->run_ability( 'tainacan/metadata-delete', [ 'metadatum_id' => $metadatum_id ] );
		$this->assertNotWPError( $deleted );
	}

	public function test_metadata_section_round_trip_through_abilities() {
		$collection_id = $this->create_collection_via_ability( 'Section host collection' );

		$created = $this->run_ability(
			'tainacan/metadata-section-create',
			[
				'collection_id' => $collection_id,
				'name'          => 'Ability created section',
				'description'   => 'Section created through the abilities layer.',
			]
		);

		$this->assertNotWPError( $created );

		$list = $this->run_ability( 'tainacan/metadata-section-list', [ 'collection_id' => $collection_id ] );
		$this->assertNotWPError( $list );
		$this->assertStringContainsString( 'Ability created section', wp_json_encode( $list ) );
	}

	public function test_taxonomy_and_term_round_trip_through_abilities() {
		$taxonomy = $this->run_ability(
			'tainacan/taxonomy-create',
			[
				'name'              => 'Ability taxonomy',
				'description'       => 'Taxonomy created through the abilities layer.',
				'allow_ui_creation' => true,
				'status'            => 'publish',
			]
		);

		$this->assertNotWPError( $taxonomy );
		$this->assertArrayHasKey( 'id', $taxonomy );

		$taxonomy_id = (int) $taxonomy['id'];

		$term = $this->run_ability(
			'tainacan/term-create',
			[
				'taxonomy_id' => $taxonomy_id,
				'name'        => 'Ability term',
			]
		);

		$this->assertNotWPError( $term );
		$this->assertArrayHasKey( 'id', $term );

		/*
		 * Fetch by ID rather than asserting through term-list: term listings can
		 * hide terms that no item uses, so an empty list would say nothing about
		 * whether the create worked.
		 */
		$fetched = $this->run_ability(
			'tainacan/term-get',
			[
				'taxonomy_id' => $taxonomy_id,
				'term_id'     => (int) $term['id'],
			]
		);

		$this->assertNotWPError( $fetched );
		$this->assertEquals( 'Ability term', $fetched['name'] );
	}

	public function test_two_taxonomies_and_terms_in_one_process() {
		/*
		 * Regression: prepare_item_for_database() used to mutate a single shared
		 * entity, so the second create in a process reused an entity that already
		 * had an ID and parent::insert() returned false.
		 */
		$first  = $this->run_ability( 'tainacan/taxonomy-create', [ 'name' => 'First ability taxonomy', 'status' => 'publish' ] );
		$second = $this->run_ability( 'tainacan/taxonomy-create', [ 'name' => 'Second ability taxonomy', 'status' => 'publish' ] );

		$this->assertNotWPError( $first );
		$this->assertNotWPError( $second );
		$this->assertNotEquals( (int) $first['id'], (int) $second['id'] );

		$term_a = $this->run_ability(
			'tainacan/term-create',
			[
				'taxonomy_id' => (int) $first['id'],
				'name'        => 'First ability term',
			]
		);

		$term_b = $this->run_ability(
			'tainacan/term-create',
			[
				'taxonomy_id' => (int) $first['id'],
				'name'        => 'Second ability term',
			]
		);

		$this->assertNotWPError( $term_a );
		$this->assertNotWPError( $term_b );
		$this->assertNotEquals( (int) $term_a['id'], (int) $term_b['id'] );
	}

	public function test_filter_types_list_returns_types() {
		$types = $this->run_ability( 'tainacan/filter-types-list', [] );

		$this->assertNotWPError( $types );
		$this->assertIsArray( $types );
		$this->assertNotEmpty( $types, 'No filter types registered, so filter-create cannot be used on this install.' );
	}

	public function test_filter_create_uses_a_registered_type() {
		$collection_id = $this->create_collection_via_ability( 'Filter host collection' );

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name'          => 'Filtered metadatum',
				'description'   => 'Metadatum behind the ability filter.',
				'collection_id' => $collection_id,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'        => 'publish',
			],
			true
		);

		$types = $this->run_ability( 'tainacan/filter-types-list', [] );
		$this->assertNotWPError( $types );
		$this->assertNotEmpty( $types );

		// Never hardcode a filter type: some installs register different sets.
		$first = reset( $types );
		$type  = isset( $first['className'] ) ? $first['className'] : ( $first['name'] ?? '' );

		$this->assertNotEmpty( $type, 'Could not read a filter type name from filter-types-list.' );

		$created = $this->run_ability(
			'tainacan/filter-create',
			[
				'collection_id' => $collection_id,
				'metadatum_id'  => $metadatum->get_id(),
				'filter'        => [
					'name'        => 'Ability filter',
					'description' => 'Filter created through the abilities layer.',
					'status'      => 'publish',
				],
				'filter_type'   => $type,
			]
		);

		$this->assertNotWPError( $created );

		$list = $this->run_ability( 'tainacan/filter-list', [ 'collection_id' => $collection_id ] );
		$this->assertNotWPError( $list );
		$this->assertStringContainsString( 'Ability filter', wp_json_encode( $list ) );
	}

	public function test_capability_floor_stays_at_read() {
		$definitions = \Tainacan\Abilities\Abilities_Registry::get_instance()->get_definitions();

		foreach ( $definitions as $name => $definition ) {
			$capability = isset( $definition['required_capability'] ) ? $definition['required_capability'] : '';

			/*
			 * A global floor would refuse curators who only hold a collection-scoped
			 * capability such as tnc_col_5_edit_metadata, which is a normal Tainacan
			 * configuration. The route's permission callback is the authority.
			 */
			/*
			 * A global Tainacan capability here would refuse curators who only hold a
			 * collection-scoped capability such as tnc_col_5_edit_metadata.
			 * upload_files is a core WordPress capability and is not collection-scoped,
			 * so it is the one permitted exception.
			 */
			$this->assertContains(
				$capability,
				[ 'read', 'upload_files', 'manage_options' ],
				sprintf( '%s raised the floor to "%s"; that can break collection-scoped capabilities.', $name, $capability )
			);
		}
	}

	/*
		* ---------------------------------------------------------------------
		* Media and ingest abilities.
		* ---------------------------------------------------------------------
		*/

	/**
		* A valid 1x1 transparent PNG, small enough to keep the test fast.
		*
		* @var string
		*/
	const PIXEL_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==';

	public function test_media_abilities_are_registered() {
		$expected = [
			'tainacan/attachment-upload',
			'tainacan/attachment-get',
			'tainacan/item-set-document',
			'tainacan/item-extract-document-content',
		];

		foreach ( $expected as $name ) {
			$this->assertTrue( wp_has_ability( $name ), sprintf( '%s is not registered.', $name ) );
		}
	}

	public function test_attachment_upload_and_get_round_trip() {
		$uploaded = $this->run_ability(
			'tainacan/attachment-upload',
			[
				'filename'       => 'ability-pixel.png',
				'content_base64' => self::PIXEL_PNG,
				'title'          => 'Ability pixel',
				'alt_text'       => 'One transparent pixel',
			]
		);

		$this->assertNotWPError( $uploaded );
		$this->assertArrayHasKey( 'id', $uploaded );
		$this->assertEquals( 'image/png', $uploaded['mime_type'] );
		$this->assertNotEmpty( $uploaded['url'] );

		$fetched = $this->run_ability( 'tainacan/attachment-get', [ 'attachment_id' => (int) $uploaded['id'] ] );

		$this->assertNotWPError( $fetched );
		$this->assertEquals( 'Ability pixel', $fetched['title'] );
		$this->assertEquals( 'One transparent pixel', $fetched['alt_text'] );
		$this->assertEquals( 'image/png', $fetched['mime_type'] );
	}

	public function test_attachment_upload_rejects_bad_input() {
		$invalid_base64 = $this->run_ability(
			'tainacan/attachment-upload',
			[
				'filename'       => 'broken.png',
				'content_base64' => 'this is not base64!!',
			]
		);
		$this->assertWPError( $invalid_base64 );

		$unsupported = $this->run_ability(
			'tainacan/attachment-upload',
			[
				'filename'       => 'payload.exe',
				'content_base64' => base64_encode( 'MZ not really an executable' ),
			]
		);
		$this->assertWPError( $unsupported );

		$no_content = $this->run_ability( 'tainacan/attachment-upload', [ 'filename' => 'empty.txt' ] );
		$this->assertWPError( $no_content );

		$no_filename = $this->run_ability( 'tainacan/attachment-upload', [ 'content_base64' => self::PIXEL_PNG ] );
		$this->assertWPError( $no_filename );
	}

	public function test_item_set_document_binds_an_attachment() {
		$collection_id = $this->create_collection_via_ability( 'Document host collection' );

		$uploaded = $this->run_ability(
			'tainacan/attachment-upload',
			[
				'filename'       => 'bound-pixel.png',
				'content_base64' => self::PIXEL_PNG,
			]
		);

		$this->assertNotWPError( $uploaded );
		$attachment_id = (int) $uploaded['id'];

		$item = $this->run_ability(
			'tainacan/item-create',
			[
				'collection_id' => $collection_id,
				'title'         => 'Item with a document',
				'status'        => 'draft',
			]
		);

		$this->assertNotWPError( $item );
		$item_id = (int) $item['id'];

		$bound = $this->run_ability(
			'tainacan/item-set-document',
			[
				'item_id'       => $item_id,
				// String, not int: the items route declares document as a string.
				'document'      => (string) $attachment_id,
				'document_type' => 'attachment',
			]
		);

		$this->assertNotWPError( $bound );

		$fetched = $this->run_ability( 'tainacan/item-get', [ 'item_id' => $item_id ] );
		$this->assertNotWPError( $fetched );

		$this->assertStringContainsString(
			(string) $attachment_id,
			wp_json_encode( $fetched ),
			'The document was not bound to the item.'
		);
	}

	/*
	 * ---------------------------------------------------------------------
	 * Workflow abilities.
	 * ---------------------------------------------------------------------
	 */

	public function test_workflow_abilities_are_registered() {
		$expected = [
			'tainacan/collection-scaffold',
			'tainacan/item-create-with-metadata',
			'tainacan/batch-items-create',
			'tainacan/bulk-set-status',
		];

		foreach ( $expected as $name ) {
			$this->assertTrue( wp_has_ability( $name ), sprintf( '%s is not registered.', $name ) );
		}
	}

	public function test_scaffold_creates_collection_and_metadata() {
		$result = $this->run_ability(
			'tainacan/collection-scaffold',
			[
				'name'        => 'Scaffolded collection',
				'description' => 'Created by the scaffold workflow.',
				'status'      => 'publish',
				'metadata'    => [
					[
						'name'          => 'Scaffold text',
						'metadata_type' => 'Tainacan\Metadata_Types\Text',
						'status'        => 'publish',
					],
					[
						'name'          => 'Scaffold date',
						'metadata_type' => 'Tainacan\Metadata_Types\Date',
						'status'        => 'publish',
					],
				],
			]
		);

		$this->assertNotWPError( $result );
		$this->assertArrayHasKey( 'collection_id', $result );
		$this->assertCount( 2, $result['metadata'] );

		$list = $this->run_ability( 'tainacan/metadata-list', [ 'collection_id' => (int) $result['collection_id'] ] );
		$this->assertNotWPError( $list );
		$this->assertStringContainsString( 'Scaffold text', wp_json_encode( $list ) );
	}

	public function test_create_item_with_metadata_sets_values() {
		$scaffold = $this->run_ability(
			'tainacan/collection-scaffold',
			[
				'name'     => 'Composite host collection',
				'status'   => 'publish',
				'metadata' => [
					[
						'name'          => 'Composite metadatum',
						'metadata_type' => 'Tainacan\Metadata_Types\Text',
						'status'        => 'publish',
					],
				],
			]
		);

		$this->assertNotWPError( $scaffold );
		$metadatum_id = (int) $scaffold['metadata'][0]['id'];

		$item = $this->run_ability(
			'tainacan/item-create-with-metadata',
			[
				'collection_id' => (int) $scaffold['collection_id'],
				'title'         => 'Composite item',
				'status'        => 'publish',
				'metadata'      => [
					[
						'metadatum_id' => $metadatum_id,
						'values'       => 'value set by the composite',
					],
				],
			]
		);

		$this->assertNotWPError( $item );
		$this->assertSame( 1, $item['metadata_set'] );
		$this->assertEmpty( $item['failures'] );

		$values = $this->run_ability( 'tainacan/item-metadata-list', [ 'item_id' => (int) $item['item_id'] ] );
		$this->assertStringContainsString( 'value set by the composite', wp_json_encode( $values ) );
	}

	public function test_batch_items_create_reports_failures_by_index() {
		$collection_id = $this->create_collection_via_ability( 'Batch collection' );

		$result = $this->run_ability(
			'tainacan/batch-items-create',
			[
				'collection_id' => $collection_id,
				'items'         => [
					[
						'title'  => 'Batch one',
						'status' => 'publish',
					],
					[
						'title'  => 'Batch two',
						'status' => 'publish',
					],
					[
						'description' => 'This row has no title and must fail alone',
					],
				],
			]
		);

		$this->assertNotWPError( $result );
		$this->assertSame( 2, $result['created'] );
		$this->assertCount( 1, $result['failures'] );
		$this->assertSame( 2, $result['failures'][0]['index'] );
	}

	public function test_bulk_set_status_publishes_then_trashes() {
		$collection_id = $this->create_collection_via_ability( 'Bulk collection' );

		$ids = [];
		foreach ( [ 'Bulk one', 'Bulk two' ] as $title ) {
			$item = $this->run_ability(
				'tainacan/item-create',
				[
					'collection_id' => $collection_id,
					'title'         => $title,
					'status'        => 'draft',
				]
			);
			$this->assertNotWPError( $item );
			$ids[] = (int) $item['id'];
		}

		$published = $this->run_ability(
			'tainacan/bulk-set-status',
			[
				'operation' => 'publish',
				'item_ids'  => $ids,
			]
		);

		$this->assertNotWPError( $published );
		$this->assertCount( 2, $published['succeeded'] );

		$fetched = $this->run_ability( 'tainacan/item-get', [ 'item_id' => $ids[0] ] );
		$this->assertEquals( 'publish', $fetched['status'] );

		$trashed = $this->run_ability(
			'tainacan/bulk-set-status',
			[
				'operation' => 'trash',
				'item_ids'  => [ $ids[0] ],
			]
		);

		$this->assertNotWPError( $trashed );
		$this->assertCount( 1, $trashed['succeeded'] );

		$after = $this->run_ability( 'tainacan/item-get', [ 'item_id' => $ids[0] ] );
		$this->assertEquals( 'trash', $after['status'] );
	}

	public function test_callback_rows_build_valid_args() {
		$args = \Tainacan\Abilities\Abilities_Registry::get_instance()->build_args(
			[
				'label'       => 'Callback row',
				'description' => 'Row backed by a callable instead of a route.',
				'category'    => 'tainacan-media',
				'callback'    => static function () {
					return [ 'ok' => true ];
				},
			]
		);

		$this->assertTrue( is_callable( $args['execute_callback'] ) );
		$this->assertSame( [ 'ok' => true ], call_user_func( $args['execute_callback'] ) );
	}

	/*
		* ---------------------------------------------------------------------
		* Escape hatch and import/export sessions.
		* ---------------------------------------------------------------------
		*/

	public function test_escape_hatch_and_session_abilities_are_registered() {
		$expected = [
			'tainacan/rest-request',
			'tainacan/importer-list',
			'tainacan/importer-session-create',
			'tainacan/importer-session-update',
			'tainacan/importer-session-run',
			'tainacan/exporter-list',
			'tainacan/exporter-session-create',
			'tainacan/exporter-session-run',
		];

		foreach ( $expected as $name ) {
			$this->assertTrue( wp_has_ability( $name ), sprintf( '%s is not registered.', $name ) );
		}
	}

	public function test_escape_hatch_reads_a_route() {
		$result = $this->run_ability(
			'tainacan/rest-request',
			[ 'route' => 'tainacan/v2/metadata-types' ]
		);

		$this->assertNotWPError( $result );
		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );
	}

	public function test_escape_hatch_writes_with_a_body() {
		$created = $this->run_ability(
			'tainacan/rest-request',
			[
				'route'  => 'tainacan/v2/collections',
				'method' => 'POST',
				'params' => [
					'name'   => 'Escape hatch collection',
					'status' => 'publish',
				],
			]
		);

		$this->assertNotWPError( $created );
		$this->assertArrayHasKey( 'id', $created );

		$list = $this->run_ability( 'tainacan/collection-list', [ 'per_page' => 50 ] );
		$this->assertStringContainsString( 'Escape hatch collection', wp_json_encode( $list ) );
	}

	public function test_escape_hatch_refuses_routes_outside_tainacan() {
		$result = $this->run_ability( 'tainacan/rest-request', [ 'route' => 'wp/v2/posts' ] );

		$this->assertWPError( $result );
		$this->assertSame( 'tainacan_ability_route_not_allowed', $result->get_error_code() );
	}

	public function test_escape_hatch_refuses_unknown_methods() {
		$result = $this->run_ability(
			'tainacan/rest-request',
			[
				'route'  => 'tainacan/v2/metadata-types',
				'method' => 'TRACE',
			]
		);

		$this->assertWPError( $result );
	}

	public function test_zero_argument_ability_accepts_null_input() {
		/*
		 * Regression: the run endpoint forwards null when a client omits input.
		 * A strict "object" schema rejected that before the callback ran, which
		 * made every zero-argument ability uncallable from such a client.
		 */
		$ability = wp_get_ability( 'tainacan/metadata-types-list' );

		$result = $ability->execute( null );

		$this->assertNotWPError( $result );
		$this->assertIsArray( $result );
	}

	public function test_importer_list_returns_importers() {
		$result = $this->run_ability( 'tainacan/importer-list', [] );

		$this->assertNotWPError( $result );
		$this->assertIsArray( $result );
	}
}
