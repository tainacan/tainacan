<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;

class REST_Facets_Controller extends REST_Controller {

	/**
	 * REST_Facets_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'facets';
		parent::__construct();
        add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
        $this->collection = new Entities\Collection();
        
		$this->collection_repository = Repositories\Collections::get_instance();
		$this->metadatum_repository = Repositories\Metadata::get_instance();
        $this->filter_repository = Repositories\Filters::get_instance();
        
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<facet_id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'get_items_permissions_check'),
				'args'                => $this->get_collection_params()
			)
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<facet_id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'get_item_permissions_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE)
			)
		));
	}

	/**
	 * @param mixed $item
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
        
        $filter_id = $request['facet_id'];
        $filter = $this->filter_repository->fetch($filter_id);
        
        // TODO: get facets for the filter, if collection is specified retrieve
        // only values from collection

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