<?php

namespace Tainacan\OAIPMHExpose;

use Tainacan\Repositories;
use Tainacan\Entities;

class OAIPMH_List_Identifiers extends OAIPMH_Expose {

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
     * @signature - list_identifiers
     * @param  array $param Os argumentos vindos da url (verb,until,from,set,metadataprefix,resumptioToken)
     * @return mostra o xml do list record desejado
     * @description - Metodo responsavel em mostrar o xml do list records, o metodo executado no controller
     * ele chama os demais metodos que fazem as verificacoes de erros
     * @author: Eduardo
     */
    public function list_identifiers($data) {
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
            $cur_header = $this->xml_creater->create_header($identifier, $datestamp, $setspec, null, ( $item->get_status() === 'trash' ) ? true : false );

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