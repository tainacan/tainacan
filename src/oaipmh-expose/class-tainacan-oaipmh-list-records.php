<?php

namespace Tainacan\OAIPMHExpose;

use Tainacan\Repositories;
use Tainacan\Entities;

class OAIPMH_List_Records extends OAIPMH_Expose {

    protected $working_node;
    public $errors;
    public $xml_creater;
    public $restoken = '-';
    public $expirationdatetime;
    public $num_rows;
    public $cursor;
    public $deliveredrecords;
    public $from;
    public $until;
    public $sets;
    public $metadataPrefix;

    /**
     * @signature CONSTRUTOR
     *
     * getting the collection repository
     * @author: Eduardo
     */
    function __construct() {
        $this->collection_repository = Repositories\Collections::get_instance();
        $this->item_repository = Repositories\Items::get_instance();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function list_collections() {
        $collections = $this->collection_repository->fetch([]);

        $response = [];
        if($collections->have_posts()){
            while ($collections->have_posts()){
                $collections->the_post();

                $collection = new Entities\Collection($collections->post);
                array_push($response, $collection);
            }

            wp_reset_postdata();
        }

        return $response;
    }

    /**
     * main method return the items, filtered or not filtered
     *
     * @return array
     */
    public function get_items() {

        $items = array();
        $args = [
            'posts_per_page' => $this->MAXRECORDS,
            'paged' => $this->deliveredrecords == 0 ? 1 : ( $this->deliveredrecords / 100 ) + 1,
            'order' => 'DESC',
            'orderby' => 'ID',
            'post_status' => array( 'trash', 'publish' )
        ];

        if( !empty($this->sets) ){
            $collections = $this->list_collections();
            $collections_list = [];

            foreach ( $collections as $collection ) {
                if( !empty($this->sets) && !in_array($collection->get_id(), $this->sets)){
                    continue;
                }

                $collections_list[] = $collection;
            }

            $result = $this->item_repository->fetch($args, $collections_list, 'OBJECT');
        } else {
            $result = $this->item_repository->fetch($args, [], 'OBJECT');
        }

        if($result){
            foreach ($result as $item) {
                $item->collection = $item->get_collection();
                $items[] = $item;
            }
        }

        return $items;
    }


    /**
     * @signature - list_records
     * @param  array $param Os argumentos vindos da url (verb,until,from,set,metadataprefix,resumptioToken)
     * @return mostra o xml do list record desejado
     * @description - Metodo responsavel em mostrar o xml do list records, o metodo executado no controller
     * ele chama os demais metodos que fazem as verificacoes de erros
     * @author: Eduardo
     */
    public function list_records($data) {
        session_write_close();

        $this->config();
        $this->initiate_variables($data);

        $items = $this->get_items();
        $numRows = count($items);
        if($numRows == 0){
            $this->errors[] = $this->oai_error('noRecordsMatch');
            $this->oai_exit($data,$this->errors);
        }

        $this->verify_resumption_token($numRows);

        $this->xml_creater = new Xml_Response($data);

        foreach ( $items as $item ) {
            $collection = $item->collection;
            $identifier = 'oai:'.$this->repositoryIdentifier.':'. $item->get_id();
            $datestamp = $this->formatDatestamp($item->get_creation_date());
            $setspec = $collection->get_id();
            $cur_record = $this->xml_creater->create_record();
            $cur_header = $this->xml_creater->create_header($identifier, $datestamp, $setspec,$cur_record, ( $item->get_status() === 'trash' ) ? true : false );

            if( $item->get_status() !== 'trash' ){
                $this->working_node = $this->xml_creater->create_metadata($cur_record);
                $this->create_metadata_node( $item, $collection, $cur_record);
            }

        }

        //resumptionToken
        $this->add_resumption_token_xml($numRows);
        ob_start('ob_gzhandler');
        header($this->CONTENT_TYPE);
        
        if (isset($this->xml_creater)) {
            $this->xml_creater->display();
        } else {
            exit("There is a bug in codes");
        }

        ob_end_flush();
    }

    /**
     * Gets the current mapper object, if one was chosen by the user, false Otherwise
     */
    public function get_current_mapper() {
        $prefix = ($this->metadataPrefix === 'oai_dc') ? 'dublin-core' : $this->metadataPrefix;

        return \Tainacan\Mappers_Handler::get_instance()->get_mapper($prefix);
    }

    /**
     * @signature - create_metadata_node
     * @param  \Tainacan\Entities\Item $item
     * @param  wp_post $collection O objeto da colecao
     * @return Adciona no  noh <metadata> os valores necessarios
     * @description - Metodo responsavel realizar o povoamento no noh metadata
     * @author: Eduardo
     */
    protected function create_metadata_node( $item, $collection,$record_node = null) {
        $this->working_node = $this->xml_creater->addChild($this->working_node, 'oai_dc:dc');
        $this->working_node->setAttribute('xmlns:oai_dc', "http://www.openarchives.org/OAI/2.0/oai_dc/");
        $this->working_node->setAttribute('xmlns:dc', "http://purl.org/dc/elements/1.1/");
        $this->working_node->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $this->working_node->setAttribute('xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        $maps = $this->map_item_metadata($item);

        try{
            if ($maps) {
                foreach ($maps as $key => $val) {

                    if( $val && is_object($val) )
                        $this->xml_creater->addChild($this->working_node, $key, html_entity_decode($val->get_value_as_string()));
                    else
                        $this->xml_creater->addChild($this->working_node, $key, '');
                }
            }
        }catch(Exception $e){
            var_dump($e,$this->working_node,'dc:' . $key);
        }
    }

    /**
     * Gets an Item as input and return an array of ItemMetadataObjects
     * If a mapper is selected, the array keys will be the slugs of the metadata
     * declared by the mapper, in the same order.
     * Note that if one of the metadata is not mapped, this array item will be null
     */
    private function map_item_metadata(\Tainacan\Entities\Item $item) {
        $prefix = ($this->metadataPrefix === 'oai_dc') ? 'dublin-core' : $this->metadataPrefix;
        $mapper = $this->get_current_mapper();
        $metadata = $item->get_metadata();
        if (!$mapper) {
            return $metadata;
        }
        $pre = [];
        foreach ($metadata as $item_metadata) {
            $metadatum = $item_metadata->get_metadatum();
            $meta_mappings = $metadatum->get_exposer_mapping();
            if ( array_key_exists($prefix, $meta_mappings) ) {

                $pre[ $meta_mappings[$prefix] ] = $item_metadata;
            }
        }

        // reorder
        $return = [];
        foreach ( $mapper->metadata as $meta_slug => $meta ) {
            if ( array_key_exists($meta_slug, $pre) ) {
                $return[$meta_slug] = $pre[$meta_slug];
            } else {
                $return[$meta_slug] = null;
            }
        }

        return $return;

    }

    /**
     * @param $data
     */
    public function initiate_variables( $data ) {

        if ( isset($data['resumptionToken']) ) {

            if ( !file_exists($this->token_prefix . $data['resumptionToken']) ) {
                $this->errors[] = $this->oai_error('badResumptionToken', '', $data['resumptionToken']);
            } else {
                $readings = $this->readResumToken($this->token_prefix . $data['resumptionToken']);
                if ($readings == false) {
                    $this->errors[] = $this->oai_error('badResumptionToken', '', $data['resumptionToken']);
                } else {
                    list($this->deliveredrecords, $this->from, $this->until, $sets, $this->metadataPrefix) = $readings;
                    if($sets=='-'){
                        $this->sets = array();
                    }else{
                        $this->sets = explode(',', $sets);
                    }
                }
            }

        } else {
            $this->deliveredrecords = 0;

            if (isset($data['set'])) {
                if (is_array($data['set'])) {
                    $this->sets = $data['set'];
                } else {
                    $this->sets = array($data['set']);
                }
            } else {
                $this->sets = array();
            }

            if (isset($data['from'])) {
                $this->from = $data['from'];
            } else {
                $this->from = '-';
            }

            if (isset($data['until'])) {
                $this->until = $data['until'];
            } else {
                $this->until = '-';
            }

            $this->metadataPrefix =  $data['metadataPrefix'];
        }

        if(is_array($this->errors)&&count($this->errors)>0){
            $this->oai_exit($data,$this->errors);
        }
    }

    public function verify_resumption_token($numRows) {

        if ( $numRows === $this->MAXRECORDS ) {
            if( implode(',',$this->sets) ==  ''){
                $this->sets = '-';
            }else{
                $this->sets = implode(',',$this->sets);
            }
            $this->cursor = (int) $this->deliveredrecords + $this->MAXRECORDS;
            $this->restoken = $this->createResumToken($this->cursor, $this->from,$this->until,$this->sets, $this->metadataPrefix);
            $this->expirationdatetime = date("Y-m-d\TH:i:s\Z", time() * $this->token_valid);
        }
    }

    public function add_resumption_token_xml($numRows) {
        // ResumptionToken
        if ( $this->restoken != '-') {
            if ($this->expirationdatetime) {
                $this->xml_creater->create_resumpToken($this->restoken, $this->expirationdatetime, $numRows, $this->cursor);
            } else {
                $this->xml_creater->create_resumpToken('', null, $numRows, $this->deliveredrecords);
            }
        }
    }
}