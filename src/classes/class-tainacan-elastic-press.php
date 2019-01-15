<?php
/**
 * This class implements the integration of Tainacan with ElasticPress, a WordPress plugin that connects your WordPress installation with Elastic Search
 *
 * https://github.com/10up/ElasticPress
 * https://www.elasticpress.io/
 * 
 * 
 */

namespace Tainacan;

class Elastic_Press {
	public $last_aggregations;
	
	private static $instance = null;
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct($ajax_query=false) {
		add_action('init', [$this, 'init']);
		
	}
	
	function init() {
		if (!class_exists('EP_API')) {
			return; // ElasticPress not active
		}
		add_filter('tainacan_fetch_args', [$this, 'filter_args'], 10, 2);
		
		add_action('ep_retrieve_aggregations', function ( array $aggregations, $scope, $args ) {
			$this->last_aggregations = $aggregations;
		}, 10, 3);
		
	}
	
	function filter_args($args, $type) {

		if ($type == 'items' && (!isset($args['ep_integrate']) || $args['ep_integrate'] === true)) {
			$args['ep_integrate'] = true;
			$args = $this->add_items_args($args);
		}

		return $args;
	}
	
	private function add_items_args($args) {
		
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();
		
		if (is_array($args['post_type']) && isset($args['s']) && !empty($args['s'])) {
		
			$meta_ids = [];
			$taxonomies = [];
		
			foreach ( $args['post_type'] as $cpt ) {
			
				$col = $Tainacan_Collections->fetch_by_db_identifier($cpt);
				
				$taxonomies = array_merge( $taxonomies, get_object_taxonomies($cpt) );
				
				if ($col) {
					
					$metadata = $Tainacan_Metadata->fetch_by_collection($col, ['posts_per_page' => -1], 'OBJECT');

					foreach ($metadata as $meta) {
						$meta_ids[] = $meta->get_id();
					}
				}

			}
			
			$search_fields = [
				'post_title',
				'post_content',
				'post_excerpt'
			];
			
			if (!empty($meta_ids)) {
				$search_fields['meta'] = array_unique($meta_ids);
			}
			if (!empty($taxonomies)) {
				$search_fields['taxonomies'] = array_unique($taxonomies);
			}
			
			$args['search_fields'] = $search_fields;
		
		}
		
		if ( is_array($args['post_type']) ) {
			$args['aggs'] = array (
				'use-filter' => true,
				'name'       => 'aggs',
				'aggs'       => array( )
		 );
			foreach ( $args['post_type'] as $cpt ) {
				$col = $Tainacan_Collections->fetch_by_db_identifier($cpt);
				$_filters = $Tainacan_Filters->fetch_by_collection($col, ['posts_per_page' => -1], 'OBJECT');
				foreach ($_filters as $filter) {
					if ($filter->get_metadatum()->get_metadata_type() == 'Tainacan\Metadata_Types\Taxonomy') {
						$metadatum_options = $filter->get_metadatum()->get_metadata_type_options();
						$taxonomy_id = $metadatum_options['taxonomy_id'];
						$taxonomy_slug = Repositories\Taxonomies::get_instance()->get_db_identifier_by_id($taxonomy_id);
						$args['aggs']['aggs'][$taxonomy_slug] = array(
																				"terms" => array( 
																					"size" => 10000, 
																					"field" => "terms.$taxonomy_slug.slug")
																			);
					} else {
						$metadatum_id = $filter->get_metadatum()->get_ID();
						$args['aggs']['aggs'][$metadatum_id] = array(
							"terms" => array( 
								"size" => 10000, 
								"field" => "meta.$metadatum_id.raw")
						);
					}
				}
			}
		}
		
		return $args;

		// $metadatum_options = $metadatum->get_metadata_type_options();
		// $taxonomy_id = $metadatum_options['taxonomy_id'];
		// $taxonomy_slug = Repositories\Taxonomies::get_instance()->get_db_identifier_by_id($taxonomy_id);
		//'Tainacan\Metadata_Types\Compound'

		//'Tainacan\Metadata_Types\Taxonomy'

		//error_log(\json_encode($filters));
		//error_log(\json_encode($args['aggs']));

		// $args['aggs'] = array (
		//  	'name'       => 'facets',
		//  	'use-filter' => true, 
		//  	'aggs'       => array( 
		// 			"tnc_tax_5" => array(
		// 				"terms" => array(
		// 					"size" => 10000,
		// 					"field" => "terms.tnc_tax_5.slug"
		// 				)
		// 			),
		// 			"teste_meta" => array(
		// 				"terms" => array (
		// 					"size" => 10000,
		// 					"field" => "meta.11.raw"
		// 				)
		// 			)
		//  	)
		// );
	

		// {"name":"terms",
		//  "use-filter":true,
		//  "aggs": { 
		// 	 "category": { 
		// 			"terms": {
		// 				"size":10000,
		// 				"field":"terms.category.slug"
		// 			}
		// 		},
		// 		"post_tag":{
		// 			"terms":{
		// 				"size":10000,
		// 				"field":"terms.post_tag.slug"
		// 			}
		// 		},
		// 		"post_format":{
		// 			"terms":{
		// 				"size":10000,
		// 				"field":"terms.post_format.slug"
		// 			}
		// 		},
		// 		"tnc_tax_5":{
		// 			"terms":{
		// 				"size":10000,
		// 				"field":"terms.tnc_tax_5.slug"
		// 			}
		// 		}
		// 	}
		// }
		

		
	}



} // END
