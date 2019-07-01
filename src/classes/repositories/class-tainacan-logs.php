<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;
use Tainacan\Entities\Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Implement a Logs system
 *
 * @author medialab
 *
 */
class Logs extends Repository {
	public $entities_type = '\Tainacan\Entities\Log';
	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	protected function __construct() {
		parent::__construct();

		add_action( 'add_attachment', array( $this, 'prepare_attachment_log_before_insert' ), 10 );
	}

	public function get_map() {
		return apply_filters( 'tainacan-get-map-' . $this->get_name(), [
			'title'          => [
				'map'         => 'post_title',
				'title'       => __( 'Title', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The title of the log', 'tainacan' ),
				'on_error'    => __( 'The title should be a text value and not empty', 'tainacan' ),
				'validation'  => ''
			],
			'log_date'       => [
				'map'         => 'post_date',
				'title'       => __( 'Log date', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The log date', 'tainacan' ),
			],
			'order'          => [
				'map'         => 'menu_order',
				'title'       => __( 'Menu order', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Log order' ),
				'validation'  => ''
			],
			'parent'         => [
				'map'         => 'parent',
				'title'       => __( 'Parent', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Log order' ),
				'validation'  => ''
			],
			'description'    => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The log description' ),
				'default'     => '',
				'validation'  => ''
			],
			'slug'           => [
				'map'         => 'post_name',
				'title'       => __( 'Slug', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The log slug' ),
				'validation'  => ''
			],
			'items_per_page' => [
				'map'         => 'meta',
				'title'       => __( 'Items per page', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'The quantity of items that should be loaded' ),
				'validation'  => ''
			],
			'user_id'        => [
				'map'         => 'post_author',
				'title'       => __( 'User ID', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'Unique identifier' ),
				'validation'  => ''
			],
			'blog_id'        => [
				'map'         => 'meta',
				'title'       => __( 'Blog ID', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'Unique identifier' ),
				'validation'  => ''
			],
			'value'          => [
				'map'         => 'meta',
				'title'       => __( 'Actual value', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The actual log value' ),
				'validation'  => ''
			],
			'log_diffs'      => [
				'map'         => 'meta',
				'title'       => __( 'Log differences', 'tainacan' ),
				'description' => __( 'Differences between old and new versions of object', 'tainacan' )
			],
			'collection_id'  => [
				'map'         => 'meta',
				'title'       => __( 'Log collection relationship', 'tainacan' ),
				'description' => __( 'The ID of the collection that this log is related to', 'tainacan' )
			],
			'item_id' => [
				'map'         => 'meta',
				'title'       => __( 'Log item relationship', 'tainacan' ),
				'description' => __( 'The id of the item that this log is related to', 'tainacan' ),
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
			'show_ui'             => tnc_enable_dev_wp_interface(),
			'show_in_menu'        => tnc_enable_dev_wp_interface(),
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'map_meta_cap'        => true,
			'capability_type'     => Entities\Log::get_capability_type(),
			'supports'            => [
				'title',
				'editor',
				'thumbnail',
			]
		);
		register_post_type( Entities\Log::get_post_type(), $args );
	}


	/**
	 * fetch logs based on ID or WP_Query args
	 *
	 * Logs are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 * 
	 * If a number is passed to $args, it will return a \Tainacan\Entities\Log object.  But if the post is not found or
	 * does not match the entity post type, it will return an empty array
	 *
	 * @param array $args WP_Query args || int $args the log id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return \WP_Query|Array an instance of wp query OR array of entities;
	 */
	public function fetch( $args = [], $output = null ) {
		if ( is_numeric( $args ) ) {
			
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				try {
					return new Entities\Log( $existing_post );
				} catch (\Exception $e) {
					return [];
				}
			} else {
				return [];
			}

		} elseif ( is_array( $args ) ) {
			$args = array_merge( [
				'posts_per_page' => - 1,
			], $args );

			$args = $this->parse_fetch_args( $args );

			$args['post_type'] = Entities\Log::get_post_type();

			$args = apply_filters( 'tainacan_fetch_args', $args, 'logs' );

			$wp_query = new \WP_Query( $args );

			return $this->fetch_output( $wp_query, $output );
		}
	}

	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	public function fetch_last() {
		$args = [
			'post_type'      => Entities\Log::get_post_type(),
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'DESC'
		];

		$logs = $this->fetch( $args, 'OBJECT' );

		return array_pop( $logs );
	}


	public function prepare_attachment_log_before_insert( $post_ID ) {
		$attachment = get_post( $post_ID );
		$post       = $attachment->post_parent;

		if ( $post ) {
			// was added attachment on a tainacan object

			$tainacan_post = Repository::get_entity_by_post( $post );

			if ( $tainacan_post ) {
				// was added a normal attachment

				// get all attachments except the new
				$old_attachments = $tainacan_post->get_attachments( $post_ID );

				// get all attachments
				$new_attachments = $tainacan_post->get_attachments();

				$array_diff_with_index = array_map( 'unserialize',
					array_diff_assoc( array_map( 'serialize', $new_attachments ), array_map( 'serialize', $old_attachments ) ) );

				$diff['attachments'] = [
					'new'             => $new_attachments,
					'old'             => $old_attachments,
					'diff_with_index' => $array_diff_with_index
				];

				$this->insert_log( $tainacan_post, $diff, true );

			}
		}

	}

	/**
	 * Insert a log when a new entity is inserted
	 *
	 * @param Entity $value
	 * @param array $diffs
	 * @param bool $is_update
	 *
	 * @param bool $is_delete
	 * @param bool $is_trash
	 *
	 * @return Entities\Log|bool new created log
	 */
	public function insert_log( $value, $diffs = [], $is_update = false, $is_delete = false, $is_trash = false ) {
		$title       = null;
		$description = null;

		if ( is_object( $value ) ) {
			// do not log a log
			if ( ( method_exists( $value, 'get_post_type' ) && $value->get_post_type() === 'tainacan-log' ) || $value->get_status() === 'auto-draft' ) {
				return false;
			}

			if ( $value instanceof Entities\Metadatum ) {
				$type = $value->get_metadata_type();

				if ( $type === 'Tainacan\Metadata_Types\Core_Title' || $type === 'Tainacan\Metadata_Types\Core_Description' ) {
					return false;
				}
			}

			$type       = get_class( $value );
			$class_name = explode( '\\', $type )[2];

			$name = method_exists( $value, 'get_name' ) ? $value->get_name() :
				( method_exists( $value, 'get_title' ) ? $value->get_title() : $value->get_metadatum()->get_name() );

			if ( ! $name ) {
				$name = $value->get_status();
			}

			if ( $is_update ) {
				// entity was delete
				$title         = $this->prepare_event_title( $value, $name, $class_name, 'updated' );

				$description = $title;
			} elseif ( $is_delete ) {
				// entity was deleted
				$title         = $this->prepare_event_title( $value, $name, $class_name, 'deleted' );

				$description = $title;
			} elseif ( ! empty( $diffs ) ) {
				// entity was created
				$title         = $this->prepare_event_title( $value, $name, $class_name, 'created' );

				$description = $title;
			} elseif ( $is_trash ) {
				// entity was trashed
				$title         = $this->prepare_event_title( $value, $name, $class_name, 'trashed' );

				$description = $title;
			}

			$title       = apply_filters( 'tainacan-insert-log-message-title', $title, $type, $value );
			$description = apply_filters( 'tainacan-insert-log-description', $description, $type, $value );
		}

		if ( !empty( $diffs ) || $is_delete || $is_trash) {
			return Entities\Log::create( $title, $description, $value, $diffs );
		}
	}

	/**
	 * This will prepare the event title for objects
	 *
	 * @param $object
	 * @param $name
	 * @param $class_name
	 *
	 * @param $action_message
	 *
	 * @return string
	 */
	private function prepare_event_title( $object, $name, $class_name, $action_message ) {

		// translators: 1=Object name, 2=Object type, 3=Action. e.g. The "Subject" taxonomy has been created
		$title_format = __( '"%1$s" %2$s has been %3$s', 'tainacan' );

		if ( $object instanceof Entities\Metadatum || $object instanceof Entities\Item || $object instanceof Entities\Filter ) {
			$collection = $object->get_collection();

			if ( $collection ) {
				$parent = sprintf( __('(collection: %s)', 'tainacan'), $collection->get_name() );
			} else {
				$parent = __('(on repository level)', 'tainacan');
			}

			$title = sprintf( $title_format, $name, strtolower( $class_name ), $action_message );
			$title .= ' ' . $parent . '.';
		} elseif($object instanceof Entities\Item_Metadata_Entity) {
			$title = sprintf(
				$title_format,
				$name,
				__('item metadatum', 'tainacan'),
				$action_message
			               ) . ' ' . sprintf( __('(item: %s)', 'tainacan'), $object->get_item()->get_title() ) . '.';
		} else {
			$title = sprintf( $title_format, $name, strtolower( $class_name ), $action_message ) . '.';
		}

		return $title;
	}

	/**
	 *
	 * @param Entities\Log $log
	 *
	 * @return Entities\Entity|boolean return insert/update valeu or false
	 * @throws \Exception
	 */
	public function approve( $log ) {
		$log = self::get_entity_by_post( $log );
		if ( $log->get_status() == 'pending' ) {
			/** @var Entity $value * */
			$value = $log->get_value();

			$value->set_status( 'publish' ); // TODO check if publish the entity on approve

			$repository = self::get_repository( $value );

			if ( $value->validate() ) {
				return $repository->insert( $value );
			}
		}

		return false;
	}
}