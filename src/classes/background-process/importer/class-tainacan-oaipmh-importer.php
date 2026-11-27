<?php
namespace Tainacan\Importer;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan;
use Tainacan\Entities;

/**
 * Imports the records of an OAI-PMH 2.0 data provider (`oai_dc` format) as items of ONE
 * collection chosen by the user, through the standard importer flow: the user picks the
 * destination collection and maps the Dublin Core elements to its metadata (manual collection
 * and manual mapping, as in the CSV importer). The background job never creates collections.
 *
 * Two sources are possible, chosen on the options form:
 *
 *  - "One set": a single `ListRecords` harvest restricted to the set spec typed by the user
 *    (or taken from a pasted URL that has `set=`). `ListSets` is never called in this mode.
 *  - "Whole repository": a single `ListRecords` harvest with no `set` argument. Every setSpec
 *    of a record header becomes a term of one flat "OAI-PMH sets" taxonomy, through a multiple
 *    taxonomy metadatum created in the destination collection. The human names of the sets only
 *    exist in `ListSets`, which is walked first, one page per iteration, solely to fill a
 *    spec => name map.
 *
 * Each iteration of the "Import Items" step fetches ONE `ListRecords` page and inserts its records.
 * `total_items` of the collection counts pages and grows while the provider returns resumption
 * tokens. When the token ends, or a page fails for good, the collection stops.
 */
class Oaipmh_Importer extends Importer {

	const OAI_DC_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
	const DC_NAMESPACE     = 'http://purl.org/dc/elements/1.1/';
	const METADATA_PREFIX  = 'oai_dc';

	/**
	 * Source field holding the OAI header identifier of a record.
	 */
	const IDENTIFIER_FIELD = 'oai:identifier';

	/**
	 * Internal source field holding the set names of a record (whole repository mode).
	 */
	const SETS_FIELD = 'oai:setSpec';

	/**
	 * Item post meta keys: the OAI identifier of the source record and the import run that
	 * inserted it (used to skip records already inserted when a page is processed again).
	 */
	const ITEM_IDENTIFIER_META = '_tainacan_oaipmh_identifier';
	const ITEM_RUN_META        = '_tainacan_oaipmh_run';

	/**
	 * Collection post meta key that remembers the sets metadatum created in it.
	 */
	const COLLECTION_SETS_META = '_tainacan_oaipmh_sets_metadatum';

	/**
	 * Attempts of the same page (transport errors, HTTP 5xx, invalid XML) before it fails for good.
	 */
	const MAX_PAGE_ATTEMPTS = 3;

	/**
	 * Flow-control waits (HTTP 503/429 with Retry-After) accepted for the same page.
	 */
	const MAX_PAGE_WAITS = 20;

	/**
	 * Longest Retry-After honored, in seconds.
	 */
	const MAX_RETRY_AFTER = 3600;

	/**
	 * Longest pause inside one iteration, in seconds. Longer waits span several iterations,
	 * so the background process keeps recording heartbeats.
	 */
	const MAX_SLEEP_PER_ITERATION = 5;

	/**
	 * The fifteen Dublin Core elements, in the order of the specification.
	 */
	const DC_ELEMENTS = [
		'dc:title',
		'dc:creator',
		'dc:subject',
		'dc:description',
		'dc:publisher',
		'dc:contributor',
		'dc:date',
		'dc:type',
		'dc:format',
		'dc:identifier',
		'dc:source',
		'dc:language',
		'dc:relation',
		'dc:coverage',
		'dc:rights',
	];

	protected $steps = [
		[
			'name' => 'Read set names',
			'progress_label' => 'Reading set names',
			'callback' => 'read_set_names'
		],
		[
			'name' => 'Import Items',
			'progress_label' => 'Importing Items',
			'callback' => 'process_collections'
		],
	];

	/**
	 * Metadatum entities of the mapping, cached while one page is inserted.
	 *
	 * @var array<int, \Tainacan\Entities\Metadatum|false>
	 */
	private $metadata_cache = [];

	/**
	 * OAI identifier of the record being inserted, read by after_inserted_item().
	 *
	 * @var string
	 */
	private $current_record_identifier = '';

	public function __construct( $attributes = array() ) {
		parent::__construct( $attributes );

		$this->set_default_options([
			'source'   => 'set',
			'set_spec' => '',
		]);

		$this->remove_import_method('file');
		$this->add_import_method('url');
	}

	// -------------------------------------------------------------------------
	// Setup (synchronous, from the importer form)
	// -------------------------------------------------------------------------

	/**
	 * Stores the OAI-PMH base URL.
	 *
	 * Only scheme, host, port and path are kept: userinfo, query string and fragment are dropped,
	 * so the verbs built by this class are always sent (a fragment would otherwise swallow them).
	 * A `set` argument found in the query string is kept as the set spec to use when the options
	 * form leaves it empty.
	 *
	 * The URL typed by the user is used as is; it is never replaced by the `baseURL` advertised
	 * by `Identify`, which may point to another host.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function set_url( $url ) {
		if ( ! is_string( $url ) ) {
			return false;
		}

		$parts = wp_parse_url( trim( $url ) );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || empty( $parts['scheme'] ) || ! in_array( strtolower( $parts['scheme'] ), [ 'http', 'https' ], true ) ) {
			return false;
		}

		if ( ! empty( $parts['query'] ) ) {
			$query = [];
			parse_str( $parts['query'], $query );
			if ( isset( $query['set'] ) && is_string( $query['set'] ) && '' !== trim( $query['set'] ) ) {
				$this->add_transient( 'url_set_spec', trim( $query['set'] ) );
			}
		}

		$clean = strtolower( $parts['scheme'] ) . '://' . $parts['host'];
		if ( ! empty( $parts['port'] ) ) {
			$clean .= ':' . (int) $parts['port'];
		}
		if ( ! empty( $parts['path'] ) ) {
			$clean .= $parts['path'];
		}

		return parent::set_url( $clean );
	}

	/**
	 * The fifteen Dublin Core elements plus the OAI header identifier, offered on the mapping screen.
	 *
	 * @return string[]
	 */
	public function get_source_metadata() {
		return array_merge( self::DC_ELEMENTS, [ self::IDENTIFIER_FIELD ] );
	}

	/**
	 * Stores the destination collection and its mapping.
	 *
	 * Mapping keys that ask for a new metadatum (`create_metadata*`, `create_repository_metadata*`)
	 * create one named after the Dublin Core element. New metadata are multiple, since OAI records
	 * repeat elements (subjects, dates, identifiers...). `total_items` counts `ListRecords` pages:
	 * it starts at 1 and grows while the provider returns resumption tokens.
	 *
	 * @param array $collection
	 * @return bool
	 */
	public function add_collection( array $collection ) {
		if ( ! isset( $collection['id'] ) ) {
			return false;
		}

		$mapping = isset( $collection['mapping'] ) && is_array( $collection['mapping'] ) ? $collection['mapping'] : [];

		foreach ( $mapping as $metadatum_id => $source_field ) {
			if ( is_numeric( $metadatum_id ) ) {
				continue;
			}
			unset( $mapping[ $metadatum_id ] );

			$key = (string) $metadatum_id;
			$is_create_collection = 0 === strpos( $key, 'create_metadata' );
			$is_create_repository = 0 === strpos( $key, 'create_repository_metadata' );
			if ( ( ! $is_create_collection && ! $is_create_repository ) || ! in_array( $source_field, $this->get_source_metadata(), true ) ) {
				continue;
			}

			$metadatum = $this->create_new_metadata(
				$this->describe_source_field( $source_field ),
				$is_create_repository ? 'default' : $collection['id']
			);
			if ( ! $metadatum instanceof Entities\Metadatum ) {
				$this->add_error_log( __( 'Error while creating metadatum, please review the metadatum description.', 'tainacan' ) );
				return false;
			}
			$mapping[ $metadatum->get_id() ] = $source_field;
		}

		$collection['mapping'] = $mapping;
		$collection['total_items'] = 1;

		$this->save_mapping( $collection['id'], $mapping );
		$this->remove_collection( $collection['id'] );
		$this->collections[] = $collection;
		// remove_collection() unsets an index; the queue is read from index 0.
		$this->collections = array_values( $this->collections );

		// Identifies this import run on the inserted items. Created here, before the background
		// process starts, so a retry from the first saved state still recognizes its own items.
		if ( null === $this->get_transient('run_id') ) {
			$this->add_transient( 'run_id', uniqid( 'oai', true ) );
		}
		return true;
	}

	/**
	 * @param int   $collection_id
	 * @param array $mapping metadatum ID => source field
	 */
	public function save_mapping( $collection_id, $mapping ) {
		update_post_meta( $collection_id, 'metadata_mapping', $mapping );
	}

	/**
	 * @param int $collection_id
	 * @return array|false
	 */
	public function get_mapping( $collection_id ) {
		$mapping = get_post_meta( $collection_id, 'metadata_mapping', true );
		return $mapping ? $mapping : false;
	}

	/**
	 * Renders the options form: the source of the records and the set spec.
	 *
	 * @return string
	 */
	public function options_form() {
		$source = $this->get_option('source');
		ob_start();
		?>
		<div class="columns is-multiline">
			<div class="column is-12">
				<div class="field">
					<label class="label" style="position: relative">
						<?php esc_html_e('Records to import', 'tainacan'); ?>
						<span class="help-wrapper">
							<a class="help-button">
								<span class="icon is-small">
									<i class="tainacan-icon tainacan-icon-help" ></i>
								</span>
							</a>
							<div class="help-tooltip">
								<div class="help-tooltip-header">
									<h5><?php esc_html_e('Records to import', 'tainacan'); ?></h5>
								</div>
								<div class="help-tooltip-body">
									<p><?php esc_html_e('Every record is imported as one item of the selected collection. Import a single set, or the whole repository: in that case the sets of each record are stored as terms of an "OAI-PMH sets" taxonomy metadatum created in the collection.', 'tainacan'); ?></p>
								</div>
							</div>
						</span>
					</label>
					<div class="control">
						<label class="b-radio radio">
							<input type="radio" name="source" value="set" <?php checked( 'repository' !== $source ); ?>>
							<span class="check"></span>
							<span class="control-label"><?php esc_html_e('One set', 'tainacan'); ?></span>
						</label>
						<label class="b-radio radio">
							<input type="radio" name="source" value="repository" <?php checked( 'repository', $source ); ?>>
							<span class="check"></span>
							<span class="control-label"><?php esc_html_e('The whole repository', 'tainacan'); ?></span>
						</label>
					</div>
				</div>
			</div>
			<div class="column is-12-tablet is-6-desktop">
				<div class="field">
					<label class="label" style="position: relative">
						<?php esc_html_e('Set spec', 'tainacan'); ?>
						<span class="help-wrapper">
							<a class="help-button">
								<span class="icon is-small">
									<i class="tainacan-icon tainacan-icon-help" ></i>
								</span>
							</a>
							<div class="help-tooltip">
								<div class="help-tooltip-header">
									<h5><?php esc_html_e('Set spec', 'tainacan'); ?></h5>
								</div>
								<div class="help-tooltip-body">
									<p><?php esc_html_e('Required when importing one set. It is the value of the "set" argument of a ListRecords URL, for example col_123456789_5061. A URL that contains "set=" is also accepted.', 'tainacan'); ?></p>
								</div>
							</div>
						</span>
					</label>
					<div class="control is-clearfix">
						<input class="input" type="text" name="set_spec" value="<?php echo esc_attr( $this->get_option('set_spec') ); ?>">
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// Step 1: set names (whole repository only)
	// -------------------------------------------------------------------------

	/**
	 * Walks `ListSets`, one page per iteration, to fill the spec => name map used for the set
	 * terms. Only runs when importing the whole repository; it never creates collections.
	 * Failing to read the names is not fatal: the raw specs are used instead.
	 *
	 * @return int|false A positive number while there are pages left, false when done.
	 */
	public function read_set_names() {
		if ( ! $this->is_repository_mode() ) {
			return false;
		}

		if ( null === $this->get_transient('set_names') ) {
			$this->add_transient( 'set_names', [] );
			$this->prepare_sets_metadatum();
		}

		if ( ! $this->get_transient('sets_metadatum_id') ) {
			return false;
		}

		if ( $this->wait_for_provider() ) {
			return $this->get_in_step_count() + 1;
		}

		$token  = (string) $this->get_transient('list_sets_token');
		$params = '' !== $token ? [ 'verb' => 'ListSets', 'resumptionToken' => $token ] : [ 'verb' => 'ListSets' ];
		$response = $this->fetch_oai( $params );

		if ( 'wait' === $response['status'] ) {
			return $this->get_in_step_count() + 1;
		}

		if ( 'ok' === $response['status'] ) {
			$xml = $response['xml'];

			if ( isset( $xml->error ) ) {
				if ( 'noSetHierarchy' !== (string) $xml->error['code'] ) {
					$this->add_error_log( sprintf( 'Could not read the set names (OAI-PMH error "%s": %s). The set specs are used as term names.', (string) $xml->error['code'], trim( (string) $xml->error ) ) );
				}
				return $this->finish_set_names();
			}

			if ( isset( $xml->ListSets ) ) {
				$this->reset_attempts();
				$names = $this->get_transient('set_names');
				foreach ( $xml->ListSets->set as $set ) {
					$spec = trim( (string) $set->setSpec );
					$name = trim( (string) $set->setName );
					if ( '' !== $spec ) {
						$names[ $spec ] = '' !== $name ? $name : $spec;
					}
				}
				$this->add_transient( 'set_names', $names );

				$token = isset( $xml->ListSets->resumptionToken ) ? trim( (string) $xml->ListSets->resumptionToken ) : '';
				if ( '' === $token ) {
					return $this->finish_set_names();
				}
				$this->add_transient( 'list_sets_token', $token );
				return $this->get_in_step_count() + 1;
			}

			$response = [ 'status' => 'failed', 'message' => 'Unexpected response: no ListSets element found' ];
		}

		if ( $this->register_failed_attempt( 'failed' === $response['status'] ) ) {
			return $this->get_in_step_count() + 1;
		}

		$this->add_error_log( sprintf( 'Could not read the set names (%s). The set specs are used as term names.', $response['message'] ) );
		return $this->finish_set_names();
	}

	/**
	 * Ends the set names step: sets that share a name get their spec appended, so two different
	 * sets never collapse into the same term.
	 *
	 * @return false
	 */
	protected function finish_set_names() {
		$names = (array) $this->get_transient('set_names');
		$count = array_count_values( $names );
		foreach ( $names as $spec => $name ) {
			if ( $count[ $name ] > 1 ) {
				$names[ $spec ] = sprintf( '%s (%s)', $name, $spec );
			}
		}
		$this->add_transient( 'set_names', $names );
		$this->delete_transient('list_sets_token');
		$this->reset_attempts();
		return false;
	}

	/**
	 * Creates (or reuses) the flat "OAI-PMH sets" taxonomy and its multiple metadatum in the
	 * destination collection, and maps it to the internal sets field.
	 */
	protected function prepare_sets_metadatum() {
		$collections = $this->get_collections();
		$position = $this->get_current_collection();
		if ( empty( $collections[ $position ]['id'] ) ) {
			return;
		}
		$collection_id = (int) $collections[ $position ]['id'];

		$metadata_repo = \Tainacan\Repositories\Metadata::get_instance();
		$metadatum = $metadata_repo->fetch( (int) get_post_meta( $collection_id, self::COLLECTION_SETS_META, true ) );

		if ( ! $metadatum instanceof Entities\Metadatum || (int) $metadatum->get_collection_id() !== $collection_id ) {
			$name = __( 'OAI-PMH sets', 'tainacan' );

			$taxonomy = new Entities\Taxonomy();
			$taxonomy->set_name( $name );
			$taxonomy->set_allow_insert('yes');
			$taxonomy->set_status('publish');
			if ( ! $taxonomy->validate() ) {
				$this->add_error_log( 'Error creating the taxonomy for the sets; they will not be imported.' );
				$this->add_error_log( $taxonomy->get_errors() );
				return;
			}
			$taxonomy = \Tainacan\Repositories\Taxonomies::get_instance()->insert( $taxonomy );

			$metadatum = new Entities\Metadatum();
			$metadatum->set_name( $name );
			$metadatum->set_metadata_type('Tainacan\Metadata_Types\Taxonomy');
			$metadatum->set_metadata_type_options([
				'taxonomy_id'     => $taxonomy->get_id(),
				'allow_new_terms' => 'yes',
				'input_type'      => 'tainacan-taxonomy-checkbox',
			]);
			$metadatum->set_multiple('yes');
			$metadatum->set_collection_id( $collection_id );
			$metadatum->set_status('publish');
			if ( ! $metadatum->validate() ) {
				$this->add_error_log( 'Error creating the metadatum for the sets; they will not be imported.' );
				$this->add_error_log( $metadatum->get_errors() );
				return;
			}
			$metadatum = $metadata_repo->insert( $metadatum );
			update_post_meta( $collection_id, self::COLLECTION_SETS_META, $metadatum->get_id() );
			$this->add_log( 'Metadatum created for the sets: ' . $metadatum->get_name() );
		}

		$collections[ $position ]['mapping'][ $metadatum->get_id() ] = self::SETS_FIELD;
		$this->set_collections( $collections );
		$this->add_transient( 'sets_metadatum_id', $metadatum->get_id() );
	}

	// -------------------------------------------------------------------------
	// Step 2: records
	// -------------------------------------------------------------------------

	/**
	 * Fetches ONE `ListRecords` page and inserts its records.
	 *
	 * The page index (current collection item) only advances after a page was read. A page that
	 * must wait (Retry-After) or be retried keeps its index; a page that fails for good, an OAI
	 * error, or the end of the resumption tokens stops the collection.
	 *
	 * @return int|false
	 */
	public function process_collections() {
		$collections = $this->get_collections();
		$definition  = isset( $collections[ $this->get_current_collection() ] ) ? $collections[ $this->get_current_collection() ] : false;
		if ( ! is_array( $definition ) || empty( $definition['id'] ) ) {
			$this->add_error_log('Collection misconfigured');
			return false;
		}

		if ( ! $this->get_url() ) {
			$this->add_error_log( __( 'No OAI-PMH base URL was informed.', 'tainacan' ) );
			return false;
		}

		if ( ! $this->is_repository_mode() && '' === $this->get_set_spec() ) {
			$this->add_error_log( __( 'No set spec was informed. Type the set spec, or choose to import the whole repository.', 'tainacan' ) );
			return false;
		}

		if ( null === $this->get_transient('run_id') ) {
			$this->add_transient( 'run_id', uniqid( 'oai', true ) );
		}

		$page = (int) $this->get_current_collection_item();
		$this->add_log( sprintf( 'Processing page %d', $page + 1 ) );

		$result = $this->process_item( $page, $definition );

		if ( 'retry' === $result['status'] ) {
			return max( 1, $page );
		}

		if ( 'ok' === $result['status'] ) {
			$this->insert( $result['records'], $this->get_current_collection() );

			if ( $result['has_more'] ) {
				$this->set_current_collection_item( $page + 1 );
				$this->ensure_total_pages( $page + 2, $result['estimated_pages'] );
				return $page + 1;
			}
		}

		$this->ensure_total_pages( $page + 1, 0, true );
		return $this->next_collection();
	}

	/**
	 * Fetches and parses one `ListRecords` page.
	 *
	 * @param int   $index                 Page index (0 = first page).
	 * @param array $collection_definition Entry of the collections queue.
	 * @return array {
	 *     @type string $status          'ok', 'retry' (same page again later) or 'stop' (end of the collection).
	 *     @type array  $records         Parsed records ('ok').
	 *     @type bool   $has_more        Whether a resumption token was returned ('ok').
	 *     @type int    $estimated_pages Pages announced through completeListSize, or 0 ('ok').
	 * }
	 */
	public function process_item( $index, $collection_definition ) {
		if ( $this->wait_for_provider() ) {
			return [ 'status' => 'retry' ];
		}

		if ( 0 === (int) $index ) {
			$params = [
				'verb'           => 'ListRecords',
				'metadataPrefix' => self::METADATA_PREFIX,
			];
			if ( ! $this->is_repository_mode() ) {
				$params['set'] = $this->get_set_spec();
			}
		} else {
			$token = (string) $this->get_transient('resumption_token');
			if ( '' === $token ) {
				// The token is stored with the page that announced it; without it there is nothing left to request.
				$this->add_error_log( sprintf( 'No resumption token for page %d; stopping.', (int) $index + 1 ) );
				return [ 'status' => 'stop' ];
			}
			$params = [
				'verb'            => 'ListRecords',
				'resumptionToken' => $token,
			];
		}

		$response = $this->fetch_oai( $params );

		if ( 'wait' === $response['status'] ) {
			return [ 'status' => 'retry' ];
		}

		if ( 'ok' === $response['status'] ) {
			$xml = $response['xml'];

			if ( isset( $xml->error ) ) {
				$code = (string) $xml->error['code'];
				$this->reset_attempts();
				if ( 'noRecordsMatch' === $code ) {
					$this->add_log('The provider has no records for this request (noRecordsMatch)');
				} else {
					// badResumptionToken, badArgument, cannotDisseminateFormat...: retrying the same request cannot help.
					$this->add_error_log( sprintf( 'OAI-PMH error "%s" on page %d: %s. Stopping.', $code, (int) $index + 1, trim( (string) $xml->error ) ) );
				}
				$this->delete_transient('resumption_token');
				return [ 'status' => 'stop' ];
			}

			if ( isset( $xml->ListRecords ) ) {
				$this->reset_attempts();

				$token = isset( $xml->ListRecords->resumptionToken ) ? trim( (string) $xml->ListRecords->resumptionToken ) : '';
				$estimated_pages = 0;
				if ( '' !== $token ) {
					$this->add_transient( 'resumption_token', $token );
					$complete_list_size = (int) $xml->ListRecords->resumptionToken['completeListSize'];
					$records_in_page    = count( $xml->ListRecords->record );
					if ( $complete_list_size > 0 && $records_in_page > 0 ) {
						$estimated_pages = (int) ceil( $complete_list_size / $records_in_page );
					}
				} else {
					$this->delete_transient('resumption_token');
				}

				$records = [];
				foreach ( $xml->ListRecords->record as $record ) {
					$parsed = $this->parse_record( $record );
					if ( $parsed ) {
						$records[] = $parsed;
					}
				}

				return [
					'status'          => 'ok',
					'records'         => $records,
					'has_more'        => '' !== $token,
					'estimated_pages' => $estimated_pages,
				];
			}

			$response = [ 'status' => 'failed', 'message' => 'Unexpected response: no ListRecords element found' ];
		}

		if ( $this->register_failed_attempt( 'failed' === $response['status'] ) ) {
			return [ 'status' => 'retry' ];
		}

		$this->add_error_log( sprintf( 'Page %d failed for good (%s). Stopping.', (int) $index + 1, $response['message'] ) );
		$this->delete_transient('resumption_token');
		return [ 'status' => 'stop' ];
	}

	/**
	 * Inserts the records of one page through the standard insert(), so every item is published
	 * only after its metadata are saved and after_inserted_item() runs.
	 *
	 * Records already inserted by this import run (a page processed again after a retry) are skipped.
	 *
	 * @param array $records          Parsed records of the page.
	 * @param int   $collection_index Index in the collections queue.
	 * @return Entities\Item|false The last inserted item.
	 */
	public function insert( $records, $collection_index ) {
		$collections = $this->get_collections();
		$definition  = isset( $collections[ $collection_index ] ) ? $collections[ $collection_index ] : false;
		if ( ! is_array( $records ) || ! is_array( $definition ) || empty( $definition['id'] ) ) {
			return false;
		}

		$mapping = isset( $definition['mapping'] ) && is_array( $definition['mapping'] ) ? $definition['mapping'] : [];
		$already_inserted = $this->get_identifiers_inserted_by_this_run( (int) $definition['id'], array_column( $records, 'identifier' ) );

		$this->metadata_cache = [];
		$last_inserted = false;

		foreach ( $records as $record ) {
			if ( '' !== $record['identifier'] && isset( $already_inserted[ $record['identifier'] ] ) ) {
				$this->add_log( 'Skipping record already inserted by this import: ' . $record['identifier'] );
				continue;
			}

			$processed_item = $this->values_for_mapping( $record, $mapping );
			if ( empty( $processed_item ) ) {
				$this->add_error_log( 'No mapped value for record ' . $record['identifier'] );
				continue;
			}

			$this->current_record_identifier = $record['identifier'];
			$item = parent::insert( $processed_item, $collection_index );
			$this->current_record_identifier = '';

			if ( $item ) {
				$last_inserted = $item;
			} else {
				$this->add_error_log( 'Record not imported: ' . $record['identifier'] );
			}
		}

		// Do not carry entity objects into the serialized session.
		$this->metadata_cache = [];
		return $last_inserted;
	}

	/**
	 * Stores the OAI identifier and the import run on the item, once it is published.
	 *
	 * @param Entities\Item $inserted_item
	 * @param int           $collection_index
	 */
	public function after_inserted_item( $inserted_item, $collection_index ) {
		if ( $inserted_item instanceof Entities\Item && '' !== $this->current_record_identifier ) {
			update_post_meta( $inserted_item->get_id(), self::ITEM_IDENTIFIER_META, $this->current_record_identifier );
			update_post_meta( $inserted_item->get_id(), self::ITEM_RUN_META, (string) $this->get_transient('run_id') );
		}
	}

	/**
	 * Builds the source field => value array that the standard insert() expects.
	 *
	 * Every repeated element is kept: a multiple metadatum receives the whole list. A single-valued
	 * metadatum receives the first value, except the core description, which keeps every
	 * paragraph; dropped values are reported in the log.
	 *
	 * @param array $record  Output of parse_record().
	 * @param array $mapping metadatum ID => source field.
	 * @return array
	 */
	protected function values_for_mapping( array $record, array $mapping ) {
		$fields = $record['metadata'];
		$fields[ self::IDENTIFIER_FIELD ] = '' !== $record['identifier'] ? [ $record['identifier'] ] : [];
		$fields[ self::SETS_FIELD ] = $this->set_names_of( $record['sets'] );

		$processed_item = [];
		foreach ( $mapping as $metadatum_id => $source_field ) {
			if ( ! is_string( $source_field ) || isset( $processed_item[ $source_field ] ) || empty( $fields[ $source_field ] ) ) {
				continue;
			}

			// insert() resolves the metadatum of a source field with array_search(), so the same is done here.
			$metadatum = $this->get_mapped_metadatum( (int) array_search( $source_field, $mapping, true ) );
			$values = array_values( $fields[ $source_field ] );

			if ( ! $metadatum || $metadatum->is_multiple() || 1 === count( $values ) ) {
				$processed_item[ $source_field ] = $metadatum && $metadatum->is_multiple() ? $values : $values[0];
				continue;
			}

			if ( 'Tainacan\Metadata_Types\Core_Description' === $metadatum->get_metadata_type() ) {
				$processed_item[ $source_field ] = implode( "\n\n", $values );
				continue;
			}

			$processed_item[ $source_field ] = $values[0];
			$this->add_log( sprintf(
				'Record %1$s has %2$d values for %3$s, but "%4$s" accepts one: only the first was kept. Map it to a multiple metadatum to keep them all.',
				$record['identifier'],
				count( $values ),
				$source_field,
				$metadatum->get_name()
			) );
		}

		return $processed_item;
	}

	/**
	 * Term names for the setSpecs of a record (whole repository mode).
	 *
	 * @param string[] $set_specs
	 * @return string[]
	 */
	protected function set_names_of( array $set_specs ) {
		if ( ! $this->is_repository_mode() ) {
			return [];
		}
		$names = (array) $this->get_transient('set_names');
		$terms = [];
		foreach ( $set_specs as $spec ) {
			$terms[] = isset( $names[ $spec ] ) ? $names[ $spec ] : $spec;
		}
		return array_values( array_unique( $terms ) );
	}

	/**
	 * Which of the given OAI identifiers were already inserted in the collection by this import run.
	 *
	 * @param int      $collection_id
	 * @param string[] $identifiers
	 * @return array<string, true>
	 */
	protected function get_identifiers_inserted_by_this_run( $collection_id, array $identifiers ) {
		$identifiers = array_values( array_filter( array_map( 'strval', $identifiers ), 'strlen' ) );
		$collection  = \Tainacan\Repositories\Collections::get_instance()->fetch( $collection_id );
		if ( empty( $identifiers ) || ! $collection instanceof Entities\Collection ) {
			return [];
		}

		$item_ids = get_posts([
			'post_type'        => $collection->get_db_identifier(),
			'post_status'      => 'any',
			'posts_per_page'   => -1,
			'fields'           => 'ids',
			'suppress_filters' => true,
			'no_found_rows'    => true,
			'meta_query'       => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- bounded by the records of one page.
				[
					'key'   => self::ITEM_RUN_META,
					'value' => (string) $this->get_transient('run_id'),
				],
				[
					'key'     => self::ITEM_IDENTIFIER_META,
					'value'   => $identifiers,
					'compare' => 'IN',
				],
			],
		]);

		$found = [];
		foreach ( $item_ids as $item_id ) {
			$found[ (string) get_post_meta( $item_id, self::ITEM_IDENTIFIER_META, true ) ] = true;
		}
		return $found;
	}

	/**
	 * Raises (or, at the end, fixes) the number of pages of the current collection, for the progress bar.
	 *
	 * @param int  $minimum_pages   Pages known to exist.
	 * @param int  $estimated_pages Pages announced by the provider, or 0.
	 * @param bool $is_final        Whether $minimum_pages is the final number.
	 */
	protected function ensure_total_pages( $minimum_pages, $estimated_pages = 0, $is_final = false ) {
		$collections = $this->get_collections();
		$position = $this->get_current_collection();
		if ( ! isset( $collections[ $position ] ) ) {
			return;
		}
		$current = isset( $collections[ $position ]['total_items'] ) ? (int) $collections[ $position ]['total_items'] : 0;
		$total = $is_final ? (int) $minimum_pages : max( $current, (int) $minimum_pages, (int) $estimated_pages );
		if ( $total !== $current ) {
			$collections[ $position ]['total_items'] = $total;
			$this->set_collections( $collections );
		}
	}

	// -------------------------------------------------------------------------
	// Records
	// -------------------------------------------------------------------------

	/**
	 * Converts one `<record>` element into an array.
	 *
	 * Deleted records (`<header status="deleted">`) and records without metadata are skipped.
	 * Dublin Core elements are keyed as `dc:<element>` (attributes such as xml:lang are ignored)
	 * and always hold the list of their values, in the order of the record.
	 *
	 * @param \SimpleXMLElement $record
	 * @return array|null ['identifier' => string, 'sets' => string[], 'metadata' => array<string, string[]>]
	 */
	protected function parse_record( \SimpleXMLElement $record ) {
		if ( ! isset( $record->header ) ) {
			return null;
		}
		$header = $record->header;
		$identifier = trim( (string) $header->identifier );

		if ( isset( $header['status'] ) && 'deleted' === (string) $header['status'] ) {
			$this->add_log( 'Skipping deleted record ' . $identifier );
			return null;
		}

		if ( ! isset( $record->metadata ) ) {
			$this->add_log( 'Skipping record without metadata ' . $identifier );
			return null;
		}

		$sets = [];
		if ( isset( $header->setSpec ) ) {
			foreach ( $header->setSpec as $set_spec ) {
				$spec = trim( (string) $set_spec );
				if ( '' !== $spec ) {
					$sets[] = $spec;
				}
			}
		}

		$metadata = [];
		$dc_wrapper = $record->metadata->children( self::OAI_DC_NAMESPACE );
		if ( isset( $dc_wrapper->dc ) ) {
			$this->collect_dc_elements( $metadata, $dc_wrapper->dc->children( self::DC_NAMESPACE ) );
		} else {
			// Lenient fallback for providers that omit the oai_dc wrapper.
			$this->collect_dc_elements( $metadata, $record->metadata->children( self::DC_NAMESPACE ) );
			foreach ( $record->metadata->children() as $child ) {
				$this->collect_dc_elements( $metadata, $child->children( self::DC_NAMESPACE ) );
			}
		}

		if ( empty( $metadata ) ) {
			$this->add_log( 'Skipping record without Dublin Core elements ' . $identifier );
			return null;
		}

		return [
			'identifier' => $identifier,
			'sets'       => array_values( array_unique( $sets ) ),
			'metadata'   => $metadata,
		];
	}

	/**
	 * Appends non-empty element values to $metadata, keyed as `dc:<element>`.
	 *
	 * @param array             $metadata Accumulator (by reference).
	 * @param \SimpleXMLElement $elements
	 */
	protected function collect_dc_elements( array &$metadata, $elements ) {
		if ( ! $elements ) {
			return;
		}
		foreach ( $elements as $element ) {
			$value = trim( (string) $element );
			if ( '' === $value ) {
				continue;
			}
			$metadata[ 'dc:' . $element->getName() ][] = $value;
		}
	}

	/**
	 * Fetches a metadatum of the mapping once per page.
	 *
	 * @param int $metadatum_id
	 * @return Entities\Metadatum|false
	 */
	protected function get_mapped_metadatum( $metadatum_id ) {
		if ( ! array_key_exists( $metadatum_id, $this->metadata_cache ) ) {
			$metadatum = \Tainacan\Repositories\Metadata::get_instance()->fetch( $metadatum_id );
			$this->metadata_cache[ $metadatum_id ] = $metadatum instanceof Entities\Metadatum ? $metadatum : false;
		}
		return $this->metadata_cache[ $metadatum_id ];
	}

	/**
	 * Description passed to create_new_metadata() for a source field: the Dublin Core label,
	 * a text type, and multiple values.
	 *
	 * @param string $source_field
	 * @return string
	 */
	protected function describe_source_field( $source_field ) {
		if ( self::IDENTIFIER_FIELD === $source_field ) {
			return __( 'OAI-PMH identifier', 'tainacan' ) . '|text';
		}

		$label = ucfirst( substr( $source_field, 3 ) );
		$mappers = \Tainacan\Mappers_Handler::get_instance();
		$mapper_class = $mappers->check_class_name( 'dublin-core', true, $mappers::MAPPER_CLASS_PREFIX );
		if ( $mapper_class ) {
			$mapper = new $mapper_class();
			if ( isset( $mapper->metadata[ $source_field ]['label'] ) ) {
				$label = $mapper->metadata[ $source_field ]['label'];
			}
		}
		return str_replace( '|', ' ', $label ) . '|text|multiple';
	}

	// -------------------------------------------------------------------------
	// Options
	// -------------------------------------------------------------------------

	/**
	 * @return bool
	 */
	protected function is_repository_mode() {
		return 'repository' === $this->get_option('source');
	}

	/**
	 * The set spec typed on the form (a pasted URL with `set=` is accepted), or the `set` argument
	 * of the URL informed as source.
	 *
	 * @return string
	 */
	protected function get_set_spec() {
		$spec = trim( (string) $this->get_option('set_spec') );

		if ( false !== strpos( $spec, 'set=' ) ) {
			$query = [];
			parse_str( (string) wp_parse_url( $spec, PHP_URL_QUERY ), $query );
			if ( empty( $query ) ) {
				parse_str( ltrim( $spec, '?' ), $query );
			}
			$spec = isset( $query['set'] ) && is_string( $query['set'] ) ? trim( $query['set'] ) : '';
		}

		if ( '' === $spec ) {
			$spec = (string) $this->get_transient('url_set_spec');
		}
		return $spec;
	}

	// -------------------------------------------------------------------------
	// HTTP / XML
	// -------------------------------------------------------------------------

	/**
	 * Requests one OAI-PMH verb and parses the answer.
	 *
	 * @param array $params Query arguments (verb, metadataPrefix, set, resumptionToken).
	 * @return array {
	 *     @type string            $status  'ok' (with xml), 'wait' (flow control, waiting scheduled),
	 *                                      'failed' (may be retried) or 'fatal' (retrying cannot help).
	 *     @type \SimpleXMLElement $xml
	 *     @type string            $message
	 * }
	 */
	protected function fetch_oai( array $params ) {
		$url = $this->get_url() . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
		$this->add_log( 'Fetching ' . $url );

		$response = wp_safe_remote_get( $url, [
			'timeout'             => 30,
			'redirection'         => 3,
			'limit_response_size' => $this->get_max_response_size(),
			/**
			 * Filters whether the OAI-PMH importer verifies SSL certificates of the provider.
			 *
			 * @param bool   $sslverify Default true.
			 * @param string $url       Request URL.
			 */
			'sslverify'           => (bool) apply_filters( 'tainacan-oaipmh-importer-sslverify', true, $url ),
			'user-agent'          => 'Tainacan/' . TAINACAN_VERSION . ' OAI-PMH importer; ' . home_url(),
		] );

		if ( is_wp_error( $response ) ) {
			return [ 'status' => 'failed', 'message' => $response->get_error_message() ];
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( 503 === $code || 429 === $code ) {
			$retry_after = $this->parse_retry_after( wp_remote_retrieve_header( $response, 'retry-after' ) );
			if ( null !== $retry_after ) {
				return $this->schedule_flow_control_wait( $retry_after, $code );
			}
		}

		if ( $code < 200 || $code >= 300 ) {
			$message = sprintf( 'the provider answered HTTP %d', $code );
			// A client error will not change by retrying (429 is handled above when it carries Retry-After).
			return [ 'status' => ( $code >= 400 && $code < 500 && 429 !== $code ) ? 'fatal' : 'failed', 'message' => $message ];
		}

		$body = (string) wp_remote_retrieve_body( $response );
		if ( strlen( $body ) >= $this->get_max_response_size() ) {
			return [ 'status' => 'fatal', 'message' => sprintf( 'the response is larger than %d bytes', $this->get_max_response_size() ) ];
		}

		$xml = $this->parse_xml( $body );
		if ( ! $xml ) {
			return [ 'status' => 'failed', 'message' => 'invalid XML received' ];
		}

		return [ 'status' => 'ok', 'xml' => $xml ];
	}

	/**
	 * Largest response body accepted, in bytes.
	 *
	 * @return int
	 */
	protected function get_max_response_size() {
		/**
		 * Filters the largest OAI-PMH response accepted by the importer, in bytes.
		 *
		 * @param int $bytes Default 16 MB.
		 */
		return max( 1024, (int) apply_filters( 'tainacan-oaipmh-importer-max-response-size', 16 * MB_IN_BYTES ) );
	}

	/**
	 * Reads a Retry-After header: delta seconds or an HTTP date. Capped at MAX_RETRY_AFTER.
	 *
	 * @param string|array $header
	 * @return int|null Seconds to wait, or null when the header is missing or invalid.
	 */
	protected function parse_retry_after( $header ) {
		if ( is_array( $header ) ) {
			$header = reset( $header );
		}
		$header = trim( (string) $header );
		if ( '' === $header ) {
			return null;
		}
		if ( ctype_digit( $header ) ) {
			$seconds = (int) $header;
		} else {
			$timestamp = strtotime( $header );
			if ( false === $timestamp ) {
				return null;
			}
			$seconds = $timestamp - time();
		}
		return min( max( 0, $seconds ), self::MAX_RETRY_AFTER );
	}

	/**
	 * Schedules the wait asked by the provider (OAI-PMH flow control) without consuming the request.
	 *
	 * @param int $seconds
	 * @param int $code HTTP status.
	 * @return array fetch_oai() result.
	 */
	protected function schedule_flow_control_wait( $seconds, $code ) {
		$waits = (int) $this->get_transient('page_waits') + 1;
		if ( $waits > self::MAX_PAGE_WAITS ) {
			return [ 'status' => 'fatal', 'message' => sprintf( 'the provider kept answering HTTP %d after %d waits', $code, self::MAX_PAGE_WAITS ) ];
		}
		$this->add_transient( 'page_waits', $waits );
		$this->add_transient( 'not_before', time() + $seconds );
		$this->add_log( sprintf( 'The provider answered HTTP %1$d with Retry-After; waiting %2$d seconds before requesting it again', $code, $seconds ) );
		return [ 'status' => 'wait', 'message' => '' ];
	}

	/**
	 * Counts a failed attempt of the current request and, if attempts are left, schedules the next one.
	 *
	 * @param bool $can_retry False for errors that retrying cannot fix.
	 * @return bool Whether the request will be retried.
	 */
	protected function register_failed_attempt( $can_retry ) {
		$attempts = (int) $this->get_transient('page_attempts') + 1;
		if ( ! $can_retry || $attempts >= self::MAX_PAGE_ATTEMPTS ) {
			$this->reset_attempts();
			return false;
		}
		$this->add_transient( 'page_attempts', $attempts );
		/**
		 * Filters the pause, in seconds, before retrying a failed OAI-PMH request.
		 *
		 * @param int $seconds Default: 10 seconds times the attempt number.
		 * @param int $attempt Attempt that just failed (1-based).
		 */
		$wait = max( 0, (int) apply_filters( 'tainacan-oaipmh-importer-retry-wait', 10 * $attempts, $attempts ) );
		$this->add_transient( 'not_before', time() + $wait );
		$this->add_log( sprintf( 'Request failed (attempt %1$d of %2$d); retrying in %3$d seconds', $attempts, self::MAX_PAGE_ATTEMPTS, $wait ) );
		return true;
	}

	/**
	 * Clears the attempt and wait counters after a request is answered.
	 */
	protected function reset_attempts() {
		$this->delete_transient('page_attempts');
		$this->delete_transient('page_waits');
		$this->delete_transient('not_before');
	}

	/**
	 * Waits for a scheduled retry. A pause never exceeds MAX_SLEEP_PER_ITERATION, so a long wait
	 * spans several iterations and the background process keeps recording heartbeats.
	 *
	 * @return bool True when the request must still wait (nothing is fetched in this iteration).
	 */
	protected function wait_for_provider() {
		$remaining = (int) $this->get_transient('not_before') - time();
		if ( $remaining <= 0 ) {
			return false;
		}
		/**
		 * Filters the longest pause inside one importer iteration, in seconds.
		 *
		 * @param int $seconds Default MAX_SLEEP_PER_ITERATION.
		 */
		$sleep = min( $remaining, max( 0, (int) apply_filters( 'tainacan-oaipmh-importer-max-sleep', self::MAX_SLEEP_PER_ITERATION ) ) );
		if ( $sleep > 0 ) {
			sleep( $sleep );
		}
		return $remaining > $sleep;
	}

	/**
	 * Parses an XML body without resolving external entities or fetching remote resources.
	 *
	 * @param string $body
	 * @return \SimpleXMLElement|false
	 */
	protected function parse_xml( $body ) {
		if ( ! is_string( $body ) || '' === trim( $body ) ) {
			return false;
		}
		$previous = libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $body, 'SimpleXMLElement', LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		return $xml instanceof \SimpleXMLElement ? $xml : false;
	}
}
