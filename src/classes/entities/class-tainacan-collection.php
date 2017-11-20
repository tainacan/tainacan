<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class that represetns the Collection entity
 */
class Collection extends \Tainacan\Entity  {
    const POST_TYPE = 'tainacan-collections';    
      
    /**
     * Create an instance of Collection
     * @param integer|\WP_Post optional $which Collection ID or a WP_Post object for existing collections. Leave empty to create a new collection.
     */
    function __construct($which = 0) {
        
        $this->repository = 'Tainacan_Collections';
        
        if (is_numeric($which) && $which > 0) {
            $post = get_post($which);
            
            if ($post instanceof \WP_Post) {
                $this->WP_Post = get_post($which);
            }
            
        } elseif ($which instanceof \WP_Post) {
            $this->WP_Post = $which;
        } else {
            $this->WP_Post = new \StdClass();
        }
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
    function tainacan_register_post_type() {
        $cpt_labels = array(
            'name'               => 'Item',
            'singular_name'      => 'Item',
            'add_new'            => 'Adicionar Novo',
            'add_new_item'       => 'Adicionar Item',
            'edit_item'          => 'Editar',
            'new_item'           => 'Novo Item',
            'view_item'          => 'Visualizar',
            'search_items'       => 'Pesquisar',
            'not_found'          => 'Nenhum Item encontrado',
            'not_found_in_trash' => 'Nenhum Item encontrado na lixeira',
            'parent_item_colon'  => 'Item acima:',
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
        );
        
        if (post_type_exists($this->get_db_identifier())) 
            unregister_post_type($this->get_db_identifier());
        
        register_post_type($cpt_slug, $args);
    }

    /**
     * Get the collection ID
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
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
     * Get collection items per page option
     *
     * @return integer
     */
    function get_itens_per_page() {
        return $this->get_mapped_property('itens_per_page');
    }
    
    /**
     * Get collection DB identifier
     *
     * This identifier is used to register the collection post type and never changes, even if you change the name and the slug of the collection.
     *
     * @return string
     */
    function get_db_identifier() {
        return $this->get_id() ? 'tnc_col_' . $this->get_id() : false;
    }
    
    /**
     * Get collection metadata.
     *
     * Returns an array of \Entity\Metadata objects, representing all the metadata of the collection.
     *
     * @see \Tainacan\Repositories\Metadatas->get_metadata_by_collection()
     *
     * @return array
     */
    function get_metadata() {
        $Tainacan_Metadatas = new \Tainacan\Repositories\Metadatas();
        return $Tainacan_Metadatas->get_metadata_by_collection($this);
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
     * Set collection itens per page option
     *
     * @param [integer] $value
     * @return void
     */
    function set_itens_per_page($value) {
        $this->set_mapped_property('itens_per_page', $value);
    }
}