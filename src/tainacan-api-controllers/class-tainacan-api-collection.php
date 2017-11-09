<?php

require_once(realpath(dirname(__FILE__)) .'/../tainacan-helpers/class-collection-helper.php');

class TainacanAPICollection{
    private $collection_helper;

    function __construct(){
        $this->collection_helper = new CollectionHelper();
    }

    public function insert_collection($name, $description){
        return $this->collection_helper->insert_collection($name, $description);
    }

    public function get_collections($args){
        return $this->collection_helper->get_collections($args);
    }

    public function get_collection($id){
        return $this->collection_helper->get_collection($id);
    }
}

?>