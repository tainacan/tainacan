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
     * @return array
     */
    public function get_items() {
        $collections = $this->list_collections();
        $items = array();
        $args = [];

        foreach ($collections as $collection) {

            if( !empty($this->sets) && !in_array($collection->get_id(), $this->sets)){
                continue;
            }

            if( $this->from !== '-' ){
                $args['date_query']['after'] = strtotime( $this->checkDateFormat($this->from));
            }
            if( $this->until !== '-' ){
                $args['date_query']['before'] = strtotime( $this->checkDateFormat($this->until));
            }

            $result = $this->item_repository->fetch($args, [$collection], 'OBJECT');

            if($result){
                foreach ($result as $item) {
                    $item->collection = $collection;
                    $items[] = $item;
                }
                $items = array_merge($items, $result);
            }

        }

        return $items;
    }

    public function limit_items_without_set($items){
        $conter = 0;
        $result = array();
        foreach ($items as $item) {
            if( $conter >= $this->deliveredrecords && ($this->deliveredrecords+$this->MAXRECORDS) > $conter ){
                $result_objects[] = $item;
            }
            $conter++;
        }
        return $result;
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

        $objects = $this->get_items();
        $numRows = count($objects);
        if($numRows==0){
            $this->errors[] = $this->oai_error('noRecordsMatch');
            $this->oai_exit($data,$this->errors);
        }

        $items = $this->limit_items_without_set($objects);
        $this->verify_resumption_token($numRows);

        $this->xml_creater = new Xml_Response($data);

        foreach ( $items as $item ) {
            $collection = $item->collection;
            $identifier = 'oai:'.$this->repositoryIdentifier.':'. $item->get_id();
            $datestamp = $this->formatDatestamp($item->get_creation_date());
            $setspec = $collection->get_id();
            $cur_record = $this->xml_creater->create_record();
            $cur_header = $this->xml_creater->create_header($identifier, $datestamp, $setspec,$cur_record);
            $this->working_node = $this->xml_creater->create_metadata($cur_record);
            //$this->create_metadata_node($object,$collection,$cur_record);
            // $this->insert_xml($object);
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
     * @signature - get_mapping_value
     * @param  wp_post $object O objeto do tipo post
     * @param  wp_post $collection O objeto da colecao
     * @return array Com o mapeamento com seu valor respectivo
     * @description - Metodo responsavel em buscar o mapeamento especifico do objeto com seu valor
     * @author: Eduardo
     */
    public function get_mapping_value($object,$collection) {
        $maps = [];
        $files = [];

        return $result;
    }

    /**
     * @signature - create_metadata_node
     * @param  wp_post $object O objeto do tipo post
     * @param  wp_post $collection O objeto da colecao
     * @return Adciona no  noh <metadata> os valores necessarios
     * @description - Metodo responsavel realizar o povoamento no noh metadata
     * @author: Eduardo
     */
    protected function create_metadata_node($object,$collection,$record_node = null) {
        $this->working_node = $this->xml_creater->addChild($this->working_node, 'oai_dc:dc');
        $this->working_node->setAttribute('xmlns:oai_dc', "http://www.openarchives.org/OAI/2.0/oai_dc/");
        $this->working_node->setAttribute('xmlns:dc', "http://purl.org/dc/elements/1.1/");
        $this->working_node->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $this->working_node->setAttribute('xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        $maps = $this->get_mapping_value($object,$collection);

        try{
            if ($maps['metadata']) {
                foreach ($maps['metadata'] as $map) {
                    if (isset($map['attribute_value'])) {
                        $node = $this->xml_creater->addChild($this->working_node, 'dc:' . $map['tag'], $map['value']);
                        $node->setAttribute($map['attribute_name'], $map['attribute_value']);
                        //$this->add_value_metadata($map['tag'], $map['value'], $map['attribute_value'], $map['attribute_name']);
                    } else {
                        $this->xml_creater->addChild($this->working_node, 'dc:' . $map['tag'], html_entity_decode($map['value']));
                    }
                }
            }
        }catch(Exception $e){
            var_dump($e,$this->working_node,'dc:' . $map['tag']);
        }
    }

    /**
     * @param $data
     */
    public function initiate_variables( $data ) {

        if ( isset($data['resumptionToken']) ) {

            if ( !file_exists(TOKEN_PREFIX . $data['resumptionToken']) ) {
                $this->errors[] = $this->oai_error('badResumptionToken', '', $data['resumptionToken']);
            } else {
                $readings = $this->readResumToken(TOKEN_PREFIX . $data['resumptionToken']);
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
        if ( $numRows - $this->deliveredrecords > $this->MAXRECORDS ) {
            if( implode(',',$this->sets) ==  ''){
                $this->sets = '-';
            }else{
                $this->sets = implode(',',$this->sets);
            }
            $this->cursor = (int) $this->deliveredrecords + $this->MAXRECORDS;
            $this->restoken = $this->createResumToken($this->cursor, $this->from,$this->until,$this->sets, $this->metadataPrefix);
            $this->expirationdatetime = date("Y-m-d\TH:i:s\Z", time() + TOKEN_VALID);
        }
    }

    public function add_resumption_token_xml($numRows) {
        // ResumptionToken
        if ($this->restoken!='-') {
            if ($this->expirationdatetime) {
                $this->xml_creater->create_resumpToken($this->restoken, $this->expirationdatetime, $numRows, $this->cursor);
            } else {
                $this->xml_creater->create_resumpToken('', null, $numRows, $this->deliveredrecords);
            }
        }
    }
}