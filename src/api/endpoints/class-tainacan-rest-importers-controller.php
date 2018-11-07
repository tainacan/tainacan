<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Repositories;
use Tainacan\Entities;

/**
 * Represents the Importers REST Controller
 *
 * */
class REST_Importers_Controller extends REST_Controller {
    private $collections_repository;
    private $collection;

	/**
	 * REST_Importers_Controller constructor.
	 * Define the namespace, rest base and instantiate your attributes.
	 */
	public function __construct(){
        $this->rest_base = 'importers';
        if (session_status() == PHP_SESSION_NONE) {
            @session_start(); // @ avoids Warnings when running phpunit tests
        }
		parent::__construct();
    }


	/**
	 * Register the collections route and their endpoints
	 */
	public function register_routes(){
        register_rest_route($this->namespace, '/' . $this->rest_base . '/session', array(
	        array(
		        'methods'             => \WP_REST_Server::CREATABLE,
		        'callback'            => array($this, 'create_item'),
		        'permission_callback' => array($this, 'import_permissions_check'),
		        'args'                => [
                    'importer_slug' => [
                        'type'        => 'string',
                        'description' => __( 'The slug of the importer to be initialized', 'tainacan' ),
                    ]
                ],
	        ),
        ));
        register_rest_route($this->namespace, '/' . $this->rest_base . '/session/(?P<session_id>[0-9a-f]+)', array(
            
            array(
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'update_item'),
                'permission_callback' => array($this, 'import_permissions_check'),
                'args'                => [
                    'url' => [
                        'type'        => 'string',
                        'description' => __( 'The URL to be used by the importer', 'tainacan' ),
                    ],
                    'collection' => [
                        'type'        => 'array/object',
                        'description' => __( 'The array describing the destination collectino as expected by the importer', 'tainacan' ),
                    ],
                    'options' => [
                        'type'        => 'array/object',
                        'description' => __( 'The importer options', 'tainacan' ),
                    ]
                ],
            ),
            
        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/session/(?P<session_id>[0-9a-f]+)/file', array(
            
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'add_file'),
                'permission_callback' => array($this, 'import_permissions_check'),
            ),
            
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/session/(?P<session_id>[0-9a-f]+)/source_info', array(
            
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'source_info'),
                'permission_callback' => array($this, 'import_permissions_check'),
            ),
            
        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/session/(?P<session_id>[0-9a-f]+)/get_mapping/(?P<collection_id>[0-9a-f]+)', array(

            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_saved_mapping'),
                'permission_callback' => array($this, 'import_permissions_check'),
            ),

        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/session/(?P<session_id>[0-9a-f]+)', array(
            
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_item'),
                'permission_callback' => array($this, 'import_permissions_check'),
            ),
            
        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/session/(?P<session_id>[0-9a-f]+)/run', array(
            
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'run'),
                'permission_callback' => array($this, 'import_permissions_check'),
            ),
            
        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/available', array(
            
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_registered_importers'),
                'permission_callback' => array($this, 'import_permissions_check'),
            ),
            
        ));

    }

	
	/**
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function  import_permissions_check($request){
        // TODO
        return true;
	}

	/**
	 * Creates a new instance of the desired importer and returns its ID
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function create_item( $request ) {
		$body = json_decode($request->get_body(), true);

		if(empty($body)){
			return new \WP_REST_Response([
				'error_message' => __('Body can not be empty.', 'tainacan'),
			], 400);
		}
		
        $slug = $body['importer_slug'];

        global $Tainacan_Importer_Handler;

        if ($object = $Tainacan_Importer_Handler->initialize_importer($slug)) {
            $response = $object->_to_Array();
            return new \WP_REST_Response($response, 201);
        } else {
            return new \WP_REST_Response([
				'error_message' => __('Importer not found', 'tainacan'),
			], 400);
        }
        
    }

	/**
	 * Update a collection
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string|\WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {
	    $session_id = $request['session_id'];

	    $body = json_decode($request->get_body(), true);

	    if(!empty($body)){
	    	$attributes = [];

	    	foreach ($body as $att => $value){
	    		$attributes[$att] = $value;
		    }

		    $importer = $_SESSION['tainacan_importer'][$session_id];

	    	if($importer) {
                
                foreach ($body as $att => $value){
                    
                    if ($att == 'collection') {
                        if (is_array($value) && isset($value['id'])) {
                            $importer->add_collection($value);
                            continue;
                        } else {
                            return new \WP_REST_Response([
                                'error_message' => __('Invalid collection', 'tainacan' ),
                                'session_id' => $session_id
                            ], 400);
                        }
                    }
                    
                    $method = 'set_' . $att;
                    if (method_exists($importer, $method)) {
                        $importer->$method($value);
                    }
                }

                $response = $importer->_to_Array();
                return new \WP_REST_Response( $response, 200 );

		    }

		    return new \WP_REST_Response([
		    	'error_message' => __('Importer Session not found', 'tainacan' ),
			    'session_id' => $session_id
		    ], 400);
	    }

	    return new \WP_REST_Response([
	    	'error_message' => __('The body can not be empty', 'tainacan'),
		    'body'          => $body
	    ], 400);
    }


    public function source_info( $request ) {
        $session_id = $request['session_id'];
        $importer = $_SESSION['tainacan_importer'][$session_id];

        if(!$importer) {
            return new \WP_REST_Response([
		    	'error_message' => __('Importer Session not found', 'tainacan' ),
			    'session_id' => $session_id
		    ], 400);
        }

        $response = [
            'source_metadata' => false,
            'source_total_items' => false
        ];

        if ( method_exists($importer, 'get_source_metadata') ) {
            $response['source_metadata'] = $importer->get_source_metadata();
        }

        if ( method_exists($importer, 'get_source_number_of_items') ) {
            $response['source_total_items'] = $importer->get_source_number_of_items();
        }

        return new \WP_REST_Response( $response, 200 );

    }

    public function get_saved_mapping( $request ){
        $session_id = $request['session_id'];
        $collection_id = $request['collection_id'];
        $importer = $_SESSION['tainacan_importer'][$session_id];
        $response = false;

        if(!$importer) {
            return new \WP_REST_Response([
                'error_message' => __('Importer Session not found', 'tainacan' ),
                'session_id' => $session_id
            ], 400);
        }

        if ( method_exists($importer, 'get_mapping') ) {
            $response = $importer->get_mapping($collection_id);
        }

        return new \WP_REST_Response( $response, 200 );
    }

    public function get_item( $request ) {
        $session_id = $request['session_id'];
        $importer = $_SESSION['tainacan_importer'][$session_id];

        if(!$importer) {
            return new \WP_REST_Response([
		    	'error_message' => __('Importer Session not found', 'tainacan' ),
			    'session_id' => $session_id
		    ], 400);
        }

        $response = $importer->_to_Array();
        return new \WP_REST_Response( $response, 200 );

    }

    public function add_file( $request )  {
        $session_id = $request['session_id'];
        $importer = $_SESSION['tainacan_importer'][$session_id];

        if(!$importer) {
            return new \WP_REST_Response([
		    	'error_message' => __('Importer Session not found', 'tainacan' ),
			    'session_id' => $session_id
		    ], 400);
        }

        $files = $request->get_file_params();
        $headers = $request->get_headers();

        if ( isset($files['file']) && $importer->add_file($files['file']) ) {
            $response = $importer->_to_Array();
            return new \WP_REST_Response( $response, 200 );
        } else {
            return new \WP_REST_Response([
		    	'error_message' => __('Failed to upload file', 'tainacan' ),
			    'session_id' => $session_id
		    ], 400);
        }



    }


    public function run($request) {
        $session_id = $request['session_id'];
        $importer = $_SESSION['tainacan_importer'][$session_id];

        if(!$importer) {
            return new \WP_REST_Response([
		    	'error_message' => __('Importer Session not found', 'tainacan' ),
			    'session_id' => $session_id
		    ], 400);
        }

        global $Tainacan_Importer_Handler; 

        $process = $Tainacan_Importer_Handler->add_to_queue($importer);

        if (false === $process) {
            return new \WP_REST_Response([
		    	'error_message' => __('Error starting importer', 'tainacan' ),
			    'session_id' => $session_id
		    ], 400);
        }

        $response = [
            'bg_process_id' => $process->ID
        ];
        return new \WP_REST_Response( $response, 200 );

    }

    public function get_registered_importers() {
        global $Tainacan_Importer_Handler; 
        $importers = $Tainacan_Importer_Handler->get_registered_importers();
        return new \WP_REST_Response( $importers, 200 );
    }



}

?>
