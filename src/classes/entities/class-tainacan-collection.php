<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class that represents the Collection entity
 */
class Collection extends Entity {

	/**
	 * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::post_type
	 * @var string
	 */
    protected static $post_type = 'tainacan-collections';
    /**
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::repository
     * @var string
     */
    protected $repository = 'Tainacan_Collections';
    
    /**
     * Prefix used to create the db_identifier
     * @var string
     */
    static $db_identifier_prefix = 'tnc_col_';

    public function  __toString(){
	    return 'Hello, my name is '. $this->get_name();
    }

    /**
     * Register the post type for this collection
     *
     * Each collection is a post type, and each item inside a collection is a post of this post type
     *
     * This method register the post type for a collection, so that items can be created.
     *
     * @return void
     */
    function register_collection_item_post_type() {
        $cpt_labels = array(
            'name'               => __('Items', 'tainacan'),
            'singular_name'      => __('Item', 'tainacan'),
            'add_new'            => __('Add new', 'tainacan'),
            'add_new_item'       => __('Add new', 'tainacan'),
            'edit_item'          => __('Edit Item', 'tainacan'),
            'new_item'           => __('New Item', 'tainacan'),
            'view_item'          => __('View Item', 'tainacan'),
            'search_items'       => __('Search items', 'tainacan'),
            'not_found'          => __('No items found', 'tainacan'),
            'not_found_in_trash' => __('No items found in trash', 'tainacan'),
            'parent_item_colon'  => __('Parent item:', 'tainacan'),
            'menu_name'          => $this->get_name()
        );

        $cpt_slug = $this->get_db_identifier();

        $args = array(
            'labels'              => $cpt_labels,
            'hierarchical'        => true,
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
            'capability_type'     => 'post',
            'supports'            => [
                'title',
                'editor',
                'thumbnail',
                'revisions',
                'page-attributes',
                'post-formats'
            ]
        );

        if (post_type_exists($this->get_db_identifier()))
            unregister_post_type($this->get_db_identifier());

        register_post_type($cpt_slug, $args);
    }

    /**
     * Get collection name
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }

    /**
     * Get collection slug
     *
     * @return string
     */
    function get_slug() {
        return $this->get_mapped_property('slug');
    }

    /**
     * Get collection order
     *
     * @return integer
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Get collection parent ID
     *
     * @return integer
     */
    function get_parent() {
        return $this->get_mapped_property('parent');
    }

    /**
     * Get collection description
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }
    
    /**
     * Get collection default order
     *
     * @return string
     */
    function get_default_order() {
        return $this->get_mapped_property('default_order');
    }
    
    /**
     * Get collection default orderby
     *
     * @return string
     */
    function get_default_orderby() {
        return $this->get_mapped_property('default_orderby');
    }
    
    /**
     * Get collection columns option
     *
     * @return string
     */
    function get_columns() {
        return $this->get_mapped_property('columns');
    }
    
    /**
     * Get collection default_view_mode option
     *
     * @return string
     */
    function get_default_view_mode() {
        return $this->get_mapped_property('default_view_mode');
    }

    /**
     * Get collection DB identifier
     *
     * This identifier is used to register the collection post type and never changes, even if you change the name and the slug of the collection.
     *
     * @return string
     */
    function get_db_identifier() {
        return $this->get_id() ? Collection::$db_identifier_prefix . $this->get_id() : false;
    }

	/**
	 * Get collection metadata.
	 *
	 * Returns an array of \Entity\Metadata objects, representing all the metadata of the collection.
	 *
	 * @see \Tainacan\Repositories\Metadatas->fetch()
	 *
	 * @return [\Tainacan\Entities\Metadata] array
	 */
    function get_metadata() {
        $Tainacan_Metadatas = new \Tainacan\Repositories\Metadatas();
        return $Tainacan_Metadatas->fetch_by_collection( $this,  [], 'OBJECT'  );
    }

    /**
     * Set the collection name
     *
     * @param [string] $value
     * @return void
     */
    function set_name($value) {
        $this->set_mapped_property('name', $value);
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
     * @return void
     */
    function set_slug($value) {
        $this->set_mapped_property('slug', $value);
    }

    /**
     * Set collection order
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Set collection parent ID
     *
     * @param [integer] $value
     * @return void
     */
    function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Set collection description
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }
    
    /**
     * Set collection default order option
     *
     * @param [string] $value
     * @return void
     */
    function set_default_order($value) {
        $this->set_mapped_property('default_order', $value);
    }
    
    /**
     * Set collection default_orderby option
     *
     * @param [string] $value
     * @return void
     */
    function set_default_orderby($value) {
        $this->set_mapped_property('default_orderby', $value);
    }
    
    /**
     * Set collection columns option
     *
     * @param [string] $value
     * @return void
     */
    function set_columns($value) {
        $this->set_mapped_property('columns', $value);
    }
    
    /**
     * Set collection default_view_mode option
     *
     * @param [string] $value
     * @return void
     */
    function set_default_view_mode($value) {
        $this->set_mapped_property('default_view_mode', $value);
    }

}
