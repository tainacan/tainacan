<?php


class Tainacan_Entity {
    
    var $repository;
    var $errors = [];
    
    function get_mapped_property($prop) {
        
        global ${$this->repository};
        $map = ${$this->repository}->map;
        
        if (isset($this->$prop) && !empty($this->$prop)){
            return $this->$prop;
        }
        
        if (!array_key_exists($prop, $map)){
            return null;
        }
        
        $mapped = $map[$prop]['map'];
        
        if ( $mapped == 'meta') {
            $return = get_post_meta($this->WP_Post->ID, $prop, true);
        } elseif ( $mapped == 'meta_multi') {
            $return = get_post_meta($this->WP_Post->ID, $prop, false);
        } elseif ( $mapped == 'termmeta' ){
            $return = get_term_meta($this->WP_Term->term_id, $prop, true);
        } elseif ( isset( $this->WP_Post )) {
            $return = isset($this->WP_Post->$mapped) ? $this->WP_Post->$mapped : null;
        } elseif ( isset( $this->WP_Term )) {
            $return = isset($this->WP_Term->$mapped) ? $this->WP_Term->$mapped : null;
        }
        
        if (empty($return) && isset($map[$prop]['default']) && !empty($map[$prop]['default'])){
            $return = $map[$prop]['default'];
        }
            
        return $return;
    }
    
    function set_mapped_property($prop, $value) {
        $this->$prop = $value;
    }
    
    function save() {
        
        // validate
        
        global ${$this->repository};
        
        return ${$this->repository}->insert($this); 
    }

    function validate() {
        return true;
    }
    
    function get_errors() {
        return $this->errors;
    }
    
    function add_error($type, $message) {
        $this->errors[] = [$type => $message];
    }
    
    function reset_errors() {
        $this->errors = [];
    }
        
}