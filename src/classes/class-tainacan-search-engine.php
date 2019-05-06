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

        add_filter( 'posts_join', array( &$this, 'terms_join' ) );
		
		add_filter( 'posts_join', array( &$this, 'search_metadata_join' ) );
		
		add_filter( 'posts_join', array( &$this, 'relationships_join' ) );

        //add_filter( 'posts_where', array( &$this, 'search_attachments' ) );

        add_filter( 'posts_join', array( &$this, 'search_authors_join' ) );
		
		add_filter( 'posts_search', array( &$this, 'search_where' ), 10, 2 );

        add_filter( 'posts_request', array( &$this, 'distinct' ) );
        
		//add_filter( 'posts_request', array( &$this, 'log_query' ), 10, 2 );
	}

	// creates the list of search keywords from the 's' parameters.
	function get_search_terms() {
		global $wpdb;
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
						return trim($a, '\\"\'\\n\\r ');
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

	// add where clause to the search query
	function search_where( $where, $wp_query ) {
        
        if ($this->is_inner_query) {
			return $where;
		}
		
		$this->query_instance = &$wp_query;

        $this->init_tainacan_search_vars();
        
        if ( !$this->is_tainacan_search && !$this->ajax_request)
			return $where;

		global $wpdb;

        $searchQuery = $this->search_default();

        $searchQuery .= $this->build_search_categories();

		$searchQuery .= $this->build_search_metadata();
		
		$searchQuery .= $this->build_search_relationships();

		$searchQuery .= $this->search_authors();
		

		if ( $searchQuery != '' && $searchQuery != '()' ) {
            // lets use _OUR_ query instead of WP's, as we have posts already included in our query as well(assuming it's not empty which we check for)
			$where = " AND ((" . $searchQuery . ")) ";
		}

		return $where;
    }
    
	// search for terms in default locations like title and content
	// replacing the old search terms seems to be the best way to
	// avoid issue with multiple terms
	function search_default(){
		global $wpdb;
		$not_exact = empty($this->query_instance->query_vars['exact']);
		$search_sql_query = '';
		$seperator = '';
		$terms = $this->get_search_terms();

		// if it's not a sentance add other terms
		$search_sql_query .= '(';

		foreach ( $terms as $term ) {
			$search_sql_query .= $seperator;

			$esc_term = $wpdb->prepare("%s", $not_exact ? "%".$term."%" : $term);

			$like_title = "($wpdb->posts.post_title LIKE $esc_term)";
			$like_post = "($wpdb->posts.post_content LIKE $esc_term)";

			$search_sql_query .= "($like_title OR $like_post)";

			$seperator = ' AND ';
		}

		$search_sql_query .= ')';
		return $search_sql_query;
	}


	//Duplicate fix provided by Tiago.Pocinho
	function distinct( $query ) {
		global $wpdb;
		if ( !empty( $this->query_instance->query_vars['s'] ) ) {
			if ( strstr( $query, 'DISTINCT' ) ) {}
			else {
				$query = str_replace( 'SELECT', 'SELECT DISTINCT', $query );
			}
		}
		return $query;
	}


	//search attachments
	function search_attachments( $where ) {
		global $wpdb;
		if ( !empty( $this->query_instance->query_vars['s'] ) ) {
			$where = str_replace( '"', '\'', $where );
			if ( !$this->wp_ver28 ) {
				$where = str_replace( " AND (post_status = 'publish'", " AND (post_status = 'publish' OR post_type = 'attachment'", $where );
				$where = str_replace( "AND post_type != 'attachment'", "", $where );
			}
			else {
				$where = str_replace( " AND ($wpdb->posts.post_status = 'publish'", " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_type = 'attachment'", $where );
				$where = str_replace( "AND $wpdb->posts.post_type != 'attachment'", "", $where );
			}
		}
		return $where;
	}

	// Build the author search
	function search_authors() {
		global $wpdb;
		$s = $this->query_instance->query_vars['s'];
		$search_terms = $this->get_search_terms();
		$exact = ( isset( $this->query_instance->query_vars['exact'] ) && $this->query_instance->query_vars['exact'] ) ? true : false;
		$search = '';
		$searchand = '';

		if ( !empty( $search_terms ) ) {
			// Building search query
			foreach ( $search_terms as $term ) {
				$term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                $search .= "{$searchand}(u.display_name LIKE $term)";
				$searchand = ' OR ';
			}
			$sentence_term = $wpdb->prepare("%s", $s);
			if ( count( $search_terms ) > 1 && $search_terms[0] != $sentence_term ) {
                $search .= " OR (u.display_name LIKE $sentence_term)";
			}

			if ( !empty( $search ) )
				$search = " OR ({$search}) ";

		}

		return $search;
	}
	
	function build_search_relationships(){
		
		if ( empty( $this->relationships ) ) {
			return '';
		}
		
		global $wpdb;
		$s = $this->query_instance->query_vars['s'];
		$search_terms = $this->get_search_terms();
		$exact = ( isset( $this->query_instance->query_vars['exact'] ) && $this->query_instance->query_vars['exact'] ) ? true : false;
		$search = '';

		if ( !empty( $search_terms ) ) {
			// Building search query
			$searchand = '';
			foreach ( $search_terms as $term ) {
				$term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                $search .= "{$searchand}(p2.post_title LIKE $term)";
				$searchand = ' AND ';
			}
			$sentence_term = $wpdb->prepare("%s", $s);
			if ( count( $search_terms ) > 1 && $search_terms[0] != $sentence_term ) {
                $search = "($search) OR (p2.post_title LIKE $sentence_term)";
			}

			if ( !empty( $search ) )
				$search = " OR ({$search}) ";

		}
		return $search;
	}

	// create the search meta data query
	function build_search_metadata() {
		global $wpdb;
		$s = $this->query_instance->query_vars['s'];
		$search_terms = $this->get_search_terms();
		$exact = ( isset( $this->query_instance->query_vars['exact'] ) && $this->query_instance->query_vars['exact'] ) ? true : false;
		$search = '';

		if ( !empty( $search_terms ) ) {
			// Building search query
			$searchand = '';
			foreach ( $search_terms as $term ) {
				$term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                $search .= "{$searchand}(m.meta_value LIKE $term)";
				$searchand = ' AND ';
			}
			$sentence_term = $wpdb->prepare("%s", $s);
			if ( count( $search_terms ) > 1 && $search_terms[0] != $sentence_term ) {
                $search = "($search) OR (m.meta_value LIKE $sentence_term)";
			}

			if ( !empty( $search ) )
				$search = " OR ({$search}) ";

		}
		return $search;
	}

	// create the search categories query
	function build_search_categories() {
		global $wpdb;
		$vars = $this->query_instance->query_vars;

		if (empty($this->taxonomies)) {
			return '';
		}

		$s = $vars['s'];
		$search_terms = $this->get_search_terms();
		$exact = isset( $vars['exact'] ) ? $vars['exact'] : '';
		$search = '';

		if ( !empty( $search_terms ) ) {
			// Building search query for categories slug.
			$searchand = '';
			$searchSlug = '';
			foreach ( $search_terms as $term ) {
				$term = $wpdb->prepare("%s", $exact ? $term : "%". $term . "%");
				$searchSlug .= "{$searchand}(tter.name LIKE $term)";
				$searchand = ' AND ';
			}

			$term = $wpdb->prepare("%s", $exact ? $term : "%". $s . "%");
			if ( count( $search_terms ) > 1 && $search_terms[0] != $s ) {
				$searchSlug = "($searchSlug) OR (tter.name LIKE $term)";
			}
			if ( !empty( $searchSlug ) )
				$search = " OR ({$searchSlug}) ";

			// Building search query for categories description.
			$searchand = '';
			$searchDesc = '';
			foreach ( $search_terms as $term ) {
                $term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
				$searchDesc .= "{$searchand}(ttax.description LIKE $term)";
				$searchand = ' AND ';
			}
			$sentence_term = $wpdb->prepare("%s", $s);
			if ( count( $search_terms ) > 1 && $search_terms[0] != $sentence_term ) {
				$searchDesc = "($searchDesc) OR (ttax.description LIKE $sentence_term)";
			}
			if ( !empty( $searchDesc ) )
				$search = $search." OR ({$searchDesc}) ";
		}
		return $search;
	}


	//join for searching authors

	function search_authors_join( $join ) {
		
		if ($this->is_inner_query) {
			return $join;
		}
		
		global $wpdb;

		if ( $this->is_tainacan_search ) {
			$join .= " LEFT JOIN $wpdb->users AS u ON ($wpdb->posts.post_author = u.ID) ";
		}
		return $join;
	}

	//join for searching metadata
	function search_metadata_join( $join ) {
		
		if ($this->is_inner_query) {
			return $join;
		}
		
		global $wpdb;

		if ( $this->is_tainacan_search ) {

            $join .= " LEFT JOIN $wpdb->postmeta AS m ON ($wpdb->posts.ID = m.post_id) ";
		}
		return $join;
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

	//join for searching tags
	function terms_join( $join ) {
		
		if ($this->is_inner_query) {
			return $join;
		}
		
		global $wpdb;

		if ( $this->is_tainacan_search && !empty( $this->taxonomies ) ) {

            foreach ( $this->taxonomies as $taxonomy ) {
                $on[] = "ttax.taxonomy = '" . addslashes( $taxonomy )."'";
            }
			// build our final string
			$on = ' ( ' . implode( ' OR ', $on ) . ' ) ';
			$join .= " LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ttax ON ( " . $on . " AND trel.term_taxonomy_id = ttax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) ";
		}
		return $join;
	}


} // END
