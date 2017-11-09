<?php

require_once(realpath(dirname(__FILE__)) .'/../tainacan-models/class-collection.php');

class CollectionHelper {
    private $collection;

    public function __construct(){
        $this->collection = new Collection();
    }

    public function get_collection($id){
        return $this->collection->get_collection($id);
    }

    public function get_collections($args = []){
        // $args = array_merge([
        //     'post_type'      => Collection::POSTTYPE,
        //     'posts_per_page' => -1,
        //     'post_status'    => 'publish',
        // ], $args);

        return $this->collection->get_collections($args);
    }

    public function insert_collection($name, $description){
        $collection = new Collection();

        $collection->set_name($name);
        $collection->set_description($description);
        
        return $collection->insert_collection($collection);
    }
    
}

?>