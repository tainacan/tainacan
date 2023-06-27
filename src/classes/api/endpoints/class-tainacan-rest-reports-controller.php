<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Reports_Controller extends REST_Controller {
	protected function get_schema() {
        return "TODO:get_schema";
    }

	public function __construct() {
		$this->rest_base = 'reports';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}

	public function init_objects() {
		$this->items_repository = Repositories\Items::get_instance();
		$this->taxonomy_repository = Repositories\Taxonomies::get_instance();
		$this->metadatum_repository = Repositories\Metadata::get_instance();
		$this->collections_repository = Repositories\Collections::get_instance();
	}

	/**
	 *
	 *
	 * @throws \Exception
	 */
	public function register_routes() {
		register_rest_route($this->namespace, $this->rest_base . '/collection',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_collections'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/collection/(?P<collection_id>[\d]+)/summary',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_summary'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/collection/(?P<collection_id>[\d]+)/metadata',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_stats_collection_metadata'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/metadata',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_stats_collection_metadata'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/repository/summary',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_summary'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/taxonomy',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_taxonomies_list'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/taxonomy/(?P<taxonomy_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_taxonomy'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/activities',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_activities'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'start' => [
							'title'       => __( 'start Date', 'tainacan' ),
							'type'        => 'string',
							'format'      => 'date-time',
						], 
						'end' => [
							'title'       => __( 'start Date', 'tainacan' ),
							'type'        => 'string',
							'format'      => 'date-time', //  RFC3339. https://tools.ietf.org/html/rfc3339#section-5.8
						],
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/collection/(?P<collection_id>[\d]+)/activities',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_activities'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'start' => [
							'title'       => __( 'start Date', 'tainacan' ),
							'type'        => 'string',
							'format'      => 'date-time',
						], 
						'end' => [
							'title'       => __( 'start Date', 'tainacan' ),
							'type'        => 'string',
							'format'      => 'date-time', //  RFC3339. https://tools.ietf.org/html/rfc3339#section-5.8
						],
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
		register_rest_route($this->namespace, $this->rest_base . '/collection/(?P<collection_id>[\d]+)/metadata/(?P<metadata_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_metadata'),
					'permission_callback' => array($this, 'reports_permissions_check'),
					'args'                => [
						'parent' => [
							'title'       => __( 'parent', 'tainacan' ),
							'type'        => 'integer',
						],
						'force' => [
							'title'       => __( 'Force regenerate', 'tainacan' ),
							'type'        => 'string',
							'default' 	  => 'no',
							'description' => __( 'Force generating the report, despite presence of cache.', 'tainacan' ),
							'enum'    	  => array(
								'no',
								'yes'
							)
						]
					]
				),
			)
		);
	}

	public function reports_permissions_check($request) {
		return \is_user_logged_in() && current_user_can('read');
	}

	public function get_collections($request) {
		$response = array(
			'list' => []
		);
		$key_cache_object = 'collections';
		$cached_object = $this->get_cache_object($key_cache_object, $request);
		if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);
		$collections = $this->collections_repository->fetch([]);
		
		if($collections->have_posts()) {
			while ($collections->have_posts()) {
				$collections->the_post();
				$collection = new Entities\Collection($collections->post);
				$total_items = wp_count_posts( $collection->get_db_identifier(), 'readable' );

				if (isset($total_items->publish) || isset($total_items->private) ||
					isset($total_items->trash) || isset($total_items->draft)) {
					$response['list'][$collection->get_db_identifier()] = array(
						'id' => $collection->get_id(),
						'name' => $collection->get_name(),
						'items' => array(
							'trash' => intval($total_items->trash),
							'draft'   => intval($total_items->draft),
							'publish' => intval($total_items->publish),
							'private' => intval($total_items->private),
							'total' => $total_items->trash + $total_items->draft + $total_items->publish + $total_items->private
						)
					);
				}
			}
			wp_reset_postdata();
		}
		$this->set_cache_object($key_cache_object, $response);
		return new \WP_REST_Response($response, 200);
	}

	public function get_summary($request) {
		$response = array(
			'totals'=> array(
				'items' => array(
					'total' => 0,
					'trash'   => 0,
					'draft'   => 0,
					'publish' => 0,
					'private' => 0,
					'restrict' => 0,
					'not_restrict' => 0
				)
			)
		);
		if(isset($request['collection_id'])) {
			$collection_id = $request['collection_id'];
			
			$key_cache_object = 'summary_' . $collection_id;
			$cached_object = $this->get_cache_object($key_cache_object, $request);
			if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);
			
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

				if ( \is_post_status_viewable( $collection->get_status() ) === true ) {
					$response['totals']['items']['not_restrict'] += isset($total_items->publish) ? intval($total_items->publish) : 0;
				} else {
					$response['totals']['items']['restrict'] += (
						//(isset($total_items->trash) ? intval($total_items->trash) : 0) +
						(isset($total_items->draft) ? intval($total_items->draft) : 0) +
						(isset($total_items->publish) ? intval($total_items->publish) : 0) +
						(isset($total_items->private) ? intval($total_items->private) : 0)
					);
				}
			}
		} else {
			$key_cache_object = 'summary';
			$cached_object = $this->get_cache_object($key_cache_object, $request);
			if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);

			$collections = $this->collections_repository->fetch(['status'=> ['publish', 'private', 'trash']]);
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

					$response['totals']['items']['trash']   += isset($total_items->trash)  ? intval($total_items->trash)   : 0;
					$response['totals']['items']['draft']   += isset($total_items->draft)  ? intval($total_items->draft)   : 0;
					$response['totals']['items']['publish'] += isset($total_items->publish)? intval($total_items->publish) : 0;
					$response['totals']['items']['private'] += isset($total_items->private)? intval($total_items->private) : 0;
					
					if ( \is_post_status_viewable( $collection->get_status() ) === true ) {
						$response['totals']['items']['not_restrict'] += isset($total_items->publish) ? intval($total_items->publish) : 0;
					} else {
						$response['totals']['items']['restrict'] += (
							//(isset($total_items->trash) ? intval($total_items->trash) : 0) +
							(isset($total_items->draft) ? intval($total_items->draft) : 0) +
							(isset($total_items->publish) ? intval($total_items->publish) : 0) +
							(isset($total_items->private) ? intval($total_items->private) : 0)
						);
					}
				}
				wp_reset_postdata();
			}

			$response['totals']['taxonomies'] = array(
				'total'   => 0,
				'trash'   => 0,
				'publish' => 0,
				'draft'   => 0,
				'private' => 0,
				'used'    => 0,
				'not_used'=> 0
			);
			$total_taxonomies = wp_count_posts( 'tainacan-taxonomy', 'readable' );

			$response['totals']['taxonomies']['trash']   = isset($total_taxonomies->trash)  ? intval($total_taxonomies->trash)   : 0;
			$response['totals']['taxonomies']['draft']   = isset($total_taxonomies->draft)  ? intval($total_taxonomies->draft)   : 0;
			$response['totals']['taxonomies']['publish'] = isset($total_taxonomies->publish)? intval($total_taxonomies->publish) : 0;
			$response['totals']['taxonomies']['private'] = isset($total_taxonomies->private)? intval($total_taxonomies->private) : 0;
			$response['totals']['taxonomies']['total'] = $response['totals']['taxonomies']['trash'] + $response['totals']['taxonomies']['publish'] + $response['totals']['taxonomies']['draft'] + $response['totals']['taxonomies']['private'];
			$response['totals']['taxonomies']['used'] = $this->query_count_used_taxononomies();
			$response['totals']['taxonomies']['not_used'] = $response['totals']['taxonomies']['total'] - $response['totals']['taxonomies']['used'];
		}
		$response['totals']['items']['total'] = ($response['totals']['items']['trash'] + $response['totals']['items']['draft'] + $response['totals']['items']['publish'] + $response['totals']['items']['private']);
		$this->set_cache_object($key_cache_object, $response);
		return new \WP_REST_Response($response, 200);
	}

	public function get_taxonomies_list($request) {
		$response = array(
			'list'=> array(
				
			)
		);

		$key_cache_object = 'taxonomies_list';
		$cached_object = $this->get_cache_object($key_cache_object, $request);
		if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);

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
		$this->set_cache_object($key_cache_object, $response);
		return new \WP_REST_Response($response, 200);
	}

	public function get_taxonomy($request) {
		$response = array(
			'terms'=> array()
		);
		$taxonomy_id = $request['taxonomy_id'];
		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);
		$taxonomy_identifier = $taxonomy->get_db_identifier();
		$taxonomy_total_terms = wp_count_terms($taxonomy_identifier, array('hide_empty' => false) );
		$limit = 100;
		$offset = 0;

		if ( !$taxonomy_total_terms) {
			$taxonomy_total_terms = 0;
		} else {
			$key_cache_object = 'taxonomy_' . $taxonomy_identifier;
			$cached_object = $this->get_cache_object($key_cache_object, $request);
			if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);
		}

		while($offset < $taxonomy_total_terms) {
			$terms = get_terms( array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'number' => $limit,
				'offset' => $offset,
				'hide_empty' => false,
			) );
			foreach ($terms as $term) {
				$response['terms'][$term->term_id] = array(
					'id' => $term->term_id,
					'name' => $term->name,
					'count' => $term->count
				);
			}
			$offset+=$limit;
		}
		$this->set_cache_object($key_cache_object, $response);
		return new \WP_REST_Response($response, 200);
	}

	public function get_metadata($request) {
		$response = array(
			'list' => array()
		);
		$collection_id = $request['collection_id'];
		$taxonomy_metadata_id = $request['metadata_id'];;
		$parent_id = 0;
		if ( isset($request['parent']) ) {
			$parent_id = (int) $request['parent'];
		}
		
		$key_cache_object = "facet_taxonomy_$taxonomy_metadata_id-$collection_id-$parent_id";
		$cached_object = $this->get_cache_object($key_cache_object, $request);
		if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);

		$metadatum = $this->metadatum_repository->fetch($taxonomy_metadata_id);
		$metadatum_type = $metadatum->get_metadata_type();
		if($metadatum_type != 'Tainacan\Metadata_Types\Taxonomy') {
			return new \WP_REST_Response([
				'error_message' => __('Only taxonomy metadata type is allowed.', 'tainacan'),
				'metadatum_type'          => $metadatum_type
			], 400);
		}

		$args = [
			'collection_id' => $collection_id,
			'parent_id' => $parent_id,
			'count_items'=> true,
		];
		$data = $this->metadatum_repository->fetch_all_metadatum_values($taxonomy_metadata_id, $args);
		$response['list'] = array_map(function($item) {
			return [
				'type' => $item['type'],
				'value' => $item['value'],
				'label' => $item['label'],
				'parent' => $item['parent'] == null ? 0 : $item['parent'],
				'total_items' => intval($item['total_items']),
				'total_children' => intval($item['total_children']),
			];
		}, $data['values']);

		$this->set_cache_object($key_cache_object, $response);
		return new \WP_REST_Response($response, 200);
	}

	public function get_stats_collection_metadata($request) {
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

			$key_cache_object = 'stats_collection_metadata_' . $collection_id;
			$cached_object = $this->get_cache_object($key_cache_object, $request);
			if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);

			$collection = new Entities\Collection( $collection_id );
			$result_metadatum = $this->metadatum_repository->fetch_by_collection( $collection, [] );
			$response['totals']['metadata']['total'] = count($result_metadatum);
			$meta_ids=[];
			foreach($result_metadatum as $metadatum) {
				$meta_type =  explode('\\', $metadatum->get_metadata_type()) ;
				$meta_type = strtolower($meta_type[sizeof($meta_type)-1]);
				$meta_type_name = $metadatum->get_metadata_type_object()->get_name();
				if( in_array($meta_type, ['core_description','core_title']) ) {
					$meta_type = 'text';
					$meta_type_name = (new \Tainacan\Metadata_Types\Text())->get_name();
				}

				$response['totals']['metadata'][$metadatum->get_status()]++;
				if ( !isset($response['totals']['metadata_per_type'][$meta_type]) ) {
					$response['totals']['metadata_per_type'][$meta_type] = array(
						'name' => $meta_type_name,
						'count' => 0,
					);
				}
				$response['totals']['metadata_per_type'][$meta_type]['count']++;

				if ( $metadatum->get_metadata_type() == 'Tainacan\Metadata_Types\Compound' ) {
					$metadatum_childs = $this->metadatum_repository->fetch(['parent' => $metadatum->get_id()], 'OBJECT');
					foreach($metadatum_childs as $childs) {
						$meta_ids[] = $childs->get_id();
					}
				} else {
					$meta_ids[] = $metadatum->get_id();
				}
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

			$key_cache_object = 'stats_collection_metadata';
			$cached_object = $this->get_cache_object($key_cache_object, $request);
			if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);

			$result_metadatum = $this->metadatum_repository->fetch( $args, 'OBJECT' );
			$meta_ids=[];
			foreach($result_metadatum as $metadatum) {
				$meta_type =  explode('\\', $metadatum->get_metadata_type()) ;
				$meta_type = strtolower($meta_type[sizeof($meta_type)-1]);
				$meta_type_name = $metadatum->get_metadata_type_object()->get_name();
				if( in_array($meta_type, ['core_description','core_title']) ) {
					$meta_type = 'text';
					$meta_type_name = (new \Tainacan\Metadata_Types\Text())->get_name();
				}

				$response['totals']['metadata'][$metadatum->get_status()]++;
				if ( !isset($response['totals']['metadata_per_type'][$meta_type]) ) {
					$response['totals']['metadata_per_type'][$meta_type] = array(
						'name' => $meta_type_name,
						'count' => 0,
					);
				}
				$response['totals']['metadata_per_type'][$meta_type]['count']++;

				$meta_ids[] = $metadatum->get_id();
			}
			$response['distribution'] = $this->query_item_metadata_distribution($meta_ids, 'default');
			
		}
		$this->set_cache_object($key_cache_object, $response);
		return new \WP_REST_Response($response, 200);
	}

	private function query_item_metadata_distribution($meta_ids, $collection_post_type) {
		$count_posts = wp_count_posts( $collection_post_type, 'readable' );
		$total_items =  intval($count_posts->trash) + intval($count_posts->draft) +
				intval($count_posts->publish) + intval($count_posts->private);

		global $wpdb;
		$string_meta_ids = "'".implode("','", $meta_ids)."'";
		$sql_statement = $wpdb->prepare(
			"SELECT p.post_title AS 'name', pp.post_title AS 'parent_name', p.id AS id, IFNULL(((m.total/$total_items) * 100), 0) as fill_percentage
			FROM
				$wpdb->posts p 
				LEFT JOIN $wpdb->posts pp ON (p.post_parent = pp.id)
				LEFT JOIN
				(
					SELECT meta_key, count(DISTINCT post_id) AS total
					FROM $wpdb->postmeta 
					WHERE $wpdb->postmeta.meta_key IN ($string_meta_ids)
						AND $wpdb->postmeta.post_id IN ( 
							SELECT id
							FROM $wpdb->posts
							WHERE $wpdb->posts.post_type = '$collection_post_type'
						)
					GROUP BY $wpdb->postmeta.meta_key
					UNION
					SELECT mt.meta_key, count(DISTINCT tr.object_id) AS total
					FROM $wpdb->term_taxonomy tt
						INNER JOIN $wpdb->term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
						INNER JOIN (
							SELECT post_id as 'meta_key', concat('tnc_tax_', meta_value) as 'tax_id'
							FROM $wpdb->postmeta
							WHERE meta_key='_option_taxonomy_id'
						) mt ON tt.taxonomy = mt.tax_id
					WHERE mt.meta_key IN ($string_meta_ids)
						AND tr.object_id IN (
							SELECT id
							FROM $wpdb->posts
							WHERE $wpdb->posts.post_type = '$collection_post_type'
						)
					GROUP BY mt.meta_key
				) m
				ON (p.id = m.meta_key)
				WHERE p.id IN($string_meta_ids)
			", []
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
			", []
		);

		$res = intval($wpdb->get_var( $sql_statement ));
		return $res;
	}

	public function get_activities($request) {
		$response = array(
			'totals' => []
		);
		$collection_id = false;
		if(isset($request['collection_id'])) { 
			$collection_id = $request['collection_id'];
		}

		if( isset($request['start']) ) {
			$start = new \DateTime($request['start']);

			$key_cache_object = 'activities_' . $start->format('Y-m-d') . '_' . $collection_id;
			$cached_object = $this->get_cache_object($key_cache_object, $request);
			if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);

			$end_limit = $start->add(new \DateInterval('P1Y1D'))->setTime(0,0,0);
			$end = isset($request['end']) ? new \DateTime($request['end']) : $end_limit;
			if($end > $end_limit)
				$end = $end_limit;
			$interval = [
				'start' => (new \DateTime($request['start']))->format('Y-m-d H:i:s'),
				'end' => $end->format('Y-m-d H:i:s')
			];
		} else {
			$key_cache_object = 'activities_' . $collection_id;
			$cached_object = $this->get_cache_object($key_cache_object, $request);
			if($cached_object !== false ) return new \WP_REST_Response($cached_object, 200);
			$end = (new \DateTime())->add(new \DateInterval('P1D'))->setTime(0,0,0);
			$interval = [
				'end' => $end->format('Y-m-d H:i:s'),
				'start' => $end->sub(new \DateInterval('P1Y1D'))->format('Y-m-d H:i:s')
			];
		}

		$response['totals'] = array(
			'by_interval' => array(
				'start' => $interval['start'],
				'end' => $interval['end'],
				'general' => $this->get_activities_general($collection_id, $interval),
				'by_user' => $this->get_activities_general_by_user($collection_id, $interval)
			),
			'by_user' => $this->get_activities_users($collection_id)
		);
		$this->set_cache_object($key_cache_object, $response);
		return new \WP_REST_Response($response, 200);
	}

	private function get_activities_general($collection_id = false, $interval = false) {
		global $wpdb;
		if($interval == false) {
			$end = (new \DateTime())->add(new \DateInterval('P1D'))->setTime(0,0,0);
			$interval = [
				'end' => $end->format('Y-m-d H:i:s'),
				'start' => $end->sub(new \DateInterval('P1Y1D'))->format('Y-m-d H:i:s')
			];
		}
		$collection_from = "";
		$start = $interval['start'];
		$end = $interval['end'];
		if($collection_id !== false) {
			$collection_from = "INNER JOIN $wpdb->postmeta pm ON p.id = pm.post_id AND (pm.meta_key='collection_id' AND pm.meta_value='$collection_id')";
		}
		$sql_statement = $wpdb->prepare(
			"SELECT count(DISTINCT (unix_timestamp(p.post_date) DIV 60)) as total, DATE(p.post_date) as date
			FROM $wpdb->posts p $collection_from
			WHERE p.post_type='tainacan-log' AND p.post_date BETWEEN '$start' AND '$end'
			GROUP BY DATE(p.post_date)
			ORDER BY DATE(p.post_date)", []
		);
		return $wpdb->get_results($sql_statement);
	}

	private function get_activities_general_by_user($collection_id = false, $interval = false) {
		global $wpdb;
		if($interval == false) {
			$end = (new \DateTime())->add(new \DateInterval('P1D'))->setTime(0,0,0);
			$interval = [
				'end' => $end->format('Y-m-d H:i:s'),
				'start' => $end->sub(new \DateInterval('P1Y1D'))->format('Y-m-d H:i:s')
			];
		}
		$collection_from = "";
		$start = $interval['start'];
		$end = $interval['end'];
		if($collection_id !== false) {
			$collection_from = "INNER JOIN $wpdb->postmeta pm ON p.id = pm.post_id AND (pm.meta_key='collection_id' AND pm.meta_value='$collection_id')";
		}
		$sql_statement = $wpdb->prepare(
			"SELECT p.post_author  as user_id, count(DISTINCT (unix_timestamp(p.post_date) DIV 60)) as total, DATE(p.post_date) as date
			FROM $wpdb->posts p $collection_from
			WHERE p.post_type='tainacan-log' AND p.post_date BETWEEN '$start' AND '$end'
			GROUP BY p.post_author, DATE(p.post_date)
			ORDER BY DATE(p.post_date)", []
		);
		$data =$wpdb->get_results($sql_statement);
		$arr = array();
		$avatar_sizes = rest_get_avatar_sizes();
		foreach ($data as $item) {
			if(!isset($arr[$item->user_id])) {
				$user_data = get_userdata($item->user_id);
				$urls = array();
				foreach ( $avatar_sizes as $size ) {
					$urls[ $size ] = get_avatar_url( $user_data, array( 'size' => $size ) );
				}
				$arr[$item->user_id] = [
					'user' => !$user_data ? [] : [
						'id' => $user_data->ID,
						'username' => $user_data->user_login,
						'name' => $user_data->display_name,
						'first_name' => $user_data->first_name,
						'last_name' => $user_data->last_name,
						'email' => $user_data->user_email,
						'avatar_urls' => $urls,
					],
					'user_id' => $item->user_id,
					'total' => 0,
					'by_date' => []
				];
			}
			$arr[$item->user_id]['by_date'][] = $item;
			$arr[$item->user_id]['total'] += $item->total;
		}
		return array_values($arr);
	}

	private function get_activities_users($collection_id = false) {
		global $wpdb;
		$collection_from = "";
		if($collection_id !== false) {
			$collection_from = "INNER JOIN $wpdb->postmeta pm_col ON p.id = pm_col.post_id AND (pm_col.meta_key='collection_id' AND pm_col.meta_value='$collection_id')";
		}
		$sql_statement = $wpdb->prepare(
			"SELECT	count(DISTINCT (unix_timestamp(p.post_date) DIV 60)) as total, p.post_author as user, pm.meta_value as action
			FROM $wpdb->posts p 
			INNER JOIN $wpdb->postmeta pm ON p.id = pm.post_id AND pm.meta_key = 'action'
			$collection_from
			WHERE p.post_type='tainacan-log'
			GROUP BY p.post_author, pm.meta_value 
			ORDER BY total DESC", []
		);
		$results = $wpdb->get_results($sql_statement);
		$response = [];
		$avatar_sizes = rest_get_avatar_sizes();
		foreach($results as $key => $result) {
			$user = $result->user;
			$total = $result->total;
			$action = $result->action;
			if(!isset($response[$user])) {
				$user_data = get_userdata($user);
				$urls = array();
				foreach ( $avatar_sizes as $size ) {
					$urls[ $size ] = get_avatar_url( $user_data, array( 'size' => $size ) );
				}
				$response[$user] = [
					'user' => !$user_data ? [] : [
						'id' => $user_data->ID,
						'username' => $user_data->user_login,
						'name' => $user_data->display_name,
						'first_name' => $user_data->first_name,
						'last_name' => $user_data->last_name,
						'email' => $user_data->user_email,
						'avatar_urls' => $urls,
					],
					'user_id' => $user,
					'total' => 0,
					'by_action' => []
				];
			}
			$response[$user]['by_action'][$action] = intval($total);
			$response[$user]['total'] += $total;
		}
		return array_values($response);
	}

	private $prefix_transient_cahce = 'reports_tnc_';

	private function get_cache_object($key, $request) {
		if ( !isset($request['force']) || $request['force'] == 'no' ) {
			$transient = get_transient($this->prefix_transient_cahce . $key);
			return $transient;
		}
		return false;
	}

	private function set_cache_object($key, $data) {
		$expiration = 604800; //one week
		$data['report_cached_on'] = (new \DateTime())->format('Y-m-d H:i:s');
		return set_transient($this->prefix_transient_cahce . $key, $data, $expiration);
	}
}

?>
