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

	}

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Repository::get_map()
	 */
	public function get_map() {
		return apply_filters( 'tainacan-get-map-' . $this->get_name(), [
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
				'description' => __( 'A unique and santized string representation of the metadata', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'order'                 => [
				'map'         => 'menu_order',
				'title'       => __( 'Order', 'tainacan' ),
				'type'        => 'string/integer',
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
				'description' => __( 'The metadata description', 'tainacan' ),
				'default'     => '',
				//'on_error'   => __('The description should be a text value', 'tainacan'),
				//'validation' => v::stringType()->notEmpty(),
			],
			'metadata_type'         => [
				'map'         => 'meta',
				'title'       => __( 'Type', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The metadata type', 'tainacan' ),
				'on_error'    => __( 'Metadata type is empty', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'required'              => [
				'map'         => 'meta',
				'title'       => __( 'Required', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The metadata is required', 'tainacan' ),
				'on_error'    => __( 'The metadata content is invalid', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'default'     => 'no'
			],
			'collection_key'        => [
				'map'         => 'meta',
				'title'       => __( 'Collection key', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Metadata value should not be repeated', 'tainacan' ),
				'on_error'    => __( 'Collection key is invalid', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'default'     => 'no'
			],
			'multiple'              => [
				'map'         => 'meta',
				'title'       => __( 'Multiple', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Allow multiple values for the metadata', 'tainacan' ),
				'on_error'    => __( 'Invalid multiple metadata', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ),
				// yes or no. It cant be multiple if its collection_key
				'default'     => 'no'
			],
			'cardinality'           => [
				'map'         => 'meta',
				'title'       => __( 'Cardinality', 'tainacan' ),
				'type'        => 'string/number',
				'description' => __( 'Number of multiples possible metadata', 'tainacan' ),
				'on_error'    => __( 'This number of multiples metadata is not allowed', 'tainacan' ),
				'validation'  => v::numeric()->positive(),
				'default'     => 1
			],
			'mask'                  => [
				'map'         => 'meta',
				'title'       => __( 'Mask', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The mask to be used in the metadata', 'tainacan' ),
				//'on_error'   => __('Mask is invalid', 'tainacan'),
				//'validation' => ''
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
				'type'        => 'array/object/string',
				'items'       => [ 'type' => 'array/string/integer/object' ],
				'description' => __( 'Specific options for metadata type', 'tainacan' ),
				// 'validation' => ''
			],
			'collection_id'         => [ // not showed in form
				'map'         => 'meta',
				'title'       => __( 'Collection', 'tainacan' ),
				'type'        => 'integer/string',
				'description' => __( 'The collection ID', 'tainacan' ),
				//'validation' => ''
			],
			'accept_suggestion'     => [
				'map'         => 'meta',
				'title'       => __( 'Metadata Value Accepts Suggestions', 'tainacan' ),
				'type'        => 'bool',
				'description' => __( 'Allow community to suggest different values for the metadata', 'tainacan' ),
				'default'     => false,
				'validation'  => v::boolType()
			],
			'exposer_mapping'       => [
				'map'         => 'meta',
				'title'       => __( 'Relationship metadata mapping', 'tainacan' ),
				'type'        => 'array/object/string',
				'items'       => [ 'type' => 'array/string/integer/object' ],
				'description' => __( 'The metadata mapping options. Metadata can be configured to match another type of data distribution.', 'tainacan' ),
				'on_error'    => __( 'Invalid Metadata Mapping', 'tainacan' ),
				//'validation' =>  v::arrayType(),
				'default'     => []
			],
			'display'               => [
				'map'         => 'meta',
				'title'       => __( 'Display', 'tainacan' ),
				'type'        => __( 'string' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no', 'never' ] ),
				'description' => __( 'Display by default on listing or do not display or never display.', 'tainacan' ),
				'default'     => 'yes'
			],
			'semantic_uri'          => [
				'map'         => 'meta',
				'title'       => __( 'The semantic metadatum description URI' ),
				'type'        => __( 'url' ),
				'validation'  => v::optional( v::url() ),
				'description' => __( 'The semantic metadatum description URI like: ', 'tainacan' ) . 'https://schema.org/URL',
				'default'     => ''
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
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'map_meta_cap'        => true,
			'show_in_nav_menus'   => false,
			'capability_type'     => Entities\Metadatum::get_capability_type(),
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
	 * @param array $args WP_Query args || int $args the metadatum id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return Entities\Metadatum|\WP_Query|Array an instance of wp query OR array of entities;
	 * @throws \Exception
	 */
	public function fetch( $args, $output = null ) {

		if ( is_numeric( $args ) ) {
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				return new Entities\Metadatum( $existing_post );
			} else {
				return [];
			}
		} elseif ( is_array( $args ) ) {

			$args = array_merge( [
				'posts_per_page' => - 1,
			], $args );

			$args = $this->parse_fetch_args( $args );

			$args['post_type'] = Entities\Metadatum::get_post_type();

			$args = apply_filters( 'tainacan_fetch_args', $args, 'metadata' );

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
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return array Entities\Metadatum
	 * @throws \Exception
	 */
	public function fetch_by_collection( Entities\Collection $collection, $args = [], $output = null ) {
		$collection_id = $collection->get_id();

		//get parent collections
		$parents = get_post_ancestors( $collection_id );

		//insert the actual collection
		$parents[] = $collection_id;

		//search for default metadatum
		$parents[] = $this->get_default_metadata_attribute();

		$meta_query = array(
			'key'     => 'collection_id',
			'value'   => $parents,
			'compare' => 'IN',
		);

		$args = array_merge( [
			'parent' => 0
		], $args );

		if ( isset( $args['meta_query'] ) ) {
			$args['meta_query'][] = $meta_query;
		} elseif ( is_array( $args ) ) {
			$args['meta_query'] = array( $meta_query );
		}

		return $this->order_result(
			$this->fetch( $args, $output ),
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
		$parents[] = $collection_id;

		//search for default metadatum
		$parents[] = $this->get_default_metadata_attribute();

		$meta_query = array(
			'key'     => 'collection_id',
			'value'   => $parents,
			'compare' => 'IN',
		);

		$args = array_merge( [
			'parent' => 0
		], $args );

		if ( isset( $args['meta_query'] ) ) {
			$args['meta_query'][] = $meta_query;
		} elseif ( is_array( $args ) ) {
			$args['meta_query'] = array( $meta_query );
		}

		return $this->fetch_ids( $args );
	}

	/**
	 * Ordinate the result from fetch response if $collection has an ordination,
	 * metadata not ordinated appear on the end of the list
	 *
	 *
	 * @param \WP_Query|array $result Response from method fetch
	 * @param Entities\Collection $collection
	 * @param bool $include_disabled Wether to include disabled metadata in the results or not
	 *
	 * @return array or WP_Query ordinate
	 */
	public function order_result( $result, Entities\Collection $collection, $include_disabled = false ) {
		$order = $collection->get_metadata_order();
		if ( $order ) {
			$order = ( is_array( $order ) ) ? $order : unserialize( $order );

			if ( is_array( $result ) ) {
				$result_ordinate = [];
				$not_ordinate    = [];

				foreach ( $result as $item ) {
					$id    = $item->WP_Post->ID;
					$index = array_search( $id, array_column( $order, 'id' ) );

					if ( $index !== false ) {

						// skipping metadata disabled if the arg is set
						if ( ! $include_disabled && isset( $order[ $index ]['enabled'] ) && ! $order[ $index ]['enabled'] ) {
							continue;
						}

						$enable = ( isset( $order[ $index ]['enabled'] ) ) ? $order[ $index ]['enabled'] : true;
						$item->set_enabled_for_collection( $enable );

						$result_ordinate[ $index ] = $item;
					} else {
						$not_ordinate[] = $item;
					}
				}

				ksort( $result_ordinate );
				$result_ordinate = array_merge( $result_ordinate, $not_ordinate );

				return $result_ordinate;
			} // if the result is a wp query object
			else {
				$posts           = $result->posts;
				$result_ordinate = [];
				$not_ordinate    = [];

				foreach ( $posts as $item ) {
					$id    = $item->ID;
					$index = array_search( $id, array_column( $order, 'id' ) );

					if ( $index !== false ) {
						$result_ordinate[ $index ] = $item;
					} else {
						$not_ordinate[] = $item;
					}
				}

				ksort( $result_ordinate );
				$result->posts = $result_ordinate;
				$result->posts = array_merge( $result->posts, $not_ordinate );

				return $result;
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

		$this->update_taxonomy_metadatum( $new_metadatum );

		return $new_metadatum;
	}

	/**
	 * @param $object
	 * @param $new_values
	 *
	 * @return mixed|string|Entities\Entity
	 * @throws \Exception
	 */
	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	/**
	 * @param $metadatum_id
	 *
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function delete( $metadatum_id ) {
		$deleted = new Entities\Metadatum( wp_delete_post( $metadatum_id, true ) );

		if ( $deleted ) {
			$this->logs_repository->insert_log( $deleted, [], false, true );

			do_action( 'tainacan-deleted', $deleted );
		}

		return $deleted;
	}

	/**
	 * @param $metadatum_id
	 *
	 * @return mixed|Entities\Metadatum
	 * @throws \Exception
	 */
	public function trash( $metadatum_id ) {
		$this->delete_taxonomy_metadatum( $metadatum_id );

		$trashed = new Entities\Metadatum( wp_trash_post( $metadatum_id ) );

		if ( $trashed ) {
			$this->logs_repository->insert_log( $trashed, [], false, false, true );

			do_action( 'tainacan-trashed', $trashed );
		}

		return $trashed;
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

		if ( $output === 'NAME' ) {
			foreach ( $this->metadata_types as $metadata_type ) {
				$return[] = str_replace( 'Tainacan\Metadata_Types\\', '', $metadata_type );
			}

			return $return;
		}

		return $this->metadata_types;
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

		return $data_core_metadata = [
			'core_description' => [
				'name'            => 'Description',
				'description'     => 'description',
				'collection_id'   => $collection->get_id(),
				'metadata_type'   => 'Tainacan\Metadata_Types\Core_Description',
				'status'          => 'publish',
				'exposer_mapping' => [
					'dublin-core' => 'description'
				]
			],
			'core_title'       => [
				'name'            => 'Title',
				'description'     => 'title',
				'collection_id'   => $collection->get_id(),
				'metadata_type'   => 'Tainacan\Metadata_Types\Core_Title',
				'status'          => 'publish',
				'exposer_mapping' => [
					'dublin-core' => 'title'
				]
			]
		];

	}

	/**
	 * @param Entities\Collection $collection
	 *
	 * @return bool
	 * @throws \ErrorException
	 * @throws \Exception
	 */
	public function register_core_metadata( Entities\Collection $collection ) {

		if ( $collection->get_status() == 'auto-draft' ) {
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
	 * block user from remove core metadata
	 *
	 * @param $before  wordpress pass a null value
	 * @param $post the post which is moving to trash
	 *
	 * @return null/bool
	 * @throws \Exception
	 */
	public function disable_delete_core_metadata( $before, $post ) {
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
		$metadatum = $this->fetch( $post->ID );

		if ( $metadatum && in_array( $metadatum->get_metadata_type(), $this->core_metadata ) && is_numeric( $metadatum->get_collection_id() ) ) {
			return false;
		}
	}

	/**
	 * returns all core items from a specific collection
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
			]
		], 'OBJECT' );

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
		], 'OBJECT' );

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
		], 'OBJECT' );

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


	# TODO: Fetch all metadatum value for repository level

	/**
	 * Fetch all values of a metadatum from a collection or repository
	 *
	 * @param $collection_id
	 * @param $metadatum_id
	 *
	 * @param string $search
	 *
	 * @param int $offset
	 * @param string $number
	 *
	 * @return array|null|object
	 * @throws \Exception
	 */
	public function fetch_all_metadatum_values( $collection_id, $metadatum_id, $search = '', $offset = 0, $number = '' ) {
		global $wpdb;

		// Clear the result cache
		$wpdb->flush();

		$metadatum = new Entities\Metadatum( $metadatum_id );

		// handle core titles
		if ( strpos( $metadatum->get_metadata_type(), 'Core' ) !== false && $search ) {
			$collection     = new Entities\Collection( $collection_id );
			$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

			if ( $number >= 1 && $offset >= 0 ) {
				$items = $Tainacan_Items->fetch( [
					's'              => $search,
					'offset'         => $offset,
					'posts_per_page' => $number
				], $collection, 'OBJECT' );
			} else {
				$items = $Tainacan_Items->fetch( [ 's' => $search ], $collection, 'OBJECT' );
			}

			$return = [];

			foreach ( $items as $item ) {
				if ( strpos( $metadatum->get_metadata_type(), 'Core_Title' ) !== false ) {
					$title = $item->get_title();

					if ( ! empty( $search ) && stristr( $title, $search ) !== false ) {
						$return[] = [ 'metadatum_id' => $metadatum_id, 'mvalue' => $title ];
					} elseif ( empty( $search ) ) {
						$return[] = [ 'metadatum_id' => $metadatum_id, 'mvalue' => $title ];
					}
				} else {
					$description = $item->get_description();

					if ( ! empty( $search ) && stristr( $description, $search ) !== false ) {
						$return[] = [ 'metadatum_id' => $metadatum_id, 'mvalue' => $description ];
					} elseif ( empty( $search ) ) {
						$return[] = [ 'metadatum_id' => $metadatum_id, 'mvalue' => $description ];
					}
				}
			}

			return $return;
		}

		$item_post_type = "%%{$collection_id}_item";

		$collection   = new Entities\Collection( $collection_id );
		$capabilities = $collection->get_capabilities();

		$results = [];

		$search_query = '';
		if ( $search ) {
			$search_param = '%' . $search . '%';
			$search_query = $wpdb->prepare( "WHERE meta_value LIKE %s", $search_param );
		}

		$pagination = '';
		if ( $offset >= 0 && $number >= 1 ) {
			$pagination = $wpdb->prepare( "LIMIT %d,%d", (int) $offset, (int) $number );
		}

		// If no has logged user or actual user can not read private posts
		if ( get_current_user_id() === 0 || ! current_user_can( $capabilities->read_private_posts ) ) {
			$args = [
				'exclude_from_search' => false,
				'public'              => true,
				'private'             => false,
				'internal'            => false,
			];

			$post_statuses = get_post_stati( $args, 'names', 'and' );

			foreach ( $post_statuses as $post_status ) {

				if ( $collection_id ) {
					$sql_string = $wpdb->prepare(
						"SELECT DISTINCT metadatum_id, mvalue 
				  		FROM (
			  				SELECT ID as item_id
		  					FROM $wpdb->posts
	  						WHERE post_type LIKE %s AND post_status = %s
  						) items
						JOIN (
						  	SELECT meta_key as metadatum_id, meta_value as mvalue, post_id
					  	  	FROM $wpdb->postmeta $search_query
				  		) metas
			  			ON items.item_id = metas.post_id AND metas.metadatum_id = %d ORDER BY mvalue $pagination",
						$item_post_type, $post_status, $metadatum_id
					);
				} else {
					$sql_string = $wpdb->prepare(
						"SELECT DISTINCT metadatum_id, mvalue 
				  		FROM (
			  				SELECT ID as item_id
		  					FROM $wpdb->posts
	  						WHERE post_status = %s
  						) items
						JOIN (
						  	SELECT meta_key as metadatum_id, meta_value as mvalue, post_id
					  	  	FROM $wpdb->postmeta $search_query
				  		) metas
			  			ON items.item_id = metas.post_id AND metas.metadatum_id = %d ORDER BY mvalue $pagination",
						$post_status, $metadatum_id
					);
				}

				$pre_result = $wpdb->get_results( $sql_string, ARRAY_A );

				if ( ! empty( $pre_result ) ) {
					foreach ( $pre_result as $pre ) {
						$results[] = $pre;
					}
				}
			}
		} elseif ( current_user_can( $capabilities->read_private_posts ) ) {
			$args = [
				'exclude_from_search' => false,
			];

			$post_statuses = get_post_stati( $args, 'names', 'and' );

			foreach ( $post_statuses as $post_status ) {

				if ( $collection_id ) {
					$sql_string = $wpdb->prepare(
						"SELECT DISTINCT metadatum_id, mvalue 
		  	        	FROM (
	  	  		        	SELECT ID as item_id
  	  			        	FROM $wpdb->posts
  				        	WHERE post_type LIKE %s AND post_status = %s
					  	) items
					  	JOIN (
					    	SELECT meta_key as metadatum_id, meta_value as mvalue, post_id
							FROM $wpdb->postmeta $search_query
					  	) metas
					  	ON items.item_id = metas.post_id AND metas.metadatum_id = %d ORDER BY mvalue $pagination",
						$item_post_type, $post_status, $metadatum_id
					);
				} else {
					$sql_string = $wpdb->prepare(
						"SELECT DISTINCT metadatum_id, mvalue 
		  	        	FROM (
	  	  		        	SELECT ID as item_id
  	  			        	FROM $wpdb->posts
  				        	WHERE post_status = %s
					  	) items
					  	JOIN (
					    	SELECT meta_key as metadatum_id, meta_value as mvalue, post_id
							FROM $wpdb->postmeta $search_query
					  	) metas
					  	ON items.item_id = metas.post_id AND metas.metadatum_id = %d ORDER BY mvalue $pagination",
						$post_status, $metadatum_id
					);
				}

				$pre_result = $wpdb->get_results( $sql_string, ARRAY_A );

				if ( ! empty( $pre_result ) ) {
					foreach ( $pre_result as $pre ) {
						$results[] = $pre;
					}
				}
			}
		}

		$spliced = $this->unique_multidimensional_array( $results, 'mvalue' );

		if($number > 0 && count($spliced) > $number){
			array_splice($spliced, (int) $number);
		}

		return $spliced;
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

	private function delete_taxonomy_metadatum( $metadatum_id ) {
		$metadatum     = $this->fetch( $metadatum_id );
		$metadata_type = $metadatum->get_metadata_type_object();

		if ( $metadata_type->get_primitive_type() == 'term' ) {
			$removed_tax = $metadata_type->get_option( 'taxonomy_id' );

			$collection = $metadatum->get_collection_id();

			if ( ! empty( $removed_tax ) && $collection ) {
				do_action( 'tainacan-taxonomy-removed-from-collection', $removed_tax, $collection );
			}
		}
	}
}
