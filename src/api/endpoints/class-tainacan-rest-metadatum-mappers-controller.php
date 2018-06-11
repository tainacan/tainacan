<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;

class REST_Metadatum_Mappers_Controller extends REST_Controller {

	/**
	 * REST_Metadatum_Mappers_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'metadatum-mappers';
		parent::__construct();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
				)
			)
		);
	}

	/**
	 * @param \Tainacan\Exposers\Mappers\Mapper $mapper
	 * @param \WP_REST_Request $request
	 *map
	 * @return mixed|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $mapper, $request ) {

	    $metadatum_arr = $mapper->_toArray();

		return $metadatum_arr;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$Tainacan_Exposers = \Tainacan\Exposers\Exposers::get_instance();

		$metadatum_mappers = $Tainacan_Exposers->get_mappers( 'OBJECT' );

		$prepared = [];
		foreach ($metadatum_mappers as $metadatum_mapper){
			array_push($prepared, $this->prepare_item_for_response($metadatum_mapper, $request));
		}

		return new \WP_REST_Response($prepared, 200);
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