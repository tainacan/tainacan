<?php


class Entity {
    
    
    function get_mapped_property($prop) {
        
        if (isset($this->$prop) && !empty($this->$prop))
            return $this->$prop;
        
        
        $map = $this->map_properties();
        
        if (!array_key_exists($prop, $map)) 
            return null;
        
        $mapped = $map[$prop];
        
        if ($mapped == 'meta') {
            return get_post_meta($this->WP_Post->ID, $prop, true);
        } else {
            return isset($this->WP_Post->$mapped) ? $this->WP_Post->$mapped : null;
                
        }
        
    }
    
    function set_mapped_property($prop, $value) {
        
        
        $this->$prop = $value;
        
        
    }
    
    
}