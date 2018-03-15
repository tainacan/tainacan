<?php

use Tainacan\Entities;
use Tainacan\Repositories;
use Tainacan\Repositories\Repository;

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
					'args'                => $this->get_collection_params()
				)
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<log_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::READABLE)
				)
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<log_id>[\d]+)/approve',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'approve_item'),
					'permission_callback' => array($this, 'approve_item_permissions_check'),
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
		if(!empty($item)){
			return $item->__toArray();
		}

		return $item;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$args = $this->prepare_filters($request);

		$logs = $this->logs_repository->fetch($args);

		$response = [];
		if($logs->have_posts()){
			while ($logs->have_posts()){
				$logs->the_post();

				$log = new Entities\Log($logs->post);

				array_push($response, $this->prepare_item_for_response($log, $request));
			}

			wp_reset_postdata();
		}

		$total_logs  = $logs->found_posts;
		$max_pages = ceil($total_logs / (int) $logs->query_vars['posts_per_page']);

		$rest_response = new WP_REST_Response($response, 200);

		$rest_response->header('X-WP-Total', (int) $total_logs);
		$rest_response->header('X-WP-TotalPages', (int) $max_pages);

		return $rest_response;
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

		$prepared_log = $this->prepare_item_for_response( $log, $request );

		return new WP_REST_Response($prepared_log, 200);
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
	
	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function approve_item_permissions_check( $request ) {
		$log = $this->logs_repository->fetch($request['log_id']);

		if($log instanceof Entities\Log){
			if($log->can_read()) {
				$entity = $log->get_value();

				if($entity instanceof Entities\Entity) {
					if($entity instanceof Entities\Item_Metadata_Entity) {
						$item = $entity->get_item();
						return $item->can_edit();
					} // TODO for other entities types
					else {
						return $entity->can_edit();
					}
				}

				return new WP_Error();
			}
		}

		return false;
	}
	
	/**
	 * approve a logged modification
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function approve_item($request) {
		$log = $this->logs_repository->fetch($request['log_id']);

		if($log instanceof Entities\Log){
			$entity = $log->approve();
			$prepared_entity = $this->prepare_item_for_response( $entity, $request );
			
			return new WP_REST_Response($prepared_entity, 200);
		}
	}

	/**
	 * @param string $method
	 *
	 * @return array|mixed
	 */
	public function get_endpoint_args_for_item_schema( $method = null ) {
		$endpoint_args = [];
		if($method === WP_REST_Server::READABLE) {
			$endpoint_args['context'] = array(
				'type'    => 'string',
				'default' => 'view',
				'items'   => array( 'view' )
			);
		}

		return $endpoint_args;
	}

	/**
	 *
	 * Return the queries supported when getting a collection of objects
	 *
	 * @param null $object_name
	 *
	 * @return array
	 */
	public function get_collection_params($object_name = null) {
		$query_params['context']['default'] = 'view';

		$query_params = array_merge($query_params, parent::get_collection_params('log'));

		$query_params['title'] = array(
			'description' => __('Limit result set to log with specific title.'),
			'type'        => 'string',
		);

		$query_params = array_merge($query_params, parent::get_meta_queries_params());

		return $query_params;
	}
}

?>