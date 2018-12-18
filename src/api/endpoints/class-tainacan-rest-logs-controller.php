<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;
use Tainacan\Repositories\Repository;

class REST_Logs_Controller extends REST_Controller {
	private $logs_repository;
	private $log;

	/**
	 * REST_Logs_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'logs';
		parent::__construct();
		add_action('init', array($this, 'init_objects'));
	}

	public function init_objects(){
		$this->logs_repository = Repositories\Logs::get_instance();
		$this->log = new Entities\Log();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base . '/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_collection_params()
				)
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<log_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE)
				)
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<log_id>[\d]+)/approve',
			array(
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'approve_item'),
					'permission_callback' => array($this, 'approve_item_permissions_check'),
				)
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				)
			)
		);
		register_rest_route($this->namespace, '/item/(?P<item_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				)
			)
		);
	}

	/**
	 * @param mixed $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if(!empty($item)){

			if(!isset($request['fetch_only'])) {
				$item_array = $item->_toArray();

				unset($item_array['value']);
				unset($item_array['old_value']);

				return $item_array;
			}

			$attributes_to_filter = $request['fetch_only'];

			return $this->filter_object_by_attributes($item, $attributes_to_filter);
		}

		return $item;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function get_items( $request ) {

		$args = $this->prepare_filters( $request );


		if ($request['item_id']){
			$item_id = $request['item_id'];

			$item_repository = Repositories\Items::get_instance();

			$item = $item_repository->fetch($item_id);

			if(!$item){
				return new \WP_REST_Response([
					'error_message' => __('An item with this ID does not exist', 'tainacan'),
					'item_id' => $item
				], 400);
			}

			if($args &&
			   array_key_exists('meta_query', $args) &&
			   array_key_exists('relation', $args['meta_query'])){

				$metaq = $args['meta_query'];

				unset($args['meta_query']);

				$args['meta_query'][] = $metaq;
				$args['meta_query']['relation'] = 'AND';

			} elseif($args &&
			         array_key_exists('meta_query', $args)){
				$args['meta_query']['relation'] = 'AND';
			}

			$args = array_merge_recursive(array(
				'meta_query' => array(
					'item_clause' => array(
						'key'     => 'item_id',
						'value'   => $item_id,
						'compare' => '='
					)
				)
			), $args);
		}

		if($request['collection_id']){
			$collection_id = $request['collection_id'];

			$collection_repository = Repositories\Collections::get_instance();

			$collection = $collection_repository->fetch($collection_id);

			if(!$collection){
				return new \WP_REST_Response([
					'error_message' => __('A collection with this ID does not exist', 'tainacan'),
					'collection_id' => $collection_id
				], 400);
			}

			if($args &&
			   array_key_exists('meta_query', $args) &&
			   array_key_exists('relation', $args['meta_query'])){

				$metaq = $args['meta_query'];

				unset($args['meta_query']);

				$args['meta_query'][] = $metaq;
				$args['meta_query']['relation'] = 'AND';

			} elseif($args &&
			         array_key_exists('meta_query', $args)){
				$args['meta_query']['relation'] = 'AND';
			}

			$args = array_merge_recursive(array(
				'meta_query' => array(
					'collection_clause' => array(
						'key'     => 'collection_id',
						'value'   => $collection_id,
						'compare' => '='
					)
				)
			), $args);
		}

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

		$rest_response = new \WP_REST_Response($response, 200);

		$rest_response->header('X-WP-Total', (int) $total_logs);
		$rest_response->header('X-WP-TotalPages', (int) $max_pages);

		return $rest_response;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return $this->logs_repository->can_read($this->log);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$log_id = $request['log_id'];

		$log = $this->logs_repository->fetch($log_id);

		$prepared_log = $this->prepare_item_for_response( $log, $request );

		return new \WP_REST_Response($prepared_log, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$log = $this->logs_repository->fetch($request['log_id']);

		if(($log instanceof Entities\Log)) {
			if('edit' === $request['context'] && !$log->can_read()) {
				return false;
			}

			return true;
		}

		return false;
	}
	
	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
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

				return new \WP_Error();
			}
		}

		return false;
	}
	
	/**
	 * approve a logged modification
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function approve_item($request) {
		$log = $this->logs_repository->fetch($request['log_id']);

		if($log instanceof Entities\Log){
			$entity = $log->approve();
			$prepared_entity = $this->prepare_item_for_response( $entity, $request );
			
			return new \WP_REST_Response($prepared_entity, 200);
		}
	}

	/**
	 * @param string $method
	 *
	 * @return array|mixed
	 */
	public function get_endpoint_args_for_item_schema( $method = null ) {
		$endpoint_args = [];
		if($method === \WP_REST_Server::READABLE) {
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
			'description' => __('Limits the result set to a log with a specific title'),
			'type'        => 'string',
		);

		$query_params = array_merge($query_params, parent::get_meta_queries_params());

		return $query_params;
	}
}

?>