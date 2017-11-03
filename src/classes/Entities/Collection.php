<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}





class TainacanCollection extends Entity {
    
    
    
    function __construct($which = 0) {
        
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
    
    function map_properties() {
        return [
            'ID' => 'ID',
            'name' => 'post_title',
            'order' => 'menu_order',
            'parent' => 'parent',
            'description' => 'post_content',
            'itens_per_page' => 'meta'
        ];
        
    }
    
    
    
    // Getters
    //
    function get_id() {
        return $this->get_mapped_property('ID');
    }
    function get_name() {
        return $this->get_mapped_property('name');
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
    
    // Setters
    // 
    
    function set_name($value) {
        return $this->set_mapped_property('name', $value);
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