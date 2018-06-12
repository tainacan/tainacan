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
			'name' => 'Link relationship metadata',
			'callback' => 'close_taxonomies'
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
     * create taxonomies ( categories in tainacan old in first level )
     * next create the terms
     * 
     */
    public function create_taxonomies() {
        
        foreach ($this->get_taxonomies() as $taxonomy) {

            $tax = new Entities\Taxonomy();
            $tax->set_name( $category->name );
            $tax->set_description( $category->description );
            $tax->set_allow_insert('yes');
            $tax->set_status('publish');    

            if ($tax->validate()) {
                $tax = $this->tax_repo->insert($tax);

                $this->add_transient('tax_' . $term->term_id . '_id', $tax->get_id());
                $this->add_transient('tax_' . $term->term_id . '_name', $tax->get_name());

                if (isset($category->children) && $tax) {
                    $this->add_all_terms($tax, $category->children);
                }

            } else {
                $this->add_error_log('Error creating taxonomy ' . $category->name );
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
    * Method implemented by the child importer class to proccess each item
    * @return int
    */
    public function process_item( $index, $collection_id ){
    }

    /**
    * Method implemented by the child importer class to return the number of items to be imported
    * @return int
    */
    public function get_progress_total_from_source(){
    }

    /**
    * Method implemented by the child importer class to return the number of items to be imported
    * @return int
    */
    public function get_source_metadata(){
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
    protected function get_taxonomies(){

        $categories_link = $this->get_url() . $this->tainacan_api_address . "/categories";
        $categories = wp_remote_get($categories_link);
        $categories_array = $this->decode_request($categories);

        return ($categories_array) ? $categories_array : [];
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
    * create recursively the terms from tainacan OLD
    *
    * @param Entities\Taxonomy $taxonomy_father
    * @param array $children Array of categories from tainacan old 
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
        //TODO: create process to insert different types of metadata
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
            $type = "Category";
        } else if(strcmp($type, 'compound') === 0) {
            $type = "Compound";
        } else {
            $type = 'Text';
        } 

        return $type;
    }
}
