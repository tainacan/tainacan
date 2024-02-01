<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class that represents the Collection entity
 */
class Collection extends Entity {

	protected
		$diplay_name,
		$full,
		$_thumbnail_id,
		$modification_date,
		$creation_date,
		$author_id,
		$url,
		$name,
		$slug,
		$order,
		$parent,
		$description,
		$default_order,
		$default_orderby,
		$columns,
		$default_view_mode,
		$enabled_view_modes,
		$metadata_order,
		$metadata_section_order,
		$filters_order,
		$enable_cover_page,
		$cover_page_id,
		$header_image_id,
		$header_image,
		$comment_status,
		$allow_comments,
		$allows_submission,
		$hide_items_thumbnail_on_lists,
		$submission_anonymous_user,
		$submission_default_status,
		$submission_use_recaptcha,
		$item_enabled_document_types,
		$item_document_label,
		$item_thumbnail_label,
		$item_enable_thubmnail,
		$item_attachment_label,
		$item_enable_attachments,
		$item_enable_metadata_focus_mode,
		$item_enable_metadata_required_filter,
		$item_enable_metadata_searchbar,
		$item_enable_metadata_collapses,
		$item_enable_metadata_enumeration;

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::post_type
	 * @var string
	 */
	static $post_type = 'tainacan-collection';
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Collections';

	/**
	 * Prefix used to create the db_identifier
	 *
	 * @var string
	 */
	static $db_identifier_prefix = 'tnc_col_';

	/**
	 * sufix used to create the db_identifier
	 *
	 * @var string
	 */
	static $db_identifier_sufix = '_item';

	public function __toString() {
		return apply_filters("tainacan-collection-to-string", $this->get_name(), $this);
	}

	public function _toArray() {
		$array_collection = parent::_toArray();

		$array_collection['thumbnail']         = $this->get_thumbnail();
		$array_collection['header_image']      = $this->get_header_image();
		$array_collection['author_name']       = $this->get_author_name();
		$array_collection['url']               = $this->get_url();
		$array_collection['creation_date']     = $this->get_date_i18n( explode( ' ', $array_collection['creation_date'] ?? '' )[0]  );
		$array_collection['modification_date'] = $this->get_date_i18n( explode( ' ', $array_collection['modification_date'] ?? '' )[0]  );

		return apply_filters('tainacan-collection-to-array', $array_collection, $this);
	}

	/**
	 * Register the post type for this collection
	 *
	 * Each collection is a post type, and each item inside a collection is a post of this post type
	 *
	 * This method register the post type for a collection, so that items can be created.
	 *
	 * @return \WP_Post_Type|\WP_Error
	 */
	function register_collection_item_post_type() {
		$repository = $this->get_repository();

		$cpt_labels = $repository->get_cpt_labels();

		$cpt_labels['menu_name'] = $this->get_name();
		$cpt_labels['name'] = $this->get_name();
		$cpt_labels['singular_name'] = $this->get_name();

		$cpt_slug     = $this->get_db_identifier();
		$capabilities = $this->get_items_capabilities();

		$args = array(
			'labels'              => $cpt_labels,
			'hierarchical'        => true,
			'description'		  => $this->get_description(),
			//'supports'          => array('title'),
			//'taxonomies'        => array(self::TAXONOMY),
			'public'              => true,
			'show_ui'             => tnc_enable_dev_wp_interface(),
			'show_in_menu'        => tnc_enable_dev_wp_interface(),
			//'menu_position'     => 5,
			//'show_in_nav_menus' => false,
			'show_in_rest'        => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => [
				'slug' => $this->get_slug()
			],
			'map_meta_cap'        => true,
			'capabilities'        => (array) $capabilities,
			'show_in_nav_menus'   => false,
			'supports'            => [
				'title',
				'editor',
				'thumbnail',
				'revisions',
				'page-attributes',
				'post-formats',
				'comments'
			]
		);

		if ( post_type_exists( $this->get_db_identifier() ) ) {
			unregister_post_type( $this->get_db_identifier() );
		}

		return register_post_type( $cpt_slug, $args );
	}

	/**
	 * Get the capabilities list for the post type of the items of this collection
	 *
	 * This method is usefull for getting the capabilities of the collection's items post type
	 * regardless if it has been already registered or not.
	 *
	 * @return object Object with all the capabilities as member variables.
	 */
	function get_items_capabilities() {

		$id = $this->get_id();

		return (object) [
			// meta
			'edit_post' => "tnc_col_{$id}_edit_item",
			'read_post' => "tnc_col_{$id}_read_item",
			'delete_post' => "tnc_col_{$id}_delete_item",

			// primitive
			'edit_posts' => "tnc_col_{$id}_edit_items",
			'edit_others_posts' => "tnc_col_{$id}_edit_others_items",
			'publish_posts' => "tnc_col_{$id}_publish_items",
			'read_private_posts' => "tnc_col_{$id}_read_private_items",
			'read' => "read",
			'delete_posts' => "tnc_col_{$id}_delete_items",
			'delete_private_posts' => "tnc_col_{$id}_delete_items",
			'delete_published_posts' => "tnc_col_{$id}_delete_published_items",
			'delete_others_posts' => "tnc_col_{$id}_delete_others_items",
			'edit_private_posts' => "tnc_col_{$id}_edit_others_items",
			'edit_published_posts' => "tnc_col_{$id}_edit_published_items",
			'create_posts' => "tnc_col_{$id}_edit_items"
		];
	}

	public function get_capabilities() {

		return (object) [
			// meta
			'edit_post' => "tnc_rep_edit_collection",
			'read_post' => "tnc_rep_read_collection",
			'delete_post' => "tnc_rep_delete_collection",

			// primitive
			'edit_posts' => "tnc_rep_edit_collections",
			'edit_others_posts' => "manage_tainacan",
			'publish_posts' => "tnc_rep_edit_collections",
			'read_private_posts' => "tnc_rep_read_private_collections",
			'read' => "read",
			'delete_posts' => "tnc_rep_delete_collections",
			'delete_private_posts' => "tnc_rep_delete_collections",
			'delete_published_posts' => "tnc_rep_delete_collections",
			'delete_others_posts' => "manage_tainacan",
			'edit_private_posts' => "tnc_rep_edit_collections",
			'edit_published_posts' => "tnc_rep_edit_collections",
			'create_posts' => "tnc_rep_edit_collections"
		];

	}

	/**
	 * @param null $exclude
	 *
	 * @return array
	 */
	function get_attachments($exclude = null) {
		$collection_id = $this->get_id();

		if(!$exclude){
			$to_exclude = get_post_thumbnail_id( $collection_id );
		} else {
			$to_exclude = $exclude;
		}

		$attachments_query = [
			'post_type'     => 'attachment',
			'post_per_page' => -1,
			'post_parent'   => $collection_id,
			'exclude'       => $to_exclude
		];

		$attachments = get_posts( $attachments_query );

		return apply_filters("tainacan-collection-get-attachments", $attachments, $exclude, $this);
	}

	/**
	 * @return string
	 */
	function get_author_name() {
		$name = get_the_author_meta( 'display_name', $this->get_author_id() );
		return apply_filters("tainacan-collection-get-author-name", $name, $this);
	}

	/**
	 * @return array
	 */
	function get_thumbnail() {

		$sizes = get_intermediate_image_sizes();

		array_unshift($sizes, 'full');

		foreach ( $sizes as $size ) {
			$thumbs[$size] = wp_get_attachment_image_src( $this->get__thumbnail_id(), $size );
		}

		return apply_filters("tainacan-collection-get-thumbnail", $thumbs, $this);
	}

	/**
	 * @return false|string
	 */
	function get_header_image(){
		$header_image = wp_get_attachment_url( $this->get_header_image_id() );
		return apply_filters("tainacan-collection-get-header-image", $header_image, $this);
	}

	/**
	 * @param $id
	 */
	function set__thumbnail_id( $id ) {
		$this->set_mapped_property( '_thumbnail_id', $id );
	}

	/**
	 * @return int|string
	 */

	function get__thumbnail_id() {
		$_thumbnail_id = $this->get_mapped_property("_thumbnail_id");
		if ( isset( $_thumbnail_id ) ) {
			return $_thumbnail_id;
		}

		return get_post_thumbnail_id( $this->get_id() );
	}

	/**
	 * @return mixed|null
	 */
	function get_modification_date() {
		return $this->get_mapped_property( 'modification_date' );
	}

	/**
	 * @return mixed|null
	 */
	function get_creation_date() {
		return $this->get_mapped_property( 'creation_date' );
	}

	/**
	 * @return mixed|null
	 */
	function get_author_id() {
		return $this->get_mapped_property( 'author_id' );
	}

	/**
	 * @return mixed|null
	 */
	function get_url() {
		return get_permalink( $this->get_id() );
	}

	/**
	 * Get collection name
	 *
	 * @return string
	 */
	function get_name() {
		return $this->get_mapped_property( 'name' );
	}

	/**
	 * Get collection slug
	 *
	 * @return string
	 */
	function get_slug() {
		return $this->get_mapped_property( 'slug' );
	}

	/**
	 * Get collection order
	 *
	 * @return integer
	 */
	function get_order() {
		return $this->get_mapped_property( 'order' );
	}

	/**
	 * Get collection parent ID
	 *
	 * @return integer
	 */
	function get_parent() {
		return $this->get_mapped_property( 'parent' );
	}

	/**
	 * Get collection description
	 *
	 * @return string
	 */
	function get_description() {
		return $this->get_mapped_property( 'description' );
	}

	/**
	 * Get collection default order
	 *
	 * @return string
	 */
	function get_default_order() {
		return $this->get_mapped_property( 'default_order' );
	}

	/**
	 * Get collection default orderby
	 *
	 * @return string
	 */
	function get_default_orderby() {
		return $this->get_mapped_property( 'default_orderby' );
	}

	/**
	 * Get collection columns option
	 *
	 * @return string
	 */
	function get_default_displayed_metadata() {
		return $this->get_mapped_property( 'default_displayed_metadata' );
	}

	/**
	 * Get collection default_view_mode option
	 *
	 * @return string
	 */
	function get_default_view_mode() {
		return $this->get_mapped_property( 'default_view_mode' );
	}

	/**
	 * Get collection enabled_view_modes option
	 *
	 * @return string
	 */
	function get_enabled_view_modes() {
		return $this->get_mapped_property( 'enabled_view_modes' );
	}

	/**
	 * Get collection metadata ordination
	 *
	 * @return Object | string
	 */
	function get_metadata_order() {
		return $this->get_mapped_property( 'metadata_order' );
	}

	/**
	 * Get collection metadata section ordination
	 *
	 * @return Array | Object | string
	 */
	function get_metadata_section_order() {
		return $this->get_mapped_property( 'metadata_section_order' );
	}

	/**
	 * Get enable cover page attribute
	 *
	 * @return string
	 */
	function get_enable_cover_page() {
		return $this->get_mapped_property( 'enable_cover_page' );
	}

	/**
	 * Get Header Image ID attribute
	 *
	 * @return string
	 */
	function get_header_image_id() {
		return $this->get_mapped_property( 'header_image_id' );
	}

	/**
	 * Return true if enabled cover page is set to yes
	 *
	 * @return boolean
	 */
	function is_cover_page_enabled() {
		return $this->get_enable_cover_page() === 'yes';
	}

	/**
	 * Get enable cover page attribute
	 *
	 * @return string
	 */
	function get_cover_page_id() {
		return $this->get_mapped_property( 'cover_page_id' );
	}

	/**
	 * Get collection filters ordination
	 *
	 * @return string
	 */
	function get_filters_order() {
		return $this->get_mapped_property( 'filters_order' );
	}

	/**
	 * Get collection DB identifier
	 *
	 * This identifier is used to register the collection post type and never changes, even if you change the name and the slug of the collection.
	 *
	 * @return string
	 */
	function get_db_identifier() {
		return $this->get_id() ? Collection::$db_identifier_prefix . $this->get_id() . Collection::$db_identifier_sufix : false;
	}

	/**
	 * Get collection metadatum.
	 *
	 * Returns an array of \Entity\Metadatum objects, representing all the metadatum of the collection.
	 *
	 * @see \Tainacan\Repositories\Metadata->fetch()
	 *
	 * @return [\Tainacan\Entities\Metadatum] array
	 * @throws \Exception
	 */
	function get_metadata() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

		return $Tainacan_Metadata->fetch_by_collection( $this );
	}

	/**
	 * Get collection filters.
	 *
	 * Returns an array of \Entity\Filter objects, representing all the filters of the collection.
	 *
	 * @see \Tainacan\Repositories\Filters->fetch()
	 *
	 * @return [\Tainacan\Entities\Filter] array
	 * @throws \Exception
	 */
	function get_filters() {
		$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

		return $Tainacan_Filters->fetch_by_collection( $this );
	}

	/**
	 * Get the two core metadata of the collection (title and description)
	 *
	 * @return array[\Tainacan\Entities\Metadatum]
	 */
	function get_core_metadata() {
		$repo = \Tainacan\Repositories\Metadata::get_instance();

		return $repo->get_core_metadata($this);

	}

	/**
	 * Get the Core Title Metadatum for this collection
	 *
	 * @return \Tainacan\Entities\Metadatum The Core Title Metadatum
	 */
	function get_core_title_metadatum() {
		$repo = \Tainacan\Repositories\Metadata::get_instance();

		return $repo->get_core_title_metadatum($this);
	}

	/**
	 * Get the Core Description Metadatum for this collection
	 *
	 * @return \Tainacan\Entities\Metadatum The Core Description Metadatum
	 */
	function get_core_description_metadatum() {
		$repo = \Tainacan\Repositories\Metadata::get_instance();

		return $repo->get_core_description_metadatum($this);
	}

	/**
	 * Checks if comments are allowed for the current Collection.
	 * @return string "open"|"closed"
	 */
	public function get_comment_status() {
		return $this->get_mapped_property('comment_status');
	}

	/**
	 * Checks if comments are allowed for the current Collection Items.
	 * @return bool
	 */
	public function get_allow_comments() {
		return $this->get_mapped_property('allow_comments');
	}

	/**
	 * Get enable submission with anonymous user
	 *
	 * @return string "yes"|"no"
	 */
	function get_submission_anonymous_user() {
		return $this->get_mapped_property( 'submission_anonymous_user' );
	}

	/**
	 * Get default submission status
	 *
	 * @return string
	 */
	function get_submission_default_status() {
		return $this->get_mapped_property( 'submission_default_status' );
	}

	/**
	 * Checks if submission items are allowed for the current collection.
	 *
	 * @return string "yes"|"no"
	 */
	function get_allows_submission() {
		return $this->get_mapped_property( 'allows_submission' );
	}

	/**
	 * Get the state of $hide_items_thumbnail_on_lists to decide if it should always display item thumbnails, even being placeholders
	 *
	 * @return string
	 */
	function get_hide_items_thumbnail_on_lists() {
		return $this->get_mapped_property( 'hide_items_thumbnail_on_lists' );
	}

	/**
	 * Checks if submission items use a recaptcha.
	 *
	 * @return string "yes"|"no"
	 */
	function get_submission_use_recaptcha() {
		return $this->get_mapped_property( 'submission_use_recaptcha' );
	}

	/**
	 * Get the default metadata section properties.
	 *
	 * @return void
	 */
	function get_default_metadata_section_properties( ) {
		return $this->get_mapped_property( 'default_metadata_section_properties' );
	}

	/**
	 * Get the enabled document types for this collection.
	 *
	 * @return array The enabled document types.
	 */
	function get_item_enabled_document_types() {
		return $this->get_mapped_property('item_enabled_document_types');
	}

	/**
	 * Get the label for the section in this collection.
	 *
	 * @return string The label for the section.
	 */
	function get_item_document_label() {
		return $this->get_mapped_property('item_document_label');
	}

	/**
	 * Get the label for the thumbnail section in this collection.
	 *
	 * @return string The label for the thumbnail section.
	 */
	function get_item_thumbnail_label() {
		return $this->get_mapped_property('item_thumbnail_label');
	}

	/**
	 * Check if thumbnail are enabled for this collection.
	 *
	 * @return string 'yes' if thumbnail are enabled, 'no' otherwise.
	 */
	function get_item_enable_thumbnail() {
		return $this->get_mapped_property('item_enable_thumbnail');
	}

	/**
	 * Get the label for the attachment section in this collection.
	 *
	 * @return string The label for the attachment section.
	 */
	function get_item_attachment_label() {
		return $this->get_mapped_property('item_attachment_label');
	}

	/**
	 * Check if attachments are enabled for this collection.
	 *
	 * @return string 'yes' if attachments are enabled, 'no' otherwise.
	 */
	function get_item_enable_attachments() {
		return $this->get_mapped_property('item_enable_attachments');
	}

	/**
	 * Check if metadata focus mode is enabled for this collection.
	 *
	 * @return string 'yes' if metadata focus mode is enabled, 'no' otherwise.
	 */
	function get_item_enable_metadata_focus_mode() {
		return $this->get_mapped_property('item_enable_metadata_focus_mode');
	}

	/**
	 * Check if metadata required filter is enabled for this collection.
	 *
	 * @return string 'yes' if metadata required filter is enabled, 'no' otherwise.
	 */
	function get_item_enable_metadata_required_filter() {
		return $this->get_mapped_property('item_enable_metadata_required_filter');
	}

	/**
	 * Check if metadata search bar is enabled for this collection.
	 *
	 * @return string 'yes' if metadata search bar is enabled, 'no' otherwise.
	 */
	function get_item_enable_metadata_searchbar() {
		return $this->get_mapped_property('item_enable_metadata_searchbar');
	}

	/**
	 * Check if metadata collapses are enabled for this collection.
	 *
	 * @return bool 'yes' if metadata collapses are enabled, 'no' otherwise.
	 */
	function get_item_enable_metadata_collapses() {
		return $this->get_mapped_property('item_enable_metadata_collapses');
	}

	/**
	 * Check if metadata and metadata section should be enumerated in the edition form.
	 *
	 * @return bool 'yes' if metadata are enumerated, 'no' otherwise.
	 */
	function get_item_enable_metadata_enumeration() {
		return $this->get_mapped_property('item_enable_metadata_enumeration');
	}

	// Setters
	/**
	 * Set the collection name
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_name( $value ) {
		$this->set_mapped_property( 'name', $value );
	}

	/**
	 * Set the collection slug
	 *
	 * If you dont set the collection slug, it will be set automatically based on the name and
	 * following WordPress default behavior of creating slugs for posts.
	 *
	 * If you set the slug for an existing one, WordPress will append a number at the end of in order
	 * to make it unique (e.g slug-1, slug-2)
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_slug( $value ) {
		$this->set_mapped_property( 'slug', $value );
	}

	/**
	 * Set collection order
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_order( $value ) {
		$this->set_mapped_property( 'order', $value );
	}

	/**
	 * Set collection parent ID
	 *
	 * @param [integer] $value
	 *
	 * @return void
	 */
	function set_parent( $value ) {
		$this->set_mapped_property( 'parent', $value );
	}

	/**
	 * Set collection description
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_description( $value ) {
		$this->set_mapped_property( 'description', $value );
	}

	/**
	 * Set collection default order option
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_default_order( $value ) {
		$this->set_mapped_property( 'default_order', $value );
	}

	/**
	 * Set collection default_orderby option
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_default_orderby( $value ) {
		$this->set_mapped_property( 'default_orderby', $value );
	}

	/**
	 * Set collection columns option
	 *
	 * @param array $value
	 *
	 * @return void
	 */
	function set_default_displayed_metadata( $value ) {
		$this->set_mapped_property( 'default_displayed_metadata', $value );
	}

	/**
	 * Set collection default_view_mode option
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_default_view_mode( $value ) {
		$this->set_mapped_property( 'default_view_mode', $value );
	}

	/**
	 * Set collection enabled_view_modes option
	 *
	 * @param [array] $value
	 *
	 * @return void
	 */
	function set_enabled_view_modes( $value ) {
		$this->set_mapped_property( 'enabled_view_modes', $value );
	}

	/**
	 * Set collection metadata ordination
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_metadata_order( $value ) {
		$this->set_mapped_property( 'metadata_order', $value );
	}

	/**
	 * Set collection metadata section ordination
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_metadata_section_order( $value ) {
		if( !empty( $value ) ) {
			$metadata_order = array( );
			foreach($value as $section) {
				$metadata_order =  array_merge($metadata_order, $section['metadata_order']);
			}
			$this->set_metadata_order($metadata_order);
		}
		$this->set_mapped_property( 'metadata_section_order', $value );
	}

	/**
	 * Set collection filters ordination
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_filters_order( $value ) {
		$this->set_mapped_property( 'filters_order', $value );
	}

	/**
	 * Set enable cover page attribute
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_enable_cover_page( $value ) {
		$this->set_mapped_property( 'enable_cover_page', $value );
	}

	/**
	 * Set cover page ID
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_cover_page_id( $value ) {
		$this->set_mapped_property( 'cover_page_id', $value );
	}

	/**
	 * Set Header Image ID
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_header_image_id( $value ) {
		$this->set_mapped_property( 'header_image_id', $value );
	}

	/**
	 * Sets if comments are allowed for the current Collection.
	 *
	 * @param $value string "open"|"closed"
	 */
	public function set_comment_status( $value ) {
		$this->set_mapped_property('comment_status', $value);
	}

	/**
	 * Sets if comments are allowed for the current Collection Items.
	 *
	 * @param $value bool
	 */
	public function set_allow_comments( $value ) {
		$this->set_mapped_property('allow_comments', $value );
	}

	/**
	 * Set enable submission with anonymous user
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_submission_anonymous_user( $value ) {
		$this->set_mapped_property( 'submission_anonymous_user', $value );
	}

	/**
	 * Set default submission status
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_submission_default_status( $value ) {
		$this->set_mapped_property( 'submission_default_status', $value );
	}

	/**
	 * Set if submission items are allowes for the current collection.
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_allows_submission( $value ) {
		$this->set_mapped_property( 'allows_submission', $value );
	}

	/**
	 * Set the state of $hide_items_thumbnail_on_lists
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	function set_hide_items_thumbnail_on_lists( $value ) {
		$this->set_mapped_property( 'hide_items_thumbnail_on_lists', $value );
	}

	/**
	 * Set if submission items are use recaptcha.
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_submission_use_recaptcha( $value ) {
		return $this->set_mapped_property( 'submission_use_recaptcha', $value);
	}

	/**
	 * Set the default metadata section properties.
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_default_metadata_section_properties( $value ) {
		return $this->set_mapped_property( 'default_metadata_section_properties', $value);
	}


	/**
	 * Set the enabled document types for this collection.
	 *
	 * @param array $value The enabled document types.
	 * @return void
	 */
	function set_item_enabled_document_types( $value ) {
		$this->set_mapped_property('item_enabled_document_types', $value);
	}

	/**
	 * Set the label for the document section in this collection.
	 *
	 * @param string $value The label for the document section.
	 * @return void
	 */
	function set_item_document_label( $value ) {
		$this->set_mapped_property('item_document_label', $value);
	}

	/**
	 * Set the label for the thumbnail section in this collection.
	 *
	 * @param string $value The label for the thumbnail section.
	 * @return void
	 */
	function set_item_thumbnail_label( $value ) {
		$this->set_mapped_property('item_thumbnail_label', $value);
	}

	/**
	 * Enable or disable thumbnail for this collection.
	 *
	 * @param string $value 'yes' to enable thumbnail, 'no' to disable.
	 * @return void
	 */
	function set_item_enable_thumbnail( $value ) {
		$this->set_mapped_property('item_enable_thumbnail', $value);
	}

	/**
	 * Set the label for the attachment section in this collection.
	 *
	 * @param string $value The label for the attachment section.
	 * @return void
	 */
	function set_item_attachment_label( $value ) {
		$this->set_mapped_property('item_attachment_label', $value);
	}

	/**
	 * Enable or disable attachments for this collection.
	 *
	 * @param string $value 'yes' to enable attachments, 'no' to disable.
	 * @return void
	 */
	function set_item_enable_attachments( $value ) {
		$this->set_mapped_property('item_enable_attachments', $value);
	}

	/**
	 * Enable or disable metadata focus mode for this collection.
	 *
	 * @param string $value 'yes' to enable metadata focus mode, 'no' to disable.
	 * @return void
	 */
	function set_item_enable_metadata_focus_mode( $value ) {
		$this->set_mapped_property('item_enable_metadata_focus_mode', $value);
	}

	/**
	 * Enable or disable metadata required filter for this collection.
	 *
	 * @param string $value 'yes' to enable metadata required filter, 'no' to disable.
	 * @return void
	 */
	function set_item_enable_metadata_required_filter( $value ) {
		$this->set_mapped_property('item_enable_metadata_required_filter', $value);
	}

	/**
	 * Enable or disable metadata search bar for this collection.
	 *
	 * @param string $value 'yes' to enable metadata search bar, 'no' to disable.
	 * @return void
	 */
	function set_item_enable_metadata_searchbar( $value ) {
		$this->set_mapped_property('item_enable_metadata_searchbar', $value);
	}

	/**
	 * Enable or disable metadata collapses for this collection.
	 *
	 * @param string $value 'yes' to enable metadata collapses, 'no' to disable.
	 * @return void
	 */
	function set_item_enable_metadata_collapses( $value ) {
		$this->set_mapped_property('item_enable_metadata_collapses', $value);
	}

	/**
	 * Enable or disable metadata and metadata sections enumeration for the item edition form this collection.
	 *
	 * @param string $value 'yes' to enable metadata enumeration, 'no' to disable.
	 * @return void
	 */
	function set_item_enable_metadata_enumeration( $value ) {
		$this->set_mapped_property('item_enable_metadata_enumeration', $value);
	}

	/**
	 * Validate Collection
	 *
	 * @return bool
	 */
	function validate() {
		if ( ! in_array( $this->get_status(), apply_filters( 'tainacan-status-require-validation', [
			'publish',
			'future',
			'private'
		] ) ) ) {
			return true;
		}

		if( $this->is_cover_page_enabled() && !is_numeric( $this->get_cover_page_id() ) ) {
			$this->add_error('cover_page_id', __('cover page is enabled, please specify the page', 'tainacan'));
			return false;
		}

		$metadata_section_order = $this->get_metadata_section_order();
		if ( isset($metadata_section_order['metadata_section_order']) ) {
			$section_order =  $metadata_section_order['metadata_section_order'];
			$metadata_order = $this->get_metadata_order();
			$order_general = array();
			foreach($section_order as $section) {
				$order_general = array_merge($order_general, $section['metadata_order']);
			}

			if( count($order_general) != count($metadata_order) ) {
				return false;
			}
		}

		return parent::validate();

	}

	/**
	 * Checks if an user have permission on any of the collections capabilities
	 * defined in Tainacan\Roles class.
	 * It applies to all capabilities with 'collection' scope starting with 'tnc_col_'
	 *
	 * Usage: use only the suffix of the capability (after tnc_col_%d_). For example, to check if user can
	 * tnc_col_%d_read_private_items for this collection, simply call:
	 * $collection->user_can('read_private_items');
	 *
	 * @param string $capability The capability to be checked.
	 * @param int|\WP_User|null $user the user for capability check, null for the current user
	 * @return void
	 */
	public function user_can($capability, $user = null) {
		if ( \is_null($user) ) {
			$user = wp_get_current_user();
		}

		if ( is_int($user) || $user instanceof \WP_User ) {
			return user_can( $user, 'tnc_col_' . $this->get_id() . '_' . $capability );
		}

		return false;

	}

}
