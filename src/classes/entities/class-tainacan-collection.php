<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Representa a entidade Collection
 * @author
 */
class Collection extends \Tainacan\Entity  {
    const POST_TYPE = 'tainacan-collections';    
      
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
     * Registra novo tipo de post (post type)
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
     * Retorna ID da coleção
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }
    /**
     * Retorna post_title da coleção
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }

    /**
     * Retorna slug da coleção
     *
     * @return string
     */
    function get_slug() {
        return $this->get_mapped_property('slug');
    }

    /**
     * Retorna ordenação da coleção
     *
     * @return integer
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Retorna id do parent da coleção
     *
     * @return integer
     */
    function get_parent() {
        return $this->get_mapped_property('parent');
    }

    /**
     * Retorna descrição da coleção
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }

    /**
     * Retorna quantidade de itens por página
     *
     * @return integer
     */
    function get_itens_per_page() {
        return $this->get_mapped_property('itens_per_page');
    }
    
    /**
     * Retorna identificador imutável de referência ao post type
     *
     * @return string
     */
    function get_db_identifier() {
        return $this->get_id() ? 'tnc_col_' . $this->get_id() : false;
    }
    
    /**
     * Retorna os metadados da coleção
     *
     * @return array
     */
    function get_metadata() {
        $Tainacan_Metadatas = new \Tainacan\Repositories\Metadatas();
        return $Tainacan_Metadatas->get_metadata_by_collection($this);
    }
    
    /**
     * Atribui valor ao nome da coleção
     *
     * @param [string] $value
     * @return void
     */
    function set_name($value) {
        $this->set_mapped_property('name', $value);
    }

    /**
     * Atribui valor ao slug da coleção
     *
     * @param [string] $value
     * @return void
     */
    function set_slug($value) {
        $this->set_mapped_property('slug', $value);
    }

    /**
     * Atribui valor ao tipo de ordenação da coleção
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Atribui valor ao parent da coleção
     *
     * @param [integer] $value
     * @return void
     */
    function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Atribui valor à descrição da coleção
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Define a quantidade de itens por página na coleção
     *
     * @param [integer] $value
     * @return void
     */
    function set_itens_per_page($value) {
        $this->set_mapped_property('itens_per_page', $value);
    }
}