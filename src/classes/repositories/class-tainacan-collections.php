<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;
use Tainacan\Entities\Collection;

class Collections extends Repository {
	public $entities_type = '\Tainacan\Entities\Collection';

	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Collections constructor.
	 */
	protected function __construct() {
		parent::__construct();
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
	}

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Repository::get_map()
	 */
	public function get_map() {
		return apply_filters( 'tainacan-get-map-' . $this->get_name(), [
			'name'                       => [
				'map'         => 'post_title',
				'title'       => __( 'Name', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The title of the collection', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'status'                     => [
				'map'         => 'post_status',
				'title'       => __( 'Status', 'tainacan' ),
				'type'        => 'string',
				'default'     => '',
				'description' => __( 'The current situation of the post', 'tainacan' )
			],
			'author_id'                  => [
				'map'         => 'post_author',
				'title'       => __( 'Author ID', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The collection author\'s user ID (numeric string)', 'tainacan' )
			],
			'creation_date'              => [
				'map'         => 'post_date',
				'title'       => __( 'Creation Date', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The collection creation date', 'tainacan' )
			],
			'modification_date'          => [
				'map'         => 'post_modified',
				'title'       => __( 'Modification Date', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The collection modification date', 'tainacan' )
			],
			'order'                      => [
				'map'         => 'order',
				'title'       => __( 'Order', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Collection order. This metadata is used if collections are manually ordered.', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'parent'                     => [
				'map'         => 'post_parent',
				'title'       => __( 'Parent Collection', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'Original collection from which features are inherited', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'description'                => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'An introductory text describing the collection', 'tainacan' ),
				'default'     => '',
				//'validation' => v::stringType(),
			],
			'slug'                       => [
				'map'         => 'post_name',
				'title'       => __( 'Slug', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'An unique and sanitized string representation of the collection, used to build the collection URL. It must not contain any special characters or spaces.', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'default_orderby'            => [
				'map'         => 'meta',
				'title'       => __( 'Default Order metadata', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Default property items in this collections will be ordered by', 'tainacan' ),
				'default'     => 'name',
				//'validation' => v::stringType(),
			],
			'default_order'              => [
				'map'         => 'meta',
				'title'       => __( 'Default order', 'tainacan' ),
				'description' => __( 'Default order for items in this collection. ASC or DESC', 'tainacan' ),
				'type'        => 'string',
				'default'     => 'ASC',
				'validation'  => v::stringType()->in( [ 'ASC', 'DESC' ] ),
			],
			'default_displayed_metadata' => [
				'map'         => 'meta',
				'title'       => __( 'Default Displayed Metadata', 'tainacan' ),
				'type'        => 'array/object/string',
				'items'       => [ 'type' => 'array/string/integer/object' ],
				'default'     => [],
				'description' => __( 'List of collection properties that will be displayed in the table view', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'default_view_mode'          => [
				'map'         => 'meta',
				'title'       => __( 'Default view mode', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Collection default visualization mode', 'tainacan' ),
				'default'     => 'table',
				//'validation' => v::stringType(),
			],
			'enabled_view_modes'         => [
				'map'         => 'meta',
				'title'       => __( 'Enabled view modes', 'tainacan' ),
				'type'        => 'array',
				'description' => __( 'Which visualization modes will be available for the public to choose from', 'tainacan' ),
				'default'     => [ 'table', 'cards' ],
				'items'       => [ 'type' => 'string' ],
				//'validation' => v::stringType(),
			],
			'metadata_order'             => [
				'map'         => 'meta',
				'title'       => __( 'Ordination metadata', 'tainacan' ),
				'type'        => 'array/object/string',
				'items'       => [ 'type' => 'array/string/integer/object' ],
				'description' => __( 'Collection metadata ordination', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'filters_order'              => [
				'map'         => 'meta',
				'title'       => __( 'Ordination filters', 'tainacan' ),
				'type'        => 'array/object/string',
				'items'       => [ 'type' => 'array/string/integer/object' ],
				'description' => __( 'Collection filters ordination', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'enable_cover_page'          => [
				'map'         => 'meta',
				'title'       => __( 'Enable Cover Page', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'To use this page as the home page of this collection', 'tainacan' ),
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'default'     => 'no'
			],
			'cover_page_id'              => [
				'map'         => 'meta',
				'title'       => __( 'Cover Page ID', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, this custom page will be used as cover for this collection, instead of default items list.', 'tainacan' ),
				'on_error'    => __( 'Invalid page', 'tainacan' ),
				//'validation' => v::numeric(),
				'default'     => ''
			],
			'header_image_id'            => [
				'map'         => 'meta',
				'title'       => __( 'Header Image', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The image to be used in collection header', 'tainacan' ),
				'on_error'    => __( 'Invalid image', 'tainacan' ),
				//'validation' => v::numeric(),
				'default'     => ''
			],
			'moderators_ids'             => [
				'map'         => 'meta_multi',
				'title'       => __( 'Moderators', 'tainacan' ),
				'type'        => 'array/object/string',
				'items'       => [ 'type' => 'array/string/integer/object' ],
				'description' => __( 'To assign users as Moderators of this collection', 'tainacan' ),
				'validation'  => ''
			],
			'_thumbnail_id'              => [
				'map'         => 'meta',
				'title'       => __( 'Thumbnail', 'tainacan' ),
				'description' => __( 'Squared reduced-size version of a picture that helps recognizing and organizing files', 'tainacan' )
			],
		    'comment_status'  => [
	            'map'         => 'comment_status',
	            'title'       => __( 'Comment Status', 'tainacan' ),
	            'type'        => 'string',
	            'description' => __( 'Collection comment status: "open" means comments are allowed, "closed" means comments are not allowed.', 'tainacan' ),
		        'default'     => get_default_comment_status(Entities\Collection::get_post_type()),
		        'validation' => v::optional(v::stringType()->in( [ 'open', 'closed' ] )),
		    ],
		    'allow_comments'  => [
		        'map'         => 'meta',
		        'title'       => __( 'Allow Items Comments', 'tainacan' ),
		        'type'        => 'string',
		        'description' => __( 'Collection items comment status: "open" means comments are allowed, "closed" means comments are not allowed.', 'tainacan' ),
		        'default'     => 'open',
		        'validation' => v::optional(v::stringType()->in( [ 'open', 'closed' ] )),
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
			'name'               => __( 'Collections', 'tainacan' ),
			'singular_name'      => __( 'Collection', 'tainacan' ),
			'add_new'            => __( 'Add new', 'tainacan' ),
			'add_new_item'       => __( 'Add new Collection', 'tainacan' ),
			'edit_item'          => __( 'Edit Collection', 'tainacan' ),
			'new_item'           => __( 'New Collection', 'tainacan' ),
			'view_item'          => __( 'View Collection', 'tainacan' ),
			'view_items'         => __( 'View Collections', 'tainacan' ),
			'search_items'       => __( 'Search Collections', 'tainacan' ),
			'not_found'          => __( 'No Collections found ', 'tainacan' ),
			'not_found_in_trash' => __( 'No Collections found in trash', 'tainacan' ),
			'parent_item_colon'  => __( 'Parent Collection:', 'tainacan' ),
			'all_items'			 => __( 'All Collections', 'tainacan' ),
			'archives'			 => __( 'Collections Archive', 'tainacan' ),
			'menu_name'          => __( 'Collections', 'tainacan' )
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
			/* Translators: The Collections slug - will be the URL for the collections archive */
			'rewrite'             => ['slug' => sanitize_title(_x('collections', 'Slug: the string that will be used to build the URL', 'tainacan'))],
			'capability_type'     => Entities\Collection::get_capability_type(),
			'map_meta_cap'        => true,
			'supports'            => [
				'title',
				'editor',
				'thumbnail',
				'revisions',
				'page-attributes'
			]
		);
		register_post_type( Entities\Collection::get_post_type(), $args );
	}

	/**
	 * @param \Tainacan\Entities\Collection $collection
	 *
	 * @return \Tainacan\Entities\Collection
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Repository::insert()
	 */
	public function insert( $collection ) {

		$this->pre_process( $collection );

		$new_collection = parent::insert( $collection );

		$this->handle_core_metadata( $new_collection );

		$collection->register_collection_item_post_type();
		flush_rewrite_rules( false ); // needed to activate items post type archive url
		$this->update_moderators( $new_collection );

		return $new_collection;
	}

	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	/**
	 * fetch collection based on ID or WP_Query args
	 *
	 * Collections are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 * 
	 * If a number is passed to $args, it will return a \Tainacan\Entities\Collection object.  But if the post is not found or
	 * does not match the entity post type, it will return an empty array
	 *
	 * @param array $args WP_Query args || int $args the collection id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return \WP_Query|Array an instance of wp query OR array of entities;
	 */
	public function fetch( $args = [], $output = null ) {
		if ( is_numeric( $args ) ) {
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				try {
					return new Entities\Collection( $existing_post );
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

			$args['post_type'] = Entities\Collection::get_post_type();

			// TODO: Pegar coleções registradas via código

			$args = apply_filters( 'tainacan_fetch_args', $args, 'collections' );

			$wp_query = new \WP_Query( $args );

			return $this->fetch_output( $wp_query, $output );
		}
	}

	public function fetch_by_db_identifier( $db_identifier ) {
		if ( $id = $this->get_id_by_db_identifier( $db_identifier ) ) {
			return $this->fetch( $id );
		}
	}

	public function get_id_by_db_identifier( $db_identifier ) {
		$prefix = \Tainacan\Entities\Collection::$db_identifier_prefix;
		$sufix  = \Tainacan\Entities\Collection::$db_identifier_sufix;
		$id     = str_replace( $prefix, '', $db_identifier );
		$id     = str_replace( $sufix, '', $id );
		if ( is_numeric( $id ) ) {
			return (int) $id;
		}

		return false;
	}

	function pre_process( $collection ) {
		// make sure we get the current value from database
		$current_moderators       = $this->get_mapped_property( $collection, 'moderators_ids' );
		$this->current_moderators = is_array( $current_moderators ) ? $current_moderators : [];

		$this->old_collection       = $this->fetch( $collection->get_id() );
		$this->old_core_title       = $collection->get_core_title_metadatum();
		$this->old_core_description = $collection->get_core_description_metadatum();


	}

	function update_moderators( $collection ) {
		$moderators = $collection->get_moderators_ids();

		$deleted = array_diff( $this->current_moderators, $moderators );
		$added   = array_diff( $moderators, $this->current_moderators );

		do_action( 'tainacan-add-collection-moderators', $collection, $added );
		do_action( 'tainacan-remove-collection-moderators', $collection, $deleted );
	}

	function handle_core_metadata( $collection ) {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

		$Tainacan_Metadata->register_core_metadata( $collection );

		if ( $this->old_collection instanceof Entities\Collection && 
			$this->old_collection->get_parent() != $collection->get_parent() &&
			$this->old_core_title instanceof Entities\Metadatum &&
			$this->old_core_description instanceof Entities\Metadatum 
		) {
			$Tainacan_Metadata->maybe_update_core_metadata_meta_keys( $collection, $this->old_collection, $this->old_core_title, $this->old_core_description );
		}
	}

	/**
	 * Filter to handle special permissions
	 *
	 * @see https://developer.wordpress.org/reference/hooks/map_meta_cap/
	 *
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {

		// Filters meta caps edit_tainacan-collection and check if user is moderator

		if ( $cap == 'edit_post' && is_array( $args ) && array_key_exists( 0, $args ) ) { // edit_tainacan-colletion is mapped to edit_post

			$entity = $args[0];

			if ( is_numeric( $entity ) || $entity instanceof Entities\Collection ) {

				if ( is_numeric( $entity ) ) {
					$post = get_post( $entity );
					if ( $post instanceof \WP_Post && $post->post_type == Entities\Collection::get_post_type() ) {
						$entity = new Entities\Collection( $post );
					}

				}

				if ( $entity instanceof Entities\Collection ) {
					$moderators = $entity->get_moderators_ids();
					if ( is_array( $moderators ) && in_array( $user_id, $moderators ) ) {

						// if user is moderator, we clear the current caps
						// (that might fave edit_others_posts) and leave only read, that everybody has
						$collection_cpt = get_post_type_object( Entities\Collection::get_post_type() );
						$caps           = [ 'read' ];
					}
				}
			}
		}

		return $caps;
	}

}