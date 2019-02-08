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
	public $facets;
	
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
		
		//activates the inclusion of the complete hierarchy of terms.
		add_filter('ep_sync_terms_allow_hierarchy', '__return_true');

		add_filter('tainacan_fetch_args', [$this, 'filter_args'], 10, 2);
		
		add_action('ep_retrieve_aggregations', function ( array $aggregations, $scope, $args ) {
			$this->last_aggregations = $this->format_aggregations($aggregations);
		}, 10, 3);

		//format args to include aggregations for filters
		//add_filter('ep_formatted_args', array($this, "add_aggs"));

		// add_action('ep_add_query_log', function($query) { //using to DEBUG
		// 	error_log("DEGUG:");
		// 	error_log($query["args"]["body"]);
		// });
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
			$this->facets = [];
			foreach ( $args['post_type'] as $cpt ) {
				$col = $Tainacan_Collections->fetch_by_db_identifier($cpt);
				$_filters = $Tainacan_Filters->fetch_by_collection($col, ['posts_per_page' => -1], 'OBJECT');
				foreach ($_filters as $filter) {
					$filter_id = $filter->get_id();
					$metadata_type = $filter->get_metadatum()->get_metadata_type();
					if ($metadata_type == 'Tainacan\Metadata_Types\Taxonomy') {
						$metadatum_options = $filter->get_metadatum()->get_metadata_type_options();
						$taxonomy_id = $metadatum_options['taxonomy_id'];
						$taxonomy_slug = Repositories\Taxonomies::get_instance()->get_db_identifier_by_id($taxonomy_id);
						$id = "$filter_id.taxonomy.$taxonomy_slug";
						$key = "terms.$taxonomy_slug.term_id";
						$field = "terms.$taxonomy_slug";
					} else {
						$metadatum_id = $filter->get_metadatum()->get_ID();
						$id = "$filter_id.meta.$metadatum_id";
						$key = "meta.$metadatum_id.raw";
						$field = "meta.$metadatum_id.raw";
					}
					$this->facets[$id] = ["key" => $key, "field" => $field, "max_options" => $filter->get_max_options(), "metadata_type" => $metadata_type];
				}
			}
		}

		add_filter('ep_formatted_args', array($this, "add_aggs"));
		return $args;
	}

	public function add_aggs($formatted_args) {
		$default_filters = $formatted_args['post_filter'];
		$aggs = [];
		
		foreach($this->facets as $id => $filter) {
			
			$custom_filter = $default_filters;
			$temp = [];
			foreach ($custom_filter['bool']['must'] as $item) {
				if ( isset($item['bool'])) {
					foreach ($item['bool']["must"] as $item_filter) {
						if ( !isset( $item_filter["terms"][$filter['key']] ) ) { //do use array_filter ?
						 	$temp[] = $item;
						}
					}
				} elseif ( isset($item['term'])) {
					$temp[] = $item;
				}
			}
			$custom_filter['bool']['must'] = $temp;

			$parent = 0;
			if ($filter['metadata_type'] == 'Tainacan\Metadata_Types\Taxonomy') {
				$field = $filter['field'];
				$aggs[$id] = [
					"filter" => $custom_filter,
					"aggs"	=> array(
						$id => array(
							"terms"=>array(
								"size" => $filter['max_options'],
								"script" => [
									"lang" 	=> "painless",
									"source"=> "def c= ['']; for(term in params._source.$field) { if(term.parent==$parent) { c.add(term.term_id); }} return c;"
								]
							)
						)
					)
				];
			} else {
				$aggs[$id] = [
					"filter" => $custom_filter,
					"aggs"	=> array(
						$id => array(
							"terms"=>array(
								"size" => $filter['max_options'],
								"field"=> $filter['field']
							)
						)
					)
				];
			}
			
		}
		$formatted_args['aggs'] = $aggs;
		return $formatted_args;
	}

	public function format_aggregations($aggregations) {
		global $wpdb;
		$formated_aggs = [];
		foreach($aggregations as $key => $aggregation) {
			$metadata_type = \explode(".", $key);
			$filter_id = $metadata_type[0];
			if($metadata_type[1] == 'taxonomy') {
				$taxonomy_slug = $metadata_type[2];
				$formated_aggs[$filter_id] = [];
				foreach ($aggregation[$key]['buckets'] as $term) {
					$term_id = $term['key'];
					$term_object = \Tainacan\Repositories\Terms::get_instance()->fetch($term_id, $taxonomy_slug);
					$count_query = $wpdb->prepare("SELECT COUNT(term_id) FROM $wpdb->term_taxonomy WHERE parent = %d", $term_id);
					$total_children = $wpdb->get_var($count_query);
					$formated_aggs[$filter_id][] = [
						"type" 						=> "Taxonomy",
						"value" 					=> $term['key'],
						"taxonomy" 				=> $taxonomy_slug,
						"taxonomy_id"			=> $taxonomy_slug,
						"total_children"	=> $total_children,
						"total_items"			=> $term['doc_count'],
						"label" 					=> $term_object->get('name'),
						"parent"					=> $term_object->get('parent')
					];
				}
			} else {
				$metada_label = $metadata_type[1];
				$formated_aggs[$filter_id] = [];
				foreach ($aggregation[$key]['buckets'] as $term) {
					$formated_aggs[$filter_id][] = [
						"type" 				=> "Text",
						"label" 			=> $term['key'],
						"value" 			=> $term['key'],
						"total_items" => $term['doc_count']
					];
				}
			}
		}
		return $formated_aggs;
	}

} // END
