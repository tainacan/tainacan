<?php

namespace Tainacan\API\EndPoints;

use Tainacan\Exposers_Handler;
use Tainacan\Mappers_Handler;
use \Tainacan\API\REST_Controller;

class REST_Exposers_Controller extends REST_Controller {


	private $metadatum_repository;

	/**
	 * REST_Facets_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'exposers';
		parent::__construct();
        add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->exposers = Exposers_Handler::get_instance();
		$this->mappers = Mappers_Handler::get_instance();
	}

	public function register_routes() {
		
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'get_items_permissions_check')
			)
		));
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		
		$exposers = $this->exposers->get_exposers();
		
		$response = [];
		
		
		foreach ($exposers as $exposer) {
			if ( class_exists($exposer) ) {
				$e = new $exposer();
				$response[] = $e->_toArray();
			}
		}
		
		$rest_response = new \WP_REST_Response($response, 200);

		$rest_response->header('X-WP-Total', count($response));
		
		return $rest_response;
		
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

}

?>