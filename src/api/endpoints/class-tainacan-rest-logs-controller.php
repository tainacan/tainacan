<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Logs_Controller extends TAINACAN_REST_Controller {
	private $logs_repository;
	private $log;

	/**
	 * TAINACAN_REST_Logs_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'logs';

		add_action('rest_api_init', array($this, 'register_routes'));
		add_action('init', array($this, 'init_objects'));
	}

	public function init_objects(){
		$this->logs_repository = new Repositories\Logs();
		$this->log = new Entities\Log();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base . '/',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
				)
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<log_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
				)
			)
		);
	}

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$prepared = [];
		$map = $this->logs_repository->get_map();


		if(is_array($item)){
			foreach ($item as $it){
				$prepared[] = $this->get_only_needed_attributes($it, $map);
			}

			return $prepared;
		}

		return $item->__toArray();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$args = $this->prepare_filters($request);

		$logs = $this->logs_repository->fetch($args, 'OBJECT');

		if(!empty($logs)) {
			$prepared_logs = $this->prepare_item_for_response( $logs, $request );

			return new WP_REST_Response($prepared_logs, 200);
		}

		return new WP_REST_Response($logs, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return $this->logs_repository->can_read($this->log);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$log_id = $request['log_id'];

		$log = $this->logs_repository->fetch($log_id);

		if(!empty($log)) {
			$prepared_log = $this->prepare_item_for_response( $log, $request );

			return new WP_REST_Response($prepared_log, 200);
		}

		return new WP_REST_Response($log, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$log = $this->logs_repository->fetch($request['log_id']);

		if($log instanceof Entities\Log){
			return $log->can_read();
		}

		return false;
	}
}

?>