<?php

namespace Tainacan\Importer;
use \Tainacan\Entities;

class Old_Tainacan extends Importer{

    protected $steps = [
		
		[
            'name' => 'Create Taxonomies',
            'progress_label' => 'Creating taxonomies',
			'callback' => 'create_taxonomies'
        ],
        [
            'name' => 'Create Repo Metadata',
            'progress_label' => 'Create Repo Metadata',
			'callback' => 'create_repo_metadata'
		],
		[
            'name' => 'Create Collections',
            'progress_label' => 'Create Collections',
			'callback' => 'create_collections'
        ],
		[
            'name' => 'Import Items',
            'progress_label' => 'Import Items',
			'callback' => 'process_collections'
        ],
        [
            'name' => 'Link Relationships',
            'progress_label' => 'Link Relationships',
            'callback' => 'link_relationships',
            'total' => 5
		]
		
	];
    
    protected $tainacan_api_address, $wordpress_api_address, $actual_collection;

    /**
     * tainacan old importer construct
     */
    public function __construct($attributes = array()) {
		parent::__construct($attributes);

        $this->tax_repo = \Tainacan\Repositories\Taxonomies::get_instance();
		$this->col_repo = \Tainacan\Repositories\Collections::get_instance();
		$this->items_repo = \Tainacan\Repositories\Items::get_instance();
        $this->metadata_repo = \Tainacan\Repositories\Metadata::get_instance();
        $this->term_repo = \Tainacan\Repositories\Terms::get_instance();
        $this->item_metadata_repo = \Tainacan\Repositories\Item_Metadata::get_instance();
        
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

        if(!$this->get_url()){
            $this->add_error_log('Site url not found');
            $this->abort();
            return false;
        }
        
        $this->add_log('Creating taxonomies');
		
		foreach ($this->get_taxonomies() as $taxonomy) {

            $tax = new Entities\Taxonomy();
            $tax->set_name( $taxonomy->name );
            $tax->set_description( $taxonomy->description );
            $tax->set_allow_insert('yes');
            $tax->set_status('publish');    

            if ($tax->validate()) {
                $tax = $this->tax_repo->insert($tax);
				
				$this->add_log('Taxonomy ' . $tax->get_name() . ' created, id from Old'. $taxonomy->term_id );

                $this->add_transient('tax_' . $taxonomy->term_id . '_id', $tax->get_id());
                $this->add_transient('tax_' . $taxonomy->term_id . '_name', $tax->get_name());

                if (isset($taxonomy->children) && $tax) {
                    $this->add_all_terms($tax, $taxonomy->children);
                }

            } else {
                $this->add_log('Error creating taxonomy ' . $taxonomy->name );
                $this->add_log($tax->get_errors());
                
            }

        }
		
		return false;
    }
    
    /**
     * create the repository metadata which each collection inherits by default
     * 
     */
    public function create_repo_metadata(){

        $this->add_log('Creating repository metadata');
		
		foreach ($this->get_repo_metadata() as $metadata) {

            if (isset($metadata->slug) && strpos($metadata->slug, 'socialdb_property_fixed') === false) {
                $metadatum_id = $this->create_metadata( $metadata );  
            } elseif ( strpos($metadata->slug, 'socialdb_property_fixed_tags') !== false ){
                $metadatum_id = $this->create_metadata( $metadata );     
            }

        }

        $this->add_log('FInished repository metadata');
        return false;
    }

    /**
     * create all collections and its metadata
     * 
     */
    public function create_collections(){

        $this->add_log('Creating collections');
		
		foreach ($this->fetch_collections() as $collection) {
            $map = [];
            $this->add_log(memory_get_usage());

            if ( isset($collection->post_title) && $collection->post_status === 'publish') {

                $collection_id = $this->create_collection( $collection );
                foreach( $this->get_collection_metadata( $collection->ID ) as $metadatum_old ) {

                    if (isset($metadatum_old->slug) && strpos($metadatum_old->slug, 'socialdb_property_fixed') === false) {
                        $metadatum_id = $this->create_metadata( $metadatum_old, $collection_id );

                        if( $metadatum_id ){
                            $map[$metadatum_id] = $metadatum_old->id;          
                        }
                        
                    } else if( isset($metadatum_old->slug) && strpos($metadatum_old->slug, 'socialdb_property_fixed_tags') !== false
                        && isset($metadatum_old->type) && strpos($metadatum_old->type, 'checkbox') !== false
                    ){
                        $metadatum_id = $this->create_metadata( $metadatum_old, $collection_id );
                        $this->add_log('Creating tag');
                        if( $metadatum_id ){
                            $map[$metadatum_id] = $metadatum_old->id;          
                        }
                    }

                }

                if( isset($collection->ID) ) {
                    $this->add_collection([
                        'id' => $collection_id,
                        'mapping' => $map,
                        'total_items' => intval($this->get_total_items_from_source($collection->ID)),
                        'source_id' => $collection->ID
                    ]);
                }
            }

        }

        return false;
    }

    /**
    * Method responsible for links all relationships metadata
    * @return int
    */
    public function link_relationships(){
        $collections = $this->col_repo->fetch([], 'OBJECT');
        $this->add_log('Linking relationships');

        if( $collections && is_array( $collections ) ){

            foreach( $collections as $collection ){ // loop collections
                $map = $this->get_transient('collection_' . $collection->get_id() . '_relationships'); 
                
                if(!$map){
                    return false;
                }

                foreach( $map as $metadatum_id => $items ){ // all relations in collection 
                    $newMetadatum = new Entities\Metadatum($metadatum_id);

                    $first_index_id = key($items); 
                    $collection_id = $this->get_transient('item_' . $items[$first_index_id] . '_collection'); 

                    $newMetadatum->set_metadata_type_options(['collection_id' => $collection_id ]);

                    if($newMetadatum->validate()){
                        $this->metadata_repo->update( $newMetadatum );
                        $this->add_log('Relationship ' . $newMetadatum->get_name() . ' updated');
                    }

                    reset($items);
                    foreach( $items as $item_id => $value_old ){ // all values
                        $value_new = $this->get_transient('item_' . $value_old . '_id'); 
                        $item = new Entities\Item($item_id);
                        $item_metadata = new Entities\Item_Metadata_Entity( $item, $newMetadatum );

                        $item_metadata->set_value($value_new);

                        if( $item_metadata->validate() ){
                            $this->item_metadata_repo->insert( $item_metadata );
                        }
                    }

                }
            }
        }

        return false;
    }

    /**
    * Method implemented by the child importer class to proccess each item
    * @return int
    */
    public function process_item( $index, $collection_id ){
        $args = array(
            'timeout'     => 30,
            'redirection' => 30,
        );

        $page = intval( $index ) + 1; 

        $this->add_log('Proccess item index' . $index . ' in collection OLD ' . $collection_id['source_id'] );

        $url_to_fetch = $this->get_url() . $this->tainacan_api_address . "/collections/". 
                            $collection_id['source_id']."/items?includeMetadata=1&filter[items_per_page]=1&filter[page]=" . $page
                            . "&filter[order_by]=ID&filter[order]=ASC";

        $info = $this->requester( $url_to_fetch, $args );                    
        $info = json_decode($info['body']);

        if( !isset( $info->items ) ){

            if( isset($info->code) && $info->code === 'empty_search' ){
                return true;
            }

            $this->add_error_log('Error in fetch remote (' . $url_to_fetch . ')');
            $this->add_error_log(serialize($collection_id));
            $this->abort();
            return false;
        }
        
        $the_item = null;

        foreach( $info->items as $item ){
            $the_item = $item;
        }

        if( isset($the_item) && isset($the_item->item) && !empty($the_item->item) ){
            $item_Old = $the_item;
            $this->add_log('item found ', $the_item->item->ID );  
           return [ 'item' => $item_Old, 'collection_definition' => $collection_id ];
        } else {
            $this->add_error_log('fetching remote ' . $url_to_fetch);
            $this->add_error_log('proccessing an item empty');
			$this->abort();
            return false;
        }
    }

    /**
     * insert processed item from source to Tainacan
     *
     * @param array $processed_item Associative array with metadatum source's as index with
     *                              its value or values
     * @param integet $collection_index The index in the $this->collections array of the collection the item is beeing inserted into
     * 
     * @return Tainacan\Entities\Item Item inserted
     */
    public function insert( $processed_item, $collection_index ) {
        $this->items_repo->disable_logs();
		$collection_id = $processed_item['collection_definition'];
        $item_Old = $processed_item['item']->item;

        $collection = new Entities\Collection($collection_id['id']);
        $item = new Entities\Item();

        // get the id if exists
        $id = $this->get_transient('item_' . $item_Old->ID . '_id');

        if( $id && is_numeric( $id ) ) {
            $this->add_log('Updating item ' . $item_Old->post_title );
            $item = new Entities\Item( $id );
        }

        $item->set_title( $item_Old->post_title );
        $this->add_log('Begin insert ' . $item_Old->ID . ': ' . $item_Old->post_title . ' in collection ' . $collection_id['id'] );

        $item->set_description( (isset($item_Old->post_content)) ? $item_Old->post_content : '' );
        $item->set_status('publish');

        $item->set_collection( $collection );

        if( $item->validate() ){
            $insertedItem = $this->items_repo->insert( $item );
            $this->add_transient('item_' . $item_Old->ID . '_id', $insertedItem->get_id()); // add reference for relations
            $this->add_transient('item_' . $item_Old->ID . '_collection', $collection_id['id']); // add collection for relations

            if( $insertedItem->get_id() && is_array($processed_item['item']->metadata) ){
                $this->add_log('Item ' . $insertedItem->get_id() . ': ' . $insertedItem->get_title() . ' inserted. ID in source: ' . $item_Old->ID);

                $this->add_item_metadata(  $insertedItem, $processed_item['item']->metadata, $collection_id );

                //inserting files
                $this->insert_files( $item_Old, $insertedItem );
            }

            return $insertedItem;
        } else {
            $this->add_error_log( 'Error inserting item' );
            $this->add_error_log( $item->get_errors() );
            return false;
        }
    }

    /**
    * Method responsible to insert item metadata
    */
    public function add_item_metadata( $item, $metadata_old, $collection_id ){
        $relationships = [];
		$this->item_metadata_repo->disable_logs();

        foreach( $metadata_old as $metadatum ){

            if( isset($metadatum->id) && array_search($metadatum->id,$collection_id['mapping']) ){
                $new_tainacanid = array_search($metadatum->id,$collection_id['mapping']);
                $newMetadatum = new Entities\Metadatum($new_tainacanid);

                $item_metadata = new Entities\Item_Metadata_Entity( $item, $newMetadatum );
               
                // avoid blank metadatum

                if( isset($metadatum->empty) ){
                    continue;
                }

                if( isset($metadatum->values) && empty($metadatum->values) ){
                    continue;
                }

                if( is_array($metadatum->values) && empty( array_filter($metadatum->values) ) ){
                    continue;
                }

                $unique = !$item_metadata->is_multiple();
                $value = ( is_array($metadatum->values) && $unique ) ? $metadatum->values[0] : $metadatum->values;

                if( in_array($metadatum->type,['text', 'textarea', 'numeric', 'date']) ){

                    if($metadatum->type === 'date'){
                        
                        if(is_array($value)){
                            $values = [];

                            foreach( $value as $day){
                                $v = explode('/',$day);

                                $v[1]= ( $v[1] < 10 ) ? '0'.$v[1] : $v[1];
                                $v[0]= ( $v[0] < 10 ) ? '0'.$v[0] : $v[0];     

                                $values[] = $v[2] . '-' . $v[1] . '-' . $v[0];
                            }   
                            
                            
                        } else {
                            $v = explode('/',$value);

                            $v[1]= ( $v[1] < 10 ) ? '0'. $v[1] : $v[1];
                            $v[0]= ( $v[0] < 10 ) ? '0'. $v[0] : $v[0];  

                            $values = $v[2] . '-' . $v[1] . '-' . $v[0];
                        }

                        $value = $values;
                    }

                    $item_metadata->set_value($value);

                } else if( $metadatum->type === 'item' ){ // RELATIONSHIPS
                    
                    /**
                     * save the values to allow set the correct collection
                     * in metadata option in next step
                     */
                    $relationships[$new_tainacanid][$item->get_id()] = $value; 

                } else {

                    if( is_array($value) ) {
                        $values = [];

                        foreach( $value as $cat){
                            $id = $this->get_transient('term_' . $cat . '_id');

                            if( $id )
                                $values[] = intval($id);
                        }     
                    } else {

                        $id = $this->get_transient('term_' . $value . '_id');
                        if( $id )
                            $values = intval($id);

                    }

                    $item_metadata->set_value($values);
                }

                if( $item_metadata->validate() ){
                    $inserted = $this->item_metadata_repo->insert( $item_metadata );
                    $this->add_log('Item Metadata inserted for item  ' .$item->get_title() . ' and metadata ' . $newMetadatum->get_name() );
                } else {
                    $this->add_log( 'Error inserting metadatum' . $newMetadatum->get_name() );
                    $this->add_log( 'Values' . $value );
                    $this->add_log( $item_metadata->get_errors() );
                }
            }

        }

        $this->add_transient('collection_' . $collection_id['id'] . '_relationships', $relationships ); // add reference for relations
    }

    /**
    * Method implemented by the child importer class to return the number of items to be imported
    * @return int
    */
    public function get_total_items_from_source( $collection_id ) {
        $args = array(
            'timeout'     => 30,
            'redirection' => 30,
        );

        $info = $this->requester( $this->get_url() . $this->tainacan_api_address . "/collections/".$collection_id."/items",  $args );

        if( !isset($info['body']) ){
            $this->add_error_log($result->get_error_message());
            $this->add_error_log('Error in fetch remote total items');
			$this->abort();
            return false;
        }

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
        $args = array(
            'timeout'     => 30,
            'redirection' => 30,
        ); 

        $info = wp_remote_get( $this->get_url() . $this->tainacan_api_address . "/collections/".$collection_id."/items?includeMetadata=1&filter[page]=" . $page,  $args );  
        
        if( !isset($info['body']) ){
            $this->add_error_log($result->get_error_message());
            $this->add_error_log('Error in fetch remote first page item');
			$this->abort();
            return false;
        }
        
        $info = json_decode($info['body']);

        while( isset($info->items) && count( $info->items ) > 0  ){

            foreach( $info->items as $item){
                $items[] = $item;
            }

            $page++;
            $info = wp_remote_get( $this->get_url() . $this->tainacan_api_address . "/collections/".$collection_id."/items?includeMetadata=1&filter[page]=" . $page, $args );                    
            $info = json_decode($info['body']);

            if( !isset($info['body']) ){
                $this->add_error_log($result->get_error_message());
                $this->add_error_log('Error in fetch remote ' . $page . ' page item');
                $this->abort();
                return false;
            }
        } 
        
        return $items;
    }



    // AUX functions

    /**
    * decode request from wp_remote
    * @return array/bool
    */
    protected function decode_request($result, $url){
        if (is_wp_error($result)) {

            $this->add_error_log($result->get_error_message());
            $this->add_error_log('Error in fetch remote' . $url);
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
        $collections = $this->requester($collections_link);
        $collections_array = $this->decode_request($collections, $collections_link);

        return ($collections_array) ? $collections_array : [];
    }

    /**
    * return all taxonomies from tainacan old
    * @return array
    */
    protected function get_taxonomies(){

        $taxonomies_link = $this->get_url() . $this->tainacan_api_address . "/categories";
        $taxonomies = $this->requester($taxonomies_link);
        $taxonomies_array = $this->decode_request($taxonomies, $taxonomies_link);

        return ($taxonomies_array) ? $taxonomies_array : [];
    }

    /**
    * return all repository metadata from tainacan old
    * @return array
    */
    protected function get_repo_metadata(){

        $repository_meta_link = $this->get_url() . $this->tainacan_api_address . "/repository/metadata?includeMetadata=1";
        $repo_meta = $this->requester($repository_meta_link);
        $repo_meta_array = $this->decode_request($repo_meta, $repository_meta_link);

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
        $collection = $this->requester($metadata_link);

        $collection_tabs = $this->decode_request($collection, $metadata_link);
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
     * executes the request 
     */
    protected function requester( $link ){
        $has_response = false;
        $requests = 0;

        $args = array(
            'timeout'     => 60,
            'redirection' => 30,
            'sslverify'   => false
        );

        $this->add_log('fetching init  ' . $link );
        $result = wp_remote_get($link, $args);

        while( !$has_response ){
               
            if (is_wp_error($result)) {

                $this->add_log($result->get_error_message());
                $this->add_log('Error in fetch remote' . $url);
                $this->add_log('request number ' . $requests);
    
            } else if (isset($result['body'])){
                $this->add_log('fetch OK  ');
                return $result;
            }

            if( $requests > 10 ){
                break;
            }

            if( $requests > 3 ){
                $this->add_log('taking a moment to breathe, waiting for ' . ( $requests * 10 ) . ' seconds ' );
                sleep( $requests * 10 ); 
            }

            $args = array(
                'timeout'     => 60,
                'redirection' => 30,
                'sslverify'   => false
            );
    
            $result = wp_remote_get($link, $args);

            $requests++;
            $this->add_log('going to  ' . $requests );
        }

        

        $this->add_error_log('Error in fetch remote, expired the 10 requests limit ' . $url);
        $this->abort();
        return false;
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

        $this->add_log('adding terms');
		
		foreach ($children as $term) {

            $new_term = new Entities\Term();
            $new_term->set_taxonomy($taxonomy_father->get_db_identifier());
            $new_term->set_name($term->name);
            $new_term->set_description($term->description);

            if($term_father){
                $new_term->set_parent($term_father->get_id());
            }
            
            // block terms with same name and parent in taxonomy
            if( get_term_by( 'name', $term->name, $taxonomy_father->get_db_identifier()) ){
                continue;
            }
            
            if ($new_term->validate()) {
                $inserted_term = $this->term_repo->insert($new_term);
            } else {
                $this->add_log( implode(',', $new_term->get_errors()) );
                return false;
            }
            

            if (is_wp_error($inserted_term)) {

                $this->add_log($inserted_term->get_error_message());
    
            } else {
                $this->add_log('Added term: ' . $inserted_term->get_name() . ' in tax: ' . $taxonomy_father->get_name());
                $this->add_log('Added term before id ' . $term->term_id );
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
        $this->add_log('Creating metadata' . $meta->name);

        $newMetadatum = new Entities\Metadatum();
        $meta = $node_metadata_old;

        $name = $meta->name;
        $type = $this->define_type($meta->type);

        $newMetadatum->set_name($name);
        $newMetadatum->set_metadata_type('Tainacan\Metadata_Types\\'.$type);
        $newMetadatum->set_collection_id( (isset($collection_id)) ? $collection_id : 'default');
        $newMetadatum->set_status('publish');

        if(strcmp($type, "Taxonomy") === 0){
            $taxonomy_id = $meta->metadata->taxonomy;

            $related_taxonomy = $this->get_transient('tax_' . $taxonomy_id . '_id');
            if ($related_taxonomy) {
                $newMetadatum->set_metadata_type_options(['taxonomy_id' => $related_taxonomy]);
            } else {
                $this->add_log('Taxonomy ID not found: ' . $taxonomy_id);
                $this->add_log('Skipping creating metadata ' . $name . ' taxonomy ID is required ' );
                return false;
			}

        } else if(strcmp($type, "Relationship") === 0){
            $relation_id = $meta->metadata->object_category_id;
            $related_taxonomy = $this->get_transient('tax_' . $relation_id . '_id');
            $related_name = $this->get_transient('tax_' . $relation_id . '_name');

            if(isset($related_taxonomy)){
                $newMetadatum->set_metadata_type_options(['collection_id' => $related_taxonomy]);
            } else {
                $this->add_log('Related Collection ID not found: ' . $relation_id);
                $this->add_log('Skipping creating metadata ' . $name . ' Related Collection ID is required ' );
                return false;
			}
        } else if(strcmp($type, "Compound") === 0){
             
            if( isset( $meta->metadata->children ) ){
                foreach( $meta->metadata->children as $child ){
                    $this->create_metadata( $child, $collection_id);
                }
            }

        }

        /*Properties of metadatum*/
        if(isset($meta->metadata)){

            if(!empty($meta->metadata->default_value)){
                $newMetadatum->set_default_value($meta->metadata->default_value);
            }

            if(!empty($meta->metadata->cardinality)){

                if($meta->metadata->cardinality === 'n'){
                    $newMetadatum->set_multiple('yes');
                }

            }
        }

        if($newMetadatum->validate()){
            $inserted_metadata = $this->metadata_repo->insert( $newMetadatum );
			
			$this->add_log('Metadata created: ' . $inserted_metadata->get_name());
			
            $this->add_transient('metadata_' . $inserted_metadata->get_id() . '_id', $inserted_metadata->get_id());

            if(isset( $related_name) ){
                $this->add_transient('relation_' . $meta->id . '_name', $related_name);
            }

            return $inserted_metadata->get_id();
        } else{ 
            $this->add_log('Error creating metadata ' . $name . ' in collection ' . $collection_id);
            $this->add_log($newMetadatum->get_errors());
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
			
			$this->add_log('Collection created: ' . $new_collection->get_name());

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
        } else if(strcmp($type, 'tree') === 0 || strcmp($type, 'selectbox') || strcmp($type, 'checkbox')) {
            $type = "Taxonomy";
        } else if(strcmp($type, 'compound') === 0) {
            $type = "Compound";
        } else {
            $type = 'Text';
        } 

        return $type;
    }

    /**
     * create attachments, document and thumb from old
     * 
     * @param string $node_old 
     * 
     * @return string the class name
     */
    private function insert_files( $node_old, $item ){
        if( isset( $node_old->attachments ) && $node_old->attachments ){
            $TainacanMedia = \Tainacan\Media::get_instance();
            $types = ['audio','video','image'];

            foreach( $types as $type){
                if( isset( $node_old->attachments->$type ) ){
                    
                    foreach( $node_old->attachments->$type as $attach){
                        $TainacanMedia->insert_attachment_from_url($attach->url, $item->get_id());
                    }
                }
            }
        }

        if( isset( $node_old->thumbnail ) && $node_old->thumbnail ){
            $TainacanMedia = \Tainacan\Media::get_instance();
            $id = $TainacanMedia->insert_attachment_from_url( $node_old->thumbnail, $item->get_id());
            $item->set__thumbnail_id( $id );
        }

        if( isset( $node_old->content_tainacan ) && $node_old->content_tainacan ){
            $TainacanMedia = \Tainacan\Media::get_instance();
            
            if( isset($node_old->content_tainacan->guid) ){
                $id = $TainacanMedia->insert_attachment_from_url( $node_old->content_tainacan->guid, $item->get_id());

                if( $id ){
                    $item->set_document( $id );
                    $item->set_document_type( 'attachment' );
                    $this->add_log('Document imported from ' . $node_old->content_tainacan->guid);
                } 
            } else if( isset($node_old->type_tainacan) && in_array( $node_old->type_tainacan, ['audio','video','image']) ){
                    $item->set_document( $node_old->content_tainacan );
                    $item->set_document_type( 'url' );
                    $this->add_log('URL imported from ' . $node_old->content_tainacan);
            }
            
        }

        if( $item->validate() )
            $this->items_repo->update($item);
    }
}
