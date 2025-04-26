<?php
/**
 * This class implements the integration of Tainacan with ElasticPress, a WordPress plugin that connects your WordPress installation with Elastic Search
 *
 * https://github.com/10up/ElasticPress
 * https://www.elasticpress.io/
 * 
 * @package Tainacan
 * @since 0.21.15
 */
namespace Tainacan;
require_once('class-tainacan-elastic-press-lte-4.7.2.php');

/**
 * Class Elastic_Press
 * Handles the integration between Tainacan and ElasticPress
 */
class Elastic_Press {
	/**
	 * Stores the last aggregations result
	 *
	 * @var array
	 */
	public $last_aggregations;
	
	/**
	 * Stores the facets configuration
	 *
	 * @var array
	 */
	public $facets;

	/**
	 * Defines the type of aggregation being performed
	 * 
	 * @var string
	 */
	private $aggregation_type = 'items';
	
	/**
	 * Singleton instance
	 *
	 * @var Elastic_Press
	 */
	private static $instance = null;
	
	/**
	 * Get singleton instance
	 *
	 * @return Elastic_Press
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			if ( defined('EP_VERSION') && version_compare(EP_VERSION, '5.0.0', '<') ) {
				self::$instance = \Tainacan\Elastic_Press_lte4::get_instance();
			} else {
				self::$instance = new self();
			}
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @param bool $ajax_query Whether this is an AJAX query
	 */
	protected function __construct($ajax_query = false) {
		add_action('init', [$this, 'init']);
	}

	/**
	 * Check if ElasticPress is active
	 *
	 * @return bool
	 */
	public function is_active() {
		if ( !class_exists('ElasticPress\Elasticsearch') ) {
			return false;
		}
		if ( defined('EP_VERSION') ) {
			return version_compare(EP_VERSION, '5.0.0', '>=');
		}
	}

	/**
	 * Initialize the integration
	 */
	public function init() {
		if (!$this->is_active()) {
			return; // ElasticPress not active
		}
		
		$this->last_aggregations = [];
		
		// Activates the inclusion of the complete hierarchy of terms
		add_filter('ep_sync_terms_allow_hierarchy', '__return_true');
		
		// Allow specific meta keys to be indexed
		add_filter('ep_prepare_meta_allowed_keys', [$this, 'prepare_meta_allowed_keys'], 10, 1);
		
		// Main filters for Tainacan integration
		add_filter('tainacan-fetch-args', [$this, 'filter_args'], 10, 2);
		add_filter('tainacan-api-items-filters-response', function($filters) { 
			return $this->last_aggregations; 
		});
		
		add_filter('tainacan-fetch-all-metadatum-values', [$this, 'fetch_all_metadatum_values'], 10, 3);

		// ElasticPress specific filters
		add_filter('ep_config_mapping', [$this, 'elasticpress_config_mapping'], 10, 1);
		add_filter('ep_post_sync_args', [$this, 'ep_post_sync_args'], 10, 2);
		
		// Modify the formatted arguments for search
		add_filter('ep_formatted_args', [$this, 'modify_formatted_args'], 10, 1);
		
		// Debug log for queries (uncomment for debugging)
		// add_action('ep_add_query_log', function($query) {
		//     error_log("DEBUG:");
		//     error_log(print_r($query["args"]["body"], true));
		// });
	}
	
	/**
	 * Define which meta keys should be indexed
	 *
	 * @param array $allowed_keys Previously allowed keys
	 * @return array Modified allowed keys
	 * 
	 * The function should return the meta_keys that will be indexed by ElasticPress.
	 * Currently, we are retrieving the list of all metadata IDs for all collections, but this behavior might not be ideal.
	 * Should we instead look at the post being indexed and return only the metadata for the collection it belongs to?
	 * 
	 * apply_filters( 'ep_prepare_meta_allowed_protected_keys', $keys, $post )
	 * https://10up.github.io/ElasticPress/ep_prepare_meta_allowed_protected_keys.html 
	 */
	public function prepare_meta_allowed_keys($allowed_keys) {
		// Add Tainacan specific meta keys
		$tainacan_keys = [
			'_tainacan_item_metadata',
			'_tainacan_metadata_type',
			'relationship_label',
			'_thumbnail_id',
			'_tnc_log',
		];
		
		// Get all registered metadata IDs from Tainacan
		$metadata_repository = \Tainacan\Repositories\Metadata::get_instance();
		$all_metadata = $metadata_repository->fetch(
			[
				'posts_per_page' => -1,
				'post_status' => 'publish',
			],
			'OBJECT'
		);
		
		if (is_array($all_metadata)) {
			foreach ($all_metadata as $meta) {
				if ($meta instanceof \Tainacan\Entities\Metadatum) {
					$tainacan_keys[] = $meta->get_id();
				}
			}
		}
		
		return array_merge($allowed_keys, $tainacan_keys);
	}

	/**
	 * Modify the formatted arguments for elasticsearch query
	 *
	 * @param array $formatted_args The arguments already formatted by ElasticPress
	 * @return array Modified arguments
	 */
	public function modify_formatted_args($formatted_args) {
		// Ensure we track total hits for accurate pagination
		$formatted_args['track_total_hits'] = true;
		
		// Improve search quality by changing should to must and using phrase_prefix
		if (!empty($formatted_args['query']['bool']['should'])) {
			$formatted_args['query']['bool']['must'] = $formatted_args['query']['bool']['should'];
			
			// Set search type to phrase_prefix for better results
			if (isset($formatted_args['query']['bool']['must'][0]['multi_match'])) {
				$formatted_args["query"]["bool"]["must"][0]["multi_match"]["type"] = "phrase_prefix";
				
				// Clean up operator configuration that can cause conflicts
				if (isset($formatted_args['query']['bool']['must'][0]['multi_match']['operator'])) {
					unset($formatted_args['query']['bool']['must'][0]['multi_match']['operator']);
				}
				if (isset($formatted_args['query']['bool']['must'][1]['multi_match']['operator'])) {
					unset($formatted_args['query']['bool']['must'][1]['multi_match']['operator']);
				}
				if (isset($formatted_args['query']['bool']['must'][2]['multi_match']['operator'])) {
					unset($formatted_args['query']['bool']['must'][2]['multi_match']['operator']);
				}
				
				// Set appropriate analyzer
				if (isset($formatted_args['query']['bool']['must'][2])) {
					$formatted_args['query']['bool']['must'][2]['multi_match']['analyzer'] = 'default';
				}
			}
			
			// Remove the original should clause
			unset($formatted_args['query']['bool']['should']);
		}
		
		// Handle private content visibility for logged-in users
		if (is_user_logged_in() && !isset($formatted_args['post_status'])) {
			if (isset($formatted_args['post_filter']['bool']['must'])) {
				$post_filter = $formatted_args['post_filter']['bool']['must'];
				foreach ($post_filter as $idx => $filter) {
					if (isset($filter['terms']['post_status'])) {
						// Include both private and public posts for logged-in users
						$formatted_args['post_filter']['bool']['must'][$idx]['terms']['post_status'] = ["private", "publish"];
						break;
					}
				}
			}
		}
		
		// Handle sorting configuration
		if (isset($formatted_args['sort'])) {
			$new_sort = [];
			foreach ($formatted_args['sort'] as $sort) {
				foreach ($sort as $key => $value) {
					$parts = \explode(".", $key);
					
					// Adjust field names for proper sorting
					if ($key == 'post_title') {
						$new_sort["$key.sortable"] = $value;
					} elseif ($key == 'post_author') {
						$new_sort["post_author.login.sortable"] = $value;
					} elseif ($key == 'post_name') {
						$new_sort["post_name.raw"] = $value;
					} elseif ($key == 'post_type') {
						$new_sort["post_type.raw"] = $value;
					} else {
						$new_sort[$key] = $value;
					}
				}
			}
			$formatted_args['sort'] = $new_sort;
		}
		
		return $formatted_args;
	}

	/**
	 * Configure the mapping for Elasticsearch
	 *
	 * @param array $mapping Current mapping configuration
	 * @return array Modified mapping
	 */
	public function elasticpress_config_mapping($mapping) {
		$name_field = 'relationship_label';
		
		// Process dynamic templates to ensure proper field mapping
		if (isset($mapping["mappings"]["dynamic_templates"])) {
			$array_dynamic_templates = $mapping["mappings"]["dynamic_templates"];
			
			foreach ($array_dynamic_templates as $key => $dynamic_templates) {
				// Handle meta types template
				if (isset($dynamic_templates['template_meta_types'])) {
					$mapping["mappings"]["dynamic_templates"][$key]['template_meta_types']["mapping"]["properties"][$name_field] = [
    'type' => 'text',
    'fields' => [
        'keyword' => [
            'type' => 'keyword',
            'ignore_above' => 2048
        ]
    ]
					];
				} 
				// Handle terms template
				elseif (isset($dynamic_templates['template_terms'])) {
					$mapping["mappings"]["dynamic_templates"][$key]['template_terms']['mapping']['properties']['term_name.id'] = [
						'type' => 'keyword', 
						'normalizer' => 'lowerasciinormalizer'
					];
				}
			}
		}
		
		return $mapping;
	}

	/**
	 * Modify post arguments before syncing to Elasticsearch
	 *
	 * @param array $post_args Post arguments
	 * @param int $post_id Post ID
	 * @return array Modified post arguments
	 */
	public function ep_post_sync_args($post_args, $post_id) {
		$name_field = 'relationship_label';
		
		// Get item repositories
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		// Fetch the item
		$item = $Tainacan_Items->fetch($post_id);
		
		if ($item instanceof \Tainacan\Entities\Item) {
			// Get all numeric meta IDs
			$ids_meta = array_filter(
				array_keys($post_args['meta']), 
				function($n) {
					return is_numeric($n) ? intval($n) : false;
				}
			);
			
			// Get repositories
			$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
			$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
			
			// Fetch relationship metadata for this item
			$metadatas = $Tainacan_Item_Metadata->fetch(
				$item, 
				'OBJECT', 
				[
					'post__in' => $ids_meta, 
					'order' => 'id', 
					'metadata_type' => 'Tainacan\Metadata_Types\Relationship'
				]
			);
			
			// Process relationship metadata
			if (is_array($metadatas)) {
				foreach ($metadatas as $meta) {
					if (!empty($meta)) {
						$meta_id = $meta->get_metadatum()->get_id();
						$title = $meta->get_value_as_string();
						
						// Add relationship label to metadata
						if (isset($post_args['meta'][$meta_id][0])) {
							$post_args['meta'][$meta_id][0][$name_field] = $title;
						}
					}
				}
			}
			
			// Process taxonomy terms
			if (isset($post_args['terms']) && !empty($post_args['terms'])) {
				foreach ($post_args['terms'] as $key => $terms_doc) {
					for ($i = 0; $i < count($terms_doc); $i++) {
						// Create term_name.id format for better searching
						$post_args['terms'][$key][$i]['term_name.id'] = 
							$terms_doc[$i]['name'] . '.' . 
							$terms_doc[$i]['term_id'] . '.parent=' . 
							$terms_doc[$i]['parent'];
					}
				}
			}
		}
		
		return $post_args;
	}

	/**
	 * Filter arguments for Tainacan queries
	 *
	 * @param array $args The original arguments
	 * @param string $type The query type (items, taxonomies, etc)
	 * @return array Modified arguments
	 */
	public function filter_args($args, $type) {
		// Only process 'items' type and if ElasticPress integration is not disabled
		if ($type == 'items' && (!isset($args['ep_integrate']) || $args['ep_integrate'] === true)) {
			$args['ep_integrate'] = true;
			
			// Hook to capture elasticsearch response and process aggregations
			add_action('ep_valid_response', function ($response, $query, $query_args) {
				$aggregations = isset($response['aggregations']) ? $response['aggregations'] : [];
				$this->last_aggregations = $this->format_aggregations($aggregations);
			}, 10, 3);
			
			// Add Tainacan-specific arguments
			$args = $this->add_items_args($args);
		}
		
		return $args;
	}

	/**
	 * Add Tainacan-specific arguments for items queries
	 *
	 * @param array $args Original arguments
	 * @return array Modified arguments
	 */
	private function add_items_args($args) {
		// Get repositories
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();
		
		// Handle search configuration for multiple post types
		if (is_array($args['post_type']) && isset($args['s']) && !empty($args['s'])) {
			$meta_ids = [];
			$taxonomies = [];
			
			// Process each post type (collection)
			foreach ($args['post_type'] as $cpt) {
				$col = $Tainacan_Collections->fetch_by_db_identifier($cpt);
				
				// Get taxonomies for this post type
				$taxonomies = array_merge($taxonomies, get_object_taxonomies($cpt));
				
				// Get metadata for this collection
				if ($col) {
					$metadata = $Tainacan_Metadata->fetch_by_collection($col, ['posts_per_page' => -1]);
					
					foreach ($metadata as $meta) {
						$meta_ids[] = $meta->get_id();
					}
				}
			}
			
			// Define search fields
			$search_fields = [
				'post_title',
				'post_content',
				'post_excerpt'
			];
			
			// Add metadata fields to search
			if (!empty($meta_ids)) {
				$search_fields['meta'] = array_unique($meta_ids);
			}
			
			// Add taxonomy fields to search
			if (!empty($taxonomies)) {
				$search_fields['taxonomies'] = array_unique($taxonomies);
			}
			
			$args['search_fields'] = $search_fields;
		}
		
		// Process facets configuration
		if (is_array($args['post_type'])) {
			$this->facets = [];
			
			// Handle single facet requests
			if (isset($args['facet_metadatum_id'])) {
				$this->aggregation_type = 'facets';
				$metadatum = \Tainacan\Repositories\Metadata::get_instance()->fetch($args['facet_metadatum_id']);
				
				if (!$metadatum) {
					return $args;
				}
				
				$metadatum_options = $metadatum->get_metadata_type_options();
				$metadata_type = $metadatum->get_metadata_type();
				
				// Configure taxonomy facet
				if ($metadata_type == 'Tainacan\Metadata_Types\Taxonomy') {
					$taxonomy_id = $metadatum_options['taxonomy_id'];
					$taxonomy_slug = \Tainacan\Repositories\Taxonomies::get_instance()->get_db_identifier_by_id($taxonomy_id);
					$id = "taxonomy.$taxonomy_slug";
					$key = "terms.$taxonomy_slug.term_id";
					$field = "terms.$taxonomy_slug";
				} 
				// Configure regular metadata facet
				else {
					$metadatum_id = $args['facet_metadatum_id'];
					$id = "meta.$metadatum_id";
					$key = "meta.$metadatum_id.raw";
					$field = "meta.$metadatum_id.raw";
				}
				
				// Store facet configuration
				$this->facets[$id] = [
					"key" => $key, 
					"field" => $field,
					"metadata_type" => $metadata_type, 
					"last_term" => isset($args['facet_last_term']) ? $args['facet_last_term'] : '',
					"parent" => isset($args['facet_term_parent_id']) ? $args['facet_term_parent_id'] : 0,
					"pagesize" => !isset($args['facet_pagesize']) || empty($args['facet_pagesize']) ? 10 : $args['facet_pagesize'],
					"search" => !isset($args['facet_search']) || empty($args['facet_search']) ? '' : $args['facet_search'],
					"include" => isset($args['facet_include']) ? $args['facet_include'] : [],
				];
			} 
			// Process all filters for collections
			else {
				foreach ($args['post_type'] as $cpt) {
					$col = $Tainacan_Collections->fetch_by_db_identifier($cpt);
					
					if (!$col) {
						continue;
					}
					
					$_filters = $Tainacan_Filters->fetch_by_collection($col, ['posts_per_page' => -1]);
					
					foreach ($_filters as $filter) {
						if ($filter == null || $filter->get_metadatum() == null) {
							continue;
						}
						
						$include = [];
						$filter_id = $filter->get_id();
						$metadata_type = $filter->get_metadatum()->get_metadata_type();
						
						// Configure taxonomy filter
						if ($metadata_type == 'Tainacan\Metadata_Types\Taxonomy') {
							$metadatum_options = $filter->get_metadatum()->get_metadata_type_options();
							$taxonomy_id = $metadatum_options['taxonomy_id'];
							$taxonomy_slug = \Tainacan\Repositories\Taxonomies::get_instance()->get_db_identifier_by_id($taxonomy_id);
							$id = "$filter_id.taxonomy.$taxonomy_slug";
							$key = "terms.$taxonomy_slug.term_id";
							$field = "terms.$taxonomy_slug";
							
							// Check if there are already tax_query values for this taxonomy
							if (isset($args['tax_query'])) {
								foreach ($args['tax_query'] as $taxquery) {
									if (!isset($taxquery['taxonomy']) || !isset($taxquery['terms'])) {
										continue;
									}
									
									if ($taxquery['taxonomy'] === $taxonomy_slug) {
										$include = is_array($taxquery['terms']) ? $taxquery['terms'] : [$taxquery['terms']];
									}
								}
							}
						} 
						// Configure regular metadata filter
						else {
							$metadatum_id = $filter->get_metadatum()->get_ID();
							$id = "$filter_id.meta.$metadatum_id";
							$key = "meta.$metadatum_id.raw";
							$field = "meta.$metadatum_id.raw";
							
							// Check if there are already meta_query values for this metadata
							if (isset($args['meta_query'])) {
								foreach ($args['meta_query'] as $metaquery) {
									if (!isset($metaquery['key']) || !isset($metaquery['value'])) {
										continue;
									}
									
									if (isset($metaquery['key']) && $metaquery['key'] == $metadatum_id) {
										$include = is_array($metaquery['value']) ? $metaquery['value'] : [$metaquery['value']];
									}
								}
							}
						}
						
						// Store filter configuration
						$this->aggregation_type = 'items';
						$this->facets[$id] = [
							"key" => $key, 
							"field" => $field,
							"use_max_options" => $filter->get_filter_type_object()->get_use_max_options(),
							"max_options" => $filter->get_max_options(),
							"metadata_type" => $metadata_type,
							"include" => $include
						];
					}
				}
			}
		}
		
		// Add filter to prepare the elasticsearch request
		add_filter('ep_formatted_args', [$this, 'prepare_request'], 10, 2);
		
		return $args;
	}

	/**
	 * Prepare the elasticsearch request with aggregations for items or facets
	 *
	 * @param array $formatted_args Arguments formatted by ElasticPress
	 * @param array $args Original WP_Query arguments
	 * @return array Modified arguments
	 */
	public function prepare_request($formatted_args, $args) {
		// Process based on aggregation type
		switch ($this->aggregation_type) {
			case 'items':
				$formatted_args = $this->prepare_request_for_items($formatted_args);
				break;
			case 'facets':
				$formatted_args = $this->prepare_request_for_facet($formatted_args);
				break;
		}
		
		return $formatted_args;
	}

	/**
	 * Format elasticsearch aggregations response according to aggregation type
	 *
	 * @param array $aggregations Aggregations from elasticsearch response
	 * @return array Formatted aggregations
	 */
	public function format_aggregations($aggregations) {
		switch ($this->aggregation_type) {
			case 'items':
				return $this->format_aggregations_items($aggregations);
			case 'facets':
				return $this->format_aggregations_facet($aggregations);
			default:
				return [];
		}
	}

	/**
	 * Fetch all values for a metadata
	 *
	 * @param mixed $return Default return value
	 * @param \Tainacan\Entities\Metadatum $metadatum The metadatum object
	 * @param array $args Arguments for the query
	 * @return array|null The metadatum values
	 */
	public function fetch_all_metadatum_values($return, $metadatum, $args) {
		// Skip if not using items filter
		if ($args['items_filter'] === false) {
			return null;
		}
		
		// Get metadatum information
		$metadatum_type = $metadatum->get_metadata_type();
		$metadatum_id = $metadatum->get_id();
		
		// Prepare filter arguments
		$args['items_filter']['ep_integrate'] = true;
		$args['items_filter']['facet_term_parent_id'] = $args['parent_id'];
		$args['items_filter']['facet_pagesize'] = $args['number'];
		$args['items_filter']['facet_last_term'] = $args['last_term'];
		$args['items_filter']['facet_search'] = $args['search'];
		$args['items_filter']['facet_metadatum_id'] = $metadatum_id;
		$args['items_filter']['facet_include'] = $args['include'];
		
		// Execute the query
		$itemsRepo = \Tainacan\Repositories\Items::get_instance();
		$items = $itemsRepo->fetch($args['items_filter'], $args['collection_id'], 'WP_Query');
		
		// Get aggregations from the query
		$items_aggregations = $this->last_aggregations;
		
		// Process the last term value
		$last_term = [];
		if (isset($items_aggregations['last_term'])) {
			$value = explode('.', $items_aggregations['last_term']);
			$last_term = [
				'value' => count($value) > 1 ? $value[count($value) - 2] : $value[0],
				'es_term' => $items_aggregations['last_term']
			];
		}
		
		// Return the values
		return [
			'values' => isset($items_aggregations['values']) ? $items_aggregations['values'] : [],
			'last_term' => $last_term
		];
	}

	/**
	 * Prepare the elasticsearch request for items with aggregations for each filter
	 *
	 * @param array $formatted_args Arguments formatted by ElasticPress
	 * @return array Modified arguments with aggregations
	 */
	private function prepare_request_for_items($formatted_args) {
		// Get default filters from the query
		$default_filters = isset($formatted_args['post_filter']) ? $formatted_args['post_filter'] : ['bool' => ['must' => []]];
		$aggs = [];
		
		// Process each facet
		foreach ($this->facets as $id => $filter) {
			// Copy the default filters to avoid modifying the originals
			$custom_filter = $default_filters;
			$temp = [];
			
			// Remove any existing filters for this facet
			foreach ($custom_filter['bool']['must'] as $item) {
				if (isset($item['bool']) && isset($item['bool']["must"])) {
					foreach ($item['bool']["must"] as $item_filter) {
						if (!isset($item_filter["terms"][$filter['key']])) {
							$temp[] = $item;
						}
					}
				} elseif (isset($item['terms']) || isset($item['term'])) {
					$temp[] = $item;
				}
			}
			
			// Update the filter
			$custom_filter['bool']['must'] = $temp;
			
			// Default parent for taxonomies
			$parent = 0;
			
			// Handle taxonomy facets
			if ($filter['metadata_type'] == 'Tainacan\Metadata_Types\Taxonomy') {
				$field = $filter['field'];
				
				// Configure aggregation for taxonomy
				$aggs[$id] = [
					"filter" => $custom_filter,
					"aggs" => [
						$id => [
							"terms" => [
								"order" => ["_key" => "asc"],
								"include" => "(.)*parent=$parent",
								"field" => "$field.term_name.id"
							]
						]
					]
				];
				
				// Add include filter if necessary
				if (!empty($filter['include'])) {
					$custom_filter_include = $custom_filter;
					$custom_filter_include['bool']['must'][] = [
						"bool" => [
							"must" => [
								[
									"terms" => ["$field.term_id" => $filter['include']]
								]
							]
						]
					];
					
					$terms_id_include = implode("|", $filter['include']);
					
					// Configure aggregation for included terms
					$aggs[$id . '.include'] = [
						"filter" => $custom_filter_include,
						"aggs" => [
							$id . '.include' => [
								"terms" => [
									"order" => ["_key" => "asc"],
									"field" => "$field.term_name.id",
									"include" => "(.)*.($terms_id_include).parent=$parent",
									"min_doc_count" => 0
								]
							]
						]
					];
				}
			} 
			// Handle regular metadata facets
			else {
				$field = $filter['field'];
				
				// Configure aggregation for metadata
				$aggs[$id] = [
					"filter" => $custom_filter,
					"aggs" => [
						$id => [
							"terms" => [
								"field" => $filter['field']
							]
						]
					]
				];
				
				// Add include filter if necessary
				if (!empty($filter['include'])) {
					$custom_filter_include = $custom_filter;
					$custom_filter_include['bool']['must'][] = [
						"bool" => [
							"must" => [
								[
									"terms" => ["$field" => $filter['include']]
								]
							]
						]
					];
					
					$meta_label = explode(".", $id)[1] . '.' . explode(".", $id)[2];
					$meta_id_include = "'" . implode("','", $filter['include']) . "'";
					
					// Configure aggregation for included metadata values
					$aggs[$id . '.include'] = [
						"filter" => $custom_filter_include,
						"aggs" => [
							$id . '.include' => [
								"terms" => [
									"script" => [
										"lang" => "painless",
										"source" => "def c= []; if(!params._source.meta.empty && params._source.$meta_label != null) { for(meta in params._source.$meta_label) { if([$meta_id_include].contains(meta.raw)) { c.add(meta.raw); }}} return c;"
									]
								]
							]
						]
					];
				}
			}
			
			// Set max options if configured
			if ($filter['use_max_options'] == true && isset($filter['max_options']) && intval($filter['max_options']) > 0) {
				$aggs[$id]['aggs'][$id]['terms']['size'] = intval($filter['max_options']);
			}
		}
		
		// Add aggregations to the request
		if (!empty($aggs)) {
			$formatted_args['aggs'] = $aggs;
		}
		
		return $formatted_args;
	}

	/**
	 * Prepare the elasticsearch request for a single facet
	 *
	 * @param array $formatted_args Arguments formatted by ElasticPress
	 * @return array Modified arguments with aggregations
	 */
	private function prepare_request_for_facet($formatted_args) {
		// Set size to 0 since we only need aggregations
		$formatted_args['size'] = 0;
		
		// Set query from post_filter
		$formatted_args['query'] = $formatted_args['post_filter'];
		$custom_filter_include = $formatted_args['post_filter'];
		
		// Remove post_filter since we're using query
		unset($formatted_args['post_filter']);
		
		$aggs = [];
		
		// Process each facet
		foreach ($this->facets as $id => $filter) {
			$search = $filter['search'];
			$field = $filter['field'];
			$pagesize = intval($filter['pagesize']);
			
			// Handle taxonomy facets
			if ($filter['metadata_type'] == 'Tainacan\Metadata_Types\Taxonomy') {
				$parent = intval($filter['parent']);
				
				// Build proper script based on search parameter
				$source = !empty($search)
					? "if ( doc.containsKey('$field.term_name.id') ) {List l = new ArrayList(doc['$field.term_name.id']); return l;} return[];"
					: "if ( doc.containsKey('$field.term_name.id') ) {List l = new ArrayList(doc['$field.term_name.id']); l.removeIf(item->!item.endsWith('.parent=$parent')); return l;} return[];";
				
				// Configure composite aggregation
				$aggs[$id] = [
					"composite" => [
						"size" => $pagesize,
						"sources" => [
							$id => [
								"terms" => [
									"order" => "asc",
									"script" => [
										"lang" => "painless",
										"source" => $source
									]
								]
							]
						]
					]
				];
				
				// Add include filter if necessary
				if (!empty($filter['include'])) {
					$custom_filter_include['bool']['must'][] = [
						"bool" => [
							"must" => [
								[
									"terms" => ["$field.term_id" => $filter['include']]
								]
							]
						]
					];
					
					// Configure aggregation for included terms
					$aggs[$id.'.include'] = [
						"filter" => $custom_filter_include,
						"aggs" => [
							$id.'.include' => [
								"terms" => [
									"script" => [
										"lang" => "painless",
										"source" => "for (int i = 0; i < doc['$field.parent'].length; ++i) { if (doc['$field.parent'][i] == $parent) { return doc['$field.term_id'][i]; }}"
									]
								]
							]
						]
					];
				}
				
				// Add wildcard search if search is provided
				if ($search != '') {
					$formatted_args['query']['bool']['must'][] = ["wildcard" => ["$field.name.sortable" => "*$search*"]];
				}
			} 
			// Handle regular metadata facets
			else {
				// Configure composite aggregation
				$aggs[$id] = [
					"composite" => [
						"size" => $pagesize,
						"sources" => [
							$id => [
								"terms" => [
									"field" => $field
								]
							]
						]
					]
				];
				
				// Add include filter if necessary
				if (!empty($filter['include'])) {
					$custom_filter_include['bool']['must'][] = [
						"bool" => [
							"must" => [
								[
									"terms" => ["$field" => $filter['include']]
								]
							]
						]
					];
					
					// Configure aggregation for included values
					$aggs[$id.'.include'] = [
						"filter" => $custom_filter_include,
						"aggs" => [
							$id => [
								"terms" => [
									"field" => $field
								]
							]
						]
					];
				}
				
				// Add search if provided
				if ($search != '') {
					$field_relationship_label = explode(".", $field);
					$field_relationship_label = "$field_relationship_label[0].$field_relationship_label[1].relationship_label";
					
					// Search both in value and in relationship label
					$formatted_args['query']['bool']['must'][] = [
						"bool" => [
							"should" => [
								["wildcard" => ["$id.value.sortable" => "*$search*"]],
								["wildcard" => ["$field_relationship_label" => "*$search*"]]
							]
						]
					];
				}
			}
			
			// Set after key for pagination
			if (!empty($filter['last_term'])) {
				$aggs[$id]['composite']['after'] = [$id => $filter['last_term']];
			}
		}
		
		// Add aggregations to request
		if (!empty($aggs)) {
			$formatted_args['aggs'] = $aggs;
		}
		
		return $formatted_args;
	}

	/**
	 * Format elasticsearch aggregations for items request
	 *
	 * @param array $aggregations Aggregations from elasticsearch response
	 * @return array Formatted aggregations
	 */
	private function format_aggregations_items($aggregations) {
		global $wpdb;
		$formated_aggs = [];
		
		// Return empty array if no aggregations
		if (empty($aggregations)) {
			return $formated_aggs;
		}
		
		// Get term hierarchy separator
		$separator = strip_tags(apply_filters('tainacan-terms-hierarchy-html-separator', '>'));
		
		// Process each aggregation
		foreach ($aggregations as $key => $aggregation) {
			$description_types = explode(".", $key);
			$filter_id = $description_types[0];
			
			// Initialize filter array if not exists
			$formated_aggs[$filter_id] = isset($formated_aggs[$filter_id]) ? $formated_aggs[$filter_id] : [];
			
			// Process taxonomy aggregations
			if ($description_types[1] == 'taxonomy') {
				$taxonomy_slug = $description_types[2];
				$taxonomy_id = \Tainacan\Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($taxonomy_slug);
				
				// Skip if buckets don't exist
				if (!isset($aggregation[$key]['buckets'])) {
					continue;
				}
				
				// Process each term bucket
				foreach ($aggregation[$key]['buckets'] as $term) {
					$temp = explode('.', $term['key']);
					$term_id = intval($temp[count($temp) - 2]);
					
					// Fetch term object
					$term_object = \Tainacan\Repositories\Terms::get_instance()->fetch($term_id, $taxonomy_slug);
					
					// Skip if term doesn't exist
					if (!$term_object) {
						continue;
					}
					
					// Count children
					$count_query = $wpdb->prepare(
						"SELECT COUNT(term_id) FROM $wpdb->term_taxonomy WHERE parent = %d", 
						$term_id
					);
					$total_children = $wpdb->get_var($count_query);
					
					// Format term data
					$fct = [
						"type" => "Taxonomy",
						"value" => $term_id,
						"taxonomy" => $taxonomy_slug,
						"taxonomy_id" => $taxonomy_id,
						"total_children" => $total_children,
						"total_items" => $term['doc_count'],
						"label" => $term_object->get('name'),
						"parent" => $term_object->get('parent'),
						'hierarchy_path' => get_term_parents_list(
							$term_id, 
							$taxonomy_slug, 
							['format' => 'name', 'separator' => $separator, 'link' => false, 'inclusive' => false]
						)
					];
					
					// Add to results array
					if (isset($description_types[3])) {
						array_unshift($formated_aggs[$filter_id], $fct);
					} else {
						$formated_aggs[$filter_id][] = $fct;
					}
				}
			} 
			// Process metadata aggregations
			else {
				$metada_label = $description_types[1];
				$metada_id = $description_types[2];
				
				// Skip if buckets don't exist
				if (!isset($aggregation[$key]['buckets'])) {
					continue;
				}
				
				// Process each metadata value bucket
				foreach ($aggregation[$key]['buckets'] as $term) {
					$label = $term['key'];
					
					// Handle relationship metadata
					if (is_numeric($term['key'])) {
						$metadatadum = \Tainacan\Repositories\Metadata::get_instance()->fetch($metada_id);
						
						if ($metadatadum && isset($metadatadum->get_metadata_type_options()['collection_id'])) {
							$item = \Tainacan\Repositories\Items::get_instance()->fetch(intval($term['key']));
							
							if ($item instanceof \Tainacan\Entities\Item) {
								$label = $item->get_title();
							} else {
								continue; // Skip if item doesn't exist
							}
						}
					}
					
					// Format metadata value
					$fct = [
						"type" => "Text",
						"label" => $label,
						"value" => $term['key'],
						"total_items" => $term['doc_count']
					];
					
					// Add to results array
					if (isset($description_types[3])) {
						array_unshift($formated_aggs[$filter_id], $fct);
					} else {
						$formated_aggs[$filter_id][] = $fct;
					}
				}
			}
			
			// Remove duplicates
			$formated_aggs[$filter_id] = array_values(
				array_map(
					'unserialize', 
					array_unique(
						array_map('serialize', $formated_aggs[$filter_id])
					)
				)
			);
		}
		
		return $formated_aggs;
	}

	/**
	 * Format elasticsearch aggregations for facet request
	 *
	 * @param array $aggregations Aggregations from elasticsearch response
	 * @return array Formatted aggregations
	 */
	private function format_aggregations_facet($aggregations) {
		global $wpdb;
		$formated_aggs = ['values' => []];
		
		// Return empty array if no aggregations
		if (empty($aggregations)) {
			return $formated_aggs;
		}
		
		// Get term hierarchy separator
		$separator = strip_tags(apply_filters('tainacan-terms-hierarchy-html-separator', '>'));
		
		// Process each aggregation
		foreach ($aggregations as $key => $aggregation) {
			$description_types = explode(".", $key);
			
			// Process taxonomy aggregations
			if ($description_types[0] == 'taxonomy') {
				$has_include = isset($description_types[2]);
				
				$taxonomy_label = $description_types[0] . '.' . $description_types[1];
				$taxonomy_slug = $description_types[1];
				$taxonomy_id = \Tainacan\Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($taxonomy_slug);
				
				// Get buckets based on aggregation type
				$buckets = ($has_include == false && isset($aggregation['buckets'])) 
					? $aggregation['buckets'] 
					: (isset($aggregation[$key]['buckets']) ? $aggregation[$key]['buckets'] : []);
				
				// Process each term bucket
				foreach ($buckets as $term) {
					if ($has_include) {
						$term_id = intval($term['key']);
						$doc_count = $term['doc_count'];
					} else {
						// Handle composite aggregation format
						$temp = explode('.', $term['key'][$key]);
						$term_id = intval($temp[count($temp) - 2]);
						$doc_count = $term['doc_count'];
					}
					
					// Skip term 0
					if ($term_id === 0) {
						continue;
					}
					
					// Fetch term object
					$term_object = \Tainacan\Repositories\Terms::get_instance()->fetch($term_id, $taxonomy_slug);
					
					// Skip if term doesn't exist
					if (!$term_object) {
						continue;
					}
					
					// Count children
					$count_query = $wpdb->prepare(
						"SELECT COUNT(term_id) FROM $wpdb->term_taxonomy WHERE parent = %d", 
						$term_id
					);
					$total_children = $wpdb->get_var($count_query);
					
					// Format term data
					$fct = [
						"type" => "Taxonomy",
						"value" => $term_id,
						"taxonomy" => $taxonomy_slug,
						"taxonomy_id" => $taxonomy_id,
						"total_children" => $total_children,
						"total_items" => $term['doc_count'],
						"label" => $term_object->get('name'),
						"parent" => $term_object->get('parent'),
						'hierarchy_path' => get_term_parents_list(
							$term_id, 
							$taxonomy_slug, 
							['format' => 'name', 'separator' => $separator, 'link' => false, 'inclusive' => false]
						)
					];
					
					// Add to results array
					if ($has_include) {
						array_unshift($formated_aggs['values'], $fct);
					} else {
						$formated_aggs['values'][] = $fct;
						
						// Store last term for pagination
						if (isset($aggregation['after_key'][$key])) {
							$formated_aggs['last_term'] = $aggregation['after_key'][$key];
						}
					}
				}
			} 
			// Process metadata aggregations
			else {
				$metada_id = $description_types[1];
				$metada_label = $description_types[0] . '.' . $description_types[1];
				
				// Handle composite aggregation results
				if (isset($aggregation['buckets'])) {
					foreach ($aggregation['buckets'] as $term) {
						// Skip if key doesn't exist
						if (!isset($term['key'][$key])) {
							continue;
						}
						
						$label = $term['key'][$key];
						
						// Handle relationship metadata
						if (is_numeric($term['key'][$key])) {
							$metadatadum = \Tainacan\Repositories\Metadata::get_instance()->fetch($metada_id);
							
							if ($metadatadum && isset($metadatadum->get_metadata_type_options()['collection_id'])) {
								$item = \Tainacan\Repositories\Items::get_instance()->fetch(intval($term['key'][$key]));
								
								if ($item instanceof \Tainacan\Entities\Item) {
									$label = $item->get_title();
								} else {
									continue; // Skip if item doesn't exist
								}
							}
						}
						
						// Format metadata value
						$fct = [
							"type" => "Text",
							"label" => $label,
							"value" => $term['key'][$key],
							"total_items" => $term['doc_count']
						];
						
						// Add to results array
						$formated_aggs['values'][] = $fct;
					}
					
					// Store last term for pagination
					if (isset($aggregation['after_key'][$key])) {
						$formated_aggs['last_term'] = $aggregation['after_key'][$key];
					}
				} 
				// Handle non-composite (includes) aggregation results
				elseif (isset($aggregation[$metada_label]['buckets'])) {
					foreach ($aggregation[$metada_label]['buckets'] as $term) {
						// Format metadata value
						$fct = [
							"type" => "Text",
							"label" => $term['key'],
							"value" => $term['key'],
							"total_items" => $term['doc_count']
						];
						
						// Add to results array
						array_unshift($formated_aggs['values'], $fct);
					}
				}
			}
			
			// Remove duplicates
			$formated_aggs['values'] = array_values(
				array_intersect_key(
					$formated_aggs['values'], 
					array_unique(array_map('serialize', $formated_aggs['values']))
				)
			);
		}
		
		return $formated_aggs;
	}
}