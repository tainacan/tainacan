<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;
use Tainacan\Entities\Entity;

class REST_Bulkedit_Controller extends REST_Controller {

	public function __construct() {
		$this->rest_base = 'bulk-edit';
			parent::__construct();
			add_action('init', array(&$this, 'init_objects'), 11);
	}

	public function init_objects() {
		$this->metadatum_repository = Repositories\Metadata::get_instance();
		$this->collections_repository = Repositories\Collections::get_instance();
	}

	/**
	 * 
	 *
	 * @throws \Exception
	 */
	public function register_routes() {
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
					'args'                => $this->get_create_params()
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/add',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'add_value'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
					'args'                => [
						'metadatum_id' => [
							'type'        => 'integer',
							'description' => __( 'The metadatum ID', 'tainacan' ),
						],
						'value' => [
							'type'        => 'string/integer',
							'description' => __( 'The value to be added', 'tainacan' ),
						],
					],
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/trash',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'trash_items'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/untrash',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'untrash_items'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/delete_items',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'delete_items'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/set_status',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'set_status'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
					'args'                => [
						'value' => [
							'type'        => 'string',
							'description' => __( 'The new status value', 'tainacan' ),
						],
					],
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/set',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'set_value'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
					'args'                => [
						'metadatum_id' => [
							'type'        => 'integer',
							'description' => __( 'The metadatum ID', 'tainacan' ),
						],
						'value' => [
							'type'        => 'string/integer/array',
							'description' => __( 'The value to be set', 'tainacan' ),
						],
					],
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/remove',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'remove_value'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
					'args'                => [
						'metadatum_id' => [
							'type'        => 'integer',
							'description' => __( 'The metadatum ID', 'tainacan' ),
						],
						'value' => [
							'type'        => 'string/integer',
							'description' => __( 'The value to be added', 'tainacan' ),
						],
					],
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/replace',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'replace_value'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
					'args'                => [
						'metadatum_id' => [
							'type'        => 'integer',
							'description' => __( 'The metadatum ID', 'tainacan' ),
						],
						'old_value' => [
							'type'        => 'string/integer',
							'description' => __( 'The value to search for', 'tainacan' ),
						],
						'new_value' => [
							'type'        => 'string/integer',
							'description' => __( 'The value to be set', 'tainacan' ),
						],
					],
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/clear',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'clear_value'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
					'args'                => [
						'metadatum_id' => [
							'type'        => 'integer',
							'description' => __( 'The metadatum ID', 'tainacan' ),
						]
					],
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/set_comment_status',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'set_comment_status'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
					'args'                => [
						'value' => [
							'type'        => 'string',
							'description' => __( 'The new coments status (open or close)', 'tainacan' ),
						],
					],
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/sequence/(?P<sequence_index>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item_in_sequence'),
					'permission_callback' => array($this, 'bulk_edit_permissions_check'),
				),
			)
		);
	}

    public function bulk_edit_permissions_check($request) {
        $collection = $this->collections_repository->fetch($request['collection_id']);
		$bulk_cap = 'tnc_col_' . $request['collection_id'] . '_bulk_edit';

		if ($collection instanceof Entities\Collection) {
            return current_user_can($bulk_cap) && current_user_can($collection->get_items_capabilities()->edit_others_posts);
        }

        return false;
    }

	public function create_item($request) {
		$body = json_decode($request->get_body(), true);
		$args = [];

		if (isset($body['items_ids']) && is_array($body['items_ids']) && !empty($body['items_ids'])) {
			$collection_id = $request['collection_id'];
			$args = [
				'items_ids' => $body['items_ids'],
				'collection_id' => $collection_id
			];
			if (isset($body['options'])) {
				$args['options'] = $body['options'];
			}
		} elseif ( isset($body['use_query']) && $body['use_query'] ) {
			unset($body['use_query']['paged']);
			unset($body['use_query']['offset']);
			unset($body['use_query']['perpage']);
			$body['use_query']['nopaging'] = 1;

			$query_args = $this->prepare_filters($body['use_query']);
			$collection_id = $request['collection_id'];
			$args = [
				'query' => $query_args,
				'collection_id' => $collection_id
			];
		} else {
			return new \WP_REST_Response([
				'error_message' => __('You mus specify items_ids OR use_query', 'tainacan'),
			], 400);
		}

		global $Tainacan_Generic_Process_Handler;
		$bulk = $Tainacan_Generic_Process_Handler->initialize_generic_process('bulk_edit');
		$bulk->create_bulk_edit($args);
		$Tainacan_Generic_Process_Handler->save_process_instance($bulk);

		$response = $this->prepare_item_for_response($bulk, $request);
		$rest_response = new \WP_REST_Response($response, 200);
		return $rest_response;
	}

	public function set_status($request) {
		return $this->generic_action('set_status', $request);
	}

	public function trash_items($request) {
		return $this->generic_action('trash_items', $request);
	}

	public function untrash_items($request) {
		return $this->generic_action('untrash_items', $request);
	}

	public function delete_items($request) {
		return $this->generic_action('delete_items', $request);
	}

	public function add_value($request) {
		return $this->generic_action('add_value', $request);
	}

	public function set_value($request) {
		return $this->generic_action('set_value', $request);
	}

	public function clear_value($request) {
		return $this->generic_action('clear_value', $request, []);
	}

	public function remove_value($request) {
		return $this->generic_action('remove_value', $request);
	}

	public function replace_value($request) {
		return $this->generic_action('replace_value', $request, ['old_value', 'new_value']);
	}

	public function set_comment_status($request) {
		return $this->generic_action('set_comment_status', $request, []);
	}

	public function get_item($request) {
		$group_id = $request['group_id'];

		global $Tainacan_Generic_Process_Handler;
		$bulk = $Tainacan_Generic_Process_Handler->get_process_instance_by_session_id($group_id);
		if ($bulk == false) {
			return new \WP_REST_Response([
				'error_message' => __('Group not found', 'tainacan'),
			], 404);
		}
		$return = $this->prepare_item_for_response($bulk, $request);
		return new \WP_REST_Response($return, 200);
	}

	function prepare_item_for_response($bulk_object, $request) {
		$options = $bulk_object->get_options();
		$return = [
			'id' => $bulk_object->get_id(),
			'options' => $options
		];
		return $return;
	}

	private function generic_action($method, $request, $keys = ['value']) {
		$body = json_decode($request->get_body(), true);

		if ( !in_array($method, ['trash_items', 'untrash_items', 'delete_items']) ) {
			if (empty($body)) {
				return new \WP_REST_Response([
					'error_message' => __('Body can not be empty.', 'tainacan'),
				], 400);
			}

			if ( !in_array($method, ['set_status', 'set_comment_status']) && !isset($body['metadatum_id'])) {
				return new \WP_REST_Response([
					'error_message' => __('You must specify a Metadatum ID.', 'tainacan'),
				], 400);
			}

			foreach ($keys as $key) {
				if (!isset($body[$key])) {
					return new \WP_REST_Response([
						'error_message' => sprintf(__('%s must be provided', 'tainacan'), $key),
					], 400);
				}
			}
		}

		$bulk_id = $request['group_id'];

		global $Tainacan_Generic_Process_Handler;
		$process = $Tainacan_Generic_Process_Handler->get_process_instance_by_session_id($bulk_id);
		if ($process !== false) {
			$bulk_edit_data = [
				"value" 				=> isset($body['new_value']) ? $body['new_value'] : $body['value'],
				"method" 				=> $method,
				"old_value"			=> isset($body['old_value']) ? $body['old_value'] : null,
				"metadatum_id" 	=> isset($body['metadatum_id']) ? $body['metadatum_id'] : null,
			];
			$process->set_bulk_edit_data($bulk_edit_data);
			$bg_bulk = $Tainacan_Generic_Process_Handler->add_to_queue($process);
			//$Tainacan_Generic_Process_Handler->delete_process_instance($process);
		}

		return new \WP_REST_Response(["bg_process_id"=>$bulk_id, "method" => $method], 200);
	}

	public function get_item_in_sequence($request) {
		$group_id = $request['group_id'];
		$index = $request['sequence_index'];
		$args = ['id' => $group_id];
		$bulk = new \Tainacan\Bulk_Edit($args);
		$item_id = $bulk->get_item_id_by_index( (int) $index );

		if ( !$item_id ) {
			return new \WP_REST_Response([
				'error_message' => __('Item not found.', 'tainacan'),
			], 404);
		} else {
			return new \WP_REST_Response($item_id, 200);
		}
	}

	/**
	 * @param null $object_name
	 *
	 * @return array|void
	 */
	public function get_create_params($object_name = null) {

		$query_params['title'] = array(
			'description' => __('Limits the result set to items with a specific title'),
			'type'        => 'string',
		);

		$query_params['items_ids'] = [
			'type'        => 'array',
			'items'       => ['type' => 'integer'],
			'description' => __( 'Array of items IDs', 'tainacan' ),
		];

		$query_params['use_query'] = [
			'type'        => 'bool',
			'description' => __( 'Whether to use the current query to select posts', 'tainacan' ),
		];

		$query_params = array_merge(
			$query_params,
			parent::get_wp_query_params(),
			parent::get_meta_queries_params()
		);
		return $query_params;
	}

}

?>