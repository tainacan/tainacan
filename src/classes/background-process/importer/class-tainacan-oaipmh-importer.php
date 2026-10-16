<?php
namespace Tainacan\Importer;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan;
use Tainacan\Entities;

/**
 * Imports items from an OAI-PMH 2.0 data provider using the `oai_dc` metadata format.
 *
 * The importer follows the standard importer flow (see the wiki page "importer-flow"):
 *
 *  1. Step "Create Collections" (`create_collections()`): calls `ListSets` and, depending on
 *     the "Create set as" option, creates one collection per set, or a single collection whose
 *     sets become terms of a "Sets" taxonomy. Providers without sets get a single collection
 *     named after the repository. Every collection receives the Dublin Core metadata described
 *     by the `dublin-core` mapper, and the resulting mapping (`metadatum ID => 'dc:element'`)
 *     is stored in the collections queue through `add_collection()`.
 *
 *  2. Step "Import Items" (default `process_collections()`): each iteration fetches ONE
 *     `ListRecords` page in `process_item()` and inserts all of its records in `insert()`.
 *     Paging follows the `resumptionToken` returned by the provider; `total_items` of each
 *     queued collection counts pages, not records, and grows while tokens keep coming.
 *
 * Only the OAI-PMH base URL is required. Query strings pasted along with the URL are discarded.
 */
class Oaipmh_Importer extends Importer {

	const OAI_DC_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
	const DC_NAMESPACE     = 'http://purl.org/dc/elements/1.1/';
	const METADATA_PREFIX  = 'oai_dc';

	/**
	 * How many times a single OAI request is attempted before giving up.
	 */
	const MAX_HTTP_ATTEMPTS = 3;

	/**
	 * Safety cap for `ListSets` pagination inside a single step iteration.
	 */
	const MAX_LIST_SETS_PAGES = 20;

	protected $steps = [
		[
			'name' => 'Create Collections',
			'progress_label' => 'Create Collections',
			'callback' => 'create_collections'
		],
		[
			'name' => 'Import Items',
			'progress_label' => 'Import Items',
			'callback' => 'process_collections',
			'total' => 2
		],
	];

	/**
	 * Name of the taxonomy (and of its metadatum) created when sets are imported as terms.
	 */
	protected $NAME_FOR_SETS = 'Sets';

	private $col_repo;
	private $items_repo;
	private $metadata_repo;
	private $item_metadata_repo;
	private $tax_repo;
	private $term_repo;

	/**
	 * Metadatum entities of the mapping, cached per collection while a page is inserted.
	 *
	 * @var array<int, \Tainacan\Entities\Metadatum>
	 */
	private $metadata_cache = [];

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		$this->set_default_options([
			'using_set' => 'collection'
		]);

		$this->col_repo = \Tainacan\Repositories\Collections::get_instance();
		$this->items_repo = \Tainacan\Repositories\Items::get_instance();
		$this->metadata_repo = \Tainacan\Repositories\Metadata::get_instance();
		$this->item_metadata_repo = \Tainacan\Repositories\Item_Metadata::get_instance();
		$this->tax_repo = \Tainacan\Repositories\Taxonomies::get_instance();
		$this->term_repo = \Tainacan\Repositories\Terms::get_instance();

		$this->remove_import_method('file');
		$this->add_import_method('url');
	}

	/**
	 * Stores the OAI-PMH base URL.
	 *
	 * Users often paste a full request such as `.../oai?verb=Identify`; the query string is
	 * dropped so that the verbs built by this class are not appended to it.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function set_url($url) {
		if ( is_string($url) ) {
			$url = trim($url);
			$query_position = strpos($url, '?');
			if ( false !== $query_position ) {
				$url = substr($url, 0, $query_position);
			}
		}
		return parent::set_url($url);
	}

	/**
	 * Step 1: discovers the sets of the provider and creates the target collections.
	 *
	 * @return bool Always false, so the importer moves to the next step.
	 */
	public function create_collections() {
		$this->add_log('Creating collections');

		if ( ! $this->get_url() ) {
			$this->add_error_log( __('No OAI-PMH base URL was informed.', 'tainacan') );
			$this->abort();
			return false;
		}

		$sets = $this->fetch_sets();
		if ( false === $sets ) {
			// Error already logged; the importer was aborted.
			return false;
		}

		if ( empty($sets) ) {
			$this->add_log('Provider has no sets; importing everything into a single collection');
			$this->queue_collection_for_set( '', $this->get_repository_name() );
			return false;
		}

		if ( 'taxonomy' === $this->get_option('using_set') ) {
			$this->queue_single_collection_with_sets_taxonomy( $sets );
			return false;
		}

		foreach ( $sets as $set ) {
			$this->queue_collection_for_set( $set['spec'], $set['name'] );
		}

		return false;
	}

	/**
	 * Step 2 worker: fetches one `ListRecords` page of the given collection.
	 *
	 * @param int   $index                 Page index within the collection (0 = first page).
	 * @param array $collection_definition Entry of the collections queue.
	 * @return array|bool Records of the page to be handled by insert(); true when there is
	 *                    nothing (left) to import for this page; false on error.
	 */
	public function process_item( $index, $collection_definition ) {
		if ( ! is_array($collection_definition) || ! isset($collection_definition['id']) ) {
			$this->add_error_log('Collection misconfigured');
			return false;
		}

		$set_spec = $this->get_set_spec_of( $collection_definition );
		$this->add_log( sprintf('Processing page %d of set "%s"', (int) $index + 1, '' === $set_spec ? '(all records)' : $set_spec) );

		if ( 0 === (int) $index ) {
			$this->delete_transient('resumptionToken');
			$params = [
				'verb'           => 'ListRecords',
				'metadataPrefix' => self::METADATA_PREFIX,
			];
			if ( '' !== $set_spec ) {
				$params['set'] = $set_spec;
			}
		} else {
			$token = $this->get_transient('resumptionToken');
			if ( empty($token) ) {
				$this->add_log('No resumption token left; nothing else to fetch for this set');
				return true;
			}
			$params = [
				'verb'            => 'ListRecords',
				'resumptionToken' => $token,
			];
		}

		$xml = $this->fetch_xml( $this->build_request_url($params) );
		if ( ! $xml ) {
			return false;
		}

		if ( isset($xml->error) ) {
			$code = (string) $xml->error['code'];
			if ( 'noRecordsMatch' === $code ) {
				$this->add_log('Provider returned noRecordsMatch');
				return true;
			}
			$this->add_error_log( sprintf('OAI-PMH error "%s": %s', $code, trim((string) $xml->error)) );
			return false;
		}

		if ( ! isset($xml->ListRecords) ) {
			$this->add_error_log('Unexpected response: no ListRecords element found');
			return false;
		}

		$token = isset($xml->ListRecords->resumptionToken) ? trim( (string) $xml->ListRecords->resumptionToken ) : '';
		if ( '' !== $token ) {
			$this->add_transient('resumptionToken', $token);
			// Keep the queue calling us: this collection has at least one more page.
			$this->ensure_total_pages( $collection_definition, (int) $index + 2 );
		} else {
			$this->delete_transient('resumptionToken');
		}

		$records = [];
		foreach ( $xml->ListRecords->record as $record ) {
			$parsed = $this->parse_record($record);
			if ( $parsed ) {
				$records[] = $parsed;
			}
		}

		if ( empty($records) ) {
			$this->add_log('Page has no importable records');
			return true;
		}

		return [
			'records'               => $records,
			'collection_definition' => $collection_definition,
		];
	}

	/**
	 * Inserts all records of a page returned by process_item().
	 *
	 * @param array $processed_item   ['records' => array[], 'collection_definition' => array]
	 * @param int   $collection_index Index in the collections queue (unused: the definition travels with the page).
	 * @return Entities\Item|false The last inserted item, or false when nothing was inserted.
	 */
	public function insert( $processed_item, $collection_index ) {
		if ( ! is_array($processed_item) || empty($processed_item['records']) || empty($processed_item['collection_definition']['id']) ) {
			$this->add_error_log('Nothing to insert: malformed page');
			return false;
		}

		$definition = $processed_item['collection_definition'];
		$collection = $this->col_repo->fetch( (int) $definition['id'] );
		if ( ! $collection instanceof Entities\Collection ) {
			$this->add_error_log('Target collection not found: ' . $definition['id']);
			return false;
		}
		if ( ! $collection->user_can('edit_items') ) {
			$this->add_error_log( __("You don't have permission to create items in this collection.", 'tainacan') );
			return false;
		}

		$this->items_repo->disable_logs();
		$this->metadata_repo->disable_logs();
		$this->item_metadata_repo->disable_logs();

		$mapping = isset($definition['mapping']) && is_array($definition['mapping']) ? $definition['mapping'] : [];
		$this->metadata_cache = [];

		$last_inserted = false;
		foreach ( $processed_item['records'] as $record ) {
			$item = $this->insert_record( $record, $collection, $mapping, $definition );
			if ( $item ) {
				$last_inserted = $item;
			}
		}
		// Do not carry entity objects into the serialized session.
		$this->metadata_cache = [];

		return $last_inserted;
	}

	/**
	 * Renders the options form (how sets should be imported).
	 *
	 * @return string
	 */
	public function options_form() {
		ob_start();
		?>
		<div class="columns">
			<div class="column">
				<div class="field">
					<label class="label" style="position: relative">
						<?php esc_html_e('Create set as', 'tainacan'); ?>
						<span class="help-wrapper">
							<a class="help-button">
								<span class="icon is-small">
									<i class="tainacan-icon tainacan-icon-help" ></i>
								</span>
							</a>
							<div class="help-tooltip">
								<div class="help-tooltip-header">
									<h5><?php esc_html_e('Create set as', 'tainacan'); ?></h5>
								</div>
								<div class="help-tooltip-body">
									<p><?php esc_html_e('Choose the action to manipulate sets', 'tainacan'); ?></p>
								</div>
							</div>
						</span>
					</label>
					<div class="control is-clearfix">
						<div class="select">
							<select name="using_set">
								<option value="collection" <?php selected($this->get_option('using_set'), 'collection'); ?> ><?php esc_html_e('Collections', 'tainacan'); ?></option>
								<option value="taxonomy" <?php selected($this->get_option('using_set'), 'taxonomy'); ?> ><?php esc_html_e('Taxonomies', 'tainacan'); ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// Collections queue
	// -------------------------------------------------------------------------

	/**
	 * Creates (or reuses) the collection for a set and adds it to the queue.
	 *
	 * @param string $set_spec Set identifier; empty string when the provider has no sets.
	 * @param string $name     Collection name.
	 * @return bool
	 */
	protected function queue_collection_for_set( $set_spec, $name ) {
		$collection = $this->create_collection( '' === $set_spec ? 'repository' : $set_spec, $name );
		if ( ! $collection ) {
			return false;
		}

		$mapping = $this->create_collection_metadata( $collection );
		$total_pages = $this->estimate_total_pages( $set_spec );
		if ( false === $total_pages ) {
			return false;
		}

		$this->add_log( sprintf('Collection "%s" (ID %d) queued with %d page(s)', $name, $collection->get_id(), $total_pages) );
		$this->add_collection([
			'id'          => $collection->get_id(),
			'mapping'     => $mapping,
			'total_items' => $total_pages,
			'source_id'   => $set_spec,
		]);
		return true;
	}

	/**
	 * Creates one collection for the whole repository plus a taxonomy whose terms are the sets.
	 *
	 * @param array $sets List of ['spec' => string, 'name' => string].
	 * @return bool
	 */
	protected function queue_single_collection_with_sets_taxonomy( array $sets ) {
		$collection = $this->create_collection( 'repository', $this->get_repository_name() );
		if ( ! $collection ) {
			return false;
		}
		$mapping = $this->create_collection_metadata( $collection );

		$taxonomy_id = $this->get_transient('set_taxonomy_id');
		if ( $taxonomy_id ) {
			$taxonomy = $this->tax_repo->fetch( (int) $taxonomy_id );
		} else {
			$taxonomy = new Entities\Taxonomy();
			$taxonomy->set_name( $this->NAME_FOR_SETS );
			$taxonomy->set_allow_insert('yes');
			$taxonomy->set_status('publish');
			if ( ! $taxonomy->validate() ) {
				$this->add_error_log('Error creating taxonomy ' . $this->NAME_FOR_SETS);
				$this->add_error_log( $taxonomy->get_errors() );
				$this->abort();
				return false;
			}
			$taxonomy = $this->tax_repo->insert($taxonomy);
			$this->add_transient('set_taxonomy_id', $taxonomy->get_id());
			$this->add_log('Taxonomy ' . $taxonomy->get_name() . ' created');
		}

		if ( ! $taxonomy instanceof Entities\Taxonomy ) {
			$this->add_error_log('Taxonomy for sets not found');
			$this->abort();
			return false;
		}

		$metadatum_set_id = $this->create_set_metadata( $collection->get_id(), $taxonomy->get_id() );
		if ( ! $metadatum_set_id ) {
			$this->add_error_log('Error creating the metadatum for sets');
			$this->abort();
			return false;
		}

		foreach ( $sets as $set ) {
			$this->create_set_term( $taxonomy, $set['name'], $set['spec'] );
		}

		$total_pages = $this->estimate_total_pages( '' );
		if ( false === $total_pages ) {
			return false;
		}

		$this->add_collection([
			'id'           => $collection->get_id(),
			'mapping'      => $mapping,
			'total_items'  => $total_pages,
			'source_id'    => 'sets',
			'metadatum_id' => $metadatum_set_id,
		]);
		return true;
	}

	/**
	 * Returns the set to request for a queued collection ('' = no set restriction).
	 *
	 * @param array $collection_definition
	 * @return string
	 */
	protected function get_set_spec_of( array $collection_definition ) {
		if ( empty($collection_definition['source_id']) || 'sets' === $collection_definition['source_id'] ) {
			return '';
		}
		return (string) $collection_definition['source_id'];
	}

	/**
	 * Raises `total_items` (pages) of a queued collection when the provider keeps paging.
	 *
	 * @param array $collection_definition
	 * @param int   $minimum_pages
	 */
	protected function ensure_total_pages( array $collection_definition, $minimum_pages ) {
		$collections = $this->get_collections();
		foreach ( $collections as $position => $queued ) {
			if ( isset($queued['id']) && (int) $queued['id'] === (int) $collection_definition['id'] ) {
				if ( empty($queued['total_items']) || (int) $queued['total_items'] < $minimum_pages ) {
					$collections[$position]['total_items'] = $minimum_pages;
					$this->set_collections($collections);
				}
				return;
			}
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
	 * and always hold a list of values.
	 *
	 * @param \SimpleXMLElement $record
	 * @return array|null ['identifier' => string, 'sets' => string[], 'metadata' => array<string, string[]>]
	 */
	protected function parse_record( \SimpleXMLElement $record ) {
		if ( ! isset($record->header) ) {
			return null;
		}
		$header = $record->header;
		$identifier = trim( (string) $header->identifier );

		if ( isset($header['status']) && 'deleted' === (string) $header['status'] ) {
			$this->add_log('Skipping deleted record ' . $identifier);
			return null;
		}

		if ( ! isset($record->metadata) ) {
			$this->add_log('Skipping record without metadata ' . $identifier);
			return null;
		}

		$sets = [];
		if ( isset($header->setSpec) ) {
			foreach ( $header->setSpec as $set_spec ) {
				$sets[] = (string) $set_spec;
			}
		}

		$metadata = [];
		$dc_wrapper = $record->metadata->children( self::OAI_DC_NAMESPACE );
		if ( isset($dc_wrapper->dc) ) {
			$this->collect_dc_elements( $metadata, $dc_wrapper->dc->children( self::DC_NAMESPACE ) );
		} else {
			// Lenient fallback for providers that omit the oai_dc wrapper.
			$this->collect_dc_elements( $metadata, $record->metadata->children( self::DC_NAMESPACE ) );
			foreach ( $record->metadata->children() as $child ) {
				$this->collect_dc_elements( $metadata, $child->children( self::DC_NAMESPACE ) );
			}
		}

		if ( empty($metadata) ) {
			$this->add_log('Skipping record without Dublin Core elements ' . $identifier);
			return null;
		}

		return [
			'identifier' => $identifier,
			'sets'       => $sets,
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
	 * Inserts a single parsed record as an item of the collection.
	 *
	 * @param array                $record     Output of parse_record().
	 * @param Entities\Collection  $collection Target collection.
	 * @param array                $mapping    metadatum ID => 'dc:element'.
	 * @param array                $definition Collections queue entry (for the sets metadatum).
	 * @return Entities\Item|false
	 */
	protected function insert_record( array $record, Entities\Collection $collection, array $mapping, array $definition ) {
		$metadata = $record['metadata'];

		$title = isset($metadata['dc:title'][0]) ? $metadata['dc:title'][0] : '';
		if ( '' === $title ) {
			$title = '' !== $record['identifier'] ? $record['identifier'] : __('Untitled', 'tainacan');
		}

		$item = new Entities\Item();
		$item->set_collection( $collection );
		$item->set_title( $title );
		$item->set_description( isset($metadata['dc:description']) ? implode( "\n\n", $metadata['dc:description'] ) : '' );
		$item->set_status('publish');

		if ( ! $item->validate() ) {
			$this->add_error_log( 'Error inserting item ' . $record['identifier'] );
			$this->add_error_log( $item->get_errors() );
			return false;
		}
		$item = $this->items_repo->insert( $item );
		$this->add_log( 'Item inserted: ' . $title );

		if ( ! empty($record['sets']) && ! empty($definition['metadatum_id']) ) {
			$this->set_item_sets( $item, (int) $definition['metadatum_id'], $record['sets'] );
		}

		foreach ( $mapping as $metadatum_id => $source_field ) {
			if ( empty($metadata[ $source_field ]) ) {
				continue;
			}
			// Title and description map to core metadata and were already set on the item
			// (description keeps every dc:description value; the core metadatum holds only one).
			if ( in_array( $source_field, [ 'dc:title', 'dc:description' ], true ) ) {
				continue;
			}
			$metadatum = $this->get_mapped_metadatum( (int) $metadatum_id );
			if ( ! $metadatum ) {
				$this->add_error_log( 'Metadatum ' . $metadatum_id . ' of the mapping not found' );
				continue;
			}

			$item_metadata = new Entities\Item_Metadata_Entity( $item, $metadatum );
			$values = array_values( $metadata[ $source_field ] );
			$item_metadata->set_value( $item_metadata->is_multiple() ? $values : $values[0] );

			if ( $item_metadata->validate() ) {
				$this->item_metadata_repo->insert( $item_metadata );
			} else {
				$this->add_error_log( sprintf('Error saving "%s" for item %s', $metadatum->get_name(), $record['identifier']) );
				$this->add_error_log( $item_metadata->get_errors() );
			}
		}

		return $item;
	}

	/**
	 * Fills the "Sets" taxonomy metadatum of an item with the terms of its setSpecs.
	 *
	 * @param Entities\Item $item
	 * @param int           $metadatum_id
	 * @param string[]      $set_specs
	 */
	protected function set_item_sets( Entities\Item $item, $metadatum_id, array $set_specs ) {
		$term_ids = [];
		foreach ( $set_specs as $set_spec ) {
			$term_id = $this->get_transient( 'set_term_' . $set_spec );
			if ( $term_id ) {
				$term_ids[] = (int) $term_id;
			}
		}
		if ( empty($term_ids) ) {
			return;
		}

		$metadatum = $this->get_mapped_metadatum( $metadatum_id );
		if ( ! $metadatum ) {
			return;
		}
		$item_metadata = new Entities\Item_Metadata_Entity( $item, $metadatum );
		$item_metadata->set_value( $term_ids );
		if ( $item_metadata->validate() ) {
			$this->item_metadata_repo->insert( $item_metadata );
		} else {
			$this->add_error_log( 'Error saving sets for item ' . $item->get_id() );
			$this->add_error_log( $item_metadata->get_errors() );
		}
	}

	/**
	 * Fetches a metadatum of the mapping once per inserted page.
	 *
	 * @param int $metadatum_id
	 * @return Entities\Metadatum|false
	 */
	protected function get_mapped_metadatum( $metadatum_id ) {
		if ( ! array_key_exists( $metadatum_id, $this->metadata_cache ) ) {
			$metadatum = $this->metadata_repo->fetch( $metadatum_id );
			$this->metadata_cache[ $metadatum_id ] = $metadatum instanceof Entities\Metadatum ? $metadatum : false;
		}
		return $this->metadata_cache[ $metadatum_id ];
	}

	// -------------------------------------------------------------------------
	// Provider discovery
	// -------------------------------------------------------------------------

	/**
	 * Lists the sets of the provider, following `ListSets` resumption tokens.
	 *
	 * @return array|false List of ['spec' => string, 'name' => string]; empty when the provider
	 *                     has no set hierarchy; false (and importer aborted) on fetch errors.
	 */
	protected function fetch_sets() {
		$sets = [];
		$params = [ 'verb' => 'ListSets' ];

		for ( $page = 0; $page < self::MAX_LIST_SETS_PAGES; $page++ ) {
			$xml = $this->fetch_xml( $this->build_request_url($params) );
			if ( ! $xml ) {
				$this->abort();
				return false;
			}

			if ( isset($xml->error) ) {
				if ( 'noSetHierarchy' === (string) $xml->error['code'] ) {
					return [];
				}
				$this->add_error_log( sprintf('OAI-PMH error "%s": %s', (string) $xml->error['code'], trim((string) $xml->error)) );
				$this->abort();
				return false;
			}

			if ( isset($xml->ListSets->set) ) {
				foreach ( $xml->ListSets->set as $set ) {
					$spec = trim( (string) $set->setSpec );
					if ( '' === $spec ) {
						continue;
					}
					$name = trim( (string) $set->setName );
					$sets[] = [
						'spec' => $spec,
						'name' => '' !== $name ? $name : $spec,
					];
				}
			}

			$token = isset($xml->ListSets->resumptionToken) ? trim( (string) $xml->ListSets->resumptionToken ) : '';
			if ( '' === $token ) {
				break;
			}
			$params = [ 'verb' => 'ListSets', 'resumptionToken' => $token ];
		}

		return $sets;
	}

	/**
	 * Returns the repository name advertised by `Identify`, or a generic fallback.
	 *
	 * @return string
	 */
	protected function get_repository_name() {
		$fallback = __('Imported repository', 'tainacan');
		$xml = $this->fetch_xml( $this->build_request_url([ 'verb' => 'Identify' ]) );
		if ( ! $xml || ! isset($xml->Identify->repositoryName) ) {
			return $fallback;
		}
		$name = trim( (string) $xml->Identify->repositoryName );
		return '' !== $name ? $name : $fallback;
	}

	/**
	 * Estimates how many `ListRecords` pages a set has, from its first page.
	 *
	 * Uses `completeListSize` when the provider announces it; otherwise assumes two pages
	 * whenever a resumption token is present (process_item() extends the count as it goes).
	 *
	 * @param string $set_spec '' for the whole repository.
	 * @return int|false Number of pages, or false (and importer aborted) when the page cannot be fetched.
	 */
	protected function estimate_total_pages( $set_spec ) {
		$params = [
			'verb'           => 'ListRecords',
			'metadataPrefix' => self::METADATA_PREFIX,
		];
		if ( '' !== $set_spec ) {
			$params['set'] = $set_spec;
		}

		$xml = $this->fetch_xml( $this->build_request_url($params) );
		if ( ! $xml ) {
			$this->add_error_log('Error fetching the first page of records');
			$this->abort();
			return false;
		}

		if ( isset($xml->error) ) {
			if ( 'noRecordsMatch' === (string) $xml->error['code'] ) {
				return 0;
			}
			$this->add_error_log( sprintf('OAI-PMH error "%s": %s', (string) $xml->error['code'], trim((string) $xml->error)) );
			$this->abort();
			return false;
		}

		if ( ! isset($xml->ListRecords) ) {
			return 0;
		}

		$records_in_page = count( $xml->ListRecords->record );
		if ( ! isset($xml->ListRecords->resumptionToken) || '' === trim( (string) $xml->ListRecords->resumptionToken ) ) {
			return $records_in_page > 0 ? 1 : 0;
		}

		$complete_list_size = (int) $xml->ListRecords->resumptionToken['completeListSize'];
		if ( $complete_list_size > 0 && $records_in_page > 0 ) {
			return (int) ceil( $complete_list_size / $records_in_page );
		}

		return 2;
	}

	// -------------------------------------------------------------------------
	// Entities created by the importer
	// -------------------------------------------------------------------------

	/**
	 * Creates the collection for a set, reusing it on later runs of the same session.
	 *
	 * @param string $set_spec Set identifier (or 'repository').
	 * @param string $name     Collection name.
	 * @return Entities\Collection|false
	 */
	protected function create_collection( $set_spec, $name ) {
		$transient_key = 'collection_' . $set_spec . '_name';
		$existing_id = $this->get_transient( $transient_key );
		if ( $existing_id ) {
			$existing = $this->col_repo->fetch( (int) $existing_id );
			if ( $existing instanceof Entities\Collection ) {
				return $existing;
			}
		}

		$collection = new Entities\Collection();
		$collection->set_name( '' !== trim((string) $name) ? $name : $set_spec );
		$collection->set_status('publish');

		if ( ! $collection->validate() ) {
			$this->add_error_log( 'Error creating collection ' . $name );
			$this->add_error_log( $collection->get_errors() );
			$this->abort();
			return false;
		}

		$collection = $this->col_repo->insert( $collection );
		$this->add_log( 'Collection created: ' . $collection->get_name() );
		$this->add_transient( $transient_key, $collection->get_id() );
		return $collection;
	}

	/**
	 * Creates the Dublin Core metadata of a collection from the `dublin-core` mapper.
	 *
	 * @param Entities\Collection $collection
	 * @return array metadatum ID => 'dc:element'
	 */
	protected function create_collection_metadata( Entities\Collection $collection ) {
		$Tainacan_Mappers = \Tainacan\Mappers_Handler::get_instance();
		$mapper_class = $Tainacan_Mappers->check_class_name( 'dublin-core', true, $Tainacan_Mappers::MAPPER_CLASS_PREFIX );
		$mapper = new $mapper_class();
		$mapping = [];

		if ( ! is_array($mapper->metadata) ) {
			return $mapping;
		}

		$collection_id = $collection->get_id();
		foreach ( $mapper->metadata as $slug => $mapper_metadatum ) {
			if ( array_key_exists('core_metadatum', $mapper_metadatum) ) {
				$method = 'get_core_' . $mapper_metadatum['core_metadatum'] . '_metadatum';
				if ( ! method_exists($collection, $method) ) {
					continue;
				}
				$core_metadatum = $collection->$method();
				if ( ! $core_metadatum ) {
					$this->metadata_repo->register_core_metadata( $collection, true );
					$core_metadatum = $collection->$method();
				}
				if ( ! $core_metadatum instanceof Entities\Metadatum ) {
					continue;
				}
				$exposer_mapping = $core_metadatum->get_exposer_mapping();
				$exposer_mapping[ $mapper->slug ] = $slug;
				$core_metadatum->set_exposer_mapping( $exposer_mapping );
				if ( $core_metadatum->validate() ) {
					$core_metadatum = $this->metadata_repo->insert( $core_metadatum );
					$mapping[ $core_metadatum->get_id() ] = $slug;
				}
				continue;
			}

			$transient_key = 'collection_' . $collection_id . '_' . $slug;
			$existing_id = $this->get_transient( $transient_key );
			if ( $existing_id ) {
				$mapping[ (int) $existing_id ] = $slug;
				continue;
			}

			$metadatum = new Entities\Metadatum();
			if ( ! empty($mapper_metadatum['metadata_type']) && class_exists( $mapper_metadatum['metadata_type'] ) ) {
				$metadatum->set_metadata_type( $mapper_metadatum['metadata_type'] );
			} else {
				$metadatum->set_metadata_type('Tainacan\Metadata_Types\Text');
			}
			$metadatum->set_name( $mapper_metadatum['label'] );
			$metadatum->set_semantic_uri( $mapper->get_url($slug) );
			$metadatum->set_exposer_mapping([ $mapper->slug => $slug ]);
			$metadatum->set_status('publish');
			$metadatum->set_collection_id( $collection_id );
			$metadatum->set_slug( $slug );

			if ( ! $metadatum->validate() ) {
				$this->add_error_log( 'Error creating metadatum ' . $mapper_metadatum['label'] );
				$this->add_error_log( $metadatum->get_errors() );
				continue;
			}
			$metadatum = $this->metadata_repo->insert( $metadatum );
			$this->add_transient( $transient_key, $metadatum->get_id() );
			$mapping[ $metadatum->get_id() ] = $slug;
		}

		return $mapping;
	}

	/**
	 * Creates the term of a set in the "Sets" taxonomy (once per session).
	 *
	 * @param Entities\Taxonomy $taxonomy
	 * @param string            $name
	 * @param string            $set_spec
	 * @return bool
	 */
	protected function create_set_term( Entities\Taxonomy $taxonomy, $name, $set_spec ) {
		$transient_key = 'set_term_' . $set_spec;
		if ( $this->get_transient( $transient_key ) ) {
			return false;
		}

		$term = new Entities\Term();
		$term->set_taxonomy( $taxonomy->get_db_identifier() );
		$term->set_name( '' !== trim((string) $name) ? $name : $set_spec );

		if ( ! $term->validate() ) {
			$this->add_log( 'Error creating term for set ' . $set_spec . ': ' . implode( ',', $term->get_errors() ) );
			return false;
		}

		$inserted = $this->term_repo->insert( $term );
		if ( is_wp_error($inserted) ) {
			$this->add_log( $inserted->get_error_message() );
			return false;
		}

		$this->add_transient( $transient_key, $inserted->get_id() );
		$this->add_log( 'Added term: ' . $inserted->get_name() . ' in taxonomy: ' . $taxonomy->get_name() );
		return true;
	}

	/**
	 * Creates the taxonomy metadatum that holds the sets of each item (once per session).
	 *
	 * @param int $collection_id
	 * @param int $taxonomy_id
	 * @return int|false Metadatum ID.
	 */
	public function create_set_metadata( $collection_id, $taxonomy_id ) {
		$existing_id = $this->get_transient('set_metadatum_id');
		if ( $existing_id ) {
			return (int) $existing_id;
		}

		$metadatum = new Entities\Metadatum();
		$metadatum->set_name( $this->NAME_FOR_SETS );
		$metadatum->set_metadata_type('Tainacan\Metadata_Types\Taxonomy');
		$metadatum->set_collection_id( $collection_id );
		$metadatum->set_status('publish');
		$metadatum->set_metadata_type_options([ 'taxonomy_id' => $taxonomy_id ]);
		$metadatum->set_multiple('yes');

		if ( ! $metadatum->validate() ) {
			$this->add_error_log( $metadatum->get_errors() );
			return false;
		}

		$metadatum = $this->metadata_repo->insert( $metadatum );
		$this->add_transient( 'set_metadatum_id', $metadatum->get_id() );
		$this->add_log( 'Metadata created: ' . $metadatum->get_name() );
		return $metadatum->get_id();
	}

	// -------------------------------------------------------------------------
	// HTTP / XML
	// -------------------------------------------------------------------------

	/**
	 * Builds an OAI-PMH request URL for the stored base URL.
	 *
	 * @param array $params Query parameters (verb, metadataPrefix, set, resumptionToken...).
	 * @return string
	 */
	protected function build_request_url( array $params ) {
		return $this->get_url() . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Fetches and parses an OAI-PMH response.
	 *
	 * @param string $url
	 * @return \SimpleXMLElement|false
	 */
	protected function fetch_xml( $url ) {
		$body = $this->requester( $url );
		if ( false === $body ) {
			return false;
		}

		$xml = $this->parse_xml( $body );
		if ( ! $xml ) {
			$this->add_error_log( 'Invalid XML received from ' . $url );
			return false;
		}
		return $xml;
	}

	/**
	 * Performs a GET request with a few retries on transport or server errors.
	 *
	 * @param string $link
	 * @return string|false Response body, or false after the last failed attempt.
	 */
	protected function requester( $link ) {
		$args = [
			'timeout'     => 60,
			'redirection' => 5,
			/**
			 * Filters whether the OAI-PMH importer verifies SSL certificates of the provider.
			 *
			 * @param bool   $sslverify Default true.
			 * @param string $link      Request URL.
			 */
			'sslverify'   => (bool) apply_filters( 'tainacan-oaipmh-importer-sslverify', true, $link ),
			'user-agent'  => 'Tainacan/' . TAINACAN_VERSION . ' OAI-PMH importer; ' . home_url(),
		];

		for ( $attempt = 1; $attempt <= self::MAX_HTTP_ATTEMPTS; $attempt++ ) {
			$this->add_log( sprintf('Fetching %s (attempt %d)', $link, $attempt) );
			$response = wp_remote_get( $link, $args );

			if ( is_wp_error($response) ) {
				$this->add_log( $response->get_error_message() );
			} else {
				$code = (int) wp_remote_retrieve_response_code( $response );
				if ( $code >= 200 && $code < 300 ) {
					return (string) wp_remote_retrieve_body( $response );
				}
				$this->add_log( sprintf('Provider answered HTTP %d', $code) );
				if ( $code >= 400 && $code < 500 && 429 !== $code ) {
					break; // A client error will not change by retrying.
				}
			}

			if ( $attempt < self::MAX_HTTP_ATTEMPTS ) {
				/**
				 * Filters the pause, in seconds, before retrying a failed OAI-PMH request.
				 *
				 * @param int $seconds Default: 2 seconds times the attempt number.
				 * @param int $attempt Attempt that just failed (1-based).
				 */
				$wait = (int) apply_filters( 'tainacan-oaipmh-importer-retry-wait', 2 * $attempt, $attempt );
				if ( $wait > 0 ) {
					sleep( $wait );
				}
			}
		}

		$this->add_error_log( 'Could not fetch ' . $link );
		return false;
	}

	/**
	 * Parses an XML body without resolving external entities or fetching remote resources.
	 *
	 * @param string $body
	 * @return \SimpleXMLElement|false
	 */
	protected function parse_xml( $body ) {
		if ( ! is_string($body) || '' === trim($body) ) {
			return false;
		}
		$previous = libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $body, 'SimpleXMLElement', LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		return $xml instanceof \SimpleXMLElement ? $xml : false;
	}
}
