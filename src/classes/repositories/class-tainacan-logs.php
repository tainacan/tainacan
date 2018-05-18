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
		add_action( 'tainacan-insert', array( $this, 'insert_log' ), 10, 5 );
		add_action( 'tainacan-deleted', array( $this, 'insert_log'), 10, 5 );
		add_action( 'tainacan-trashed', array( $this, 'insert_log'), 10, 5 );

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
			//'supports'          => array('title'),
			//'taxonomies'        => array(self::TAXONOMY),
			'public'              => false,
			'show_ui'             => tnc_enable_dev_wp_interface(),
			'show_in_menu'        => tnc_enable_dev_wp_interface(),
			//'menu_position'     => 5,
			//'show_in_nav_menus' => false,
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
	 * @param array $args WP_Query args || int $args the log id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return \WP_Query|Array an instance of wp query OR array of entities;
	 */
	public function fetch( $args = [], $output = null ) {
		if ( is_numeric( $args ) ) {
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				return new Entities\Log( $existing_post );
			} else {
				return [];
			}

		} elseif ( is_array( $args ) ) {
			$args = array_merge( [
				'posts_per_page' => -1,
			], $args );

			$args = $this->parse_fetch_args( $args );

			$args['post_type'] = Entities\Log::get_post_type();

			$wp_query = new \WP_Query( $args );

			return $this->fetch_output( $wp_query, $output );
		}
	}

	public function delete( $object ) {

	}

	public function update( $object, $new_values = null ) {

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

			if($tainacan_post) {
				// was added a normal attachment

				// get all attachments except the new
				$old_attachments = $tainacan_post->get_attachments( $post_ID );

				foreach ( $old_attachments as $index => $a ) {
					unset( $old_attachments[$index]['id'] );
				}

				$new_attachments[] = [
					'title'       => $attachment->post_title,
					'description' => $attachment->post_content,
					'mime_type'   => $attachment->post_mime_type,
					'url'         => $attachment->guid,
				];

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
	 * @param Entity $new_value
	 * @param array $diffs
	 * @param bool $is_update
	 *
	 * @param bool $is_delete
	 * @param bool $is_trash
	 *
	 * @return Entities\Log new created log
	 */
	public function insert_log( $new_value, $diffs = [], $is_update = false, $is_delete = false, $is_trash = false ) {
		$msn         = null;
		$description = null;

		if ( is_object( $new_value ) ) {
			// do not log a log
			if ( ( method_exists( $new_value, 'get_post_type' ) && $new_value->get_post_type() === 'tainacan-log' ) || $new_value->get_status() === 'auto-draft' ) {
				return false;
			}

			if ( $new_value instanceof Entities\Field ) {
				$type = $new_value->get_field_type();

				if ( $type === 'Tainacan\Field_Types\Core_Title' || $type === 'Tainacan\Field_Types\Core_Description' ) {
					return false;
				}
			}

			$type       = get_class( $new_value );
			$class_name = explode( '\\', $type )[2];

			$name = method_exists( $new_value, 'get_name' ) ? $new_value->get_name() :
				( method_exists( $new_value, 'get_title' ) ? $new_value->get_title() : $new_value->get_field()->get_name() );

			if ( ! $name ) {
				$name = $new_value->get_status();
			}

			if ( $is_update ) {
				$msn = $this->prepare_event_message($class_name, 'updated');
				$description = $this->prepare_event_description_message($new_value, $name, $class_name, 'updated');
			} elseif( $is_delete ){
				// was deleted
				$msn = $this->prepare_event_message($class_name, 'deleted');
				$description = $this->prepare_event_description_message($new_value, $name, $class_name, 'deleted');
			} elseif( !empty($diffs) ) {
				// was created
				$msn = $this->prepare_event_message($class_name, 'created');
				$description = $this->prepare_event_description_message($new_value, $name, $class_name, 'created');

			} elseif( $is_trash ) {
				// was trashed
				$msn = $this->prepare_event_message($class_name, 'trashed');
				$description = $this->prepare_event_description_message($new_value, $name, $class_name, 'trashed');
			}

			$msn         = apply_filters( 'tainacan-insert-log-message-title', $msn, $type, $new_value );
			$description = apply_filters( 'tainacan-insert-log-description', $description, $type, $new_value );
		}


		if ( ! empty( $diffs ) || $is_delete || $is_trash ) {
			return Entities\Log::create( $msn, $description, $new_value, $diffs );
		}
	}

	private function prepare_event_message($class_name, $action_message){
		$articleA  = 'A';
		$articleAn = 'An';
		$vowels    = 'aeiou';

		if ( substr_count( $vowels, strtolower( substr( $class_name, 0, 1 ) ) ) > 0 ) {
			$msn = sprintf( __( '%s %s has been %s.', 'tainacan' ), $articleAn, $class_name, $action_message );
		} else {
			$msn = sprintf( __( '%s %s has been %s.', 'tainacan' ), $articleA, $class_name, $action_message );
		}

		return $msn;
	}

	/**
	 * This will prepare the event description for objects
	 *
	 * @param $object
	 * @param $name
	 * @param $class_name
	 *
	 * @param $action_message
	 *
	 * @return string
	 */
	private function prepare_event_description_message($object, $name, $class_name, $action_message){
		if ( $object instanceof Entities\Field || $object instanceof Entities\Item || $object instanceof Entities\Filter) {
			$collection = $object->get_collection();
			$parent     = $collection;

			if ( $collection ) {
				$parent = $collection->get_name();

				if ( ! $parent ) {
					$parent = $collection->get_status();
				}
			}

			$description = sprintf( __( "The \"%s\" %s has been %s (parent %s).", 'tainacan' ), $name, strtolower( $class_name ), $action_message, $parent );
		} else {
			$description = sprintf( __( "The \"%s\" %s has been %s.", 'tainacan' ), $name, strtolower( $class_name ), $action_message );
		}

		return $description;
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

			$value->set_status('publish'); // TODO check if publish the entity on approve

			$repository = self::get_repository( $value );

			if($value->validate()) {
				return $repository->insert( $value );
			}
		}

		return false;
	}
}