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
		register_rest_route($this->namespace, $this->rest_base . '/collection/(?P<collection_id>[\d]+)/metadata',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_stats_metadata'),
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

	public function get_stats_metadata($request) {
		$response = array(
			'totals'=> array(
				'metadata' => array(
					'total' => 0,
					'publish' => 0,
					'private' => 0
				),
				'metadata_per_type' => array()
			),
			'distribution' => array(

			)
		);

		if(isset($request['collection_id'])) {
			$collection_id = $request['collection_id'];
			$collection = new Entities\Collection( $collection_id );
			$result_metadatum = $this->metadatum_repository->fetch_by_collection( $collection, [] );
			$response['totals']['metadata']['total'] = count($result_metadatum);
			$meta_ids=[];
			foreach($result_metadatum as $metadatum) {
				$meta_type =  explode('\\', $metadatum->get_metadata_type()) ;
				$meta_type = strtolower($meta_type[sizeof($meta_type)-1]);

				$response['totals']['metadata'][$metadatum->get_status()]++;
				$response['totals']['metadata_per_type'][$meta_type]++;
				$meta_ids[] = $metadatum->get_id();
			}
			$response['distribution'] = $this->query_item_metadata_distribution($meta_ids, $collection->get_db_identifier());
			//wp_count_posts()
		} else {
			$args = [
				'meta_query' => [
					[
						'key'     => 'collection_id',
						'value'   => 'default',
						'compare' => '='
					]
				]
			];
			$result = $this->metadatum_repository->fetch( $args, 'OBJECT' );
		}
		return new \WP_REST_Response($response, 200);
	}

	private function query_item_metadata_distribution($meta_ids, $collection_post_type) {
		$count_posts = wp_count_posts( $collection_post_type, 'readable' );
		$total_items =  intval($count_posts->trash) + intval($count_posts->draft) +
				intval($count_posts->publish) + intval($count_posts->private);

		global $wpdb;
		$string_meta_ids = "'".implode("','", $meta_ids)."'";
		$sql_statement = $wpdb->prepare(
			"SELECT p.post_title AS 'name', m.meta_key AS id, ((m.total/$total_items) * 100) as fill_percentage
			FROM
				(SELECT meta_key, count(DISTINCT post_id) AS total
				FROM $wpdb->postmeta 
				WHERE $wpdb->postmeta.meta_key IN ($string_meta_ids)
				AND $wpdb->postmeta.post_id IN ( 
					SELECT id
					FROM $wpdb->posts
					WHERE $wpdb->posts.post_type = '$collection_post_type'
				)
				GROUP BY $wpdb->postmeta.meta_key) m 
			INNER JOIN $wpdb->posts p on m.meta_key = p.id
			UNION
			SELECT p.post_title AS 'name', m.meta_key AS id, ((m.total/$total_items) * 100) as fill_percentage
			FROM
				(
				SELECT 
					mt.meta_key, count(DISTINCT tr.object_id) AS total
				FROM 
					$wpdb->term_taxonomy tt
					INNER JOIN $wpdb->term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
					INNER JOIN (
						SELECT
							post_id as 'meta_key', concat('tnc_tax_', meta_value) as 'tax_id'
						FROM
							$wpdb->postmeta
						WHERE
							meta_key='_option_taxonomy_id'
					) mt ON tt.taxonomy = mt.tax_id
				WHERE
					mt.meta_key IN ($string_meta_ids)
					AND tr.object_id IN (
						SELECT id
						FROM $wpdb->posts
						WHERE $wpdb->posts.post_type = '$collection_post_type'
					)
				GROUP BY mt.meta_key) m 
				INNER JOIN $wpdb->posts p on m.meta_key = p.id;"
		);
		$res = $wpdb->get_results($sql_statement);
		//return ['t' => $res, 's' => $sql_statement];
		return $res;
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