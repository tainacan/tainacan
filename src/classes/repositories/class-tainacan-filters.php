<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;

class Filters extends Repository {
	public $entities_type = '\Tainacan\Entities\Filter';
	public $filters_types = [];

	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		parent::__construct();
		add_action( 'tainacan-deleted-tainacan-metadatum', array( &$this, 'hook_delete_when_metadata_deleted' ), 10, 2 );
	}

	public function get_map() {
		return apply_filters( 'tainacan-get-map-' . $this->get_name(), [
			'name'                => [
				'map'         => 'post_title',
				'title'       => __( 'Name', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Name of the filter', 'tainacan' ),
				'on_error'    => __( 'The filter name should be a text value and not empty', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'order'               => [
				'map'         => 'menu_order',
				'title'       => __( 'Order', 'tainacan' ),
				'type'        => 'string/integer',
				'description' => __( 'Filter order. This metadata is used if filters were manually ordered.', 'tainacan' ),
				'validation'  => ''
			],
			'description'         => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The filter description', 'tainacan' ),
				'validation'  => '',
				'default'     => '',
			],
			'filter_type_options' => [
				'map'         => 'meta',
				'title'       => __( 'Filter type options', 'tainacan' ),
				'type'        => 'array/object/string',
				'items'       => [ 'type' => 'array/string/integer/object' ],
				'description' => __( 'The filter type options', 'tainacan' ),
				'validation'  => ''
			],
			'filter_type'         => [
				'map'         => 'meta',
				'title'       => __( 'Type', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The filter type', 'tainacan' ),
				'validation'  => ''
			],
			'collection_id'       => [
				'map'         => 'meta',
				'title'       => __( 'Collection', 'tainacan' ),
				'type'        => 'integer/string',
				'description' => __( 'The collection ID', 'tainacan' ),
				'validation'  => ''
			],
			'color'               => [
				'map'         => 'meta',
				'title'       => __( 'Color', 'tainacan' ),
				'type'        => 'integer/string',
				'description' => __( 'Filter color', 'tainacan' ),
				'validation'  => ''
			],
			'metadatum_id'           => [
				'map'         => 'meta',
				'title'       => __( 'Metadata', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'Filter metadata', 'tainacan' ),
				'validation'  => ''
			],
			'max_options'         => [
				'map'         => 'meta',
				'title'       => __( 'Max of options', 'tainacan' ),
				'type'        => 'integer/string',
				'description' => __( 'The max number of options to be showed in filter sidebar.', 'tainacan' ),
				'validation'  => '',
				'default'     => 4
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
			'name'               => __( 'Filters', 'tainacan' ),
			'singular_name'      => __( 'Filter', 'tainacan' ),
			'add_new'            => __( 'Add new', 'tainacan' ),
			'add_new_item'       => __( 'Add new Filter', 'tainacan' ),
			'edit_item'          => __( 'Edit Filter', 'tainacan' ),
			'new_item'           => __( 'New Filter', 'tainacan' ),
			'view_item'          => __( 'View Filter', 'tainacan' ),
			'search_items'       => __( 'Search Filters', 'tainacan' ),
			'not_found'          => __( 'No Filters found ', 'tainacan' ),
			'not_found_in_trash' => __( 'No Filters found in trash', 'tainacan' ),
			'parent_item_colon'  => __( 'Parent Filter:', 'tainacan' ),
			'menu_name'          => __( 'Filters', 'tainacan' )
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
		register_post_type( Entities\Filter::get_post_type(), $args );
	}


    /**
     * @param Entities\Metadatum $metadatum
     * @return int
     *
    public function insert($metadatum) {
        // First iterate through the native post properties
        $map = $this->get_map();
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
                $metadatum->WP_Post->{$mapped['map']} = $metadatum->get_mapped_property($prop);
            }
        }

        // save post and get its ID
        $metadatum->WP_Post->post_type = Entities\Filter::get_post_type();
        $metadatum->WP_Post->post_status = 'publish';
        $id = wp_insert_post($metadatum->WP_Post);
        $metadatum->WP_Post = get_post($id);

        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'meta') {
                update_post_meta($id, $prop, $metadatum->get_mapped_property($prop));
            } elseif ($mapped['map'] == 'meta_multi') {
                $values = $metadatum->get_mapped_property($prop);

                delete_post_meta($id, $prop);

                if (is_array($values)){
                    foreach ($values as $value){
                        add_post_meta($id, $prop, $value);
                    }
                }
            }
        }

        // return a brand new object
        return new Entities\Filter($metadatum->WP_Post);
    }*/

	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	/**
	 * fetch filter based on ID or WP_Query args
	 *
	 * Filters are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 * 
	 * If a number is passed to $args, it will return a \Tainacan\Entities\Filter object.  But if the post is not found or
	 * does not match the entity post type, it will return an empty array
	 *
	 * @param array $args WP_Query args || int $args the filter id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return \WP_Query|Array an instance of wp query OR array of entities;
	 */
	public function fetch( $args = [], $output = null ) {
		if ( is_numeric( $args ) ) {
			
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				try {
					return new Entities\Filter( $existing_post );
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

			$args['post_type'] = Entities\Filter::get_post_type();

			$args = apply_filters( 'tainacan_fetch_args', $args, 'filters' );

			$wp_query = new \WP_Query( $args );

			return $this->fetch_output( $wp_query, $output );
		}
	}


	/**
	 * register metadatum types class on array of types
	 *
	 * @param $class_name string | object The class name or the instance
	 */
	public function register_filter_type( $class_name ) {
		if ( is_object( $class_name ) ) {
			$class_name = get_class( $class_name );
		}

		if ( ! in_array( $class_name, $this->filters_types ) ) {
			$this->filters_types[] = $class_name;
		}
	}

	/**
	 * register metadatum types class on array of types
	 *
	 * @param $class_name string | object The class name or the instance
	 */
	public function deregister_filter_type( $class_name ) {
		if ( is_object( $class_name ) ) {
			$class_name = get_class( $class_name );
		}

		$key = array_search( $class_name, $this->filters_types );
		if ( $key !== false ) {
			unset( $this->filters_types[ $key ] );
		}
	}

	/**
	 * fetch all registered filter type classes
	 *
	 * Possible outputs are:
	 * CLASS (default) - returns the Class name of of filter types registered
	 * NAME - return an Array of the names of filter types registered
	 *
	 * @param $output string CLASS | NAME
	 *
	 * @return array of Entities\Filter_Types\Filter_Type classes path name
	 */
	public function fetch_filter_types( $output = 'CLASS' ) {
		$return = [];

		do_action( 'register_filter_types' );

		if ( $output === 'NAME' ) {
			foreach ( $this->filters_types as $filter_type ) {
				$return[] = str_replace( 'Tainacan\Filter_Types\\', '', $filter_type );
			}

			return $return;
		}

		return $this->filters_types;
	}

	/**
	 * fetch only supported filters for the type specified
	 *
	 * @param ( string || array )  $types Primitve types of metadatum ( float, string, int)
	 *
	 * @return array Filters supported by the primitive types passed in $types
	 */
	public function fetch_supported_filter_types( $types ) {
		$filter_types           = $this->fetch_filter_types();
		$supported_filter_types = [];

		foreach ( $filter_types as $filter_type ) {
			$filter = new $filter_type();

			if ( ( is_array( $types ) ) ) {
				foreach ( $types as $single_type ) {
					if ( in_array( $single_type, $filter->get_supported_types() ) ) {
						$supported_filter_types[] = $filter;
					}
				}
			} else if ( in_array( $types, $filter->get_supported_types() ) ) {
				$supported_filter_types[] = $filter;
			}
		}

		return $supported_filter_types;
	}
	
	/**
	 * fetch filters IDs based on WP_Query args
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
	 * fetch filters by collection, searches all filters available
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
		$parents[] = 'filter_in_repository';

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
		} else {
			$args['meta_query'] = array( $meta_query );
		}

		return $this->order_result(
			$this->fetch( $args, $output ),
			$collection,
			isset( $args['include_disabled'] ) ? $args['include_disabled'] : false
		);
	}
	
	/**
	 * fetch filters IDs by collection, considering inheritance
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
		$parents[] = 'filter_in_repository';

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
	 * filters not ordinated appear on the end of the list
	 *
	 *
	 * @param $result Response from method fetch
	 * @param Entities\Collection $collection
	 *
	 * @return array or WP_Query ordinate
	 */
	public function order_result( $result, Entities\Collection $collection, $include_disabled = false ) {
		$order = $collection->get_filters_order();
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
	
	public function hook_delete_when_metadata_deleted($filter, $permanent) {
		
		if ( $filter instanceof Entities\Metadatum ) {
			$metadatum_id = $filter->get_id();
			$filters = $this->fetch(['metadatum_id' => $metadatum_id, 'post_status' => 'any'], 'OBJECT');
			foreach ($filters as $filter) {
				if ($permanent) {
					$this->delete($filter);
				} else {
					$this->trash($filter);
				}
				
			}
		}
		
	}
	
}