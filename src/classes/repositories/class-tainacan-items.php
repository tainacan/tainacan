<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;
use \Respect\Validation\Validator as v;
use Tainacan\Entities\Item;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Items extends Repository {
	public $entities_type = '\Tainacan\Entities\Item';

	private static $instance = null;

	// temporary variable used to filter items query
	private $fetching_from_collections = [];

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		parent::__construct();
		add_filter( 'comments_open', [$this, 'hook_comments_open'], 10, 2);
		add_action( 'tainacan-api-item-updated', array( &$this, 'hook_api_updated_item' ), 10, 2 );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
	}

	protected function _get_map() {
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

		$no_collection_set = false;

		/**
		 * We can not user $collections->fetch() here because facets
		 * filter wp_query to just return the query and not the results
		 * See Repositories\Metadata::fetch_all_metadatum_values()
		 *
		 * Conceptually, it's a good idea that a fetch() method like this only
		 * produces one WP_Query request
		 */
		if ( empty( $collections ) ) {
			$no_collection_set = true;
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
			 * If no specific collection was queried, we will fetch
			 * mimick WordPress behavior and return only collections current user can read
			 */
			if ( $no_collection_set) {
				$status_obj = get_post_status_object( $collection->get_status() );
				if ( $status_obj->public || current_user_can( $collection->cap->read_private_posts ) ) {

					$cpt[] = $collection->get_db_identifier();
				}
			} else {
				$cpt[] = $collection->get_db_identifier();
			}

		}

		$this->fetching_from_collections = $collections_objects;

		if ( empty( $cpt ) ) {
			$cpt[] = 'please-return-nothing';
		}

		$args = $this->parse_fetch_args( $args );

		$args['post_type'] = $cpt;

		// If no orderby was passed, or if only one orderby parameter is passed
		// we add a second criteria to order by ID and make sure items are always returned in the same order
		// See #337
		if ( ! isset($args['orderby']) ) {
			$args['orderby'] = 'post_date';
		}
		if ( ! isset($args['order']) ) {
			$args['order'] = 'DESC';
		}
		if ( is_string( $args['orderby'] ) ) {
			$new_order = [
				$args['orderby'] => $args['order'],
				'ID' => 'DESC'
			];
			$args['orderby'] = $new_order;
		}

		$args = apply_filters( 'tainacan_fetch_args', $args, 'items' );

		$should_filter = is_user_logged_in() && ! isset($args['post_status']) && sizeof($cpt) > 1;

		if ( $should_filter ) {
			add_filter('posts_where', [$this, '_filter_where'], 10, 2);
		}

		$wp_query = new \WP_Query( $args );

		if ( $should_filter ) {
			remove_filter('posts_where', [$this, '_filter_where']);
		}

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

	/**
	 * When querying posts without explictly asking for a post_status, WordPress will
	 * check current user capabilities and return posts user can read based on read_private_posts capabilities.
	 *
	 * However, when querying for multiple post types, WordPress does not handle a per post type permission check. It either
	 * return only public posts or all private posts if read_private_multiple_post_types cap is present.
	 *
	 * This hook fixes this, modifying the where clause.
	 *
	 * @param string $where the wehere clause
	 * @param \WP_Query $wp_query
	 * @return string The modified where clause
	 */
	public function _filter_where($where, $wp_query) {
		global $wpdb;
		$clauses = [];
		$user_id = get_current_user_id();

		foreach ($this->fetching_from_collections as $collection) {

			$read_private_cap = $collection->get_items_capabilities()->read_private_posts;

			$clause = '(';

				$clause .= "{$wpdb->posts}.post_type = '{$collection->get_db_identifier()}' AND (";

					// public status
					$public_states = get_post_stati( array( 'public' => true ) );
					$status_clause = [];
					foreach ( (array) $public_states as $state ) {
						$status_clause[] = "{$wpdb->posts}.post_status = '$state'";
					}
					$clause .= implode(' OR ', $status_clause);

					// private statuses
					$private_states = get_post_stati( array( 'private' => true ) );
					foreach ( (array) $private_states as $state ) {
						$clause .= current_user_can( $read_private_cap ) ? " OR {$wpdb->posts}.post_status = '$state'" : " OR {$wpdb->posts}.post_author = $user_id AND {$wpdb->posts}.post_status = '$state'";
					}

				$clause .= ')';


			$clause .= ')';

			$clauses[] = $clause;

		}

		$final = '(' . implode(' OR ', $clauses) . ')';

		// find post_type and post_status queries. They always come one right after another
		$regexp = '/(' . $wpdb->posts . '\.post_type.+AND \(' . $wpdb->posts . '\.post_status.+\))/';

		return \preg_replace($regexp, $final, $where);

	}

	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	/**
	 * generate a content of document to index.
	 *
	 * @param  Entities\Item $item The item
	 *
	 * @return boolean
	 */
	public function generate_index_content(Entities\Item $item) {
		$TainacanMedia = \Tainacan\Media::get_instance();
		if ( empty( $item->get_document() ) ) {
			$TainacanMedia->index_pdf_content( null, $item->get_ID() );
		} elseif ( $item->get_document_type() == 'attachment' ) {
			if (! wp_attachment_is_image( $item->get_document() ) ) {
				$filepath = get_attached_file( $item->get_document() );
				$TainacanMedia->index_pdf_content( $filepath, $item->get_ID() );
			}
		}
		return true;
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
        $this->generate_index_content( $updated_item );
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

	/**
	 * Filter to handle special permissions
	 *
	 * @see https://developer.wordpress.org/reference/hooks/map_meta_cap/
	 *
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {

		// Even if the item is public, user must have read_private_posts if the collection is private
		if ( $cap == 'read_post' && is_array( $args ) && array_key_exists( 0, $args ) ) {

			$entity = $args[0];

			if ( is_numeric( $entity ) || $entity instanceof Entities\Item ) {

				if ( is_numeric( $entity ) ) {
					$entity = $this->fetch( (int) $entity );
				}

				if ( $entity instanceof Entities\Item ) {

					$collection = $entity->get_collection();

					if ( $collection instanceof Entities\Collection ) {
						$status_obj = get_post_status_object( $collection->get_status() );
						if ( ! $status_obj->public ) {
							$caps[] = $collection->get_capabilities()->read_private_posts;
						}
					}


				}
			}
		}

		return $caps;
	}


}
