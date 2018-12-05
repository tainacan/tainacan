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
        $filters_order,
        $enable_cover_page,
        $cover_page_id,
        $header_image_id,
	    $header_image,
        $moderators_ids,
        $comment_status,
        $allow_comments;

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
		$array_collection['url']               = get_permalink( $this->get_id() );
		$array_collection['creation_date']     = $this->get_date_i18n( explode( ' ', $array_collection['creation_date'] )[0] );
		$array_collection['modification_date'] = $this->get_date_i18n( explode( ' ', $array_collection['modification_date'] )[0] );

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
	 * @uses get_post_type_capabilities to get the list.
	 *
	 * This method is usefull for getting the capabilities of the collection's items post type
	 * regardless if it has been already registered or not.
	 *
	 * @return object Object with all the capabilities as member variables.
	 */
	function get_items_capabilities() {
		$args = [
			'map_meta_cap'    => true,
			'capability_type' => $this->get_db_identifier(),
			'capabilities'    => array()
		];

		return get_post_type_capabilities( (object) $args );
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

		$attachments_prepared = [];
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$prepared = [
					'id'          => $attachment->ID,
					'title'       => $attachment->post_title,
					'description' => $attachment->post_content,
					'mime_type'   => $attachment->post_mime_type,
					'url'         => $attachment->guid,
				];

				array_push( $attachments_prepared, $prepared );
			}
		}

		return apply_filters("tainacan-collection-get-attachments", $attachments_prepared, $exclude, $this);
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
		return $this->get_mapped_property( 'url' );
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
	 * @return string
	 */
	function get_metadata_order() {
		return $this->get_mapped_property( 'metadata_order' );
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
	 * Get collection moderators ids
	 *
	 * @return array
	 */
	function get_moderators_ids() {
		return $this->get_mapped_property( 'moderators_ids' );
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

		return $Tainacan_Metadata->fetch_by_collection( $this, [], 'OBJECT' );
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
	 * Set collection moderators ids
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_moderators_ids( $value ) {
	    if(!is_array($value)) {
	        if(empty($value)) {
                $value = [];
	        } else {
                throw new \Exception('moderators_ids must be a array of users ids');
	        }
	    }
		// make sure you never have duplicated moderators 
		$value = array_unique($value);
		
		$this->set_mapped_property( 'moderators_ids', $value );
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

	// Moderators methods

	/**
	 * Add a moderator ID to the moderators_ids list
	 *
	 * @param int $user_id The user ID to be added
	 *
	 * @return boolean Wether the ID was added or not. (if it already existed in the list it returns false)
	 */
	function add_moderator_id( $user_id ) {
		if ( is_integer( $user_id ) ) {
			$current_moderators = $this->get_moderators_ids();
			if ( ! in_array( $user_id, $current_moderators ) ) {
				$current_moderators[] = $user_id;
				$this->set_moderators_ids( $current_moderators );

				return true;
			}
		}

		return false;
	}

	/**
	 * Remove a moderator ID to the moderators_ids list
	 *
	 * @param int $user_id The user ID to be removed
	 *
	 * @return boolean Wether the ID was added or not. (if it did not exist in the list it returns false)
	 */
	function remove_moderator_id( $user_id ) {
		if ( is_integer( $user_id ) ) {
			$current_moderators = $this->get_moderators_ids();
			if ( ( $key = array_search( $user_id, $current_moderators ) ) !== false ) {
				unset( $current_moderators[ $key ] );
				$this->set_moderators_ids( $current_moderators );

				return true;
			}
		}

		return false;
	}

	/**
	 * TODO implement the following methods to handle moderators_ids
	 *
	 * set_moderators
	 * get_moderators
	 * (the same as moderators_ids but gets and sets WP_User objects)
	 *
	 */

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

		return parent::validate();

	}

}
