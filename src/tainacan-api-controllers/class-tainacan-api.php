<?php 

require_once('class-tainacan-api-collection.php');

class Tainacan_API extends WP_REST_Controller {
    private $collection_API;
    

    function __construct(){
        $this->collection_API = new TainacanAPICollection();
    }

    public function insert_collection($name, $description){
        return $this->collection_API->insert_collection($name, $description);
    }

    public function get_collections($args){
        return $this->collection_API->get_collections($args);
    }

    public function get_collection($id){
        return $this->collection_API->get_collection($id);
    }
}

?>