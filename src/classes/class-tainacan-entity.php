<?php

namespace Tainacan;

class Entity {
    
    var $repository;
    var $errors = [];
    
    function get_mapped_property($prop) {
        
        global ${$this->repository};
        $map = ${$this->repository}->get_map();
        
        if (isset($this->$prop) && !empty($this->$prop)){
            return $this->$prop;
        }
        
        if (!array_key_exists($prop, $map)){
            return null;
        }
        
        $mapped = $map[$prop]['map'];
        
        if ( $mapped == 'meta') {
            $return = isset($this->WP_Post->ID) ? get_post_meta($this->WP_Post->ID, $prop, true) : null;
        } elseif ( $mapped == 'meta_multi') {
            $return = isset($this->WP_Post->ID) ? get_post_meta($this->WP_Post->ID, $prop, false) : null;
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
        
        global ${$this->repository};
        $map = ${$this->repository}->get_map();
        $valid = true;
        $this->reset_errors();
        
        foreach ($map as $prop => $mapped) {
            if (!$this->validate_prop($prop))
                $valid = false;
        }
        
        return $valid;
    }
    
    function validate_prop($prop) {
        global ${$this->repository};
        $map = ${$this->repository}->get_map();
        $mapped = $map[$prop];
        
        
        $return = true;
        
        if (
            isset($mapped['validation']) && 
            is_object($mapped['validation']) &&
            method_exists($mapped['validation'], 'validate')
        ) {
            $validation = $mapped['validation'];
            $prop_value = $this->get_mapped_property($prop);
            
            if (is_array($prop_value)) {
                foreach ($prop_value as $val) {
                    if (!$validation->validate($val)) {
                        //
                        $this->add_error('invalid', $prop . ' is invalid');
                        $return = false;
                    }
                }
            } else {
                if (!$validation->validate($prop_value)) {
                    //
                    $this->add_error('invalid', $prop . ' is invalid');
                    $return = false;
                }
            }
        }
        
        return $return;

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