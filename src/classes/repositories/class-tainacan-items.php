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
		$entity = $this->get_name();
		return apply_filters( "tainacan-get-map-$entity", [
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
				'description' => __( 'The current situation of the item. Notice that the item visibility also depends on the collection status.', 'tainacan' )
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
				'items'       => [ 'type' => ['string', 'integer']	],
			],
			'document_type'     => [
				'map'         => 'meta',
				'title'       => __( 'Document Type', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The document type, can be a local attachment, an external URL or a text', 'tainacan' ),
				'on_error'    => __( 'Invalid document type', 'tainacan' ),
				'enum'		  => [ 'attachment', 'url', 'text', 'empty' ],
				'validation'  => v::stringType()->in( [ 'attachment', 'url', 'text', 'empty' ] ),
				'default'     => 'empty'
			],
			'document'          => [
				'map'         => 'meta',
				'title'       => __( 'Document', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The item main content. May be a file attached, an URL or a text depending on the type of the document.', 'tainacan' ),
				'on_error'    => __( 'Invalid document', 'tainacan' ),
				'default'     => ''
			],
			'document_options'=> [
				'map'         => 'meta',
				'title'       => __( 'Document options', 'tainacan' ),
				'type'        => 'object',
				'description' => __( 'Object of options related to the document display.', 'tainacan' ),
				'on_error'    => __( 'Invalid document options', 'tainacan' ),
				'properties'  => array(
					'forced_iframe' => array(
						'description' => __( 'Render content in iframe', 'tainacan' ),
						'type'        => 'boolean',
						'context'     => array( 'view', 'edit', 'embed' ),
						'default'     => false
					),
					'is_image' => array(
						'title' => __( 'Is link to external image', 'tainacan' ),
						'description' => __( 'Is link to external image', 'tainacan' ),
						'type'        => 'boolean',
						'context'     => array( 'view', 'edit', 'embed' ),
						'default'     => false
					),
					'forced_iframe_height'      => array(
						'description' => __( 'Iframe height (px)', 'tainacan' ),
						'type'        => 'number',
						'context'     => array( 'view', 'edit', 'embed' ),
						'default'     => 450
					),
					'forced_iframe_width' => array(
						'description' => __( 'Iframe width (px)' , 'tainacan'),
						'type'        => 'number',
						'context'     => array( 'view', 'edit', 'embed' ),
						'default'     => 600
					),
				)
			],
			'_thumbnail_id'     => [
				'map'         => 'meta',
				'title'       => __( 'Thumbnail', 'tainacan' ),
				'description' => __( 'Squared reduced-size version of a picture that helps recognizing and organizing files', 'tainacan' ),
				'type'        => ['integer', 'string'],
			],
			'comment_status'  => [
				'map'         => 'comment_status',
				'title'       => __( 'Comment Status', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Item comment status: "open" means comments are allowed, "closed" means comments are not allowed.', 'tainacan' ),
				'default'     => get_default_comment_status(Entities\Collection::get_post_type()),
				'enum'        => [ 'open', 'closed' ],
				'validation' => v::optional(v::stringType()->in( [ 'open', 'closed' ] )),
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
		$Tainacan_Taxonomies  = \Tainacan\Repositories\Taxonomies::get_instance();

		// TODO: This can be a problem in large repositories.
		$collections = $Tainacan_Collections->fetch( ['nopaging' => true], 'OBJECT' );
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
		$Tainacan_Taxonomies->register_taxonomies_for_all_collections($collections);

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
					$item = new Entities\Item( $existing_post );
					$collection = $item->get_collection();
					if (isset($collection) && $collection->can_read()) {
						return $item;
					}
					return [];
				} catch (\Exception $e) {
					return [];
				}
			} else {
				return [];
			}

		}

		$no_collection_set = false;

		/**
		 * We cannot use $collections->fetch() here because facets
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

		if ( defined('TAINACAN_ENABLE_RELATIONSHIP_METAQUERY') && true === TAINACAN_ENABLE_RELATIONSHIP_METAQUERY ) {
			$args = $this->parse_relationship_metaquery($args);
		}

		if ( !defined('TAINACAN_DISABLE_CORE_METADATA_ON_ADVANCED_SEARCH') || false === TAINACAN_DISABLE_CORE_METADATA_ON_ADVANCED_SEARCH ) {
			$args = $this->parse_core_metadata_for_advanced_search($args, $collections_objects);
		}

		$args = apply_filters( 'tainacan-fetch-args', $args, 'items' );

		$should_filter = is_user_logged_in() && sizeof($cpt) > 1;

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
		$post_status = $wp_query->get( 'post_status' );
		$post_status = is_array($post_status) || empty($post_status) ? $post_status : explode(",", $post_status);

		foreach ($this->fetching_from_collections as $collection) {

			$read_private_cap = $collection->get_items_capabilities()->read_private_posts;

			$clause = '(';

				$clause .= "{$wpdb->posts}.post_type = '{$collection->get_db_identifier()}' AND (";
					$status_clause = [];

					// public status
					$public_states = get_post_stati( array( 'public' => true ) );
					foreach ( (array) $public_states as $state ) {
						if( empty($post_status) || in_array($state, $post_status) )
							$status_clause[] = "{$wpdb->posts}.post_status = '$state'";
					}

					// private statuses
					$private_states = get_post_stati( array( 'private' => true ) );
					foreach ( (array) $private_states as $state ) {
						if( empty($post_status) || in_array($state, $post_status) )
						$status_clause[] = current_user_can( $read_private_cap ) ? " {$wpdb->posts}.post_status = '$state'" : " {$wpdb->posts}.post_author = $user_id AND {$wpdb->posts}.post_status = '$state'";
					}

					//draft
					// $draft_states = get_post_stati( array( 'draft' => true ) );
					$draft_states = ['draft'];
					foreach ( $draft_states as $state ) {
						if( !empty($post_status) && in_array($state, $post_status) )
						$status_clause[] = current_user_can( $read_private_cap ) ? " {$wpdb->posts}.post_status = '$state'" : " {$wpdb->posts}.post_author = $user_id AND {$wpdb->posts}.post_status = '$state'";
					}
					$clause .= implode(' OR ', $status_clause);
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
	 * @param bool $comments_open
	 * @param integer $post_id Item id
	 * @return bool
	 */
	public function hook_comments_open($comments_open, $post_id) {
		$item = self::get_entity_by_post($post_id);

		if($item != false && $item instanceof Entities\Item) {
			$collection = $item->get_collection();
			if( $collection != null && $collection->get_allow_comments() !== 'open' ) return false;
		}

		return $comments_open;
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

	private function get_related_items_by_collection($item, $collection, $metadata, $args=[]) {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		if (!$collection instanceof \Tainacan\Entities\Collection || !$metadata instanceof \Tainacan\Entities\Metadatum || !$Tainacan_Metadata->metadata_is_enabled($collection, $metadata))
			return false;

		$prepared_items = array();
		$items = $this->fetch(array_merge([
			'meta_query' => [
				[
					'key'   => $metadata->get_id(),
					'value' => $item->get_id()
				]
			]
		], $args), $collection->get_id(), 'WP_Query');

		if ($items->have_posts()) {
			while ( $items->have_posts() ) {
				$items->the_post();
				$item_related = new \Tainacan\Entities\Item($items->post);
				$item_arr = $item_related->_toArray();
				$item_arr['thumbnail'] = $item_related->get_thumbnail();
				array_push($prepared_items, apply_filters( 'tainacan_add_related_item', $item_arr ) );
			}
			wp_reset_postdata();
		}

		return array(
			"found_posts" => $items->found_posts,
			'items' => $prepared_items
		);
	}

	public function fetch_related_items($item, $args=[]) {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$current_collection = $item->get_collection();
		$metadatas = $Tainacan_Metadata->fetch([
			'meta_query' => [
				[
					'key'   => 'metadata_type',
					'value' => 'Tainacan\Metadata_Types\Relationship'
				],
				[
					'key' => '_option_collection_id',
					'value' => $current_collection->get_id()
				],
				[
					'key' => '_option_display_in_related_items',
					'value' => 'yes'
				]
			]
		], 'OBJECT');
		
		$response = array();
		foreach($metadatas as $metadata) {
			if($metadata->get_collection_id() == $Tainacan_Metadata->get_default_metadata_attribute()) {
				$collections = $Tainacan_Collections->fetch([], 'OBJECT');
				foreach($collections as $collection) {
					$related_items = $this->get_related_items_by_collection($item, $collection, $metadata, $args);
					if($related_items == false) continue;
					$response[$metadata->get_id() . '_' . $collection->get_id()] = array(
						'collection_id' => $collection->get_id(),
						'collection_name' => $collection->get_name(),
						'collection_url' => $collection->get_url(),
						'collection_slug' => $collection->get_slug(),
						'metadata_id' => $metadata->get_id(),
						'metadata_name' => $metadata->get_name(),
						'total_items' => $related_items['found_posts'],
						'items' => $related_items['items']
					);
				}
			} else {
				$collection = $metadata->get_collection();
				$related_items = $this->get_related_items_by_collection($item, $collection, $metadata, $args);
				if($related_items == false) continue;
				$response[$metadata->get_id()] = array(
					'collection_id' => $collection->get_id(),
					'collection_name' => $collection->get_name(),
					'collection_url' => $collection->get_url(),
					'collection_slug' => $collection->get_slug(),
					'metadata_id' => $metadata->get_id(),
					'metadata_name' => $metadata->get_name(),
					'total_items' => $related_items['found_posts'],
					'items' => $related_items['items']
				);
			}
		}

		return $response;
	}

	private function parse_relationship_metaquery ($args) {
		if( isset($args['meta_query']) ) {
			$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
			foreach($args['meta_query'] as $idx => $meta) {
				$meta_id = $meta['key'];
				$metadata = $Tainacan_Metadata->fetch($meta_id);
				if(
					isset($metadata) &&
					$metadata instanceof \Tainacan\Entities\Metadatum &&
					$metadata->get_metadata_type() === 'Tainacan\\Metadata_Types\\Relationship' &&
					(isset($meta['compare']) && !in_array($meta['compare'], ['IN', 'NOT IN', '=']))
				) {
					$options  = $metadata->get_metadata_type_options();
					if( isset($options) && isset($options['search']) ) {
						$this->relationsip_metaquery = array(
							'meta_id' => $meta_id,
							'search_meta_id' => $options['search'],
							'search_meta_value' => $args['meta_query'][$idx]['value']
						);
						$args['meta_query'][$idx]['compare'] = '!=';
						$args['meta_query'][$idx]['value'] = '';
						add_filter( 'posts_where' , array($this, 'posts_where_relationship_metaquery'), 10, 1 );
						return $args;
					}
				}
			}
		}
		return $args;
	}

	function posts_where_relationship_metaquery( $where ) {
		$meta_id = $this->relationsip_metaquery['meta_id'];
		$search_meta_id = $this->relationsip_metaquery['search_meta_id'];
		$search_meta_value = $this->relationsip_metaquery['search_meta_value'];
		$SQL_related_item = " SELECT DISTINCT post_id FROM wp_postmeta WHERE meta_key=$search_meta_id AND meta_value LIKE '%$search_meta_value%'";
		$where .= " AND (wp_postmeta.meta_key = '$meta_id' AND wp_postmeta.meta_value IN ( $SQL_related_item ) ) ";
		remove_filter( 'posts_where', array($this, 'posts_where_relationship_metaquery') );
		return $where;
	}

	/**
	 * checks if there is `tainacan_core_title` or `tainacan_core_description` as a key for a meta_query,
	 * and replaces the ids of the metadata referring to `title_core` and `description_core`.
	 *
	 * @param array $args WP_Query args
	 * @param array $collections Array \Taainacan\Entities\Collection
	 *
	 * @return \WP_Query|Array;
	 */
	private function parse_core_metadata_for_advanced_search($args, $collections = [])
	{
		if (
			isset($args["meta_query"]) &&
			!empty($args["meta_query"]) &&
			is_array($args["meta_query"])
		) {
			$core_title_meta_query = [];
			$core_description_meta_query = [];

			foreach ($args["meta_query"] as $key => $meta_query) {
				// Finds a special key value, that should represent all core title or core description
				if (
					isset($meta_query["key"]) &&
					($meta_query["key"] === "tainacan_core_title" ||
					$meta_query["key"] === "tainacan_core_description")
				) {
					// Gets every collection to build the OR query
					if( empty($collections) ) {
						$collections = \Tainacan\Repositories\Collections::get_instance()->fetch(
							[],
							"OBJECT"
						);
					}

					foreach ($collections as $collection) {
						if ($meta_query["key"] === "tainacan_core_title") {
							$title_meta = $collection->get_core_title_metadatum();

							// Builds inner meta_queries for each collection, using the same settings of the special one
							$core_title_meta_query[] = [
								"key" => $title_meta->get_id(),
								"compare" => $meta_query["compare"],
								"value" => $meta_query["value"],
							];
						} elseif (
							$meta_query["key"] === "tainacan_core_description"
						) {
							$description_meta = $collection->get_core_description_metadatum();

							// Builds inner meta_queries for each collection, using the same settings of the special one
							$core_description_meta_query[] = [
								"key" => $description_meta->get_id(),
								"compare" => $meta_query["compare"],
								"value" => $meta_query["value"],
							];
						}
					}
					unset($args["meta_query"][$key]);
				}
			}
			if (count($core_title_meta_query)) {
				$core_title_meta_query["relation"] = "OR";
				$args["meta_query"][] = $core_title_meta_query;
			}
			if (count($core_description_meta_query)) {
				$core_description_meta_query["relation"] = "OR";
				$args["meta_query"][] = $core_description_meta_query;
			}
		}
		return $args;
	}

}
