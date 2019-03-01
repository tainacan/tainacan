<?php

namespace Tainacan\OAIPMHExpose;

use Tainacan\Repositories;
use Tainacan\Entities;

class OAIPMH_Get_Record extends OAIPMH_Expose {

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
     * @param $params
     * @return bool|Entities\Item
     * @throws \Exception
     */
    public function get_item( $params ) {

        $id = str_replace('oai:'.$this->repositoryIdentifier.':','', $params['identifier']);
        $item = new Entities\Item( $id );

        if( !$item->get_id() ){
            return false;
        }

        $item->collection = $item->get_collection();
        return $item;
    }


    /**
     * @param $data
     * @throws \Exception
     */
    public function get_record( $data ) {
        session_write_close();

        $this->config();
        $this->initiate_variables( $data );

        $item = $this->get_item( $data );

        if( $item ){
            $formats = $this->get_metadata_formats();
            $prefix = ( $data['metadataPrefix'] === 'oai_dc') ? 'dublin-core' : $data['metadataPrefix'];

            if( empty($formats) || !in_array($prefix, $formats) ){
                $this->errors[] = $this->oai_error('cannotDisseminateFormat');
                $this->oai_exit($data,$this->errors);
            }
        }else{
            $this->errors[] = $this->oai_error('idDoesNotExist');
            $this->oai_exit( $data, $this->errors);
        }

        $this->xml_creater = new Xml_Response($data);

        $collection = $item->collection;
        $identifier = 'oai:'.$this->repositoryIdentifier.':'. $item->get_id();
        $datestamp = $this->formatDatestamp($item->get_creation_date());
        $setspec = $collection->get_id();
        $cur_record = $this->xml_creater->create_record();

        $this->xml_creater->create_header($identifier, $datestamp, $setspec,$cur_record, ( $item->get_status() === 'trash' ) ? true : false );

        if( $item->get_status() !== 'trash' ){
            $this->working_node = $this->xml_creater->create_metadata($cur_record);
            $this->create_metadata_node( $item, $collection, $cur_record);
        }

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
                        $this->xml_creater->addChild($this->working_node, $key, html_entity_decode($val->get_value()));
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
}