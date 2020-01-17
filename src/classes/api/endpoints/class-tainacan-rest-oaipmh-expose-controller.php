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
        $this->list_sets = new \Tainacan\OAIPMHExpose\OAIPMH_List_Sets();
        $this->list_metadata_formats = new \Tainacan\OAIPMHExpose\OAIPMH_List_Metadata_Formats();
        $this->list_records = new \Tainacan\OAIPMHExpose\OAIPMH_List_Records();
        $this->get_record = new \Tainacan\OAIPMHExpose\OAIPMH_Get_Record();
        $this->identify = new \Tainacan\OAIPMHExpose\OAIPMH_Identify();
        $this->identifiers = new \Tainacan\OAIPMHExpose\OAIPMH_List_Identifiers();
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
        $request['verb'] = ( isset($request['verb']) ) ? $request['verb'] : 'Identify';
        
        switch ($request['verb']){

            case 'ListSets':
                $allowed_arguments = array('verb','resumptionToken');
                foreach ($request as $key => $value) {
                    if(!in_array($key, $allowed_arguments)){
                        $this->list_sets->config();
                        $this->list_sets->errors[] =  $this->list_sets->oai_error('badArgument');
                        $this->list_sets->oai_exit($request, $this->list_sets->errors);
                    }
                }
                $this->list_sets->list_sets($request);
                break;

            case 'ListRecords':
                if ( !isset($request['metadataPrefix']) && !isset($request['resumptionToken']) ) {
                    $this->list_records->config();
                    $this->list_records->errors[] =  $this->list_records->oai_error('missingArgument','metadataPrefix');
                    $this->list_records->oai_exit($request, $this->list_records->errors);
                }
                $this->list_records->list_records($request);
                break;

            case 'ListIdentifiers':
                if ( !isset($request['metadataPrefix']) && !isset($request['resumptionToken']) ) {
                    $this->identifiers->config();
                    $this->identifiers->errors[] =  $this->list_records->oai_error('missingArgument','metadataPrefix');
                    $this->identifiers->oai_exit($request, $this->list_records->errors);
                }
                $this->identifiers->list_identifiers($request);
                break;

            case 'GetRecord':

                if ( !isset($request['metadataPrefix']) ) {
                    $this->get_record->config();
                    $this->get_record->errors[] = $this->get_record->oai_error('missingArgument','metadataPrefix');
                    $this->get_record->oai_exit( $request, $this->get_record->errors );
                }

                if( !isset($request['identifier']) ){
                    $this->get_record->config();
                    $this->get_record->errors[] = $this->get_record->oai_error('missingArgument','identifier');
                    $this->get_record->oai_exit( $request, $this->get_record->errors );
                }

                $this->get_record->get_record( $request );

                break;

            case 'ListMetadataFormats':
                $this->list_metadata_formats->list_metadata_formats($request);
                break;

            case 'Identify':
                $this->identify->identify($request);
                break;

            default:
                $this->controller_oai->config();
                $this->controller_oai->errors[] = $this->controller_oai->oai_error('noVerb');
                $this->controller_oai->oai_exit( $request, $this->controller_oai->errors);
                break;
        }

        die;
    }
}