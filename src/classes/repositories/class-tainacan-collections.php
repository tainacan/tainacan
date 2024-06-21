<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;
use Tainacan\Entities\Collection;

class Collections extends Repository {
	public $entities_type = '\Tainacan\Entities\Collection';

	private static $instance = null;
	private $old_collection;
	private $old_core_title;
	private $old_core_description;

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
		$entity = $this->get_name();
		return apply_filters( "tainacan-get-map-$entity", [
			'name' => [
				'map'         => 'post_title',
				'title'       => __( 'Name', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The title of the collection', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'status' => [
				'map'         => 'post_status',
				'title'       => __( 'Status', 'tainacan' ),
				'type'        => 'string',
				'default'     => '',
				'description' => __( 'The current situation of the collection. It also affects the visibility of the collection items, as public items from private collections do not appear in the site.', 'tainacan' )
			],
			'author_id' => [
				'map'         => 'post_author',
				'title'       => __( 'Author ID', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The collection author\'s user ID (numeric string)', 'tainacan' )
			],
			'creation_date' => [
				'map'         => 'post_date',
				'title'       => __( 'Creation Date', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The collection creation date', 'tainacan' )
			],
			'modification_date' => [
				'map'         => 'post_modified',
				'title'       => __( 'Modification Date', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The collection modification date', 'tainacan' )
			],
			'order' => [
				'map'         => 'order',
				'title'       => __( 'Order', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Collection order. This metadata is used if collections are manually ordered.', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'parent' => [
				'map'         => 'post_parent',
				'title'       => __( 'Parent Collection', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'Original collection from which features are inherited', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'description' => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'An introductory text describing the collection', 'tainacan' ),
				'default'     => '',
				//'validation' => v::stringType(),
			],
			'slug' => [
				'map'         => 'post_name',
				'title'       => __( 'Slug', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'An unique and sanitized string representation of the collection, used to build the collection URL. It must not contain any special characters or spaces.', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'default_orderby' => [
				'map'         => 'meta',
				'title'       => __( 'Default order metadata', 'tainacan' ),
				'type'        => ['string', 'array', 'object'],
				'description' => __( 'Default property that items in this collections will be ordered by', 'tainacan' ),
				'default'     => 'date',
				//'validation' => v::stringType(),
			],
			'default_order' => [
				'map'         => 'meta',
				'title'       => __( 'Default order', 'tainacan' ),
				'description' => __( 'Default order for items in this collection. ASC or DESC', 'tainacan' ),
				'type'        => 'string',
				'default'     => 'ASC',
				'enum'  => [ 'ASC', 'DESC' ],
				'validation'  => v::stringType()->in( [ 'ASC', 'DESC' ] ),
			],
			'default_displayed_metadata' => [
				'map'         => 'meta',
				'title'       => __( 'Default Displayed Metadata', 'tainacan' ),
				'type'        => ['array', 'object', 'string'],
				'items'       => [ 'type' => ['array','string', 'integer', 'object'] ],
				'default'     => [],
				'description' => __( 'List of collection properties that will be displayed in the table view', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'default_view_mode' => [
				'map'         => 'meta',
				'title'       => __( 'Default view mode', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Collection default visualization mode', 'tainacan' ),
				'default'     => 'table',
				//'validation' => v::stringType(),
			],
			'enabled_view_modes' => [
				'map'         => 'meta',
				'title'       => __( 'Enabled view modes', 'tainacan' ),
				'type'        => 'array',
				'description' => __( 'Which visualization modes will be available for the public to choose from', 'tainacan' ),
				'default'     => [ 'table', 'cards', 'masonry' ],
				'items'       => [ 'type' => 'string' ],
				//'validation' => v::stringType(),
			],
			'metadata_section_order' => [
				'map'         => 'meta',
				'title'       => __( 'Metadata order', 'tainacan' ),
				'type'        => 'array',
				'items'       => [
					'type' => 'object',
					'properties' => [
						'id' => [
							'description' => __( 'Metadata Section ID', 'tainacan' ),
							'type' => ['integer', 'string'],
						],
						'enabled' => [
							'description' => __( 'Whether the metadata section is enabled or not.', 'tainacan' ),
							'type' => 'boolean',
						],
						'metadata_order' => [
							'type'        => 'array',
							'description' => __( 'Array containing the metadata order inside the section', 'tainacan' ),
							'items' => [
								'type' => 'object', 
								'properties' => [
									'id' => [
										'description' => __( 'Metadata ID', 'tainacan' ),
										'type' => 'integer'
									],
									'enabled' => [
										'description' => __( 'Whether the metadata is enabled or not.', 'tainacan' ),
										'type' => 'boolean'
									]

								]
							]
						]
					]
				],
				'description' => __( 'The order of the metadata section in the collection', 'tainacan' ),
			],
			'metadata_order'             => [
				'map'         => 'meta',
				'title'       => __( 'Metadata order', 'tainacan' ),
				'type'        => 'array',
				'description' => __( 'The order of the metadata in the collection', 'tainacan' ),
				'items' => [
					'type' => 'object', 
					'properties' => [
						'id' => [
							'description' => __( 'Metadata ID', 'tainacan' ),
							'type' => 'integer'
						],
						'enabled' => [
							'description' => __( 'Whether the metadata is enabled or not.', 'tainacan' ),
							'type' => 'boolean'
						]
					]
				]
				//'validation' => v::stringType(),
			],
			'filters_order'              => [
				'map'         => 'meta',
				'title'       => __( 'Filters order', 'tainacan' ),
				'type'        => 'array',
				'description' => __( 'The order of the filters in the collection', 'tainacan' ),
				'items' => [
					'type' => 'object', 
					'properties' => [
						'id' => [
							'description' => __( 'Filter ID', 'tainacan' ),
							'type' => 'integer'
						],
						'enabled' => [
							'description' => __( 'Whether the filter is enabled or not.', 'tainacan' ),
							'type' => 'boolean'
						]
					]
				]
				//'validation' => v::stringType(),
			],
			'enable_cover_page'          => [
				'map'         => 'meta',
				'title'       => __( 'Enable Cover Page', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'To use this page as the home page of this collection', 'tainacan' ),
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'default'     => 'no'
			],
			'cover_page_id'              => [
				'map'         => 'meta',
				'title'       => __( 'Cover Page ID', 'tainacan' ),
				'type'        => ['integer', 'string'],
				'description' => __( 'If enabled, this custom page will be used as cover for this collection, instead of default items list.', 'tainacan' ),
				'on_error'    => __( 'Invalid page', 'tainacan' ),
				//'validation' => v::numeric(),
				'default'     => ''
			],
			'header_image_id'            => [
				'map'         => 'meta',
				'title'       => __( 'Header Image', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The image to be used in collection header, if the theme has one.', 'tainacan' ),
				'on_error'    => __( 'Invalid image', 'tainacan' ),
				//'validation' => v::numeric(),
				'default'     => ''
			],
			'_thumbnail_id'              => [
				'map'         => 'meta',
				'title'       => __( 'Thumbnail', 'tainacan' ),
				'description' => __( 'Squared reduced-size version of a picture that helps recognizing and organizing files', 'tainacan' ),
				'type'        => ['integer', 'string'],
			],
			'comment_status'  => [
				'map'         => 'comment_status',
				'title'       => __( 'Comment Status', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Collection comment status: "open" means comments are allowed, "closed" means comments are not allowed.', 'tainacan' ),
				'default'     => get_default_comment_status(Entities\Collection::get_post_type()),
				'enum'  => [ 'open', 'closed' ],
				'validation'  => v::optional(v::stringType()->in( [ 'open', 'closed' ] )),
			],
			'allow_comments'  => [
				'map'         => 'meta',
				'title'       => __( 'Allow enabling comments on items', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If this option is enabled, items of this collection can be set to enable a comments section on their page. "open" means comments are allowed, "closed" means comments are not allowed.', 'tainacan' ),
				'default'     => 'closed',
				'enum'  => [ 'open', 'closed' ],
				'validation'  => v::optional(v::stringType()->in( [ 'open', 'closed' ] )),
			],
			'submission_anonymous_user'  => [
				'map'         => 'meta',
				'title'       => __( 'Allows submission by anonymous user', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, allows submission by anonymous users, whose does not have to be logged in with permissions on the WordPress system.', 'tainacan' ),
				'default'     => 'no',
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
			],
			'submission_default_status'  => [
				'map'         => 'meta',
				'title'       => __( 'Default submission item status', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The default status of the item that will be created in the collection after submission.', 'tainacan' ),
				'default'     => 'draft'
			],
			'allows_submission' => [
				'map'         => 'meta',
				'title'       => __( 'Allows item submission', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, the collection allows item submission, for example via the Item Submission block.', 'tainacan' ),
				'default'     => 'no',
				'enum'  => [ 'yes', 'no' ],
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
			],
			'hide_items_thumbnail_on_lists' => [
				'map'         => 'meta',
				'title'       => __( 'Hide items thumbnail on lists', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Enable this option to never display the item thumbnail on the items list. This is ment for collections made of mainly textual content.', 'tainacan' ),
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'default'     => 'no'
			],
			'submission_use_recaptcha' => [
				'map'         => 'meta',
				'title'       => __( 'Use reCAPTCHA verification on submission form', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, the collection allows item submission using a reCAPTCHA', 'tainacan' ),
				'default'     => 'no',
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
			],
			'default_metadata_section_properties' => [
				'map'         => 'meta',
				'title'       => __( 'Default metadata section properties', 'tainacan' ),
				'type'        => 'object',
				'description' => __( 'The default metadata section properties', 'tainacan' ),
				'properties'  => [
					'name' => [
						'type'        => 'string',
						'description' => __( 'The name of the default metadata section', 'tainacan' ),
					],
					'description' => [
						'type'        => 'string',
						'description' => __( 'The description of the default metadata section', 'tainacan' ),
					],
					'description_bellow_name' => [
						'type'        => 'string',
						'description' => __( 'Whether the description should appear bellow the metadata section.', 'tainacan' ),
						'enum'  => [ 'yes', 'no' ]
					]
				]
			],
			'item_enabled_document_types' => [
				'map'         => 'meta',
				'title'       => __( 'Enabled document types', 'tainacan' ),
				'type'        => 'object',
				'description' => __( 'The document types that are available in the item edition form.', 'tainacan' ),
				'items' => [
					'type' => 'object', 
					'properties' => [
						'enabled' => [
							'description' => __( 'Whether the document type is enabled or not.', 'tainacan' ),
							'type' => 'string',
							'enum'  => [ 'yes', 'no' ],
						],
						'label' => [
							'description' => __( 'The label that will represent the document type.', 'tainacan' ),
							'type' => 'string',
						],
						'icon' => [
							'description' => __( 'The slug of the icon that will represent the document type.', 'tainacan' ),
							'type' => 'string',
						],
					]
				],
				'default' => [
					'attachment' => [
						'enabled' =>  'yes',
						'label' => __( 'File', 'tainacan' ),
						'icon' => 'attachments'
					],
					'url' => [
						'enabled' => 'yes',
						'label' => __('URL', 'tainacan' ),
						'icon' => 'url'
					],
					'text' => [
						'enabled' => 'yes',
						'label' => __('Text', 'tainacan' ),
						'icon' => 'text'
					]
				]
			],
			'item_document_label' => [
				'map'         => 'meta',
				'title'       => __( 'Main document label', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The label for the main document section in the item edition form', 'tainacan' ),
				'default'     => __( 'Document', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'item_thumbnail_label' => [
				'map'         => 'meta',
				'title'       => __( 'Thumbnail label', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The label for the thumbnail section in the item edition form', 'tainacan' ),
				'default'     => __( 'Thumbnail', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'item_enable_thumbnail'  => [
				'map'         => 'meta',
				'title'       => __( 'Item thumbnail', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, each item can have a thumbnail customized instead of the one automatically generated based on the item document.', 'tainacan' ),
				'default'     => 'yes',
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
			],
			'item_attachment_label' => [
				'map'         => 'meta',
				'title'       => __( 'Attachments label', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The label for the attachments section in the item edition form', 'tainacan' ),
				'default'     => __( 'Attachments', 'tainacan' ),
				//'validation' => v::stringType(),
			],
			'item_enable_attachments'  => [
				'map'         => 'meta',
				'title'       => __( 'Item attachments', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, each item can have a set of files attached to it, complementary to the item document.', 'tainacan' ),
				'default'     => 'yes',
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
			],
			'item_enable_metadata_focus_mode'  => [
				'map'         => 'meta',
				'title'       => __( 'Metadata focus mode', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, a button can start a special navigation mode, that focus one metadatum per time in the item edition form.', 'tainacan' ),
				'default'     => 'yes',
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
			],
			'item_enable_metadata_required_filter'  => [
				'map'         => 'meta',
				'title'       => __( 'Metadata required filter', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, a switch can be toggled to display only required metadata in the item edition form.', 'tainacan' ),
				'default'     => 'yes',
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
			],
			'item_enable_metadata_searchbar'  => [
				'map'         => 'meta',
				'title'       => __( 'Metadata search bar', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, a search bar can be used for filtering the list of metadata in the item edition form.', 'tainacan' ),
				'default'     => 'yes',
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
			],
			'item_enable_metadata_collapses'  => [
				'map'         => 'meta',
				'title'       => __( 'Metadata collapses', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, each metadata in the item form will be wrapped in a collapsable component.', 'tainacan' ),
				'default'     => 'yes',
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
			],
			'item_enable_metadata_enumeration'  => [
				'map'         => 'meta',
				'title'       => __( 'Metadata enumeration', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'If enabled, the metadata sections and their metadata in the item form will be enumerated automatically.', 'tainacan' ),
				'default'     => 'no',
				'on_error'    => __( 'Value should be yes or no', 'tainacan' ),
				'enum'  => [ 'yes', 'no' ],
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
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
			'capabilities'        => (array) $this->get_capabilities(),
			'map_meta_cap'        => true,
			'show_in_rest'        => true,
			'show_in_nav_menus'	  => true,
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
		$this->handle_parent_order_clone( $collection );

		$new_collection = parent::insert( $collection );

		$this->handle_core_metadata( $new_collection );
		$this->handle_control_metadata( $new_collection );

		$collection->register_collection_item_post_type();
		flush_rewrite_rules( false ); // needed to activate items post type archive url

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
					$col = new Entities\Collection( $existing_post );
					if ( $col->can_read() ) {
						return $col;
					}
					return [];
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

			$args = apply_filters( 'tainacan-fetch-args', $args, 'collections' );

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
		$this->old_collection       = $this->fetch( $collection->get_id() );
		$this->old_core_title       = $collection->get_core_title_metadatum();
		$this->old_core_description = $collection->get_core_description_metadatum();
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

	function handle_control_metadata( $collection ) {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

		$Tainacan_Metadata->register_control_metadata( $collection );
	}

	/**
	 * This function guarantees that children collections has its own clone 
	 * of "metadata_order" from the parent collention.
	 * 
	 * @param \Tainacan\Entities\Collection $collection, children collection
	 *
	 * @return void
	 */
	function handle_parent_order_clone( &$collection ) {
		if ($collection instanceof Entities\Collection && $collection->get_parent() != 0) {
			$parent_collection = $this->fetch( $collection->get_parent() );
			$collection->set_metadata_section_order($parent_collection->get_metadata_section_order());
			$collection->set_metadata_order($parent_collection->get_metadata_order());
			$collection->set_filters_order($parent_collection->get_filters_order());

			if (!$collection->validate()) {
				throw new \Exception( implode(",", $collection->get_errors()) );
			}
		}
	}

}
