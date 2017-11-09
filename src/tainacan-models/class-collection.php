<?php

require_once(realpath(dirname(__FILE__)) . '/../tainacan-dao/class-collection-dao.php');

class Collection {
    const POSTTYPE = 'tainacan_collection';
    const DB_IDENTIFIER_META = '_db_identifier';

    private $name;
    private $description;
    private $collection_DAO;

    private $mapped_atributes = [
        'ID'             => 'ID',
        'name'           => 'post_title',
        'order'          => 'menu_order',
        'parent'         => 'parent',
        'description'    => 'post_content',
        'itens_per_page' => 'meta'
    ];

    /*
    * Default constructor
    */
    function __construct(){
        $this->collection_DAO = new CollectionDAO();

        if(!has_action( 'init', 'tainacan_register_post_type' )){
            add_action( 'init', array($this->collection_DAO, 'tainacan_register_post_type'));
        }
    }

    /*
    * Constructor that receive a WP_Post object
    * @param $wp_post
    * @type WP_Post
    */
    public static function __construct_with_wp_post($wp_post){
        $instance = new self();
        $instance->set_name($wp_post->post_title);
        $instance->set_description($wp_post->post_content);

        return $instance;
    }

    function set_name($name){
        $this->name = $name;
    }

    public function get_name(){
        return $this->name;
    }

    public function set_description($description){
        $this->description = $description;
    }

    public function get_description(){
        return $this->description;
    }

    public function get_mapped_atributes(){
        return $this->mapped_atributes;
    }

    public function get_collections($args){
        return $this->collection_DAO->tainacan_select($args);
    }

    public function get_collection($id){
        return $this->collection_DAO->tainacan_select($id);
    }

    public function insert_collection($collection){
        return $this->collection_DAO->tainacan_insert($collection);
    }
}

?>