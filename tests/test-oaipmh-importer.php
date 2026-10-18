<?php
/**
 * Tests for the OAI-PMH importer against a mocked data provider.
 *
 * @package Test_Tainacan
 */

namespace Tainacan\Tests;

/**
 * Drives the whole importer (both steps) against canned OAI-PMH responses served
 * through the `pre_http_request` filter, so no network access is needed.
 *
 * @group importer
 */
class OaipmhImporterTests extends TAINACAN_UnitTestCase {

	const BASE_URL = 'https://oai.example.org/request';

	/**
	 * Canned responses keyed by "verb", "verb:set=<spec>" or "verb:token=<token>".
	 *
	 * @var array<string, string|array>
	 */
	private $responses = array();

	/**
	 * Every URL requested by the importer, in order.
	 *
	 * @var string[]
	 */
	private $requests = array();

	/**
	 * Installs the fake provider and disables retry pauses.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->responses = array();
		$this->requests  = array();
		add_filter( 'pre_http_request', array( $this, 'serve_fake_provider' ), 10, 3 );
		add_filter( 'tainacan-oaipmh-importer-retry-wait', '__return_zero' );
	}

	/**
	 * Removes the fake provider.
	 */
	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'serve_fake_provider' ), 10 );
		remove_filter( 'tainacan-oaipmh-importer-retry-wait', '__return_zero' );
		parent::tearDown();
	}

	/**
	 * `pre_http_request` callback answering with the canned response for the requested verb.
	 *
	 * @param false|array $preempt Whether to preempt the request.
	 * @param array       $args    Request arguments.
	 * @param string      $url     Requested URL.
	 * @return array
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
		if ( is_array( $this->responses[ $key ] ) ) {
			return $this->responses[ $key ];
		}
		return $this->http_response( 200, $this->responses[ $key ] );
	}

	/**
	 * Builds a WP_Http-like response array.
	 *
	 * @param int    $code HTTP status code.
	 * @param string $body Response body.
	 * @return array
	 */
	private function http_response( $code, $body ) {
		return array(
			'headers'  => array(),
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

	/**
	 * Wraps a verb payload in the OAI-PMH envelope.
	 *
	 * @param string $verb  Requested verb.
	 * @param string $inner XML of the verb element (or of an <error>).
	 * @return string
	 */
	private function envelope( $verb, $inner ) {
		return '<?xml version="1.0" encoding="UTF-8"?>'
			. '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
			. '<responseDate>2026-01-01T00:00:00Z</responseDate>'
			. '<request verb="' . $verb . '">' . self::BASE_URL . '</request>'
			. $inner
			. '</OAI-PMH>';
	}

	/**
	 * Identify response.
	 *
	 * @param string $repository_name Repository name.
	 * @return string
	 */
	private function identify( $repository_name ) {
		return $this->envelope(
			'Identify',
			'<Identify><repositoryName>' . $repository_name . '</repositoryName><baseURL>' . self::BASE_URL . '</baseURL>'
			. '<protocolVersion>2.0</protocolVersion><adminEmail>admin@example.org</adminEmail>'
			. '<earliestDatestamp>2020-01-01T00:00:00Z</earliestDatestamp><deletedRecord>persistent</deletedRecord>'
			. '<granularity>YYYY-MM-DDThh:mm:ssZ</granularity></Identify>'
		);
	}

	/**
	 * ListSets response.
	 *
	 * @param array $sets spec => name.
	 * @return string
	 */
	private function list_sets( array $sets ) {
		$xml = '<ListSets>';
		foreach ( $sets as $spec => $name ) {
			$xml .= '<set><setSpec>' . $spec . '</setSpec><setName>' . $name . '</setName></set>';
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
	 * @return string
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
	 * @param string   $token              Resumption token ('' for none; the element is still
	 *                                     emitted empty when $complete_list_size is given).
	 * @param int|null $complete_list_size completeListSize attribute.
	 * @param int      $cursor             cursor attribute.
	 * @return string
	 */
	private function list_records( array $records, $token = '', $complete_list_size = null, $cursor = 0 ) {
		$xml = '<ListRecords>' . implode( '', $records );
		if ( '' !== $token || null !== $complete_list_size ) {
			$attributes = null !== $complete_list_size ? ' completeListSize="' . $complete_list_size . '" cursor="' . $cursor . '"' : '';
			$xml       .= '<resumptionToken' . $attributes . '>' . $token . '</resumptionToken>';
		}
		return $this->envelope( 'ListRecords', $xml . '</ListRecords>' );
	}

	/**
	 * OAI-PMH <error> response.
	 *
	 * @param string $verb Requested verb.
	 * @param string $code Error code.
	 * @return string
	 */
	private function oai_error( $verb, $code ) {
		return $this->envelope( $verb, '<error code="' . $code . '">' . $code . '</error>' );
	}

	/**
	 * Registers the default provider: two sets, three records in "col1" split in two
	 * pages, one active plus one deleted record in "col2".
	 */
	private function install_default_provider() {
		$this->responses['Identify'] = $this->identify( 'Repositório de Teste' );
		$this->responses['ListSets'] = $this->list_sets(
			array(
				'col1' => 'Pinturas',
				'col2' => 'Esculturas',
			)
		);

		$col1_page1 = array(
			$this->record(
				'oai:example.org:1',
				array(
					'title'       => 'Obra 1',
					'creator'     => 'Artista Um',
					'description' => array( 'Primeiro parágrafo.', 'Segundo parágrafo.' ),
					'subject'     => array( 'pintura', 'óleo' ),
					'date'        => '1920',
				),
				array( 'col1' )
			),
			$this->record(
				'oai:example.org:2',
				array(
					'title'   => 'Obra 2',
					'creator' => 'Artista Dois',
				),
				array( 'col1' )
			),
		);
		$col1_page2 = array(
			$this->record(
				'oai:example.org:3',
				array(
					'title'   => 'Obra 3',
					'creator' => 'Artista Três',
				),
				array( 'col1' )
			),
		);
		$col2_page  = array(
			$this->record(
				'oai:example.org:4',
				array(
					'title'   => 'Escultura 1',
					'creator' => 'Escultor',
				),
				array( 'col2' )
			),
			$this->record( 'oai:example.org:5', array(), array( 'col2' ), true ),
		);

		$this->responses['ListRecords:set=col1']       = $this->list_records( $col1_page1, 'tok-col1', 3, 0 );
		$this->responses['ListRecords:token=tok-col1'] = $this->list_records( $col1_page2, '', 3, 2 );
		$this->responses['ListRecords:set=col2']       = $this->list_records( $col2_page );
		// Whole repository (used when sets are imported as a taxonomy or do not exist).
		$this->responses['ListRecords'] = $this->list_records( array_merge( $col1_page1, $col1_page2, $col2_page ) );
	}

	// ----------------------------------------------------------------------
	// Helpers
	// ----------------------------------------------------------------------

	/**
	 * Creates an importer session for the fake provider.
	 *
	 * @param array $options Importer options.
	 * @return \Tainacan\Importer\Oaipmh_Importer
	 */
	private function new_importer( array $options = array() ) {
		$handler  = \Tainacan\Importer_Handler::get_instance();
		$importer = $handler->initialize_importer( 'oaipmh_importer' );
		$this->assertInstanceOf( '\Tainacan\Importer\Oaipmh_Importer', $importer );
		$importer->set_url( self::BASE_URL . '?verb=Identify' );
		if ( ! empty( $options ) ) {
			$importer->set_options( $options );
		}
		return $importer;
	}

	/**
	 * Runs the importer until it reports completion, as the background process would.
	 *
	 * @param \Tainacan\Importer\Importer $importer Importer session.
	 */
	private function run_to_completion( \Tainacan\Importer\Importer $importer ) {
		$iterations = 0;
		while ( false !== $importer->run() ) {
			$this->assertLessThan( 50, ++$iterations, 'Importer did not finish; paging may be looping' );
			if ( $importer->get_abort() ) {
				break;
			}
		}
	}

	/**
	 * Finds a collection created by the importer by its name.
	 *
	 * @param string $name Collection name.
	 * @return \Tainacan\Entities\Collection|null
	 */
	private function find_collection( $name ) {
		$collections = \Tainacan\Repositories\Collections::get_instance()->fetch( array( 'posts_per_page' => -1 ), 'OBJECT' );
		foreach ( $collections as $collection ) {
			if ( $collection->get_name() === $name ) {
				return $collection;
			}
		}
		return null;
	}

	/**
	 * Lists the items of a collection ordered by title.
	 *
	 * @param \Tainacan\Entities\Collection $collection Collection.
	 * @return \Tainacan\Entities\Item[]
	 */
	private function items_of( \Tainacan\Entities\Collection $collection ) {
		return \Tainacan\Repositories\Items::get_instance()->fetch(
			array(
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			),
			$collection,
			'OBJECT'
		);
	}

	/**
	 * Finds a metadatum of a collection by its name.
	 *
	 * @param \Tainacan\Entities\Collection $collection Collection.
	 * @param string                        $name       Metadatum name.
	 * @return \Tainacan\Entities\Metadatum|null
	 */
	private function metadatum_named( \Tainacan\Entities\Collection $collection, $name ) {
		foreach ( \Tainacan\Repositories\Metadata::get_instance()->fetch_by_collection( $collection ) as $metadatum ) {
			if ( $metadatum->get_name() === $name ) {
				return $metadatum;
			}
		}
		return null;
	}

	/**
	 * Titles of a list of items, in order.
	 *
	 * @param \Tainacan\Entities\Item[] $items Items.
	 * @return string[]
	 */
	private function titles_of( array $items ) {
		$titles = array();
		foreach ( $items as $item ) {
			$titles[] = $item->get_title();
		}
		return $titles;
	}

	/**
	 * Counts the requests made to the fake provider whose URL contains a string.
	 *
	 * @param string $needle Substring to look for.
	 * @return int
	 */
	private function count_requests_containing( $needle ) {
		$count = 0;
		foreach ( $this->requests as $url ) {
			if ( false !== strpos( $url, $needle ) ) {
				++$count;
			}
		}
		return $count;
	}

	/**
	 * Reads the value of a metadatum for an item.
	 *
	 * @param \Tainacan\Entities\Item      $item      Item.
	 * @param \Tainacan\Entities\Metadatum $metadatum Metadatum.
	 * @return mixed
	 */
	private function value_of( \Tainacan\Entities\Item $item, \Tainacan\Entities\Metadatum $metadatum ) {
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $item, $metadatum );
		return $item_metadata->get_value();
	}

	// ----------------------------------------------------------------------
	// Tests
	// ----------------------------------------------------------------------

	/**
	 * Query strings pasted with the base URL are dropped, so verbs can be appended.
	 */
	public function test_url_query_string_is_discarded() {
		$importer = $this->new_importer();
		$this->assertSame( self::BASE_URL, $importer->get_url() );
	}

	/**
	 * Default mode: one collection per set, records paged through resumption tokens,
	 * deleted records skipped, Dublin Core values mapped.
	 */
	public function test_imports_one_collection_per_set() {
		$this->install_default_provider();
		$importer = $this->new_importer( array( 'using_set' => 'collection' ) );

		$this->run_to_completion( $importer );

		$this->assertSame( array(), $importer->get_error_log(), 'Importer logged errors: ' . implode( ' | ', $importer->get_error_log() ) );

		$pinturas = $this->find_collection( 'Pinturas' );
		$this->assertNotNull( $pinturas, 'Collection for set col1 was not created' );
		$items = $this->items_of( $pinturas );
		$this->assertCount( 3, $items, 'Both pages of set col1 should be imported' );
		$this->assertSame( array( 'Obra 1', 'Obra 2', 'Obra 3' ), $this->titles_of( $items ) );

		$obra1 = $items[0];
		$this->assertSame( "Primeiro parágrafo.\n\nSegundo parágrafo.", $obra1->get_description() );

		$creator = $this->metadatum_named( $pinturas, 'Creator' );
		$this->assertNotNull( $creator, 'Dublin Core metadata should be created on the collection' );
		$this->assertSame( 'Artista Um', $this->value_of( $obra1, $creator ) );

		$subject = $this->metadatum_named( $pinturas, 'Subject' );
		$this->assertSame( 'pintura', $this->value_of( $obra1, $subject ), 'Single-valued metadatum keeps the first value' );

		$esculturas = $this->find_collection( 'Esculturas' );
		$this->assertNotNull( $esculturas, 'Collection for set col2 was not created' );
		$this->assertCount( 1, $this->items_of( $esculturas ), 'Deleted record must not become an item' );

		// The first page of a set is fetched for counting and again for importing; the token page only once.
		$this->assertSame( 1, $this->count_requests_containing( 'resumptionToken=tok-col1' ) );
	}

	/**
	 * Sets imported as terms of a "Sets" taxonomy on a single collection.
	 */
	public function test_imports_sets_as_taxonomy_terms() {
		$this->install_default_provider();
		$importer = $this->new_importer( array( 'using_set' => 'taxonomy' ) );

		$this->run_to_completion( $importer );

		$this->assertSame( array(), $importer->get_error_log(), 'Importer logged errors: ' . implode( ' | ', $importer->get_error_log() ) );

		$collection = $this->find_collection( 'Repositório de Teste' );
		$this->assertNotNull( $collection, 'Collection named after the repository was not created' );
		$items = $this->items_of( $collection );
		$this->assertCount( 4, $items );

		$sets_metadatum = $this->metadatum_named( $collection, 'Sets' );
		$this->assertNotNull( $sets_metadatum, 'Sets taxonomy metadatum was not created' );

		// Items are ordered by title: "Escultura 1" comes before "Obra 1".
		$terms = $this->value_of( $items[0], $sets_metadatum );
		$this->assertCount( 1, $terms );
		$this->assertSame( 'Esculturas', $terms[0]->get_name() );

		$terms = $this->value_of( $items[1], $sets_metadatum );
		$this->assertSame( 'Pinturas', $terms[0]->get_name() );
	}

	/**
	 * Providers without a set hierarchy get a single collection with every record.
	 */
	public function test_provider_without_sets_imports_into_a_single_collection() {
		$this->install_default_provider();
		$this->responses['ListSets'] = $this->oai_error( 'ListSets', 'noSetHierarchy' );
		$importer                    = $this->new_importer();

		$this->run_to_completion( $importer );

		$this->assertSame( array(), $importer->get_error_log(), 'Importer logged errors: ' . implode( ' | ', $importer->get_error_log() ) );
		$collection = $this->find_collection( 'Repositório de Teste' );
		$this->assertNotNull( $collection );
		$this->assertCount( 4, $this->items_of( $collection ) );
		$this->assertNull( $this->find_collection( 'Pinturas' ) );
	}

	/**
	 * A provider that answers garbage aborts the import with an error log instead of a fatal.
	 */
	public function test_invalid_xml_aborts_with_error_log() {
		$this->install_default_provider();
		$this->responses['ListSets'] = '<html>Service unavailable';
		$importer                    = $this->new_importer();

		$this->run_to_completion( $importer );

		$this->assertTrue( $importer->get_abort() );
		$this->assertNotEmpty( $importer->get_error_log() );
		$this->assertNull( $this->find_collection( 'Pinturas' ) );
	}

	/**
	 * HTTP 5xx answers are retried, then reported; 4xx answers are not retried.
	 */
	public function test_http_errors_are_retried_and_reported() {
		$this->install_default_provider();
		$this->responses['ListSets'] = $this->http_response( 503, '' );
		$importer                    = $this->new_importer();

		$this->run_to_completion( $importer );

		$this->assertTrue( $importer->get_abort() );
		$this->assertSame( \Tainacan\Importer\Oaipmh_Importer::MAX_HTTP_ATTEMPTS, $this->count_requests_containing( 'verb=ListSets' ) );

		$this->requests              = array();
		$this->responses['ListSets'] = $this->http_response( 404, '' );
		$importer                    = $this->new_importer();
		$this->run_to_completion( $importer );

		$this->assertSame( 1, $this->count_requests_containing( 'verb=ListSets' ), 'Client errors must not be retried' );
	}
}
