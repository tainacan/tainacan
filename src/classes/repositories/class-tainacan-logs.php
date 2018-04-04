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

	public static function getInstance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	protected function __construct() {
		parent::__construct();
		add_action( 'tainacan-insert', array( $this, 'log_inserts' ) );
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
			'itens_per_page' => [
				'map'         => 'meta',
				'title'       => __( 'Itens per page', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'The quantity of items that should be load' ),
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
			'old_value'      => [
				'map'         => 'meta',
				'title'       => __( 'Old value', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The old log value' ),
				'validation'  => ''
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
			'posts_per_page' => -1,
		];

		$logs = $this->fetch( $args, 'OBJECT' );

		return array_pop( $logs );
	}

	/**
	 * Insert a log when a new entity is inserted
	 *
	 * @param Entity $new_value
	 * @param Entity $value
	 *
	 * @return Entities\Log new created log
	 */
	public function log_inserts( $new_value, $value = null ) {
		$msn = "";
		$description = "";

		if ( is_object( $new_value ) ) {
			// do not log a log
			if ( method_exists( $new_value, 'get_post_type' ) && $new_value->get_post_type() == 'tainacan-log' ) {
				return;
			}

			$type = get_class( $new_value );
			$class_name = explode('\\', $type)[2];

			$name = method_exists($new_value, 'get_name') ? $new_value->get_name() :
				(method_exists($new_value, 'get_title') ? $new_value->get_title() : $new_value->get_field()->get_name());

			$msn  = sprintf( esc_html__( 'A %s has been created/updated.', 'tainacan' ), $class_name);
			$description = sprintf( esc_html__("The '%s' %s has been created/updated.", 'tainacan' ), $name, strtolower($class_name));

		}

		$msn = apply_filters( 'tainacan-insert-log-message-title', $msn, $type, $new_value );
		$description = apply_filters('tainacan-insert-log-description', $description, $type, $new_value);

		return Entities\Log::create( $msn, $description, $new_value, $value );
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
			//$value->set_status('publish'); // TODO check if publish the entity on approve
			$repository = self::get_repository( $value );

			return $repository->insert( $value );
		}

		return false;
	}
}