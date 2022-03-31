<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;

/**
 * Class Metadata
 */
class Metadata_Section extends Repository {

	public $entities_type = '\Tainacan\Entities\Metadatum_Section';
	private static $instance = null;

	protected function __construct() {
		parent::__construct();
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Repository::get_map()
	 */
	protected function _get_map() {
		return apply_filters( 'tainacan-get-map-' . $this->get_name(), [
			'name'                  => [
				'map'         => 'post_title',
				'title'       => __( 'Name', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Name of the metadata section', 'tainacan' ),
				'on_error'    => __( 'The name should be a text value and not empty', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'slug'                  => [
				'map'         => 'post_name',
				'title'       => __( 'Slug', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'A unique and sanitized string representation of the metadata sction', 'tainacan' ),
			],
			'status'                     => [
				'map'         => 'post_status',
				'title'       => __( 'Status', 'tainacan' ),
				'type'        => 'string',
				'default'     => 'publish',
				'description' => __( 'Status', 'tainacan' )
			],
			'description'           => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The metadatum section description.', 'tainacan' ),
				'default'     => '',
			],
			'collection_id'         => [
				'map'         => 'meta',
				'title'       => __( 'Collection', 'tainacan' ),
				'type'        => ['integer', 'string'],
				'description' => __( 'The collection ID', 'tainacan' ),
			],
			'metadatum_list'         => [
				'map'         => 'meta',
				'title'       => __( 'Metadatum list', 'tainacan' ),
				'type'        => 'array',
				'items' => [
					'type' => 'integer'
				],
				'description' => __( 'The metadatum ID list', 'tainacan' ),
			]
		] );
	}

	/**
	 * Get the labels for the custom post type of this repository
	 *
	 * @return array Labels in the format expected by register_post_type()
	 */
	public function get_cpt_labels() {
		return array(
			'name'               => __( 'Metadata Section', 'tainacan' ),
			'singular_name'      => __( 'Metadata Section', 'tainacan' ),
			'add_new'            => __( 'Add new', 'tainacan' ),
			'add_new_item'       => __( 'Add new Metadata Section', 'tainacan' ),
			'edit_item'          => __( 'Edit Metadata Section', 'tainacan' ),
			'new_item'           => __( 'New Metadata Section', 'tainacan' ),
			'view_item'          => __( 'View Metadata Section', 'tainacan' ),
			'search_items'       => __( 'Search Metadata Section', 'tainacan' ),
			'not_found'          => __( 'No Metadata Section found ', 'tainacan' ),
			'not_found_in_trash' => __( 'No Metadata Section found in trash', 'tainacan' ),
			'parent_item_colon'  => __( 'Parent Metadata Section:', 'tainacan' ),
			'menu_name'          => __( 'Metadata Section', 'tainacan' )
		);
	}

	public function register_post_type() {
		$labels = $this->get_cpt_labels();
		$args   = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => tnc_enable_dev_wp_interface(),
			'show_in_menu'        => tnc_enable_dev_wp_interface(),
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'map_meta_cap'        => true,
			'show_in_nav_menus'   => false,
			'capabilities'        => (array) $this->get_capabilities(),
			'supports'            => [
				'title',
				'editor',
				'page-attributes'
			]
		);
		register_post_type( Entities\Metadatum_Section::get_post_type(), $args );
	}

	/**
	 * fetch metadatum section based on ID or WP_Query args
	 *
	 * metadatum section are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 *
	 * If a number is passed to $args, it will return a \Tainacan\Entities\Metadatum_Section object. But if the post is not found or
	 * does not match the entity post type, it will return an empty array
	 *
	 * @param array $args WP_Query args || int $args the metadatum section id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return Entities\Metadatum_Section|\WP_Query|Array an instance of wp query OR array of entities;
	 * @throws \Exception
	 */
	public function fetch( $args, $output = null ) {

		if ( is_numeric( $args ) ) {
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				try {
					return new Entities\Metadatum_Section( $existing_post );
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
			$args['post_type'] = Entities\Metadatum_Section::get_post_type();
			$args = apply_filters( 'tainacan_fetch_args', $args, 'metadata-section' );

			$wp_query = new \WP_Query( $args );

			return $this->fetch_output( $wp_query, $output );
		}
	}

	/**
	 * fetch metadata sections IDs based on WP_Query args
	 *
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 *
	 * @param array $args WP_Query args || int $args the item id
	 *
	 * @return Array array of IDs;
	 * @throws \Exception
	 */
	public function fetch_ids( $args = [] ) {

		$args['fields'] = 'ids';

		return $this->fetch( $args )->get_posts();
	}

	/**
	 * fetch metadatum section by collection
	 *
	 * @param Entities\Collection $collection
	 * @param array $args WP_Query args
	 *
	 * @return array Entities\Metadatum_Section
	 * @throws \Exception
	 */
	public function fetch_by_collection( Entities\Collection $collection, $args = [] ) {
		$collection_id = $collection->get_id();

		//get parent collections
		$parents = get_post_ancestors( $collection_id );

		//insert the actual collection
		if ( is_numeric($collection_id) ) {
			$parents[] = $collection_id;
		}

		$results = [];

		$args = array_merge( [
			'parent' => 0
		], $args );

		$original_meta_q = isset( $args['meta_query'] ) ? $args['meta_query'] : [];

		/**
		 * Since we introduced roles & capabalities management, we cannot rely
		 * on WordPress behavior when handling default post status values.
		 * WordPress checks if the current user can read_priva_posts, but this is
		 * not enough for us. We have to handle this ourselves to mimic WordPress behavior
		 * considering how tainacan manages metadata capabilities
		 */
		if ( ! isset($args['post_status']) ) {

			foreach ( $parents as $parent_id ) {

				// Add public states.
				$statuses = get_post_stati( array( 'public' => true ) );
				$read_private_cap = 'tnc_col_' . $parent_id . '_read_private_metadata_section';
				if ( current_user_can($read_private_cap) ) {
					$statuses = array_merge( $statuses, get_post_stati( array( 'private' => true ) ) );
				}

				$args['post_status'] = $statuses;

				$meta_query = array(
					'key'     => 'collection_id',
					'value'   => $parent_id,
				);

				$args['meta_query'] = $original_meta_q;
				$args['meta_query'][] = $meta_query;

				$results = array_merge($results, $this->fetch( $args, 'OBJECT' ));
			}

		} else {
			$meta_query = array(
				'key'     => 'collection_id',
				'value'   => $parents,
				'compare' => 'IN',
			);
			$args['meta_query'] = $original_meta_q;
			$args['meta_query'][] = $meta_query;
			$results = $this->fetch( $args, 'OBJECT' );
		}

		return $results;
		// return $this->order_result(
		// 	$results,
		// 	$collection,
		// 	isset( $args['include_disabled'] ) ? $args['include_disabled'] : false
		// );
	}

	/**
	 * @param \Tainacan\Entities\Metadatum_Section $metadatum_section
	 *
	 * @return \Tainacan\Entities\Metadatum_Section
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Repository::insert()
	 */
	public function insert( $metadatum_section ) {
		$new_metadatum_section = parent::insert( $metadatum_section );
		return $new_metadatum_section;
	}

	/**
	 * @param \Tainacan\Entities\Metadatum_Section $object
	 * @param $new_values
	 *
	 * @return mixed|string|Entities\Entity
	 * @throws \Exception
	 */
	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	public function add_metadatum($metadata_section_id, $metadatum_list) {
		$metadata_section = $this->fetch($metadata_section_id);
		$list = $metadata_section->get_metadatum_list();
		$metadatum_list = array_merge($list, $metadatum_list);
		$metadata_section->set_metadatum_list($metadatum_list);
		if($metadata_section->validate()) {
			$metadata_section = $this->update($metadata_section);
			return $metadata_section;
		}
		return false;
	}

	public function delete_metadatum($metadata_section_id, $metadatum_list) {
		$metadata_section = $this->fetch($metadata_section_id);
		$list = $metadata_section->get_metadatum_list();
		$list = array_diff($list, $metadatum_list);
		$metadata_section->set_metadatum_list($list);
		if($metadata_section->validate()) {
			$metadata_section = $this->update($metadata_section);
			return $metadata_section;
		}
		return false;
	}

	public function get_metadatum_list($metadata_section_id) {
		$metadata_section = $this->fetch($metadata_section_id);
		$list = $metadata_section->get_metadatum_list();
		$args = array('post__in' => $list);
		$metadata_repository = \Tainacan\Repositories\Metadata::get_instance();
		$metadatum_list = $metadata_repository->fetch($args, 'OBJECT');
		return $metadatum_list;
	}

	/**
	 * @inheritDoc
	 */
	public function delete( Entities\Entity $entity, $permanent = true ) {
		//test if not exist a metadata using this section
		return parent::delete($entity, $permanent);
	}
}
