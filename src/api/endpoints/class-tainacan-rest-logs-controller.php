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
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base . '/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE)
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
		register_rest_route($this->namespace, '/filter/(?P<filter_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				)
			)
		);
		register_rest_route($this->namespace, '/metadatum/(?P<metadatum_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				)
			)
		);
		register_rest_route($this->namespace, '/taxonomy/(?P<taxonomy_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				)
			)
		);
		register_rest_route($this->namespace, '/term/(?P<term_id>[\d]+)/' . $this->rest_base,
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


		if ($request['item_id']) {
			$args['item_id'] = $request['item_id'];
		} elseif ($request['collection_id']) {
			$args['collection_id'] = $request['collection_id'];
		} elseif ($request['filter_id']) {
			$args['object_type'] = 'Tainacan\Entities\Filter';
			$args['object_id'] = $request['filter_id'];
		} elseif ($request['metadatum_id']) {
			$args['object_type'] = 'Tainacan\Entities\Metadatum';
			$args['object_id'] = $request['metadatum_id'];
		} elseif ($request['taxonomy_id']) {
			$args['object_type'] = 'Tainacan\Entities\Taxonomy';
			$args['object_id'] = $request['taxonomy_id'];
		} elseif ($request['term_id']) {
			$args['object_type'] = 'Tainacan\Entities\Term';
			$args['object_id'] = $request['term_id'];
		}

		$logs = Repositories\Logs::get_instance()->fetch($args);
		
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
		return true;
		return current_user_can('read');
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$log_id = $request['log_id'];

		$log = Repositories\Logs::get_instance()->fetch($log_id);

		$prepared_log = $this->prepare_item_for_response( $log, $request );

		return new \WP_REST_Response($prepared_log, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		return true;
		$log = Repositories\Logs::get_instance()->fetch($request['log_id']);

		if(($log instanceof Entities\Log)) {
			if('edit' === $request['context'] && !$log->can_read()) {
				return false;
			}

			return true;
		}

		return false;
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
}

?>