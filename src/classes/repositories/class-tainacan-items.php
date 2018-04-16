<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;
use \Respect\Validation\Validator as v;
use Tainacan\Entities\Item;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Items extends Repository {
	public $entities_type = '\Tainacan\Entities\Item';

    private static $instance = null;

    public static function get_instance()
    {
        if(!isset(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        parent::__construct();
        add_filter( 'posts_where', array(&$this, 'title_in_posts_where'), 10, 2 );
        add_filter( 'posts_where', array(&$this, 'content_in_posts_where'), 10, 2 );
    }

    public function get_map() {
		return apply_filters( 'tainacan-get-map-' . $this->get_name(), [
			'title'             => [
				'map'         => 'post_title',
				'title'       => __( 'Title', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Title of the item', 'tainacan' ),
				'on_error'    => __( 'The title should be a text value and not empty', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'status'            => [
				'map'         => 'post_status',
				'title'       => __( 'Status', 'tainacan' ),
				'type'        => 'string',
				'default'     => 'draft',
				'description' => __( 'The posts status', 'tainacan' )
			],
			'description'       => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The item description', 'tainacan' ),
				'default'     => '',
				'validation'  => ''
			],
			'collection_id'     => [
				'map'         => 'meta',
				'title'       => __( 'Collection', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'The collection ID', 'tainacan' ),
				'validation'  => ''
			],
			'author_id'         => [
				'map'         => 'post_author',
				'title'       => __( 'Author', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The item author\'s user ID (numeric string)', 'tainacan' )
			],
			'creation_date'     => [
				'map'         => 'post_date',
				'title'       => __( 'Creation Date', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The item creation date', 'tainacan' )
			],
			'modification_date' => [
				'map'         => 'post_modified',
				'title'       => __( 'Modification Date', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The item modification date', 'tainacan' )
			],
			'url'               => [
				'map'         => 'guid',
				'title'       => __( 'Item URL', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The item URL', 'tainacan' )
			],
			'terms'             => [
				'map'         => 'terms',
				'title'       => __( 'Term IDs', 'tainacan' ),
				'type'        => 'array',
				'description' => __( 'The item term IDs', 'tainacan' ),
			],
			'document_type'       => [
                'map'        => 'meta',
                'title'      => __('Document Type', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The document type, can be a local attachment, an external URL or a text', 'tainacan'),
                'on_error'   => __('Invalid document type', 'tainacan'),
                'validation' => v::stringType()->in(['attachment', 'url', 'text']),
                'default'    => 'attachment'
            ],
			'document'       => [
                'map'        => 'meta',
                'title'      => __('Document', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The document itself. An ID in case of attachment, an URL in case of url or a text in the case of text', 'tainacan'),
                'on_error'   => __('Invalid document', 'tainacan'),
                'default'    => ''
            ],
		] );
	}

	/**
	 * Get generic labels for the custom post types created for each collection
	 *
	 * @see \Tainacan\Entities\Collection::register_collection_item_post_type()
	 *
	 * @return array Labels in the format expected by register_post_type()
	 */
	public function get_cpt_labels() {
		return array(
			'name'               => __( 'Items', 'tainacan' ),
			'singular_name'      => __( 'Item', 'tainacan' ),
			'add_new'            => __( 'Add new', 'tainacan' ),
			'add_new_item'       => __( 'Add new', 'tainacan' ),
			'edit_item'          => __( 'Edit Item', 'tainacan' ),
			'new_item'           => __( 'New Item', 'tainacan' ),
			'view_item'          => __( 'View Item', 'tainacan' ),
			'search_items'       => __( 'Search items', 'tainacan' ),
			'not_found'          => __( 'No items found', 'tainacan' ),
			'not_found_in_trash' => __( 'No items found in trash', 'tainacan' ),
			'parent_item_colon'  => __( 'Parent item:', 'tainacan' ),
		);
	}

	/**
	 * Register each Item post_type
	 * {@inheritDoc}
	 *
	 * @see \Tainacan\Repositories\Repository::register_post_type()
	 */
	public function register_post_type() {

        $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

		$collections = $Tainacan_Collections->fetch( [], 'OBJECT' );
		$taxonomies  = $Tainacan_Taxonomies->fetch( ['status' => ['auto-draft', 'draft', 'publish', 'private']], 'OBJECT' );

		if ( ! is_array( $collections ) ) {
			return;
		}

		// register collections post type and associate taxonomies
		foreach ( $collections as $collection ) {
			$collection->register_collection_item_post_type();
		}

		// register taxonomies
		if ( is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
			foreach ( $taxonomies as $taxonomy ) {
				$taxonomy->register_taxonomy();
			}
		}
	}

	public function insert( $item ) {

		$old = $item;
		$is_update = false;
		// TODO get props obj before update
		if( $item->get_id() ) {
			$is_update = true;
			$old = $item->get_repository()->fetch( $item->get_id() );
		}

		$map = $this->get_map();

		// get collection to determine post type
		$collection = $item->get_collection();

		if ( ! $collection ) {
			return false;
		}

		$cpt = $collection->get_db_identifier();

		// iterate through the native post properties
		foreach ( $map as $prop => $mapped ) {
			if ( $mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi' && $mapped['map'] != 'terms' ) {
				$item->WP_Post->{$mapped['map']} = $item->get_mapped_property( $prop );
			}
		}

		// save post and get its ID
		$item->WP_Post->post_type = $cpt;
		//$item->WP_Post->post_status = 'publish';

		$id            = wp_insert_post( $item->WP_Post );
		$item->WP_Post = get_post( $id );

		// Now run through properties stored as postmeta
		foreach ( $map as $prop => $mapped ) {
			if ( $mapped['map'] == 'meta' ) {
				update_post_meta( $id, $prop, wp_slash( $item->get_mapped_property( $prop ) ) );
			} elseif ( $mapped['map'] == 'meta_multi' ) {
				$values = $item->get_mapped_property( $prop );

				delete_post_meta( $id, $prop );

				if ( is_array( $values ) ) {
					foreach ( $values as $value ) {
						add_post_meta( $id, $prop, wp_slash( $value ) );
					}
				}
			}
		}

		if ( method_exists( $item, 'get_featured_img_id' ) ) {
			set_post_thumbnail( $item->WP_Post, $item->get_featured_img_id( $item->WP_Post->ID ) );
		}

		do_action( 'tainacan-insert', $item, $old, $is_update );
		do_action( 'tainacan-insert-Item', $item );

		// return a brand new object
		return new Entities\Item( $item->WP_Post );
	}

	/**
	 * fetch items based on ID or WP_Query args
	 *
	 * Items are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 *
	 * The second paramater specifies from which collections item should be fetched.
	 * You can pass the Collection ID or object, or an Array of IDs or collection objects
	 *
	 * @param array $args WP_Query args || int $args the item id
	 * @param array $collections Array Entities\Collection || Array int collections IDs || int collection id || Entities\Collection collection object
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return \WP_Query|Array|Item an instance of wp query OR array of entities OR a Item;
	 */
	public function fetch( $args = [], $collections = [], $output = null ) {

		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();

		if ( is_numeric( $args ) ) {
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				return new Entities\Item( $existing_post );
			} else {
				return [];
			}

		}

		if ( empty( $collections ) ) {
			$collections = $Tainacan_Collections->fetch( [], 'OBJECT' );
		}

		if ( is_numeric( $collections ) ) {
			$collections = $Tainacan_Collections->fetch( $collections );
		}

		$collections_objects = [];
		$cpt                 = [];

		if ( $collections instanceof Entities\Collection ) {
			$collections_objects[] = $collections;
		} elseif ( is_array( $collections ) ) {
			foreach ( $collections as $col ) {
				if ( is_numeric( $col ) ) {
					$col = $Tainacan_Collections->fetch( $col );
				}
				if ( $col instanceof Entities\Collection ) {
					$collections_objects[] = $col;
				}
			}

		}
		foreach ( $collections_objects as $collection ) {

			/**
			 * If no specific status is defined in the query, WordPress will fetch
			 * public items and private items for users withe the correct permission.
			 *
			 * If a collection is private, it must have the same behavior, despite its
			 * items are public or not.
			 */
			if ( ! isset( $args['post_status'] ) ) {
				$status_obj = get_post_status_object( $collection->get_status() );
				if ( $status_obj->public || current_user_can( $collection->cap->read_private_posts ) ) {
					$cpt[] = $collection->get_db_identifier();
				}
			} else {
				$cpt[] = $collection->get_db_identifier();
			}

		}


		if ( empty( $cpt ) ) {
			$cpt[] = 'please-return-nothing';
		}

		//TODO: get collection order and order by options

		$args = $this->parse_fetch_args( $args );

		$args['post_type'] = $cpt;

		$wp_query = new \WP_Query( $args );

		return $this->fetch_output( $wp_query, $output );
	}
	
	/**
	 * fetch items IDs based on WP_Query args
	 *
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 *
	 * The second paramater specifies from which collections item should be fetched.
	 * You can pass the Collection ID or object, or an Array of IDs or collection objects
	 *
	 * @param array $args WP_Query args || int $args the item id
	 * @param array $collections Array Entities\Collection || Array int collections IDs || int collection id || Entities\Collection collection object
	 *
	 * @return Array array of IDs;
	 */
	public function fetch_ids( $args = [], $collections = [] ) {
		
		$args['fields'] = 'ids';
		
		return $this->fetch( $args, $collections )->get_posts();
	}

	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	/**
	 * @param $args ( is a array like [post_id, [is_permanently => bool]] )
	 *
	 * @return mixed|Entities\Item
	 */
	public function delete( $args ) {
		if ( ! empty( $args[1] ) && $args[1] === true ) {
			return new Entities\Item( wp_delete_post( $args[0], $args[1] ) );
		}

		return new Entities\Item( wp_trash_post( $args[0] ) );
	}

    /**
     * allow wp query filter post by array of titles
     *
     * @param $where
     * @param $wp_query
     * @return string
     */
    public function title_in_posts_where( $where, $wp_query ) {
        global $wpdb;
        if ( $post_title_in = $wp_query->get( 'post_title_in' ) ) {
            if(is_array( $post_title_in ) && isset( $post_title_in['value']) ){
                $quotes = [];
                foreach ($post_title_in['value'] as $title) {
                    $quotes[] = " $wpdb->posts.post_title  LIKE  '%" .   esc_sql( $wpdb->esc_like( $title ) ). "%'";
                }
            }

            // retrieve only posts for the specified collection and status
            $type = " $wpdb->posts.post_type = '" . $wp_query->get( 'post_type' )[0]."' ";
            $status = " ( $wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private') ";
            $where .= ' '.$post_title_in['relation'] . '( ( ' .implode(' OR ', $quotes ) . ' ) AND ' .
                $status . ' AND  ' . $type . ' )';
        }
        return $where;
    }

    /**
     * allow wp query filter post by array of content
     *
     * @param $where
     * @param $wp_query
     * @return string
     */
    public function content_in_posts_where( $where, $wp_query ) {
        global $wpdb;
        if ( $post_content_in = $wp_query->get( 'post_content_in' ) ) {
            if(is_array( $post_content_in ) && isset( $post_content_in['value']) ){
                $quotes = [];
                foreach ($post_content_in['value'] as $title) {
                    $quotes[] = " $wpdb->posts.post_content  LIKE  '%" .esc_sql( $wpdb->esc_like( $title ) ). "%'";
                }
            }

            // retrieve only posts for the specified collection and status
            $type = " $wpdb->posts.post_type = '" . $wp_query->get( 'post_type' )[0]."' ";
            $status = " ( $wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private') ";
            $where .= ' '.$post_content_in['relation'] . '( ( ' .implode(' OR ', $quotes ) . ' ) AND ' .
                $status . ' AND  ' . $type . ' )';
        }
        return $where;
    }
}