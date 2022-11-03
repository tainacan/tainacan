<?php
/**
 * This class implements the default Tainacan Search engine.
 * 
 * It replaces the default WordPress behavior, which is search only in the title and content of posts, and searches every item metadata.
 * 
 * This is a very basic and non-performatic approach. For better results, you should try other WordPress plugins for this task. We recommend integration with Elastic Search
 * 
 * 
 * This class is a modification of the class found in the Search Everything plugin. All credits to the authors
 * http://wordpress.org/plugins/search-everything/
 * Author: Sovrn, zemanta
 * Author URI: http://www.sovrn.com
 * 
 */

namespace Tainacan;

class Search_Engine {

	var $logging = false;
	var $options;
	var $ajax_request;
	private $query_instance;

	private $taxonomies = [];
	private $relationships = [];
	private $is_tainacan_search = false;
	private $is_inner_query = false;

	function __construct($ajax_query=false) {
		$this->ajax_request = $ajax_query ? true : false;
		$this->options = [];

		if (!defined('TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE') || TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE !== true) {
			$this->search_hooks();
		}
	}

	function search_hooks() {

		// add_filter( 'posts_join', array( &$this, 'terms_join' ) );
		// add_filter( 'posts_join', array( &$this, 'search_metadata_join' ) );
		add_filter( 'posts_join', array( &$this, 'relationships_join' ) );

		//add_filter( 'posts_where', array( &$this, 'search_attachments' ) );

		add_filter( 'posts_search', array( &$this, 'search_where' ), 10, 2 );
		add_filter( 'posts_request', array( &$this, 'distinct' ) );
		//add_filter( 'posts_request', array( &$this, 'log_query' ), 10, 2 );
	}

	// creates the list of search keywords from the 's' parameters.
	function get_search_terms() {
		$s = isset( $this->query_instance->query_vars['s'] ) ? $this->query_instance->query_vars['s'] : '';
		$sentence = isset( $this->query_instance->query_vars['sentence'] ) ? $this->query_instance->query_vars['sentence'] : false;
		$search_terms = array();

		if ( !empty( $s ) ) {
			// added slashes screw with quote grouping when done early, so done later
			$s = stripslashes( $s );
			if ( $sentence ) {
				$search_terms = array( $s );
			} else {
				preg_match_all( '/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches );

				// The method create_function is deprecated in PHP 7.2, then it was changed to anonymous functions
				$search_terms = array_filter(array_map(
					function($a) {
						return trim($a, " '\"\n\r\t\v\0");
					},
					$matches[0]
				));
			}
		}

		return $search_terms;
	}

	function init_tainacan_search_vars() {

		if (!$this->query_instance->is_search() || empty($this->query_instance->query_vars['s']) ) {
			$this->is_tainacan_search = false;
			return;
		}

		$post_types = $this->query_instance->get('post_type');

		$taxonomies = [];
		$relationships = [];

		if (!$post_types || empty($post_types) || !is_array($post_types)) {
			$this->is_tainacan_search = false;
		} else {
			foreach ($post_types as $pt) {
				$pattern = '/^' . \Tainacan\Entities\Collection::$db_identifier_prefix . '(\d+)' . \Tainacan\Entities\Collection::$db_identifier_sufix . '$/';
				if ( preg_match_all($pattern, $pt, $matches) ) {
					$taxonomies = array_merge( $taxonomies, get_object_taxonomies($pt) );
					if (isset($matches[1][0])) {
						$this->is_inner_query = true;
						$relationships = array_merge( $relationships, \Tainacan\Repositories\Metadata::get_instance()->fetch_ids_by_collection( (int) $matches[1][0], ['metadata_type' => 'Tainacan\Metadata_Types\Relationship'] ) );
						$this->is_inner_query = false;
					}
					$this->is_tainacan_search = true;
				} else {
					$this->is_tainacan_search = false;
					break;
				}
			}
		}
		if ( $this->is_tainacan_search ) {
			$taxonomies = array_unique($taxonomies);
			$relationships = array_unique($relationships);
			$this->taxonomies = $taxonomies;
			$this->relationships = $relationships;
		}
	}

	private function build_search_terms_query ($type = 'default') {
		global $wpdb;
		$searchQuery = '';
		$seperator = '';
		$not_exact = empty($this->query_instance->query_vars['exact']);
		$terms = $this->get_search_terms();
		foreach ( $terms as $term ) {
			$esc_term = $wpdb->prepare("%s", $not_exact ? "%".$term."%" : $term);
			switch ($type) {
				case 'tax_terms':
					$searchQuery .= "{$seperator}(tter.name LIKE {$esc_term})";
					break;
				case 'meta_terms':
					if (defined('TAINACAN_SEARCH_ENGINE_USING_FULL_TEXT') && TAINACAN_SEARCH_ENGINE_USING_FULL_TEXT === true) {
						$esc_term = $wpdb->prepare("%s", $not_exact ? "*".$term."*" : $term);
						$searchQuery .= "{$seperator}(MATCH (m.meta_value) AGAINST ({$esc_term} IN BOOLEAN MODE))";
					} else {
						$searchQuery .= "{$seperator}(m.meta_value LIKE {$esc_term})";
					}
					break;
				default:
					if ( !empty( $this->relationships ) ) {
						$searchQuery .= "{$seperator}($wpdb->posts.post_title LIKE {$esc_term} OR $wpdb->posts.post_content LIKE {$esc_term} OR p2->posts.post_title LIKE {$esc_term} OR p2.post_content LIKE {$esc_term})";
					} else {
						$searchQuery .= "{$seperator}($wpdb->posts.post_title LIKE {$esc_term} OR $wpdb->posts.post_content LIKE {$esc_term})";
					}
					break;
			}
			$seperator = ' OR ';
		}
		return empty($searchQuery) ? false : "($searchQuery)";
	}

	function get_where_to_term_taxonomies() {
		global $wpdb;
		$join = '';
		$searchTaxQuery = $this->build_search_terms_query('tax_terms');
		if ( $searchTaxQuery != false && $this->is_tainacan_search && !empty( $this->taxonomies ) ) {
			$tax_where = ' ttax.taxonomy IN ( \'' . implode( '\',\'', $this->taxonomies ) . '\' ) ';
			$join = "EXISTS (
				SELECT trel.object_id
				FROM
					$wpdb->term_relationships AS trel
					INNER JOIN $wpdb->term_taxonomy AS ttax ON trel.term_taxonomy_id = ttax.term_taxonomy_id
					INNER JOIN $wpdb->terms AS tter ON ttax.term_id = tter.term_id
				WHERE
					$wpdb->posts.ID = trel.object_id AND $tax_where AND ( $searchTaxQuery )
			)";
		}
		return $join;
	}

	function get_where_to_metadatas() {
		global $wpdb;
		$join = "";
		$searchMetaQuery = $this->build_search_terms_query('meta_terms');
		if ( $searchMetaQuery != false && $this->is_tainacan_search ) {
			$join .= "EXISTS (
				SELECT m.post_id
				FROM $wpdb->postmeta m
				WHERE ( $wpdb->posts.ID = m.post_id AND $searchMetaQuery )
			)";
		}
		return $join;
	}

	// add where clause to the search query
	function search_where( $where, $wp_query ) {

		if ($this->is_inner_query) {
			return $where;
		}

		$this->query_instance = &$wp_query;
		$this->init_tainacan_search_vars();

		if ( !$this->is_tainacan_search && !$this->ajax_request)
			return $where;

		$searchQuery = $this->build_search_terms_query();
		$searchTaxQuery = $this->get_where_to_term_taxonomies();
		$searchMetaQuery = $this->get_where_to_metadatas();
		$searchQuery = "($searchQuery) ";
		if(!empty($searchTaxQuery)) $searchQuery .= " OR ($searchTaxQuery) "; 
		if(!empty($searchMetaQuery)) $searchQuery .= " OR ($searchMetaQuery) ";

		if ( $searchQuery != '' && $searchQuery != '()' ) {
			// lets use _OUR_ query instead of WP's, as we have posts already included in our query as well(assuming it's not empty which we check for)
			$where = " AND ((" . $searchQuery . ")) ";
		}
		return $where;
	}

	//Duplicate fix provided by Tiago.Pocinho
	function distinct( $query ) {
		if ( !empty( $this->query_instance->query_vars['s'] ) ) {
			if ( strstr( $query, 'DISTINCT' ) ) {}
			else {
				$query = str_replace( 'SELECT', 'SELECT DISTINCT', $query );
			}
		}
		return $query;
	}

	// join for relationship metadata
	function relationships_join( $join ) {
		
		if ($this->is_inner_query) {
			return $join;
		}
		
		global $wpdb;

		if ( $this->is_tainacan_search && !empty( $this->relationships ) ) {

			$relationships = implode(',', $this->relationships);
			$join .= " LEFT JOIN $wpdb->posts AS p2 ON (m.meta_value = p2.ID AND m.meta_key IN ($relationships)) ";
		}
		return $join;
	}

// 	//search attachments
// 	function search_attachments( $where ) {
// 		global $wpdb;
// 		if ( !empty( $this->query_instance->query_vars['s'] ) ) {
// 			$where = str_replace( '"', '\'', $where );
// 			if ( !$this->wp_ver28 ) {
// 				$where = str_replace( " AND (post_status = 'publish'", " AND (post_status = 'publish' OR post_type = 'attachment'", $where );
// 				$where = str_replace( "AND post_type != 'attachment'", "", $where );
// 			}
// 			else {
// 				$where = str_replace( " AND ($wpdb->posts.post_status = 'publish'", " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_type = 'attachment'", $where );
// 				$where = str_replace( "AND $wpdb->posts.post_type != 'attachment'", "", $where );
// 			}
// 		}
// 		return $where;
// 	}

// 	//join for searching metadata
// 	function search_metadata_join( $join ) {
		
// 		if ($this->is_inner_query) {
// 			return $join;
// 		}
		
// 		global $wpdb;
// 		if ( $this->is_tainacan_search ) {
// 			$searchMetaQuery = $this->build_search_terms_query('meta_terms');
// 			$join .= <<<EOF
// 			LEFT JOIN
// 			(
// 				SELECT
// 					m.post_id, true as contains
// 				FROM 
// 					$wpdb->postmeta m
// 				WHERE
// 					( $searchMetaQuery )
// 			) AS metas ON $wpdb->posts.ID = metas.post_id
// EOF;
// 		}
// 		return $join;
// 	}

// 	//join for searching taxonomies terms
// 	function terms_join( $join ) {
		
// 		if ($this->is_inner_query) {
// 			return $join;
// 		}
		
// 		global $wpdb;
// 		$searchTaxQuery = $this->build_search_terms_query('tax_terms');
// 		if ( $this->is_tainacan_search && !empty( $this->taxonomies ) ) {
// 			$tax_where = ' ttax.taxonomy IN ( \'' . implode( '\',\'', $this->taxonomies ) . '\' ) ';
// 			$join .= <<<EOF
// 				LEFT JOIN (
// 					SELECT DISTINCT
// 						trel.object_id as post_id,
// 						true as contains
// 					FROM
// 						$wpdb->term_relationships AS trel
// 						INNER JOIN $wpdb->term_taxonomy AS ttax ON trel.term_taxonomy_id = ttax.term_taxonomy_id
// 						INNER JOIN $wpdb->terms AS tter ON ttax.term_id = tter.term_id
// 					WHERE
// 						$tax_where AND ( $searchTaxQuery )
// 				) tax_terms ON $wpdb->posts.ID = tax_terms.post_id 
// EOF;
// 		}
// 		return $join;
// 	}

}