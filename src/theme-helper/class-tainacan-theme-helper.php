<?php

namespace Tainacan;
use Tainacan\Entities;


class Theme_Helper {

	private static $instance = null;
	
	public $visiting_collection_cover = false;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {

		add_filter( 'the_content', [&$this, 'the_content_filter'] );
		
		
		// Replace collections permalink to post type archive if cover not enabled
		add_filter('post_type_link', array(&$this, 'permalink_filter'), 10, 3);

		// Replace single query to the page content set as cover for the colllection
		// Redirect to post type archive if no cover page is set
		add_action('wp', array(&$this, 'collection_single_redirect'));
		
		add_action('wp_print_scripts', array(&$this, 'enqueue_scripts'));
		
		// make archive for terms work with items
		add_action('pre_get_posts', array(&$this, 'tax_archive_pre_get_posts'));
		
		add_action('archive_template_hierarchy', array(&$this, 'items_template_hierachy'));
		add_action('single_template_hierarchy', array(&$this, 'items_template_hierachy'));
		
		add_filter('theme_mod_header_image', array(&$this, 'header_image'));

		add_filter('get_the_archive_title', array(&$this, 'filter_archive_title'));

		add_shortcode( 'tainacan-search', array(&$this, 'search_shortcode'));
		
	}
	
	public function enqueue_scripts($force = false) {
		global $TAINACAN_BASE_URL;
		if ( $force || is_post_type_archive( \Tainacan\Repositories\Repository::get_collections_db_identifiers() ) ) {
			//\Tainacan\Admin::get_instance()->add_admin_js();
			wp_enqueue_script('tainacan-search', $TAINACAN_BASE_URL . '/assets/user_search-components.js' , [] , null, true);
			wp_localize_script('tainacan-search', 'tainacan_plugin', \Tainacan\Admin::get_instance()->get_admin_js_localization_params());
		}
	}
	
	public function is_post_an_item(\WP_Post $post) {
		$post_type = $post->post_type;
		$prefix = substr( $post_type, 0, strlen( Entities\Collection::$db_identifier_prefix ) );
		return $prefix == Entities\Collection::$db_identifier_prefix;
	}
	
	public function is_taxonomy_a_tainacan_tax($tax_slug) {
		$prefix = substr( $tax_slug, 0, strlen( Entities\Taxonomy::$db_identifier_prefix ) );
		return $prefix == Entities\Taxonomy::$db_identifier_prefix;
	}
	
	public function is_term_a_tainacan_term( \WP_Term $term ) {
		return $this->is_taxonomy_a_tainacan_tax($term->taxonomy);
	}
	
	public function filter_archive_title($title) {
		if (is_post_type_archive()) {
			
			$collections_post_types = \Tainacan\Repositories\Repository::get_collections_db_identifiers();
			$current_post_type = get_post_type();
			
			if (in_array($current_post_type, $collections_post_types)) {
				$title = sprintf( __( 'Collection: %s' ), post_type_archive_title( '', false ) );
			}
		}
		return $title;
	}

	public function the_content_filter($content) {
		global $post;
		
		if (!is_single())
			return $content;
		
		// Is it a collection Item?
		if ( !$this->is_post_an_item($post) ) {
			return $content;
		}
		
		$item = new Entities\Item($post);
		$content = '';
		
		// document
		$content .= $item->get_document_html();
		
		// metadata
		$content .= $item->get_metadata_as_html();
		
		// attachments
		
		return $content;
		
	}
	
	/**
     * Filters the permalink for posts to:
     *
     * * Replace Collectino single permalink with the link to the post type archive for items of that collection
     * 
     * @return string new permalink
     */
    function permalink_filter($permalink, $post, $leavename) {
        
        $collection_post_type = \Tainacan\Entities\Collection::get_post_type();
        
        if (!is_admin() && $post->post_type == $collection_post_type) {
            
            $collection = new \Tainacan\Entities\Collection($post);
            
			if ( $collection->is_cover_page_enabled() ) {
				return $permalink;
			}
			
			$items_post_type = $collection->get_db_identifier();
            
            $post_type_object = get_post_type_object($items_post_type);
            
            if (isset($post_type_object->rewrite) && is_array($post_type_object->rewrite) && isset($post_type_object->rewrite['slug']))
                return site_url($post_type_object->rewrite['slug']);
                
        }
        
        return $permalink;
        
    }
	
	function tax_archive_pre_get_posts($wp_query) {
		
		if (!$wp_query->is_tax() || !$wp_query->is_main_query())
			return;
		
		$term = get_queried_object();
		
		if ($term instanceof \WP_Term && $this->is_term_a_tainacan_term($term)) {
			// TODO: Why post_type = any does not work? 
			// ANSWER because post types are registered with exclude_from_search. Should we change it?
			$wp_query->set( 'post_type', \Tainacan\Repositories\Repository::get_collections_db_identifiers() );
		}
		
	}
	
	function collection_single_redirect() {
		
		if (is_single() && get_post_type() == \Tainacan\Entities\Collection::$post_type) {
			
			$post = get_post();
			
			$collection = new \Tainacan\Entities\Collection($post);
			
			if ( ! $collection->is_cover_page_enabled() ) {
				
				wp_redirect(get_post_type_archive_link( $collection->get_db_identifier() ));
				
			} else {
				
				$cover_page_id = $collection->get_cover_page_id();
				
				if ($cover_page_id) {
					
					// TODO: it would be better to do this via pre_get_posts. But have to find out how to do it
					// Without generating a redirect.
					// Another question is that, doing this way, hooking in wp, assures that the template loader 
					// still looks for tainacan-collection-single, and not for page.
					
					global $wp_query;
					$wp_query = new \WP_Query('page_id=' . $cover_page_id);
					
					$this->visiting_collection_cover = $collection->get_id();
				}
				
			}
			
		}
		
		
	}
	
	function items_template_hierachy($templates) {
		
		if (is_post_type_archive() || is_single()) {
			
			$collections_post_types = \Tainacan\Repositories\Repository::get_collections_db_identifiers();
			$current_post_type = get_post_type();
			
			if (in_array($current_post_type, $collections_post_types)) {
				
				$last_template = array_pop($templates);
				
				if (is_post_type_archive()) {
					array_push($templates, 'tainacan/archive-items.php');
				} elseif (is_single()) {
					array_push($templates, 'tainacan/single-items.php');
				}
				
				array_push($templates, $last_template);
				
			}
			
		}
		
		return $templates;
		
	}
	
	function header_image($image) {
		
		$object = false;
		
		if ($collection_id = tainacan_get_collection_id()) {
			$object = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_id);
		} elseif ($term = tainacan_get_term()) {
			$object = \Tainacan\Repositories\Terms::get_instance()->fetch($term->term_id, $term->taxonomy);
		}
		
		if (!$object)
			return $image;
		
		$header_image = $object->get_header_image_id();
		
		if (is_numeric($header_image)) {
			$src = wp_get_attachment_image_src($header_image, 'full');
			if (is_array($src)) {
				$image = $src[0];
			}
		}
		
		return $image;
	}

	public function search_shortcode($atts) {
		
		$atts = shortcode_atts(
			array(
				'collection-id' => '',
				'term' => '',
			),
			$atts
		);

		$params = '';
		if (isset($atts['collection-id'])) {
			$params = "collection-id=" . $atts['collection-id'];
		}
		
		$this->enqueue_scripts(true);

		return "<div id='tainacan-items-page' $params ></div>";


	}
	
}

