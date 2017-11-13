<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}





class Tainacan_Log extends Tainacan_Entity {
    
    function __construct($which = 0) {
        
        $this->repository = 'Tainacan_Logs';
        
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

    // Getters
    //
    function get_id() {
        return $this->get_mapped_property('ID');
    }
    function get_title() {
        return $this->get_mapped_property('title');
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
    
    /**
     * Return User Id of who make the action
     * @return int User Id of logged action
     */
    function get_user_id() {
    	return $this->get_mapped_property('user_id');
    }
    


    
    // Setters
    // 
    
    function set_title($value) {
        return $this->set_mapped_property('title', $value);
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

    
}