<?php

namespace Tainacan\API\EndPoints;

use Tainacan\OAIPMHExpose;
use \Tainacan\API\REST_Controller;

class REST_Oaipmh_Expose_Controller extends REST_Controller {

    /**
     * REST_Facets_Controller constructor.
     */
    public function __construct() {
        $this->rest_base = 'oai';
        parent::__construct();
        add_action('init', array(&$this, 'init_objects'), 11);
    }

    /**
     * Initialize objects after post_type register
     */
    public function init_objects() {
        $this->controller_oai = new \Tainacan\OAIPMHExpose\OAIPMH_Expose();
    }

    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_verb'),
                'permission_callback' => array($this, 'get_verb_permissions_check')
            )
        ));
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return bool|\WP_Error
     */
    public function get_verb_permissions_check( $request ) {
        return true;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|\WP_REST_Response
     * @throws \Exception
     */
    public function get_verb( $request ){
        $verb = ( isset($request['verb']) ) ? $request['verb'] : false;

        switch ($verb){

            default:
                $this->controller_oai->config();
                $this->controller_oai->errors[] = $this->controller_oai->oai_error('noVerb');
                $this->controller_oai->oai_exit( $request, $this->controller_oai->errors);
                break;
        }
    }
}