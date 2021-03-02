<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Reports_Controller extends REST_Controller {

	public function __construct() {
		$this->rest_base = 'reports';
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
		register_rest_route($this->namespace, $this->rest_base . '/collection/(?P<collection_id>[\d]+)/summary',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_summary'),
					'permission_callback' => array($this, 'reports_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/repository/summary',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_summary'),
					'permission_callback' => array($this, 'reports_permissions_check'),
				),
			)
		);
	}

	public function reports_permissions_check($request) {
		return true;
	}

	public function get_summary($request) {
		$response = array(
			'total_items' => array(
				'total' => 0,
				'trash'   => 0,
				'draft'   => 0,
				'publish' => 0,
				'private' => 0
			)
		);
		if(isset($request['collection_id'])) {
			$collection_id = $request['collection_id'];
			$collection = $this->collections_repository->fetch($collection_id);
			$total_items = wp_count_posts( $collection->get_db_identifier(), 'readable' );
			if (isset($total_items->publish) ||
				isset($total_items->private) ||
				isset($total_items->trash) ||
				isset($total_items->draft)) {
				
				$response['total_items']['trash']   = $total_items->trash;
				$response['total_items']['draft']   = $total_items->draft;
				$response['total_items']['publish'] = $total_items->publish;
				$response['total_items']['private'] = $total_items->private;
			}
		} else {
			$collections = $this->collections_repository->fetch([]);
			if($collections->have_posts()) {
				while ($collections->have_posts()) {
					$collections->the_post();
					$collection = new Entities\Collection($collections->post);
					$total_items = wp_count_posts( $collection->get_db_identifier(), 'readable' );

					if (isset($total_items->publish) || isset($total_items->private) ||
						isset($total_items->trash) || isset($total_items->draft)) {
							$response['total_items']['trash']   += $total_items->trash;
							$response['total_items']['draft']   += $total_items->draft;
							$response['total_items']['publish'] += $total_items->publish;
							$response['total_items']['private'] += $total_items->private;
					}
				}
				wp_reset_postdata();
			}
		}
		$response['total_items']['total'] =	($response['total_items']['trash'] + $response['total_items']['draft'] + $response['total_items']['publish'] + $response['total_items']['private']);
		
		return new \WP_REST_Response($response, 200);
	}

}

?>