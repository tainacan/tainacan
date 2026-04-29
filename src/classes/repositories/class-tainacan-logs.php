<?php

namespace Tainacan\Repositories;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Temporary fallback for sites that cannot run the WP-CLI log migration command.
 * The TAINACAN_USE_DEPRECATED_LOGS constant must be manually defined in wp-config.php to enable this behavior.
 * This block will be removed in future versions once legacy support is discontinued.
 * 
 */ 
if (!defined('TAINACAN_USE_DEPRECATED_LOGS') || TAINACAN_USE_DEPRECATED_LOGS !== false) {
    require_once __DIR__ . '/class-tainacan-logs-deprecated.php';
    return;
}

use Tainacan\Entities;
use Tainacan\Entities\Entity;

/**
 * Repository for managing Tainacan logs.
 *
 * Implements a comprehensive logging system for tracking changes
 * and operations within Tainacan including entity modifications.
 *
 * @since 1.0.0
 */
class Logs extends Repository {
	use \Tainacan\Traits\Singleton_Instance;

	public $entities_type = '\Tainacan\Entities\Log';
	private $current_diff = null;
	private $current_deleting_entity;
	private $current_action;

	protected function init() {

		add_action( 'tainacan-insert', array( $this, 'insert_entity' ) );
		add_action( 'tainacan-pre-insert', array( $this, 'pre_insert_entity' ) );
		add_action( 'tainacan-deleted', array( $this, 'delete_entity' ), 10, 2 );
		add_action( 'tainacan-pre-delete', array( $this, 'pre_delete_entity' ), 10, 2 );

		add_action( 'add_attachment', array( $this, 'insert_attachment' ) );
		add_action( 'delete_attachment', array( $this, 'pre_delete_attachment' ) );
		add_action( 'delete_post', array( $this, 'delete_attachment' ) );

		add_filter( 'tainacan-log-set-title', array( $this, 'filter_log_title' ) );
		add_filter( 'pre_wp_unique_post_slug', array( $this, 'tainacan_set_log_slug' ), 10, 6 );
	}

	protected function _get_map() {
		$entity = $this->get_name();
		return apply_filters( "tainacan-get-map-$entity", [
			'title'          => [
				'map'         => 'post_title',
				'title'       => __( 'Title', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The title of the log', 'tainacan' ),
				'on_error'    => __( 'The title should be a text value and not empty', 'tainacan' ),
				'validation'  => ''
			],
			'date'       => [
				'map'         => 'post_date',
				'title'       => __( 'Log date', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The moment when the log was registered', 'tainacan' ),
			],
			'description'    => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The log description', 'tainacan' ),
				'default'     => '',
				'validation'  => ''
			],
			'slug'           => [
				'map'         => 'post_name',
				'title'       => __( 'Slug', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The log slug', 'tainacan' ),
				'validation'  => ''
			],
			'author'        => [
				'map'         => 'user_id',
				'title'       => __( 'User ID', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'Unique identifier', 'tainacan' ),
				'validation'  => ''
			],
			'item_id'        => [
				'map'         => 'meta',
				'title'       => __( 'Item ID', 'tainacan' ),
				'description' => __( 'Item ID', 'tainacan' ),
				'type'        => 'integer',
			],
			// 'value'          => [
			// 	'map'         => 'meta',
			// 	'title'       => __( 'Actual value', 'tainacan' ),
			// 	'type'        => 'string',
			// 	'description' => __( 'The actual log value' ),
			// 	'validation'  => ''
			// ],
			// 'log_diffs'      => [ // deprecated
			// 	'map'         => 'meta',
			// 	'title'       => __( 'Log differences', 'tainacan' ),
			// 	'description' => __( 'Differences between old and new versions of object', 'tainacan' ),
			// 	'type'        => 'string',
			// ],
			'collection_id'  => [
				'map'         => 'collection_id',
				'title'       => __( 'Log collection relationship', 'tainacan' ),
				'description' => __( 'The ID of the collection that this log is related to', 'tainacan' ),
				'type'        => 'string',
			],
			'object_id' => [
				'map'         => 'object_id',
				'title'       => __( 'Log item relationship', 'tainacan' ),
				'description' => __( 'The ID of the object that this log is related to', 'tainacan' ),
				'type'        => ['string', 'integer'],
			],
			'object_type' => [
				'map'         => 'object_type',
				'title'       => __( 'Log item relationship', 'tainacan' ),
				'description' => __( 'The type of the object that this log is related to', 'tainacan' ),
				'type'        => 'string',
			],
			'old_value' => [
				'map'         => 'old_value',
				'title'       => __( 'Old value', 'tainacan' ),
				'description' => __( 'Value of the field previous to the edition registered by the log.', 'tainacan' ),
				'type'        => 'string',
			],
			'new_value' => [
				'map'         => 'new_value',
				'title'       => __( 'New value', 'tainacan' ),
				'description'       => __( 'Value of the field after the edition registered by the log.', 'tainacan' ),
				'type'        => 'string',
			],
			'action' => [
				'map'         => 'action',
				'title'       => __( 'Action', 'tainacan' ),
				'description' => __( 'Type of action registered by the log.', 'tainacan' ),
				'type'        => 'string',
			]
		] );
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Repository::register_post_type()
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Logs', 'tainacan' ),
			'singular_name'      => __( 'Log', 'tainacan' ),
			'add_new'            => __( 'Add new', 'tainacan' ),
			'add_new_item'       => __( 'Add new Log', 'tainacan' ),
			'edit_item'          => __( 'Edit Log', 'tainacan' ),
			'new_item'           => __( 'New Log', 'tainacan' ),
			'view_item'          => __( 'View Log', 'tainacan' ),
			'search_items'       => __( 'Search Logs', 'tainacan' ),
			'not_found'          => __( 'No Logs found ', 'tainacan' ),
			'not_found_in_trash' => __( 'No Logs found in trash', 'tainacan' ),
			'parent_item_colon'  => __( 'Parent Log:', 'tainacan' ),
			'menu_name'          => __( 'Logs', 'tainacan' )
		);
		$args   = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			'public'              => false,
			'show_ui'             => tainacan_enable_dev_wp_interface(),
			'show_in_menu'        => tainacan_enable_dev_wp_interface(),
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'map_meta_cap'        => true,
			'capabilities'        => (array) $this->get_capabilities(),
			'supports'            => [
				'title',
				'editor',
				'thumbnail',
			]
		);
		register_post_type( Entities\Log::get_post_type(), $args );
	}


	/**
	 * Fetch logs from the custom wp_tainacan_logs table.
	 *
	 * When an integer is passed, returns a single Log entity matching that ID,
	 * or an empty array if not found.
	 *
	 * When an array is passed, supports the following keys:
	 *
	 * Filtering (WHERE):
	 *   - int    $item_id       Filter by item ID.
	 *   - int    $user_id       Filter by user ID.
	 *   - string $collection_id Filter by collection ID (or 'default' for repository-level).
	 *   - string $object_type   Filter by object type (fully-qualified class name).
	 *   - string $object_id     Filter by object ID.
	 *   - string $action        Filter by action key (e.g. 'create', 'update', 'delete').
	 *   - string $s             Search term matched (case-insensitive LIKE) against
	 *                           title, old_value, and new_value columns.
	 *   - array  $date_query    Array of date clauses. Each clause may contain:
	 *                             'after'     (string) – lower date bound (Y-m-d or Y-m-d H:i:s),
	 *                             'before'    (string) – upper date bound,
	 *                             'inclusive' (bool)   – whether bounds are inclusive (default false).
	 *
	 * Ordering (ORDER BY):
	 *   - string $orderby  Column to sort by. Allowed: ID, date, title, user_id,
	 *                      collection_id, item_id, action. Defaults to 'ID'.
	 *   - string $order    Sort direction: 'ASC' or 'DESC'. Defaults to 'DESC'.
	 *
	 * Pagination (LIMIT / OFFSET):
	 *   - int $posts_per_page  Number of rows to return. Use -1 for all. Defaults to -1.
	 *   - int $paged           Page number (1-based), used with posts_per_page. Defaults to 1.
	 *   - int $offset          Raw row offset, overrides paged when provided.
	 *
	 * @param array|int $args Associative array of query args, or an integer log ID.
	 *
	 * @return Entities\Log|Entities\Log[] A single entity when $args is an ID,
	 *                                     or an array of entities when $args is an array.
	 */
	public function fetch( $args = [], $_output = null ) {
		global $wpdb;

		$table = $this->get_table_name();

		// Fetch single record by ID.
		if ( is_numeric( $args ) ) {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE ID = %d", (int) $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return $row ? $this->row_to_entity( $row ) : [];
		}

		if ( ! is_array( $args ) ) {
			return [];
		}

		$args = $this->parse_fetch_args( $args );
		$args = apply_filters( 'tainacan-fetch-args', $args, 'logs' );

		// --- WHERE ---------------------------------------------------------
		[ $where_sql, $params ] = $this->build_where( $args );

		// --- ORDER BY ------------------------------------------------------
		$valid_orderby = [ 'ID', 'date', 'title', 'user_id', 'collection_id', 'item_id', 'action' ];
		$orderby = isset( $args['orderby'] ) && in_array( $args['orderby'], $valid_orderby, true )
			? $args['orderby']
			: 'ID';
		$order = isset( $args['order'] ) && strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		// --- LIMIT / OFFSET ------------------------------------------------
		$number = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : -1;
		$paged  = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : 1;
		$offset = isset( $args['offset'] )
			? (int) $args['offset']
			: ( $number > 0 ? ( $paged - 1 ) * $number : 0 );

		$limit_sql = $number > 0 ? sprintf( 'LIMIT %d OFFSET %d', $number, $offset ) : '';

		$sql = "SELECT * FROM $table $where_sql ORDER BY `$orderby` $order $limit_sql";

		if ( $params ) {
			$sql = $wpdb->prepare( $sql, ...$params );
		}

		$rows = $wpdb->get_results( $sql );

		return array_map( [ $this, 'row_to_entity' ], $rows ?: [] );
	}

	/**
	 * Count logs matching the given filters.
	 *
	 * Accepts the same filtering args as fetch() (item_id, user_id,
	 * collection_id, object_type, object_id, action) but ignores
	 * pagination and ordering — it always returns an integer.
	 *
	 * Typical pagination usage:
	 *   $total      = $logs->fetch_count( $filters );
	 *   $rows       = $logs->fetch( array_merge( $filters, [ 'posts_per_page' => 20, 'paged' => 2 ] ) );
	 *   $total_pages = ceil( $total / 20 );
	 *
	 * @param array $args Same filtering keys supported by fetch().
	 * @return int Total number of matching rows.
	 */
	public function fetch_count( array $args = [] ) {
		global $wpdb;

		$table  = $this->get_table_name();
		$args = $this->parse_fetch_args( $args );
		$args = apply_filters( 'tainacan-fetch-args', $args, 'logs' );

		[ $where_sql, $params ] = $this->build_where( $args );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = "SELECT COUNT(*) FROM $table $where_sql";

		if ( $params ) {
			$sql = $wpdb->prepare( $sql, ...$params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Build a parameterized WHERE clause from a filter args array.
	 *
	 * Column names are taken from a whitelist, so they are never
	 * interpolated from user input. Values are returned as a separate
	 * $params array to be bound via $wpdb->prepare().
	 *
	 * Supports an optional date_query key — an array of clause arrays, each
	 * accepting: before (string), after (string), inclusive (bool|string).
	 * Date-only values (Y-m-d) are automatically expanded to full datetimes.
	 * Example:
	 *   'date_query' => [ [ 'after' => '2026-04-09', 'before' => '2026-04-11', 'inclusive' => true ] ]
	 *
	 * @param array $args Filtering args (same keys accepted by fetch()).
	 * @return array{ 0: string, 1: array } Tuple of [ $where_sql, $params ].
	 *               $where_sql is either an empty string or 'WHERE col = %x AND …'.
	 *               $params holds the corresponding values in order.
	 */
	private function build_where( array $args ) {
		global $wpdb;
		$wheres = [];
		$params = [];

		foreach ( [ 'item_id', 'user_id' ] as $col ) {
			if ( isset( $args[ $col ] ) ) {
				$wheres[] = "`$col` = %d";
				$params[]  = (int) $args[ $col ];
			}
		}

		foreach ( [ 'collection_id', 'object_type', 'object_id', 'action' ] as $col ) {
			if ( isset( $args[ $col ] ) ) {
				$wheres[] = "`$col` = %s";
				$params[]  = $args[ $col ];
			}
		}

		if ( ! empty( $args['date_query'] ) && is_array( $args['date_query'] ) ) {
			foreach ( $args['date_query'] as $clause ) {
				if ( ! is_array( $clause ) ) {
					continue;
				}
				$inclusive = isset( $clause['inclusive'] ) && filter_var( $clause['inclusive'], FILTER_VALIDATE_BOOLEAN );

				if ( ! empty( $clause['after'] ) ) {
					if ( $inclusive ) {
						$wheres[] = '`date` >= %s';
						$params[] = $this->normalize_date_bound( $clause['after'], 'start' );
					} else {
						$wheres[] = '`date` > %s';
						$params[] = $this->normalize_date_bound( $clause['after'], 'end' );
					}
				}

				if ( ! empty( $clause['before'] ) ) {
					if ( $inclusive ) {
						$wheres[] = '`date` <= %s';
						$params[] = $this->normalize_date_bound( $clause['before'], 'end' );
					} else {
						$wheres[] = '`date` < %s';
						$params[] = $this->normalize_date_bound( $clause['before'], 'start' );
					}
				}
			}
		}

		if ( ! empty( $args['s'] ) ) {
			$like      = '%' . $wpdb->esc_like( $args['s'] ) . '%';
			$wheres[]  = '(`title` LIKE %s OR `old_value` LIKE %s OR `new_value` LIKE %s)';
			$params[]  = $like;
			$params[]  = $like;
			$params[]  = $like;
		}

		$where_sql = $wheres ? 'WHERE ' . implode( ' AND ', $wheres ) : '';

		return [ $where_sql, $params ];
	}

	/**
	 * Expands a date-only string (Y-m-d) to a full datetime for WHERE comparisons.
	 * Datetime strings that already include a time component are returned as-is.
	 *
	 * @param string $date  A date or datetime string.
	 * @param string $bound 'start' → appends 00:00:00, 'end' → appends 23:59:59.
	 * @return string
	 */
	private function normalize_date_bound( string $date, string $bound ): string {
		if ( preg_match( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $date ) ) {
			return $date;
		}
		return $date . ( $bound === 'end' ? ' 23:59:59' : ' 00:00:00' );
	}

	/**
	 * Build a Log entity from a custom table row.
	 *
	 * Fields that have setters are stored as class properties via
	 * set_mapped_property(), so get_mapped_property() returns them
	 * directly without hitting WP_Post or wp_postmeta.
	 *
	 * Fields without setters (date, slug, id, status) are written
	 * directly onto the entity's WP_Post stub so the inherited
	 * get_mapped_property() fallback can still find them.
	 *
	 * @param \stdClass $row Row returned by $wpdb->get_row() / get_results().
	 * @return Entities\Log
	 */
	private function row_to_entity( \stdClass $row ) {
		$log = new Entities\Log();

		$log->WP_Post->ID          = (int) $row->ID;
		$log->WP_Post->post_date   = $row->date;
		$log->WP_Post->post_name   = $row->slug;
		$log->WP_Post->post_status = 'publish';
		$log->WP_Post->post_type   = Entities\Log::get_post_type();

		$log->set_title( $row->title );
		$log->set_description( $row->description );
		$log->set_user_id( (int) $row->user_id );
		$log->set_collection_id( $row->collection_id );
		$log->set_item_id( (int) $row->item_id );
		$log->set_object_type( $row->object_type );
		$log->set_object_id( $row->object_id );
		$log->set_old_value( maybe_unserialize( $row->old_value ) );
		$log->set_new_value( maybe_unserialize( $row->new_value ) );
		$log->set_action( $row->action );

		return $log;
	}

	/**
	 * Returns the name of the custom logs table.
	 *
	 * @return string
	 */
	public function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'tainacan_logs';
	}

	/**
	 * Persist a Log entity into the custom wp_tainacan_logs table.
	 *
	 * Uses $wpdb->insert() with explicit format specifiers so all values
	 * go through wpdb's internal prepare(), preventing SQL injection.
	 * Serializable fields (old_value, new_value) are passed through
	 * maybe_serialize() before storage.
	 *
	 * @param Entities\Log $obj
	 * @return Entities\Log|false The entity with its new ID set, or false on failure.
	 * @throws \Exception When the entity has not been validated before insert.
	 */
	public function insert( $obj ) {
		global $wpdb;

		if ( ! $obj instanceof Entities\Log ) {
			return false;
		}

		do_action( 'tainacan-pre-insert', $obj );
		do_action( 'tainacan-pre-insert-' . Entities\Log::get_post_type(), $obj );

		$old_value = $obj->get_old_value();
		$new_value = $obj->get_new_value();

		$data = [
			'title'            => $this->sanitize_value( (string) $obj->get_title() ),
			'date'             => $obj->get_date() ?: current_time( 'mysql' ),
			'description'      => $this->sanitize_value( (string) $obj->get_description() ),
			'slug'             => uniqid( Entities\Log::get_post_type() . '-' ),
			'user_id'          => absint( $obj->get_user_id() ) ?: absint( get_current_user_id() ),
			'collection_id'    => (string) $obj->get_collection_id(),
			'item_id'          => absint( $obj->get_item_id() ),
			'object_type'      => (string) $obj->get_object_type(),
			'object_id'        => (string) $obj->get_object_id(),
			'old_value'        => maybe_serialize( $old_value ),
			'new_value'        => maybe_serialize( $new_value ),
			'action'           => (string) $obj->get_action(),
			'user_edit_lastr' => absint( get_current_user_id() ),
		];

		$formats = [
			'%s', // title
			'%s', // date
			'%s', // description
			'%s', // slug
			'%d', // user_id
			'%s', // collection_id ('default' or numeric string)
			'%d', // item_id
			'%s', // object_type
			'%s', // object_id
			'%s', // old_value
			'%s', // new_value
			'%s', // action
			'%d', // user_edit_lastr
		];

		$result = $wpdb->insert( $this->get_table_name(), $data, $formats );

		if ( false === $result ) {
			return false;
		}

		$obj->WP_Post->ID = $wpdb->insert_id;

		do_action( 'tainacan-insert', $obj, [], false );
		do_action( 'tainacan-insert-' . Entities\Log::get_post_type(), $obj );

		return $obj;
	}

	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	/**
	 * Fetch most recent log.
	 *
	 * @return Entities\Log|null The most recent Log entity, or null if none exists.
	 */
	public function fetch_last() {
		$logs = $this->fetch( [
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'DESC',
		] );

		return array_pop( $logs );
	}

	/**
	 * Callback to generate log when attachments are added to any Tainacan entity
	 */
	public function insert_attachment( $post_ID ) {
		$attachment = get_post( $post_ID );
		$post       = $attachment->post_parent;

		if ( $post ) { // attached to a post

			$entity = Repository::get_entity_by_post( $post );

			if ( $entity ) { // attached to a tainacan entity

				$log = new Entities\Log();

				$collection_id = method_exists($entity, 'get_collection_id') ? $entity->get_collection_id() : 'default';

				// @TODO: definir melhor forma de controlar quais logs devem ser gerados
				// if ( method_exists($entity, 'get_repository') && !$entity->get_repository()->use_logs ) {
				// 	return;
				// }

				if ( $entity instanceof Entities\Collection ) {
					$collection_id = $entity->get_id();
					/* translators: %s is the collection name */
					$log->set_title( sprintf( __( 'New file was attached to Collection "%s"', 'tainacan'), $entity->get_name() ) );
				}
				if ( $entity instanceof Entities\Item ) {
					$log->set_item_id($entity->get_id());
					/* translators: %s is the item title */
					$log->set_title( sprintf( __( 'New file was attached to Item "%s"', 'tainacan'), $entity->get_title() ) );
				}

				$object_type = 'attachment'; //get_class($attachment);
				$object_id = $entity->get_id();

				$log->set_collection_id($collection_id);
				$log->set_object_type($object_type);
				$log->set_object_id($post_ID);
				$log->set_action('new-attachment');

				$prepared = [
					'id'          => $attachment->ID,
					'title'       => $attachment->post_title,
					'description' => $attachment->post_content,
					'mime_type'   => $attachment->post_mime_type,
				];

				$log->set_new_value($prepared);

				if ( $log->validate() ) {
					$this->insert($log);
				}

			}
		}

	}

	/**
	 * Callback to generate log when attachments attached to any Tainacan entity are deleted
	 */
	public function pre_delete_attachment($attachment_id) {

		$attachment_post = get_post($attachment_id);

		$entity_post = get_post($attachment_post->post_parent);

		if ( $entity_post ) {
			try {
				$entity = Repository::get_entity_by_post( $entity_post );

				if ( $entity ) {
					if ( method_exists($entity, 'get_repository') && !$entity->get_repository()->use_logs ) {
						return;
					}
					$collection_id = method_exists($entity, 'get_collection_id') ? $entity->get_collection_id() : 'default';

					$log = new Entities\Log();

					if ( $entity instanceof Entities\Collection ) {
						$collection_id = $entity->get_id();
						/* translators: %s is the collection name */
						$log->set_title( sprintf(__( 'File attached to Collection "%s" was removed', 'tainacan'), $entity->get_name() ) );
					}
					if ( $entity instanceof Entities\Item ) {
						$log->set_item_id($entity->get_id());
						/* translators: %s is the item title */
						$log->set_title( sprintf( __( 'File attached to Item "%s" was removed' , 'tainacan'), $entity->get_title() ) );
					}

					$object_type = 'attachment'; //get_class($entity);
					$object_id = $attachment_id; //$entity->get_id();

					$preapred = [
						'id'          => $attachment_id,
						'title'       => $attachment_post->post_title,
						'description' => $attachment_post->post_content,
					];

					$log->set_collection_id($collection_id);
					$log->set_object_type($object_type);
					$log->set_object_id($object_id);
					$log->set_old_value($preapred);
					$log->set_action('delete-attachment');

					$this->current_attachment_delete_log = $log;

				}
			} catch (\Exception $e) {
				error_log("[pre_delete_attachment]:" . $e->getMessage());
			}

		}
	}

	/**
	 * Callback to generate log when attachments attached to any Tainacan entity are deleted
	 */
	public function delete_attachment($attachment_id) {
		if ( isset($this->current_attachment_delete_log) && $this->current_attachment_delete_log instanceof Entities\Log ) {
			$log = $this->current_attachment_delete_log;
			$att = $log->get_old_value();
			if ( is_array($att) && isset($att['id']) && $att['id'] == $attachment_id && $log->validate() ) {
				$this->insert($log);
			}
		}
	}

	/**
	 * Compare two repository entities and sets the current_diff property to be used in the insert hook
	 *
	 * @param Entity $unsaved The new entity that is going to be saved
	 *
	 * @return void
	 */
	public function pre_insert_entity( Entities\Entity $unsaved ) {

		// @TODO: definir melhor forma de controlar quais logs devem ser gerados
		// if ( ! $unsaved->get_repository()->use_logs ) {
		// 	return;
		// }

		if ( $unsaved instanceof Entities\Item_Metadata_Entity ) {
			return $this->prepare_item_metadata_diff($unsaved);
		}

		// do not log a log
		if ( ( method_exists( $unsaved, 'get_post_type' ) && $unsaved->get_post_type() === 'tainacan-log' ) || $unsaved->get_status() === 'auto-draft' ) {
			return;
		}

		$creating = true;

		$old = null;

		if ( is_numeric( $unsaved->get_id() ) ) {
			if ( $unsaved instanceof Entities\Term ) {
				$old = $unsaved->get_repository()->fetch( $unsaved->get_id(), $unsaved->get_taxonomy() );
			} else {
				$old = $unsaved->get_repository()->fetch( $unsaved->get_id() );
			}
		}


		if ( $old instanceof Entities\Entity ) {

			if ( $old->get_status() !== 'auto-draft' ) {
				$creating = false;
			}

		}

		$diff = [
			'old' => [],
			'new' => []
		];

		$has_diff = false;

		if ( $creating ) {
			$diff['new'] = $unsaved->_toArray();
			$has_diff = true;
		} else {
			$map = $unsaved->get_repository()->get_map();

			foreach ( $map as $prop => $mapped ) {
				if ( $old->get( $prop ) != $unsaved->get( $prop ) ) {

					$diff['old'][$prop] = $old->get( $prop );
					$diff['new'][$prop] = $unsaved->get( $prop );
					$has_diff = true;

				}
			}
		}

		$diff = apply_filters( 'tainacan-entity-diff', $diff, $unsaved, $old );

		$this->current_diff = $has_diff ? $diff : false;
		$this->current_action = $creating ? 'create' : 'update';

	}


	private function prepare_item_metadata_diff( Entities\Entity $unsaved ) {

		$diff = [
			'old' => [],
			'new' => []
		];

		$old = new Entities\Item_Metadata_Entity($unsaved->get_item(), $unsaved->get_metadatum());

		add_filter('tainacan-item-metadata-get-multivalue-separator', [$this, '__temporary_multivalue_separator']);

		if ( $old instanceof Entities\Item_Metadata_Entity ) {
			$diff['old'] = \explode($this->__temporary_multivalue_separator(''), $old->get_value_as_string());
		}

		$diff['new'] = \explode($this->__temporary_multivalue_separator(''), $unsaved->get_value_as_string());

		remove_filter('tainacan-item-metadata-get-multivalue-separator', [$this, '__temporary_multivalue_separator']);

		$diff = apply_filters( 'tainacan-entity-diff', $diff, $unsaved, $old );

		$this->current_diff = $diff;
		$this->current_action = 'update-metadata-value';

	}

	public function __temporary_multivalue_separator($sep) {
		return '--xx--';
	}

	/**
	 * Callback to generate log when Tainacan entities are edited
	 */
	public function insert_entity( Entities\Entity $entity ) {

		// @TODO: definir melhor forma de controlar quais logs devem ser gerados
		// if ( ! $entity->get_repository()->use_logs ) {
		// 	return;
		// }

		if ( $entity instanceof Entities\Item_Metadata_Entity ) {
			return $this->insert_item_metadata($entity);
		}

		// do not log a log
		if ( ( method_exists( $entity, 'get_post_type' ) && $entity->get_post_type() === 'tainacan-log' ) || $entity->get_status() === 'auto-draft' ) {
			return false;
		}

		$log = new Entities\Log();
		$log->set_action($this->current_action);

		$collection_id = method_exists($entity, 'get_collection_id') ? $entity->get_collection_id() : 'default';

		$diff = $this->current_diff;

		if (false === $diff) {
			return;
		}

		if ( $entity instanceof Entities\Collection ) {

			$collection_id = $entity->get_id();

			if ($this->current_action == 'update') {
				if (isset($diff['new']['metadata_order'])) {
					/* translators: %s is the collection name */
					$log->set_title( sprintf( __( 'Collection "%s" metadata order was updated', 'tainacan'), $entity->get_name() ) );
					$log->set_action('update-metadata-order');
				} elseif (isset($diff['new']['filters_order'])) {
					/* translators: %s is the collection name */
					$log->set_title( sprintf( __( 'Collection "%s" filters order was updated', 'tainacan'), $entity->get_name() ) );
					$log->set_action('update-filters-order');
				} else {
					/* translators: %s is the collection name */
					$log->set_title( sprintf( __( 'Collection "%s" was updated', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ($this->current_action == 'create') {
				/* translators: %s is the collection name */
				$log->set_title( sprintf( __( 'Collection "%s" was created', 'tainacan'), $entity->get_name() ) );
			}

		} elseif ( $entity instanceof Entities\Item ) {

			$log->set_item_id($entity->get_id());

			if ($this->current_action == 'update') {
				if (isset($diff['new']['document'])) {
					/* translators: %s is the item title */
					$log->set_title( sprintf( __( 'Item "%s" document was updated', 'tainacan'), $entity->get_title() ) );
					$log->set_action('update-document');
				} elseif (isset($diff['new']['_thumbnail_id'])) {
					/* translators: %s is the item title */
					$log->set_title( sprintf( __( 'Item "%s" thumbnail was updated', 'tainacan'), $entity->get_title() ) );
					$log->set_action('update-thumbnail');
				} else {
					/* translators: %s is the item title */
					$log->set_title( sprintf( __( 'Item "%s" was updated', 'tainacan'), $entity->get_title() ) );
				}
			} elseif ($this->current_action == 'create') {
				/* translators: %1$s is the item title, %2$s is the item ID */
				$log->set_title( sprintf( __( 'Item "%1$s" was created with the ID %2$s', 'tainacan'), $entity->get_title(), $entity->get_id() ) );
			}
		} elseif ( $entity instanceof Entities\Filter ) {

			if ( 'default' == $collection_id ) {
				if ($this->current_action == 'update') {
					/* translators: %s is the filter name */
					$log->set_title( sprintf( __( 'Filter "%s" was updated in repository level', 'tainacan'), $entity->get_name() ) );
				} elseif ($this->current_action == 'create') {
					/* translators: %s is the filter name */
					$log->set_title( sprintf( __( 'Filter "%s" was added to the repository', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ( is_numeric($collection_id) ) {
				$collection = $entity->get_collection();

				if ( $collection instanceof Entities\Collection ) {
					if ($this->current_action == 'update') {
						/* translators: %1$s is the filter name, %2$s is the collection name */
						$log->set_title( sprintf( __( 'Filter "%1$s" was updated in Collection "%2$s"', 'tainacan'), $entity->get_name(), $collection->get_name() ) );
					} elseif ($this->current_action == 'create') {
						/* translators: %1$s is the filter name, %2$s is the collection name */
						$log->set_title( sprintf( __( 'Filter "%1$s" was added to Collection "%2$s"', 'tainacan'), $entity->get_name(), $collection->get_name() ) );
					}
				} else {
					if ($this->current_action == 'update') {
						/* translators: %s is the filter name */
						$log->set_title( sprintf( __( 'Filter "%s" was updated in unknown collection', 'tainacan'), $entity->get_name() ) );
					} elseif ($this->current_action == 'create') {
						/* translators: %s is the filter name */
						$log->set_title( sprintf( __( 'Filter "%s" was added to unknown collection', 'tainacan'), $entity->get_name() ) );
					}
				}
			}

		} elseif ( $entity instanceof Entities\Metadatum ) {

			if ( 'default' == $collection_id ) {
				if ($this->current_action == 'update') {
					/* translators: %s is the metadatum name */
					$log->set_title( sprintf( __( 'Metadatum "%s" was updated in repository level', 'tainacan'), $entity->get_name() ) );
				} elseif ($this->current_action == 'create') {
					/* translators: %s is the metadatum name */
					$log->set_title( sprintf( __( 'Metadatum "%s" was added to the repository', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ( is_numeric($collection_id) ) {
				$collection = $entity->get_collection();

				if ( $collection instanceof Entities\Collection ) {
					if ($this->current_action == 'update') {
						/* translators: %1$s is the metadatum name, %2$s is the collection name */
						$log->set_title( sprintf( __( 'Metadatum "%1$s" was updated in Collection "%2$s"', 'tainacan'), $entity->get_name(), $collection->get_name() ) );
					} elseif ($this->current_action == 'create') {
						/* translators: %1$s is the metadatum name, %2$s is the collection name */
						$log->set_title( sprintf( __( 'Metadatum "%1$s" was added to Collection "%2$s"', 'tainacan'), $entity->get_name(), $collection->get_name() ) );
					}
				} else {
					if ($this->current_action == 'update') {
						/* translators: %s is the metadatum name */
						$log->set_title( sprintf( __( 'Metadatum "%s" was updated in unknown collection', 'tainacan'), $entity->get_name() ) );
					} elseif ($this->current_action == 'create') {
						/* translators: %s is the metadatum name */
						$log->set_title( sprintf( __( 'Metadatum "%s" was added to unknown collection', 'tainacan'), $entity->get_name() ) );
					}
				}
			}

		} elseif ( $entity instanceof Entities\Taxonomy ) {

			if ($this->current_action == 'update') {
				/* translators: %s is the taxonomy name */
				$log->set_title( sprintf( __( 'Taxonomy "%s" was updated', 'tainacan'), $entity->get_name() ) );
			} elseif ($this->current_action == 'create') {
				/* translators: %s is the taxonomy name */
				$log->set_title( sprintf( __( 'Taxonomy "%s" was created', 'tainacan'), $entity->get_name() ) );
			}

		}  elseif ( $entity instanceof Entities\Term ) {

			$taxonomy = Taxonomies::get_instance()->fetch_by_db_identifier($entity->get_taxonomy());
			$tax_name = '';
			if ($taxonomy instanceof Entities\Taxonomy) {
				$tax_name = $taxonomy->get_name();
			}

			if ($this->current_action == 'update') {
				/* translators: %1$s is the term name, %2$s is the taxonomy name */
				$log->set_title( sprintf( __( 'Term "%1$s" was updated in "%2$s" taxonomy', 'tainacan'), $entity->get_name(), $tax_name ) );
			} elseif ($this->current_action == 'create') {
				/* translators: %1$s is the term name, %2$s is the taxonomy name */
				$log->set_title( sprintf( __( 'Term "%1$s" was added to "%2$s" taxonomy', 'tainacan'), $entity->get_name(), $tax_name ) );
			}

		}

		$object_type = get_class($entity);
		$object_id = $entity->get_id();

		$log->set_collection_id($collection_id);
		$log->set_object_type($object_type);
		$log->set_object_id($object_id);
		$log->set_old_value($diff['old']);
		$log->set_new_value($diff['new']);


		if ( $log->validate() ) {
			$this->insert($log);
		}

	}

	public function pre_delete_entity( Entities\Entity $entity, $permanent) {

		if ( ! $entity->get_repository()->use_logs ) {
			return;
		}

		// do not log a log
		if ( ( method_exists( $entity, 'get_post_type' ) && $entity->get_post_type() === 'tainacan-log' ) || $entity->get_status() === 'auto-draft' ) {
			return false;
		}

		$this->current_deleting_entity = $entity->_toArray();
		$this->current_action = $permanent ? 'delete' : 'trash';

	}

	public function delete_entity( Entities\Entity $entity, $permanent) {

		if ( ! $entity->get_repository()->use_logs ) {
			return;
		}

		// do not log a log
		if ( ( method_exists( $entity, 'get_post_type' ) && $entity->get_post_type() === 'tainacan-log' ) || $entity->get_status() === 'auto-draft' ) {
			return false;
		}

		$log = new Entities\Log();

		$collection_id = method_exists($entity, 'get_collection_id') ? $entity->get_collection_id() : 'default';

		if ( $entity instanceof Entities\Collection ) {

			$collection_id = $entity->get_id();

			if ($this->current_action == 'delete') {
				/* translators: %s is the collection name */
				$log->set_title( sprintf( __( 'Collection "%s" was permanently deleted', 'tainacan'), $entity->get_name() ) );
			} elseif ($this->current_action == 'trash') {
				/* translators: %s is the collection name */
				$log->set_title( sprintf( __( 'Collection "%s" was moved to trash', 'tainacan'), $entity->get_name() ) );
			}

		} elseif ( $entity instanceof Entities\Item ) {

			$log->set_item_id($entity->get_id());

			if ($this->current_action == 'delete') {
				/* translators: %1$s is the item title, %2$s is the item ID */
				$log->set_title( sprintf( __( 'Item "%1$s" (ID %2$s) was updated', 'tainacan'), $entity->get_title(), $entity->get_id() ) );
			} elseif ($this->current_action == 'trash') {
				/* translators: %1$s is the item title, %2$s is the item ID */
				$log->set_title( sprintf( __( 'Item "%1$s" (ID %2$s) was moved to trash', 'tainacan'), $entity->get_title(), $entity->get_id() ) );
			}
		} elseif ( $entity instanceof Entities\Filter ) {

			if ( 'default' == $collection_id ) {
				if ($this->current_action == 'delete') {
					/* translators: %s is the filter name */
					$log->set_title( sprintf( __( 'Filter "%s" was permanently deleted from the repository', 'tainacan'), $entity->get_name() ) );
				} elseif ($this->current_action == 'trash') {
					/* translators: %s is the filter name */
					$log->set_title( sprintf( __( 'Repository Filter "%s" was moved to trash', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ( is_numeric($collection_id) ) {
				$collection = $entity->get_collection();

				if ( $collection instanceof Entities\Collection ) {
					if ($this->current_action == 'delete') {
						/* translators: %1$s is the filter name, %2$s is the collection name */
						$log->set_title( sprintf( __( 'Filter "%1$s" was permanently deleted from Collection "%2$s"', 'tainacan'), $entity->get_name(), $collection->get_name() ) );
					} elseif ($this->current_action == 'trash') {
						/* translators: %1$s is the filter name, %2$s is the collection name */
						$log->set_title( sprintf( __( 'Filter "%1$s" was moved to trash in Collection "%2$s"', 'tainacan'), $entity->get_name(), $collection->get_name() ) );
					}
				} else {
					if ($this->current_action == 'delete') {
						/* translators: %s is the filter name */
						$log->set_title( sprintf( __( 'Filter "%s" was permanently deleted from unknown collection', 'tainacan'), $entity->get_name() ) );
					} elseif ($this->current_action == 'trash') {
						/* translators: %s is the filter name */
						$log->set_title( sprintf( __( 'Filter "%s" was moved to trash in unknown collection', 'tainacan'), $entity->get_name() ) );
					}
				}
			}

		} elseif ( $entity instanceof Entities\Metadatum ) {

			if ( 'default' == $collection_id ) {
				if ($this->current_action == 'delete') {
					/* translators: %s is the metadatum name */
					$log->set_title( sprintf( __( 'Metadatum "%s" was permanently deleted from the repository', 'tainacan'), $entity->get_name() ) );
				} elseif ($this->current_action == 'trash') {
					/* translators: %s is the metadatum name */
					$log->set_title( sprintf( __( 'Repository Metadatum "%s" was moved to trash', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ( is_numeric($collection_id) ) {
				$collection = $entity->get_collection();

				if ( $collection instanceof Entities\Collection ) {
					if ($this->current_action == 'delete') {
						/* translators: %1$s is the metadatum name, %2$s is the collection name */
						$log->set_title( sprintf( __( 'Metadatum "%1$s" was permanently deleted from Collection "%2$s"', 'tainacan'), $entity->get_name(), $collection->get_name() ) );
					} elseif ($this->current_action == 'trash') {
						/* translators: %1$s is the metadatum name, %2$s is the collection name */
						$log->set_title( sprintf( __( 'Metadatum "%1$s" was moved to trash in Collection "%2$s"', 'tainacan'), $entity->get_name(), $collection->get_name() ) );
					}
				} else {
					if ($this->current_action == 'delete') {
						/* translators: %s is the metadatum name */
						$log->set_title( sprintf( __( 'Metadatum "%s" was permanently deleted from unknown collection', 'tainacan'), $entity->get_name() ) );
					} elseif ($this->current_action == 'trash') {
						/* translators: %s is the metadatum name */
						$log->set_title( sprintf( __( 'Metadatum "%s" was moved to trash in unknown collection', 'tainacan'), $entity->get_name() ) );
					}
				}
			}

		} elseif ( $entity instanceof Entities\Taxonomy ) {

			if ($this->current_action == 'delete') {
				/* translators: %s is the taxonomy name */
				$log->set_title( sprintf( __( 'Taxonomy "%s" was permanently deleted', 'tainacan'), $entity->get_name() ) );
			} elseif ($this->current_action == 'trash') {
				/* translators: %s is the taxonomy name */
				$log->set_title( sprintf( __( 'Taxonomy "%s" was moved to trash', 'tainacan'), $entity->get_name() ) );
			}

		}  elseif ( $entity instanceof Entities\Term ) {

			$taxonomy = Taxonomies::get_instance()->fetch_by_db_identifier($entity->get_taxonomy());
			$tax_name = '';
			if ($taxonomy instanceof Entities\Taxonomy) {
				$tax_name = $taxonomy->get_name();
			}

			if ($this->current_action == 'delete') {
				/* translators: %1$s is the term name, %2$s is the taxonomy name */
				$log->set_title( sprintf( __( 'Term "%1$s" was permanently deleted from "%2$s" taxonomy', 'tainacan'), $entity->get_name(), $tax_name ) );
			} elseif ($this->current_action == 'trash') {
				/* translators: %1$s is the term name, %2$s is the taxonomy name */
				$log->set_title( sprintf( __( 'Term "%1$s" was moved to trash in "%2$s" taxonomy', 'tainacan'), $entity->get_name(), $tax_name ) );
			}

		}


		$object_type = get_class($entity);
		$object_id = $entity->get_id();

		$diff = $this->current_diff;

		$log->set_collection_id($collection_id);
		$log->set_object_type($object_type);
		$log->set_object_id($object_id);
		$log->set_action($this->current_action);

		if ( $permanent ) {
			$log->set_old_value( $this->current_deleting_entity );
		} else {
			$log->set_old_value( ['status' => $entity->get_status()] );
			$log->set_new_value( ['status' => 'trash']  );
		}


		if ( $log->validate() ) {
			$this->insert($log);
		}

	}

	private function insert_item_metadata( Entities\Item_Metadata_Entity $entity ) {
		if($this->current_diff == false) {
			return;
		}
		$log = new Entities\Log();

		$item_id = $entity->get_item()->get_id();
		$collection_id = $entity->get_item()->get_collection_id();
		$object_type = get_class($entity);
		$object_id = $entity->get_metadatum()->get_id();

		$diff = $this->current_diff;

		$log->set_collection_id($collection_id);
		$log->set_object_type($object_type);
		$log->set_object_id($object_id);
		$log->set_item_id($item_id);
		$log->set_old_value($diff['old']);
		$log->set_new_value($diff['new']);
		$log->set_action($this->current_action);

		$meta_name = $entity->get_metadatum()->get_name();
		$item_title = $entity->get_item()->get_title();

		/* translators: %1$s is the metadatum name, %2$s is the item title */
		$title = sprintf( __( 'Value for %1$s metadatum was updated in item "%2$s"', 'tainacan' ), $meta_name, $item_title );

		$log->set_title($title);

		if ( $log->validate() ) {
			$this->insert($log);
		}

	}

	public function filter_log_title($title) {
		if (defined('TAINACAN_DOING_IMPORT') && true === TAINACAN_DOING_IMPORT) {
			$_title = __('Importer', 'tainacan');
			$title .= " ($_title)";
		}
		return $title;
	}

	function tainacan_set_log_slug( $override, $slug, $post_ID, $post_status, $post_type, $post_parent ) {
		if ( 'tainacan-log' === $post_type ) {
			if ( $post_ID ) {
				return uniqid( $post_type . '-' . $post_ID );
			}
			return uniqid( $post_type . '-' );
		}
		return $override;
	}
}
