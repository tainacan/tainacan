<?php


class Tainacan_Repository {
    
    function find_by($prop, $value) {
        
        $map = $this->map;
        
        if (!key_exists($prop, $map)){
            return null;
        }
    }
        
}