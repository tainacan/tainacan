<?php

namespace Tainacan\OAIPMH;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * OAI-PMH data provider.
 *
 * Maps Tainacan entities (collections, items, item metadata) to the array
 * structures consumed by {@see OAIPMH_Xml_Generator}. All data access is done
 * through Tainacan repositories/entities, except the earliestDatestamp
 * aggregate which is a justified direct query.
 */
class OAIPMH_Data_Provider {

	/**
	 * Transient caching the repository earliest datestamp.
	 */
	const EARLIEST_TRANSIENT = 'tnc_oai_earliest_datestamp';

	/**
	 * Identifier prefix, e.g. "oai:example.org:".
	 *
	 * @var string
	 */
	private $identifier_prefix;

	public function __construct() {
		$domain                  = wp_parse_url( home_url(), PHP_URL_HOST );
		$this->identifier_prefix = 'oai:' . $domain . ':';
	}

	/**
	 * Base URL advertised by the provider.
	 *
	 * @return string
	 */
	public function get_base_url() {
		return rest_url( 'tainacan/v2/oai' );
	}

	/**
	 * @return string
	 */
	public function get_identifier_prefix() {
		return $this->identifier_prefix;
	}

	/**
	 * @param int $item_id
	 * @return string
	 */
	public function build_identifier( $item_id ) {
		return $this->identifier_prefix . $item_id;
	}

	/**
	 * @param string $identifier
	 * @return int
	 */
	public function extract_item_id( $identifier ) {
		if ( ! is_string( $identifier ) || 0 !== strpos( $identifier, $this->identifier_prefix ) ) {
			return 0;
		}
		return (int) substr( $identifier, strlen( $this->identifier_prefix ) );
	}

	/**
	 * Build the Identify response.
	 *
	 * @return array
	 */
	public function get_identify() {
		return array(
			/**
			 * Filter the repository name advertised on Identify.
			 *
			 * @param string $name Defaults to the site name.
			 */
			'repositoryName'         => apply_filters( 'tainacan-oai-repository-name', get_bloginfo( 'name' ) ),
			'baseURL'                => $this->get_base_url(),
			'protocolVersion'        => '2.0',
			'adminEmail'             => apply_filters( 'tainacan-oai-admin-email', get_bloginfo( 'admin_email' ) ),
			'earliestDatestamp'      => $this->get_earliest_datestamp(),
			'deletedRecord'          => 'transient',
			'granularity'            => 'YYYY-MM-DDThh:mm:ssZ',
			'repositoryIdentifier'   => $this->get_repository_identifier(),
			'sampleIdentifier'       => $this->build_identifier( 1 ),
		);
	}

	/**
	 * Repository identifier for the oai-identifier description block.
	 *
	 * @return string
	 */
	public function get_repository_identifier() {
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		return $domain ? $domain : 'unknown';
	}

	/**
	 * Earliest item datestamp in the repository (UTC), cached for a day.
	 *
	 * @return string
	 */
	public function get_earliest_datestamp() {
		$cached = get_transient( self::EARLIEST_TRANSIENT );
		if ( is_string( $cached ) && '' !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- MIN() aggregate over $wpdb->posts restricted to Tainacan item post types; not expressible via WP_Query without loading every post; constant predicates, no user input; result cached in a transient below.
		$earliest = $wpdb->get_var(
			"SELECT MIN(post_modified_gmt) FROM {$wpdb->posts}
			 WHERE post_type LIKE 'tnc_col_%_item'
			   AND post_status IN ('publish', 'trash')
			   AND post_modified_gmt > '1970-01-01 00:00:00'"
		);

		if ( $earliest ) {
			$result = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $earliest . ' UTC' ) );
		} else {
			// No items yet; per the OAI-PMH spec earliestDatestamp must still be set.
			$result = gmdate( 'Y-m-d\TH:i:s\Z' );
		}

		/**
		 * Filter the repository earliest datestamp advertised on Identify.
		 *
		 * @param string $result Datestamp in YYYY-MM-DDThh:mm:ssZ.
		 */
		$result = apply_filters( 'tainacan-oai-earliest-datestamp', $result );

		set_transient( self::EARLIEST_TRANSIENT, $result, DAY_IN_SECONDS );

		return $result;
	}

	/**
	 * List collections as OAI sets.
	 *
	 * @return array
	 */
	public function get_sets() {
		$result = $this->get_sets_page(
			array(
				'per'  => PHP_INT_MAX,
				'page' => 1,
			)
		);
		return $result['sets'];
	}

	/**
	 * Fetch a page of OAI sets (Tainacan collections).
	 *
	 * @param array $query {
	 *     @type int $page 1-based page number.
	 *     @type int $per  Page size.
	 * }
	 * @return array { @type array $sets; @type int $total }
	 */
	public function get_sets_page( $query ) {
		$args = array(
			'posts_per_page' => (int) $query['per'],
			'paged'          => max( 1, (int) $query['page'] ),
			'order'          => 'DESC',
			'orderby'        => 'ID',
			'post_status'    => 'publish',
		);

		$repo     = \Tainacan\Repositories\Collections::get_instance();
		$wp_query = $repo->fetch( $args, 'WP_Query' );

		$sets = array();
		foreach ( $wp_query->posts as $post ) {
			try {
				$col = new \Tainacan\Entities\Collection( $post );
			} catch ( \Exception $e ) {
				continue;
			}
			if ( ! $this->is_set_oai_readable( $col ) ) {
				continue;
			}
			$sets[] = array(
				'setSpec'        => (string) $col->get_id(),
				'setName'        => $col->get_name(),
				'setDescription' => $col->get_description(),
			);
		}

		return array(
			'sets'  => $sets,
			'total' => (int) $wp_query->found_posts,
		);
	}

	/**
	 * Whether a setSpec maps to an existing collection.
	 *
	 * Sets are Tainacan collections, identified by numeric IDs. Non-numeric
	 * specs are rejected on purpose (yields badArgument upstream).
	 *
	 * @param string $set_spec
	 * @return bool
	 */
	public function set_exists( $set_spec ) {
		if ( ! ctype_digit( (string) $set_spec ) ) {
			return false;
		}
		try {
			$collection = new \Tainacan\Entities\Collection( (int) $set_spec );
		} catch ( \Exception $e ) {
			return false;
		}
		return $this->is_set_oai_readable( $collection );
	}

	/**
	 * @param string $identifier
	 * @return bool
	 */
	public function item_exists( $identifier ) {
		$item_id = $this->extract_item_id( $identifier );
		if ( $item_id <= 0 ) {
			return false;
		}
		try {
			$item = new \Tainacan\Entities\Item( $item_id );
		} catch ( \Exception $e ) {
			return false;
		}
		return $this->is_item_oai_readable( $item );
	}

	/**
	 * Fetch a single formatted record by OAI identifier.
	 *
	 * @param string $identifier
	 * @return array|null
	 */
	public function get_item( $identifier ) {
		$item_id = $this->extract_item_id( $identifier );
		if ( $item_id <= 0 ) {
			return null;
		}
		try {
			$item = new \Tainacan\Entities\Item( $item_id );
		} catch ( \Exception $e ) {
			return null;
		}
		if ( ! $item->get_id() || ! $this->is_item_oai_readable( $item ) ) {
			return null;
		}
		return $this->format_item( $item->get_id() );
	}

	/**
	 * Fetch a page of records, with the matching total in a single query.
	 *
	 * @param array $query {
	 *     @type int    $page  1-based page number.
	 *     @type int    $per   Page size.
	 *     @type int    $set   Optional collection ID filter.
	 *     @type string $from  Optional lower datestamp bound (UTC).
	 *     @type string $until Optional upper datestamp bound (UTC).
	 * }
	 * @return array { @type array $items; @type int $total }
	 */
	public function get_records( $query ) {
		$args = array(
			'posts_per_page' => (int) $query['per'],
			'paged'          => max( 1, (int) $query['page'] ),
			'order'          => 'DESC',
			'orderby'        => 'ID',
			'post_status'    => array( 'publish', 'trash' ),
		);

		$date_query = $this->build_date_query(
			isset( $query['from'] ) ? $query['from'] : null,
			isset( $query['until'] ) ? $query['until'] : null
		);
		if ( ! empty( $date_query ) ) {
			$args['date_query'] = $date_query;
		}

		$collections = array();
		if ( ! empty( $query['set'] ) ) {
			$collections = array( (int) $query['set'] );
		}

		$items_repo = \Tainacan\Repositories\Items::get_instance();
		$wp_query   = $items_repo->fetch( $args, $collections, 'WP_Query' );

		$items = array();
		foreach ( $wp_query->posts as $post ) {
			$formatted = $this->format_item( $post );
			if ( null !== $formatted ) {
				$items[] = $formatted;
			}
		}

		return array(
			'items' => $items,
			'total' => (int) $wp_query->found_posts,
		);
	}

	/**
	 * Build a WP_Query date_query on post_modified_gmt from OAI from/until.
	 *
	 * @param string|null $from
	 * @param string|null $until
	 * @return array
	 */
	private function build_date_query( $from, $until ) {
		$date_query = array();
		if ( ! empty( $from ) ) {
			$date_query[] = array(
				'column'    => 'post_modified_gmt',
				'after'     => $from,
				'inclusive' => true,
			);
		}
		if ( ! empty( $until ) ) {
			$date_query[] = array(
				'column'    => 'post_modified_gmt',
				'before'    => $until,
				'inclusive' => true,
			);
		}
		return $date_query;
	}

	/**
	 * Map an item (post or ID) to the OAI record array.
	 *
	 * @param \WP_Post|int $post
	 * @return array|null
	 */
	private function format_item( $post ) {
		try {
			$item = new \Tainacan\Entities\Item( $post );
		} catch ( \Exception $e ) {
			return null;
		}

		if ( ! $item->get_id() || ! $this->is_item_oai_readable( $item ) ) {
			return null;
		}

		$collection = $item->get_collection();
		$gmt_date   = ( $post instanceof \WP_Post && ! empty( $post->post_modified_gmt ) )
			? $post->post_modified_gmt
			: $item->get_modification_date();

		return array(
			'identifier' => $this->build_identifier( $item->get_id() ),
			'datestamp'  => gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $gmt_date . ' UTC' ) ),
			'setSpec'    => $collection ? (string) $collection->get_id() : null,
			'status'     => $item->get_status(),
			'metadata'   => 'trash' === $item->get_status() ? array() : $this->get_item_dc( $item ),
		);
	}

	/**
	 * Build the Dublin Core field map for an item.
	 *
	 * @param \Tainacan\Entities\Item $item
	 * @return array
	 */
	private function get_item_dc( $item ) {
		// Values are accumulated as arrays so Dublin Core fields can repeat, then
		// de-duplicated: a core property (e.g. the title) and a metadatum mapped
		// to the same DC element commonly carry the identical value.
		$dc = array(
			'title'      => array( $item->get_title() ),
			'identifier' => array( get_permalink( $item->get_id() ) )
		);

		if ( $item->get_description() ) {
			$dc['description'] = array( $item->get_description() );
		}

		$metadata = $item->get_metadata(
			array(
				'post_status' => 'publish',
			)
		);
		if ( is_array( $metadata ) ) {
			foreach ( $metadata as $item_meta ) {
				$metadatum = $item_meta->get_metadatum();
				if ( ! $metadatum || 'publish' !== $metadatum->get_status() ) {
					continue;
				}

				$mapping = $metadatum->get_exposer_mapping();
				if ( empty( $mapping['dublin-core'] ) ) {
					continue;
				}

				$field = str_replace( 'dc:', '', $mapping['dublin-core'] );
				$value = $item_meta->get_value_as_string();

				if ( '' === $value || null === $value ) {
					continue;
				}

				if ( ! isset( $dc[ $field ] ) ) {
					$dc[ $field ] = array();
				}
				$dc[ $field ][] = $value;
			}
		}

		foreach ( $dc as $field => $values ) {
			$dc[ $field ] = array_values( array_unique( array_filter( $values, 'strlen' ) ) );
		}

		return array_filter( $dc );
	}

	/**
	 * Whether a collection may appear as an OAI set for anonymous harvesters.
	 *
	 * Uses publish status explicitly so logged-in administrators browsing the
	 * endpoint do not widen the public harvest surface.
	 *
	 * @param \Tainacan\Entities\Collection $collection
	 * @return bool
	 */
	private function is_set_oai_readable( \Tainacan\Entities\Collection $collection ) {
		return $collection->get_id() > 0 && 'publish' === $collection->get_status();
	}

	/**
	 * Whether an item may be disseminated through the public OAI-PMH interface.
	 *
	 * Published items in published collections are harvestable. Trashed items
	 * remain visible as header-only records when deletedRecord=transient. All
	 * other statuses are withheld regardless of the current user session.
	 *
	 * @param \Tainacan\Entities\Item $item
	 * @return bool
	 */
	private function is_item_oai_readable( \Tainacan\Entities\Item $item ) {
		if ( ! $item->get_id() ) {
			return false;
		}

		$status = $item->get_status();

		if ( 'trash' === $status ) {
			return true;
		}

		if ( 'publish' !== $status ) {
			return false;
		}

		$collection = $item->get_collection();

		return $collection instanceof \Tainacan\Entities\Collection
			&& $this->is_set_oai_readable( $collection );
	}
}
