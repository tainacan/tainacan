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
		$this->taxonomy_repository = Repositories\Taxonomies::get_instance();
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
		register_rest_route($this->namespace, $this->rest_base . '/taxonomies/summary',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_taxonomies_summary'),
					'permission_callback' => array($this, 'reports_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/taxonomies/list',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_taxonomies_list'),
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
			'totals'=> array(
				'items' => array(
					'total' => 0,
					'trash'   => 0,
					'draft'   => 0,
					'publish' => 0,
					'private' => 0
				)
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
				
				$response['totals']['items']['trash']   = intval($total_items->trash);
				$response['totals']['items']['draft']   = intval($total_items->draft);
				$response['totals']['items']['publish'] = intval($total_items->publish);
				$response['totals']['items']['private'] = intval($total_items->private);
			}
		} else {
			$collections = $this->collections_repository->fetch([]);
			$response['totals']['collections'] = array(
				'total' => 0,
				'trash'   => 0,
				'publish' => 0,
				'private' => 0
			);
			if($collections->have_posts()) {
				while ($collections->have_posts()) {
					$collections->the_post();
					$collection = new Entities\Collection($collections->post);
					$response['totals']['collections'][$collection->get_status()]++;
					$response['totals']['collections']['total']++;
					$total_items = wp_count_posts( $collection->get_db_identifier(), 'readable' );

					if (isset($total_items->publish) || isset($total_items->private) ||
						isset($total_items->trash) || isset($total_items->draft)) {
							$response['totals']['items']['trash']   += $total_items->trash;
							$response['totals']['items']['draft']   += $total_items->draft;
							$response['totals']['items']['publish'] += $total_items->publish;
							$response['totals']['items']['private'] += $total_items->private;
					}
				}
				wp_reset_postdata();
			}

			$response['totals']['taxonomies'] = array(
				'total'   => 0,
				'trash'   => 0,
				'publish' => 0,
				'draft'   => 0,
				'private' => 0
			);
			$total_taxonomies = wp_count_posts( 'tainacan-taxonomy', 'readable' );

			if (isset($total_taxonomies->publish) ||
				isset($total_taxonomies->private) ||
				isset($total_taxonomies->trash) ||
				isset($total_taxonomies->draft)) {

				$response['totals']['taxonomies']['trash'] = intval($total_taxonomies->trash);
				$response['totals']['taxonomies']['publish'] = intval($total_taxonomies->publish);
				$response['totals']['taxonomies']['draft'] = intval($total_taxonomies->draft);
				$response['totals']['taxonomies']['private'] = intval($total_taxonomies->private);
				$response['totals']['taxonomies']['total'] = $response['totals']['taxonomies']['trash'] + $response['totals']['taxonomies']['publish'] + $response['totals']['taxonomies']['draft'] + $response['totals']['taxonomies']['private'];
			}
		}
		$response['totals']['items']['total'] = ($response['totals']['items']['trash'] + $response['totals']['items']['draft'] + $response['totals']['items']['publish'] + $response['totals']['items']['private']);
		return new \WP_REST_Response($response, 200);
	}

	public function get_taxonomies_summary($request) {
		$response = array(
			'totals'=> array(
				'taxonomies' => array(
					'total'   => 0,
					'trash'   => 0,
					'draft'   => 0,
					'publish' => 0,
					'private' => 0,
					'used'    => 0,
					'not_used'=> 0
				)
			)
		);

		$total_taxonomies = wp_count_posts( 'tainacan-taxonomy', 'readable' );

		if (isset($total_taxonomies->publish) ||
			isset($total_taxonomies->private) ||
			isset($total_taxonomies->trash) ||
			isset($total_taxonomies->draft)) {

			$response['totals']['taxonomies']['trash'] = intval($total_taxonomies->trash);
			$response['totals']['taxonomies']['publish'] = intval($total_taxonomies->publish);
			$response['totals']['taxonomies']['draft'] = intval($total_taxonomies->draft);
			$response['totals']['taxonomies']['private'] = intval($total_taxonomies->private);
			$response['totals']['taxonomies']['total'] = $response['totals']['taxonomies']['trash'] + $response['totals']['taxonomies']['publish'] + $response['totals']['taxonomies']['draft'] + $response['totals']['taxonomies']['private'];
			$response['totals']['taxonomies']['used'] = $this->query_count_used_taxononomies();
			$response['totals']['taxonomies']['not_used'] = $response['totals']['taxonomies']['total'] - $response['totals']['taxonomies']['used'];
		}
		return new \WP_REST_Response($response, 200);
	}

	public function get_taxonomies_list($request) {
		$response = array(
			'list'=> array(
				
			)
		);

		$taxonomies = $this->taxonomy_repository->fetch();
		if($taxonomies->have_posts()){
			while ($taxonomies->have_posts()){
				$taxonomies->the_post();

				$taxonomy = new Entities\Taxonomy($taxonomies->post);
				$total_terms = intval(wp_count_terms( $taxonomy->get_db_identifier(), array('hide_empty'=> false) ));
				$total_terms_used = intval(wp_count_terms( $taxonomy->get_db_identifier(), array('hide_empty'=> true) ));
				$total_terms_not_used = $total_terms - $total_terms_used;
				
				$response['list'][$taxonomy->get_db_identifier()] = array(
					'id' => $taxonomy->get_id(),
					'name' => $taxonomy->get_name(),
					'total_terms' => $total_terms,
					'total_terms_used' => $total_terms_used,
					'total_terms_not_used' => $total_terms_not_used
				);
			}
			wp_reset_postdata();
		}
		return new \WP_REST_Response($response, 200);
	}


	private function query_count_used_taxononomies() {
		global $wpdb;
		$sql_statement = $wpdb->prepare(
			"SELECT COUNT(DISTINCT($wpdb->postmeta.meta_value))
			 FROM $wpdb->postmeta
			 WHERE meta_key = '_option_taxonomy_id'
			"
		);

		$res = intval($wpdb->get_var( $sql_statement ));
		return $res;
	}

}

?>