<?php

namespace Tainacan\API\EndPoints;

use Tainacan\Exposers_Handler;
use Tainacan\Mappers_Handler;
use \Tainacan\API\REST_Controller;

class REST_Exposers_Controller extends REST_Controller {
	
	private $metadatum_repository;
	private $exposers;
	private $mappers;

	protected function get_schema() {
        return "TODO:get_schema";
    }

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
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base, array(
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

		$collection_id = isset($request['collection_id']) ? $request['collection_id'] : 'default';
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_id);
		$disabled_mappers = isset($collection) ? $collection->get_disabled_mappers() : [];
		
		$response = [];
		
		
		foreach ($exposers as $exposer) {
			if ( class_exists($exposer) ) {
				$e = (new $exposer())->_toArray();
				$e['mappers'] = array_filter($e['mappers'], function($n) use ($disabled_mappers) {
					return !in_array($n, $disabled_mappers);
				});
				$response[] = $e;
				
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