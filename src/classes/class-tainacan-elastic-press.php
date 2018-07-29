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

	function __construct($ajax_query=false) {
		add_action('init', [&$this, 'init']);
		
	}
	
	function init() {
		if (!class_exists('EP_API')) {
			return; // ElasticPress not active
		}
		add_filter('tainacan_fetch_args', [&$this, 'filter_args'], 10, 2);
	}
	
	function filter_args($args, $type) {

		if ($type == 'items') {
			$args['ep_integrate'] = true;
			$args = $this->add_items_args($args);
		}
		
		return $args;
	}
	
	private function add_items_args($args) {
		
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		
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
		
		return $args;
		
	}



} // END
