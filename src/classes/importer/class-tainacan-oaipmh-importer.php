<?php
namespace Tainacan\Importer;
use Tainacan;
use Tainacan\Entities;

class Oaipmh_Importer extends Importer {

    protected $steps = [
        [
            'name' => 'Create Collections',
            'progress_label' => 'Create Collections',
            'callback' => 'create_collections'
        ],
        [
            'name' => 'Import Items',
            'progress_label' => 'Import Items',
            'callback' => 'process_collections',
            'total' => 2
        ],
    ];

    protected $NAME_FOR_SETS = 'Sets';
    protected $tainacan_api_address, $wordpress_api_address, $actual_collection;
    protected $has_sets = true;
    protected $items_per_page = 100;

    /**
     * tainacan old importer construct
     */
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        $this->set_default_options([
            'delimiter' => ','
        ]);

        $this->col_repo = \Tainacan\Repositories\Collections::get_instance();
        $this->items_repo = \Tainacan\Repositories\Items::get_instance();
        $this->metadata_repo = \Tainacan\Repositories\Metadata::get_instance();
        $this->item_metadata_repo = \Tainacan\Repositories\Item_Metadata::get_instance();
        $this->tax_repo = \Tainacan\Repositories\Taxonomies::get_instance();
        $this->term_repo = \Tainacan\Repositories\Terms::get_instance();

        $this->remove_import_method('file');
        $this->add_import_method('url');

    }

    /**
     * Method implemented by the child importer class to proccess each item
     * @return int
     */
    public function process_item( $index, $collection_id ){
        $this->add_log('Proccess item index' . $index . ' in set ' . $collection_id['source_id'] );
        $records = [ 'records' => [] , 'collection_definition' => $collection_id ];
        $record_processed = [];

        if( $index === 0 ){

            if( $collection_id['source_id'] !== 'sets' && $this->has_sets ){
                $info = $this->requester( $this->get_url() . "?verb=ListRecords&metadataPrefix=oai_dc&set=" . $collection_id['source_id'] );
                $this->add_log('fetching ' . $this->get_url() . "?verb=ListRecords&metadataPrefix=oai_dc&set=" . $collection_id['source_id']);
            } else  {
                $info = $this->requester( $this->get_url() . "?verb=ListRecords&metadataPrefix=oai_dc" );
                $this->add_log('fetching ' . $this->get_url() . "?verb=ListRecords&metadataPrefix=oai_dc");
            }

        } else {
            $token = $this->get_transient('resumptionToken');
            $info = $this->requester( $this->get_url() . "?verb=ListRecords&resumptionToken=" . $token);
            $this->add_log('fetching ' . $this->get_url() . "?verb=ListRecords&resumptionToken=" . $token);
        }

        if( !isset($info['body']) ){
            $this->add_log('no answer');
            return false;
        }


        try {
            $xml = new \SimpleXMLElement($info['body']);

            if ( isset($xml->ListRecords) && isset($xml->ListRecords->resumptionToken) ){

                $resumptionToken = $xml->ListRecords->resumptionToken;
                if ($resumptionToken) {
                    $this->add_transient('resumptionToken',(string) $resumptionToken);
                }

                // if there is no total in resumption token and exists cursor
                // it will change dynamic the total of items

                $resumptionToken_attributes = $xml->ListRecords->resumptionToken->attributes();
                $real_total = $this->get_transient('total_general');

                foreach ($resumptionToken_attributes as $tag => $attribute) {

                    if ( $tag == 'cursor' && $real_total == ( (string) $attribute ) && !$this->hasCompleteSize( $resumptionToken_attributes ) ) {

                        $real_total = $real_total + intval($this->get_transient('items_per_page'));
                        $this->add_transient('total_general', $real_total);

                        $total = ( $this->get_transient('change_total') ) ?  $this->get_transient('change_total') : 1;

                        $this->add_transient('change_total', $total + 1);
                        break;
                    }
                }
            }

        } catch (Exception $e) {
            $this->add_log('error on read xml and get ');
            return false;
        }

        if( $xml->ListRecords ){
            $j = 0;

            while ( isset($xml->ListRecords->record[$j]) ) {
                $record = $record = $xml->ListRecords->record[$j];
                $dc = $record->metadata->children("http://www.openarchives.org/OAI/2.0/oai_dc/");
                $header = $record->header;

                $is_inserted = $this->get_transient($header->identifier);
                if( $is_inserted ){
                    continue;
                }

                if( $this->get_option('using_set') == 'taxonomy' && ( isset($header) && isset($header->setSpec) ) ){
                    foreach ($header->setSpec as $item ) {
                        $record_processed['sets'][] = (string) $item;
                    }
                }

                if ($record->metadata->Count() > 0 ) {
                    $metadata = $dc->children('http://purl.org/dc/elements/1.1/');
                    $tam_metadata = count($metadata);
                    for ($i = 0; $i < $tam_metadata; $i++) {

                        $value = (string) $metadata[$i];
                        $identifier = $this->get_identifier($metadata[$i]);
                        $record_processed['dc:' . $identifier ][] = $value;

                    }
                }

                if( $record_processed ){
                    $records['records'][] = $record_processed;
                    $record_processed = [];
                }

                $j++;
            }
        }

        if( $records['records'] ){
            return $records;
        } else {
            $this->add_log('proccessing an item empty or xml not found');
            return false;
        }
    }

    /**
     * create all collections and its metadata
     *
     */
    public function create_collections(){

        $this->add_log('Creating collections');
        $collection_xml = $this->fetch_collections();

        if( $collection_xml ){

            if( !$this->get_option('using_set') || $this->get_option('using_set') == 'collection' ){

                foreach ($collection_xml as $set ) {

                    $setSpec = (string) $set->setSpec;
                    $setName =  (string) $set->setName;

                    $collection = $this->create_collection( $setSpec, $setName );

                    $metadata_map = $this->create_collection_metadata($collection);
                    $total = intval($this->get_total_items_from_source($setSpec));
                    $this->add_log('total in collection: ' . $total);
                    $this->add_log('collection id ' . (string) $collection->get_id());

                    $this->add_collection([
                        'id' => $collection->get_id(),
                        'mapping' => $metadata_map,
                        'total_items' => ceil( $total / $this->items_per_page ),
                        'source_id' => $setSpec,
                    ]);
                }
            } else if( $this->get_option('using_set') == 'taxonomy') {

                $collection = $this->create_collection( 'set', $this->getRepoName() );
                $metadata_map = $this->create_collection_metadata($collection);
                $total = intval( $this->get_total_items_from_source(false) );
                $this->add_log('total in collection: ' . $total);
                $this->add_log('collection id ' . (string) $collection->get_id());

                $tax = new Entities\Taxonomy();
                $tax->set_name( $this->NAME_FOR_SETS );
                $tax->set_allow_insert('yes');
                $tax->set_status('publish');

                if ($tax->validate()) {

                    $is_tax_created = $this->get_transient('set_taxonomy_id');
                    if( $is_tax_created ){
                        $tax = new Entities\Taxonomy( $is_tax_created );
                    } else {
                        $tax = $this->tax_repo->insert($tax);
                        $this->add_transient('set_taxonomy_id', $tax->get_id());
                    }

                    $metadatum_set_id = $this->create_set_metadata( $collection->get_id(), $tax->get_id() );

                    if( $metadatum_set_id ){
                        $this->add_transient('set_metadatum_id', $metadatum_set_id);

                        $this->add_collection([
                            'id' => $collection->get_id(),
                            'mapping' => $metadata_map,
                            'total_items' =>ceil( $total / $this->items_per_page ),
                            'source_id' => 'sets',
                            'metadatum_id' => $metadatum_set_id
                        ]);
                    }

                    $this->add_log('Taxonomy ' . $tax->get_name() . ' created' );

                    foreach ($collection_xml as $set) {

                        $setSpec = (string)$set->setSpec;
                        $setName = (string)$set->setName;

                        $this->createTerms( $tax, $setName, $setSpec );
                    }

                } else {
                    $this->add_log('Error creating taxonomy Sets' );
                    $this->add_log($tax->get_errors());

                }

            }

        }
        // if there is no set
        else {
            $collection = $this->create_collection( 'set', $this->getRepoName() );
            $metadata_map = $this->create_collection_metadata($collection);
            $total = intval( $this->get_total_items_from_source(false) );
            $this->add_log('total in collection: ' . $total);
            $this->add_log('collection id ' . (string) $collection->get_id());

            $this->add_collection([
                'id' => $collection->get_id(),
                'mapping' => $metadata_map,
                'total_items' =>ceil( $total / $this->items_per_page ),
            ]);

            $this->has_sets = false;
        }

        $resumptionToken = $this->get_transient('collection_resump');
        if( $resumptionToken !== ''){
            return 1;
        } else {
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
        $records = $processed_item['records'];
        $collection_id = $processed_item['collection_definition'];
        $collection = new Entities\Collection($collection_id['id']);
        $map = $collection_id['mapping'];

        foreach ( $records as $record ) {
            $item = new Entities\Item();
            $item->set_status('publish');
            $item->set_collection( $collection );
            $item->set_title( ( isset($record['dc:title']) ) ? $record['dc:title'][0] : 'title' );
            $item->set_description(  '' );

            $this->add_log(  ( isset($record['dc:title']) ) ? $record['dc:title'][0] : 'title'   );
            if( $record && $item->validate() ){
                $insertedItem = $this->items_repo->insert( $item );

                if( isset($record['sets']) ){
                    $terms  = [];
                    $metadatum_set_id = $this->get_transient('set_metadatum_id');

                    foreach ($record['sets'] as $set) {
                        $term_id = $this->get_transient($set);

                        if( $term_id ) $terms[] = $term_id;
                    }

                    if( $metadatum_set_id && $terms ){
                        $newMetadatum = new Entities\Metadatum($metadatum_set_id);

                        $item_metadata = new Entities\Item_Metadata_Entity( $insertedItem, $newMetadatum );
                        $item_metadata->set_value($terms);

                        if( $item_metadata->validate() ){
                            $this->item_metadata_repo->insert( $item_metadata );
                        }
                    }

                    unset($record['sets']);
                }

                foreach ( $record as $index => $value ){

                    if( in_array( $index, $map ) && $insertedItem->get_id()){
                        $metadatum_id = array_search($index, $map );
                        $newMetadatum = new Entities\Metadatum($metadatum_id);

                        $item_metadata = new Entities\Item_Metadata_Entity( $insertedItem, $newMetadatum );

                        $unique = !$item_metadata->is_multiple();
                        $value_final = ( is_array($value) && $unique ) ? $value[0] : $value;
                        $item_metadata->set_value($value_final);

                        if( $item_metadata->validate() ){
                            $inserted = $this->item_metadata_repo->insert( $item_metadata );
                            // $this->add_log('Item Metadata inserted for item  ' .$item->get_title() . ' and metadata ' . $newMetadatum->get_name() );
                        } else {
                            $this->add_log( 'Error inserting metadatum' . $newMetadatum->get_name() );
                            $this->add_log( 'Values' . $value );
                            $this->add_log( $item_metadata->get_errors() );
                        }
                    }

                }
            } else {
                $this->add_log('item not inserted ');
            }

        }

        return isset($insertedItem) ? $insertedItem : false;
    }

    //protected functions

    /**
     * @signature - get_identifyier($metadata)
     * @param \SimpleXMLElement $metadata
     * @return string O identifier
     */
    protected function get_identifier($metadata) {
        $attributes = $metadata->attributes(); // atributos
        if ($attributes) {
            foreach ($attributes as $a => $b) {
                return $metadata->getName().'_'.(string) $b;
            }
        } else {
            return $metadata->getName();
        }
    }


    /**
     * Method implemented by the child importer class to return the number of items to be imported
     * @return int
     */
    public function get_total_items_from_source( $setSpec ) {

        if($setSpec)
            $info = $this->requester( $this->get_url() . "?verb=ListRecords&metadataPrefix=oai_dc&set=" . $setSpec);
        else
            $info = $this->requester( $this->get_url() . "?verb=ListRecords&metadataPrefix=oai_dc");

        if( !isset($info['body']) ){
            $this->add_log('ERROR');
            $this->add_error_log('Error in fetch remote total items');
            $this->abort();
            return false;
        }

        try {
            $xml = new \SimpleXMLElement($info['body']);

            if( isset($xml->ListRecords) && !isset($xml->ListRecords->resumptionToken) ){
                $cont = 0;
                foreach ($xml->ListRecords->record as $record) $cont++;

                $this->add_transient('total_general', (string) $cont );
                return $cont;
            } elseif ( isset($xml->ListRecords) && isset($xml->ListRecords->resumptionToken) ){

                $resumptionToken_attributes = $xml->ListRecords->resumptionToken->attributes();

                foreach ($resumptionToken_attributes as $tag => $attribute) {
                    if ($tag == 'completeListSize') {
                        $this->add_transient('total_general', (string) $attribute);
                        return (string) $attribute;
                    }
                }

                foreach ($resumptionToken_attributes as $tag => $attribute) {
                    if ($tag == 'cursor') {
                        $this->items_per_page = $attribute;
                        $this->add_transient('items_per_page', (string) $this->items_per_page);
                    }
                }

                // if the total is not found
                $this->add_transient('total_general', (string) $this->items_per_page);
                return $this->items_per_page;
            }
        } catch (Exception $e) {
            $this->add_log('ERROR');
            return 0;
        }

        return 0;
    }

    /**
     * create the collection in tainacan
     *
     * @return Entities\Collection
     */
    protected function create_collection( $setSpec, $setName ){
        $is_created = $this->get_transient('collection_' . $setSpec. '_name');
        if( $is_created ){
            $new_collection = new Entities\Collection( $is_created );
            return $new_collection;
        }

        $new_collection = new Entities\Collection();
        $new_collection->set_name($setName);
        $new_collection->set_status('publish');
        $new_collection->validate();


        if($new_collection->validate()){
            $new_collection =$this->col_repo->insert($new_collection);

            $this->add_log('Collection created: ' . $new_collection->get_name());

            if( $new_collection )
                $this->add_transient('collection_' . $setSpec. '_name', $new_collection->get_id());

            return $new_collection;
        } else{
            $this->add_error_log('Error creating collection ' . $setName );
            $this->add_error_log($new_collection->get_errors());
            $this->abort();
            return false;
        }

    }

    /**
     * @param $collection_object
     * @throws \ErrorException
     */
    protected function create_collection_metadata( $collection_object ){
        $Tainacan_Mappers = \Tainacan\Mappers_Handler::get_instance();
        $mapper_obj = $Tainacan_Mappers->check_class_name('dublin-core', true, $Tainacan_Mappers::MAPPER_CLASS_PREFIX);
        $mapper = new $mapper_obj;
        $array_metadata = [];

        $mapper_metadata = $mapper->metadata;
        if(is_array($mapper_metadata) ) {
            $id = $collection_object->get_id();

            $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
            foreach ($mapper_metadata as $slug => $mapper_metadatum) {
                if( array_key_exists('core_metadatum', $mapper_metadatum) ) {
                    $method = 'get_core_' . $mapper_metadatum['core_metadatum'] . '_metadatum';
                    if (method_exists($collection_object, $method)) {
                        $core_meta = $collection_object->$method();
                        if ( ! $core_meta ) {
                            $Tainacan_Metadata->register_core_metadata( $collection_object, true );
                            $core_meta = $collection_object->$method();
                        }
                        $_meta_mapping = $core_meta->get_exposer_mapping();
                        $_meta_mapping[$mapper->slug] = $slug;
                        $core_meta->set_exposer_mapping($_meta_mapping);
                        if ($core_meta->validate()) {
                            $new_metadata = $Tainacan_Metadata->insert($core_meta);
                            $array_metadata[$new_metadata->get_id()] = $slug;
                        }
                    }
                    continue;
                }

                $metadatum = new \Tainacan\Entities\Metadatum();
                if(
                    array_key_exists('metadata_type', $mapper_metadatum) &&
                    $mapper_metadatum['metadata_type'] != false &&
                    class_exists($mapper_metadatum['metadata_type'])
                ) {
                    $metadatum->set_metadata_type($mapper_metadatum['metadata_type']);
                } else {
                    $metadatum->set_metadata_type('Tainacan\Metadata_Types\Text');
                }
                $metadatum->set_name($mapper_metadatum['label']);
                $metadatum->set_semantic_uri($mapper->get_url($slug));
                $metadatum->set_exposer_mapping([
                    $mapper->slug => $slug
                ]);
                $metadatum->set_status('publish');
                $metadatum->set_collection_id($id);
                $metadatum->set_slug($slug);

                if($metadatum->validate()){

                    $metadatum_id_created = $this->get_transient('collection_' . $id . '_' . $slug );
                    if( $metadatum_id_created ){
                        $array_metadata[$metadatum_id_created] = $slug;
                    } else {
                        $new_metadata = $Tainacan_Metadata->insert($metadatum);
                        $array_metadata[$new_metadata->get_id()] = $slug;
                    }

                }
            }
        }

        return $array_metadata;
    }

    /**
     * return all taxonomies from tainacan old
     * @return array
     */
    protected function fetch_collections(){

        $collections_array = [];
        // block terms with same set spec
        $resumptionToken = $this->get_transient('collection_resump');
        if( $resumptionToken ){
            $collections_link = $this->get_url() . "?verb=ListSets&resumptionToken=" . $resumptionToken;
        } else {
            $collections_link = $this->get_url() . "?verb=ListSets";
        }

        $collections = $this->requester($collections_link);
        $xml = $this->decode_request($collections, $collections_link);

        if( isset($xml->ListSets->set) ) {
            foreach ($xml->ListSets->set as $set) {

                $collections_array[] = $set;
            }
        }

        if( isset($xml->ListSets) && isset($xml->ListSets->resumptionToken) ){
            $this->add_transient('collection_resump',(string) $xml->ListSets->resumptionToken);
        } else {
            $this->add_transient('collection_resump', (string) $xml->ListSets->resumptionToken);
        }

        // TODO: verify if exists resumption token

        return ($collections_array) ? $collections_array : [];
    }

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

            try {
                $xml = new \SimpleXMLElement($result['body']);
                return $xml;
            } catch (Exception $e) {
                return false;
            }
        }

        $this->add_error_log('Error in fetch remote');
        $this->abort();
        return false;
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
                $this->add_log('Error in fetch remote' . $link);
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



        $this->add_error_log('Error in fetch remote, expired the 10 requests limit ' . $link);
        $this->abort();
        return false;
    }

    /**
     * @param $taxonomy_father
     * @param $name
     * @param $slug
     * @return bool
     */
    public function createTerms( $taxonomy_father, $name, $slug ){
        $new_term = new Entities\Term();
        $new_term->set_taxonomy($taxonomy_father->get_db_identifier());
        $new_term->set_name($name);

        // block terms with same set spec
        $map = $this->get_transient($slug);

        if($map){
            return false;
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
            $this->add_transient($slug, $inserted_term->get_id());
            $this->add_log('Added term: ' . $inserted_term->get_name() . ' in tax: ' . $taxonomy_father->get_name());
            return true;
        }

        return false;
    }

    /**
     * @param $collection_id
     * @param $taxonomy_id
     * @return bool|int
     * @throws \Exception
     */
    public function create_set_metadata( $collection_id, $taxonomy_id ){
        $newMetadatum = new Entities\Metadatum();

        $name = $this->NAME_FOR_SETS;
        $type = 'Taxonomy';

        $newMetadatum->set_name($name);
        $newMetadatum->set_metadata_type('Tainacan\Metadata_Types\\'.$type);
        $newMetadatum->set_collection_id( (isset($collection_id)) ? $collection_id : 'default');
        $newMetadatum->set_status('publish');
        $newMetadatum->set_metadata_type_options(['taxonomy_id' => $taxonomy_id ]);
        $newMetadatum->set_multiple('yes');

        if($newMetadatum->validate()){
            $is_meta_created = $this->get_transient('set_metadatum_id');
            if( $is_meta_created ){
                $inserted_metadata =  new Entities\Metadatum($is_meta_created);

                $this->add_log('Metadata get: ' . $inserted_metadata->get_name());
            } else {
                $inserted_metadata = $this->metadata_repo->insert( $newMetadatum );

                $this->add_log('Metadata created: ' . $inserted_metadata->get_name());
            }

            return $inserted_metadata->get_id();
        } else{
            return false;
        }
    }

    public function getRepoName(){
        $info = $this->requester( $this->get_url() . "?verb=Identify");

        if( !isset($info['body']) ){
            $this->add_log('ERROR on get repo name');
            $this->add_error_log('Error in fetch remote total items');
            $this->abort();
            return __('Imported Repo');
        } else {

            try {
                $xml = new \SimpleXMLElement($info['body']);

                if( isset($xml->Identify) && isset($xml->Identify->repositoryName) && !empty($xml->Identify->repositoryName) ){
                    return (string) $xml->Identify->repositoryName;
                }
            } catch (Exception $e) {
                return __('Imported Repo');
            }

            return __('Imported Repo');

        }
    }

    /**
     * @param $attributes
     * @return bool
     */
    public function hasCompleteSize( $attributes ) {
        foreach ( $attributes as $tag => $attribute ) {
            if ( $tag == 'completeListSize' ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the current value to build the progress bar and give feedback to the user
     * on the background process that is running the importer.
     *
     * It does so by comparing the "size" attribute with the $in_step_count class attribute
     * where size indicates the total size of iterations the step will take and $this->in_step_count
     * is the current iteration.
     *
     * For the step with "process_items" as a callback, this method will look for the the $this->collections array
     * and sum the value of all "total_items" attributes of each collection. Then it will look for
     * $this->get_current_collection and $this->set_current_collection_item to calculate the progress.
     *
     * The value must be from 0 to 100
     *
     * If a negative value is passed, it is assumed that the progress is unknown
     */
    public function get_progress_value() {
        $current_step = $this->get_current_step();
        $steps = $this->get_steps();
        $value = -1;

        if ( isset($steps[$current_step]) ) {
            $step = $steps[$current_step];

            if ($step['callback'] == 'process_collections') {

                $totalItems = 0;
                $currentItem = $this->get_current_collection_item();
                $current_collection = $this->get_current_collection();
                $collections = $this->get_collections();

                foreach ($collections as $i => $col) {
                    if ( isset($col['total_items']) && is_numeric($col['total_items']) ) {
                        $totalItems += intval($col['total_items']);
                        if ($i < $current_collection) {
                            $currentItem += $col['total_items'];
                        }
                    }
                }

                if ($totalItems > 0) {
                    $totalItems = ($this->get_transient('change_total')) ? $this->get_transient('change_total') : $totalItems;
                    $value = round( ($currentItem/$totalItems) * 100 );
                }


            } else {

                if ( isset($step['total']) && is_numeric($step['total']) && $step['total'] > 0 ) {
                    $total = ($this->get_transient('change_total')) ? $this->get_transient('change_total') : $step['total'];

                    $current = $this->get_in_step_count();
                    $value = round( ($current/$total) * 100 );
                }

            }


        }
        return $value;
    }

    public function options_form(){
        ob_start();
        ?>
        <div class="field">
            <label class="label"><?php _e('Create set as', 'tainacan'); ?></label>
            <span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
						 </span>
					</a>
					<vdiv class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Create set as', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('Choose the action to manipulate sets', 'tainacan'); ?></p>
						</div>
					</vdiv>
			</span>
            <div class="control is-clearfix">
                <div class="select">
                    <select name="using_set">
                        <option value="collection" <?php selected($this->get_option('using_set'), 'collection'); ?> ><?php _e('Collections', 'tainacan'); ?></option>
                        <option value="taxonomy" <?php selected($this->get_option('using_set'), 'taxonomy'); ?> ><?php _e('Taxonomies', 'tainacan'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}