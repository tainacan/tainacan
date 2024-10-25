<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;

/**
 * Class Metadata
 */
class Metadata extends Repository {
	public $entities_type = '\Tainacan\Entities\Metadatum';
	protected $default_metadata = 'default';
	protected $current_taxonomy;

	public $metadata_types = [];

	public $core_metadata = [
		'Tainacan\Metadata_Types\Core_Title',
		'Tainacan\Metadata_Types\Core_Description'
	];

	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register specific hooks for metadatum repository
	 */
	protected function __construct() {
		parent::__construct();
		add_filter( 'pre_trash_post', array( &$this, 'disable_delete_core_metadata' ), 10, 2 );
		add_filter( 'pre_delete_post', array( &$this, 'force_delete_core_metadata' ), 10, 3 );

		add_action('tainacan-insert-tainacan-taxonomy', [$this, 'hook_taxonomies_saved_as_private']);
		add_action('tainacan-insert-tainacan-taxonomy', [$this, 'hook_taxonomies_saved_not_allow_insert_new_terms']);
		add_action('tainacan-insert-tainacan-metadatum', [$this, 'hook_metadata_update_order']);

	}

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Repository::get_map()
	 */
	protected function _get_map() {
		$entity = $this->get_name();
		return apply_filters( "tainacan-get-map-$entity", [
			'name'                  => [
				'map'         => 'post_title',
				'title'       => __( 'Name', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Name of the metadata', 'tainacan' ),
				'on_error'    => __( 'The name should be a text value and not empty', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'slug'                  => [
				'map'         => 'post_name',
				'title'       => __( 'Slug', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'A unique and sanitized string representation of the metadata', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'order'                 => [
				'map'         => 'menu_order',
				'title'       => __( 'Order', 'tainacan' ),
				'type'        => ['string', 'integer'],
				'description' => __( 'Metadata order. This metadata will be used if collections were manually ordered.', 'tainacan' ),
				'on_error'    => __( 'The menu order should be a numeric value', 'tainacan' ),
				//'validation' => v::numeric(),
			],
			'parent'                => [
				'map'         => 'post_parent',
				'title'       => __( 'Parent', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'Parent metadata', 'tainacan' ),
				'default'     => 0
				//'on_error'   => __('The Parent should be numeric value', 'tainacan'),
				//'validation' => v::numeric(),
			],
			'description'           => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The metadatum description. This may provide information on how to fill this metadatum, which will appear inside a tooltip alongside the input label, or below it.', 'tainacan' ),
				'default'     => '',
				//'on_error'   => __('The description should be a text value', 'tainacan'),
				//'validation' => v::stringType()->notEmpty(),
			],
			'description_bellow_name' => [
				'map'         => 'meta',
				'title'       => __( 'Description below name', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Whether the metadatum description should be displayed below the input label instead of inside a tooltip.', 'tainacan' ),
				'on_error'    => __( 'Please set the "Description below name" value as "yes" or "no"', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'enum'		  => [ 'yes', 'no' ],
				'default'     => 'no'
			],
			'placeholder'     => [
				'map'         => 'meta',
				'title'       => __( 'Placeholder', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The metadatum placeholder. This is a simple message that will appear inside textual input and may indicate to the user what kind of information is expected.', 'tainacan' ),
				'default'     => '',
			],
			'metadata_type'         => [
				'map'         => 'meta',
				'title'       => __( 'Type', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The metadata type class name, such as Tainacan\Metadata_Types\Core_Title', 'tainacan' ),
				'on_error'    => __( 'Metadata type is empty', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'required'              => [
				'map'         => 'meta',
				'title'       => __( 'Required', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The metadata is required. All items in this collection must fill this field', 'tainacan' ),
				'on_error'    => __( 'The metadata content is invalid', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'enum'		  => [ 'yes', 'no' ],
				'default'     => 'no'
			],
			'collection_key'        => [
				'map'         => 'meta',
				'title'       => __( 'Unique value', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Metadata value should be unique accross all items in this collection', 'tainacan' ),
				'on_error'    => __( 'You cannot have two items with the same value for this metadatum', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'enum'		  => [ 'yes', 'no' ],
				'default'     => 'no'
			],
			'multiple'              => [
				'map'         => 'meta',
				'title'       => __( 'Multiple', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Allow items to have more than one value for this metadatum', 'tainacan' ),
				'on_error'    => __( 'Invalid multiple metadata', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ),
				// yes or no. It cant be multiple if its collection_key
				'enum'		  => [ 'yes', 'no' ],
				'default'     => 'no'
			],
			'cardinality'           => [
				'map'         => 'meta',
				'title'       => __( 'Maximum number of values', 'tainacan' ),
				'type'        => ['string', 'number'],
				'description' => __( 'Limit the amount of possible metadata values', 'tainacan' ),
				'on_error'    => __( 'This number of multiple metadata is not allowed', 'tainacan' ),
				//'validation'  => v::numeric()->positive(),
			],
			'default_value'         => [
				'map'         => 'meta',
				'title'       => __( 'Default value', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The default value for the metadata', 'tainacan' ),
			],
			'metadata_type_options' => [ // not showed in form
				'map'         => 'meta',
				'title'       => __( 'Metadata type options', 'tainacan' ),
				'type'        => ['array', 'object'],
				'items'       => [ 'type' => ['array', 'string', 'integer', 'object'] ],
				'description' => __( 'Specific options for metadata type', 'tainacan' ),
				// 'validation' => ''
			],
			'collection_id'         => [ // not showed in form
				'map'         => 'meta',
				'title'       => __( 'Collection', 'tainacan' ),
				'type'        => ['integer', 'string'],
				'description' => __( 'The collection ID', 'tainacan' ),
				//'validation' => ''
			],
			'accept_suggestion'     => [
				'map'         => 'meta',
				'title'       => __( 'Metadata Value Accepts Suggestions', 'tainacan' ),
				'type'        => 'boolean',
				'description' => __( 'Allow community to suggest different values for the metadata', 'tainacan' ),
				'default'     => false,
				'validation'  => v::boolType()
			],
			'exposer_mapping'       => [
				'map'         => 'meta',
				'title'       => __( 'Relationship metadata mapping', 'tainacan' ),
				'type'        => ['array', 'object', 'string'],
				'items'       => [ 'type' => ['array', 'string', 'integer', 'object'] ],
				'description' => __( 'The metadata mapping options. Metadata can be configured to match another type of data distribution.', 'tainacan' ),
				'on_error'    => __( 'Invalid Metadata Mapping', 'tainacan' ),
				//'validation' =>  v::arrayType(),
				'default'     => []
			],
			'display'               => [
				'map'         => 'meta',
				'title'       => __( 'Display', 'tainacan' ),
				'type'        => 'string',
				'validation'  => v::stringType()->in( [ 'yes', 'no', 'never' ] ),
				'enum'		  => [ 'yes', 'no', 'never' ],
				'description' => __( 'Display by default on listing or do not display or never display.', 'tainacan' ),
				'default'     => 'no'
			],
			'semantic_uri'          => [
				'map'         => 'meta',
				'title'       => __( 'The semantic metadatum description URI' ),
				'type'        => 'string',
				'validation'  => v::optional( v::url() ),
				'description' => __( 'The semantic metadatum description URI like: ', 'tainacan' ) . 'https://schema.org/URL',
				'default'     => ''
			],
			'repository_level' => [
				'map'         => 'meta',
				'title'       => __( 'Repository metadata', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Makes this metadatum a repository level metadatum instead of collection metadatum', 'tainacan' ),
				'on_error'    => __( 'Invalid value for repository metadata', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ),
				'enum'		  => [ 'yes', 'no' ],
				// yes or no. It cant be multiple if its collection_key
				'default'     => 'no'
			],
			'metadata_section_id' => [
				'map'         => 'meta_multi',
				'title'       => __( 'Metadata section', 'tainacan' ),
				'type'        => ['integer', 'string', 'array'],
				'description' => __( 'The metadata section ID', 'tainacan' ),
				'default'     => \Tainacan\Entities\Metadata_Section::$default_section_slug
			],
		] );
	}

	/**
	 * Get the labels for the custom post type of this repository
	 *
	 * @return array Labels in the format expected by register_post_type()
	 */
	public function get_cpt_labels() {
		return array(
			'name'               => __( 'Metadata', 'tainacan' ),
			'singular_name'      => __( 'Metadata', 'tainacan' ),
			'add_new'            => __( 'Add new', 'tainacan' ),
			'add_new_item'       => __( 'Add new Metadata', 'tainacan' ),
			'edit_item'          => __( 'Edit Metadata', 'tainacan' ),
			'new_item'           => __( 'New Metadata', 'tainacan' ),
			'view_item'          => __( 'View Metadata', 'tainacan' ),
			'search_items'       => __( 'Search Metadata', 'tainacan' ),
			'not_found'          => __( 'No Metadata found ', 'tainacan' ),
			'not_found_in_trash' => __( 'No Metadata found in trash', 'tainacan' ),
			'parent_item_colon'  => __( 'Parent Metadata:', 'tainacan' ),
			'menu_name'          => __( 'Metadata', 'tainacan' )
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
			'publicly_queryable'  => false,
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
		register_post_type( Entities\Metadatum::get_post_type(), $args );
	}

	/**
	 * constant used in default metadatum in attribute collection_id
	 *
	 * @return string the value of constant
	 */
	public function get_default_metadata_attribute() {
		return $this->default_metadata;
	}

	/**
	 * register metadatum types class on array of types
	 *
	 * @param $class_name string | object The class name or the instance
	 */
	public function register_metadata_type( $class_name ) {

		// TODO: we shoud not allow registration of metadatum types of retricted core metadatum types (e.g. compound, term) by plugins

		if ( is_object( $class_name ) ) {
			$class_name = get_class( $class_name );
		}

		if ( ! in_array( $class_name, $this->metadata_types ) ) {
			$this->metadata_types[] = $class_name;
		}
	}

	/**
	 * register metadatum types class on array of types
	 *
	 * @param $class_name string | object The class name or the instance
	 */
	public function unregister_metadata_type( $class_name ) {
		if ( is_object( $class_name ) ) {
			$class_name = get_class( $class_name );
		}

		$key = array_search( $class_name, $this->metadata_types );
		if ( $key !== false ) {
			unset( $this->metadata_types[ $key ] );
		}
	}

	/**
	 * fetch metadatum based on ID or WP_Query args
	 *
	 * metadatum are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 *
	 * If a number is passed to $args, it will return a \Tainacan\Entities\Metadatum object.  But if the post is not found or
	 * does not match the entity post type, it will return an empty array
	 *
	 * @param array $args WP_Query args || int $args the metadatum id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return Entities\Metadatum|\WP_Query|Array an instance of wp query OR array of entities;
	 * @throws \Exception
	 */
	public function fetch( $args = [], $output = null ) {

		if ( is_numeric( $args ) ) {
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				try {
					return new Entities\Metadatum( $existing_post );
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

			if ( ! (isset($args['include_control_metadata_types']) && $args['include_control_metadata_types'] == true) ) {
				if( !isset($args['meta_query']) )
					$args['meta_query'] = [];
				$args['meta_query'][] = [
					'key'     => 'metadata_type',
					'value'   => 'Tainacan\Metadata_Types\Control',
					'compare' => 'NOT IN'
				];
			}

			$args = $this->parse_fetch_args( $args );

			$args['post_type'] = Entities\Metadatum::get_post_type();

			$args = apply_filters( 'tainacan-fetch-args', $args, 'metadata' );


			$wp_query = new \WP_Query( $args );

			return $this->fetch_output( $wp_query, $output );
		}
	}

	/**
	 * fetch metadata IDs based on WP_Query args
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
	 * fetch metadatum by collection, considering inheritance and order
	 *
	 * @param Entities\Collection $collection
	 * @param array $args WP_Query args plus disabled_metadata
	 *
	 * @return array Entities\Metadatum
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

		//search for default metadatum
		$parents[] = $this->get_default_metadata_attribute();

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

				$read_private_cap = $this->get_default_metadata_attribute() == $parent_id ? 'tnc_rep_read_private_metadata' : 'tnc_col_' . $parent_id . '_read_private_metadata';
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

				if ($this->get_default_metadata_attribute() != $parent_id) {
					$read_private_metasection_cap = "tnc_col_{$parent_id}_read_private_metasection";
					if ( !current_user_can($read_private_metasection_cap) ) {
						$private_metadata_sections_ids = \tainacan_metadata_sections()->fetch_ids(
						array(
							'post_status' => get_post_stati( array( 'private' => true ) ),
							'meta_query' => array(
								'key'     => 'collection_id',
								'value'   => $parent_id,
							)
						));

						$args['meta_query'][] = array(
							'relation' => 'OR',
							array(
								'key'     => 'metadata_section_id',
								'value'   => $private_metadata_sections_ids,
								'compare' => 'NOT IN'
							),
							array( //note: using the comparete 'NOT EXISTS' to cases where metadata section id is not present in mapped property (meta) of metadatum  
								'key'     => 'metadata_section_id',
								'compare' => 'NOT EXISTS'
							)
						);
					}
				}

				$results = array_merge($results, $this->fetch( $args, 'OBJECT' ));
			}

		} else {
			$meta_query = array(
				'key'     => 'collection_id',
				'value'   => $parents,
				'compare' => 'IN',
			);

			if( !isset( $args['parent']) ) {
				$args = array_merge( [
					'parent' => 0
				], $args );
			}

			if( $args['parent'] === 'all ')
				unset( $args['parent'] );

			$args['meta_query'] = $original_meta_q;
			$args['meta_query'][] = $meta_query;

			$results = $this->fetch( $args, 'OBJECT' );

		}

		if (!isset($args['add_only_repository']) || $args['add_only_repository'] !== true) {
			$results = array_filter($results, function($meta) {
				return ( !isset($meta->get_metadata_type_options()['only_repository']) || $meta->get_metadata_type_options()['only_repository'] == 'no' );
			});
		}

		return $this->order_result(
			$results,
			$collection,
			isset( $args['include_disabled'] ) ? $args['include_disabled'] : false
		);
	}

	/**
	 * fetch metadata IDs by collection, considering inheritance
	 *
	 * @param Entities\Collection|int $collection object or ID
	 * @param array $args WP_Query args plus disabled_metadata
	 *
	 * @return array List of metadata IDs
	 * @throws \Exception
	 */
	public function fetch_ids_by_collection( $collection, $args = [] ) {

		if ( $collection instanceof Entities\Collection ) {
			$collection_id = $collection->get_id();
		} elseif ( is_integer( $collection ) ) {
			$collection_id = $collection;
		} else {
			throw new \InvalidArgumentException( 'fetch_ids_by_collection expects paramater 1 to be a integer or a \Tainacan\Entities\Collection object. ' . gettype( $collection ) . ' given' );
		}

		//get parent collections
		$parents = get_post_ancestors( $collection_id );

		//insert the actual collection
		if ( is_numeric($collection_id) ) {
			$parents[] = $collection_id;
		}

		//search for default metadatum
		$parents[] = $this->get_default_metadata_attribute();

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

				$read_private_cap = $this->get_default_metadata_attribute() == $parent_id ? 'tnc_rep_read_private_metadata' : 'tnc_col_' . $parent_id . '_read_private_metadata';
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

				$results = array_merge($results, $this->fetch_ids( $args ));

			}

		} else {
			$meta_query = array(
				'key'     => 'collection_id',
				'value'   => $parents,
				'compare' => 'IN',
			);

			$args = array_merge( [
				'parent' => 0
			], $args );

			$args['meta_query'] = $original_meta_q;
			$args['meta_query'][] = $meta_query;

			$results = $this->fetch_ids( $args );
		}

		return $results;
	}

	/**
	 * fetch metadatum by metadata section, considering order
	 *
	 * @param Entities\Metadata_Section $collection
	 * @param array $args WP_Query args plus disabled_metadata
	 *
	 * @return array Entities\Metadatum
	 * @throws \Exception
	 */
	public function fetch_by_metadata_section( Entities\Metadata_Section $metadata_section, $args = [] ) {
		$results = [];
		if ($metadata_section && $metadata_section->can_read()) {
			$metadata_section_id = $metadata_section->get_id();
			$collection = $metadata_section->get_collection();
			$args = array_merge($args, array(
				'parent' => 0,
				'meta_query' => [
					[
						'key'     => 'metadata_section_id',
						'value'   => $metadata_section_id,
						'compare' => '='
					]
				]
			));
			$results = $this->fetch($args, 'OBJECT');
			return $this->order_result(
				$results,
				$collection,
				isset( $args['include_disabled'] ) ? $args['include_disabled'] : false
			);
		}
		return $results;
	}

	/**
	 * Ordinate the result from fetch response if $collection has an ordination,
	 * metadata not ordinated appear on the end of the list
	 *
	 *
	 * @param array $result Response from method fetch_by_collection
	 * @param Entities\Collection $collection
	 * @param bool $include_disabled Whether to include disabled metadata in the results or not
	 *
	 * @return array
	 */
	public function order_result( $result, Entities\Collection $collection, $include_disabled = false ) {
		$order = $collection->get_metadata_order();
		$section_order = $collection->get_metadata_section_order();

		if ( $order ) {
			$order = ( is_array( $order ) ) ? $order : unserialize( $order );

			if ( is_array( $result ) ) {
				$result_ordinate = [];
				$not_ordinate    = [];

				foreach ( $result as $item ) {
					$id    = $item->WP_Post->ID;
					$index = array_search( $id, array_column( $order, 'id' ) );
					$metadata_section_ids = get_post_meta( $id, 'metadata_section_id');

					$enabled_metadata_section = true;
					if(!empty($metadata_section_ids) && $metadata_section_ids !== false && !empty($section_order)) {
						foreach( $metadata_section_ids as $metadata_section_id) {
							$section_order_index = array_search( $metadata_section_id, array_column( $section_order, 'id' ) );
							if ( $section_order_index !== false ) {
								$enabled_metadata_section = boolval($section_order[$section_order_index]['enabled']);
								break;
							}
						}
					}

					if ( $index !== false ) {

						// skipping metadata disabled if the arg is set
						if ( ! $include_disabled && (!$enabled_metadata_section || isset( $order[ $index ]['enabled'] ) && ! $order[ $index ]['enabled'] )) {
							continue;
						}

						$enable = ( isset( $order[ $index ]['enabled'] ) ) ? $order[ $index ]['enabled'] : true;
						$item->set_enabled_for_collection( $enable );

						$result_ordinate[ $index ] = $item;
					} else {
						// skipping if metadata coumpound is disabled if the arg is set
						if ($item->get_parent() > 0) {
							$parent_metadatum = new \Tainacan\Entities\Metadatum($item->get_parent());
							$parent_index = array_search( $parent_metadatum->get_id(), array_column( $order, 'id' ) );
							if ( ! $include_disabled && (!$enabled_metadata_section || isset( $order[ $parent_index ]['enabled'] ) && ! $order[ $parent_index ]['enabled'] )) {
								continue;
							}
						}
						$not_ordinate[] = $item;
					}
				}

				ksort( $result_ordinate );
				$result_ordinate = array_merge( $result_ordinate, $not_ordinate );

				return $result_ordinate;
			}

		}

		return $result;
	}

	/**
	 * @param \Tainacan\Entities\Metadatum $metadatum
	 *
	 * @return \Tainacan\Entities\Metadatum
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Repository::insert()
	 */
	public function insert( $metadatum ) {
		$this->pre_update_taxonomy_metadatum( $metadatum );
		$new_metadatum = parent::insert( $metadatum );
		$this->update_metadata_type_index( $new_metadatum );
		$this->update_taxonomy_metadatum( $new_metadatum );
		return $new_metadatum;
	}

	public function pre_update_metadata_repository_level($metadatum, $attributes) {
		if (isset($attributes['target_collection_id']) && is_numeric($attributes['target_collection_id']) && $metadatum->is_repository_level()) {
			$collection =  \tainacan_collections()->fetch($attributes['target_collection_id'], 'OBJECT');

			$new_metadata_section_id = $metadatum->get_metadata_section_id();
			$new_metadata_section_id = is_array($new_metadata_section_id) ? $new_metadata_section_id[0] : $new_metadata_section_id;

			if($collection instanceof \Tainacan\Entities\Collection) {
				$collection_metadata_sections_id = array_filter(
					array_map(function($el) {return $el->get_id();} , \tainacan_metadata_sections()->fetch_by_collection($collection)),
					function($el) {return $el != \Tainacan\Entities\Metadata_Section::$default_section_slug;}
				);
				$old_value = get_post_meta($metadatum->get_id(), 'metadata_section_id');
				$new_value = array_diff($old_value, $collection_metadata_sections_id);
				$new_value[] = (string)$new_metadata_section_id;
				$metadatum->set_metadata_section_id($new_value);
				if(!$metadatum->validate()) { 
					throw new \Exception( $metadatum->get_errors() );
				}
			}
		}
		return $metadatum;
	}

	/**
	 * @param $object
	 * @param $new_values
	 *
	 * @return mixed|string|Entities\Entity
	 * @throws \Exception
	 */
	public function update( $object, $new_values = null ) {
		$object = $this->pre_update_metadata_repository_level($object, $new_values);
		return $this->insert( $object );
	}

	/**
	 * fetch all registered metadatum type classes
	 *
	 * Possible outputs are:
	 * CLASS (default) - returns the Class name of of metadatum types registered
	 * NAME - return an Array of the names of metadatum types registered
	 *
	 * @param $output string CLASS | NAME
	 *
	 * @return array of Entities\Metadata_Types\Metadata_Type classes path name
	 */
	public function fetch_metadata_types( $output = 'CLASS' ) {
		$return = [];

		do_action( 'register_metadata_types' );

		return array_map(
			function($metadata_type) use ($output) {
				return $output === 'NAME' ? str_replace( 'Tainacan\\Metadata_Types\\', '', $metadata_type ) : $metadata_type;
			},
			array_filter($this->metadata_types, function($metadata_type) { return $metadata_type != 'Tainacan\Metadata_Types\Control';})
		);
	}

	/**
	 * That function update the core metadatum meta key, in case of changing the collection parent
	 *
	 * @param Entities\Collection $collection
	 * @param $parent_collection_id
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function maybe_update_core_metadata_meta_keys( Entities\Collection $collection_new, Entities\Collection $collection_old, Entities\Metadatum $old_title_metadatum, Entities\Metadatum $old_description_metadatum ) {

		global $wpdb;

		$item_post_type       = $collection_new->get_db_identifier();
		$parent_collection_id = $collection_new->get_parent();

		if ( $parent_collection_id != 0 && $collection_old->get_parent() == 0 ) {
			update_post_meta( $old_description_metadatum->get_id(), 'metadata_type', 'to_delete', $old_description_metadatum->get_metadata_type() );
			wp_delete_post( $old_description_metadatum->get_id(), true );
			update_post_meta( $old_title_metadatum->get_id(), 'metadata_type', 'to_delete', $old_title_metadatum->get_metadata_type() );
			wp_delete_post( $old_title_metadatum->get_id(), true );
		}

		$new_title_metadatum       = $collection_new->get_core_title_metadatum();
		$new_description_metadatum = $collection_new->get_core_description_metadatum();

		$sql_statement = $wpdb->prepare(
			"UPDATE $wpdb->postmeta
				SET meta_key = %s
				WHERE meta_key = %s AND post_id IN (
				SELECT ID
				FROM $wpdb->posts
				WHERE post_type = %s
			)", $new_title_metadatum->get_id(), $old_title_metadatum->get_id(), $item_post_type
		);

		$res = $wpdb->query( $sql_statement );

		$sql_statement = $wpdb->prepare(
			"UPDATE $wpdb->postmeta
				SET meta_key = %s
				WHERE meta_key = %s AND post_id IN (
				SELECT ID
				FROM $wpdb->posts
				WHERE post_type = %s
			)", $new_description_metadatum->get_id(), $old_description_metadatum->get_id(), $item_post_type
		);

		$res = $wpdb->query( $sql_statement );

		wp_cache_flush();

	}

	/**
	 * @param Entities\Collection $collection
	 *
	 * @return array
	 */
	private function get_data_core_metadata( Entities\Collection $collection ) {

		$data_core_metadata = [
			'core_title'       => [
				'name'            => __('Title', 'tainacan'),
				'collection_id'   => $collection->get_id(),
				'metadata_type'   => 'Tainacan\Metadata_Types\Core_Title',
				'status'          => 'publish',
				'display'		  => 'yes',
			],
			'core_description' => [
				'name'            => __('Description', 'tainacan'),
				'collection_id'   => $collection->get_id(),
				'metadata_type'   => 'Tainacan\Metadata_Types\Core_Description',
				'status'          => 'publish',
			]
		];
		return $data_core_metadata;

	}

	/**
	 * @param Entities\Collection $collection
	 * @param bool $force if true will register core metadata even if collection is auto draft
	 *
	 * @return bool
	 * @throws \ErrorException
	 * @throws \Exception
	 */
	public function register_core_metadata( Entities\Collection $collection, $force = false ) {

		if ( $force !== true && $collection->get_status() == 'auto-draft' ) {
			return;
		}

		$metadata = $collection->get_core_metadata();

		$data_core_metadata = $this->get_data_core_metadata( $collection );

		foreach ( $data_core_metadata as $index => $data_core_metadatum ) {
			if ( empty( $metadata ) ) {
				$this->insert_array_metadatum( $data_core_metadatum );
			} else {
				$exists = false;
				foreach ( $metadata as $metadatum ) {
					if ( $metadatum->get_metadata_type() === $data_core_metadatum['metadata_type'] ) {
						$exists = true;
					}
				}

				if ( ! $exists ) {
					$this->insert_array_metadatum( $data_core_metadatum );
				}
			}
		}
	}

	/**
	 * @param Entities\Collection $collection
	 * @param bool $force if true will register control metadata even if collection is auto draft
	 *
	 * @return bool
	 * @throws \ErrorException
	 * @throws \Exception
	 */
	public function register_control_metadata( Entities\Collection $collection, $force = false ) {

		if ( $force !== true && $collection->get_status() == 'auto-draft' ) {
			return;
		}

		$metadata = $this->fetch( [
			'meta_query' => [
				[
					'key'     => 'metadata_type',
					'value'   => 'Tainacan\Metadata_Types\Control',
					'compare' => '='
				],[
					'key'     => 'collection_id',
					'value'   => 'default',
					'compare' => '='
				]
			],
			'include_disabled' => true,
			'include_control_metadata_types' => true
		], 'OBJECT' );

		$data_control_metadata = [
			'document_type' => [
				'name'            => __('Document type', 'tainacan'),
				'description'     => __('The item main document type', 'tainacan'),
				'collection_id'   => 'default',
				'metadata_type'   => 'Tainacan\Metadata_Types\Control',
				'status'          => 'publish',
				'display'		  => 'never',
				'metadata_type_options' => [ 'control_metadatum' => 'document_type' ]
			],
			'collection_id'       => [
				'name'            => __('Collection', 'tainacan'),
				'description'     => __('The item collection ID', 'tainacan'),
				'collection_id'   => 'default',
				'metadata_type'   => 'Tainacan\Metadata_Types\Control',
				'status'          => 'publish',
				'display'		  => 'never',
				'metadata_type_options' => [ 
					'control_metadatum' => 'collection_id',
					'only_repository' => 'yes' 
				]
			],
			'has_thumbnail' => [
				'name'            => __('Has thumbnail', 'tainacan'),
				'description'     => __('Does the item has a thumbnail set?', 'tainacan'),
				'collection_id'   => 'default',
				'metadata_type'   => 'Tainacan\Metadata_Types\Control',
				'status'          => 'publish',
				'display'		  => 'never',
				'metadata_type_options' => [ 'control_metadatum' => 'has_thumbnail' ]
			],
		];

		foreach ( $data_control_metadata as $index => $data_control_metadatum ) {
			if ( empty( $metadata ) ) {
				$this->insert_array_metadatum( $data_control_metadatum );
			} else {
				$exists = false;
				foreach ( $metadata as $metadatum ) {
					if ( 
						$metadatum->get_metadata_type() === $data_control_metadatum['metadata_type']  &&
						$metadatum->get_metadata_type_options()['control_metadatum'] == $data_control_metadatum['metadata_type_options']['control_metadatum']
					) {
						$exists = true;
					}
				}

				if ( ! $exists ) {
					$this->insert_array_metadatum( $data_control_metadatum );
				}
			}
		}
	}

	/**
	 * block user from remove core metadata
	 *
	 * @param $before  wordpress pass a null value
	 * @param $post the post which is moving to trash
	 *
	 * @return null/bool
	 * @throws \Exception
	 */
	public function disable_delete_core_metadata( $before, $post ) {

		if ( Entities\Metadatum::get_post_type() != $post->post_type ) {
			return null;
		}

		$metadatum = $this->fetch( $post->ID );

		if ( $metadatum && in_array( $metadatum->get_metadata_type(), $this->core_metadata ) && is_numeric( $metadatum->get_collection_id() ) ) {
			return false;
		}
	}

	/**
	 * block user from remove core metadata ( if use wp_delete_post)
	 *
	 * @param $before  wordpress pass a null value
	 * @param $post the post which is deleting
	 * @param $force_delete a boolean that force the deleting
	 *
	 * @return null /bool
	 * @throws \Exception
	 * @internal param The $post_id post ID which is deleting
	 */
	public function force_delete_core_metadata( $before, $post, $force_delete ) {

		if ( Entities\Metadatum::get_post_type() != $post->post_type ) {
			return null;
		}

		$metadatum = $this->fetch( $post->ID );

		if ( $metadatum && in_array( $metadatum->get_metadata_type(), $this->core_metadata ) && is_numeric( $metadatum->get_collection_id() ) ) {
			return false;
		}
	}

	/**
	 * returns all core metadata from a specific collection
	 *
	 * @param Entities\Collection $collection
	 *
	 * @return Array|\WP_Query
	 * @throws \Exception
	 */
	public function get_core_metadata( Entities\Collection $collection ) {

		return $this->fetch_by_collection( $collection, [
			'meta_query' => [
				[
					'key'     => 'metadata_type',
					'value'   => $this->core_metadata,
					'compare' => 'IN'
				]
			],
			'include_disabled' => true
		] );

	}

	/**
	 * Get the Core Title Metadatum for a collection
	 *
	 * @param Entities\Collection $collection
	 *
	 * @return \Tainacan\Entities\Metadatum The Core Title Metadatum
	 * @throws \Exception
	 */
	public function get_core_title_metadatum( Entities\Collection $collection ) {

		$results = $this->fetch_by_collection( $collection, [
			'meta_query'     => [
				[
					'key'   => 'metadata_type',
					'value' => 'Tainacan\Metadata_Types\Core_Title',
				]
			],
			'posts_per_page' => 1
		] );

		if ( is_array( $results ) && sizeof( $results ) == 1 && $results[0] instanceof \Tainacan\Entities\Metadatum ) {
			return $results[0];
		}

		return false;
	}

	/**
	 * Get the Core Description Metadatum for a collection
	 *
	 * @param Entities\Collection $collection
	 *
	 * @return \Tainacan\Entities\Metadatum The Core Description Metadatum
	 * @throws \Exception
	 */
	public function get_core_description_metadatum( Entities\Collection $collection ) {

		$results = $this->fetch_by_collection( $collection, [
			'meta_query'     => [
				[
					'key'   => 'metadata_type',
					'value' => 'Tainacan\Metadata_Types\Core_Description',
				]
			],
			'posts_per_page' => 1
		] );

		if ( is_array( $results ) && sizeof( $results ) == 1 && $results[0] instanceof \Tainacan\Entities\Metadatum ) {
			return $results[0];
		}

		return false;
	}

	/**
	 * create a metadatum entity and insert by an associative array ( attribute => value )
	 *
	 * @param Array $data the array of attributes to insert a metadatum
	 *
	 * @return int the metadatum id inserted
	 * @throws \ErrorException
	 * @throws \Exception
	 */
	public function insert_array_metadatum( $data ) {
		$metadatum = new Entities\Metadatum();
		foreach ( $data as $attribute => $value ) {
			$set_ = 'set_' . $attribute;
			$metadatum->$set_( $value );
		}
		if ( $metadatum->validate() ) {
			$metadatum = $this->insert( $metadatum );

			return $metadatum->get_id();
		} else {
			throw new \ErrorException( 'The entity wasn\'t validated.' . print_r( $metadatum->get_errors(), true ) );
		}
	}

	/**
	  * Return all possible values for a metadatum
	  *
	  * Each metadata is a label with the metadatum name and the value.
	  *
	  * If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in the 'metadata' argument, it returns only one metadata, otherwise
	  * it returns all metadata
	  *
	  * @param int $metadatum_id 	The ID of the metadata to fetch values from
	  * @param array|string $args {
	  *     Optional. Array or string of arguments.
	  *
	  * 	@type mixed		 $collection_id				The collection ID you want to consider or null for all collections. If a collectoin is set
	  *													then only values applied to items in this collection will be returned
	  *
	  *     @type int		 $number					The number of values to return (for pagination). Default empty (unlimited)
	  *
	  *     @type int		 $offset					The offset (for pagination). Default 0
	  *
	  *     @type array|bool $items_filter				Array in the same format used in @see \Tainacan\Repositories\Items::fetch(). It will filter the results to only return values used in the items inside this criteria. If false, it will return all values, even unused ones. Defatul [] (all items)
	  *
	  *     @type array		 $include					Array if ids to be included in the result. Default [] (nothing)
	  *
	  *     @type array		 $search					String to search. It will only return values that has this string. Default '' (nothing)
	  *
	  *     @type array		 $parent_id					Used by taxonomy metadata. The ID of the parent term to retrieve terms from. Default 0
	  *
		*     @type bool		 $count_items				Include the count of items that can be found in each value (uses $items_filter as well). Default false
		*
		*     @type string   $last_term				The last term returned when using a elasticsearch for calculates the facet.
	  *
	  * }
	  *
	  * @return array        Array with the total number of values found. The total number of pages with the current number and the results with id and label for each value. Terms also include parent, taxonomy and number of children.
	  */
	public function fetch_all_metadatum_values( $metadatum_id, $args = [] ) {

		$defaults = array(
			'collection_id' => null,
			'search' => '',
			'offset' => 0,
			'number' => '',
			'include' => [],
			'items_filter' => [],
			'parent_id' => 0,
			'count_items' => false,
			'last_term' => ''
		);
		$args = wp_parse_args($args, $defaults);

		global $wpdb;

		$itemsRepo = Items::get_instance();
		$metadataRepo = Metadata::get_instance();

		$metadatum = $metadataRepo->fetch($metadatum_id);
		$metadatum_type = $metadatum->get_metadata_type();
		$metadatum_options = $metadatum->get_metadata_type_options();

		if ( $metadatum_type === 'Tainacan\Metadata_Types\Taxonomy' ) {
			$taxonomy_id = $metadatum_options['taxonomy_id'];
			$taxonomy_slug = Taxonomies::get_instance()->get_db_identifier_by_id($taxonomy_id);
		}



		$items_query = false;
		if ( false !== $args['items_filter'] && is_array($args['items_filter']) ) {

			$args['items_filter']['fields'] = 'ids';
			unset($args['items_filter']['paged']);
			unset($args['items_filter']['offset']);
			unset($args['items_filter']['perpage']);
			$args['items_filter']['nopaging'] = 1;

			// When filtering the items, we should consider only other metadata, and ignore current metadatum
			// This is because the relation between values from the same metadatum when filtering item is OR,
			// so when you filter items by one value of a metadatum you dont want to exclude all the other possibilities for that meta.
			// Only values of all other filters (facets) are reduced.
			if ( $metadatum_type == 'Tainacan\Metadata_Types\Taxonomy' && isset($args['items_filter']['tax_query']) && is_array($args['items_filter']['tax_query']) ) {
				// remove current taxonomy from tax_query
				$args['items_filter']['tax_query'] = array_filter($args['items_filter']['tax_query'], function($t) use ($taxonomy_slug) { return $t['taxonomy'] != $taxonomy_slug; });
			} elseif ( $metadatum_type != 'Tainacan\Metadata_Types\Taxonomy' && isset($args['items_filter']['meta_query']) && is_array($args['items_filter']['meta_query']) ) {
				// remove current metadata from meta_query
				//var_dump($args['items_filter']['meta_query']);
				$args['items_filter']['meta_query'] = array_filter($args['items_filter']['meta_query'], function($t) use ($metadatum_id) { return $t['key'] != $metadatum_id; });
				//var_dump($args['items_filter']['meta_query']);
			}

		}

		$filter = apply_filters('tainacan-fetch-all-metadatum-values', null, $metadatum, $args);
		if ($filter !== null) {
			return $filter;
		}

		//////////////////////////////////////////
		// Get the query for current items
		// this avoids wp_query to run the query. We just want to build the query
		if ( false !== $args['items_filter'] && is_array($args['items_filter']) ) {
			add_filter('posts_pre_query', '__return_empty_array');
			$items_query = $itemsRepo->fetch($args['items_filter'], $args['collection_id']);
			$items_query = $items_query->request;
			remove_filter('posts_pre_query', '__return_empty_array');
		}
		//// end filtering query ////////
		////////////////////////////////////////////
		////////////////////////////////////////////


		$pagination = '';
		if ( $args['offset'] >= 0 && $args['number'] >= 1 ) {
			$pagination = $wpdb->prepare( "LIMIT %d,%d", (int) $args['offset'], (int) $args['number'] );
		}

		$search_q = '';
		$search = trim($args['search'] ?? '');
		if (!empty($search)) {
			if( $metadatum_type === 'Tainacan\Metadata_Types\Relationship' ) {
				$search_q = $wpdb->prepare("AND meta_value IN ( SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s )", '%' . $search . '%');
			} elseif ( $metadatum_type === 'Tainacan\Metadata_Types\Taxonomy' ) {
				$search_q = $wpdb->prepare("AND t.name LIKE %s", '%' . $search . '%');
			} elseif ( $metadatum_type === 'Tainacan\Metadata_Types\User' ) {
				$search_q = $wpdb->prepare("AND meta_value IN ( SELECT ID FROM $wpdb->users WHERE display_name LIKE %s )", '%' . $search . '%');
			} else {
				$search_q = $wpdb->prepare("AND meta_value LIKE %s", '%' . $search . '%');
			}


		}

		if ( $metadatum_type === 'Tainacan\Metadata_Types\Taxonomy' ) {

			if ($items_query) {

				$check_hierarchy_q = $wpdb->prepare("SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s AND parent > 0 LIMIT 1", $taxonomy_slug);
				$has_hierarchy = ! is_null($wpdb->get_var($check_hierarchy_q));

				if ( ! $has_hierarchy ) {
					$base_query = $wpdb->prepare("FROM $wpdb->term_relationships tr
						INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
						INNER JOIN $wpdb->terms t ON tt.term_id = t.term_id
						INNER JOIN ($items_query) as posts ON tr.object_id = posts.ID
						WHERE
						tt.parent = %d AND
						tt.taxonomy = %s
						$search_q
						ORDER BY t.name ASC
						",
						$args['parent_id'],
						$taxonomy_slug
					);

					$query = "SELECT DISTINCT t.name, t.term_id, tt.term_taxonomy_id, tt.parent $base_query $pagination";

					$total_query = "SELECT COUNT(DISTINCT tt.term_taxonomy_id) $base_query";
					$total = $wpdb->get_var($total_query);

					$results = $wpdb->get_results($query);

				} else {

					$base_query = $wpdb->prepare("
						SELECT DISTINCT t.term_id, t.name, tt.parent, coalesce(tr.term_taxonomy_id, 0) as have_items
						FROM
						$wpdb->terms t INNER JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id
						LEFT JOIN (
							SELECT DISTINCT term_taxonomy_id FROM $wpdb->term_relationships
								INNER JOIN ($items_query) as posts ON $wpdb->term_relationships.object_id = posts.ID
						) as tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
						WHERE tt.taxonomy = %s ORDER BY t.name ASC", $taxonomy_slug
					);

					$all_hierarchy = $wpdb->get_results($base_query);

					if (empty($search)) {
						$results = $this->_process_terms_tree($all_hierarchy, $args['parent_id'], 'parent');
					} else  {
						$results = $this->_process_terms_tree($all_hierarchy, $search, 'name');
					}

					$total = count($results);

					if ( $args['offset'] >= 0 && $args['number'] >= 1 ) {
						$results = array_slice($results, (int) $args['offset'], (int) $args['number']);
					}
				}
			} else {
				$parent_q = $wpdb->prepare("AND tt.parent = %d", $args['parent_id']);
				if ($search_q) {
					$parent_q = '';
				}
				$base_query = $wpdb->prepare("FROM $wpdb->term_taxonomy tt
					INNER JOIN $wpdb->terms t ON tt.term_id = t.term_id
					WHERE 1=1
					$parent_q
					AND tt.taxonomy = %s
					$search_q
					ORDER BY t.name ASC
					",
					$taxonomy_slug
				);

				$query = "SELECT DISTINCT t.name, t.term_id, tt.term_taxonomy_id, tt.parent $base_query $pagination";

				$total_query = "SELECT COUNT(DISTINCT tt.term_taxonomy_id) $base_query";
				$total = $wpdb->get_var($total_query);

				$results = $wpdb->get_results($query);

			}

			// add selected to the result
			if ( !empty($args['include']) ) {
				if ( is_array($args['include']) && !empty($args['include']) ) {

					// protect sql
					$args['include'] = array_map(function($t) { return (int) $t; }, $args['include']);

					$include_ids = implode(',', $args['include']);
					$query_to_include = "SELECT DISTINCT t.name, t.term_id, tt.term_taxonomy_id, tt.parent FROM $wpdb->term_taxonomy tt
						INNER JOIN $wpdb->terms t ON tt.term_id = t.term_id
						WHERE
						t.term_id IN ($include_ids)";

					$to_include = $wpdb->get_results($query_to_include);

					// remove terms that will be included at the begining
					$results = array_filter($results, function($t) use($args) { return !in_array($t->term_id, $args['include']); });

					$results = array_merge($to_include, $results);

				}
			}

			$number = ctype_digit((string)$args['number']) && $args['number'] >=1 ? $args['number'] : $total;
			if( $number < 1){
				$pages = 1;
			} else {
				$pages = ceil( $total / $number );
			}
			$separator = strip_tags(apply_filters('tainacan-terms-hierarchy-html-separator', '>'));
			$values = [];
			foreach ($results as $r) {

				$count_query = $wpdb->prepare("SELECT COUNT(term_id) FROM $wpdb->term_taxonomy WHERE parent = %d", $r->term_id);
				$total_children = $wpdb->get_var($count_query);

				$label = wp_specialchars_decode($r->name);
				$total_items = null;

				if ( $args['count_items'] ) {
					$count_items_query = array(
						'posts_per_page' => 1
					);
					$count_items_query = array_merge($args['items_filter'] != false ? $args['items_filter'] : [], $count_items_query);
					if ( !isset($count_items_query['tax_query']) ) {
						$count_items_query['tax_query'] = [];
					}
					$count_items_query['tax_query'][] = [
						'taxonomy' => $taxonomy_slug,
						'terms' => $r->term_id
					];
					$count_items_results = $itemsRepo->fetch($count_items_query, $args['collection_id']);
					$total_items = $count_items_results->found_posts;

					//$label .= " ($total_items)";

				}

				$values[] = [
					'value' => $r->term_id,
					'label' => $label,
					'total_children' => $total_children,
					'taxonomy' => $taxonomy_slug,
					'taxonomy_id' => $taxonomy_id,
					'parent' => $r->parent,
					'total_items' => $total_items,
					'type' => 'Taxonomy',
					'description' => term_description($r->term_id),
					'hierarchy_path' => get_term_parents_list($r->term_id, $taxonomy_slug, ['format'=>'name', 'separator'=>$separator, 'link'=>false, 'inclusive'=>false])
				];

			}

		} else {

			if ($items_query) {
				$items_query_clause = "($items_query) as qItems";
				$base_query = $wpdb->prepare( "(SELECT DISTINCT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s $search_q ORDER BY meta_value) as qBase", $metadatum_id );
				$total_query = "SELECT COUNT(DISTINCT qBase.meta_value) FROM $base_query INNER JOIN $items_query_clause ON qBase.post_id = qItems.ID";
				$query = "SELECT DISTINCT qBase.meta_value FROM $base_query INNER JOIN $items_query_clause ON qBase.post_id = qItems.ID $pagination";
				//$query = "SELECT DISTINCT meta_value $base_query $pagination";
			} else {
				$base_query = $wpdb->prepare( "FROM $wpdb->postmeta WHERE meta_key = %s $search_q ORDER BY meta_value", $metadatum_id );
				$total_query = "SELECT COUNT(DISTINCT meta_value) $base_query";
				$query = "SELECT DISTINCT meta_value $base_query $pagination";
			}

			$results = $wpdb->get_col($query);
			$total = $wpdb->get_var($total_query);
			$number = ctype_digit((string)$args['number']) && $args['number'] >=1 ? $args['number'] : $total;
			if( $number < 1){
				$pages = 1;
			} else {
				$pages = ceil( $total / $number );
			}

			// add selected to the result
			if ( !empty($args['include']) ) {
				if ( is_array($args['include']) ) {
					$results = array_unique( array_merge($args['include'], $results) );
				}
			}

			$values = [];
			foreach ($results as $r) {

				$label = $r;

				if ( $metadatum_type === 'Tainacan\Metadata_Types\Relationship' ) {
					$_post = get_post($r);
					if ( ! $_post instanceof \WP_Post) {
						continue;
					}
					$label = $_post->post_title;
				} elseif ( $metadatum_type === 'Tainacan\Metadata_Types\Control' ) {
					$metadata_type_object = $metadatum->get_metadata_type_object();
					$label = $metadata_type_object->get_control_metadatum_value($r, $metadatum_options['control_metadatum'], 'string' );
				} elseif ( $metadatum_type === 'Tainacan\Metadata_Types\User' ) {
					$name = get_the_author_meta( 'display_name', $label );
					$label = apply_filters("tainacan-item-get-author-name", $name);
				}

				$total_items = null;

				if ( $args['count_items'] ) {
					$count_items_query = $args['items_filter'];
					$count_items_query['posts_per_page'] = (int)1;
					if ( !isset($count_items_query['meta_query']) ) {
						$count_items_query['meta_query'] = [];
					}
					$count_items_query['meta_query'][] = [
						'key' => $metadatum_id,
						'value' => $r
					];
					$count_items_results = $itemsRepo->fetch($count_items_query, $args['collection_id']);
					$total_items = $count_items_results->found_posts;

					//$label .= " ($total_items)";

				}

				$values[] = [
					'label' => $label,
					'value' => $r,
					'total_items' => $total_items,
					'type' => 'Text'
				];

			}
		}

		return [
			'total' => $total,
			'pages' => $pages,
			'values' => $values,
			'last_term' => $args['last_term']
		];

	}

	/**
	* This method processes the result of the query for all terms in a taxonomy done in get_all_metadatum_values()
	* It efficiently runs through all the terms and checks what terms with a given $parent have items in itself or any of
	* its descendants, keeping the order they originally came.
	*
	* It returns an array with the term objects with the given $parent that have items considering items in its descendants. The objects are
	* in the same format they came, as expected by the rest of the method.
	*
	* This method is public only for tests purposes, it should not be used anywhere else
	*/
	public function _process_terms_tree($tree, $search_value, $search_type='parent') {

		$h_map = []; // all terms will mapped to this array
		$results = []; // terms that match search criteria will be copied to this array
		foreach ( $tree as $h ) {

			// if current term comes with have_items = 1 from the database
			// or, if it was temporarily added by its child that had have_items = 1:
			if ( $h->have_items > 0 || ( isset($h_map[$h->term_id]) && $h_map[$h->term_id]->have_items > 0 ) ) {

				// in the case of a parent that was temporarily added by a child, mark it as having items as well
				$h->have_items = 1;
				$h_map[$h->term_id] = $h; // send it to the map array, overriding temporary item if existed

				// if current term matches seach criteria, add to results array
				if(($search_type == 'parent' && $h->parent == $search_value) ||
					 ($search_type == 'name' && strpos(strtolower($h->name), strtolower($search_value)) !== false)) {
					$results[$h->term_id] = $h;
				}

				// Now that we know this ter have_items. Lets climb the tree all the way up
				// marking all parents with have_items = 1
				$_parent = $h->parent;

				// If parent was not added to the map array yet
				// Lets add a temporary entry
				if ( $h->parent > 0 && !isset($h_map[$_parent]) ) {
					$h_map[$_parent] = (object)['have_items' => 1];
				}

				// Now lets loop thorough the map array until I check all my parents
				while( isset($h_map[$_parent]) && $h_map[$_parent]->have_items != 1 ) {

					// If my parent was added before, but marked with have_items = 0
					// Lets set it to 1

					$h_map[$_parent]->have_items = 1;

					// If my parent is a whole object, and not a temporary one
					if ( isset($h_map[$_parent]->parent) ) {
						// if parent matches search criteira, add to results
						if(($search_type == 'parent' && $h_map[$_parent]->parent == $search_value) ||
							 ($search_type == 'name' && strpos(strtolower($h_map[$_parent]->name), strtolower($search_value)) !== false)) {
							$results[$h_map[$_parent]->term_id] = $h_map[$_parent];
						}
						// increment _parent to next Loop interation
						$_parent = $h_map[$_parent]->parent;
					} else {
						// Quit loop. We have reached as high as we could in the tree
						$_parent = 0;
					}

				}

			} else {

				// if current term have_items = 0

				// add it to the map
				$h_map[$h->term_id] = $h;

				// if parent was not mapped yet, create a temporary entry for him
				if ( $h->parent > 0 && !isset($h_map[$h->parent]) ) {
					$h_map[$h->parent] = (object)['have_items' => $h->have_items];
				}

				// if item matches search criteria, add it to the results
				if(($search_type == 'parent' && $h->parent == $search_value) ||
					 ($search_type == 'name' && $h->have_items > 0 && strpos(strtolower($h->name), strtolower($search_value)) !== false)) {
					$results[$h->term_id] = $h;
				}
			}

		}

		// Results have all terms that matches search criteria. Now we unset those who dont have items
		// and set it back to incremental keys]
		// we could have sent to $results only those with items, but doing that we would not preserve their order
		$results = array_reduce($results, function ($return, $el) {
			if ($el->have_items > 0) {
				$return[] = $el;
			}
			return $return;
		}, []);

		return $results;

	}

	/**
	 * Stores the value of the taxonomy_id option to use on update_taxonomy_metadatum method.
	 *
	 */
	private function pre_update_taxonomy_metadatum( $metadatum ) {
		$metadata_type = $metadatum->get_metadata_type_object();
		$current_tax   = '';
		if ( $metadata_type->get_primitive_type() == 'term' ) {

			$options = $this->get_mapped_property( $metadatum, 'metadata_type_options' );
			$metadata_type->set_options( $options );
			$current_tax = $metadata_type->get_option( 'taxonomy_id' );
		}
		$this->current_taxonomy = $current_tax;
	}

	/**
	 * Triggers hooks when saving a Taxonomy Metadatum, indicating wich taxonomy was added or removed from a collection.
	 *
	 * This is used by Taxonomies repository to update the collections_ids property of the taxonomy as
	 * a metadatum type taxonomy is inserted or removed
	 *
	 * @param  [type] $metadatum [description]
	 *
	 * @return void [type]        [description]
	 */
	private function update_taxonomy_metadatum( $metadatum ) {
		$metadata_type = $metadatum->get_metadata_type_object();
		$new_tax       = '';

		if ( $metadata_type->get_primitive_type() == 'term' ) {
			$new_tax = $metadata_type->get_option( 'taxonomy_id' );
		}

		if ( $new_tax != $this->current_taxonomy ) {
			$collection = $metadatum->get_collection_id();

			if ( ! empty( $this->current_taxonomy ) && $collection ) {
				do_action( 'tainacan-taxonomy-removed-from-collection', $this->current_taxonomy, $collection );
			}

			if ( ! empty( $new_tax ) && $collection ) {
				do_action( 'tainacan-taxonomy-added-to-collection', $new_tax, $collection );
			}

		}
	}

	/**
	 * Creates an index with the exploded values of metadata_type_options array. Each option is prefixed with '_option_'
	 * This is useful to allow metadata to be queried based on a specific value of a metadata type option.
	 * For example, fetch all taxonomy metadata which the taxonomy_id metadata type option is equal to 4
	 *
	 * $metadata_repository->fetch([
	 * 		'meta_query' => [
	 * 			[
	 * 				'key' => '_option_taxonomy_id',
	 * 				'value' => 4
	 * 			]
	 * 		]
	 * ])
	 *
	 * @var Entities\Metadatum $metadatum
	 */
	private function update_metadata_type_index( Entities\Metadatum $metadatum ) {

		$options = $this->get_mapped_property($metadatum, 'metadata_type_options');
		if (!is_array($options)) {
			return;
		}
		foreach ($options as $option => $value) {
			update_post_meta($metadatum->get_id(), '_option_' . $option, $value);
		}

	}

	/**
	 * @inheritDoc
	 */
	public function delete( Entities\Entity $entity, $permanent = true ) {
		$this->delete_taxonomy_metadatum($entity);
		return parent::delete($entity, $permanent);
	}

	private function delete_taxonomy_metadatum( $metadatum ) {
		$metadata_type = $metadatum->get_metadata_type_object();

		if ( $metadata_type->get_primitive_type() == 'term' ) {
			$removed_tax = $metadata_type->get_option( 'taxonomy_id' );

			$collection = $metadatum->get_collection_id();

			if ( ! empty( $removed_tax ) && $collection ) {
				do_action( 'tainacan-taxonomy-removed-from-collection', $removed_tax, $collection );
			}
		}
	}

	/**
	 * When a private taxonomy is saved, check if there are any public metadata and set them to private
	 *
	 * @param \Tainacan\Entities\Taxonomy $taxonomy
	 * @return void
	 */
	public function hook_taxonomies_saved_as_private($taxonomy) {

		if ( $taxonomy instanceof Entities\Taxonomy ) {

			$status_obj = get_post_status_object( $taxonomy->get_status() );

			if ( is_object($status_obj) && ! $status_obj->public ) {

				$stati = get_post_stati(['public' => true]);

				$taxonomy_id = $taxonomy->get_id();

				$args = [
					'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
					'meta_query' => [
						[
							'key' => '_option_taxonomy_id',
							'value' => $taxonomy_id
						]
					],
					'post_status' => $stati
				];
				$metadata = $this->fetch($args, 'OBJECT');

				foreach ($metadata as $meta) {
					$meta->set_status( $taxonomy->get_status() );
					if ( $meta->validate() ) {
						$this->insert($meta);
					}

				}

			}


		}

	}

	/**
	 * When a taxonomy is saved disabling allow_insert. All related metadata should set allow_new_terms to 'no'
	 *
	 * @param \Tainacan\Entities\Taxonomy $taxonomy
	 * @return void
	 */
	public function hook_taxonomies_saved_not_allow_insert_new_terms($taxonomy) {

		if ( $taxonomy instanceof Entities\Taxonomy && 'no' === $taxonomy->get_allow_insert() ) {

			$taxonomy_id = $taxonomy->get_id();

			$args = [
				'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'meta_query' => [
					[
						'key' => '_option_taxonomy_id',
						'value' => $taxonomy_id
					],
					[
						'key' => '_option_allow_new_terms',
						'value' => 'yes'
					]
				],
				'post_status' => 'any',
			];

			$metadata = $this->fetch($args, 'OBJECT');

			foreach ($metadata as $meta) {
				$options = $meta->get_metadata_type_options();
				$options['allow_new_terms'] = 'no';

				$meta->set_metadata_type_options( $options );
				if ( $meta->validate() ) {
					$this->insert($meta);
				}

			}

		}

	}

	/**
	 * Test if a metadata is enabled on collection.
	 *
	 * @param \Tainacan\Entities\Collection $collection
	 * @param \Tainacan\Entities\Metadatum $metadata
	 * 
	 * @return boolean
	 */
	public function metadata_is_enabled($collection, $metadata) {
		$order = $collection->get_metadata_order();
		if($order == false) return true;
		$order = ( is_array( $order ) ) ? $order : unserialize( $order );
		if( is_array($order) ) {
			foreach ($order as $metadata_order) {
				if( $metadata_order['id'] == $metadata->get_id() || $metadata_order['id'] == $metadata->get_parent() ) {
					if($metadata_order['enabled'] == false)
						return false;
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * When a metadata is saved, if the metadata section changes, the ordering needs to be updated
	 *
	 * @param \Tainacan\Entities\Metadatum $metadata
	 * @return void
	 */
	public function hook_metadata_update_order($metadata) {
		$tainacan_metadata_sections_repository = \tainacan_metadata_sections();
		$tainacan_collections_repository = \tainacan_collections();
		$metadata_section_id = $metadata->get_metadata_section_id();
		$metadata_section = $tainacan_metadata_sections_repository->fetch($metadata_section_id);
		if ( $metadata_section instanceof \Tainacan\Entities\Metadata_Section ) {
			$collection = $metadata_section->get_collection();
			$metadata_sections_order = $collection->get_metadata_section_order();
			if( empty($metadata_sections_order) ) {
				return;
			}
			foreach( $metadata_sections_order as &$metadata_section_order ) {
				$pos = array_search($metadata->get_id(), array_column($metadata_section_order['metadata_order'], 'id'));
				if($pos !== false) {
					if( $metadata_section_id != $metadata_section_order['id']) {
						array_splice($metadata_section_order['metadata_order'], $pos, 1);
					}
				} else if($metadata_section_id == $metadata_section_order['id']) {
					$metadata_section_order['metadata_order'][] = ["id" => $metadata->get_id(), "enabled" => $metadata->get_enabled_for_collection()];
				}
			}
			$collection->set_metadata_section_order($metadata_sections_order);
			if($collection->validate()) {
				$tainacan_collections_repository->update($collection);
			}
		}
	}

}
