<?php

namespace Tainacan\OAIPMHExpose;
use Tainacan\Repositories;
use Tainacan\Entities;

class OAIPMH_List_Sets extends OAIPMH_Expose {

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
    public $collection_repository;

    /**
     * @signature CONSTRUTOR
     *
     * getting the collection repository
     * @author: Eduardo
     */
    function __construct() {
        $this->collection_repository = Repositories\Collections::get_instance();
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
     * @param $objects
     * @return array
     */
    public function limit_data($objects){
        $conter = 0;
        $result_objects = array();

        foreach ($objects as $object) {
            if( $conter >= $this->deliveredrecords && ($this->deliveredrecords+$this->MAXRECORDS) > $conter ){
                $result_objects[] = $object;
            }
            $conter++;
        }

        return $result_objects;
    }

    /*
     *
     */
    public function list_sets($data) {
        session_write_close();

        $this->config();
        $this->initiate_variables($data);

        $collections = $this->list_collections();
        $numRows = count($collections);

        if($numRows == 0){
            $this->errors[] = $this->oai_error('noSetHierarchy');
            $this->oai_exit($data,$this->errors);
        }

        $collections = $this->limit_data($collections);
        $this->verify_resumption_token($numRows);
        $this->xml_creater = new Xml_Response($data);

        foreach ($collections as $collection) {

            $setNode =  $this->xml_creater->add2_verbNode("set");
            $this->xml_creater->addChild($setNode,'setSpec',$collection->get_id());
            $this->xml_creater->addChild($setNode,'setName',$collection->get_name());
            $description_node = $this->xml_creater->addChild($setNode,'setDescription');
            $this->working_node = $this->xml_creater->addChild($description_node, 'oai_dc:dc');
            $this->working_node->setAttribute('xmlns:oai_dc', "http://www.openarchives.org/OAI/2.0/oai_dc/");
            $this->working_node->setAttribute('xmlns:dc', "http://purl.org/dc/elements/1.1/");
            $this->working_node->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
            $this->working_node->setAttribute('xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
            $this->xml_creater->addChild($this->working_node, 'dc:description',htmlspecialchars($collection->get_description()));
        }


        $this->add_resumption_token_xml($numRows);
        header($this->CONTENT_TYPE);
        if (isset($this->xml_creater)) {
            $this->xml_creater->display();
        } else {
            exit("There is a bug in codes");
        }
    }

    /**
     * @param $data
     */
    public function initiate_variables($data) {

        if (isset($data['resumptionToken'])) {

            if (!file_exists($this->token_prefix . $data['resumptionToken'])) {
                $this->errors[] = $this->oai_error('badResumptionToken', '', $data['resumptionToken']);
            } else {
                $readings = $this->readResumToken($this->token_prefix . $data['resumptionToken']);
                if ($readings == false) {
                    $this->errors[] = $this->oai_error('badResumptionToken', '', $data['resumptionToken']);
                } else {
                    list($this->deliveredrecords, $this->from, $this->until, $this->sets, $this->metadataPrefix) = $readings;
                }
            }

        } else {
            $this->deliveredrecords = 0;
            $this->sets = '-';
            $this->from = '-';
            $this->until = '-';
            $this->metadataPrefix =  '-';
        }

        if(is_array($this->errors)&&count($this->errors)>0){
            $this->oai_exit($data,$this->errors);
        }
    }

    /**
     * @param $numRows
     */
    public function verify_resumption_token($numRows) {
        if ($numRows - $this->deliveredrecords > $this->MAXRECORDS) {
            if(implode(',',$this->sets)==''){
                $this->sets = '-';
            }else{
                $this->sets = implode(',',$this->sets);
            }
            $this->cursor = (int) $this->deliveredrecords + $this->MAXRECORDS;
            $this->restoken = $this->createResumToken($this->cursor, $this->from,$this->until,$this->sets, $this->metadataPrefix);
            $this->expirationdatetime = date("Y-m-d\TH:i:s\Z", time() + $this->token_valid);
        }
    }

    /**
     * @param $numRows
     */
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