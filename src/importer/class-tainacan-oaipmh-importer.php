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

    protected $tainacan_api_address, $wordpress_api_address, $actual_collection;

    /**
     * tainacan old importer construct
     */
    public function __construct($attributes = array()) {
        parent::__construct($attributes);

        $this->col_repo = \Tainacan\Repositories\Collections::get_instance();
        $this->items_repo = \Tainacan\Repositories\Items::get_instance();
        $this->metadata_repo = \Tainacan\Repositories\Metadata::get_instance();
        $this->item_metadata_repo = \Tainacan\Repositories\Item_Metadata::get_instance();

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
            $info = $this->requester( $this->get_url() . "?verb=ListRecords&metadataPrefix=oai_dc&set=" . $collection_id['source_id'] );
        } else {
            $token = $this->get_transient('resumptionToken');
            $info = $this->requester( $this->get_url() . "?verb=ListRecords&metadataPrefix=oai_dc&set=" . $collection_id['source_id'] . '&resumptionToken=' . $token);
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
            }

        } catch (Exception $e) {
            $this->add_log('error on read xml and get ');
            return false;
        }

        if( $xml->ListRecords ){
            $size = count($xml->ListRecords->record); // verifico o tamanho dos list record para o for

            for ($j = 0; $j < $size; $j++) {
                $record = $record = $xml->ListRecords->record[$j];
                $dc = $record->metadata->children("http://www.openarchives.org/OAI/2.0/oai_dc/");

                if ($record->metadata->Count() > 0 ) {
                    $metadata = $dc->children('http://purl.org/dc/elements/1.1/');
                    $tam_metadata = count($metadata);
                    for ($i = 0; $i < $tam_metadata; $i++) {

                        $value = (string) $metadata[$i];
                        $identifier = $this->get_identifier($metadata[$i]);
                        $record_processed['dc:' . $identifier ][] = $value;

                    }
                }
            }
        }

        if( $record_processed ){
            $records['records'][] = $record_processed;
            return $records;
        } else {
            $this->add_log('proccessing an item empty');
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

        if( isset($collection_xml->ListSets) ){
            foreach ($collection_xml->ListSets->set as $set) {

                $setSpec = (string) $set->setSpec;
                $setName =  (string) $set->setName;

                $collection = $this->create_collection( $setSpec, $setName );

                $metadata_map = $this->create_collection_metadata($collection);
                $total = intval($this->get_total_items_from_source($setSpec));
                $this->add_log('total in collection: ' . $total);

                $this->add_collection([
                    'id' => $collection->get_id(),
                    'mapping' => $metadata_map,
                    'total_items' =>ceil( $total / 100 ),
                    'source_id' => $setSpec
                ]);
            }
        }

        return false;
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
        $map = $collection_index['mapping'];

        foreach ( $records as $record ) {
            $item = new Entities\Item();

            if( $record ){
                foreach ( $record as $index => $value ){

                    if( in_array( $index, $map )){
                        $metadatum_id = array_search($index, $map );
                        $newMetadatum = new Entities\Metadatum($metadatum_id);

                        $item_metadata = new Entities\Item_Metadata_Entity( $item, $newMetadatum );

                        $unique = !$item_metadata->is_multiple();
                        $value_final = ( is_array($value) && $unique ) ? $value[0] : $value;
                        $item_metadata->set_value($value_final);

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
            }

        }

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
        $info = $this->requester( $this->get_url() . "?verb=ListRecords&metadataPrefix=oai_dc&set=" . $setSpec);

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

                return $cont;
            } elseif ( isset($xml->ListRecords) && isset($xml->ListRecords->resumptionToken) ){

                $resumptionToken_attributes = $xml->ListRecords->resumptionToken->attributes();
                foreach ($resumptionToken_attributes as $tag => $attribute) {
                    if ($tag == 'completeListSize') {
                        return (string) $attribute;
                    }
                }
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

                    $new_metadata = $Tainacan_Metadata->insert($metadatum);
                    $array_metadata[$new_metadata->get_id()] = $slug;
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

        $collections_link = $this->get_url() . "?verb=ListSets";
        $collections = $this->requester($collections_link);
        $collections_array = $this->decode_request($collections, $collections_link);

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



        $this->add_error_log('Error in fetch remote, expired the 10 requests limit ' . $url);
        $this->abort();
        return false;
    }
}