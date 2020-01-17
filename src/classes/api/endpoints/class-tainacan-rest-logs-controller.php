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
		$this->logs_repository = Repositories\Logs::get_instance();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base . '/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE)
				),
				'schema'                  => [$this, 'get_schema']
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<log_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
				),
				'schema'                  => [$this, 'get_schema']
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				),
				'schema'                  => [$this, 'get_schema']
			)
		);
		register_rest_route($this->namespace, '/item/(?P<item_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				),
				'schema'                  => [$this, 'get_schema']
			)
		);
		register_rest_route($this->namespace, '/filter/(?P<filter_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				),
				'schema'                  => [$this, 'get_schema']
			)
		);
		register_rest_route($this->namespace, '/metadatum/(?P<metadatum_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				),
				'schema'                  => [$this, 'get_schema']
			)
		);
		register_rest_route($this->namespace, '/taxonomy/(?P<taxonomy_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				),
				'schema'                  => [$this, 'get_schema']
			)
		);
		register_rest_route($this->namespace, '/term/(?P<term_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE)
				),
				'schema'                  => [$this, 'get_schema']
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

			// Hanle logs created before 1.0
			if ( !empty( get_post_meta($item->get_id(), 'value', true) ) &&  !empty( get_post_meta($item->get_id(), 'log_diffs', true) ) ) {
				return $this->prepare_legacy_item_for_response($item, $request);
			}

			if ($request['log_id']) {


				$item_array = $item->_toArray();

				$related_object = true;

				if ($item_array['item_id']) {
					$item = Repositories\Items::get_instance()->fetch( (int) $item_array['item_id'] );
					if ($item instanceof Entities\Item ) {
						$item_array['item'] = $item->_toArray();
					}
				}
				if ($item_array['collection_id']) {
					$collection = Repositories\Collections::get_instance()->fetch( (int) $item_array['collection_id'] );
					if ($collection instanceof Entities\Item ) {
						$item_array['collection'] = $collection->_toArray();
					}
				}

				if ( $item_array['object_id'] ) {

					if ( $item_array['object_type'] == 'Tainacan\Entities\Term' ) {
						$related_entity = Repositories\Terms::get_instance()->fetch( (int) $item_array['object_id'] );
					} else {
						$related_post = get_post($item_array['object_id']);
						$related_entity = Repository::get_entity_by_post( $related_post );
					}

					if ($related_entity instanceof Entities\Entity ) {
						$item_array[ 'object' ] = $related_entity->_toArray();
					}

				}

				if ( $item_array['action'] == 'new-attachment' ) {
					if ( isset($item_array['new_value']['id']) ) {
						$item_array['new_value']['url'] = wp_get_attachment_url($item_array['new_value']['id']);
						$item_array['new_value']['thumb'] = wp_get_attachment_image_src($item_array['new_value']['id'], 'thumbnail');
					}
				} elseif ( $item_array['action'] == 'update-document' ) {
					if ( isset( $item_array['new_value']['document'] ) && is_numeric( $item_array['new_value']['document'] ) ) {
						$item_array['new_value']['url'] = wp_get_attachment_url($item_array['new_value']['document']);
						$item_array['new_value']['thumb'] = wp_get_attachment_image_src($item_array['new_value']['document'], 'thumbnail');
					}
					if ( isset( $item_array['old_value']['document'] ) && is_numeric( $item_array['old_value']['document'] ) ) {
						$item_array['old_value']['url'] = wp_get_attachment_url($item_array['old_value']['document']);
						$item_array['old_value']['thumb'] = wp_get_attachment_image_src($item_array['old_value']['document'], 'thumbnail');
					}
				} elseif ( $item_array['action'] == 'update-thumbnail' ) {
					if ( isset( $item_array['new_value']['_thumbnail_id'] ) ) {
						$item_array['new_value']['url'] = wp_get_attachment_url($item_array['new_value']['_thumbnail_id']);
						$item_array['new_value']['thumb'] = wp_get_attachment_image_src($item_array['new_value']['_thumbnail_id'], 'thumbnail');
					}
					if ( isset( $item_array['old_value']['_thumbnail_id'] ) ) {
						$item_array['old_value']['url'] = wp_get_attachment_url($item_array['old_value']['_thumbnail_id']);
						$item_array['old_value']['thumb'] = wp_get_attachment_image_src($item_array['old_value']['_thumbnail_id'], 'thumbnail');
					}
				}


				// translate
				if (isset($related_entity) && $related_entity instanceof Entities\Entity ) {

					$map = $related_entity->get_repository()->get_map();

					foreach ( $map as $slug => $m ) {

						if ( isset($item_array['new_value'][$slug]) ) {
							$item_array['new_value'][$m['title']] = $item_array['new_value'][$slug];
							unset($item_array['new_value'][$slug]);
						}
						if ( isset($item_array['old_value'][$slug]) ) {
							$item_array['old_value'][$m['title']] = $item_array['old_value'][$slug];
							unset($item_array['old_value'][$slug]);
						}

					}

				}

				return $item_array;

			} else {

				if(!isset($request['fetch_only'])) {
					$item_array = $item->_toArray();
					return $item_array;
				}

				$attributes_to_filter = $request['fetch_only'];

			}

			return $this->filter_object_by_attributes($item, $attributes_to_filter);
		}

		return $item;
	}

	private function prepare_legacy_item_for_response($item, $request) {
		if(!isset($request['fetch_only'])) {
			$item_array = $item->_toArray();

			unset($item_array['value']);
			unset($item_array['old_value']);

			$item_array['legacy'] = true;

			return $item_array;
		}

		$attributes_to_filter = $request['fetch_only'];

		return $this->filter_object_by_attributes($item, $attributes_to_filter);
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
		return current_user_can( 'tnc_rep_read_logs' );
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
		return current_user_can( 'tnc_rep_read_logs' );
	}

	/**
	 * @param string $method
	 *
	 * @return array|mixed
	 */
	public function get_endpoint_args_for_item_schema( $method = null ) {
		$endpoint_args = [];
		if($method === \WP_REST_Server::READABLE) {
			$endpoint_args = array_merge(
                $endpoint_args,
                parent::get_wp_query_params()
            );
		}

		return $endpoint_args;
	}

	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'log',
			'type' => 'object'
		];

		$main_schema = parent::get_repository_schema( $this->logs_repository );
		$permissions_schema = parent::get_permissions_schema();

		$schema['properties'] = array_merge(
			parent::get_base_properties_schema(),
			$main_schema,
			$permissions_schema
		);

		return $schema;
	}
}

?>
