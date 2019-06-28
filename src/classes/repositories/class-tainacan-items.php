<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;
use \Respect\Validation\Validator as v;
use Tainacan\Entities\Item;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Items extends Repository {
	public $entities_type = '\Tainacan\Entities\Item';

	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		parent::__construct();
		add_filter( 'posts_where', array( &$this, 'title_in_posts_where' ), 10, 2 );
		add_filter( 'posts_where', array( &$this, 'content_in_posts_where' ), 10, 2 );
		add_filter( 'comments_open', [$this, 'hook_comments_open'], 10, 2);
		add_action( 'tainacan-api-item-updated', array( &$this, 'hook_api_updated_item' ), 10, 2 );
	}

	public function get_map() {
		return apply_filters( 'tainacan-get-map-' . $this->get_name(), [
			'title'             => [
				'map'         => 'post_title',
				'title'       => __( 'Title', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Title of the item', 'tainacan' ),
				'on_error'    => __( 'The title should be a text value and not empty', 'tainacan' ),
				//'validation'  => v::stringType()->notEmpty(),
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
			'terms'             => [
				'map'         => 'terms',
				'title'       => __( 'Term IDs', 'tainacan' ),
				'type'        => 'array',
				'description' => __( 'The item term IDs', 'tainacan' ),
			],
			'document_type'     => [
				'map'         => 'meta',
				'title'       => __( 'Document Type', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The document type, can be a local attachment, an external URL or a text', 'tainacan' ),
				'on_error'    => __( 'Invalid document type', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'attachment', 'url', 'text', 'empty' ] ),
				'default'     => 'empty'
			],
			'document'          => [
				'map'         => 'meta',
				'title'       => __( 'Document', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The document itself. An ID in case of attachment, an URL in case of link or a text in the case of text.', 'tainacan' ),
				'on_error'    => __( 'Invalid document', 'tainacan' ),
				'default'     => ''
			],
			'_thumbnail_id'     => [
				'map'         => 'meta',
				'title'       => __( 'Thumbnail', 'tainacan' ),
				'description' => __( 'Squared reduced-size version of a picture that helps recognizing and organizing files', 'tainacan' )
			],
		    'comment_status'  => [
		        'map'         => 'comment_status',
		        'title'       => __( 'Comment Status', 'tainacan' ),
		        'type'        => 'string',
		        'description' => __( 'Item comment status: "open" means comments are allowed, "closed" means comments are not allowed.', 'tainacan' ),
		        'default'     => get_default_comment_status(Entities\Collection::get_post_type()),
		        'validation' => v::optional(v::stringType()->in( [ 'open', 'closed' ] )),
		    ]
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
		$Tainacan_Taxonomies  = \Tainacan\Repositories\Taxonomies::get_instance();

		$collections = $Tainacan_Collections->fetch( [], 'OBJECT' );
		$taxonomies  = $Tainacan_Taxonomies->fetch( [
			'status' => [
				'auto-draft',
				'draft',
				'publish',
				'private'
			]
		], 'OBJECT' );

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
				$taxonomy->tainacan_register_taxonomy();
			}
		}
		
		// register taxonomies to collections considering metadata inheritance
		$Tainacan_Taxonomies->register_taxonomies_for_all_collections();
		
	}

	public function insert( $item ) {
		return parent::insert( $item );
	}

	/**
	 * fetch items based on ID or WP_Query args
	 *
	 * Items are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 * 
	 * If a number is passed to $args, it will return a \Tainacan\Entities\Item object.  But if the post is not found or
	 * does not match the entity post type, it will return an empty array
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
				try {
					return new Entities\Item( $existing_post );
				} catch (\Exception $e) {
					return [];
				}
			} else {
				return [];
			}

		}

		if ( empty( $collections ) ) {
			$post_types = get_post_types();
			$collections = array_map( function($el) use ($Tainacan_Collections) {
				if ( $id = $Tainacan_Collections->get_id_by_db_identifier($el) ) {
					return $id;
				}
			} , $post_types);
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
		
		// In progress...
		// if (isset($args['meta_query'])) {
		// 	$args = $this->transform_meta_query_to_tax_query($args);
		// }
		
		$args = apply_filters( 'tainacan_fetch_args', $args, 'items' );

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
	 * allow wp query filter post by array of titles
	 *
	 * @param $where
	 * @param $wp_query
	 *
	 * @return string
	 */
	public function title_in_posts_where( $where, $wp_query ) {
		global $wpdb;
		if ( $post_title_in = $wp_query->get( 'post_title_in' ) ) {
			if ( is_array( $post_title_in ) && isset( $post_title_in['value'] ) ) {
				$quotes = [];
				foreach ( $post_title_in['value'] as $title ) {
					$quotes[] = " $wpdb->posts.post_title  LIKE  '%" . esc_sql( $wpdb->esc_like( $title ) ) . "%'";
				}
			}

			// retrieve only posts for the specified collection and status
			$type   = " $wpdb->posts.post_type = '" . $wp_query->get( 'post_type' )[0] . "' ";
			$status = " ( $wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private') ";
			$where  .= ' ' . $post_title_in['relation'] . '( ( ' . implode( ' OR ', $quotes ) . ' ) AND ' .
			           $status . ' AND  ' . $type . ' )';
		}

		return $where;
	}

	/**
	 * allow wp query filter post by array of content
	 *
	 * @param $where
	 * @param $wp_query
	 *
	 * @return string
	 */
	public function content_in_posts_where( $where, $wp_query ) {
		global $wpdb;
		if ( $post_content_in = $wp_query->get( 'post_content_in' ) ) {
			if ( is_array( $post_content_in ) && isset( $post_content_in['value'] ) ) {
				$quotes = [];
				foreach ( $post_content_in['value'] as $title ) {
					$quotes[] = " $wpdb->posts.post_content  LIKE  '%" . esc_sql( $wpdb->esc_like( $title ) ) . "%'";
				}
			}

			// retrieve only posts for the specified collection and status
			$type   = " $wpdb->posts.post_type = '" . $wp_query->get( 'post_type' )[0] . "' ";
			$status = " ( $wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private') ";
			$where  .= ' ' . $post_content_in['relation'] . '( ( ' . implode( ' OR ', $quotes ) . ' ) AND ' .
			           $status . ' AND  ' . $type . ' )';
		}

		return $where;
	}
	
	public function transform_meta_query_to_tax_query($args) {
		
		if (!isset($args['meta_query']) || !is_array($args['meta_query'])) {
			return $args;
		}
		
		$metas = [];
		
		foreach ($args['meta_query'] as $i => $meta_q) {
			if (is_array($meta_q) && isset($meta_q['key']) && is_numeric($meta_q['key'])) {
				$metas[$meta_q['key']] = $i;
			}
		}
		
		$metadata = Metadata::get_instance()->fetch([
			'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
			'post__in' => array_keys($metas)
		], 'OBJECT');
		
		if (empty($metadata)) {
			return $args;
		}
		
		if (!isset($args['tax_query'])) {
			$args['tax_query'] = [];
		}
		
		foreach ($metadata as $metadatum) {
			if ( isset($metas[$metadatum->get_id()]) ) {
				$index = $metas[$metadatum->get_id()];
				$metaquery = $args['meta_query'][$index];
				$options = $metadatum->get_metadata_type_options();
				$tax_id = $options['taxonomy_id'];
				$tax_slug = Taxonomies::get_instance()->get_db_identifier_by_id($tax_id);
				
				if (!isset($metaquery['compare']) || $metaquery['compare'] == '=' || $metaquery['compare'] == 'IN' ) {
					
					$args['tax_query'][] = [
						'taxonomy' => $tax_slug,
						'field' => 'name',
						'terms' => $terms,
					];
					
				} elseif ( strtoupper($metaquery['compare']) == 'LIKE') {
					$search['search'] = $metaquery['value'];
					
					$terms = get_terms([
						'taxonomy' => $tax_slug,
						'fields' => 'ids',
						'search' => $metaquery['value']
					]);
					
					$args['tax_query'][] = [
						'taxonomy' => $tax_slug,
						'terms' => $terms,
					];
					
				} else {
					continue;
				}
				
				unset( $args['meta_query'][$index] );
				
			}
		}
		
		return $args;
		
	}

	/**
	 * Get a default thumbnail ID from the item document.
	 *
	 * @param  Entities\Item $item The item
	 *
	 * @return int|null           The thumbnail ID or null if it was not possible to find a thumbnail
	 */
	public function get_thumbnail_id_from_document( Entities\Item $item ) {
		/**
		 * Hook to get thumbnail from document
		 */
		$thumb_id = apply_filters( 'tainacan-get-thumbnail-id-from-document', null, $item );

		if ( ! is_null( $thumb_id ) ) {
			return $thumb_id;
		}

		if ( empty( $item->get_document() ) ) {
			return null;
		}

		if ( $item->get_document_type() == 'attachment' ) {
			if ( wp_attachment_is_image( $item->get_document() ) ) {
				return $item->get_document();
			} else {

				$filepath      = get_attached_file( $item->get_document() );
				$TainacanMedia = \Tainacan\Media::get_instance();
				$thumb_blob    = $TainacanMedia->get_pdf_cover( $filepath );
				if ( $thumb_blob ) {
					$thumb_id = $TainacanMedia->insert_attachment_from_blob( $thumb_blob, basename( $filepath ) . '-cover.jpg' );

					return $thumb_id;
				}

			}
		} elseif ( $item->get_document_type() == 'url' ) {

			$TainacanEmbed = \Tainacan\Embed::get_instance();
			if ( $thumb_url = $TainacanEmbed->oembed_get_thumbnail( $item->get_document() ) ) {
				$meta_key = '_' . $thumb_url . '__thumb';

				$existing_thumb = get_post_meta( $item->get_id(), $meta_key, true );

				if ( is_numeric( $existing_thumb ) ) {
					return $existing_thumb;
				} else {
					$TainacanMedia = \Tainacan\Media::get_instance();
					$thumb_id      = $TainacanMedia->insert_attachment_from_url( $thumb_url );
					update_post_meta( $item->get_id(), $meta_key, $thumb_id );

					return $thumb_id;
				}
			}

		}

		return $thumb_id;
	}

	/**
	 * When updating an item document, set a default thumbnail to the item if it does not have one yet
	 *
	 * @param  Entities\Item $updated_item
	 * @param  array $attributes The paramaters sent to the API
	 *
	 * @return void
	 */
	public function hook_api_updated_item( Entities\Item $updated_item, $attributes ) {
		if ( array_key_exists( 'document', $attributes )
		     && empty( $updated_item->get__thumbnail_id() )
		     && ! empty( $updated_item->get_document() )
		) {

			$thumb_id = $this->get_thumbnail_id_from_document( $updated_item );

			if ( ! is_null( $thumb_id ) ) {
				set_post_thumbnail( $updated_item->get_id(), (int) $thumb_id );
			}

		}

	}

	/**
	 * Return if comment are open for this item (post_id) and the collection too 
	 * 
	 * @param bool $open_comment
	 * @param integer $post_id Item id
	 * @return bool
	 */
	public function hook_comments_open($open_comment, $post_id) {
	    $item = self::get_entity_by_post($post_id);
	    
	    if($item != false && $item instanceof Entities\Item) {
    	    $collection = $item->get_collection();
    	    if( $collection->get_allow_comments() !== 'open' ) return false;
	    }
	    
	    return $open_comment;
	}

}