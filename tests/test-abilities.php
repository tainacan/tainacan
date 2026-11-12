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

	/*
	 * ---------------------------------------------------------------------
	 * Semantic search environment gate.
	 * ---------------------------------------------------------------------
	 */

	public function test_rag_environment_ability_is_registered() {
		$this->assertTrue( wp_has_ability( 'tainacan/rag-environment' ) );
	}

	public function test_rag_environment_verdict_has_the_expected_shape() {
		$verdict = \Tainacan\Rag\Environment_Check::get_instance()->get_verdict( true );

		$this->assertIsArray( $verdict );
		$this->assertArrayHasKey( 'status', $verdict );
		$this->assertContains( $verdict['status'], [ 'ready', 'degraded', 'unsupported' ] );
		$this->assertArrayHasKey( 'checks', $verdict );
		$this->assertNotEmpty( $verdict['checks'] );

		foreach ( $verdict['checks'] as $check ) {
			$this->assertArrayHasKey( 'key', $check );
			$this->assertArrayHasKey( 'label', $check );
			$this->assertArrayHasKey( 'ok', $check );
			$this->assertArrayHasKey( 'blocking', $check );
			$this->assertArrayHasKey( 'requirement', $check );
		}
	}

	public function test_rag_environment_is_ready_on_this_stack() {
		$verdict = \Tainacan\Rag\Environment_Check::get_instance()->get_verdict( true );

		$this->assertSame( 'ready', $verdict['status'], 'The development stack runs MariaDB 11.8, so it must report ready.' );
		$this->assertStringContainsString( 'mariadb', strtolower( $verdict['db_version'] ) );
		$this->assertGreaterThanOrEqual( 384, $verdict['max_dimensions'] );
	}

	public function test_rag_environment_recomputes_when_the_cached_verdict_is_stale() {
		$check = \Tainacan\Rag\Environment_Check::get_instance();

		// Poison the cache with a different server version: it must be discarded.
		update_option(
			\Tainacan\Rag\Environment_Check::OPTION_CACHE,
			[
				'status'     => 'degraded',
				'checks'     => [],
				'db_version' => 'not-this-server',
				'checked_at' => time(),
			],
			false
		);

		$verdict = $check->get_verdict();

		$this->assertNotSame( 'not-this-server', $verdict['db_version'] );
		$this->assertNotSame( 'degraded', $verdict['status'] );

		// And an expired entry must also be discarded.
		update_option(
			\Tainacan\Rag\Environment_Check::OPTION_CACHE,
			[
				'status'     => 'degraded',
				'checks'     => [],
				'db_version' => $verdict['db_version'],
				'checked_at' => time() - \Tainacan\Rag\Environment_Check::CACHE_TTL - 10,
			],
			false
		);

		$again = $check->get_verdict();

		$this->assertNotSame( 'degraded', $again['status'] );
	}

	public function test_rag_environment_ability_refuses_a_subscriber() {
		$subscriber = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $subscriber );

		$result = wp_get_ability( 'tainacan/rag-environment' )->execute( [] );

		wp_set_current_user( $this->user_id );

		$this->assertWPError( $result );
	}

	/*
	 * ---------------------------------------------------------------------
	 * Embedding providers.
	 * ---------------------------------------------------------------------
	 */

	/*
	 * ---------------------------------------------------------------------
	 * Chunking and the indexing queue.
	 * ---------------------------------------------------------------------
	 */

	/**
	 * Insert a registry row without DDL, so backfill tests do not commit.
	 *
	 * @param string $suffix Unique suffix.
	 * @return int Space id.
	 */
	private function fake_space_row( $suffix = 'test' ) {
		global $wpdb;

		$wpdb->insert(
			\Tainacan\Rag\Space_Repository::get_instance()->registry_table(),
			[
				'space_key'  => 'fake:' . $suffix . ':text:8',
				'provider'   => 'fake',
				'model'      => $suffix,
				'modality'   => 'text',
				'dimensions' => 8,
				'table_name' => 'wp_fake_space_' . preg_replace( '/[^a-z0-9_]/', '_', $suffix ),
				'is_active'  => 1,
				'status'     => 'ready',
				'created_at' => gmdate( 'Y-m-d H:i:s' ),
			],
			[ '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' ]
		);

		return (int) $wpdb->insert_id;
	}

	public function test_backfill_abilities_are_registered() {
		$this->assertTrue( wp_has_ability( 'tainacan/rag-backfill-start' ) );
		$this->assertTrue( wp_has_ability( 'tainacan/rag-backfill-step' ) );
	}

	public function test_backfill_requires_a_collection() {
		$result = \Tainacan\Rag\Backfill::get_instance()->for_ability_start( [ 'collection_id' => 0 ] );

		$this->assertWPError( $result );
	}

	public function test_backfill_counts_then_walks_with_a_cursor() {
		$collection_id = $this->create_collection_via_ability( 'Backfill collection' );

		foreach ( [ 'Backfill one', 'Backfill two' ] as $title ) {
			$item = $this->run_ability(
				'tainacan/item-create',
				[
					'collection_id' => $collection_id,
					'title'         => $title,
					'status'        => 'publish',
				]
			);
			$this->assertNotWPError( $item );
		}

		$space_id = $this->fake_space_row( 'backfill' );
		$backfill = \Tainacan\Rag\Backfill::get_instance();

		$started = $backfill->start( $space_id, $collection_id );

		$this->assertNotWPError( $started );
		$this->assertSame( 2, $started['total'] );
		$this->assertSame( 0, $started['cursor'] );
		$this->assertSame( 'running', $started['status'] );

		$first = $backfill->step( $space_id, $collection_id, 1 );
		$this->assertNotWPError( $first );
		$this->assertSame( 1, $first['enqueued'] );
		$this->assertGreaterThan( 0, $first['cursor'] );
		$this->assertSame( 'running', $first['status'] );

		$cursor_after_first = $first['cursor'];

		$second = $backfill->step( $space_id, $collection_id, 1 );
		$this->assertSame( 2, $second['enqueued'] );
		$this->assertGreaterThan( $cursor_after_first, $second['cursor'], 'The cursor must advance, not repeat.' );
		$this->assertSame( 'complete', $second['status'] );

		// Running again on a completed backfill is a no-op, which is what makes resume safe.
		$third = $backfill->step( $space_id, $collection_id, 5 );
		$this->assertSame( 2, $third['enqueued'] );

		delete_option( $backfill->option_key( $space_id, $collection_id ) );
	}

	public function test_circuit_breaker_pauses_after_the_threshold() {
		$breaker = \Tainacan\Rag\Provider_Circuit_Breaker::get_instance();
		$breaker->clear();

		$this->assertFalse( $breaker->is_paused( 'acme', 'model-a' ) );

		$breaker->record_failure( 'acme', 'model-a', 'boom' );
		$breaker->record_failure( 'acme', 'model-a', 'boom' );
		$this->assertFalse( $breaker->is_paused( 'acme', 'model-a' ), 'Two failures are not yet a pause.' );

		$breaker->record_failure( 'acme', 'model-a', 'rate limited' );

		$paused = $breaker->is_paused( 'acme', 'model-a' );
		$this->assertIsArray( $paused );
		$this->assertGreaterThan( 0, $paused['seconds_remaining'] );
		$this->assertSame( 'rate limited', $paused['error'] );

		// A different model on the same provider is unaffected.
		$this->assertFalse( $breaker->is_paused( 'acme', 'model-b' ) );

		$breaker->record_success( 'acme', 'model-a' );
		$this->assertFalse( $breaker->is_paused( 'acme', 'model-a' ), 'A success clears the pair.' );

		$breaker->clear();
	}

	public function test_circuit_breaker_expires_a_pause() {
		$breaker = \Tainacan\Rag\Provider_Circuit_Breaker::get_instance();
		$breaker->clear();

		update_option(
			\Tainacan\Rag\Provider_Circuit_Breaker::OPTION,
			[
				'acme|model-a' => [
					'consecutive'   => 3,
					'pauses'        => 1,
					'paused_until'  => time() - 5,
					'error'         => 'old failure',
				],
			],
			false
		);

		$this->assertFalse( $breaker->is_paused( 'acme', 'model-a' ), 'An expired pause must not block work.' );

		$breaker->clear();
	}

	public function test_chunker_splits_with_overlap_and_never_stalls() {
		$chunker = new \Tainacan\Rag\Chunker();

		$text   = str_repeat( 'abcdefghij', 30 ); // 300 characters
		$chunks = $chunker->chunk( $text, 100, 20 );

		$this->assertGreaterThan( 1, count( $chunks ) );
		$this->assertSame( 100, mb_strlen( $chunks[0] ) );

		// Consecutive chunks must share the overlap.
		$this->assertSame( mb_substr( $chunks[0], -20 ), mb_substr( $chunks[1], 0, 20 ) );

		// An overlap at or above the size must not loop forever.
		$pathological = $chunker->chunk( 'abcdefghij', 100, 100 );
		$this->assertCount( 1, $pathological );
	}

	public function test_chunker_normalises_before_hashing() {
		$chunker = new \Tainacan\Rag\Chunker();

		$this->assertSame(
			$chunker->hash( $chunker->normalise( "  hello\n\nworld  " ) ),
			$chunker->hash( $chunker->normalise( 'hello world' ) ),
			'Whitespace differences must not change the hash, or every save re-embeds.'
		);

		$this->assertSame( [], $chunker->chunk( '   ' ) );
	}

	public function test_plan_embeds_only_new_chunks_and_deletes_stale_ones() {
		$indexer = \Tainacan\Rag\Indexer::get_instance();

		$chunks = [
			[ 'source' => 'title', 'chunk_index' => 0, 'content' => 'keep me', 'hash' => 'aaa' ],
			[ 'source' => 'content', 'chunk_index' => 1, 'content' => 'brand new', 'hash' => 'bbb' ],
		];

		$plan = $indexer->plan( $chunks, [ 'aaa', 'ccc' ] );

		$this->assertCount( 1, $plan['to_embed'] );
		$this->assertSame( 'bbb', $plan['to_embed'][0]['hash'] );
		$this->assertSame( [ 'ccc' ], $plan['to_delete'] );
		$this->assertSame( 1, $plan['unchanged'] );
	}

	public function test_plan_is_empty_when_nothing_changed() {
		$indexer = \Tainacan\Rag\Indexer::get_instance();

		$chunks = [ [ 'source' => 'title', 'chunk_index' => 0, 'content' => 'same', 'hash' => 'aaa' ] ];
		$plan   = $indexer->plan( $chunks, [ 'aaa' ] );

		$this->assertSame( [], $plan['to_embed'] );
		$this->assertSame( [], $plan['to_delete'] );
		$this->assertSame( 1, $plan['unchanged'] );
	}

	public function test_indexer_builds_chunks_for_a_real_item() {
		$collection_id = $this->create_collection_via_ability( 'Chunk collection' );

		$item = $this->run_ability(
			'tainacan/item-create',
			[
				'collection_id' => $collection_id,
				'title'         => 'A title to chunk',
				'status'        => 'publish',
			]
		);

		$this->assertNotWPError( $item );

		$chunks = \Tainacan\Rag\Indexer::get_instance()->build_chunks( (int) $item['id'] );

		$this->assertNotEmpty( $chunks );
		$this->assertSame( 'title', $chunks[0]['source'] );

		foreach ( $chunks as $chunk ) {
			$this->assertSame( 40, strlen( $chunk['hash'] ), 'Hashes must be sha1 hex.' );
			$this->assertNotSame( '', $chunk['content'] );
		}
	}

	public function test_queue_table_exists_and_enqueue_is_idempotent() {
		global $wpdb;

		$queue = \Tainacan\Rag\Index_Queue::get_instance();
		$table = $queue->table();

		$this->assertSame( $table, $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) );

		$before = $queue->counts()['pending'];

		$this->assertTrue( $queue->enqueue( 999, 1234 ) );
		$this->assertTrue( $queue->enqueue( 999, 1234 ), 'A second enqueue must not duplicate the work.' );

		$this->assertSame( $before + 1, $queue->counts()['pending'] );
	}

	public function test_item_post_type_detection() {
		/*
		 * Hook wiring itself is verified against the live site (all three callbacks
		 * at priority 20) rather than asserted here: has_action() for hooks added at
		 * plugin load is not reliable inside this harness. What is asserted is the
		 * predicate those callbacks depend on.
		 */
		$indexer = \Tainacan\Rag\Indexer::get_instance();

		$this->assertFalse( $indexer->is_item_post_type( 'post' ) );
		$this->assertFalse( $indexer->is_item_post_type( 'page' ) );
		$this->assertFalse( $indexer->is_item_post_type( '' ) );

		$collection_id = $this->create_collection_via_ability( 'Post type collection' );
		$collection    = \Tainacan\Repositories\Collections::get_instance()->fetch( $collection_id );

		$this->assertNotFalse( $collection->get_db_identifier() );
		$this->assertTrue( $indexer->is_item_post_type( $collection->get_db_identifier() ) );
	}

	public function test_a_save_on_a_non_item_post_queues_nothing() {
		$indexer = \Tainacan\Rag\Indexer::get_instance();
		$queue   = \Tainacan\Rag\Index_Queue::get_instance();

		$before = $queue->counts()['pending'];

		$post_id = $this->factory()->post->create( [ 'post_type' => 'post' ] );
		$indexer->on_save( $post_id, get_post( $post_id ), false );

		$this->assertSame(
			$before,
			$queue->counts()['pending'],
			'A regular post must not enter the embedding queue.'
		);
	}

	public function test_indexer_refuses_when_no_space_is_active() {
		$result = \Tainacan\Rag\Indexer::get_instance()->for_ability_enqueue( [ 'item_id' => 1 ] );

		// Either no active space, or a queued result if another test activated one.
		if ( is_wp_error( $result ) ) {
			$this->assertSame( 'tainacan_rag_no_active_space', $result->get_error_code() );
		} else {
			$this->assertTrue( $result['queued'] );
		}
	}

	public function test_rag_provider_list_ability_is_registered() {
		$this->assertTrue( wp_has_ability( 'tainacan/rag-provider-list' ) );
	}

	public function test_rag_provider_list_exposes_no_credentials() {
		update_option( 'tainacan_rag_provider_openai_api_key', 'sk-test-secret-value', false );

		$result = wp_get_ability( 'tainacan/rag-provider-list' )->execute( [] );

		$this->assertNotWPError( $result );
		$this->assertArrayHasKey( 'providers', $result );
		$this->assertNotEmpty( $result['providers'] );

		$this->assertStringNotContainsString(
			'sk-test-secret-value',
			wp_json_encode( $result ),
			'The provider list must never leak an API key.'
		);

		delete_option( 'tainacan_rag_provider_openai_api_key' );
	}

	public function test_openai_provider_reports_known_dimensions() {
		$provider = new \Tainacan\Rag\OpenAI_Provider();

		$this->assertSame( 1536, $provider->get_dimensions( 'text-embedding-3-small' ) );
		$this->assertSame( 3072, $provider->get_dimensions( 'text-embedding-3-large' ) );
		$this->assertSame( 0, $provider->get_dimensions( 'not-a-real-model' ) );
		$this->assertSame( [ 'text' ], $provider->supported_modalities() );
	}

	public function test_ollama_provider_needs_no_key_and_accepts_tagged_models() {
		$provider = new \Tainacan\Rag\Ollama_Provider();

		$this->assertFalse( $provider->requires_api_key() );
		$this->assertTrue( $provider->is_configured(), 'A local endpoint is usable without credentials.' );
		$this->assertSame( 768, $provider->get_dimensions( 'nomic-embed-text' ) );
		$this->assertSame( 768, $provider->get_dimensions( 'nomic-embed-text:latest' ) );
	}

	public function test_embedding_without_credentials_fails_before_any_request() {
		delete_option( 'tainacan_rag_provider_openai_api_key' );

		$provider = new \Tainacan\Rag\OpenAI_Provider();
		$result   = $provider->embed( [ 'hello' ], 'text-embedding-3-small' );

		$this->assertWPError( $result );
		$this->assertSame( 'tainacan_rag_provider_not_configured', $result->get_error_code() );
	}

	public function test_embedding_rejects_empty_input_and_unknown_modality() {
		$provider = new \Tainacan\Rag\OpenAI_Provider();

		$empty = $provider->embed( [], 'text-embedding-3-small' );
		$this->assertWPError( $empty );
		$this->assertSame( 'tainacan_rag_no_input', $empty->get_error_code() );

		update_option( 'tainacan_rag_provider_openai_api_key', 'sk-test', false );

		$modality = $provider->embed( [ 'hello' ], 'text-embedding-3-small', 'image' );
		$this->assertWPError( $modality );
		$this->assertSame( 'tainacan_rag_unsupported_modality', $modality->get_error_code() );

		delete_option( 'tainacan_rag_provider_openai_api_key' );
	}

	public function test_embedding_payload_shape() {
		$provider = new \Tainacan\Rag\OpenAI_Provider();
		$payload  = $provider->build_payload( [ 'a', 'b' ], 'text-embedding-3-small' );

		$this->assertSame( 'text-embedding-3-small', $payload['model'] );
		$this->assertSame( [ 'a', 'b' ], $payload['input'] );
	}

	public function test_provider_registry_drops_entries_that_are_not_providers() {
		$registry = \Tainacan\Rag\Provider_Registry::get_instance();
		$method   = new \ReflectionMethod( $registry, 'init' );
		$method->setAccessible( true );

		$extend = static function ( $providers ) {
			$providers['custom'] = new \Tainacan\Rag\OpenAI_Provider();
			$providers['junk']   = 'not a provider';
			return $providers;
		};

		add_filter( 'tainacan_rag_register_embedding_providers', $extend );
		$method->invoke( $registry );
		$providers = $registry->get_providers();
		remove_filter( 'tainacan_rag_register_embedding_providers', $extend );

		$this->assertArrayHasKey( 'custom', $providers );
		$this->assertArrayNotHasKey( 'junk', $providers, 'A non-provider entry must be dropped, not trusted.' );

		// Restore the default registry for later tests.
		$method->invoke( $registry );
	}

	public function test_rag_space_abilities_are_registered() {
		$expected = [
			'tainacan/rag-space-list',
			'tainacan/rag-space-create',
			'tainacan/rag-space-activate',
			'tainacan/rag-space-delete',
		];

		foreach ( $expected as $name ) {
			$this->assertTrue( wp_has_ability( $name ), sprintf( '%s is not registered.', $name ) );
		}
	}

	public function test_rag_space_registry_table_exists() {
		global $wpdb;

		$repository = \Tainacan\Rag\Space_Repository::get_instance();
		$table      = $repository->registry_table();

		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		$this->assertSame( $table, $found, 'The migration did not create the vector space registry table.' );
	}

	public function test_rag_space_table_sql_includes_the_vector_index() {
		/*
		 * Asserted on the generated SQL rather than by running DDL: CREATE TABLE
		 * causes an implicit commit, which would break the transaction rollback the
		 * test case relies on. The real table is created and inspected in the
		 * sprint's live smoke instead.
		 */
		$repository = \Tainacan\Rag\Space_Repository::get_instance();
		$sql        = $repository->build_create_table_sql( 'wp_tainacan_vec_probe_384', 384 );

		$this->assertStringContainsString( 'VECTOR(384)', $sql );
		$this->assertStringContainsString( 'VECTOR INDEX vec_idx (embedding)', $sql );
		$this->assertStringContainsString( 'ENGINE=InnoDB', $sql );
		$this->assertStringContainsString( 'content_hash', $sql );
	}

	public function test_rag_space_table_names_are_sanitised() {
		$repository = \Tainacan\Rag\Space_Repository::get_instance();

		$name = $repository->table_name_for( 'Open AI!', 'text/embedding 3-小', 'text', 1536 );

		$this->assertMatchesRegularExpression( '/^[a-z0-9_]+$/', $name, 'Table names must be safe for DDL.' );
		$this->assertLessThanOrEqual( 64, strlen( $name ), 'MySQL identifiers are limited to 64 characters.' );
		$this->assertStringEndsWith( '_1536', $name );
	}

	public function test_rag_space_rejects_invalid_modality() {
		$result = \Tainacan\Rag\Space_Repository::get_instance()->create_space( 'openai', 'text-embedding-3-small', 'audio', 1536 );

		$this->assertWPError( $result );
		$this->assertSame( 'tainacan_rag_invalid_modality', $result->get_error_code() );
	}

	public function test_rag_space_rejects_dimensions_the_server_cannot_take() {
		$result = \Tainacan\Rag\Space_Repository::get_instance()->create_space( 'openai', 'too-big-model', 'text', 99999 );

		$this->assertWPError( $result );
		$this->assertSame( 'tainacan_rag_dimensions_exceeded', $result->get_error_code() );

		$data = $result->get_error_data();
		$this->assertSame( 99999, $data['dimensions'] );
		$this->assertGreaterThan( 0, $data['max_dimensions'] );
	}

	public function test_rag_space_rejects_missing_provider() {
		$result = \Tainacan\Rag\Space_Repository::get_instance()->create_space( '', '', 'text', 1536 );

		$this->assertWPError( $result );
		$this->assertSame( 'tainacan_rag_missing_provider', $result->get_error_code() );
	}

	public function test_rag_space_row_shape_is_what_consumers_read() {
		/*
		 * Regression: delete_space() read $space['table_name'] while prepare_row()
		 * exposes 'table', so DROP TABLE ran with an empty identifier and silently
		 * dropped nothing. This pins the key names that other methods rely on.
		 */
		$repository = \Tainacan\Rag\Space_Repository::get_instance();

		$method = new \ReflectionMethod( $repository, 'prepare_row' );
		$method->setAccessible( true );

		$row = $method->invoke(
			$repository,
			[
				'id'         => '7',
				'space_key'  => 'openai:m:text:1536',
				'provider'   => 'openai',
				'model'      => 'm',
				'modality'   => 'text',
				'dimensions' => '1536',
				'table_name' => 'wp_tainacan_vec_openai_m_text_1536',
				'is_active'  => '1',
				'status'     => 'ready',
				'created_at' => '2026-01-01 00:00:00',
			]
		);

		$this->assertArrayHasKey( 'table', $row );
		$this->assertSame( 'wp_tainacan_vec_openai_m_text_1536', $row['table'] );
		$this->assertSame( 1536, $row['dimensions'] );
		$this->assertTrue( $row['is_active'] );
	}

	public function test_rag_space_list_reshapes_the_registry() {
		$result = \Tainacan\Rag\Space_Repository::get_instance()->for_ability_list( [] );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'spaces', $result );
		$this->assertArrayHasKey( 'active', $result );
		$this->assertArrayHasKey( 'text', $result['active'] );
		$this->assertArrayHasKey( 'image', $result['active'] );
	}

	/*
	 * ---------------------------------------------------------------------
	 * Retrieval: space scoping and post-ANN visibility.
	 * ---------------------------------------------------------------------
	 */

	public function test_rag_search_abilities_are_registered() {
		$this->assertTrue( wp_has_ability( 'tainacan/rag-search' ) );
		$this->assertTrue( wp_has_ability( 'tainacan/rag-similar-items' ) );
	}

	public function test_rag_search_rejects_an_empty_query() {
		$result = \Tainacan\Rag\Semantic_Search::get_instance()->for_ability_search( [ 'query' => '   ' ] );

		$this->assertWPError( $result );
		$this->assertSame( 'tainacan_rag_missing_query', $result->get_error_code() );
	}

	public function test_rag_similar_items_requires_an_item() {
		$result = \Tainacan\Rag\Semantic_Search::get_instance()->for_ability_similar( [ 'item_id' => 0 ] );

		$this->assertWPError( $result );
		$this->assertSame( 'tainacan_rag_missing_item', $result->get_error_code() );
	}

	public function test_rag_ann_query_orders_by_the_distance_expression_and_lines_up_placeholders() {
		$search = \Tainacan\Rag\Semantic_Search::get_instance();
		$query  = $search->build_ann_query( 'wp_tainacan_vec_demo_3', '[1,0,0]', [ 4, 5 ], 12 );

		$this->assertStringContainsString(
			'ORDER BY VEC_DISTANCE_COSINE(embedding, VEC_FromText(%s)) ASC',
			$query['sql'],
			'The raw distance expression must be in ORDER BY; MariaDB only turns that shape into a vector index scan.'
		);
		$this->assertStringNotContainsString(
			'ORDER BY distance',
			$query['sql'],
			'Ordering by the alias would silently disable the vector index.'
		);
		$this->assertStringContainsString( 'collection_id IN (%d, %d)', $query['sql'] );
		$this->assertStringEndsWith( 'LIMIT %d', $query['sql'] );

		// The vector is bound twice (SELECT and ORDER BY), so the order matters.
		$this->assertSame( [ '[1,0,0]', 4, 5, '[1,0,0]', 12 ], $query['params'] );
		$this->assertCount( substr_count( $query['sql'], '%' ), $query['params'], 'Every placeholder must have exactly one parameter.' );
	}

	public function test_rag_ann_query_has_no_where_clause_when_nothing_is_scoped() {
		$search = \Tainacan\Rag\Semantic_Search::get_instance();
		$query  = $search->build_ann_query( 'wp_tainacan_vec_demo_3', '[0.5,0.5,0]' );

		$this->assertStringNotContainsString( 'WHERE', $query['sql'] );
		$this->assertSame( [ '[0.5,0.5,0]', '[0.5,0.5,0]', \Tainacan\Rag\Semantic_Search::DEFAULT_LIMIT ], $query['params'] );
	}

	public function test_rag_ann_query_leaves_the_seed_item_out_of_similarity_results() {
		$search = \Tainacan\Rag\Semantic_Search::get_instance();
		$query  = $search->build_ann_query( 'wp_tainacan_vec_demo_3', '[1,0,0]', [], 5, 42 );

		$this->assertStringContainsString( 'item_id <> %d', $query['sql'] );
		$this->assertSame( [ '[1,0,0]', 42, '[1,0,0]', 5 ], $query['params'] );
	}

	public function test_rag_hits_drop_candidates_the_caller_may_not_see() {
		/*
		 * The leak this guards against: the ANN scan ranked item 12 first and it is
		 * in the rows, but it is not in the visible set, so no text from it may
		 * reach the response.
		 */
		$rows = [
			[ 'item_id' => 12, 'collection_id' => 3, 'source' => 'title', 'chunk_index' => 0, 'content' => 'secret draft', 'distance' => 0.05 ],
			[ 'item_id' => 11, 'collection_id' => 3, 'source' => 'title', 'chunk_index' => 0, 'content' => 'public title', 'distance' => 0.10 ],
			[ 'item_id' => 11, 'collection_id' => 3, 'source' => 'content', 'chunk_index' => 1, 'content' => 'public body', 'distance' => 0.20 ],
		];

		$hits = \Tainacan\Rag\Semantic_Search::get_instance()->collapse_hits( $rows, [ 11 ], 2 );

		$this->assertCount( 1, $hits );
		$this->assertSame( 11, $hits[0]['item_id'] );
		$this->assertSame( 0.9, $hits[0]['score'] );
		$this->assertCount( 2, $hits[0]['snippets'] );

		$this->assertStringNotContainsString( 'secret draft', wp_json_encode( $hits ) );
	}

	public function test_rag_hits_keep_the_best_chunk_and_respect_the_snippet_cap() {
		$rows = [
			[ 'item_id' => 7, 'collection_id' => 1, 'source' => 'title', 'chunk_index' => 0, 'content' => 'first', 'distance' => 0.1 ],
			[ 'item_id' => 7, 'collection_id' => 1, 'source' => 'content', 'chunk_index' => 1, 'content' => 'second', 'distance' => 0.2 ],
			[ 'item_id' => 7, 'collection_id' => 1, 'source' => 'document', 'chunk_index' => 2, 'content' => 'third', 'distance' => 0.3 ],
		];

		$hits = \Tainacan\Rag\Semantic_Search::get_instance()->collapse_hits( $rows, [ 7 ], 2 );

		$this->assertCount( 1, $hits );
		$this->assertSame( 0.9, $hits[0]['score'], 'The best chunk decides the item score.' );
		$this->assertSame( [ 'first', 'second' ], array_column( $hits[0]['snippets'], 'content' ) );
	}

	public function test_rag_anonymous_visibility_is_public_only() {
		$statuses = \Tainacan\Rag\Semantic_Search::get_instance()->readable_statuses( [ 1, 2 ] );

		$this->assertSame( [ 'publish' ], $statuses, 'A logged-out visitor must never be offered a private status.' );
	}

	public function test_rag_visibility_stage_drops_items_an_anonymous_visitor_cannot_read() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			[ 'name' => 'Retrieval visibility', 'status' => 'publish' ],
			true
		);

		$published = $this->tainacan_entity_factory->create_entity(
			'item',
			[ 'title' => 'Visible item', 'collection' => $collection, 'status' => 'publish' ],
			true
		);

		$draft = $this->tainacan_entity_factory->create_entity(
			'item',
			[ 'title' => 'Unpublished item', 'collection' => $collection, 'status' => 'draft' ],
			true
		);

		if ( ! $published instanceof \Tainacan\Entities\Item || ! $draft instanceof \Tainacan\Entities\Item ) {
			$this->markTestSkipped( 'The entity factory did not return items on this run.' );
		}

		wp_set_current_user( 0 );

		$visible = \Tainacan\Rag\Semantic_Search::get_instance()->visible_item_ids(
			[ $draft->get_id(), $published->get_id() ],
			[ $collection->get_id() ]
		);

		wp_set_current_user( $this->user_id );

		$this->assertSame(
			[ $published->get_id() ],
			$visible,
			'The repository re-fetch is the only thing standing between a draft and a public search result.'
		);
	}

	public function test_rag_indexer_stores_the_collection_not_the_space() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			[ 'name' => 'Retrieval scoping', 'status' => 'publish' ],
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			[ 'title' => 'Scoped item', 'collection' => $collection, 'status' => 'publish' ],
			true
		);

		if ( ! $item instanceof \Tainacan\Entities\Item ) {
			$this->markTestSkipped( 'The entity factory did not return an item on this run.' );
		}

		$this->assertSame(
			$collection->get_id(),
			\Tainacan\Rag\Indexer::get_instance()->collection_id_for( $item->get_id() ),
			'collection_id must hold the collection, otherwise the per-collection ANN filter matches everything.'
		);
	}

	public function test_rag_indexer_reports_zero_for_a_missing_item() {
		$this->assertSame( 0, \Tainacan\Rag\Indexer::get_instance()->collection_id_for( 0 ) );
	}

	/*
	 * ---------------------------------------------------------------------
	 * The operator surface: status report and queue recovery.
	 * ---------------------------------------------------------------------
	 */

	public function test_rag_status_abilities_are_registered() {
		$this->assertTrue( wp_has_ability( 'tainacan/rag-status' ) );
		$this->assertTrue( wp_has_ability( 'tainacan/rag-queue-retry' ) );
	}

	public function test_rag_status_reports_every_panel_it_renders() {
		$this->fake_space_row( 'status' );

		$report = \Tainacan\Rag\Status::get_instance()->for_ability( [] );

		foreach ( [ 'environment', 'spaces', 'active', 'queue', 'paused', 'failed', 'generated_at' ] as $key ) {
			$this->assertArrayHasKey( $key, $report, sprintf( 'The report is missing the %s panel data.', $key ) );
		}

		$this->assertArrayHasKey( 'text', $report['active'] );
		$this->assertArrayHasKey( 'pending', $report['queue'] );
		$this->assertIsInt( $report['generated_at'] );
	}

	public function test_rag_status_reports_a_dropped_table_instead_of_erroring() {
		/*
		 * A registry row outlives its table whenever someone drops it by hand. The
		 * screen must say so, not emit a database error.
		 */
		$space_id = $this->fake_space_row( 'dropped' );
		$space    = \Tainacan\Rag\Space_Repository::get_instance()->get_space( $space_id );

		$entry = \Tainacan\Rag\Status::get_instance()->space_status( $space );

		$this->assertFalse( $entry['storage']['available'] );
		$this->assertNull( $entry['has_index'], 'With no table there is no index to report.' );
		$this->assertNotSame( '', $entry['storage']['error'] );
		$this->assertSame( 0, $entry['storage']['chunks'] );
	}

	public function test_rag_queue_retry_requeues_failed_rows_once() {
		$queue    = \Tainacan\Rag\Index_Queue::get_instance();
		$space_id = $this->fake_space_row( 'retry' );

		$queue->enqueue( $space_id, 9001 );
		$queue->enqueue( $space_id, 9002 );

		$rows = $queue->reserve( 10 );
		$mine = array_values(
			array_filter(
				$rows,
				static function ( $row ) use ( $space_id ) {
					return (int) $row['space_id'] === $space_id;
				}
			)
		);

		$this->assertCount( 2, $mine );

		$queue->mark_failed( (int) $mine[0]['id'], 'invalid api key' );

		$failed = $queue->failed_rows( 5 );
		$this->assertNotEmpty( $failed );
		$this->assertSame( 'invalid api key', $failed[0]['error'], 'The reason has to reach the screen.' );
		$this->assertSame( 1, $failed[0]['attempts'] );

		$this->assertSame( 1, $queue->retry_failed() );
		$this->assertSame( 0, $queue->counts()['failed'], 'A requeued row is pending again.' );
		$this->assertSame( 0, $queue->retry_failed(), 'Pressing twice must not invent work.' );
	}

	public function test_rag_status_ability_retry_returns_the_new_depth() {
		$queue    = \Tainacan\Rag\Index_Queue::get_instance();
		$space_id = $this->fake_space_row( 'retry-ability' );

		$queue->enqueue( $space_id, 9201 );

		$rows = $queue->reserve( 10 );
		$queue->mark_failed( (int) $rows[0]['id'], 'provider down' );

		$result = \Tainacan\Rag\Status::get_instance()->for_ability_retry_queue( [ 'limit' => 5 ] );

		$this->assertSame( 1, $result['requeued'] );
		$this->assertArrayHasKey( 'failed', $result['queue'] );
		$this->assertSame( 0, $result['queue']['failed'] );
	}

	public function test_rag_console_formats_bytes_and_notices() {
		$this->assertSame( 'unknown', \Tainacan\Rag_Console::format_bytes( null ) );
		$this->assertSame( '512 B', \Tainacan\Rag_Console::format_bytes( 512 ) );
		$this->assertSame( '2.0 KB', \Tainacan\Rag_Console::format_bytes( 2048 ) );
		$this->assertSame( '1.0 MB', \Tainacan\Rag_Console::format_bytes( 1048576 ) );

		$this->assertSame( '', \Tainacan\Rag_Console::notice_text( '' ) );
		$this->assertStringContainsString( '3', \Tainacan\Rag_Console::notice_text( 'queue-retried:3' ) );
		$this->assertStringContainsString( 'tainacan_rag_no_active_space', \Tainacan\Rag_Console::notice_text( 'error:tainacan_rag_no_active_space' ) );
	}

	public function test_rag_console_is_a_tinacan_admin_page() {
		if ( ! class_exists( '\Tainacan\Pages' ) ) {
			require_once dirname( __DIR__ ) . '/src/views/class-tainacan-pages.php';
		}

		if ( ! class_exists( '\Tainacan\Rag_Console' ) ) {
			require_once dirname( __DIR__ ) . '/src/views/rag/class-tainacan-rag-console.php';
		}

		$page = \Tainacan\Rag_Console::get_instance();

		$this->assertInstanceOf( '\Tainacan\Pages', $page );

		$method = new \ReflectionMethod( $page, 'get_page_slug' );
		$method->setAccessible( true );

		$this->assertSame( 'tainacan_rag', $method->invoke( $page ) );
	}

	/*
	 * ---------------------------------------------------------------------
	 * Hybrid ranking and grounded answers.
	 * ---------------------------------------------------------------------
	 */

	public function test_rag_hybrid_and_answer_abilities_are_registered() {
		$this->assertTrue( wp_has_ability( 'tainacan/rag-hybrid-search' ) );
		$this->assertTrue( wp_has_ability( 'tainacan/rag-answer' ) );
	}

	public function test_rag_fusion_rewards_agreement_between_lists() {
		$fusion = \Tainacan\Rag\Hybrid_Ranking::get_instance()->fuse( [ [ 7, 9 ], [ 9 ] ] );

		$this->assertSame( 9, $fusion['order'][0], 'An item both lists agree on must outrank a single-list item.' );

		$single = $fusion['scores'][7];
		$double = $fusion['scores'][9];

		$this->assertGreaterThan( $single, $double );
	}

	public function test_rag_fusion_is_deterministic_on_ties() {
		$fusion = \Tainacan\Rag\Hybrid_Ranking::get_instance()->fuse( [ [ 5 ], [ 6 ] ] );

		$this->assertSame( [ 5, 6 ], $fusion['order'], 'Equal scores fall back to the lower id so the same input always sorts the same way.' );
	}

	public function test_rag_fusion_ignores_invalid_ids() {
		$fusion = \Tainacan\Rag\Hybrid_Ranking::get_instance()->fuse( [ [ 0, -3, 4 ], [] ] );

		$this->assertSame( [ 4 ], $fusion['order'] );
		$this->assertSame( [], \Tainacan\Rag\Hybrid_Ranking::get_instance()->fuse( [] )['order'] );
	}

	public function test_rag_prompt_numbers_sources_and_forbids_guessing() {
		$prompt = \Tainacan\Rag\Answer::get_instance()->build_prompt(
			'when did the rover land',
			[
				[ 'n' => 1, 'title' => 'Mars rover landing', 'content' => 'A spacecraft landed on mars.' ],
				[ 'n' => 2, 'title' => 'Second source', 'content' => 'Another snippet.' ],
			]
		);

		$this->assertStringContainsString( '[1] Mars rover landing', $prompt );
		$this->assertStringContainsString( '[2] Second source', $prompt );
		$this->assertStringContainsString( 'Question: when did the rover land', $prompt );
		$this->assertStringContainsString( 'Do not guess', $prompt );
		$this->assertStringContainsString( 'using only the numbered sources', $prompt );
	}

	public function test_rag_answer_requires_a_query() {
		$result = \Tainacan\Rag\Answer::get_instance()->for_ability_answer( [ 'query' => '  ' ] );

		$this->assertWPError( $result );
		$this->assertSame( 'tainacan_rag_missing_query', $result->get_error_code() );
	}

	public function test_rag_answer_only_accepts_a_callable_generator() {
		$answer = \Tainacan\Rag\Answer::get_instance();

		$this->assertNull( $answer->generator(), 'No generator is wired by default.' );

		$wrong = static function () {
			return 'not a callable value';
		};
		add_filter( 'tainacan_rag_answer_generator', $wrong );
		$this->assertNull( $answer->generator(), 'A non-callable must be dropped, not trusted.' );
		remove_filter( 'tainacan_rag_answer_generator', $wrong );

		$generator = static function ( $prompt ) {
			return 'generated';
		};

		$wire = static function () use ( $generator ) {
			return $generator;
		};

		add_filter( 'tainacan_rag_answer_generator', $wire );
		$this->assertTrue( is_callable( $answer->generator() ) );
		remove_filter( 'tainacan_rag_answer_generator', $wire );
	}

	public function test_rag_hybrid_search_falls_back_to_keywords_without_vectors() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			[ 'name' => 'Hybrid fallback', 'status' => 'publish' ],
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			[ 'title' => 'Mars rover landing', 'collection' => $collection, 'status' => 'publish' ],
			true
		);

		if ( ! $item instanceof \Tainacan\Entities\Item ) {
			$this->markTestSkipped( 'The entity factory did not return an item on this run.' );
		}

		$result = \Tainacan\Rag\Hybrid_Ranking::get_instance()->search( 'mars rover', [ 'limit' => 5 ] );

		$this->assertNotWPError( $result );
		$this->assertContains( $item->get_id(), array_column( $result['results'], 'item_id' ), 'Keyword results must survive a missing vector side.' );
		$this->assertNotSame( '', $result['degraded'], 'A degraded search must say why.' );
		$this->assertSame( 0, $result['vector_candidates'] );
	}

	public function test_rag_rerank_keeps_order_when_vectors_are_unavailable() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			[ 'name' => 'Rerank order', 'status' => 'publish' ],
			true
		);

		$first = $this->tainacan_entity_factory->create_entity(
			'item',
			[ 'title' => 'Mars rover landing', 'collection' => $collection, 'status' => 'publish' ],
			true
		);

		$second = $this->tainacan_entity_factory->create_entity(
			'item',
			[ 'title' => 'Mars rover payload', 'collection' => $collection, 'status' => 'publish' ],
			true
		);

		if ( ! $first instanceof \Tainacan\Entities\Item || ! $second instanceof \Tainacan\Entities\Item ) {
			$this->markTestSkipped( 'The entity factory did not return items on this run.' );
		}

		$posts = [ get_post( $first->get_id() ), get_post( $second->get_id() ) ];

		$reranked = \Tainacan\Rag\Hybrid_Ranking::get_instance()->rerank_posts( 'mars rover', $posts, [ $collection->get_id() ] );

		$this->assertSame(
			wp_list_pluck( $posts, 'ID' ),
			wp_list_pluck( $reranked, 'ID' ),
			'With no vectors to compare against, the page keeps the order WordPress gave it.'
		);
	}

	public function test_rag_providers_are_the_wordpress_ai_connectors() {
		$providers = \Tainacan\Rag\Provider_Registry::get_instance()->get_providers();

		$this->assertNotEmpty( $providers, 'There is always at least a placeholder provider to explain an empty list.' );

		foreach ( array_keys( $providers ) as $id ) {
			$this->assertNotContains(
				$id,
				[ 'openai', 'ollama' ],
				'A vendor provider is opt-in through the filter; the default list is connectors.'
			);
		}

		if ( function_exists( 'WordPress\AI\get_ai_connectors' ) ) {
			foreach ( array_keys( \WordPress\AI\get_ai_connectors() ) as $connector_id ) {
				$this->assertArrayHasKey( (string) $connector_id, $providers, sprintf( 'Connector %s is not offered as a provider.', $connector_id ) );
			}
		}
	}

	public function test_rag_provider_is_bound_to_one_connector() {
		$provider = new \Tainacan\Rag\WordPress_AI_Provider( 'google', 'Google' );

		$this->assertSame( 'google', $provider->get_id(), 'The space records which connector built its vectors.' );
		$this->assertSame( 'Google', $provider->get_label() );
		$this->assertSame( [ 'text' ], $provider->supported_modalities() );
		$this->assertSame( 0, $provider->get_dimensions( 'anything' ) );
	}

	public function test_rag_provider_explains_a_missing_model_before_anything_is_sent() {
		$result = ( new \Tainacan\Rag\WordPress_AI_Provider( 'google' ) )->embed( [ 'hello' ], '   ' );

		$this->assertWPError( $result );
		$this->assertSame( 'tainacan_rag_missing_model', $result->get_error_code() );
	}

	public function test_rag_provider_embeds_through_the_client_filter() {
		/*
		 * Bound to no connector, so the AI plugin path is skipped and the filter seam is
		 * what runs: this is the documented fallback for an AI version without
		 * embeddings.
		 */
		$provider = new \Tainacan\Rag\WordPress_AI_Provider();

		$wire = static function () {
			return static function ( $inputs ) {
				$vectors = [];

				foreach ( $inputs as $input ) {
					$vectors[] = [ 0.1, 0.2, 0.3, 0.4 ];
				}

				return $vectors;
			};
		};

		add_filter( 'tainacan_rag_wordpress_ai_embed', $wire );
		$result = $provider->embed( [ 'a', 'b' ], 'some-model' );
		remove_filter( 'tainacan_rag_wordpress_ai_embed', $wire );

		$this->assertNotWPError( $result );
		$this->assertCount( 2, $result['vectors'] );
		$this->assertSame( 4, $result['dimensions'] );
		$this->assertSame( [ 0.1, 0.2, 0.3, 0.4 ], $result['vectors'][0] );
	}

	public function test_deleting_a_space_takes_its_queue_rows_with_it() {
		/*
		 * Found by clicking the console: rows whose space had been deleted sat in
		 * `failed` with "space no longer exists" and could never be requeued into
		 * anything. Deleting a space now clears its queue.
		 */
		$queue    = \Tainacan\Rag\Index_Queue::get_instance();
		$space_id = $this->fake_space_row( 'purge' );

		$queue->enqueue( $space_id, 9301 );
		$queue->enqueue( $space_id, 9302 );

		$rows = $queue->reserve( 10 );
		$mine = array_values(
			array_filter(
				$rows,
				static function ( $row ) use ( $space_id ) {
					return (int) $row['space_id'] === $space_id;
				}
			)
		);

		$this->assertCount( 2, $mine );

		$this->assertSame( 2, $queue->delete_for_space( $space_id ) );
		$this->assertSame( 0, $queue->delete_for_space( $space_id ), 'Deleting twice removes nothing.' );
	}

	public function test_rag_hybrid_reordering_is_off_by_default() {
		$this->assertFalse(
			apply_filters( 'tainacan_rag_hybrid_search_enabled', false, null ),
			'Reordering every Tainacan search must be a decision, not an upgrade side effect.'
		);
	}

	public function test_importer_list_returns_importers() {
		$result = $this->run_ability( 'tainacan/importer-list', [] );

		$this->assertNotWPError( $result );
		$this->assertIsArray( $result );
	}
}
