<?php

namespace Tainacan\Importer;
use \Tainacan\Entities;

class Old_Tainacan extends Importer{

    protected $manual_mapping = true;
    protected $manual_collection = true;

    protected $steps = [
		
		[
			'name' => 'Create Taxonomies',
			'callback' => 'create_taxonomies'
        ],
        [
			'name' => 'Create Repo Metadata',
			'callback' => 'create_repo_metadata'
		],
		[
			'name' => 'Create Collections',
			'callback' => 'create_collections'
        ],
		[
			'name' => 'Import Items',
			'callback' => 'process_collections'
		],
		[
			'name' => 'Finalize',
			'callback' => 'finish_processing'
		]
		
	];
    
    protected $tainacan_api_address, $wordpress_api_address, $actual_collection;

    /**
     * tainacan old importer construct
     */
    public function __construct(){

        parent::__construct();

        $this->tax_repo = \Tainacan\Repositories\Taxonomies::get_instance();
		$this->col_repo = \Tainacan\Repositories\Collections::get_instance();
		$this->items_repo = \Tainacan\Repositories\Items::get_instance();
        $this->metadata_repo = \Tainacan\Repositories\Metadata::get_instance();
        $this->term_repo = \Tainacan\Repositories\Terms::get_instance();
        
        $this->remove_import_method('file');
        $this->add_import_method('url');
        $this->tainacan_api_address = "/wp-json/tainacan/v1";
        $this->wordpress_api_address = "/wp-json/wp/v2";

    }

    /**
     * create taxonomies ( taxonomies in tainacan old in first level )
     * next create the terms
     * 
     */
    public function create_taxonomies() {
        
        foreach ($this->get_taxonomies() as $taxonomy) {

            $tax = new Entities\Taxonomy();
            $tax->set_name( $taxonomy->name );
            $tax->set_description( $taxonomy->description );
            $tax->set_allow_insert('yes');
            $tax->set_status('publish');    

            if ($tax->validate()) {
                $tax = $this->tax_repo->insert($tax);

                $this->add_transient('tax_' . $taxonomy->term_id . '_id', $tax->get_id());
                $this->add_transient('tax_' . $taxonomy->term_id . '_name', $tax->get_name());

                if (isset($taxonomy->children) && $tax) {
                    $this->add_all_terms($tax, $taxonomy->children);
                }

            } else {
                $this->add_error_log('Error creating taxonomy ' . $taxonomy->name );
                $this->add_error_log($tax->get_errors());
                $this->abort();
                return false;
                
            }

        }
		
		return false;
    }
    
    /**
     * create the repository metadata which each collection inherits by default
     * 
     */
    public function create_repo_metadata(){

        foreach ($this->get_repo_metadata() as $metadata) {

            if (isset($metadata->slug) && strpos($metadata->slug, 'socialdb_property_fixed') === false) {
               $metadatum_id = $this->create_metadata( $metadata );  
            }

        }
    }

    /**
     * create all collections and its metadata
     * 
     */
    public function create_collections(){

        foreach ($this->fetch_collections() as $collection) {
            $map = [];

            if ( isset($collection->post_title) && $collection->post_status === 'publish') {

                $collection_id = $this->create_collection( $collection );
                foreach( $this->get_collection_metadata( $collection->ID ) as $metadatum_old ) {

                    $metadatum_id = $this->create_metadata( $metadatum_old, $collection_id );
                    $map[$metadatum_id] = $metadatum_old->id;

                }

                $this->add_collection([
                    'id' => $collection_id,
                    'map' => $map,
                    'total_items' => $this->get_total_items_from_source( $collection->ID ),
                    'source_id' => $collection->ID,
                    'items' => $this->get_all_items( $collection->ID ) 
                ]);
            }

        }
    }

    /**
    * Method implemented by the child importer class to proccess each item
    * @return int
    */
    public function link_relationships(){
        $args = [];

       foreach( $this->metadata_repo->fetch($args, 'OBJECT' ) as $metadatum ){
           //var_dump($metadatum->get_metadata_type());
       }

        // TODO: get all imported relationships and find the collection target
    }

    /**
    * Method implemented by the child importer class to proccess each item
    * @return int
    */
    public function process_item( $index, $collection_id ){
        var_dump($collection_id['items'][$index]);
    }

    /**
    * Method implemented by the child importer class to return the number of items to be imported
    * @return int
    */
    public function get_total_items_from_source( $collection_id ) {
        $info = wp_remote_get( $this->get_url() . $this->tainacan_api_address . "/collections/".$collection_id."/items" );
        $info = json_decode($info['body']);

        if( isset($info->found_items) ){
            return $info->found_items;
        } else {
            return 0;
        }
        
    }

    /**
    * Method that retrieves all items
    * @return int
    */
    public function get_all_items( $collection_id ) {
        $page = 1;
        $items = [];

        $info = wp_remote_get( $this->get_url() . $this->tainacan_api_address . "/collections/".$collection_id."/items?includeMetadata=1&filter[items_per_page]=50&filter[page]=" . $page );
        $info = json_decode($info['body']);

        while( isset($info->items) && count( $info->items ) > 0  ){

            foreach( $info->items as $item){
                $items[] = $item;
            }

            $page++;
            $info = wp_remote_get( $this->get_url() . $this->tainacan_api_address . "/collections/".$collection_id."/items?includeMetadata=1&filter[items_per_page]=50&filter[page]=" . $page );
            $info = json_decode($info['body']);
        } 
        
        return $items;
    }



    // AUX functions

    /**
    * decode request from wp_remote
    * @return array/bool
    */
    protected function decode_request($result){
        if (is_wp_error($result)) {

            $this->add_error_log($result->get_error_message());
            $this->add_error_log('Error in fetch remote');
			$this->abort();
            return false;

        } else if (isset($result['body'])){
            return json_decode($result['body']);
        }

        $this->add_error_log('Error in fetch remote');
        $this->abort();
        return false;
    }

    /**
    * return all taxonomies from tainacan old
    * @return array
    */
    protected function fetch_collections(){

        $collections_link = $this->get_url() . $this->tainacan_api_address . "/collections";
        $collections = wp_remote_get($collections_link);
        $collections_array = $this->decode_request($collections);

        return ($collections_array) ? $collections_array : [];
    }

    /**
    * return all taxonomies from tainacan old
    * @return array
    */
    protected function get_taxonomies(){

        $taxonomies_link = $this->get_url() . $this->tainacan_api_address . "/categories";
        $taxonomies = wp_remote_get($taxonomies_link);
        $taxonomies_array = $this->decode_request($taxonomies);

        return ($taxonomies_array) ? $taxonomies_array : [];
    }

    /**
    * return all repository metadata from tainacan old
    * @return array
    */
    protected function get_repo_metadata(){

        $repository_meta_link = $this->get_url() . $this->tainacan_api_address . "/repository/metadata?includeMetadata=1";
        $repo_meta = wp_remote_get($repository_meta_link);
        $repo_meta_array = $this->decode_request($repo_meta);

        return ($repo_meta_array) ? $repo_meta_array : [];
    }

    /**
    * return all metadata from collection
    * @param $collection_id

    * @return array
    */
    protected function get_collection_metadata( $collection_id ){
        $metadata = [];
        $metadata_link = $this->get_url() . $this->tainacan_api_address . "/collections/".$collection_id."/metadata?includeMetadata=1";
        $collection = wp_remote_get($metadata_link);

        $collection_tabs = $this->decode_request($collection);
        if($collection_tabs){

            foreach ($collection_tabs as $tab) {
                if($tab->{'tab-properties'}){
                    $metadata = array_merge($metadata, $tab->{'tab-properties'});
                }
            }

        }

        return ($metadata) ? $metadata : [];
    }

    /**
    * create recursively the terms from tainacan OLD
    *
    * @param Entities\Taxonomy $taxonomy_father
    * @param array $children Array of taxonomies from tainacan old 
    * @param (optional) int $term_father the ID of father
    *
    * @return array
    */
    protected function add_all_terms($taxonomy_father, $children, $term_father = null){

        foreach ($children as $term) {

            $new_term = new Entities\Term();
            $new_term->set_taxonomy($taxonomy_father->get_db_identifier());
            $new_term->set_name($term->name);
            $new_term->set_description($term->description);

            if($term_father){
                $new_term->set_parent($term_father->get_id());
            }

            $inserted_term = $this->term_repo->insert($new_term);

            if (is_wp_error($inserted_term)) {

                $this->add_error_log($inserted_term->get_error_message());
                $this->abort();
                return false;
    
            }

            /*Insert old tainacan id*/
            $this->add_transient('term_' . $term->term_id . '_id', $inserted_term->get_id());
            $this->add_transient('term_' . $term->term_id . '_name', $inserted_term->get_name());
            $this->add_transient('term_' . $term->term_id . '_tax', $taxonomy_father->get_db_identifier());

            if(isset($term->children)){
                $this->add_all_terms($taxonomy_father, $term->children, $inserted_term);
            }
        }

    }

    /**
     * create the a Metadatum in tainacan
     * 
     * @return int $metadatum_id 
     */
    protected function create_metadata( $node_metadata_old, $collection_id = null){
        $newMetadatum = new Entities\Metadatum();
        $meta = $node_metadata_old;

        $name = $meta->name;
        $type = $this->define_type($meta->type);

        $newMetadatum->set_name($name);
        $newMetadatum->set_metadata_type('Tainacan\Metadata_Types\\'.$type);
        $newMetadatum->set_collection_id( (isset($collection_id)) ? $collection_id : 'default');

        if(strcmp($type, "Category") === 0){
            $taxonomy_id = $meta->metadata->taxonomy;

            $related_taxonomy = $this->get_transient('tax_' . $taxonomy_id . '_id');
            if ($related_taxonomy) {
                $newMetadatum->set_metadata_type_options(['taxonomy_id' => $related_taxonomy]);
            }

        } else if(strcmp($type, "Relationship") === 0){
            $relation_id = $meta->metadata->object_category_id;
            $related_taxonomy = $this->get_transient('tax_' . $relation_id . '_id');
            $related_name = $this->get_transient('tax_' . $relation_id . '_name');

            if(isset($related_taxonomy)){
                $newMetadatum->set_metadata_type_options(['collection_id' => $related_taxonomy]);
            }
        } else if(strcmp($type, "Compound") === 0){
             
            if( isset( $meta->metadata->children ) ){
                foreach( $meta->metadata->children as $child ){
                    $this->create_metadata( $node_metadata_old, $collection_id);
                }
            }

        }

        /*Properties of metadatum*/
        if(isset($meta->metadata)){
            if($meta->metadata->required == 1){
                $newMetadatum->set_required(true);
            }

            if(!empty($meta->metadata->default_value)){
                $newMetadatum->set_default_value($meta->metadata->default_value);
            }

            if(!empty($meta->metadata->cardinality)){

                if($meta->metadata->cardinality > 1){
                    $newMetadatum->set_multiple('yes');
                }

            }
        }

        if($newMetadatum->validate()){
            $inserted_metadata = $this->metadata_repo->insert( $newMetadatum );

            if(isset( $related_name) ){
                $this->add_transient('relation_' . $inserted_metadata->get_id() . '_name', $related_name);
            }

            return $inserted_metadata->get_id();
        } else{ 
            $this->add_error_log('Error creating metadata ' . $name );
            $this->add_error_log($newMetadatum->get_errors());
            $this->abort();
            return false;
        } 
    }

    /**
     * create the collection in tainacan
     * 
     * @return int $metadatum_id 
     */
    protected function create_collection( $node_collection ){
        $new_collection = new Entities\Collection();
        $new_collection->set_name($node_collection->post_title);
        $new_collection->set_status('publish');
        $new_collection->validate();
       

        if($new_collection->validate()){
            $new_collection =$this->col_repo->insert($new_collection);

            if( $new_collection )
                $this->add_transient('collection_' . $node_collection->ID . '_name', $new_collection->get_id());

            return $new_collection->get_id();
        } else{ 
            $this->add_error_log('Error creating collection ' . $node_collection->post_title );
            $this->add_error_log($new_collection->get_errors());
            $this->abort();
            return false;
        } 

    }
    
    /**
     * Define the class to create in new Tainacan
     * 
     * @param string $type The type from tainacan old
     * 
     * @return string the class name
     */
    private function define_type($type){
        $type = strtolower($type);
        $tainacan_types = ['text', 'textarea', 'numeric', 'date'];

        if (in_array($type, $tainacan_types)) {
            $type = ucfirst($type);
        } else if(strcmp($type, 'autoincrement') === 0) {
            $type = "Numeric";
        } else if(strcmp($type, 'item') === 0) {
            $type = "Relationship";
        } else if(strcmp($type, 'tree') === 0) {
            $type = "Taxonomy";
        } else if(strcmp($type, 'compound') === 0) {
            $type = "Compound";
        } else {
            $type = 'Text';
        } 

        return $type;
    }
}
