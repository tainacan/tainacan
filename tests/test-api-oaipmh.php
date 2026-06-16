<?php

namespace Tainacan\Tests;

/**
 * @group api
 * @group oai-pmh
 */
class TAINACAN_REST_Oaipmh_Controller extends TAINACAN_UnitApiTestCase {

	/**
	 * @param array $params
	 * @return \WP_REST_Response
	 */
	private function dispatch_oai( array $params ) {
		$request = new \WP_REST_Request( 'GET', $this->namespace . '/oai' );
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}
		return $this->server->dispatch( $request );
	}

	/**
	 * @param \WP_REST_Response $response
	 * @return string
	 */
	private function get_oai_body( $response ) {
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsString( $data );
		$this->assertNotEmpty( $data );
		return $data;
	}

	/**
	 * @return array{collection:\Tainacan\Entities\Collection,item:\Tainacan\Entities\Item}
	 */
	private function create_published_item() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'OAI Test Collection',
				'description' => 'For OAI-PMH tests',
				'status'      => 'publish',
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'OAI Test Item',
				'description' => 'OAI item description',
				'collection'  => $collection,
				'status'      => 'publish',
			),
			true
		);

		return compact( 'collection', 'item' );
	}

	public function test_identify() {
		$body = $this->get_oai_body( $this->dispatch_oai( array( 'verb' => 'Identify' ) ) );

		foreach ( array( 'repositoryName', 'baseURL', 'protocolVersion', 'adminEmail', 'earliestDatestamp', 'deletedRecord', 'granularity' ) as $field ) {
			$this->assertStringContainsString( "<{$field}>", $body );
		}

		$this->assertStringContainsString( 'oai-identifier', $body );
		$this->assertStringContainsString( '<repositoryIdentifier>', $body );
		$this->assertStringContainsString( '<sampleIdentifier>', $body );
	}

	public function test_list_metadata_formats() {
		$body = $this->get_oai_body( $this->dispatch_oai( array( 'verb' => 'ListMetadataFormats' ) ) );
		$this->assertStringContainsString( 'oai_dc', $body );
	}

	public function test_list_sets() {
		$this->create_published_item();
		$body = $this->get_oai_body( $this->dispatch_oai( array( 'verb' => 'ListSets' ) ) );
		$this->assertStringContainsString( '<set', $body );
		$this->assertStringContainsString( '<setSpec>', $body );
	}

	public function test_list_sets_rejects_extra_arguments() {
		$this->create_published_item();
		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb' => 'ListSets',
					'set'  => '1',
				)
			)
		);
		$this->assertStringContainsString( 'code="badArgument"', $body );
	}

	public function test_list_sets_pagination() {
		add_filter( 'tainacan-oai-maxrecords', array( $this, 'filter_oai_page_size_one' ) );

		$this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI Set A',
				'status' => 'publish',
			),
			true
		);
		$this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI Set B',
				'status' => 'publish',
			),
			true
		);

		$page_one = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb' => 'ListSets',
				)
			)
		);

		$this->assertStringContainsString( '<resumptionToken', $page_one );
		preg_match( '/<resumptionToken[^>]*>([^<]+)<\/resumptionToken>/', $page_one, $matches );
		$this->assertNotEmpty( $matches[1] );
		$token = $matches[1];

		$page_two = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'            => 'ListSets',
					'resumptionToken' => $token,
				)
			)
		);
		$this->assertStringContainsString( '<set', $page_two );

		remove_filter( 'tainacan-oai-maxrecords', array( $this, 'filter_oai_page_size_one' ) );
	}

	public function test_cross_verb_resumption_token_is_rejected() {
		add_filter( 'tainacan-oai-maxrecords', array( $this, 'filter_oai_page_size_one' ) );

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI Cross Verb Collection',
				'status' => 'publish',
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'OAI Item A',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);
		$this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'OAI Item B',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		$page_one = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'ListRecords',
					'metadataPrefix' => 'oai_dc',
					'set'            => (string) $collection->get_id(),
				)
			)
		);

		preg_match( '/<resumptionToken[^>]*>([^<]+)<\/resumptionToken>/', $page_one, $matches );
		$this->assertNotEmpty( $matches[1] );

		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'            => 'ListSets',
					'resumptionToken' => $matches[1],
				)
			)
		);
		$this->assertStringContainsString( 'code="badResumptionToken"', $body );

		remove_filter( 'tainacan-oai-maxrecords', array( $this, 'filter_oai_page_size_one' ) );
	}

	public function test_list_records() {
		$this->create_published_item();
		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'ListRecords',
					'metadataPrefix' => 'oai_dc',
				)
			)
		);
		$this->assertStringContainsString( '<ListRecords>', $body );
		$this->assertStringContainsString( '<record', $body );
		$this->assertStringContainsString( 'OAI Test Item', $body );
	}

	public function test_get_record_with_host_based_identifier() {
		$setup = $this->create_published_item();
		$provider = new \Tainacan\OAIPMH\OAIPMH_Data_Provider();
		$identifier = $provider->build_identifier( $setup['item']->get_id() );

		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'GetRecord',
					'metadataPrefix' => 'oai_dc',
					'identifier'     => $identifier,
				)
			)
		);
		$this->assertStringContainsString( '<GetRecord>', $body );
		$this->assertStringContainsString( $identifier, $body );
	}

	public function test_get_record_rejects_legacy_identifier() {
		$setup = $this->create_published_item();
		$legacy_identifier = $this->build_legacy_identifier( $setup['item']->get_id() );

		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'GetRecord',
					'metadataPrefix' => 'oai_dc',
					'identifier'     => $legacy_identifier,
				)
			)
		);
		$this->assertStringContainsString( 'code="idDoesNotExist"', $body );
	}

	public function test_get_record_rejects_private_item() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI Public Collection',
				'status' => 'publish',
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Secret OAI Item Title',
				'description' => 'Must not appear in OAI',
				'collection'  => $collection,
				'status'      => 'private',
			),
			true
		);

		$provider   = new \Tainacan\OAIPMH\OAIPMH_Data_Provider();
		$identifier = $provider->build_identifier( $item->get_id() );

		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'GetRecord',
					'metadataPrefix' => 'oai_dc',
					'identifier'     => $identifier,
				)
			)
		);

		$this->assertStringContainsString( 'code="idDoesNotExist"', $body );
		$this->assertStringNotContainsString( 'Secret OAI Item Title', $body );
	}

	public function test_list_sets_excludes_private_collection() {
		$this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI Private Set Name',
				'status' => 'private',
			),
			true
		);

		$body = $this->get_oai_body( $this->dispatch_oai( array( 'verb' => 'ListSets' ) ) );

		$this->assertStringNotContainsString( 'OAI Private Set Name', $body );
	}

	public function test_list_records_rejects_private_collection_set() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI Private Harvest Collection',
				'status' => 'private',
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'Published item in private collection',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'ListRecords',
					'metadataPrefix' => 'oai_dc',
					'set'            => (string) $collection->get_id(),
				)
			)
		);

		$this->assertStringContainsString( 'code="badArgument"', $body );
		$this->assertStringNotContainsString( 'Published item in private collection', $body );
	}

	public function test_list_metadata_formats_rejects_private_item_identifier() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI Metadata Formats Collection',
				'status' => 'publish',
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'Private item for ListMetadataFormats',
				'collection' => $collection,
				'status'     => 'private',
			),
			true
		);

		$provider   = new \Tainacan\OAIPMH\OAIPMH_Data_Provider();
		$identifier = $provider->build_identifier( $item->get_id() );

		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'       => 'ListMetadataFormats',
					'identifier' => $identifier,
				)
			)
		);

		$this->assertStringContainsString( 'code="idDoesNotExist"', $body );
	}

	public function test_dublin_core_mapping_in_list_records() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI DC Mapping Collection',
				'status' => 'publish',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'            => 'Creator field',
				'collection'      => $collection,
				'metadata_type'   => 'Tainacan\Metadata_Types\Text',
				'exposer_mapping' => array(
					'dublin-core' => 'dc:creator',
				),
			),
			true,
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'Mapped OAI Item',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata( $item, $metadatum, 'Jane Curator' );

		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'ListRecords',
					'metadataPrefix' => 'oai_dc',
					'set'            => (string) $collection->get_id(),
				)
			)
		);

		$this->assertStringContainsString( 'Jane Curator', $body );
		$this->assertStringContainsString( '<dc:creator>', $body );
	}

	public function test_selective_harvest_from_uses_modification_date() {
		global $wpdb;

		$setup   = $this->create_published_item();
		$item_id = $setup['item']->get_id();

		$wpdb->update(
			$wpdb->posts,
			array( 'post_modified_gmt' => '2020-01-15 10:00:00' ),
			array( 'ID' => $item_id ),
			array( '%s' ),
			array( '%d' )
		);
		clean_post_cache( $item_id );

		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'ListRecords',
					'metadataPrefix' => 'oai_dc',
					'from'           => '2024-01-01',
				)
			)
		);

		$this->assertStringContainsString( 'code="noRecordsMatch"', $body );
	}

	public function test_until_date_only_includes_end_of_day() {
		global $wpdb;

		$setup   = $this->create_published_item();
		$item_id = $setup['item']->get_id();

		$wpdb->update(
			$wpdb->posts,
			array( 'post_modified_gmt' => '2024-06-15 18:30:00' ),
			array( 'ID' => $item_id ),
			array( '%s' ),
			array( '%d' )
		);
		clean_post_cache( $item_id );

		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'ListRecords',
					'metadataPrefix' => 'oai_dc',
					'from'           => '2024-06-15',
					'until'          => '2024-06-15',
				)
			)
		);

		$this->assertStringContainsString( '<record', $body );
		$this->assertStringNotContainsString( 'code="noRecordsMatch"', $body );
	}

	public function test_resumption_token_pagination_and_reuse() {
		add_filter( 'tainacan-oai-maxrecords', array( $this, 'filter_oai_page_size_one' ) );

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI Pagination Collection',
				'status' => 'publish',
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'OAI Item A',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);
		$this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'OAI Item B',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		$page_one = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'           => 'ListRecords',
					'metadataPrefix' => 'oai_dc',
					'set'            => (string) $collection->get_id(),
				)
			)
		);

		$this->assertStringContainsString( '<resumptionToken', $page_one );
		preg_match( '/<resumptionToken[^>]*>([^<]+)<\/resumptionToken>/', $page_one, $matches );
		$this->assertNotEmpty( $matches[1] );
		$token = $matches[1];

		$page_two = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'            => 'ListRecords',
					'resumptionToken' => $token,
				)
			)
		);
		$this->assertStringContainsString( '<record', $page_two );

		$page_two_again = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'            => 'ListRecords',
					'resumptionToken' => $token,
				)
			)
		);
		$this->assertStringContainsString( '<record', $page_two_again );

		remove_filter( 'tainacan-oai-maxrecords', array( $this, 'filter_oai_page_size_one' ) );
	}

	public function filter_oai_page_size_one() {
		return 1;
	}

	public function test_bad_verb() {
		$body = $this->get_oai_body( $this->dispatch_oai( array( 'verb' => 'NotARealVerb' ) ) );
		$this->assertStringContainsString( 'code="badVerb"', $body );
	}

	public function test_bad_resumption_token() {
		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'            => 'ListRecords',
					'resumptionToken' => 'invalid-token-value',
				)
			)
		);
		$this->assertStringContainsString( 'code="badResumptionToken"', $body );
	}

	public function test_exclusive_argument_with_resumption_token() {
		$body = $this->get_oai_body(
			$this->dispatch_oai(
				array(
					'verb'            => 'ListRecords',
					'resumptionToken' => 'some-token',
					'metadataPrefix'  => 'oai_dc',
				)
			)
		);
		$this->assertStringContainsString( 'code="badArgument"', $body );
	}

	/**
	 * @param int $item_id
	 * @return string
	 */
	private function build_legacy_identifier( $item_id ) {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		$parts = explode( '.', $host );
		return 'oai:' . implode( '.', array_reverse( $parts ) ) . ':' . (int) $item_id;
	}
}
