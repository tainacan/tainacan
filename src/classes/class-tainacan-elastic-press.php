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

	private $aggregation_type = 'items';
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

	function is_active() {
		return class_exists('ElasticPress\Elasticsearch');
	}

	function init() {
		if (!$this->is_active()) {
			return; // ElasticPress not active
		}
		$this->last_aggregations = [];
		//activates the inclusion of the complete hierarchy of terms.
		add_filter('ep_sync_terms_allow_hierarchy', '__return_true');

		add_filter('tainacan_fetch_args', [$this, 'filter_args'], 10, 2);
		add_filter('tainacan-api-items-filters-response', function($filters) { return $this->last_aggregations; });
		
		add_filter('tainacan-fetch-all-metadatum-values', [$this, 'fetch_all_metadatum_values'], 10, 3);

		add_filter( 'ep_config_mapping', [$this, 'elasticpress_config_mapping'], 10, 1 );
		add_filter( 'ep_post_sync_args', [$this, 'ep_post_sync_args'], 10, 2 );

		// add_action('ep_add_query_log', function($query) { //using to DEBUG
		// 	error_log("DEGUG:");
		// 	error_log($query["args"]["body"]);
		// });
	}

	function elasticpress_config_mapping( $mapping ) {
		$name_field = 'relationship_label';
		$array_dynamic_templates = $mapping["mappings"]["post"]["dynamic_templates"];
		foreach ($array_dynamic_templates as $key => $dynamic_templates) {
			if ( isset($dynamic_templates['template_meta_types'] )) {
				$mapping["mappings"]["post"]["dynamic_templates"][$key]['template_meta_types']["mapping"]["properties"][$name_field] = ['type' => 'keyword'];
				// $mapping["mappings"]["post"]["dynamic_templates"][$key]['template_meta_types']["mapping"]["properties"][$name_field] =
				// 	['type' => 'nested',
				// 	 'properties' => [
				// 		 'label' => ['type' => 'text']
				// 		 //,'description' => ['type'=>'text']
				// 		]
				// 	];
			}
		}
		return $mapping;
	}

	function ep_post_sync_args( $post_args, $post_id ) {
		$name_field = 'relationship_label';

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$item = $Tainacan_Items->fetch($post_id);

		if ($item instanceof Entities\Item) {
			$ids_meta = array_keys ($post_args['meta']);
			\array_filter(function($n) {
				if (is_numeric($n)) return intval($n);
			}, $ids_meta);

			$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
			$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

			$metadatas = $Tainacan_Item_Metadata->fetch($item, 'OBJECT', [ 'post__in' => $ids_meta, 'order' => 'id', 'metadata_type' => 'Tainacan\Metadata_Types\Relationship' ] );
			
			if ( is_array( $metadatas ) ) {
				foreach ( $metadatas as $meta ) {
					if(!empty($meta)) {
						$meta_id = $meta->get_metadatum()->get_id();
						$title = $meta->get_value_as_string();
						$description = '';
						$post_args['meta'][$meta_id][0][$name_field] = $title;
						//$post_args['meta'][$meta_id][0][$name_field]['description'] = $description;
					}
				}
			}
		}
		return $post_args;
	}

	function filter_args($args, $type) {

		if ($type == 'items' && (!isset($args['ep_integrate']) || $args['ep_integrate'] === true)) {
			$args['ep_integrate'] = true;

			add_action('ep_valid_response', function ( $response, $query, $query_args ) {
				$aggregations = $response['aggregations'];
				$this->last_aggregations = $this->format_aggregations($aggregations);
			}, 10, 3);

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
			
			if ( isset($args['facet_metadatum_id']) ) {
				$this->aggregation_type = 'facets';
				$metadatum = Repositories\Metadata::get_instance()->fetch($args['facet_metadatum_id']);
				$metadatum_options = $metadatum->get_metadata_type_options();
				$metadata_type = $metadatum->get_metadata_type();
				if ($metadata_type == 'Tainacan\Metadata_Types\Taxonomy') {
					$taxonomy_id = $metadatum_options['taxonomy_id'];
					$taxonomy_slug = Repositories\Taxonomies::get_instance()->get_db_identifier_by_id($taxonomy_id);
					$id = "taxonomy.$taxonomy_slug";
					$key = "terms.$taxonomy_slug.term_id";
					$field = "terms.$taxonomy_slug";
				} else {
					$metadatum_id = $args['facet_metadatum_id'];
					$id = "meta.$metadatum_id";
					$key = "meta.$metadatum_id.raw";
					$field = "meta.$metadatum_id.raw";
				}
				$this->facets[$id] = [
					"key" => $key, 
					"field" => $field,
					"metadata_type" => $metadata_type, 
					"last_term" => $args['facet_last_term'], 
					"parent" => $args['facet_term_parent_id'],
					"pagesize" => !isset($args['facet_pagesize']) && empty($args['facet_pagesize']) ? 10 : $args['facet_pagesize'],
					"search" => !isset($args['facet_search']) && empty($args['facet_search']) ? '' : $args['facet_search'],
					"include" => $args['facet_include'],
				];
			} else {
				
				foreach ( $args['post_type'] as $cpt ) {
					$col = $Tainacan_Collections->fetch_by_db_identifier($cpt);
					$_filters = $Tainacan_Filters->fetch_by_collection($col, ['posts_per_page' => -1], 'OBJECT');
					foreach ($_filters as $filter) {
						$include = [];
						$filter_id = $filter->get_id();
						$metadata_type = $filter->get_metadatum()->get_metadata_type();
						if ($metadata_type == 'Tainacan\Metadata_Types\Taxonomy') {
							$metadatum_options = $filter->get_metadatum()->get_metadata_type_options();
							$taxonomy_id = $metadatum_options['taxonomy_id'];
							$taxonomy_slug = Repositories\Taxonomies::get_instance()->get_db_identifier_by_id($taxonomy_id);
							$id = "$filter_id.taxonomy.$taxonomy_slug";
							$key = "terms.$taxonomy_slug.term_id";
							$field = "terms.$taxonomy_slug";
							
							if( isset($args['tax_query']) ) {
								foreach( $args['tax_query'] as $taxquery ) {
									if( $taxquery['taxonomy'] === $taxonomy_slug ) {
										$include = is_array($taxquery['terms']) ? $taxquery['terms'] : [$taxquery['terms']]; 
									}
								}
							}
							
						} else {
							$metadatum_id = $filter->get_metadatum()->get_ID();
							$id = "$filter_id.meta.$metadatum_id";
							$key = "meta.$metadatum_id.raw";
							$field = "meta.$metadatum_id.raw";
							
							if( isset($args['meta_query']) ) {
								foreach( $args['meta_query'] as $metaquery ) {
									if( $metaquery['key'] == $metadatum_id ){
										$include = is_array($metaquery['value']) ? $metaquery['value'] : [$metaquery['value']];
									}
								}
							}
						}
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
		
		add_filter('ep_formatted_args', array($this, "prepare_request")); //filtro para os argumentos já no formato a ser enviado para o elasticpress.
		return $args;
	}

	/**
	 * Create a formatted array of args to send to elasticpress containing the aggregations for items or for one single facet depending on the request
	 * 
	 * @param \Array $formatted_args initial array generated by plugin elasticpress
	 *
	 * @return \Array with formatted array of args.
	 */
	public function prepare_request($formatted_args) {
		switch ($this->aggregation_type) {
			case 'items':
				$formatted_args = $this->prepare_request_for_items($formatted_args);
				break;
			case 'facets':
				$formatted_args = $this->prepare_request_for_facet($formatted_args);
				break;
		}
		if( isset($formatted_args['sort']) ) {
			$new_sort = [];
			foreach ($formatted_args['sort'] as $sort) {
				foreach ($sort as $key => $value) {
					$parts = \explode(".", $key);
					
					if ($key == 'post_title') {
						$new_sort["$key.sortable"] = $value;
					} elseif ($key == 'post_author') {
						$new_sort["post_author.login.sortable"] = $value;
					} elseif ($key == 'post_name') {
						$new_sort["post_name.raw"] = $value;
					} elseif ($key == 'post_type') {
						$new_sort["post_type.raw"] = $value;
					} elseif( !in_array("long", $parts) && in_array("meta", $parts) ) {
						$new_sort["$key.sortable"] = $value;
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
	* Formats the response from Elastic Search to Tainacan API format 
	* 
	*/
	public function format_aggregations($aggregations) {
		switch ($this->aggregation_type) {
			case 'items':
				return $this->format_aggregations_items($aggregations);
			case 'facets':
				return  $this->format_aggregations_facet($aggregations);
		}
	}

	public function fetch_all_metadatum_values($return, $metadatum, $args) {
		
		if ($args['items_filter'] === false) {
			return null;
		}
		
		$metadatum_type = $metadatum->get_metadata_type();
		$metadatum_id = $metadatum->get_id();
		$metadatum_options = $metadatum->get_metadata_type_options();

		$args['items_filter']['ep_integrate'] = true;
		$args['items_filter']['facet_term_parent_id'] = $args['parent_id'];
		$args['items_filter']['facet_pagesize'] = $args['number'];
		$args['items_filter']['facet_last_term'] = $args['last_term'];
		$args['items_filter']['facet_search'] = $args['search'];
		$args['items_filter']['facet_metadatum_id'] = $metadatum_id;
		$args['items_filter']['facet_include'] = $args['include'];
		
		$itemsRepo = \Tainacan\Repositories\Items::get_instance();
		$items = $itemsRepo->fetch($args['items_filter'], $args['collection_id'], 'WP_Query');
		$items_aggregations = $this->last_aggregations; //if elasticPress active
		
		return [
			// 'total' => count($items_aggregations),
			// 'pages' => '0', //TODO get a total of pages
			'values' => isset($items_aggregations['values']) ? $items_aggregations['values'] : [] ,
			'last_term' => isset($items_aggregations['last_term']) ? $items_aggregations['last_term'] : ''
		];
	}

	/**
	 * Prepare the request to fetch items with information about all facets
	 * Used by the /items API endpoint 
	 * WIll add all the facets (filters) for the current collection  
	 * 
	 * JSON exemple:
	 * 
	 * {
	 *   "from" : 0,
	 *   "size" : 12,
	 *   "sort" : [ { "post_date" : { "order" : "desc" } } ],
	 *   "query" : { "match_all":{ "boost":1 } },
	 *   "post_filter" : {
	 *     "bool" : {
	 *       "must" : [
	 *          { "term" : { "post_type.raw" : "tnc_col_6_item" } },
	 *          { "term" : { "post_status":"publish" } }
	 *       ]
	 *     }
	 *   },
	 *   "aggs" : {
	 *     "872.taxonomy.tnc_tax_5" : {
	 *       "filter" : {
	 *          "bool" : {
	 *             "must" : [
	 *                { "term" : { "post_type.raw":"tnc_col_6_item" } },
	 *                { "term" : { "post_status":"publish" } }
	 *             ]
	 *          }
	 *       },
	 *       "aggs":{
	 *          "872.taxonomy.tnc_tax_5":{
	 *             "terms":{
	 *                "size":"4",
	 *                "script":{
	 *                   "lang":"painless",
	 *                   "source":"def c= ['']; for(term in params._source.terms.tnc_tax_5) { if(term.parent==0) { c.add(term.term_id); }} return c;"
	 *                }
	 *             }
	 *          }
	 *       }
	 *     },
	 *     "1026.meta.14" : {
	 *       "filter":{
	 *          "bool":{
	 *             "must":[
	 *                { "term" : { "post_type.raw":"tnc_col_6_item" } },
	 *                { "term" : { "post_status":"publish" } }
	 *             ]
	 *          }
	 *       },
	 *       "aggs":{
	 *          "1026.meta.14":{
	 *             "terms":{
	 *               "size":"4",
	 *               "field":"meta.14.raw"
	 *             }
	 *          }
	 *       }
	 *     }
	 *   }
	 * }
	 */
	private function prepare_request_for_items($formatted_args) {
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
								//"size" => $filter['max_options'],
								"script" => [
									"lang" 	=> "painless",
									"source"=> "def c= [''];if(!params._source.terms.empty && params._source.$field != null){ for(term in params._source.$field) { if(term.parent==$parent) { c.add(term.term_id); }}} return c;"
								]
							)
						)
					)
				];

				if (!empty($filter['include'])) {
					$custom_filter_include = $custom_filter;
					$custom_filter_include['bool']['must'][] = ["bool" => [ "must"=> [ [ "terms" => ["$field.term_id" => $filter['include'] ] ] ] ] ];
					$terms_id_inlcude = \implode($filter['include'], ",");
					$aggs[$id.'.include'] = [
						"filter" => $custom_filter_include,
						"aggs"	=> array(
							$id.'.include' => array(
								"terms"=>array(
									"script" => [
										"lang" 	=> "painless",
										"source"=> "def c= ['']; if(!params._source.terms.empty && params._source.$field != null) { for(term in params._source.$field) { if( [$terms_id_inlcude].contains(term.term_id) ) { c.add(term.term_id); }}} return c;"
									]
								)
							)
						)
					];
				}
			} else {
				$field = $filter['field'];
				$aggs[$id] = [
					"filter" => $custom_filter,
					"aggs"	=> array(
						$id => array(
							"terms"=>array(
								//"size" => $filter['max_options'],
								"field"=> $filter['field']
							)
						)
					)
				];

				if (!empty($filter['include'])) {
					$custom_filter_include = $custom_filter;
					$custom_filter_include['bool']['must'][] = ["bool" => [ "must"=> [ [ "terms" => ["$field" => $filter['include'] ] ] ] ] ];
					$meta_label = explode(".",$id)[1] . '.' . explode(".",$id)[2];
					$meta_id_inlcude = "'" . \implode($filter['include'], "','") . "'";
					$aggs[$id.'.include'] = [
						"filter" => $custom_filter_include,
						"aggs"	=> array(
							$id.'.include' => array(
								"terms"=>array(
									"script" => [
										"lang" 	=> "painless",
										"source"=> "def c= ['']; if(!params._source.meta.empty && params._source.$meta_label != null) { for(meta in params._source.$meta_label) { if([$meta_id_inlcude].contains(meta.raw)) { c.add(meta.raw); }}} return c;"
									]
									//"field"=> $filter['field']
								)
							)
						)
					];
				}
			}

			if($filter['use_max_options'] == true ) {
				$aggs[$id]['aggs'][$id]['terms']['size'] = $filter['max_options'];
			}

		}
		$formatted_args['aggs'] = $aggs;
		return $formatted_args;
	}

	/**
	 * Prepare the request to get information about one facet (facets API endpoint)
	 * 
	 * JSON exemple:
	 * 
	 * {
	 *   "from":0,
	 *   "size":0,
	 *   "sort":[{"post_date":{"order":"desc"}}],
	 *   "query":{
	 *     "bool":{
	 *       "must":[
	 *         {"term":{"post_type.raw":"tnc_col_6_item"}},
	 *         {"term":{"post_status":"publish"}}
	 *       ]
	 *     }
	 *   },
	 *   "aggs":{
	 *     "872.taxonomy.tnc_tax_5":{
	 *       "composite" : {
	 *         "size": 2,
	 *         "after" : { "tnc_tax_5" : "2" },
	 *         "sources" : [
	 *           { "tnc_tax_5": {
	 *             "terms": {
	 *               "script":{
	 *                  "lang":"painless",
	 *                  "source":"def c= ['']; for(term in params._source.terms.tnc_tax_5) { if(term.parent==0) { c.add(term.term_id); }} return c;"
	 *               }
	 *             }
	 *           } }
	 *         ]
	 *       }
	 *     }
	 *   }
	 * }
	 */
	private function prepare_request_for_facet($formatted_args) {
		$formatted_args['size'] 	= 0;
		$formatted_args['query'] 	= $formatted_args['post_filter'];
		$custom_filter_include = $formatted_args['post_filter'];
		unset($formatted_args['post_filter']);
		foreach($this->facets as $id => $filter) {
			$search = $filter['search'];
			$field = $filter['field'];
			if ($filter['metadata_type'] == 'Tainacan\Metadata_Types\Taxonomy') {
				$parent = $filter['parent'];
				$aggs[$id] = [
					"composite"	=> array(
						"size" => $filter['pagesize'],
						"sources" => [
							$id => [
								"terms" => [
									"script" => [
										"lang"		=> "painless",
										"source"	=> "def c= ['']; if(!params._source.terms.empty && params._source.$field != null) { for(term in params._source.$field) { if(term.parent==$parent) { c.add(term.term_id); }}} return c;"
									]
								]
							]
						]
					)
				];
				if (!empty($filter['include'])) {
					$custom_filter_include['bool']['must'][] = ["bool" => [ "must"=> [ [ "terms" => ["$field.term_id" => $filter['include'] ] ] ] ] ];
					$aggs[$id.'.include'] = [
						"filter" => $custom_filter_include,
						"aggs"	=> array(
							$id.'.include' => array(
								"terms" => array(
									"script" => [
										"lang" 	=> "painless",
										"source"=> "def c= ['']; if(!params._source.terms.empty && params._source.$field != null) { for(term in params._source.$field) { if(term.parent==$parent) { c.add(term.term_id); }}} return c;"
									]
								)
							)
						)
					];
				}

				if($search != '') {
					$formatted_args['query']['bool']['must'][] = ["wildcard"=>["$field.name.raw" => "*$search*"]];
				}
			} else {
				$aggs[$id] = [
					"composite"	=> array(
						"size" => $filter['pagesize'],
						"sources" => [ $id => [ "terms" => [ "field" => $field ] ] ]
					)
				];

				if (!empty($filter['include'])) {
					$custom_filter_include['bool']['must'][] = ["bool" => [ "must"=> [ [ "terms" => ["$field" => $filter['include'] ] ] ] ] ];
					$aggs[$id.'.include'] = [
						"filter" => $custom_filter_include,
						"aggs"	=> array(
							$id => array(
								"terms"=>array(
									"field"=> $field
								)
							)
						)
					];
				}

				if($search != '') {
					$field_relationship_label = explode ( ".", $field); 
					$field_relationship_label = "$field_relationship_label[0].$field_relationship_label[1].relationship_label";
					//$formatted_args['query']['bool']['must'][] = ["wildcard"=>["$field" => "*$search*"]];
					$formatted_args['query']['bool']['must'][] = ["bool"=>["should"=>[
						["wildcard"=>["$field"=>"*$search*"]],
						["wildcard"=>["$field_relationship_label"=>"*$search*"]] //pega nome do metadado é melhor!
					]]];
				}
			}

			$aggs[$id]['composite']['after'] = [$id => $filter['last_term'] ];
		}
		$formatted_args['aggs'] = $aggs;
		return $formatted_args;
	}

	/**
	* Format ES aggregation response for items request
	*/
	private function format_aggregations_items($aggregations) {
		global $wpdb;
		$formated_aggs = [];
		foreach($aggregations as $key => $aggregation) {
			$description_types = \explode(".", $key);
			$filter_id = $description_types[0];
			$formated_aggs[$filter_id] = isset($formated_aggs[$filter_id]) ? $formated_aggs[$filter_id] : [];
			if($description_types[1] == 'taxonomy') {
				$taxonomy_slug = $description_types[2];
				$taxonomy_id = Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($taxonomy_slug);
				foreach ($aggregation[$key]['buckets'] as $term) {
					$term_id = $term['key'];
					$term_object = \Tainacan\Repositories\Terms::get_instance()->fetch($term_id, $taxonomy_slug);
					$count_query = $wpdb->prepare("SELECT COUNT(term_id) FROM $wpdb->term_taxonomy WHERE parent = %d", $term_id);
					$total_children = $wpdb->get_var($count_query);
					$fct = [
						"type" 						=> "Taxonomy",
						"value" 					=> $term['key'],
						"taxonomy" 				=> $taxonomy_slug,
						"taxonomy_id"			=> $taxonomy_id,
						"total_children"	=> $total_children,
						"total_items"			=> $term['doc_count'],
						"label" 					=> $term_object->get('name'),
						"parent"					=> $term_object->get('parent')
					];
					if (isset($description_types[3])) {
						array_unshift($formated_aggs[$filter_id], $fct);
					} else {
						$formated_aggs[$filter_id][] = $fct;
					}
				}
			} else {
				$metada_label = $description_types[1];
				$metada_id = $description_types[2];
				if (isset($aggregation[$key]['buckets']))
				foreach ($aggregation[$key]['buckets'] as $term) {

					$label = $term['key'];
					if (\is_numeric($term['key'])) {
						$metadatadum = \Tainacan\Repositories\Metadata::get_instance()->fetch($metada_id);
						if ( isset($metadatadum->get_metadata_type_options()['collection_id'])) {
							$item = \Tainacan\Repositories\Items::get_instance()->fetch(intval($term['key']));
							$label = $item->get_title();
						}
					}
					$fct = [
						"type" 				=> "Text",
						"label" 			=> $label,
						"value" 			=> $term['key'],
						"total_items" => $term['doc_count']
					];
					if (isset($description_types[3])) {
						array_unshift($formated_aggs[$filter_id], $fct);
					} else {
						$formated_aggs[$filter_id][] = $fct;
					}
				}
			}

			//remove duplicates
			$formated_aggs[$filter_id] = array_values( array_map( 'unserialize', array_unique( array_map( 'serialize', $formated_aggs[$filter_id] ) ) ) );
			//$formated_aggs[$filter_id] = array_intersect_key($formated_aggs[$filter_id], array_unique(array_map('serialize', $formated_aggs[$filter_id])));

		}
		return $formated_aggs;
	}

	/**
	* Format ES aggregation response for one facet request
	*/
	private function format_aggregations_facet($aggregations) {
		global $wpdb;
		$formated_aggs = ['values'=>[]];
		foreach($aggregations as $key => $aggregation) {
			$description_types = \explode(".", $key);
			if($description_types[0] == 'taxonomy') {
				$has_include = isset($description_types[2]);

				$taxonomy_label = $description_types[0].'.'.$description_types[1];
				$taxonomy_slug = $description_types[1];
				$taxonomy_id = Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($taxonomy_slug);

				$buckets = ($has_include == false ? $aggregation['buckets'] : $aggregation[$key]['buckets']);
				foreach ($buckets as $term) {
					if ($has_include) {
						$term_id = $term['key'];
						$doc_count = $term['doc_count'];
					} else {
						$term_id = $term['key'][$key];
						$doc_count = $term['doc_count'];
					}
					if ($term_id == '') continue;

					$term_object = \Tainacan\Repositories\Terms::get_instance()->fetch($term_id, $taxonomy_slug);
					$count_query = $wpdb->prepare("SELECT COUNT(term_id) FROM $wpdb->term_taxonomy WHERE parent = %d", $term_id);
					$total_children = $wpdb->get_var($count_query);

					$fct = [
						"type" 						=> "Taxonomy",
						"value" 					=> $term_id,
						"taxonomy" 				=> $taxonomy_slug,
						"taxonomy_id"			=> $taxonomy_id,
						"total_children"	=> $total_children,
						"total_items"			=> $term['doc_count'],
						"label" 					=> $term_object->get('name'),
						"parent"					=> $term_object->get('parent')
					];
					if ($has_include) {
						array_unshift($formated_aggs['values'], $fct);
					} else {
						$after_key = $aggregation['after_key'];
						$formated_aggs['values'][] = $fct;
						$formated_aggs['last_term'] = $after_key[$key];
					}
				}
			} else {
				$metada_id = $description_types[1];
				$metada_label = $description_types[0].'.'.$description_types[1];
				if (isset($aggregation['buckets'])) {
					foreach ($aggregation['buckets'] as $term) {

						$label = $term['key'][$key];
						if (\is_numeric($term['key'][$key])) {
							$metadatadum = \Tainacan\Repositories\Metadata::get_instance()->fetch($metada_id);
							if ( isset($metadatadum->get_metadata_type_options()['collection_id'])) {
								$item = \Tainacan\Repositories\Items::get_instance()->fetch($term['key'][$key]);
								$label = $item->get_title();
							}
						}

						if ( isset($term['key'][$key]) ) {
							$fct = [
								"type" 				=> "Text",
								"label" 			=> $label,
								"value" 			=> $term['key'][$key],
								"total_items" => $term['doc_count']
							];
							$formated_aggs['values'][] = $fct;
						}
					}
					$after_key = $aggregation['after_key'];
					$formated_aggs['last_term'] = $after_key[$key];
				} elseif ( isset($aggregation[$metada_label]['buckets'])) {
					foreach ($aggregation[$metada_label]['buckets'] as $term) {
						$fct = [
							"type" 				=> "Text",
							"label" 			=> $term['key'],
							"value" 			=> $term['key'],
							"total_items" => $term['doc_count']
						];
						array_unshift($formated_aggs['values'], $fct);
					}
				}
			}
			//remove duplicates
			$formated_aggs['values'] = array_intersect_key($formated_aggs['values'], array_unique(array_map('serialize', $formated_aggs['values'])));
		}
		return $formated_aggs;
	}

} // END
