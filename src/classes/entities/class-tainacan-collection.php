<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}





class Tainacan_Collection extends Tainacan_Entity  {
    
    
    function __construct($which = 0) {
        
        $this->repository = 'Tainacan_Collections';
        
        if (is_numeric($which) && $which > 0) {
            $post = get_post($which);
            if ($post instanceof WP_Post) {
                $this->WP_Post = get_post($which);
            }
            
        } elseif ($which instanceof WP_Post) {
            $this->WP_Post = $which;
        } else {
            $this->WP_Post = new StdClass();
        }
        
    }
    
    function register_post_type() {
        $cpt_labels = array(
            'name' => 'Item',
            'singular_name' => 'Item',
            'add_new' => 'Adicionar Novo',
            'add_new_item' =>'Adicionar Item',
            'edit_item' => 'Editar',
            'new_item' => 'Novo Item',
            'view_item' => 'Visualizar',
            'search_items' => 'Pesquisar',
            'not_found' => 'Nenhum Item encontrado',
            'not_found_in_trash' => 'Nenhum Item encontrado na lixeira',
            'parent_item_colon' => 'Item acima:',
            'menu_name' => $this->get_name()
        );
        
        $cpt_slug = $this->get_db_identifier();
        
        $args = array(
            'labels' => $cpt_labels,
            'hierarchical' => true,
            //'supports' => array('title'),
            //'taxonomies' => array(self::TAXONOMY),
            'public' => true,
            'show_ui' => tnc_enable_dev_wp_interface(),
            'show_in_menu' => tnc_enable_dev_wp_interface(),
            //'menu_position' => 5,
            //'show_in_nav_menus' => false,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => [
                'slug' => $this->get_slug()
            ],
            'capability_type' => 'post',
        );
        
        if (post_type_exists($this->get_db_identifier())) 
            unregister_post_type($this->get_db_identifier());
        
        register_post_type($cpt_slug, $args);
    }

    // Getters
    //
    function get_id() {
        return $this->get_mapped_property('ID');
    }
    function get_name() {
        return $this->get_mapped_property('name');
    }
    function get_slug() {
        return $this->get_mapped_property('slug');
    }
    function get_order() {
        return $this->get_mapped_property('order');
    }
    function get_parent() {
        return $this->get_mapped_property('parent');
    }
    function get_description() {
        return $this->get_mapped_property('description');
    }
    function get_itens_per_page() {
        return $this->get_mapped_property('itens_per_page');
    }
    
    // special Getters
    // 
    
    function get_db_identifier() {
        return $this->get_id() ? 'tnc_col_' . $this->get_id() : false;
    }
    
    // metadata
    function get_metadata() {
        $Tainacan_Metadatas = new Tainacan_Metadatas();
        return $Tainacan_Metadatas->get_metadata_by_collection($this);
    }
    
    // Setters
    // 
    
    function set_name($value) {
        return $this->set_mapped_property('name', $value);
    }
    function set_slug($value) {
        return $this->set_mapped_property('slug', $value);
    }
    function set_order($value) {
        return $this->set_mapped_property('order', $value);
    }
    function set_parent($value) {
        return $this->set_mapped_property('parent', $value);
    }
    function set_description($value) {
        return $this->set_mapped_property('description', $value);
    }
    function set_itens_per_page($value) {
        return $this->set_mapped_property('itens_per_page', $value);
    }

}