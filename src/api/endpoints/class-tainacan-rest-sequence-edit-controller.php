<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;
use Tainacan\Entities\Entity;

class REST_Sequence_Edit_Controller extends REST_Controller {

	public function __construct() {
		$this->rest_base = 'sequence-edit';
			parent::__construct();
			add_action('init', array(&$this, 'init_objects'), 11);
	}

	public function init_objects() {
		$this->items_repository = Repositories\Items::get_instance();
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
					'permission_callback' => array($this, 'sequence_edit_permissions_check'),
					'args'                => $this->get_create_params()
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'sequence_edit_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<group_id>[0-9a-f]+)/(?P<sequence_index>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item_in_sequence'),
					'permission_callback' => array($this, 'sequence_edit_permissions_check'),
				),
			)
		);

	}

	public function prepare_item_for_response($item, $request) {

	}

	public function sequence_edit_permissions_check($request) {
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if ($collection instanceof Entities\Collection) {
			return current_user_can($collection->get_items_capabilities()->edit_others_posts);
		}

		return false;
	}

	public function create_item($request) {
		$body = json_decode($request->get_body(), true);
		$args = [];

		if (isset($body['items_ids']) && is_array($body['items_ids']) && !empty($body['items_ids'])) {

			$args['items_ids'] = $body['items_ids'];
			$count = sizeof($args['items_ids']);
			$args['items_count'] = $count;

		} elseif ( isset($body['use_query']) && $body['use_query'] ) {

			unset($body['use_query']['paged']);
			unset($body['use_query']['offset']);
			unset($body['use_query']['perpage']);

			$query_args = $this->prepare_filters($body['use_query']);
			$collection_id = $request['collection_id'];
			$args = [
				'query' => $query_args,
				'collection_id' => $collection_id
			];

			// calculate size
			$query_args['posts_per_page'] = 1;
			$items_q = $this->items_repository->fetch( $query_args, $collection_id );
			$count = $items_q->found_posts;

			$args['items_count'] = $count;

		} else {

			return new \WP_REST_Response([
				'error_message' => __('You mus specify items_ids OR use_query', 'tainacan'),
			], 400);

		}

		$new_group_id = uniqid();

		update_option('tnc_transient_' . $new_group_id, $args);

		$response = [
			'id' => $new_group_id,
			'items_count' => $count
		];
		$rest_response = new \WP_REST_Response($response, 200);
		return $rest_response;
	}

	public function get_item($request) {
		$group_id = $request['group_id'];

		$group = get_option('tnc_transient_' . $group_id);
		if ( is_array($group) ) {

			return new \WP_REST_Response( $group, 200 );


		}

		return new \WP_REST_Response([
			'error_message' => __('Item not found.', 'tainacan'),
		], 404);

	}


	public function get_item_in_sequence($request) {
		$group_id = $request['group_id'];
		$index = (int) $request['sequence_index'];

		$group = get_option('tnc_transient_' . $group_id);
		if ( is_array($group) ) {

			if ( isset($group['items_ids']) && is_array($group['items_ids']) ) {
				$index = $index - 1;
				if ( isset( $group['items_ids'][$index] ) ) {
					return new \WP_REST_Response( $group['items_ids'][$index], 200 );
				}
			} elseif (
				is_array($group) &&
				isset($group['collection_id']) &&
				isset($group['query']) &&
				is_array($group['query'])
			) {

				$group['query']['paged'] = $index;

				$group['query']['posts_per_page'] = 1;

				$items = $this->items_repository->fetch_ids( $group['query'], $group['collection_id'] );

				if ( is_array($items) && !empty($items) ) {
					return new \WP_REST_Response( $items[0], 200 );
				}

			}

		}

		return new \WP_REST_Response([
			'error_message' => __('Item not found.', 'tainacan'),
		], 404);

	}

	/**
	 * @param null $object_name
	 *
	 * @return array|void
	 */
	public function get_create_params($object_name = null) {

		$query_params['items_ids'] = [
			'type'        => 'array',
			'items'       => ['type' => 'integer'],
			'description' => __( 'Array of items IDs', 'tainacan' ),
		];

		$query_params['use_query'] = [
			'description' => __( 'The query used to define the items in the group', 'tainacan' ),
		];

		return $query_params;
	}

}

?>
