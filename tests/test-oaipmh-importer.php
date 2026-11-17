<?php
/**
 * Tests for the OAI-PMH importer against a mocked data provider.
 *
 * @package Test_Tainacan
 */

namespace Tainacan\Tests;

/**
 * Drives the importer against canned OAI-PMH responses served through the
 * `pre_http_request` filter, so no network access is needed.
 *
 * Every iteration runs on an importer rebuilt from the serialized state, as the
 * background process does (Background_Importer::task()), so retries from a saved
 * state and values kept between iterations are exercised.
 *
 * @group importer
 */
class OaipmhImporterTests extends TAINACAN_UnitTestCase {

	const BASE_URL = 'https://oai.example.org/request';

	/**
	 * Canned responses keyed by "verb", "verb:set=<spec>" or "verb:token=<token>".
	 * A value is a body (HTTP 200), a full response array, or ['sequence' => [...]]
	 * whose entries are served in order (the last one is repeated).
	 *
	 * @var array
	 */
	private $responses = array();

	/**
	 * Every URL requested by the importer, in order.
	 *
	 * @var string[]
	 */
	private $requests = array();

	/**
	 * Error log entries collected from every iteration.
	 *
	 * @var array
	 */
	private $errors = array();

	public function setUp(): void {
		parent::setUp();
		$this->responses = array();
		$this->requests  = array();
		$this->errors    = array();
		add_filter( 'pre_http_request', array( $this, 'serve_fake_provider' ), 10, 3 );
		add_filter( 'tainacan-oaipmh-importer-retry-wait', '__return_zero' );
		add_filter( 'tainacan-oaipmh-importer-max-sleep', '__return_zero' );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'serve_fake_provider' ), 10 );
		remove_filter( 'tainacan-oaipmh-importer-retry-wait', '__return_zero' );
		remove_filter( 'tainacan-oaipmh-importer-max-sleep', '__return_zero' );
		parent::tearDown();
	}

	/**
	 * `pre_http_request` callback answering with the canned response for the request.
	 */
	public function serve_fake_provider( $preempt, $args, $url ) {
		$this->requests[] = $url;

		$query = array();
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		$key = isset( $query['verb'] ) ? $query['verb'] : '';
		if ( isset( $query['resumptionToken'] ) ) {
			$key .= ':token=' . $query['resumptionToken'];
		} elseif ( isset( $query['set'] ) ) {
			$key .= ':set=' . $query['set'];
		}

		if ( ! isset( $this->responses[ $key ] ) ) {
			return $this->http_response( 404, '' );
		}

		$response = $this->responses[ $key ];
		if ( is_array( $response ) && isset( $response['sequence'] ) ) {
			$response = count( $response['sequence'] ) > 1 ? array_shift( $this->responses[ $key ]['sequence'] ) : reset( $response['sequence'] );
		}
		return is_array( $response ) ? $response : $this->http_response( 200, $response );
	}

	private function http_response( $code, $body, array $headers = array() ) {
		return array(
			'headers'  => $headers,
			'body'     => $body,
			'response' => array(
				'code'    => $code,
				'message' => 200 === $code ? 'OK' : 'Error',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	// ----------------------------------------------------------------------
	// OAI-PMH fixtures
	// ----------------------------------------------------------------------

	private function envelope( $verb, $inner ) {
		return '<?xml version="1.0" encoding="UTF-8"?>'
			. '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
			. '<responseDate>2026-01-01T00:00:00Z</responseDate>'
			. '<request verb="' . $verb . '">' . self::BASE_URL . '</request>'
			. $inner
			. '</OAI-PMH>';
	}

	/**
	 * ListSets response.
	 *
	 * @param array  $sets  spec => name.
	 * @param string $token Resumption token ('' for the last page).
	 */
	private function list_sets( array $sets, $token = '' ) {
		$xml = '<ListSets>';
		foreach ( $sets as $spec => $name ) {
			$xml .= '<set><setSpec>' . $spec . '</setSpec><setName>' . $name . '</setName></set>';
		}
		if ( '' !== $token ) {
			$xml .= '<resumptionToken>' . $token . '</resumptionToken>';
		}
		return $this->envelope( 'ListSets', $xml . '</ListSets>' );
	}

	/**
	 * One <record> with oai_dc metadata.
	 *
	 * @param string $identifier OAI identifier.
	 * @param array  $dc         element => value|value[] (element without the dc: prefix).
	 * @param array  $sets       setSpecs of the record header.
	 * @param bool   $deleted    Whether the header carries status="deleted".
	 */
	private function record( $identifier, array $dc, array $sets = array(), $deleted = false ) {
		$xml = '<record><header' . ( $deleted ? ' status="deleted"' : '' ) . '>'
			. '<identifier>' . $identifier . '</identifier><datestamp>2026-01-01T00:00:00Z</datestamp>';
		foreach ( $sets as $set ) {
			$xml .= '<setSpec>' . $set . '</setSpec>';
		}
		$xml .= '</header>';

		if ( ! $deleted ) {
			$xml .= '<metadata><oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" '
				. 'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
			foreach ( $dc as $element => $values ) {
				foreach ( (array) $values as $value ) {
					$attribute = 'title' === $element ? ' xml:lang="pt-BR"' : '';
					$xml      .= '<dc:' . $element . $attribute . '>' . $value . '</dc:' . $element . '>';
				}
			}
			$xml .= '</oai_dc:dc></metadata>';
		}

		return $xml . '</record>';
	}

	/**
	 * ListRecords response.
	 *
	 * @param string[] $records            Output of record().
	 * @param string   $token              Resumption token ('' for none).
	 * @param int|null $complete_list_size completeListSize attribute.
	 */
	private function list_records( array $records, $token = '', $complete_list_size = null ) {
		$xml = '<ListRecords>' . implode( '', $records );
		if ( '' !== $token || null !== $complete_list_size ) {
			$attributes = null !== $complete_list_size ? ' completeListSize="' . $complete_list_size . '"' : '';
			$xml       .= '<resumptionToken' . $attributes . '>' . $token . '</resumptionToken>';
		}
		return $this->envelope( 'ListRecords', $xml . '</ListRecords>' );
	}

	private function oai_error( $verb, $code ) {
		return $this->envelope( $verb, '<error code="' . $code . '">' . $code . '</error>' );
	}

	/**
	 * Set "col1" in two pages (three records plus a deleted one) and set "col2" in one page.
	 */
	private function install_two_sets() {
		$this->responses['ListRecords:set=col1'] = $this->list_records(
			array(
				$this->record( 'oai:example.org:1', array( 'title' => 'Obra 1', 'subject' => array( 'pintura', 'óleo' ) ), array( 'col1' ) ),
				$this->record( 'oai:example.org:2', array( 'title' => 'Obra 2' ), array( 'col1' ) ),
			),
			'tok-col1',
			3
		);
		$this->responses['ListRecords:token=tok-col1'] = $this->list_records(
			array(
				$this->record( 'oai:example.org:3', array( 'title' => 'Obra 3' ), array( 'col1' ) ),
				$this->record( 'oai:example.org:9', array(), array( 'col1' ), true ),
			),
			'',
			3
		);
		$this->responses['ListRecords:set=col2'] = $this->list_records(
			array( $this->record( 'oai:example.org:4', array( 'title' => 'Escultura 1' ), array( 'col2' ) ) )
		);
	}

	// ----------------------------------------------------------------------
	// Helpers
	// ----------------------------------------------------------------------

	/**
	 * Creates an importer session for the fake provider.
	 */
	private function new_importer( array $options = array(), $url = self::BASE_URL ) {
		$importer = \Tainacan\Importer_Handler::get_instance()->initialize_importer( 'oaipmh_importer' );
		$this->assertInstanceOf( '\Tainacan\Importer\Oaipmh_Importer', $importer );
		$importer->set_url( $url );
		$importer->set_options( $options );
		return $importer;
	}

	/**
	 * Creates the destination collection and adds it to the importer, as the mapping screen does:
	 * title and description go to the core metadata, the other fields to new metadata.
	 *
	 * @return \Tainacan\Entities\Collection
	 */
	private function add_destination_collection( \Tainacan\Importer\Importer $importer, array $create_for = array( 'dc:subject', 'dc:identifier', 'oai:identifier' ) ) {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'OAI destination',
				'status' => 'publish',
			),
			true
		);

		$mapping = array(
			$collection->get_core_title_metadatum()->get_id()       => 'dc:title',
			$collection->get_core_description_metadatum()->get_id() => 'dc:description',
		);
		foreach ( array_values( $create_for ) as $index => $field ) {
			$mapping[ 'create_metadata' . $index ] = $field;
		}

		$this->assertTrue( $importer->add_collection( array( 'id' => $collection->get_id(), 'mapping' => $mapping, 'total_items' => null ) ) );
		return $collection;
	}

	/**
	 * Rebuilds the importer from its serialized state, as the background process does.
	 */
	private function rebuild( array $data ) {
		return new \Tainacan\Importer\Oaipmh_Importer( $data );
	}

	/**
	 * Runs one iteration on an importer rebuilt from $data and returns the new state.
	 *
	 * @param array $data   Serialized importer (from _to_Array(true)).
	 * @param mixed $result Receives the return value of run().
	 * @return array
	 */
	private function iterate( array $data, &$result = null ) {
		$importer = $this->rebuild( $data );
		$result   = $importer->run();
		$this->errors = array_merge( $this->errors, $importer->get_error_log() );
		return $importer->_to_Array( true );
	}

	/**
	 * Runs iterations until the importer finishes and returns the final state.
	 */
	private function run_in_background( array $data ) {
		for ( $i = 0; $i < 100; $i++ ) {
			$data = $this->iterate( $data, $result );
			if ( false === $result ) {
				return $data;
			}
		}
		$this->fail( 'The importer did not finish; paging may be looping' );
	}

	/**
	 * Error messages collected so far, as one readable string.
	 */
	private function error_messages() {
		return wp_json_encode( array_column( $this->errors, 'message' ) );
	}

	private function items_of( \Tainacan\Entities\Collection $collection ) {
		return \Tainacan\Repositories\Items::get_instance()->fetch(
			array(
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'orderby'        => 'title',
				'order'          => 'ASC',
			),
			$collection,
			'OBJECT'
		);
	}

	private function titles_of( array $items ) {
		return array_map( function( $item ) { return $item->get_title(); }, $items );
	}

	private function metadatum_named( \Tainacan\Entities\Collection $collection, $name ) {
		foreach ( \Tainacan\Repositories\Metadata::get_instance()->fetch_by_collection( $collection ) as $metadatum ) {
			if ( $metadatum->get_name() === $name ) {
				return $metadatum;
			}
		}
		return null;
	}

	private function value_of( \Tainacan\Entities\Item $item, \Tainacan\Entities\Metadatum $metadatum ) {
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $item, $metadatum );
		return $item_metadata->get_value();
	}

	private function count_requests_containing( $needle ) {
		return count( array_filter( $this->requests, function( $url ) use ( $needle ) { return false !== strpos( $url, $needle ); } ) );
	}

	// ----------------------------------------------------------------------
	// Tests
	// ----------------------------------------------------------------------

	/**
	 * Userinfo, query string and fragment are dropped; a `set` argument is kept as the set spec.
	 */
	public function test_url_keeps_only_scheme_host_and_path() {
		$importer = $this->new_importer( array(), 'https://user:secret@oai.example.org/request#frag?verb=Identify' );
		$this->assertSame( self::BASE_URL, $importer->get_url() );

		$importer = $this->new_importer( array(), self::BASE_URL . '?verb=ListRecords&metadataPrefix=oai_dc&set=col2' );
		$this->assertSame( self::BASE_URL, $importer->get_url() );
		$this->assertSame( 'col2', $importer->get_transient( 'url_set_spec' ) );

		$this->assertFalse( $importer->set_url( 'ftp://oai.example.org/request' ) );
	}

	/**
	 * One set: every page of that set is requested once, following the resumption tokens,
	 * and ListSets is never called.
	 */
	public function test_one_set_follows_the_tokens_and_fetches_each_page_once() {
		$this->install_two_sets();
		$importer   = $this->new_importer( array( 'source' => 'set', 'set_spec' => 'col1' ) );
		$collection = $this->add_destination_collection( $importer );

		$data = $this->run_in_background( $importer->_to_Array( true ) );

		$this->assertEquals( array( 'Obra 1', 'Obra 2', 'Obra 3' ), $this->titles_of( $this->items_of( $collection ) ), $this->error_messages() );
		$this->assertSame( 1, $this->count_requests_containing( 'set=col1' ), 'The first page must be fetched once' );
		$this->assertSame( 1, $this->count_requests_containing( 'resumptionToken=tok-col1' ) );
		$this->assertSame( 0, $this->count_requests_containing( 'verb=ListSets' ) );
		$this->assertSame( 0, $this->count_requests_containing( 'set=col2' ) );
		$this->assertEquals( 2, $data['collections'][0]['total_items'], 'total_items counts the pages' );
		foreach ( $this->items_of( $collection ) as $item ) {
			$this->assertEquals( 'publish', $item->get_status() );
		}
	}

	/**
	 * The set spec may come from a pasted ListRecords URL, in the form field or as the source URL.
	 */
	public function test_set_spec_from_a_pasted_url() {
		$this->install_two_sets();

		$importer   = $this->new_importer( array( 'source' => 'set', 'set_spec' => self::BASE_URL . '?verb=ListRecords&metadataPrefix=oai_dc&set=col2' ) );
		$collection = $this->add_destination_collection( $importer );
		$this->run_in_background( $importer->_to_Array( true ) );
		$this->assertEquals( array( 'Escultura 1' ), $this->titles_of( $this->items_of( $collection ) ), $this->error_messages() );

		$importer   = $this->new_importer( array( 'source' => 'set', 'set_spec' => '' ), self::BASE_URL . '?verb=ListRecords&set=col2' );
		$collection = $this->add_destination_collection( $importer );
		$this->run_in_background( $importer->_to_Array( true ) );
		$this->assertEquals( array( 'Escultura 1' ), $this->titles_of( $this->items_of( $collection ) ), $this->error_messages() );
	}

	/**
	 * One set mode without a set spec does not run.
	 */
	public function test_one_set_without_spec_does_not_request_anything() {
		$this->install_two_sets();
		$importer   = $this->new_importer( array( 'source' => 'set', 'set_spec' => '' ) );
		$collection = $this->add_destination_collection( $importer );

		$this->run_in_background( $importer->_to_Array( true ) );

		$this->assertSame( array(), $this->requests );
		$this->assertCount( 0, $this->items_of( $collection ) );
		$this->assertStringContainsString( 'No set spec', $this->error_messages() );
	}

	/**
	 * Whole repository: one unfiltered ListRecords; a record in several sets becomes one item
	 * with one term per set, named after ListSets (read page by page, duplicated names
	 * disambiguated by their spec, unknown specs kept as they are).
	 */
	public function test_whole_repository_assigns_every_set_as_a_term_named_from_list_sets() {
		$this->responses['ListSets'] = $this->list_sets( array( 'col1' => 'Pinturas', 'col2' => 'Teses' ), 'sets-2' );
		$this->responses['ListSets:token=sets-2'] = $this->list_sets( array( 'col3' => 'Teses', 'col4' => 'Gravuras' ) );
		$this->responses['ListRecords'] = $this->list_records(
			array(
				$this->record( 'oai:example.org:1', array( 'title' => 'Obra 1' ), array( 'col1', 'col3', 'col4' ) ),
				$this->record( 'oai:example.org:2', array( 'title' => 'Obra 2' ), array( 'col2' ) ),
				$this->record( 'oai:example.org:3', array( 'title' => 'Obra 3' ), array( 'col9' ) ),
			)
		);

		$importer   = $this->new_importer( array( 'source' => 'repository' ) );
		$collection = $this->add_destination_collection( $importer );
		$this->run_in_background( $importer->_to_Array( true ) );

		$items = $this->items_of( $collection );
		$this->assertEquals( array( 'Obra 1', 'Obra 2', 'Obra 3' ), $this->titles_of( $items ), $this->error_messages() );
		$this->assertSame( 1, $this->count_requests_containing( 'verb=ListRecords' ) );
		$this->assertSame( 0, $this->count_requests_containing( 'set=' ) );
		$this->assertSame( 2, $this->count_requests_containing( 'verb=ListSets' ) );

		$metadatum = \Tainacan\Repositories\Metadata::get_instance()->fetch( (int) get_post_meta( $collection->get_id(), \Tainacan\Importer\Oaipmh_Importer::COLLECTION_SETS_META, true ) );
		$this->assertInstanceOf( '\Tainacan\Entities\Metadatum', $metadatum );
		$this->assertTrue( $metadatum->is_multiple() );
		$options  = $metadatum->get_metadata_type_options();
		$taxonomy = \Tainacan\Repositories\Taxonomies::get_instance()->fetch( (int) $options['taxonomy_id'] );

		$expected = array(
			'Obra 1' => array( 'Gravuras', 'Pinturas', 'Teses (col3)' ),
			'Obra 2' => array( 'Teses (col2)' ),
			'Obra 3' => array( 'col9' ),
		);
		foreach ( $items as $item ) {
			$terms = wp_get_object_terms( $item->get_id(), $taxonomy->get_db_identifier(), array( 'fields' => 'names' ) );
			sort( $terms );
			$this->assertEquals( $expected[ $item->get_title() ], $terms, $item->get_title() );
		}
	}

	/**
	 * Every repeated Dublin Core value is stored: multiple metadata get the whole list, the core
	 * title the first value, the core description every paragraph. The OAI identifier is stored too.
	 */
	public function test_repeated_dublin_core_values_are_all_stored() {
		$this->responses['ListRecords:set=col1'] = $this->list_records(
			array(
				$this->record(
					'oai:example.org:42',
					array(
						'title'       => array( 'Título principal', 'Main title' ),
						'description' => array( 'Primeiro parágrafo.', 'Segundo parágrafo.' ),
						'subject'     => array( 'pintura', 'óleo', 'retrato' ),
						'identifier'  => array( 'SILVA, A. Obra. 2016.', 'http://hdl.handle.net/123/42', 'https://doi.org/10.1/42' ),
					),
					array( 'col1' )
				),
			)
		);

		$importer   = $this->new_importer( array( 'source' => 'set', 'set_spec' => 'col1' ) );
		$collection = $this->add_destination_collection( $importer );
		$this->run_in_background( $importer->_to_Array( true ) );

		$items = $this->items_of( $collection );
		$this->assertCount( 1, $items, $this->error_messages() );
		$item = $items[0];

		$this->assertEquals( 'Título principal', $item->get_title() );
		$this->assertEquals( "Primeiro parágrafo.\n\nSegundo parágrafo.", $item->get_description() );

		$subject = $this->metadatum_named( $collection, 'Subject' );
		$this->assertNotNull( $subject );
		$this->assertTrue( $subject->is_multiple() );
		$this->assertEquals( array( 'pintura', 'óleo', 'retrato' ), $this->value_of( $item, $subject ) );

		$identifier = $this->metadatum_named( $collection, 'Identifier' );
		$this->assertEquals( array( 'SILVA, A. Obra. 2016.', 'http://hdl.handle.net/123/42', 'https://doi.org/10.1/42' ), $this->value_of( $item, $identifier ) );

		$oai_identifier = $this->metadatum_named( $collection, 'OAI-PMH identifier' );
		$this->assertEquals( 'oai:example.org:42', $this->value_of( $item, $oai_identifier ) );
		$this->assertEquals( 'oai:example.org:42', get_post_meta( $item->get_id(), \Tainacan\Importer\Oaipmh_Importer::ITEM_IDENTIFIER_META, true ) );
	}

	/**
	 * A page that keeps failing is retried a few times and then stops the collection, instead of
	 * advancing to a page without a resumption token.
	 */
	public function test_a_page_that_fails_for_good_stops_the_collection() {
		$this->install_two_sets();
		$this->responses['ListRecords:token=tok-col1'] = $this->http_response( 500, '' );

		$importer   = $this->new_importer( array( 'source' => 'set', 'set_spec' => 'col1' ) );
		$collection = $this->add_destination_collection( $importer );
		$this->run_in_background( $importer->_to_Array( true ) );

		$this->assertEquals( array( 'Obra 1', 'Obra 2' ), $this->titles_of( $this->items_of( $collection ) ) );
		$this->assertSame( 1, $this->count_requests_containing( 'set=col1' ) );
		$this->assertSame( \Tainacan\Importer\Oaipmh_Importer::MAX_PAGE_ATTEMPTS, $this->count_requests_containing( 'resumptionToken=tok-col1' ) );
		$this->assertSame( 1 + \Tainacan\Importer\Oaipmh_Importer::MAX_PAGE_ATTEMPTS, $this->count_requests_containing( 'verb=ListRecords' ), 'Nothing is requested after the failed page' );
		$this->assertStringContainsString( 'failed for good', $this->error_messages() );
	}

	/**
	 * badResumptionToken cannot be fixed by retrying: the collection stops at once.
	 */
	public function test_bad_resumption_token_stops_without_retrying() {
		$this->install_two_sets();
		$this->responses['ListRecords:token=tok-col1'] = $this->oai_error( 'ListRecords', 'badResumptionToken' );

		$importer   = $this->new_importer( array( 'source' => 'set', 'set_spec' => 'col1' ) );
		$collection = $this->add_destination_collection( $importer );
		$this->run_in_background( $importer->_to_Array( true ) );

		$this->assertEquals( array( 'Obra 1', 'Obra 2' ), $this->titles_of( $this->items_of( $collection ) ) );
		$this->assertSame( 1, $this->count_requests_containing( 'resumptionToken=tok-col1' ) );
		$this->assertStringContainsString( 'badResumptionToken', $this->error_messages() );
	}

	/**
	 * HTTP 503 with Retry-After is flow control: the page is not consumed, the wait is scheduled
	 * without a long sleep, and the same page is requested again once the wait is over.
	 */
	public function test_retry_after_does_not_consume_the_page() {
		$page = $this->list_records( array( $this->record( 'oai:example.org:1', array( 'title' => 'Obra 1' ), array( 'col1' ) ) ) );
		$this->responses['ListRecords:set=col1'] = array(
			'sequence' => array(
				$this->http_response( 503, '', array( 'retry-after' => '120' ) ),
				$this->http_response( 200, $page ),
			),
		);

		$importer   = $this->new_importer( array( 'source' => 'set', 'set_spec' => 'col1' ) );
		$collection = $this->add_destination_collection( $importer );
		$data       = $importer->_to_Array( true );

		// Until the first request is answered with 503.
		for ( $i = 0; $i < 5 && 0 === count( $this->requests ); $i++ ) {
			$data = $this->iterate( $data );
		}
		$this->assertSame( 1, count( $this->requests ) );
		$this->assertEquals( 0, $data['current_collection_item'], 'The page index must not advance' );
		$this->assertGreaterThanOrEqual( time() + 100, $data['transients']['not_before'] );
		$this->assertLessThanOrEqual( time() + 121, $data['transients']['not_before'] );

		// While waiting, nothing is requested.
		$data = $this->iterate( $data, $result );
		$this->assertNotFalse( $result );
		$this->assertSame( 1, count( $this->requests ) );
		$this->assertCount( 0, $this->items_of( $collection ) );

		// Once the wait is over, the same page is requested and imported.
		$data['transients']['not_before'] = time() - 1;
		$this->run_in_background( $data );
		$this->assertSame( 2, $this->count_requests_containing( 'set=col1' ) );
		$this->assertEquals( array( 'Obra 1' ), $this->titles_of( $this->items_of( $collection ) ), $this->error_messages() );
	}

	/**
	 * When the background process retries from an older saved state, a page processed again does
	 * not insert its records twice.
	 */
	public function test_a_page_processed_again_after_a_retry_does_not_duplicate_items() {
		$this->install_two_sets();
		$importer   = $this->new_importer( array( 'source' => 'set', 'set_spec' => 'col1' ) );
		$collection = $this->add_destination_collection( $importer );
		$saved      = $importer->_to_Array( true );

		// Iterate until the first page was inserted, then drop that progress, as a crash before
		// the batch is saved would.
		$data = $saved;
		for ( $i = 0; $i < 5 && 0 === count( $this->items_of( $collection ) ); $i++ ) {
			$data = $this->iterate( $data );
		}
		$this->assertCount( 2, $this->items_of( $collection ) );

		$this->run_in_background( $saved );

		$this->assertEquals( array( 'Obra 1', 'Obra 2', 'Obra 3' ), $this->titles_of( $this->items_of( $collection ) ), $this->error_messages() );
		$this->assertSame( 2, $this->count_requests_containing( 'set=col1' ) );
	}
}
