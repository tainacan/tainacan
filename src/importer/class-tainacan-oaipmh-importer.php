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
            'callback' => 'process_collections'
        ]

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
        $this->tainacan_api_address = "/wp-json/tainacan/v1";
        $this->wordpress_api_address = "/wp-json/wp/v2";

    }

    /**
     * Method implemented by the child importer class to proccess each item
     * @return int
     */
    public function process_item( $index, $collection_id ){

    }

    //private functions

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
            // TODO: parse body
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
}