<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Entities;

/**
 * Theme helper class for Tainacan.
 *
 * Provides theme integration functionality including template overrides,
 * view modes, template tags, and theme compatibility features.
 *
 * @since 1.0.0
 */
class Theme_Helper {
	use \Tainacan\Traits\Singleton_Instance;
	
	public $visiting_collection_cover = false;

	/**
	 * Stores view modes available to be used by the theme
	 */
	private $registered_view_modes = [];
	protected $default_placeholder_template = '';

	private function init() {

		// Add support for Tainacan templates, in case a theme does not support it
		if ( 
			defined('TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER')
				? ( true !== TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER )
				: get_option( 'tainacan_option_override_item_single_template', true )
		) {
			$this->override_item_single_template();

			// add_action( 'init', [$this, 'register_block_templates_for_item_single'] );
		}

		if ( 
			defined('TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER')
				? ( true !== TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER )
				: get_option( 'tainacan_option_override_taxonomy_single_template', true )	
		) {
			$this->override_taxonomy_single_template();
		}

		if ( 
			defined('TAINACAN_ENABLE_COLLECTION_THE_CONTENT_FILTER') 
				? ( true === TAINACAN_ENABLE_COLLECTION_THE_CONTENT_FILTER )
        		: get_option('tainacan_option_override_collection_items_archive_template', false) 
		) {
			$this->override_collection_items_archive_template();

			// add_action( 'init', [$this, 'register_block_templates_for_collection_items_archive'] );
		}

		if ( 
			defined('TAINACAN_ENABLE_TAXONOMY_TERM_THE_CONTENT_FILTER')
				? ( true === TAINACAN_ENABLE_TAXONOMY_TERM_THE_CONTENT_FILTER )
				: get_option( 'tainacan_option_override_taxonomy_term_items_archive_template', false )
		) {
			$this->override_taxonomy_term_items_archive_template();
		}

		if (
			defined('TAINACAN_ENABLE_REPOSITORY_THE_CONTENT_FILTER')
				? ( true === TAINACAN_ENABLE_REPOSITORY_THE_CONTENT_FILTER )
				: get_option( 'tainacan_option_override_repository_items_archive_template', false )
		) {
			$this->override_repository_items_archive_template();
		}

		// Replace collections permalink to post type archive if cover not enabled
		add_filter('post_type_link', array($this, 'permalink_filter'), 10, 3);

		// Replace single query to the page content set as cover for the collection
		// Redirect to post type archive if no cover page is set
		add_action('wp', array($this, 'collection_single_redirect'));
		
		// make archive for terms work with items
		add_action('pre_get_posts', array($this, 'tax_archive_pre_get_posts'));
		
		add_action('archive_template_hierarchy', array($this, 'collection_items_template_hierarchy'));
		add_action('taxonomy_template_hierarchy', array($this, 'taxonomy_term_items_template_hierarchy'));
		add_action('single_template_hierarchy', array($this, 'item_template_hierarchy'));
		add_action('single_template_hierarchy', array($this, 'taxonomy_terms_template_hierarchy'));
		
		add_filter('theme_mod_header_image', array($this, 'header_image'));

		add_filter('get_the_archive_title', array($this, 'filter_archive_title'));

		add_shortcode( 'tainacan-search', array($this, 'search_shortcode'));
		add_shortcode( 'tainacan-item-submission', array($this, 'item_submission_shortcode'));
		add_shortcode( 'tainacan-items-carousel', array($this, 'get_tainacan_items_carousel'));
		add_shortcode( 'tainacan-items-gallery', array($this, 'get_tainacan_items_gallery'));
		add_shortcode( 'tainacan-terms-carousel', array($this, 'get_tainacan_terms_carousel'));
		add_shortcode( 'tainacan-dynamic-items-list', array($this, 'get_tainacan_dynamic_items_list'));
		add_shortcode( 'tainacan-related-items-carousel', array($this, 'get_tainacan_related_items_carousel'));
		add_shortcode( 'tainacan-related-items-list', array($this, 'get_tainacan_related_items_list'));

		add_action( 'generate_rewrite_rules', array( &$this, 'rewrite_rules' ), 10, 1 );
		add_filter( 'query_vars', array( &$this, 'rewrite_rules_query_vars' ) );
		add_filter( 'template_include', array( &$this, 'rewrite_rule_template_include' ) );
		add_action( 'pre_get_posts', array($this, 'archive_repository_pre_get_posts'));
		// TODO: fix the WP Title 
		// add_filter( 'wp_title', array($this, 'archive_repository_wp_title'), 10, 3);
		
		add_action( 'wp_head', array($this, 'add_social_meta'), 5 );

		// Registers view modes and their placeholders
		add_action( 'init' , array($this, 'register_tainacan_oficial_view_modes') );
	}
	
	public function is_post_type_a_collection($post_type) {
		$prefix = substr( $post_type, 0, strlen( Entities\Collection::$db_identifier_prefix ) );
		return $prefix == Entities\Collection::$db_identifier_prefix;
	}

	public function is_post_an_item(\WP_Post $post) {
		$post_type = $post->post_type;
		return $this->is_post_type_a_collection($post_type);
	}

	public function is_post_a_tainacan_taxonomy_postype(\WP_Post $post) {
		$post_type = $post->post_type;
		return $post_type == Entities\Taxonomy::$post_type;
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
	
			if ( $this->is_post_type_a_collection( get_post_type() ) ) {
				/* translators: %s is the collection name */
				$title = sprintf( __( 'Collection: %s', 'tainacan' ), post_type_archive_title( '', false ) );
			}
			
		} elseif ( is_archive() && get_query_var('tainacan_repository_archive') == 1 ) {
			$title = __( 'All items in repository', 'tainacan' );
		}
		
		return $title;
	}

	/**
	 * Overrides the WordPress post single to display a Tainacan item
	 * basic structure with Media Gallery, Metadata Sections and Attachments
	 *
	 */
	public function override_item_single_template() {

		add_filter( 'the_content', function($content) {

			if ( !is_single() )
				return $content;

			$post = get_queried_object();
			
			// Is it a collection Item 
			if ( !($post instanceof \WP_Post) || !$this->is_post_an_item($post) )
				return $content;
		
			return $this->get_tainacan_item_single_content();

		} );
	}

	/**
	 * Uses register_block_template to define a default block-based template
	 * for the item single page in FSE themes
	 */
	function register_block_templates_for_item_single() {

		if ( function_exists('register_block_template') && wp_is_block_theme() ) {
			$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
			$collections          = $Tainacan_Collections->fetch( [], 'OBJECT' );

			foreach( $collections as $collection ) {
				register_block_template( 'tainacan//single-' . $collection->get_db_identifier(), [
					'title'       => __( 'Single item', 'tainacan' ) . ': ' . $collection->get_name(),
					'description' => __( 'An open template for use with single items. Includes a basic structure with site header, item gallery, metadata and footer.', 'tainacan' ),
					'post_types'  => [ $collection->get_db_identifier() ],
					'content'     => '<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->

					<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
					<main class="wp-block-group">
						
						<!-- wp:post-title {"level":1} /-->

						<!-- wp:tainacan/item-gallery { "templateMode": true, "collectionId": "' . $collection->get_id() . '" } /-->

						<!-- wp:tainacan/item-metadata-sections { "templateMode":true,"isDynamic":true, "collectionId": "' . $collection->get_id() . '" } /-->

					</main>
					<!-- /wp:group -->

					<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->'
					] );
			}
		}
	}

	/**
	 * Overrides the WordPress blog-like archive to display collection
	 * items list using the faceted search block.
	 * 
	 * @return string HTML containing the faceted search block for items
	 */
	public function override_collection_items_archive_template() {

		if ( is_admin() )
			return;

		// Classic themes
		if ( !wp_is_block_theme() ) {

			add_action('loop_start', function( $query ) {
				if ( $query->is_main_query() && is_post_type_archive() && $this->is_post_type_a_collection( get_post_type() ) )
					ob_start();	
			});

			add_action('loop_end', function( $query ) {
				if ( $query->is_main_query() && is_post_type_archive() && $this->is_post_type_a_collection( get_post_type() ) ) {
					$theme_archive_content = ob_get_clean();
					$tainacan_archive_content = \Tainacan\Theme_Helper::get_tainacan_items_list(array());
					/**
					 * Note to code reviewers: This line doesn't need to be escaped.
					 * The output is escaped by the get_tainacan_items_list function,
					 * which uses wp_kses_post() for all dynamic content.
					 */
					/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
					echo $tainacan_archive_content;
				}
			});

		// Block-based FSE themes
		} else {

			add_filter('render_block', function($block_content, $block) {
				if ( $block['blockName'] === 'core/query' && is_post_type_archive() && $this->is_post_type_a_collection( get_post_type() ) )
					return \Tainacan\Theme_Helper::get_tainacan_items_list(array());
				
				return $block_content;
			}, 10, 2);
		}
	}

	/**
	 * Uses register_block_template to define a default block-based template
	 * for the collection items archive page in FSE themes
	 */
	function register_block_templates_for_collection_items_archive() {

		if ( function_exists('register_block_template') && wp_is_block_theme() ) {
			$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
			$collections          = $Tainacan_Collections->fetch( [], 'OBJECT' );

			foreach( $collections as $collection ) {
				register_block_template( 'tainacan//archive-' . $collection->get_db_identifier(), [
					'title'       => __( 'List of items', 'tainacan' ) . ': ' . $collection->get_name(),
					'description' => __( 'An open template for use with a lists of items. Includes a structure with site header, faceted search and footer.', 'tainacan' ),
					'post_types'  => [ $collection->get_db_identifier() ],
					'content'     => '<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->

					<!-- wp:group {"layout":{"type":"constrained"}} -->
					<div class="wp-block-group">
						<!-- wp:query-title {"type":"archive","showPrefix":false} /-->
					</div>
					<!-- /wp:group -->

					<!-- wp:group {"layout":{"type":"default"}} -->
					<div class="wp-block-group">

						<!-- wp:tainacan/faceted-search {"collectionId": "' . $collection->get_id() . '", "listType":"collection"} -->
							<div style="font-size:16px;--tainacan-base-font-size:16px;--tainacan-background-color:#ffffff;--tainacan-filter-menu-width-theme:20%;--tainacan-filters-inline-width:272px;--tainacan-input-color:#1d1d1d;--tainacan-input-background-color:#ffffff;--tainacan-input-border-color:#dbdbdb;--tainacan-label-color:#373839;--tainacan-info-color:#505253;--tainacan-heading-color:#000000;--tainacan-skeleton-color:#eeeeee;--tainacan-item-background-color:#ffffff;--tainacan-item-hover-background-color:#f2f2f2;--tainacan-item-heading-hover-background-color:#dbdbdb;--tainacan-primary:#d9eced;--tainacan-secondary:#187181" class="wp-block-tainacan-faceted-search"><main id="tainacan-items-page" data-module="faceted-search" data-collection-id="' . $collection->get_id() . '" data-default-view-mode="masonry" data-is-forced-view-mode="false" data-enabled-view-modes="" data-hide-filters="false" data-hide-hide-filters-button="false" data-hide-search="false" data-hide-advanced-search="false" data-hide-displayed-metadata-button="false" data-hide-pagination-area="false" data-hide-sorting-area="false" data-hide-items-thumbnail="false" data-hide-sort-by-button="false" data-hide-exposers-button="false" data-hide-items-per-page-button="false" data-default-items-per-page="12" data-hide-go-to-page-button="false" data-show-filters-button-inside-search-control="false" data-start-with-filters-hidden="false" data-filters-as-modal="false" data-show-inline-view-mode-options="false" data-show-fullscreen-with-view-modes="false" data-default-order="ASC" data-default-orderby="date" data-default-orderby-meta="" data-default-orderby-type="" data-should-not-hide-filters-on-mobile="false" data-hide-filter-collapses="false" data-display-filters-horizontally="false"></main></div>
						<!-- /wp:tainacan/faceted-search -->

					</div>
					<!-- /wp:group -->

					<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->'
					] );
			}
		}
	}


	/**
	 * Overrides the WordPress blog-like archive to display taxonomy term
	 * items list using the faceted search block.
	 * 
	 * @return string HTML containing the faceted search block for items
	 */
	public function override_taxonomy_term_items_archive_template() {

		if ( is_admin() )
			return;

		// Classic themes
		if ( !wp_is_block_theme() ) {

			add_action('loop_start', function( $query ) {
				
				if ( !$query->is_main_query() || get_query_var('tainacan_repository_archive') == 1 )
					return;

				$term = get_queried_object();
	
				if ( isset($term->taxonomy) && $this->is_taxonomy_a_tainacan_tax($term->taxonomy) ) {
					$tax_id = \Tainacan\Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($term->taxonomy);
					$tax = \Tainacan\Repositories\Taxonomies::get_instance()->fetch($tax_id);
					
					if ( $tax ) {
						$post_types = $tax->get_enabled_post_types();
						
						if (sizeof($post_types))
							return;
					};
					
					ob_start();
				}
			});

			add_action('loop_end', function( $query ) {

				if ( !$query->is_main_query() || get_query_var('tainacan_repository_archive') == 1 )
					return;

				$term = get_queried_object();
	
				if ( isset($term->taxonomy) && $this->is_taxonomy_a_tainacan_tax($term->taxonomy)) {
					$tax_id = \Tainacan\Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($term->taxonomy);
					$tax = \Tainacan\Repositories\Taxonomies::get_instance()->fetch($tax_id);
					
					if ( $tax ) {
						$post_types = $tax->get_enabled_post_types();

						if (sizeof($post_types))
							return;
					}

					$theme_archive_content = ob_get_clean();
					$tainacan_archive_content = \Tainacan\Theme_Helper::get_tainacan_items_list(array());
					/**
					 * Note to code reviewers: This line doesn't need to be escaped.
					 * The output is escaped by the get_tainacan_items_list function,
					 * which uses wp_kses_post() for all dynamic content.
					 */
					/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
					echo $tainacan_archive_content;
				}
			});

		// Block-based FSE themes
		} else {

			add_filter('render_block', function($block_content, $block) {
				
				if ( $block['blockName'] !== 'core/query' || get_query_var('tainacan_repository_archive') == 1  )
					return $block_content;

				$term = get_queried_object();
	
				if ( isset($term->taxonomy) && $this->is_taxonomy_a_tainacan_tax($term->taxonomy)) {
					$tax_id = \Tainacan\Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($term->taxonomy);
					$tax = \Tainacan\Repositories\Taxonomies::get_instance()->fetch($tax_id);
					
					if ( $tax ) {
						$post_types = $tax->get_enabled_post_types();
						
						if (sizeof($post_types))
							return $block_content;
					}

					return \Tainacan\Theme_Helper::get_tainacan_items_list(array());
				}
				
				return $block_content;

			}, 10, 2);
		}
	}


	/**
	 * Overrides the WordPress blog-like archive to display repository-level
	 * items lists using the faceted search block
	 * 
	 * @return string HTML containing the faceted search block for items
	 */
	public function override_repository_items_archive_template() {

		if ( is_admin() )
			return;

		// Classic themes
		if ( !wp_is_block_theme() ) {

			add_action('loop_start', function( $query ) {

				if ( !$query->is_main_query() )
					return;

				if ( get_query_var('tainacan_repository_archive') == 1 )
					ob_start();
				
			});
				
			add_action('loop_end', function( $query ) {
				
				if ( !$query->is_main_query() )
					return;

				if ( get_query_var('tainacan_repository_archive') == 1 ) {
					$theme_archive_content = ob_get_clean();
					$tainacan_archive_content = \Tainacan\Theme_Helper::get_tainacan_items_list(array());
					/**
					 * Note to code reviewers: This line doesn't need to be escaped.
					 * The output is escaped by the get_tainacan_items_list function,
					 * which uses wp_kses_post() for all dynamic content.
					 */
					/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
					echo $tainacan_archive_content;
				}
			});

		// Block-based FSE themes
		} else {

			add_filter('render_block', function($block_content, $block) {

				if ( $block['blockName'] !== 'core/query' )
					return $block_content;

				if ( get_query_var('tainacan_repository_archive') == 1 )
					return \Tainacan\Theme_Helper::get_tainacan_items_list(array());

				return $block_content;

			}, 10, 2);
		}
	}

	/**
	 * Overrides the WordPress post single to display a Tainacan taxonomy
	 * basic structure with a list of terms. Filters the post content to 
	 * create Tainacan default taxonomy single, which works as a "terms archive"
	 * 
	 */
	public function override_taxonomy_single_template() {

		add_filter( 'the_content', function($content) {

			if ( !is_single() )
				return $content;

			$post = get_queried_object();
			
			// Is it a taxonomy-post-type post?
			if ( !($post instanceof \WP_Post) || !$this->is_post_a_tainacan_taxonomy_postype($post) )
				return $content;
			
			$content .= tainacan_get_taxonomies_orderby();
			$content .= tainacan_get_taxonomies_search();
			$taxonomy_terms_list = tainacan_get_single_taxonomy_content($post);
			$content .= $taxonomy_terms_list['content'];
			$content .= tainacan_get_taxonomies_pagination($taxonomy_terms_list['total_terms']);

			return $content;

		} );
	}
	
	/**
	 * Filters the permalink for posts to:
	 *
	 * * Replace Collection single permalink with the link to the post type archive for items of that collection
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
				return get_post_type_archive_link($items_post_type);
				
		}
		
		return $permalink;
		
	}
	
	function tax_archive_pre_get_posts($wp_query) {

		if ( is_single() && get_query_var('post_type') === 'tainacan-taxonomy' && $wp_query->is_main_query() ) {
			if ( !get_query_var('orderby') )
				$wp_query->set('orderby', 'name');
			
			if ( !get_query_var('order') )
				$wp_query->set('order', 'ASC');
				
			return;
		}

		if (!$wp_query->is_tax() || !$wp_query->is_main_query())
			return;

		$term = get_queried_object();
		
		if ($term instanceof \WP_Term && $this->is_term_a_tainacan_term($term)) {
		
			$tax_id = \Tainacan\Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($term->taxonomy);
			$tax = \Tainacan\Repositories\Taxonomies::get_instance()->fetch($tax_id);
			
			if ( $tax && $tax->can_read() ) {
				$post_types = $tax->get_enabled_post_types();

				// TODO: Why post_type = any does not work? 
				// ANSWER because post types are registered with exclude_from_search. Should we change it?
				// TODO adding all post types to the list is something we need to discuss 
				$post_types = array_merge($post_types, \Tainacan\Repositories\Repository::get_collections_db_identifiers());
				$wp_query->set( 'post_type',  $post_types);
				
			} else {
				$wp_query->set_404();
				status_header( 404 );
			}
			
		}
	}
	
	function collection_single_redirect() {
		
		if (is_single() && get_post_type() == \Tainacan\Entities\Collection::$post_type) {
			
			$post = get_post();
			
			$collection = new \Tainacan\Entities\Collection($post);
			
			if ( ! $collection->is_cover_page_enabled() ) {
				
				wp_safe_redirect(get_post_type_archive_link( $collection->get_db_identifier() ));
				exit;
				
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
	
	/**
	 * Allows themes to create a tainacan/single-items.php file which will
	 * be used to represent all items single page.
	 */
	function item_template_hierarchy($templates) {
		
		if ( !is_single() )
			return $templates;

		if ( $this->is_post_type_a_collection( get_post_type() ) ) {
			
			$last_template = array_pop($templates);
			
			array_push($templates, 'tainacan/single-items.php');
			
			array_push($templates, $last_template);
		}

		return $templates;
	}

	/**
	 * Allows themes to create a tainacan/archive-items.php file which will
	 * be used to represent all collection items archive page (the list of items
	 * of a collection).
	 */
	function collection_items_template_hierarchy($templates) {
		
		if ( !is_post_type_archive() )
			return $templates;
		
		if ( $this->is_post_type_a_collection( get_post_type() )  ) {
			
			$last_template = array_pop($templates);

			array_push($templates, 'tainacan/archive-items.php');
			
			array_push($templates, $last_template);
		}
		
		return $templates;
	}
	
	/**
	 * Allows themes to create a tainacan/taxonomy-items.php file which will
	 * be used to represent all taxonomy term items archive page (the list of 
	 * items of a taxonomy term).
	 */
	function taxonomy_term_items_template_hierarchy($templates) {
		
		if ( !is_tax() ) 
			return $templates;
			
		$term = get_queried_object();
		
		if ( isset($term->taxonomy) && $this->is_taxonomy_a_tainacan_tax($term->taxonomy)) {
			$tax_id = \Tainacan\Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($term->taxonomy);
			$tax = \Tainacan\Repositories\Taxonomies::get_instance()->fetch($tax_id);
			
			if ( $tax ) {
				$post_types = $tax->get_enabled_post_types();
				if (sizeof($post_types)) {
					// if taxonomy is enabled for other post types, we disable 
					// custom template ans use default list
					// TODO: This needs discussion
					return $templates;
				}
			}
			
			$last_template = array_pop($templates);
			
			array_push($templates, 'tainacan/archive-taxonomy.php');
			
			array_push($templates, $last_template);
			
		}
		
		return $templates;
		
	}

	/**
	 * Allows themes to create a tainacan/archive-terms.php file which will
	 * be used to represent all taxonomies single (the list or terms of a taxonomy)
	 */
	function taxonomy_terms_template_hierarchy($templates) {
		
		if ( !is_single() )
			return $templates;
		
		$post = get_queried_object();
		
		// Is it a taxonomy-post-type post?
		if ( ($post instanceof \WP_Post) && $this->is_post_a_tainacan_taxonomy_postype($post) ) {
			
			$last_template = array_pop($templates);
			
			array_push($templates, 'tainacan/archive-terms.php');
			
			array_push($templates, $last_template);
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

	public function item_submission_shortcode($args) {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-fonts', $TAINACAN_BASE_URL . '/assets/fonts/tainacanicons.css', [], TAINACAN_VERSION );
		
		$props = ' ';

		// Passes arguments to custom props
		if ($args) {
			foreach ($args as $key => $value) {
				if (is_bool($value))
					$value = $value ? 'true' : 'false';
				// Changes from PHP '_' notation to HTML '-' notation
				$key_attr = str_replace('_', '-', $key);
				if ( $key !== 'class' && $key !== 'style' && $key !== 'id' && strpos($key, 'data-') === false )
					$key_attr = 'data-' . $key_attr;
				
				$props .= sprintf("%s='%s' ", $key_attr, esc_attr($value));
			}
		}

		wp_enqueue_media();

		$allowed_html = [
			'div' => [
				'id' => true,
				'data-module' => true,
				'data-collection-id' => true,
				'data-hide-file-modal-button' => true,
				'data-hide-text-modal-button' => true,
				'data-hide-link-modal-button' => true,
				'data-hide-thumbnail-section' => true,
				'data-hide-attachments-section' => true,
				'data-show-allow-comments-section' => true,
				'data-hide-collapses' => true,
				'data-hide-help-buttons' => true,
				'data-hide-metadata-types' => true,
				'data-help-info-bellow-label' => true,
				'data-document-section-label' => true,
				'data-thumbnail-section-label' => true,
				'data-attachments-section-label' => true,
				'data-metadata-section-label' => true,
				'data-sent-form-heading' => true,
				'data-sent-form-message' => true,
				'data-item-link-button-label' => true,
				'data-show-item-link-button' => true,
				'data-show-terms-agreement-checkbox' => true,
				'data-terms-agreement-message' => true,
				'data-enabled-metadata' => true,
			]
		];

		return wp_kses("<div data-module='item-submission-form' id='tainacan-item-submission-form' $props ></div>", $allowed_html);
	}

	/** 
	 * A basic content for a Tainacan Single page content.
	 * This may be used as an example for theme developers who
	 * will implement their own tainacan/single-items.php template
	 */
	public function get_tainacan_item_single_content() {

		$item = tainacan_get_item();

		$content = '';
		
		// document
		$content .= '<section id="tainacan-default-document-section">';
			$content .= '<h2>' . __( 'Document', 'tainacan' ) . '</h2>';
			$content .= $this->get_tainacan_item_gallery(array(
				'layoutElements' => array( 'main' => true, 'thumbnails' => false ),
				'mediaSources' => 	array( 'document' => true, 'attachments' => false, 'metadata' => false),
			));
		$content .= '</section>';
		
		// metadata sections
		$content .= $item->get_metadata_sections_as_html();

		// attachments
		$content .= '<section id="tainacan-default-attachments-section">';
			$content .= '<h2>' . __( 'Attachments', 'tainacan' ) . '</h2>';
			$content .= $this->get_tainacan_item_gallery(array(
				'layoutElements' => array( 'main' => false, 'thumbnails' => true ),
				'mediaSources' => 	array( 'document' => false, 'attachments' => true, 'metadata' => false),
			));
		$content .= '</section>';
		
		$content = apply_filters('tainacan_single_item_content', $content, $item);

		return $content;
	}

	/**
	 * Returns the div used by Vue to render the Items List with a powerful faceted search
	 *
	 * The items list bellong to a collection, to the whole repository or a taxonomy term, according to where
	 * it is used on the loop, or to given params
	 * 
	 * The following params are all optional for customizing the rendered vue component
	 *
	 * @param array $args {
		 *     Optional. Array of arguments.
		 *     @type string $collection_id								Collection ID for a collection items list
		 *     @type string $term_id									Term ID for a taxonomy term items list
		 *     @type bool 	$hide_filters								Completely hide filter sidebar or modal
		 *     @type bool 	$hide_hide_filters_button					Hides the button resonsible for collpasing filters sidebar on desktop
		 *     @type bool 	$hide_search								Hides the complete search bar, including advanced search link
		 *     @type bool 	$hide_advanced_search						Hides only the advanced search link
		 *     @type bool	$hide_displayed_metadata_button			Hides the "Displayed metadata" dropdown even if the current view modes allows it	
		 *     @type bool	$hide_sorting_area							Completely hides all sorting controls	
		 *     @type bool 	$hide_sort_by_button						Hides the button where user can select the metadata to sort by items (keeps the sort direction)
		 *     @type bool 	$hide_items_thumbnail						Forces the thumbnail to be hiden on every listing. This setting also disables view modes that contain the 'requires-thumbnail' attr. By default is false or inherited from collection setting
		 *     @type bool	$hide_exposers_button						Hides the "View as..." button, a.k.a. Exposers modal
		 *     @type bool 	$hide_items_per_page_button					Hides the button for selecting amount of items loaded per page
		 *     @type bool 	$hide_go_to_page_button						Hides the button for skiping to a specific page
		 *     @type bool	$hide_pagination_area						Completely hides pagination controls
		 *     @type int	$default_items_per_page						Default number of items per page loaded
		 *     @type bool 	$show_filters_button_inside_search_control	Display the "hide filters" button inside of the search control instead of floating
		 *     @type bool 	$start_with_filters_hidden					Loads the filters list hidden from start
		 *     @type bool 	$filters_as_modal							Display the filters as a modal instead of a collapsible region on desktop
		 *     @type bool 	$show_inline_view_mode_options				Display view modes as inline icon buttons instead of the dropdown
		 *     @type bool 	$show_fullscreen_with_view_modes			Lists fullscreen viewmodes alongside with other view modes istead of separatelly
		 *     @type string $default_view_mode							The default view mode
		 *     @type bool	$is_forced_view_mode						Ignores user prefs to always render the choosen default view mode
		 *     @type string[] $enabled_view_modes						The list os enable view modes to display
		 *     @type bool 	$should_not_hide_filters_on_mobile			Disables the default behavior of automatically collapsing the filters inside a modal when in small screen sizes
		 *     @type bool 	$display_filters_horizontally				Display the filters in an horizontal panel above search control instead of a sidebar
		 *     @type bool 	$hide_filter_collapses			Hides the button that collapses all filters inside the filters panel
	 * @return string  The HTML div to be used for rendering the items list vue component
	 */
	public function search_shortcode($args) {
		return $this->get_tainacan_items_list($args, true);
	}
	public function get_tainacan_items_list($args, $force_enqueue = false) {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-fonts', $TAINACAN_BASE_URL . '/assets/fonts/tainacanicons.css', [], TAINACAN_VERSION );
		
		$props = ' ';

		// Loads info related to view modes
		$view_modes = tainacan_get_the_view_modes();
		
		$enabled_view_modes = $view_modes['enabled_view_modes'];
		$default_view_mode = $view_modes['default_view_mode'];

		// If we have a default view mode set, set it
		if ( isset($args['default_view_mode']) ) {
			$default_view_mode = $args['default_view_mode'];
			unset($args['default_view_mode']);
		}
		
		// If we have custom enabled view modes set, set it
		if ( isset($args['enabled_view_modes']) ) {
			$enabled_view_modes = $args['enabled_view_modes'];
			unset($args['enabled_view_modes']);
		}

		// Checks if after updating the previous two, the default view mode is still valid
		if ( !in_array($default_view_mode, $enabled_view_modes) ) {
			$default_view_mode = $enabled_view_modes[0];
		}

		// Loads info related to sorting
		$default_order = $this->get_default_order();
		if ( isset($args['default_order']) ) {
			$default_order = $args['default_order'];
			unset($args['default_order']);
		}

		$default_orderby = $this->get_default_orderby();
		if ( isset($args['default_orderby']) ) {
			$default_orderby = $args['default_orderby'];
			unset($args['default_orderby']);
		}

		// If in a collection page
		$collection = tainacan_get_collection($args);
		if ($collection) {
			$props .= "data-collection-id='" . $collection->get_id() . "' ";
			$default_view_mode = $collection->get_default_view_mode();
			$enabled_view_modes = $collection->get_enabled_view_modes();
			$default_order = $collection->get_default_order();
			$default_orderby = $collection->get_default_orderby();
			
			// Gets hideItemsThumbnail info from collection setting
			$args['hide-items-thumbnail'] = $collection->get_hide_items_thumbnail_on_lists() == 'yes' ? true : false;
		}
		
		// If in a tainacan taxonomy
		$term = tainacan_get_term($args);
		if ($term) {
			$props .= "data-term-id='" . $term->term_id . "' ";
			$props .= "data-taxonomy='" . $term->taxonomy . "' ";
		}
		
		$props .= "data-default-view-mode='" . $default_view_mode . "' ";
		$props .= "data-enabled-view-modes='" . implode(',', $enabled_view_modes) . "' ";
		$props .= "data-default-order='" . $default_order . "' ";
		$props .= "data-default-orderby='" . (is_array($default_orderby) ? json_encode($default_orderby) : $default_orderby) . "' ";

		// Passes arguments to custom props
		foreach ($args as $key => $value) {
			if ($value == true || $value == 'true') {
				$props .= 'data-' . str_replace("_", "-", $key) . "='" . $value . "' ";
			}
		}
	
		$allowed_html = [
			'div' => [
				'id' => true,
				'data-module' => true,
				'data-collection-id' => true,
				'data-term-id' => true,
				'data-taxonomy' => true,
				'data-default-view-mode' => true,
				'data-is-forced-view-mode' => true,
				'data-enabled-view-modes' => true,
				'data-default-order' => true,
				'data-default-orderby' => true,
				'data-hide-filters' => true,
				'data-hide-hide-filters-button' => true,
				'data-hide-search' => true,
				'data-hide-advanced-search' => true,
				'data-hide-displayed-metadata-button' => true,
				'data-hide-sorting-area' => true,
				'data-hide-items-thumbnail' => true,
				'data-hide-sort-by-button' => true,
				'data-hide-exposers-button' => true,
				'data-hide-items-per-page-button' => true,
				'data-hide-go-to-page-button' => true,
				'data-hide-pagination-area' => true,
				'data-default-items-per-page' => true,
				'data-show-filters-button-inside-search-control' => true,
				'data-start-with-filters-hidden' => true,
				'data-filters-as-modal' => true,
				'data-show-inline-view-mode-options' => true,
				'data-show-fullscreen-with-view-modes' => true,
				'data-should-not-hide-filters-on-mobile' => true,
				'data-display-filters-horizontally' => true,
				'data-hide-filter-collapses' => true
			]
		];

		return wp_kses("<div data-module='faceted-search' id='tainacan-items-page' $props ></div>", $allowed_html);
	}
	
	function get_items_list_slug() {
		/* Translators: The Items slug - will be the URL for the repository archive */
		return sanitize_title(_x('items', 'Slug: the string that will be used to build the URL to list all items of the repository', 'tainacan'));
	}
	
	function rewrite_rules( &$wp_rewrite ) {
		$items_base = $this->get_items_list_slug();
		
		$new_rules = array(
			$items_base . "/?$"               => "index.php?tainacan_repository_archive=1",
			$items_base . "/page/([0-9]+)/?$" => 'index.php?tainacan_repository_archive=1&paged=$matches[1]'
		);

		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}

	function rewrite_rules_query_vars( $public_query_vars ) {
		$public_query_vars[] = "tainacan_repository_archive";
		$public_query_vars[] = "termspaged";
		$public_query_vars[] = "termsparent";
		return $public_query_vars;
	}

	function rewrite_rule_template_include( $template ) {
		global $wp_query;
		
		if ( $wp_query->get( 'tainacan_repository_archive' ) == 1 ) {

			$templates = apply_filters('tainacan_repository_archive_template_hierarchy', ['tainacan/archive-repository.php', 'index.php']);
			
			// First, search for PHP templates, which block themes can also use.
			$template = locate_template( $templates, false );

			// Pass the result into the block template locator and let it figure
			// out whether block templates are supported and this template exists.
			$template = locate_block_template( $template, 'archive-repository', $templates );

			return $template;
			
		}
		return $template;
	}
	
	function archive_repository_pre_get_posts($wp_query) {
		if (!$wp_query->is_main_query() || $wp_query->get( 'tainacan_repository_archive' ) != 1)
			return;
		
		$wp_query->set( 'is_archive', true );
		$wp_query->set( 'is_post_type_archive', false );
		$wp_query->set( 'is_home', false );
		$wp_query->is_home = false;
		$wp_query->is_post_type_archive = false;
		$wp_query->is_archive = true;
		$wp_query->set( 'post_type', \Tainacan\Repositories\Repository::get_collections_db_identifiers() );
		
	}

	/**
	 * Register a new View Mode
	 * 
	 * View Modes are used to display items in the faceted search when browsing a collection using 
	 * the current active theme. It can be a php/html template or a web component.
	 * 
	 * Collection managers can choose from registered view modes which will be the default mode and what others modes will be available 
	 * for the visitors to choose from for each collection
	 * 
	 * @param string $slug a unique slug for the view mode
	 * @param array|string $args {
	 * 		Optional. Array of arguments
	 * 
	 * 		@type string 	$label				 	Label, visible to users. Default to $slug
	 * 		@type string	$description		 	Description, visible only to editors in the admin. Default none.
	 * 		@type string	$type 				 	Type. Accepted values are 'template' or 'component'. Default 'template'
	 * 		@type string	$template			 	Full path  to the template file to be used. Required if $type is set to template.
	 * 											 	Default: theme-path/tainacan/view-mode-{$slug}.php
	 * 		@type string	$component			 	Component tag name. The web component js must be included and must accept two props:
	 * 													 	* items - the list of items to be rendered
	 * 													 	* displayed-metadata - list of metadata to be displayed
	 * 													 Default view-mode-{$slug}
	 * 		@type string	$thumbnail			 	Full URL to an thumbnail that represents the view mode. Displayed in admin.
	 * 		@type string	$icon 				 	HTML that outputs an icon that represents the view mode. Displayed in front end.
	 * 		@type bool		$show_pagination	 	Whether to display or not pagination controls. Default true.
	 * 		@type bool		$full_screen		 	Whether the view mode will display full screen or not. Default false.
	 * 		@type bool		$dynamic_metadata	 	Whether to display or not (and use or not) the "displayed metadata" selector. Default false.
	 * 		@type bool		$implements_skeleton 	Whether the view mode has its own strategy for disaplying loading state.
	 * 		@type string	$skeleton_template	 	If the view mode is a template, this is the html of its loading state.
	 * 		@type string	$placeholder_template 	The placeholder template is rendered in Gutenberg blocks to demo the view mode appearance.
	 * 		@type bool		$required_thumbnail		Whether the view mode considers essential that the item thumbnail is available, even if it is a placeholder.
	 * }
	 * 
	 * @return void
	 */
	public function register_view_mode($slug, $args = []) {

		$defaults = array(
			'label' => $slug,
			'description' => '',
			'type' => 'template',
			'template' => get_stylesheet_directory() . '/tainacan/view-mode-' . $slug . '.php',
			'component' => 'view-mode-' . $slug,
			'thumbnail' => '', // get_stylesheet_directory() . '/tainacan/view-mode-' . $slug . '.png',
			'icon' => '', //
			'show_pagination' => true,
			'full_screen' => false,
			'dynamic_metadata' => false,
			'implements_skeleton' => false,
			'skeleton_template' => '',
			'placeholder_template' => $this->default_placeholder_template,
			'requires_thumbnail' => true
		);
		$args = wp_parse_args($args, $defaults);

		$this->registered_view_modes[$slug] = $args;

	}

	/**
	 * Get a list of all registered view modes
	 * 
	 * @return array The list of registered view modes
	 */
	public function get_registered_view_modes() {
		return $this->registered_view_modes;
	}

	/**
	 * Get one specific view mode by its slug
	 * 
	 * @param string $slug The view mode slug
	 * 
	 * @return array|false The view mode definition or false if it is not found
	 */
	public function get_view_mode($slug) {
		return isset($this->registered_view_modes[$slug]) ? $this->registered_view_modes[$slug] : false;
	}
	
	/**
	 * Get the default view mode which can be tweaked in the settings page.
	 * 
	 * @return string The default view mode slug
	 */
	public function get_default_view_mode() {
		return apply_filters( 'tainacan-default-view-mode-for-themes', get_option( 'tainacan_option_default_view_mode' , 'masonry' ) );
	}

	/**
	 * Get the enabled view modes which can be tweaked in the settings page.
	 * 
	 * @return string[] The list of enabled view mode slugs
	 */
	public function get_enabled_view_modes() {
		$registered_view_modes = $this->get_registered_view_modes();

		$registered_view_modes_slugs = [];
		foreach ($registered_view_modes as $key => $value) {
			array_push($registered_view_modes_slugs, $key);
		}
		
		// If the user somehow disabled all view modes, we force keeping at least one, the default.
		$enabled_view_modes = get_option( 'tainacan_option_enabled_view_modes' , $registered_view_modes_slugs );
		if ( count ( $enabled_view_modes ) === 0 )
			$enabled_view_modes[] = $this->get_default_view_mode();
		
		return apply_filters( 'tainacan-enabled-view-modes-for-themes', $enabled_view_modes );
	}

	/**
	 * Get the default order for items lists, which can be tweaked in the settings page.
	 * 
	 * @return string The default order slug
	 */
	public function get_default_order() {
		return apply_filters( 'tainacan-default-order-for-themes', get_option( 'tainacan_option_default_order' , 'DESC' ) );
	}

	/**
	 * Get the default order by for items lists, which can be tweaked in the settings page.
	 * 
	 * @return string The default orderby slug
	 */
	public function get_default_orderby() {
		return apply_filters( 'tainacan-default-orderby-for-themes', get_option( 'tainacan_option_default_orderby' , 'date' ) );
	}

	/**
	 * Get whether query parameters should be included in item links from faceted search.
	 * This can be tweaked in the settings page.
	 * 
	 * @return bool Whether to include query parameters in item links
	 */
	public function get_enable_item_link_query_params() {
		return apply_filters( 'tainacan-enable-item-link-query-params-for-themes', get_option( 'tainacan_option_enable_item_link_query_params', true ) );
	}

	/**
	 * When visiting a collection archive or single, returns the current collection id
	 *
	 * @uses get_post_type() WordPress function, which looks for the global $wp_query variable
	 */
	function tainacan_get_collection_id() {
		if ( is_post_type_archive() || is_single() ) {
			return \Tainacan\Repositories\Collections::get_instance()->get_id_by_db_identifier(get_post_type());
		} elseif ( false !== $this->visiting_collection_cover ) {
			return $this->visiting_collection_cover;
		}
		return false;
	}

	/**
	 * When visiting a collection archive or single, returns the current collection object
	 *
	 * @uses tainacan_get_collection_id()
	 * @return \Tainacan\Entities\Collection | false
	 */
	function tainacan_get_collection($args = []) {
		$collection_id = isset($args['collection_id']) ? $args['collection_id'] : $this->tainacan_get_collection_id();
		if ( $collection_id ) {
			$TainacanCollections = \Tainacan\Repositories\Collections::get_instance();
			$collection = $TainacanCollections->fetch($collection_id);
			if ( $collection instanceof \Tainacan\Entities\Collection ) {
				return $collection;
			}
		}
		return false;
	}


	/**
	 * Gets the Tainacan Item Entity object
	 *
	 * If used inside the Loop of items, will get the Item object for the current post
	 */
	function tainacan_get_item($post_id = 0) {
		$post = get_post( $post_id );

		if (!$post)
			return null;

		if ( !($post instanceof \WP_Post) || !$this->is_post_an_item($post))
			return null;

		$item = new \Tainacan\Entities\Item($post);

		return $item;

	}

	/**
	 * Adds meta tags to the header to improve social sharing 
	 */
	public function add_social_meta() {
		global $wp;
		global $post;

		if ( !is_single() && !is_tax() && !is_post_type_archive() )
			return;

		$excerpt = get_bloginfo( 'description' );
		// Get canonical URL safely using WordPress functions
		// og:url should be the canonical URL without query parameters
		$url_src = esc_url( home_url( $wp->request ?? '' ) );
		$img_info = null; // Initialize as null, will only be set if relevant media exists
		$title = null;
			
		if ( is_single() ) {

			$post = get_queried_object();
			
			// Is it a collection Item 
			if ( !($post instanceof \WP_Post) || !$this->is_post_an_item($post) )
				return;

			$title = get_the_title();

			// Only set img_info if post has a featured image
			if ( has_post_thumbnail( $post->ID ) ) 
				$img_info = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			
			$url_src = get_permalink();
			$content = wp_trim_words( $post->post_content, 28, '[...]' );

			if ( $content ) {
				$excerpt = wp_strip_all_tags( $content );
				$excerpt = str_replace( '', "'", $excerpt );
			} 

		} elseif ( is_post_type_archive() ) {

			$collection_id = tainacan_get_collection_id();

			if ( $collection_id ) {
				$title = tainacan_get_the_collection_name();

				// Only set img_info if collection has a featured image
				if ( has_post_thumbnail( tainacan_get_collection_id() ) ) {
					$img_info = wp_get_attachment_image_src( get_post_thumbnail_id( tainacan_get_collection_id() ), 'full' );
				}
				$url_src = home_url( $wp->request );
				$excerpt = wp_strip_all_tags(tainacan_get_the_collection_description());

			} else {
				// Generic post type archive - use WordPress functions to get title and link
				$post_type = get_post_type();
				if ( $post_type ) {
					$post_type_object = get_post_type_object( $post_type );
					if ( $post_type_object ) {
						$title = $post_type_object->labels->name;
						$url_src = get_post_type_archive_link( $post_type );
					}
				}
			}

		} elseif ( is_tax() ) {

			$term = get_queried_object();

			if ( !($term instanceof \WP_Term) || !$this->is_term_a_tainacan_term($term) )
				return;

			$tainacan_term = tainacan_get_term();
			
			$title = $term->name;
			$excerpt = wp_strip_all_tags($term->description);
			
			$url_src = get_term_link($term->term_id, $term->taxonomy);

			if ( $tainacan_term ) {
				$_term = new \Tainacan\Entities\Term( $tainacan_term );
				$img_id = $_term->get_header_image_id();
				if ($img_id) {
					$img_info = wp_get_attachment_image_src( $img_id, 'full' );
				}
			}
			
		}

		if ( $title ) : ?>
			<meta property="og:type" content="article"/>
			<meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo()); ?>"/>
			<meta property="og:title" content="<?php echo esc_attr($title); ?>"/>
			<meta property="og:description" content="<?php echo esc_html($excerpt); ?>"/>
			<meta property="og:url" content="<?php echo esc_url($url_src); ?>"/>
		<?php endif;


		// Only create image array if we have valid image info
		$image = null;
		if ( ! empty( $img_info[0] ) && $img_info[1] >= 200 && $img_info[2] >= 200 ) {
			$image = array(
				'url' => $img_info[0],
				'width' => $img_info[1],
				'height' => $img_info[2],
			);
		}
		
		if ( $image ) : ?>
			<meta property="og:image" content="<?php echo esc_url($image['url']); ?>"/>
			<meta property="og:image:width" content="<?php echo esc_attr($image['width']); ?>"/>
			<meta property="og:image:height" content="<?php echo esc_attr($image['height']); ?>"/>
		<?php endif; 

		/**
		 * Adds Dublin Core meta tags to the header 
		 */
		if ( is_single() && ($post instanceof \WP_Post) && $this->is_post_an_item($post) ) {
			$metadatum_mapper = \Tainacan\Mappers_Handler::get_instance()->get_mapper('dublin-core');
			
			if ( $metadatum_mapper ) {
				$item = $this->tainacan_get_item($post->ID);
				$item_metadata = $item->get_metadata();
				
				foreach ($item_metadata as $item_metadatum) {
					$metadatum = $item_metadatum->get_metadatum();
					$meta_mappings = $metadatum->get_exposer_mapping();

					if ( array_key_exists('dublin-core', $meta_mappings) && $item_metadatum->has_value() ) {
						$values = $item_metadatum->get_value();
						$values = is_array($values) ? $values : [$values];

						$values = array_map(function($value) use ($meta_mappings) {
							echo '<meta name="' . esc_attr(str_replace('dc:' , 'dc.', $meta_mappings['dublin-core'])) . '" content="' . esc_attr($value) . '" />';
						}, $values);
					}
				}
			}
		} else if ( is_post_type_archive() ){
	
			if ( $this->is_post_type_a_collection(get_post_type()) ) : ?>
				<meta name="dc.title" content="<?php echo esc_attr($title); ?>"/>
				<meta name="dc.description" content="<?php echo esc_html($excerpt); ?>"/>
				<meta name="dc.type" content="Collection">
			<?php endif;
		}

	}


	/**
	 * Get previous and next item according to current search query
	 * 
	 * @param integer $index the position of the item in the current list. This should be added to pagination.
	 * 
	 * @return array containing the next and previous item
	 */
	public function get_adjacent_items() {

		// Array with the link results. If nothing goes well here we just don't have any link :(
		$adjacent_items = [
			'next' => null,
			'previous' => null
		];

		// Adjusts the args to obtain only on one item per request with the correct offset
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This function reads URL query parameters for navigation (read-only operation), similar to WordPress's get_adjacent_post(). Nonce verification is not needed for reading navigation parameters.
		$args = $_GET;
		
		// Defines where are we getting items from
		$entity = [];
		if ( isset($args['source_list'] ) ) {

			if ( $args['source_list'] == 'collection' ) { 
				$collection_id = isset($args['source_entity_id']) && is_numeric($args['source_entity_id']) ? absint( $args['source_entity_id'] ) : tainacan_get_collection_id();
				
				if ( $collection_id )
					$entity = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_id);

			} else if ( $args['source_list'] == 'term' ) {
				$term_id = isset($args['source_entity_id']) && is_numeric($args['source_entity_id']) ? absint( $args['source_entity_id'] ) : false;
				$term = $term_id ? tainacan_get_term(['term_id' => $term_id]) : tainacan_get_term();
				
				if ( $term ) {
					$args['taxquery'] = [
						[
							'taxonomy' => $term->taxonomy,
							'terms' => $term->term_id
						]
					];
				}
			}
		}
		
		unset($args['source_list']);
		unset($args['source_entity_id']);

		if (isset($args['pos'])) {

			// Sets Page based on position
			$args['perpage'] = '1';
			$current_position = absint( $args['pos'] ) + 1;
			unset($args['pos']);
			global $tainacan_rest_items_controller;
			$args = $tainacan_rest_items_controller->process_request_filters($args);

			// Fetch Previous Item
			if($current_position > 1) {
				$args['paged'] = $current_position - 1;
				$items = \Tainacan\Repositories\Items::get_instance()->fetch($args, $entity, 'WP_Query');
				
				if ($items->have_posts()) {
					$items->the_post();
					$item = new Entities\Item($items->post);

					if (!empty($item)  && $item instanceof \Tainacan\Entities\Item) {
						$adjacent_items['previous'] = [
							// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Building navigation URL from original query parameters (read-only operation)
							'url' => get_permalink( $item->get_id() ) . '?' . http_build_query(array_merge($_GET, ['pos'=> $current_position-2])),
							'title' => $item->get_title(),
							'thumbnail' => $item->get_thumbnail()
						];
					}
					\wp_reset_postdata();
				}
			}

			// Fetch Next Item
			$args['paged'] = $current_position + 1;
			$items = \Tainacan\Repositories\Items::get_instance()->fetch($args, $entity, 'WP_Query');

			if ($items->have_posts()) {
				$items->the_post();
				$item = new Entities\Item($items->post);

				if (!empty($item) && $item instanceof \Tainacan\Entities\Item) {
					$adjacent_items['next'] = [
						// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Building navigation URL from original query parameters (read-only operation)
						'url' => get_permalink( $item->get_id() ) . '?' . http_build_query(array_merge($_GET, ['pos'=> $current_position])),
						'title' => $item->get_title(),
						'thumbnail' => $item->get_thumbnail()
					];
				}
				\wp_reset_postdata();
			}
		}

		return $adjacent_items;
	}
	
	/**
	 * Returns the div used by Vue to render the Carousel of Related Items
	 *
	 * @param array $args {
		 *     Optional. Array of arguments.
		 *     @type string  $collection_id					The Collection ID
		 *     @type string  $search_URL					A query string to fetch items from, if load strategy is 'search'
         *     @type array   $selected_items				An array of item IDs to fetch items from, if load strategy is 'selection' and an array of items, if the load strategy is 'parent'
         *     @type string  $load_strategy					Either 'search' or 'selection', to determine how items will be fetch
         *     @type integer $max_items_number				Maximum number of items to be fetch
         *     @type integer $max_tems_per_screen			Maximum columns of items to be displayed on a row of the carousel
         *     @type string  $arrows_position				How the arrows will be positioned regarding the carousel ('around', 'left', 'right')
         *     @type bool    $large_arrows					Should large arrows be displayed?
         *     @type bool    $auto_play						Should the Caroulsel start automatically to slide?
         *     @type integer $auto_play_speed				The time in s to translate to the next slide automatically 
         *     @type bool    $loop_slides					Should slides loop when reached the end of the Carousel?
         *     @type bool    $hide_title					Should the title of the items be displayed?
         *     @type string  $image_size					Item image size. Defaults to 'tainacan-medium'
		 *     @type bool	 $variable_items_width			Should the carousel items be only as large as their natural width?
		 *     @type bool    $show_collection_header		Should it display a small version of the collection header?
         *     @type bool    $show_collection_label			Should it displar a 'Collection' label before the collection name on the collection header?
         *     @type string  $collection_background_color	Color of the collection header background
         *     @type string  $collection_text_color			Color of the collection header text
         *     @type string  $tainacan_api_root				Path of the Tainacan api root (to make the items request)
         *     @type string  $tainacan_base_url				Path of the Tainacan base URL (to make the links to the items)
         *     @type string  $class_name					Extra class to add to the wrapper, besides the default wp-block-tainacan-carousel-items-list
	 * @return string  The HTML div to be used for rendering the items carousel vue component
	 */
	public function get_tainacan_items_carousel($args = []) {
		if (!is_array($args))
			return __('There are missing parameters for Tainacan Items Carousel shortcode', 'tainacan');

		$defaults = array(
			'max_items_number' => 12,
			'max_items_per_screen' => 7,
			'arrows_position' => 'around',
			'large_arrows' => false,
			'auto_play' => false,
			'auto_play_speed' => 3,
			'loop_slides' => false,
			'hide_title' => false,
			'image_size' => ( isset($args['crop_images_to_square']) && !$args['crop_images_to_square'] )
				? 'tainacan-medium-full'
				: 'tainacan-medium',
			'variable_items_width' => false,
			'show_collection_header' => false,
			'show_collection_label' => false,
			'collection_background_color' => '#373839',
			'collection_text_color' => '#ffffff',
			'tainacan_api_root' => '',
			'tainacan_base_url' => '',
			'class_name' => ''
		);
		$args = wp_parse_args($args, $defaults);
		$props = ' ';

		// Always pass the class needed by Vue to mount the component;
		$args['class'] = $args['class_name'] . ' wp-block-tainacan-carousel-items-list';
		unset($args['class_name']);
		
		// Checks if selected_terms was passed as an array of term ids
		if ( isset($args['selected_items']) ) {

			if ( is_array($args['selected_items']) ) {
				$args['selected_items'] = json_encode($args['selected_items']);
			} elseif ( is_string($args['selected_items']) ) {
				// Checks if we already received a valid JSON array
				$decoded = json_decode($args['selected_items'], true);
				if ( json_last_error() !== JSON_ERROR_NONE ) 
					$args['selected_items'] = json_encode(explode(',', $args['selected_items']));
			}
		}

		// Builds parameters to the html div rendered by Vue
		foreach ($args as $key => $value) {
			if (is_bool($value))
				$value = $value ? 'true' : 'false';
			// Changes from PHP '_' notation to HTML '-' notation
			$key_attr = str_replace('_', '-', $key);
			if ( $key !== 'class' && $key !== 'style' && $key !== 'id' && strpos($key, 'data-') === false )
				$key_attr = 'data-' . $key_attr;
			
			$props .= sprintf("%s='%s' ", $key_attr, esc_attr($value));
		}
		
		$allowed_html = [
			'div' => [
				'id' => true,
				'class' => true,
				'style' => true,
				'data-module' => true,
				'data-search-url' => true,
				'data-selected-items' => true,
				'data-arrows-position' => true,
				'data-load-strategy' => true,
				'data-collection-id' => true,
				'data-auto-play' => true,
				'data-auto-play-speed' => true,
				'data-loop-slides' => true,
				'data-hide-title' => true,
				'data-large-arrows' => true,
				'data-arrows-style' => true,
				'data-image-size' => true,
				'data-show-collection-header' => true,
				'data-show-collection-label' => true,
				'data-collection-background-color' => true,
				'data-collection-text-color' => true,
				'data-max-items-number' => true,
				'data-max-items-per-screen' => true,
				'data-space-between-items' => true,
				'data-space-around-carousel' => true,
				'data-tainacan-api-root' => true,
				'data-variable-items-width' => true
			]
		];

		return wp_kses( "<div data-module='carousel-items-list' id='tainacan-items-carousel-shortcode_" . uniqid() . "' $props ></div>", $allowed_html );
	} 

	/**
	 * Returns the div used by Vue to render the Dynamic List of Related Items
	 *
	 * @param array $args {
		 *     Optional. Array of arguments.
		 *     @type string  $collection_id					The Collection ID
		 *     @type string  $search_URL					A query string to fetch items from, if load strategy is 'search'
         *     @type array   $selected_items				An array of item IDs to fetch items from, if load strategy is 'selection' and an array of items, if the load strategy is 'parent'
         *     @type string  $load_strategy					Either 'search' or 'selection', to determine how items will be fetch
         *     @type integer $max_items_number				Maximum number of items to be fetch
         *     @type integer $max_columns_count				Maximum columns cound (used by grid and list layouts)
         *     @type integer $grid_margin					Margin around items in every layout
         *     @type string  $show_name						Show the item title
         *     @type string  $show_image					Show the item thumbnail
         *     @type string  $layout						Either 'grid', 'list' or 'mosaic'
         *     @type string  $image_size					Item image size. Defaults to 'tainacan-medium'
         *     @type bool    $show_collection_header		Should it display a small version of the collection header?
         *     @type bool    $show_collection_label			Should it displar a 'Collection' label before the collection name on the collection header?
         *     @type string  $collection_background_color	Color of the collection header background
         *     @type string  $collection_text_color			Color of the collection header text
         *     @type string  $tainacan_api_root				Path of the Tainacan api root (to make the items request)
         *     @type string  $tainacan_base_url				Path of the Tainacan base URL (to make the links to the items)
         *     @type string  $class_name					Extra class to add to the wrapper, besides the default wp-block-tainacan-carousel-items-list
         *     @type string  $mosaic_height					Height of the panel in the 'mosaic' layout
         *     @type string  $mosaic_density				
         *     @type string  $mosaic_grid_rows				
         *     @type string  $mosaic_grid_columns			
         *     @type string  $mosaic_item_focal_point_x		
         *     @type string  $mosaic_item_focal_point_y		
	 * @return string  The HTML div to be used for rendering the items list vue component
	 */
	public function get_tainacan_dynamic_items_list($args = []) {
		if (!is_array($args))
			return __('There are missing parameters for Tainacan Items Block/shortcode', 'tainacan');

		$defaults = array(
			'max_items_number' => 12,
			'max_columns_count' => 6,
			'grid_margin' => 12,
			'show_name' => true,
			'show_image' => true,
			'layout' => 'grid',
			'image_size' => ( isset($args['crop_images_to_square']) && !$args['crop_images_to_square'] )
				? 'tainacan-medium-full'
				: 'tainacan-medium',
			'show_collection_header' => false,
			'show_collection_label' => false,
			'collection_background_color' => '#373839',
			'collection_text_color' => '#ffffff',
			'tainacan_api_root' => '',
			'tainacan_base_url' => '',
			'class_name' => '',
			'mosaic_height' => 280,
			'mosaic_density' => 5,
			'mosaic_grid_rows' => 3,
			'mosaic_grid_columns' => 4,
			'mosaic_item_focal_point_x' => 0.5,
			'mosaic_item_focal_point_y' => 0.5
		);
		$args = wp_parse_args($args, $defaults);
		$props = ' ';
		
		// Always pass the class needed by Vue to mount the component;
		$args['class'] = $args['class_name'] . ' wp-block-tainacan-dynamic-items-list';
		unset($args['class_name']);

		// Builds parameters to the html div rendered by Vue
		foreach ($args as $key => $value) {
			if (is_bool($value))
				$value = $value ? 'true' : 'false';
			// Changes from PHP '_' notation to HTML '-' notation
			$key_attr = str_replace('_', '-', $key);
			if ( $key !== 'class' && $key !== 'style' && $key !== 'id' && strpos($key, 'data-') === false )
				$key_attr = 'data-' . $key_attr;

			$props .= sprintf("%s='%s' ", $key_attr, esc_attr($value));
		}
		
		$allowed_html = [
			'div' => [
				'data-module' => true,
                'data-search-url' => true,
                'data-selected-items' => true,
                'data-collection-id' => true,
                'data-show-image' => true,
                'data-show-name' => true,
                'data-show-search-bar' => true,
                'data-show-collection-header' => true,
                'data-show-collection-label' => true,
                'data-image-size' => true,
                'data-layout' => true,
                'data-load-strategy' => true,
                'data-mosaic-height' => true,
                'data-mosaic-density' => true,
                'data-mosaic-grid-rows' => true,
                'data-mosaic-grid-columns' => true,
                'data-mosaic-item-focal-point-x' => true,
                'data-mosaic-item-focal-point-y' => true,
                'data-max-columns-count' => true,
                'data-collection-background-color' => true,
                'data-collection-text-color' => true,
                'data-grid-margin' => true,
                'data-max-items-number' => true,
                'data-order' => true,
                'data-order-by' => true,
                'data-order-by-meta-key' => true,
                'data-tainacan-view-mode' => true,
                'data-tainacan-api-root' => true,
                'id' => true,
				'class' => true,
				'style' => true
			]
		];
		
		return wp_kses("<div data-module='dynamic-items-list' id='tainacan-dynamic-items-list-shortcode_" . uniqid(). "' $props ></div>", $allowed_html );
	} 

	/**
	 * Returns a group of related items list
	 * For each metatada, the collection name, the metadata name and a button linking
	 * the items list filtered is presented
	 *
	 * @param array $args {
		 *     Optional. Array of arguments.
		 *     @type string  $itemId                            The Item ID
		 *     @type string  $itemsListLayout                   The type of list to be rendered. Accepts 'grid', 'list', 'mosaic', 'carousel', 'gallery' and 'tainacan-view-mode. 
		 * 	   @type string  $order                             Sorting direction to the related items query. Either 'desc' or 'asc'. 
		 * 	   @type string  $orderby                           Sortby metadata. By now we're accepting only 'title' and 'date'.
		 *     @type string  $className                         Extra class to add to the wrapper, besides the default wp-block-tainacan-carousel-related-items
		 *     @type string  $collectionHeadingClassName        Extra class to add to the collection name wrapper. Defaults to ''
		 * 	   @type string  $collectionHeadingTag              Tag to be used as wrapper of the collection name. Defaults to h2
		 * 	   @type boolean $hideCollectionHeading             Whether to hide the collection name or not. Defaults to false
		 *     @type string  $metadataLabelClassName            Extra class to add to the metadata label wrapper. Defaults to ''
		 * 	   @type string  $metadataLabelTag                  Tag to be used as wrapper of the metadata label. Defaults to p
		 * 	   @type boolean $hideMetadataLabel                 Whether to hide the metadata label or not. Defaults to false
		 *     @type array   $carouselArgs                      Array of arguments to be passed to the get_tainacan_items_carousel function if $itemsListLayout == carousel
		 *     @type array   $dynamicItemsArgs                  Array of arguments to be passed to the get_tainacan_dynamic_items function if $itemsListLayout != carousel && layout != gallery
		 *     @type array   $itemsGalleryArgs                  Array of arguments to be passed to the get_tainacan_items_gallery function if $itemsListLayout == gallery
		 * 	   @type string  $viewMoreLinkStyle                 Appearance of the "View all %s related items" link. Either 'button' or 'link'. Defaults to 'button'
		 * 	   @type string  $viewMoreLinkPosition              Placement of the "View all %s related items" link. Either 'top-right', 'bottom-left' or 'bottom-right'. Defaults to 'bottom-left'
		 *	   @type string  $viewMoreLinkText                  Inner text content of the "View all %s related items" link. Defaults to 'View all %s related items'.
		 * @return string  The HTML div to be used for rendering the related items vue component
	 */
	public function get_tainacan_related_items_list($args = []) {

		// Accepts both camelCase and snake_case
		// This is to make it easier to use in the block editor
		$args = $this->convert_params_to_camel_case($args);

		$defaults = array(
			'className' => '',
			'collectionHeadingClassName' => 'related-items-collection-name',
			'collectionHeadingTag' => 'h2', 
			'hideCollectionHeading' => false,
			'metadataLabelClassName' => 'related-items-metadata-label',
			'metadataLabelTag' => 'p',
			'hideMetadataLabel' => false,
			'carouselArgs' => [],
			'dynamicItemsArgs' => [],
			'itemsGalleryArgs' => [],
			'viewMoreLinkStyle' => 'button',
			'viewMoreLinkPosition' => 'bottom-left',
			/* translators: %s is the amount of existing related items */
			'viewMoreLinkText' => __('View all %s related items', 'tainacan'),
		);
		$args = wp_parse_args($args, $defaults);
		
		// Gets the current Item
		$item = isset($args['itemId']) ? $this->tainacan_get_item($args['itemId']) : $this->tainacan_get_item();
		if (!$item)
			return;
		
		// Then fetches related ones
		$related_items_query_args = [];

		if ( isset($args['orderby']) )
			$related_items_query_args['orderby'] = $args['orderby'];

		if ( isset($args['order']) )
			$related_items_query_args['order'] = $args['order'];

		if ( isset($args['maxItemsNumber']) )
			$related_items_query_args['posts_per_page'] = $args['maxItemsNumber'];
		
		$related_items = $item->get_related_items($related_items_query_args);

		if (!count($related_items))
			return;

		// Always pass the default class. We force passing the wp-block-tainacan-carousel-related-items because themes might have used it to style before the other layouts exist;
		$output = '<div data-module="related-items-list" class="' .  esc_attr($args['className']) . ' wp-block-tainacan-carousel-related-items wp-block-tainacan-related-items' . '">';
		
		foreach($related_items as $collection_id => $related_group) {
			
			if ( isset($related_group['items']) && isset($related_group['total_items']) && $related_group['total_items'] ) {
				// Adds a heading with the collection name
				$collection_heading = '';
				if ( $args['hideCollectionHeading'] !== true && isset($related_group['collection_name']) ) {
					$collection_heading = wp_kses_post('<' . $args['collectionHeadingTag'] . ' class="' . $args['collectionHeadingClassName'] . '">' . $related_group['collection_name'] . '</' . $args['collectionHeadingTag'] . '>');
				}
				
				// Adds a paragraph with the metadata name
				$metadata_label = '';
				if ( $args['hideMetadataLabel'] !== true && isset($related_group['metadata_name']) ) {
					$metadata_label = wp_kses_post('<' . $args['metadataLabelTag'] . ' class="' . $args['metadataLabelClassName'] . '">' . $related_group['metadata_name'] . '</' . $args['metadataLabelTag'] . '>');
				}

				// Sets the carousel, from the items carousel template tag.
				$items_list_div = '';
				if ( isset($related_group['collection_id']) ) {

					$block_args = ( isset($args['itemsListLayout']) && $args['itemsListLayout'] === 'carousel' )
						? $args['carouselArgs'] : ( 
							( isset($args['itemsGalleryArgs']) && isset($args['itemsListLayout']) && $args['itemsListLayout'] === 'gallery' )
							? $args['itemsGalleryArgs']
							: $args['dynamicItemsArgs']
						);

					$no_crop_images_to_square = isset($block_args['crop_images_to_square']) && !$block_args['crop_images_to_square'];
					$image_size = isset($block_args['image_size']) 
						? $block_args['image_size']
						: ($no_crop_images_to_square ? 'tainacan-medium-full' : 'tainacan-medium');

					// No need to pass the complete item to avoid poluting HTML
					$related_group['items'] = array_map(
						function($el) use ($args) {

							// In Tainacan View Modes, we fetch items from api so we only need ID
							if ( $args['itemsListLayout'] === 'tainacan-view-modes' )
								return $el['id'];

							// For other layouts, we simply remove attribute description
							unset($el['description']);
							return $el;

						}, $related_group['items']
					);
					
					if ( isset($args['itemsListLayout']) && $args['itemsListLayout'] === 'carousel' ) {
						$items_list_args = wp_parse_args([
							'collection_id' => $related_group['collection_id'],
							'load_strategy' => 'parent',
							'selected_items' => json_encode($related_group['items']),
							'image_size' => $image_size
						], $block_args);

						$items_list_div = $this->get_tainacan_items_carousel($items_list_args);

					} else if ( isset($args['itemsListLayout']) && $args['itemsListLayout'] === 'gallery' ) {
						$items_list_args = wp_parse_args([
							'collection_id' => $related_group['collection_id'],
							'load_strategy' => 'parent',
							'selected_items' => $related_group['items'],
							'image_size' => $image_size
						], $block_args);

						$items_list_div = $this->get_tainacan_items_gallery($items_list_args);

					} else if ( isset($args['itemsListLayout']) && $args['itemsListLayout'] === 'tainacan-view-modes' ) {
						$items_list_args = wp_parse_args([
							'collection_id' => $related_group['collection_id'],
							'load_strategy' => 'selection', // Tainacan View Modes fetch item from api to get item metadata as well
							'selected_items' => json_encode($related_group['items']),
							'layout' => $args['itemsListLayout'],
							'displayed_metadata' => json_encode(isset( $block_args['displayed_metadata'] ) ? $block_args['displayed_metadata'] : []),
							'tainacan_view_mode' => isset($block_args['tainacan_view_mode']) ? $block_args['tainacan_view_mode'] : ( isset($args['tainacanViewMode']) ? $args['tainacanViewMode'] : 'masonry' )
						], $block_args);
						
						$items_list_div = $this->get_tainacan_dynamic_items_list($items_list_args);

					} else {
						$items_list_args = wp_parse_args([
							'collection_id' => $related_group['collection_id'],
							'load_strategy' => 'parent',
							'selected_items' => json_encode($related_group['items']),
							'layout' => $args['itemsListLayout'],
							'image_size' => $image_size
						], $block_args);

						$items_list_div = $this->get_tainacan_dynamic_items_list($items_list_args);

					} 
				}
				
				$view_more_link = $args['viewMoreLinkStyle'] === 'button' ?
					'<div class="wp-block-buttons related-items-view-more-link">
						<div class="wp-block-button">
							<a class="wp-block-button__link" href="' . esc_url( get_permalink( $related_group['collection_id'] ) ) . '?metaquery[0][key]=' . esc_attr($related_group['metadata_id']) . '&metaquery[0][value][0]=' . esc_attr($item->get_ID()) . '&metaquery[0][compare]=IN">
								' . esc_html(sprintf( $args['viewMoreLinkText'], $related_group['total_items'] )) . '
							</a>
						</div>
					</div>' : '<a class="related-items-view-more-link" href="' . esc_url( get_permalink( $related_group['collection_id'] ) ) . '?metaquery[0][key]=' . esc_attr($related_group['metadata_id']) . '&metaquery[0][value][0]=' . esc_attr($item->get_ID()) . '&metaquery[0][compare]=IN">
						' . esc_html(sprintf( $args['viewMoreLinkText'], $related_group['total_items'] )) . '
					</a>';

				$output .= '<div class="wp-block-group has-view-more-link--' . esc_attr($args['viewMoreLinkPosition']) . '" data-related-collection-id="' . esc_attr($related_group['collection_id']) . '" data-related-metadata-id="' . esc_attr($related_group['metadata_id']) . '">
					<div class="wp-block-group__inner-container">' .
						/**
						 * Note to code reviewers: These variables don't need additional escaping:
						 * - $collection_heading: escaped with wp_kses_post() at line 1781
						 * - $view_more_link: all dynamic values escaped with esc_url(), esc_attr(), esc_html()
						 * - $metadata_label: escaped with wp_kses_post() at line 1787
						 * - $items_list_div: escaped by get_tainacan_items_carousel(), get_tainacan_items_gallery(), or get_tainacan_dynamic_items_list()
						 */
						$collection_heading .
						( $args['viewMoreLinkPosition'] === 'top-right' && $related_group['total_items'] > 1 ? $view_more_link : '' ) .
						$metadata_label .
						$items_list_div .
						( ( $args['viewMoreLinkPosition'] === 'bottom-left' || $args['viewMoreLinkPosition'] === 'bottom-right' ) && $related_group['total_items'] > 1 ? $view_more_link : '' ) .
						'<div style="height:30px" aria-hidden="true" class="wp-block-spacer">
						</div>
					</div>
				</div>';
			}
		}
		
		$output .= '</div>';

		return $output;
	}

	/**
	 * Returns a group of related items list carousel
	 * This is just a pre-set version of the get_tainacan_related_items function kept for
	 * compatibility with previous versions.
	 */	
	public function get_tainacan_related_items_carousel($args = []) {
		$args = wp_parse_args($args, [ 'itemsListLayout' => 'carousel' ]);
		return $this->get_tainacan_related_items_list($args);
	}

	/**
	 * Returns an item gallery, containing document,
	 * attachments and other information in a slider, carousel and lightbox
	 *
	 * @param array $args {
		*     Optional. Array of arguments.
		*      @type string  $item_id						  The Item ID
		* 	   @type string	 $blockId 						  A unique identifier for the gallery, will be generated automatically if not provided,
		*	   @type bool    $isBlock						  An identifier if we're comming from a block renderer, to avois using functions not available outside of the gutenberg scope;
		* 	   @type array 	 $layoutElements 				  Array of elements present in the gallery. Possible values are 'main' and 'carousel'
		* 	   @type array 	 $mediaSources 					  Array of sources for the gallery. Possible values are 'document' and 'attachments'
		* 	   @type bool 	 $hideFileNameMain 				  Hides the Main slider file name
		* 	   @type bool 	 $hideFileCaptionMain 			  Hides the Main slider file caption
		* 	   @type bool 	 $hideFileDescriptionMain		  Hides the Main slider file description
		* 	   @type bool 	 $hideFileNameThumbnails 		  Hides the Thumbnails carousel file name
		* 	   @type bool 	 $hideFileCaptionThumbnails 	  Hides the Thumbnails carousel file caption
		* 	   @type bool 	 $hideFileDescriptionThumbnails   Hides the Thumbnails carousel file description
		* 	   @type bool 	 $hideFileNameLightbox 			  Hides the Lightbox file name
		* 	   @type bool 	 $hideFileCaptionLightbox 		  Hides the Lightbox file caption
		* 	   @type bool 	 $hideFileDescriptionLightbox	  Hides the Lightbox file description
		* 	   @type bool 	 $openLightboxOnClick 			  Enables the behaviour of opening a lightbox with zoom when clicking on the media item
		*	   @type bool	 $showDownloadButtonMain		  Displays a download button below the Main slider
		*	   @type bool	 $lightboxHasLightBackground      Show a light background instead of dark in the lightbox 
		*	   @type bool    $showArrowsAsSVG				  Decides if the swiper carousel arrows will be an SVG icon or font icon
		*	   @type string  $thumbnailsSize				  Media size for the thumbnail images. Defaults to 'tainacan-medium'
		*	   @type bool  	 $thumbsHaveFixedHeight			  If thumbs should have a fixed height and auto widht. Defaults to false.
		* }		
		* @return string  The HTML div to be used for rendering the item galery component
	 */
	public function get_tainacan_item_gallery($args = []) {

		// Accepts both camelCase and snake_case
		// This is to make it easier to use in the block editor
		$args = $this->convert_params_to_camel_case($args);

		$defaults = array(
			'blockId' => 						uniqid(),
			'layoutElements' => 				array( 'main' => true, 'thumbnails' => true ),
			'isBlock' =>						false,
			'mediaSources' => 					array( 'document' => true, 'attachments' => true, 'metadata' => false),
			'hideFileNameMain' => 				true, 
			'hideFileCaptionMain' => 			false,
			'hideFileDescriptionMain' =>		true,
			'hideFileNameThumbnails' => 		true, 
			'hideFileCaptionThumbnails' => 		true,
			'hideFileDescriptionThumbnails' =>  true,
			'hideFileNameLightbox' =>	 		false, 
			'hideFileCaptionLightbox' => 		false,
			'hideFileDescriptionLightbox' =>	false,
			'openLightboxOnClick' => 			true,
			'showDownloadButtonMain' =>			true,
			'lightboxHasLightBackground' => 	false,
			'showArrowsAsSVG' =>				true,
			'thumbnailsSize' =>					'tainacan-medium',
			'thumbsHaveFixedHeight'	=>			false	
		);
		$args = wp_parse_args($args, $defaults);
		
		// Gets the current Item. This way, the function can be used in the loop without needing to pass it
		$item = isset($args['itemId']) ? $this->tainacan_get_item($args['itemId']) : $this->tainacan_get_item();
		if ( !$item )
			return;

		$item_id = $item->get_id();

		// Gets options from block attributes
		$block_id = $args['blockId'];
		$layout_elements = $args['layoutElements'];
		$media_sources = $args['mediaSources'];
		$hide_file_name_main = $args['hideFileNameMain'];
		$hide_file_caption_main = $args['hideFileCaptionMain'];
		$hide_file_description_main = $args['hideFileDescriptionMain'];
		$hide_file_name_thumbnails = $args['hideFileNameThumbnails'];
		$hide_file_caption_thumbnails = $args['hideFileCaptionThumbnails'];
		$hide_file_description_thumbnails = $args['hideFileDescriptionThumbnails'];
		$hide_file_name_lightbox = $args['hideFileNameLightbox'];
		$hide_file_caption_lightbox = $args['hideFileCaptionLightbox'];
		$hide_file_description_lightbox = $args['hideFileDescriptionLightbox'];
		$open_lightbox_on_click = $args['openLightboxOnClick'];
		$show_download_button_main = $args['showDownloadButtonMain'];
		$lightbox_has_light_background = $args['lightboxHasLightBackground'];
		$show_arrows_as_svg = $args['showArrowsAsSVG'];
		$thumbnails_size = $args['thumbnailsSize'];
		$thumbs_have_fixed_height = $args['thumbsHaveFixedHeight'];

		// Prefils arrays with proper values to avoid messsy IFs
		$layout_elements = array(
			'main' => (isset($layout_elements['main']) && ($layout_elements['main'] === true || $layout_elements['main'] == 'true')) ? true : false,
			'thumbnails' => (isset($layout_elements['thumbnails']) && ($layout_elements['thumbnails'] === true || $layout_elements['thumbnails'] == 'true')) ? true : false
		);
		$media_sources = array(
			'document' => (isset($media_sources['document']) && ($media_sources['document'] === true || $media_sources['document'] == 'true')) ? true : false,
			'attachments' => (isset($media_sources['attachments']) && ($media_sources['attachments'] === true || $media_sources['attachments'] == 'true')) ? true : false,
			'metadata' => (isset($media_sources['metadata']) && ($media_sources['metadata'] === true || $media_sources['metadata'] == 'true')) ? true : false
		);

		$media_items_main = array();
		$media_items_thumbnails = array();

		if ( $media_sources['attachments'] )
			$attachments = tainacan_get_the_attachments(null, $item_id);

		if ( $layout_elements['main'] ) {

			$class_slide_metadata = '';
			if ($hide_file_name_main)
				$class_slide_metadata .= ' hide-name';
			if ($hide_file_description_main)
				$class_slide_metadata .= ' hide-description';
			if ($hide_file_caption_main)
				$class_slide_metadata .= ' hide-caption';

			// Checks if there is at least one image alongside the media sources
			// to decide if loading the lighbox is worthy on the main slider
			if ($open_lightbox_on_click) {
				$media_includes_images = false;

				if ( $media_sources['document'] && !empty(tainacan_get_the_document($item_id)) ) {
					$document_type = tainacan_get_the_document_type($item_id);
					
					if ($document_type === 'attachment')  {
						// Uses this moment to also see if we have an image
						$attachment = get_post(tainacan_get_the_document_raw($item_id));
						$media_includes_images = wp_attachment_is('image', $attachment->ID);
					} else if ($document_type === 'url') {
						$document_options = $item->get_document_options();
						$media_includes_images = isset($document_options['is_image']) && $document_options['is_image'];
					}
				}
				
				if ( $media_sources['attachments'] ) {
					foreach ( $attachments as $attachment ) {
						$is_attachment_an_image = wp_attachment_is('image', $attachment->ID);

						if ($is_attachment_an_image)
							$media_includes_images = true; // Do not asign directly as we want to check if at least one is true
					}
				}

				if (!$media_includes_images)
					$open_lightbox_on_click = false;
			}

			if ( $media_sources['document'] && !empty(tainacan_get_the_document($item_id)) ) {
				$document_type = tainacan_get_the_document_type($item_id);
				
				// Document description is a bit more tricky
				if ($document_type === 'attachment')  {
					$attachment = get_post(tainacan_get_the_document_raw($item_id));
					$document_description = ($attachment instanceof \WP_Post) ? $attachment->post_content : '';
				}

				$media_items_main[] =
					tainacan_get_the_media_component_slide(array(
						'after_slide_metadata' => (( $show_download_button_main && tainacan_the_item_document_download_link($item_id) != '' ) ?
														sprintf('<span class="tainacan-item-file-download">%s</span>', tainacan_the_item_document_download_link($item_id))
												: ''),
						'media_content' => tainacan_get_the_document($item_id),
						'media_content_full' => $open_lightbox_on_click ?
												(
													$document_type === 'attachment' ?
													tainacan_get_the_document($item_id, 'full') :
													sprintf('<div class="attachment-without-image">%s</div>', tainacan_get_the_document($item_id, 'full'))
												) : '',
						'media_title' => $document_type === 'attachment' ? get_the_title(tainacan_get_the_document_raw($item_id)) : '',
						'media_description' => $document_type === 'attachment' ? $document_description : '',
						'media_caption' => $document_type === 'attachment' ? wp_get_attachment_caption(tainacan_get_the_document_raw($item_id)) : '',
						'media_type' => tainacan_get_the_document_type($item_id),
						'class_slide_metadata' => $class_slide_metadata
					));
			}
			
			if ( $media_sources['attachments'] ) {
				foreach ( $attachments as $attachment ) {
					$is_attachment_an_image = wp_attachment_is('image', $attachment->ID);

					$media_items_main[] =
						tainacan_get_the_media_component_slide(array(
							'after_slide_metadata' => (( $show_download_button_main && tainacan_the_item_attachment_download_link($attachment->ID) != '' ) ?
															sprintf('<span class="tainacan-item-file-download">%s</span>', tainacan_the_item_attachment_download_link($attachment->ID))
													: ''),
							'media_content' => tainacan_get_attachment_as_html($attachment->ID, $item_id),
							'media_content_full' => $open_lightbox_on_click ?
													( 
														$is_attachment_an_image ?
														wp_get_attachment_image( $attachment->ID, 'full', false) :
														sprintf('<div class="attachment-without-image tainacan-embed-container"><iframe id="tainacan-attachment-iframe--%s" src="%s"></iframe></div>', $block_id, tainacan_get_attachment_html_url($attachment->ID))
													) : '',
							'media_title' => $attachment->post_title,
							'media_description' => $attachment->post_content,
							'media_caption' => $attachment->post_excerpt,
							'media_type' => $attachment->post_mime_type,
							'class_slide_metadata' => $class_slide_metadata
						));
				}
			}
		}
		
		// Make sure we have more than one media item otherwise 
		// we don't need to show thumbnails if the main carousel exists
		if ( $layout_elements['main'] && count($media_items_main) <= 1 )
			$layout_elements['thumbnails'] = false;

		if ( $layout_elements['thumbnails'] ) {

			$class_slide_metadata = '';
			if ($hide_file_name_thumbnails)
				$class_slide_metadata .= ' hide-name';
			if ($hide_file_description_thumbnails)
				$class_slide_metadata .= ' hide-description';
			if ($hide_file_caption_thumbnails)
				$class_slide_metadata .= ' hide-caption';

			if ( $media_sources['document'] && !empty(tainacan_get_the_document($item_id)) ) {
				$is_document_type_attachment = tainacan_get_the_document_type($item_id) === 'attachment';
				
				$media_items_thumbnails[] =
					tainacan_get_the_media_component_slide(array(
						'media_content' => get_the_post_thumbnail($item_id, $thumbnails_size),
						'media_content_full' => $open_lightbox_on_click ? ($is_document_type_attachment ? tainacan_get_the_document($item_id, 'full') : sprintf('<div class="attachment-without-image">%s</div>', tainacan_get_the_document($item_id, 'full')) ) : '',
						'media_title' => $is_document_type_attachment ? get_the_title(tainacan_get_the_document_raw($item_id)) : '',
						'media_description' => $is_document_type_attachment ? get_the_content(null, false, tainacan_get_the_document_raw($item_id)) : '',
						'media_caption' => $is_document_type_attachment ? wp_get_attachment_caption(tainacan_get_the_document_raw($item_id)) : '',
						'media_type' => tainacan_get_the_document_type($item_id),
						'class_slide_metadata' => $class_slide_metadata
					));			
			}

			if ( $media_sources['attachments'] ) {
				foreach ( $attachments as $attachment ) {
					$attachment_thumbnail = get_the_post_thumbnail($attachment->ID, $thumbnails_size);
					$media_items_thumbnails[] = 
						tainacan_get_the_media_component_slide(array(
							'media_content' => $attachment_thumbnail ? $attachment_thumbnail : wp_get_attachment_image( $attachment->ID, $thumbnails_size, false ),
							'media_content_full' => ( $open_lightbox_on_click && !$layout_elements['main'] ) ? ( wp_attachment_is('image', $attachment->ID) ? wp_get_attachment_image( $attachment->ID, 'full', false) : sprintf('<div class="attachment-without-image tainacan-embed-container"><iframe id="tainacan-attachment-iframe--%s" src="%s"></iframe></div>', $block_id, tainacan_get_attachment_html_url($attachment->ID)) ) : '',
							'media_title' => $attachment->post_title,
							'media_description' => $attachment->post_content,
							'media_caption' => $attachment->post_excerpt,
							'media_type' => $attachment->post_mime_type,
							'class_slide_metadata' => $class_slide_metadata
						));
				}
			}
		}
		
		$block_custom_css = '';
		
		// Text color. First we check for custom preset colors, then actual values
		$block_custom_css .= isset($args['textColor']) ? sprintf('--tainacan-media-metadata-color: var(--wp--preset--color--%s);', $args['textColor']) : '';
		$block_custom_css .= isset($args['style']['color']['text']) ? sprintf('--tainacan-media-metadata-color: %s;', $args['style']['color']['text']) : '';
		
		// Background color. First we check for custom preset colors, then actual values
		$block_custom_css .= isset($args['backgroundColor']) ? sprintf('--tainacan-media-background: var(--wp--preset--color--%s);', $args['backgroundColor']) : '';
		$block_custom_css .= isset($args['style']['color']['background']) ? sprintf('--tainacan-media-background: %s;', $args['style']['color']['background']) : '';

		// Link color, if enabled. Firts we check for custom preset colors, then actual values.
		$block_custom_css .= isset($args['linkColor']) ? sprintf('--swiper-theme-color: var(--wp--preset--color--%s);', $args['linkColor']) : '';
		if ( isset($args['style']['elements']['link']['color']['text']) ) {
			$link_color = $args['style']['elements']['link']['color']['text'];
			if ( strpos( $link_color, 'var:' ) !== false ) {
				$link_color = str_replace('|', '--', $link_color);
				$link_color = str_replace('var:', 'var(--wp--', $link_color) . ')';
			}
			$block_custom_css .= sprintf('--swiper-theme-color: %s;', $link_color);
		}
			
		// Other values are obtained directly from the attributes
		$block_custom_css .= (isset($args['arrowsSize']) && is_numeric($args['arrowsSize'])) ? sprintf('--swiper-navigation-size: %spx;', $args['arrowsSize']) : '';
		$block_custom_css .= (isset($args['mainSliderHeight']) && is_numeric($args['mainSliderHeight'])) ? sprintf('--tainacan-media-main-carousel-height: %svh;', $args['mainSliderHeight']) : '';
		$block_custom_css .= (isset($args['mainSliderWidth']) && is_numeric($args['mainSliderWidth'])) ? sprintf('--tainacan-media-main-carousel-width: %s%%;', $args['mainSliderWidth']) : '';
		$block_custom_css .= (isset($args['thumbnailsCarouselWidth']) && is_numeric($args['thumbnailsCarouselWidth'])) ? sprintf('--tainacan-media-thumbs-carousel-width: %s%%;', $args['thumbnailsCarouselWidth']) : '';
		$block_custom_css .= (isset($args['thumbnailsCarouselItemSize']) && is_numeric($args['thumbnailsCarouselItemSize'])) ? sprintf('--tainacan-media-thumbs-carousel-item-size: %spx;', $args['thumbnailsCarouselItemSize']) : '';

		// Checks if we're inside a block, otherwise we have to build this manually.
		if ( isset($args['isBlock']) && $args['isBlock'] ) {
			$wrapper_attributes = get_block_wrapper_attributes(
				array(
					'style' => $block_custom_css,
					'class' => 'tainacan-media-component'
				)
			);
		}  else {
			$wrapper_attributes = '';
			if ( !empty($block_custom_css) )
				$wrapper_attributes .= 'style="' . $block_custom_css . '" ';
			
			$wrapper_attributes .=	'class="tainacan-media-component"';
		}

		/**
		 * Filters the Swiper options for the main slider
		 * 
		 * @param Object item The current item object
		 * @param Object args Arguments passed to the get_tainacan_item_gallery function
		 */
		$extra_swiper_main_options = [];
		$extra_swiper_main_options = apply_filters( 'tainacan-swiper-main-options', $extra_swiper_main_options, $item, $args );

		$swiper_main_options = array_merge(
			$extra_swiper_main_options,
			$layout_elements['main'] ? array(
				'navigation' => array(
					'nextEl' => sprintf('.swiper-navigation-next_tainacan-item-gallery-block_id-%s-main', $block_id),
					'prevEl' => sprintf('.swiper-navigation-prev_tainacan-item-gallery-block_id-%s-main', $block_id),
					'preloadImages' => false,
					'lazy' => true
				)
			) : []
		);

		/**
		 * Filters the Swiper options for the thumbnails slider
		 * 
		 * @param Object item The current item object
		 * @param Object args Arguments passed to the get_tainacan_item_gallery function
		 */
		$extra_swiper_thumbs_options = [];
		$extra_swiper_thumbs_options = apply_filters( 'tainacan-swiper-thumbs-options', $extra_swiper_thumbs_options, $item, $args );

		$swiper_thumbs_options = array_merge(
			$extra_swiper_thumbs_options,
			( $layout_elements['thumbnails'] && !$layout_elements['main'] ) ? array(
				'navigation' => array(
					'nextEl' => sprintf('.swiper-navigation-next_tainacan-item-gallery-block_id-%s-thumbs', $block_id),
					'prevEl' => sprintf('.swiper-navigation-prev_tainacan-item-gallery-block_id-%s-thumbs', $block_id),
					'preloadImages' => false,
					'lazy' => true
				)
			) : []
		);
		
		/**
		 * Filters the Media Component HTML
		 */
		$gallery_html = tainacan_get_the_media_component(
			'tainacan-item-gallery-block_id-' . $block_id,
			$layout_elements['thumbnails'] ? $media_items_thumbnails : null,
			$layout_elements['main'] ? $media_items_main : null,
			array(
				'wrapper_attributes' => $wrapper_attributes,
				'class_main_div' => '',
				'class_thumbs_div' => '',
				'class_thumbs_li' => $thumbs_have_fixed_height ? 'has-fixed-height' : '',
				'swiper_main_options' => $swiper_main_options,
				'swiper_thumbs_options' => $swiper_thumbs_options,
				'swiper_arrows_as_svg' => $show_arrows_as_svg,
				'disable_lightbox' => !$open_lightbox_on_click,
				'hide_media_name' => $hide_file_name_lightbox,
				'hide_media_caption' => $hide_file_caption_lightbox,
				'hide_media_description' => $hide_file_description_lightbox,
				'lightbox_has_light_background' => $lightbox_has_light_background
			)
		);
		
		// Apply wp_kses to ensure safety while preserving necessary HTML elements and attributes
		// The content is already escaped in tainacan_get_the_media_component(), but we apply
		// wp_kses here to satisfy plugin check requirements and ensure filter output is safe
		// The 'tainacan_content' context now includes data-module and SVG support
		// Temporarily add safe_style_css filter to allow CSS properties needed for .media-full-content
		add_filter('safe_style_css', 'tainacan_get_default_allowed_styles', 10, 1);
		$gallery_html = wp_kses($gallery_html, wp_kses_allowed_html('tainacan_content'));
		remove_filter('safe_style_css', 'tainacan_get_default_allowed_styles', 10);
		
		return apply_filters(
			'get_tainacan_item_gallery',
			$gallery_html,
			$args,
		);
	}

	/**
	 * Returns an items gallery, displaying a list of items in a slider, carousel and lightbox
	 *
	 * @param array $args {
		*     Optional. Array of arguments.
		*	  @type string   $collectionId					  The Collection ID
		*	  @type string   $searchURL						  A query string to fetch items from, if load strategy is 'search'
		*	  @type array    $searchParams					  An array of query params to fetch items from, if load strategy is 'search'
        *	  @type array    $selectedItems					  An array of item IDs to fetch items from, if load strategy is 'selection' and an array of items, if the load strategy is 'parent'
        *	  @type string   $loadStrategy					  Either 'search' or 'selection', to determine how items will be fetch
        *	  @type integer  $maxItemsNumber				  Maximum number of items to be fetch
        *	  @type string	 $blockId 						  A unique identifier for the gallery, will be generated automatically if not provided,
		*	  @type bool     $isBlock						  An identifier if we're comming from a block renderer, to avois using functions not available outside of the gutenberg scope;
		* 	  @type array 	 $layoutElements 				  Array of elements present in the gallery. Possible values are 'main' and 'carousel'
		* 	  @type bool 	 $hideItemTitleMain 			  Hides the Main slider item title
		* 	  @type bool 	 $hideItemLinkMain 			  	  Hides the Main slider item link
		* 	  @type bool 	 $hideItemDescriptionMain		  Hides the Main slider item description
		* 	  @type bool 	 $hideItemTitleThumbnails 		  Hides the Thumbnails carousel item title
		* 	  @type bool 	 $hideItemTitleLightbox 		  Hides the Lightbox item title
		* 	  @type bool 	 $hideItemLinkLightbox 		  	  Hides the Lightbox item link
		* 	  @type bool 	 $hideItemDescriptionLightbox	  Hides the Lightbox file description
		* 	  @type bool 	 $openLightboxOnClick 			  Enables the behaviour of opening a lightbox with zoom when clicking on the media item
		*	  @type bool	 $lightboxHasLightBackground      Show a light background instead of dark in the lightbox 
		*	  @type bool     $showArrowsAsSVG				  Decides if the swiper carousel arrows will be an SVG icon or font icon
		*	  @type string   $thumbnailsSize				  Media size for the thumbnail images. Defaults to 'tainacan-medium'
		*	  @type bool  	 $thumbsHaveFixedHeight			  If thumbs should have a fixed height and auto widht. Defaults to false.
		* }		
		* @return string  The HTML div to be used for rendering the items galery component
	 */
	public function get_tainacan_items_gallery($args = []) {
		
		// Accepts both camelCase and snake_case
		// This is to make it easier to use in the block editor
		$args = $this->convert_params_to_camel_case($args);

		$defaults = array(
			'blockId' => 						uniqid(),
			'layoutElements' => 				array( 'main' => true, 'thumbnails' => true ),
			'collectionId' => 				    '',
			'searchURL' => 					    '',	
			'searchParams' => 					array(),
			'selectedItems' => 				    array(),
			'loadStrategy' => 				    'selection',	
			'maxItemsNumber' => 				12,
			'isBlock' =>						false,
			'hideItemTitleMain' => 				true, 
			'hideItemLinkMain' => 				false,
			'hideItemDescriptionMain' =>		true,
			'hideItemTitleThumbnails' => 		true, 
			'hideItemTitleLightbox' =>	 		false, 
			'hideItemLinkLightbox' => 			false,
			'hideItemDescriptionLightbox' =>	false,
			'openLightboxOnClick' => 			true,
			'lightboxHasLightBackground' => 	false,
			'showArrowsAsSVG' =>				true,
			'thumbnailsSize' =>					'tainacan-medium',
			'thumbsHaveFixedHeight'	=>			false	
		);
		$args = wp_parse_args($args, $defaults);

		// Gets options from block attributes
		$block_id = $args['blockId'];
		$selected_items = $args['selectedItems'];
		$search_URL = $args['searchURL']; // Not used here since the request is made via searchParams
		$search_params = $args['searchParams'];
		$collection_id = $args['collectionId'];
		$load_strategy = $args['loadStrategy'];
		$max_items_number = $args['maxItemsNumber'];
		$layout_elements = $args['layoutElements'];
		$hide_item_title_main = $args['hideItemTitleMain'];
		$hide_item_link_main = $args['hideItemLinkMain'];
		$hide_item_description_main = $args['hideItemDescriptionMain'];
		$hide_item_title_thumbnails = $args['hideItemTitleThumbnails'];
		$hide_item_title_lightbox = $args['hideItemTitleLightbox'];
		$hide_item_link_lightbox = $args['hideItemLinkLightbox'];
		$hide_item_description_lightbox = $args['hideItemDescriptionLightbox'];
		$open_lightbox_on_click = $args['openLightboxOnClick'];
		$lightbox_has_light_background = $args['lightboxHasLightBackground'];
		$show_arrows_as_svg = $args['showArrowsAsSVG'];
		$thumbnails_size = $args['thumbnailsSize'];
		$thumbs_have_fixed_height = $args['thumbsHaveFixedHeight'];

		// Prefils arrays with proper values to avoid messsy IFs
		$layout_elements = array(
			'main' => (isset($layout_elements['main']) && ($layout_elements['main'] === true || $layout_elements['main'] == 'true')) ? true : false,
			'thumbnails' => (isset($layout_elements['thumbnails']) && ($layout_elements['thumbnails'] === true || $layout_elements['thumbnails'] == 'true')) ? true : false
		);

		$media_items_main = array();
		$media_items_thumbnails = array();

		$items_ids = [];
		$entity = [];

		if ( $collection_id )
			$entity = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_id);

		if ( $load_strategy === 'selection' && !empty($selected_items) ) {
			$items_ids = $selected_items;
		} else if ( $load_strategy === 'search' && !empty($search_params) ) {
			$search_params['posts_per_page'] = $max_items_number;
			$items = \Tainacan\Repositories\Items::get_instance()->fetch($search_params, $entity, 'OBJECT');

			if ( $items )
				$items_ids = array_map(function($item) { return $item->get_id(); }, $items);

		} else if ( $load_strategy === 'parent' && !empty($selected_items) ) {
			$items_ids = array_map(function($item) { return $item['id']; }, $selected_items);
		}

		// Prepares the main slider
		if ( $layout_elements['main'] ) {

			$class_slide_metadata = '';
			if ($hide_item_title_main)
				$class_slide_metadata .= ' hide-name';
			if ($hide_item_description_main)
				$class_slide_metadata .= ' hide-description';
			if ($hide_item_link_main)
				$class_slide_metadata .= ' hide-caption';

			// Checks if there is at least one image alongside the media sources
			// to decide if loading the lighbox is worthy on the main slider
			if ($open_lightbox_on_click) {
				$media_includes_images = false;

				foreach( $items_ids as $item_id ) {

					if ( !empty(tainacan_get_the_document($item_id)) ) {
						$document_type = tainacan_get_the_document_type($item_id);
						
						if ($document_type === 'attachment')  {
							// Uses this moment to also see if we have an image
							$attachment = get_post(tainacan_get_the_document_raw($item_id));
							$media_includes_images = wp_attachment_is('image', $attachment->ID);
						} else if ($document_type === 'url') {
							$item = tainacan_get_item($item_id);
							$document_options = $item->get_document_options();
							$media_includes_images = isset($document_options['is_image']) && $document_options['is_image'];
						}
						if ( $media_includes_images )
							break;
					}
				}
				
				if ( !$media_includes_images )
					$open_lightbox_on_click = false;
			}

			// Adds Item's documents as main slider content
			foreach( $items_ids as $item_id ) {
				$item = tainacan_get_item($item_id);

				$media_items_main[] = 
					tainacan_get_the_media_component_slide(array(
						'media_content' => tainacan_get_the_document($item_id),
						'media_content_full' => $open_lightbox_on_click ?
												(
													$document_type === 'attachment' ?
													tainacan_get_the_document($item_id, 'full') :
													sprintf('<div class="attachment-without-image">%s</div>', tainacan_get_the_document($item_id, 'full'))
												) : '',
						'media_title' => $item ? $item->get_title() : '',
						'media_description' =>  $item ? $item->get_description() : '',
						'media_caption' => '<a href="' . esc_url(get_permalink($item_id)) . '" target="_blank" rel="noopener noreferrer">' . __( 'Visit the page', 'tainacan' ) . '</a>',
						'media_type' => tainacan_get_the_document_type($item_id),
						'class_slide_metadata' => $class_slide_metadata
					));
			
			}
		}
		
		// Make sure we have more than one media item otherwise 
		// we don't need to show thumbnails if the main carousel exists
		if ( $layout_elements['main'] && count($media_items_main) <= 1 )
			$layout_elements['thumbnails'] = false;

		// Prepares the thumbnails carousel
		if ( $layout_elements['thumbnails'] ) {

			$class_slide_metadata = ' hide-caption hide-description';
			if ($hide_item_title_thumbnails)
				$class_slide_metadata .= ' hide-name';

			// Adds the items thumbnails as carousel
			foreach( $items_ids as $item_id ) {
				$item = tainacan_get_item($item_id);
				$is_document_type_attachment = tainacan_get_the_document_type($item_id) === 'attachment';
				
				$media_items_thumbnails[] =
					tainacan_get_the_media_component_slide(array(
						'media_content' => get_the_post_thumbnail($item_id, $thumbnails_size),
						'media_content_full' => $open_lightbox_on_click ? ($is_document_type_attachment ? tainacan_get_the_document($item_id, 'full') : sprintf('<div class="attachment-without-image">%s</div>', tainacan_get_the_document($item_id, 'full')) ) : '',
						'media_title' => $item ? $item->get_title() : '',
						'media_description' => $item ? $item->get_description() : '',
						'media_caption' => '<a href="' . esc_url(get_permalink($item_id)) . '" target="_blank" rel="noopener noreferrer">' . __( 'Visit the page', 'tainacan' ) . '</a>',
						'media_type' => tainacan_get_the_document_type($item_id),
						'class_slide_metadata' => $class_slide_metadata
					));			
			}
		}
		
		$block_custom_css = '';
		
		// Text color. First we check for custom preset colors, then actual values
		$block_custom_css .= isset($args['textColor']) ? sprintf('--tainacan-media-metadata-color: var(--wp--preset--color--%s);', $args['textColor']) : '';
		$block_custom_css .= isset($args['style']['color']['text']) ? sprintf('--tainacan-media-metadata-color: %s;', $args['style']['color']['text']) : '';
		
		// Background color. First we check for custom preset colors, then actual values
		$block_custom_css .= isset($args['backgroundColor']) ? sprintf('--tainacan-media-background: var(--wp--preset--color--%s);', $args['backgroundColor']) : '';
		$block_custom_css .= isset($args['style']['color']['background']) ? sprintf('--tainacan-media-background: %s;', $args['style']['color']['background']) : '';

		// Link color, if enabled. Firts we check for custom preset colors, then actual values.
		$block_custom_css .= isset($args['linkColor']) ? sprintf('--swiper-theme-color: var(--wp--preset--color--%s);', $args['linkColor']) : '';
		if ( isset($args['style']['elements']['link']['color']['text']) ) {
			$link_color = $args['style']['elements']['link']['color']['text'];
			if ( strpos( $link_color, 'var:' ) !== false ) {
				$link_color = str_replace('|', '--', $link_color);
				$link_color = str_replace('var:', 'var(--wp--', $link_color) . ')';
			}
			$block_custom_css .= sprintf('--swiper-theme-color: %s;', $link_color);
		}
			
		// Other values are obtained directly from the attributes
		$block_custom_css .= (isset($args['arrowsSize']) && is_numeric($args['arrowsSize'])) ? sprintf('--swiper-navigation-size: %spx;', $args['arrowsSize']) : '';
		$block_custom_css .= (isset($args['mainSliderHeight']) && is_numeric($args['mainSliderHeight'])) ? sprintf('--tainacan-media-main-carousel-height: %svh;', $args['mainSliderHeight']) : '';
		$block_custom_css .= (isset($args['mainSliderWidth']) && is_numeric($args['mainSliderWidth'])) ? sprintf('--tainacan-media-main-carousel-width: %s%%;', $args['mainSliderWidth']) : '';
		$block_custom_css .= (isset($args['thumbnailsCarouselWidth']) && is_numeric($args['thumbnailsCarouselWidth'])) ? sprintf('--tainacan-media-thumbs-carousel-width: %s%%;', $args['thumbnailsCarouselWidth']) : '';
		$block_custom_css .= (isset($args['thumbnailsCarouselItemSize']) && is_numeric($args['thumbnailsCarouselItemSize'])) ? sprintf('--tainacan-media-thumbs-carousel-item-size: %spx;', $args['thumbnailsCarouselItemSize']) : '';

		// Checks if we're inside a block, otherwise we have to build this manually.
		if ( isset($args['isBlock']) && $args['isBlock'] ) {
			$wrapper_attributes = get_block_wrapper_attributes(
				array(
					'style' => $block_custom_css,
					'class' => 'tainacan-media-component'
				)
			);
		}  else {
			$wrapper_attributes = '';
			if ( !empty($block_custom_css) )
				$wrapper_attributes .= 'style="' . $block_custom_css . '" ';
			
			$wrapper_attributes .=	'class="tainacan-media-component"';
		}

		/**
		 * Filters the Swiper options for the main slider
		 * 
		 * @param Object items_ids The array of item ids
		 * @param Object args Arguments passed to the get_tainacan_items_gallery function
		 */
		$extra_swiper_main_options = [];
		$extra_swiper_main_options = apply_filters( 'tainacan-swiper-main-options', $extra_swiper_main_options, $items_ids, $args );

		$swiper_main_options = array_merge(
			$extra_swiper_main_options,
			$layout_elements['main'] ? array(
				'navigation' => array(
					'nextEl' => sprintf('.swiper-navigation-next_tainacan-items-gallery-block_id-%s-main', $block_id),
					'prevEl' => sprintf('.swiper-navigation-prev_tainacan-items-gallery-block_id-%s-main', $block_id),
					'preloadImages' => false,
					'lazy' => true
				)
			) : []
		);

		/**
		 * Filters the Swiper options for the thumbnails slider
		 * 
		 * @param Object items_ids The array of item ids
		 * @param Object args Arguments passed to the get_tainacan_items_gallery function
		 */
		$extra_swiper_thumbs_options = [];
		$extra_swiper_thumbs_options = apply_filters( 'tainacan-swiper-thumbs-options', $extra_swiper_thumbs_options, $items_ids, $args );

		$swiper_thumbs_options = array_merge(
			$extra_swiper_thumbs_options,
			( $layout_elements['thumbnails'] && !$layout_elements['main'] ) ? array(
				'navigation' => array(
					'nextEl' => sprintf('.swiper-navigation-next_tainacan-items-gallery-block_id-%s-thumbs', $block_id),
					'prevEl' => sprintf('.swiper-navigation-prev_tainacan-items-gallery-block_id-%s-thumbs', $block_id),
					'preloadImages' => false,
					'lazy' => true
				)
			) : []
		);
		
		/**
		 * Filters the Media Component HTML
		 */
		$gallery_html = tainacan_get_the_media_component(
			'tainacan-items-gallery-block_id-' . $block_id,
			$layout_elements['thumbnails'] ? $media_items_thumbnails : null,
			$layout_elements['main'] ? $media_items_main : null,
			array(
				'wrapper_attributes' => $wrapper_attributes,
				'class_main_div' => '',
				'class_thumbs_div' => '',
				'class_thumbs_li' => $thumbs_have_fixed_height ? 'has-fixed-height' : '',
				'swiper_main_options' => $swiper_main_options,
				'swiper_thumbs_options' => $swiper_thumbs_options,
				'swiper_arrows_as_svg' => $show_arrows_as_svg,
				'disable_lightbox' => !$open_lightbox_on_click,
				'hide_media_name' => $hide_item_title_lightbox,
				'hide_media_caption' => $hide_item_link_lightbox,
				'hide_media_description' => $hide_item_description_lightbox,
				'lightbox_has_light_background' => $lightbox_has_light_background
			)
		);
		
		// Apply wp_kses to ensure safety while preserving necessary HTML elements and attributes
		// The content is already escaped in tainacan_get_the_media_component(), but we apply
		// wp_kses here to satisfy plugin check requirements and ensure filter output is safe
		// The 'tainacan_content' context now includes data-module and SVG support
		// Temporarily add safe_style_css filter to allow CSS properties needed for .media-full-content
		add_filter('safe_style_css', 'tainacan_get_default_allowed_styles', 10, 1);
		$gallery_html = wp_kses($gallery_html, wp_kses_allowed_html('tainacan_content'));
		remove_filter('safe_style_css', 'tainacan_get_default_allowed_styles', 10);
		
		return apply_filters(
			'get_tainacan_items_gallery',
			$gallery_html,
			$args,
		);
	}


	/**
	 * Returns the div used by Vue to render the Carousel of Terms
	 *
	 * @param array $args {
		 *     Optional. Array of arguments.
		 *     @type string  $taxonomy_id					The Taxonomy ID
		 *     @type array   $selected_terms				An array of term IDs to fetch terms from, if load strategy is 'selection' and an array of terms, if the load strategy is 'parent'
         *     @type integer $max_terms_number				Maximum number of terms to be fetch
         *     @type integer $max_terms_per_screen			Maximum columns of terms to be displayed on a row of the carousel
         *     @type bool    $show_term_thumbnail			Show term thumbnail instead of a grid with items terms
         *     @type string  $arrows_position				How the arrows will be positioned regarding the carousel ('around', 'left', 'right')
         *     @type bool    $large_arrows					Should large arrows be displayed?
         *     @type bool    $auto_play						Should the Caroulsel start automatically to slide?
         *     @type integer $auto_play_speed				The time in s to translate to the next slide automatically 
         *     @type integer $space_between_terms			The space in px between each term in the carousel 
         *     @type integer $space_around_carousel			The space in px between around the carousel 
         *     @type bool    $loop_slides					Should slides loop when reached the end of the Carousel?
         *     @type bool    $hide_name						Should the name of the terms be displayed?
         *     @type string  $image_size					term image size. Defaults to 'tainacan-medium'
		 *     @type bool	 $variable_terms_width			Should the carousel terms be only as large as their natural width?
		 *     @type string  $tainacan_api_root				Path of the Tainacan api root (to make the terms request)
         *     @type string  $tainacan_base_url				Path of the Tainacan base URL (to make the links to the terms)
         *     @type string  $class_name					Extra class to add to the wrapper, besides the default wp-block-tainacan-carousel-terms-list
	 * @return string  The HTML div to be used for rendering the terms carousel vue component
	 */
	public function get_tainacan_terms_carousel($args = []) {
		if ( !is_array($args) )
			return __('There are missing parameters for Tainacan Terms Carousel shortcode', 'tainacan');

		$defaults = array(
			'max_terms_number' => 12,
			'max_terms_per_screen' => 6,
			'arrows_position' => 'around',
			'large_arrows' => false,
			'auto_play' => false,
			'auto_play_speed' => 3,
			'space_between_terms' => 32,
			'space_around_carousel' => 50,
			'loop_slides' => false,
			'hide_name' => false,
			'image_size' => ( isset($args['crop_images_to_square']) && !$args['crop_images_to_square'] )
				? 'tainacan-medium-full'
				: 'tainacan-medium',
			'variable_terms_width' => false,
			'tainacan_api_root' => '',
			'tainacan_base_url' => '',
			'class_name' => ''
		);
		$args = wp_parse_args($args, $defaults);
		$props = ' ';
		
		// Always pass the class needed by Vue to mount the component;
		$args['class'] = $args['class_name'] . ' wp-block-tainacan-carousel-terms-list';
		unset($args['class_name']);

		// Checks if selected_terms was passed as an array of term ids
		if ( isset($args['selected_terms']) ) {

			if ( is_array($args['selected_terms']) ) {
				$args['selected_terms'] = json_encode($args['selected_terms']);
			} elseif ( is_string($args['selected_terms']) ) {
				// Checks if we already received a valid JSON array
				$decoded = json_decode($args['selected_terms'], true);
				if ( json_last_error() !== JSON_ERROR_NONE ) 
					$args['selected_terms'] = json_encode(explode(',', $args['selected_terms']));
			}
		}
		
		// Builds parameters to the html div rendered by Vue
		foreach ($args as $key => $value) {
			if (is_bool($value))
				$value = $value ? 'true' : 'false';
			// Changes from PHP '_' notation to HTML '-' notation
			$key_attr = str_replace('_', '-', $key);
			if ( $key !== 'class' && $key !== 'style' && $key !== 'id' && strpos($key, 'data-') === false )
				$key_attr = 'data-' . $key_attr;
			
			$props .= sprintf("%s='%s' ", $key_attr, esc_attr($value));
		}

		$allowed_html = [
			'div' => [
				'id' => true,
				'class' => true,
				'style' => true,
				'data-module' => true,
				'data-selected-terms' => true,
				'data-arrows-position' => true,
				'data-taxonomy-id' => true,
				'data-auto-play' => true,
				'data-auto-play-speed' => true,
				'data-loop-slides' => true,
				'data-hide-name' => true,
				'data-large-arrows' => true,
				'data-arrows-style' => true,
				'data-image-size' => true,
				'data-variable-terms-width' => true,
				'data-max-terms-number' => true,
				'data-max-terms-per-screen' => true,
				'data-space-between-terms' => true,
				'data-space-around-carousel' => true,
				'data-tainacan-api-root' => true,
				'data-show-term-thumbnail' => true,
				'data-space-between-terms' => true,
				'data-space-around-carousel' => true
			]
		];

		return wp_kses( "<div data-module='carousel-terms-list' id='tainacan-terms-carousel-shortcode_" . uniqid() . "' $props ></div>", $allowed_html );
	} 

	/**
	 * To be used inside Gutenberg editor side preview of template blocks
	 *
	 * Return the metadata with placeholder item metadata values as a HTML string to be used as output.
	 *
	 * Each metadata is a label with the metadatum name and the placeholder value.
	 *
	 * If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
	 * it returns all metadata
	 *
	 * @param array|string $args {
		 *     Optional. Array or string of arguments.
		 *
		 * 	   @type mixed		 $metadata					Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata
		 *     @type array		 $metadata__in				Array of metadata IDs or Slugs to be retrieved. Default none
		 *     @type array		 $metadata__not_in			Array of metadata IDs (slugs not accepted) to excluded. Default none
		 *     @type bool		 $exclude_title				Exclude the Core Title Metadata from result. Default false
		 *     @type bool		 $exclude_description		Exclude the Core Description Metadata from result. Default false
		 *     @type bool		 $exclude_core				Exclude Core Metadata (title and description) from result. Default false
		 *     @type bool        $hide_empty                Whether to hide or not metadata the item has no value to
		 *                                                  Default: true
		 *     @type string      $empty_value_message       Message string to display if $hide_empty is false and there is not metadata value. Default ''
		 *     @type string      $before                    String to be added before each metadata block
		 *                                                  Default '<div class="metadata-type-$type">' where $type is the metadata type slug
		 *     @type string      $after		                String to be added after each metadata block
		 *                                                  Default '</div>'
		 *     @type string      $before_title              String to be added before each metadata title
		 *                                                  Default '<h3>'
		 *     @type string      $after_title               String to be added after each metadata title
		 *                                                  Default '</h3>'
		 *     @type string      $before_value              String to be added before each metadata value
		 *                                                  Default '<p>'
		 *     @type string      $after_value               String to be added after each metadata value
		 *                                                  Default '</p>'
		 * }
	 * 
	 * @param int|string $item_id       (Optional) The item ID to retrive the metadatum as a HTML string to be used as output. Default is the global $post
	 * 
	 * 
	 * @return string        The HTML output
	 */
	function get_tainacan_item_metadata_template($args = array(), $collection_id = 0) {

		if ( !$collection_id )
			return '';
		
		if ( isset($args['metadata']) )
			$args['p'] = $args['metadata'];

		if ( isset($args['metadata__in']) )
			$args['post__in'] = $args['metadata__in'];
		
		if ( isset($args['metadata__not_in']) )
			$args['post__not_in'] = $args['metadata__not_in'];
		
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_id);
		$metadata = \Tainacan\Repositories\Metadata::get_instance()->fetch_by_collection($collection, $args);

		if (!is_array($metadata) || count($metadata) <= 0 && !($metadata[0] instanceof \Tainacan\Entities\Metadatum))
			return '';

		$defaults = array(
			'hide_empty' 			=> true,
			'empty_value_message' 	=> '',
			'display_slug_as_class' => false,
			'before' 				=> '<div class="metadata-type-$type" $id>',
			'after' 				=> '</div>',
			'before_title' 			=> '<h3>',
			'after_title' 			=> '</h3>',
			'before_value' 			=> '<p>',
			'after_value' 			=> '</p>'
		);
		$args = wp_parse_args($args, $defaults);

		$return = '';
		foreach($metadata as $metadatum) {
			$item_metadatum = new \Tainacan\Entities\Item_Metadata_Entity(null, $metadatum);
			
			// Gets the metadata type object to use it if we need the slug
			$metadata_type_object = $metadatum->get_metadata_type_object();

			// Get metadatum wrapper tag. 
			$before = str_replace('$type', $metadata_type_object->get_slug(), $args['before']);

			// Adds class with slug and adds metadatum id
			if ($args['display_slug_as_class']) {
				if ( !strpos($before, 'class="') ) {
					$before = str_replace('>', ' class="metadata-slug-'. $metadatum->get_slug() . '">', $before);
				} else
					$before = str_replace('class="', 'class="metadata-slug-'. $metadatum->get_slug() . ' ', $before);
			}
			$before = str_replace('$id', ' id="metadata-id-' . $metadatum->get_id() . '"', $before);

			// Let theme authors tweak the wrapper opener
			$metadata_type = $metadatum->get_metadata_type();
			$metadatum_id = $metadatum->get_id();
			$before = apply_filters( 'tainacan-get-item-metadatum-as-html-before', $before, $item_metadatum );
			$before = apply_filters( "tainacan-get-item-metadatum-as-html-before--type-$metadata_type", $before, $item_metadatum );
			$before = apply_filters( "tainacan-get-item-metadatum-as-html-before--id-$metadatum_id", $before, $item_metadatum );

			// Renders the metadatum opener
			$return .= $before;

			// Renders the metadatum name
			$metadatum_title_before = $args['before_title'];
			$metadatum_title_before = apply_filters( 'tainacan-get-item-metadatum-as-html-before-title', $metadatum_title_before, $item_metadatum );
			$metadatum_title_after = $args['after_title'];
			$metadatum_title_after = apply_filters( 'tainacan-get-item-metadatum-as-html-after-title', $metadatum_title_after, $item_metadatum );
			$return .= $metadatum_title_before . $metadatum->get_name() . $metadatum_title_after;
			
			// Renders the metadatum value
			$metadatum_value_before = $args['before_value'];
			$metadatum_value_before = apply_filters( 'tainacan-get-item-metadatum-as-html-before-value', $metadatum_value_before, $item_metadatum );
			$metadatum_value_after = $args['after_value'];
			$metadatum_value_after = apply_filters( 'tainacan-get-item-metadatum-as-html-after-value', $metadatum_value_after, $item_metadatum );
			$return .= $metadatum_value_before . __('The item metadata value goes here', 'tainacan' ) . $metadatum_value_after;

			$after = $args['after'];

			// Let theme authors tweak the wrapper closer
			$metadatum_id = $metadatum->get_id();
			$metadata_type = $metadatum->get_metadata_type();
			$after = apply_filters( "tainacan-get-item-metadatum-as-html-after--id-$metadatum_id", $after, $item_metadatum );
			$after = apply_filters( "tainacan-get-item-metadatum-as-html-after--type-$metadata_type", $after, $item_metadatum );
			$after = apply_filters( 'tainacan-get-item-metadatum-as-html-after', $after, $item_metadatum );
			
			// Closes the wrapper
			$return .= $after;
		}

		// Returns the html content created by the function
		return $return;

	}

	function get_tainacan_item_metadata_sections_template($args = array(), $collection_id = 0) {
		
		if ( !$collection_id )
			return '';

		$Tainacan_Metadata_Sections = \Tainacan\Repositories\Metadata_Sections::get_instance();

		$return = '';

		$defaults = array(
			'metadata_section' 				=> null,
			'metadata_sections__in' 		=> null,
			'metadata_sections__not_in' 	=> null,
			'hide_name' 					=> false,
			'hide_description' 				=> true,
			'hide_empty' 					=> true,
			'empty_metadata_list_message' 	=> '',
			'before' 						=> '<section class="metadata-section-slug-$slug" id="$id">',
			'after' 						=> '</section>',
			'before_name' 					=> '<h2 id="metadata-section-$slug">',
			'after_name' 					=> '</h2>',
			'before_metadata_list' 			=> '<div class="metadata-section__metadata-list" aria-labelledby="metadata-section-$slug">',
			'after_metadata_list' 			=> '</div>',
			'metadata_list_args' 			=> []
		);
		$args = wp_parse_args($args, $defaults);
		$metadata_sections = array();
		
		// If a single metadata section is passed, we use it instead of fetching more
		if ( !is_null($args['metadata_section']) ) {
			
			$metadata_section = $args['metadata_section'];
			$metadata_section_object = null;

			// A metadata section object was passed
			if ( $metadata_section instanceof \Tainacan\Entities\Metadata_Section ) {
				$metadata_section_object = $metadata_section;

			// A metadata section ID was passed
			} elseif ( is_numeric($metadata_section) ) {
				$metadata_section_object = $Tainacan_Metadata_Sections->fetch($metadata_section);

			// The default metadata section was passed
			} elseif ( $metadata_section == \Tainacan\Entities\Metadata_Section::$default_section_slug ) {
				$metadata_section_object = $Tainacan_Metadata_Sections->get_default_section($collection_id);

			// A metadata section slug was passed
			} elseif ( is_string($metadata_section) ) {
				$query = $Tainacan_Metadata_Sections->fetch(['slug' => $metadata_section], 'OBJECT');
				if ( is_array($query) && sizeof($query) == 1 && isset($metadata_section[0]) ) {
					$metadata_section_object = $metadata_section[0];
				}
			}

			// Some checks to see if things are really ok
			if ( !($metadata_section_object instanceof \Tainacan\Entities\Metadata_Section) ) {
				return $return;
			} else {
				// Makes sure the current Metadata Section is desired
				if ( is_array($args['metadata_sections__not_in'])
					&& (
						in_array($metadata_section_object->get_slug(), $args['metadata_sections__not_in']) ||
						in_array($metadata_section_object->get_id(), $args['metadata_sections__not_in'])
					)
				) {
					return $return;
				}
			}

			// Add it to the array which will be looped below
			$metadata_sections[] = $metadata_section_object;

		// If not single metadata section is passed, we query them
		} else {

			// Build query args ready to be passed to the API fetch
			$query_args = [];
			$post__in = [];
			$post__not_in = [];
			$post__name_in = [];
			if (is_array($args['metadata_sections__in'])) {
				$post__in[] = -1; // If metadata_sections__in is an empty array, this forces empty result
				foreach ($args['metadata_sections__in'] as $metadata_section) {
					if (is_numeric($metadata_section) || $metadata_section === \Tainacan\Entities\Metadata_Section::$default_section_slug) {
						$post__in[] = $metadata_section;
					} elseif (is_string($metadata_section)) {
						$post__name_in[] = $metadata_section;
					}
				}
			}
			if (is_array($args['metadata_sections__not_in'])) {
				foreach ($args['metadata_sections__not_in'] as $metadata_section) {
					if (is_integer($metadata_section) || $metadata_section === \Tainacan\Entities\Metadata_Section::$default_section_slug) {
						$post__not_in[] = $metadata_section;
					}
				}
			}

			if (sizeof($post__in) > 0) {
				$query_args['post__in'] = $post__in;
			}
			if (sizeof($post__not_in) > 0) {
				$query_args['post__not_in'] = $post__not_in;
			}
			if (sizeof($post__name_in) > 0) {
				$query_args['post__name_in'] = $post__name_in;
			}
			
			// Get metadata section objects from the metadata sections repository
			$TainacanCollections = \Tainacan\Repositories\Collections::get_instance();
			$collection = $TainacanCollections->fetch($collection_id);
			if ( $collection instanceof \Tainacan\Entities\Collection ) {
				$metadata_sections = $Tainacan_Metadata_Sections->fetch_by_collection($collection, $query_args);
			}
		}

		// Loop metadata sections to print their "values" as html
		$section_index = 0;
		foreach ( $metadata_sections as $metadata_section_object ) {
			$return .= $this->get_metadata_section_template($metadata_section_object, $args, $section_index, $collection_id);
			$section_index++;
		}

		// Returns the html content created by the function
		return $return;
	}

	public function get_metadata_section_template($metadata_section, $args = array(), $section_index = null, $collection_id = 0) {
		$return = '';

		$defaults = array(
			'hide_name' 					=> false,
			'hide_description' 				=> true,
			'hide_empty' 					=> true,
			'empty_metadata_list_message' 	=> '',
			'before' 						=> '<section class="metadata-section-slug-$slug" id="$id">',
			'after' 						=> '</section>',
			'before_name' 					=> '<h2 id="metadata-section-$slug">',
			'after_name' 					=> '</h2>',
			'before_metadata_list' 			=> '<div class="metadata-section__metadata-list" aria-labelledby="metadata-section-$slug">',
			'after_metadata_list' 			=> '</div>',
			'metadata_list_args' 			=> []
		);
		$args = wp_parse_args($args, $defaults);

		// Gets the metadata section inner metadata list
		$metadata_section_metadata_list = $metadata_section->get_metadata_object_list();
		$has_metadata_list = (is_array($metadata_section_metadata_list) && count($metadata_section_metadata_list) > 0 );
		
		if ( $has_metadata_list || !$args['hide_empty'] ) {

			// Slug and ID are used in numerous situations
			$section_slug = $metadata_section->get_slug();
			$section_id = $metadata_section->get_id();

			// Get section wrapper tag
			$before = $args['before'];
			$before = str_replace('$id', $section_id, $before);
			$before = str_replace('$slug', $section_slug, $before);

			// Let theme authors tweak the wrapper opener
			$before = apply_filters( 'tainacan-get-metadata-section-as-html-before', $before, $metadata_section );
			$before = apply_filters( 'tainacan-get-metadata-section-as-html-before--id-' . $section_id, $before, $metadata_section );
			if ( is_numeric($section_index) && $section_index >= 0 ) {
				$before = apply_filters( 'tainacan-get-metadata-section-as-html-before--index-' . $section_index, $before, $metadata_section );	
			}

			// Renders the wrapper opener
			$return .= $before;

			// Adds section label (name)
			if ( !$args['hide_name'] ) {

				// Get section name wrapper
				$before_name = $args['before_name'];
				$before_name = str_replace('$id', $section_id, $before_name);
				$before_name = str_replace('$slug', $section_slug, $before_name);

				// Let theme authors tweak the name wrapper
				$before_name = apply_filters( 'tainacan-get-metadata-section-as-html-before-name', $before_name, $metadata_section );
				$before_name = apply_filters( 'tainacan-get-metadata-section-as-html-before-name--id-' . $section_id, $before_name, $metadata_section );
				if ( is_numeric($section_index) && $section_index >= 0 ) {
					$before_name = apply_filters( 'tainacan-get-metadata-section-as-html-before-name--index-' . $section_index, $before_name, $metadata_section );	
				}

				// Get section name closer
				$after_name = $args['after_name'];

				// Let theme authors tweak the name wrapper
				$after_name = apply_filters( 'tainacan-get-metadata-section-as-html-after-name', $after_name, $metadata_section );
				$after_name = apply_filters( 'tainacan-get-metadata-section-as-html-after-name--id-' . $section_id, $after_name, $metadata_section );
				if ( is_numeric($section_index) && $section_index >= 0 ) {
					$after_name = apply_filters( 'tainacan-get-metadata-section-as-html-after-name--index-' . $section_index, $after_name, $metadata_section );	
				}

				// Renders the metadata section name
				$return .= $before_name . $metadata_section->get_name() . $after_name;
			}

			// Adds section description
			if ( !$args['hide_description'] ) {
				$return .= $args['before_description'] . $metadata_section->get_description() . $args['after_description'];
			}

			// Gets the section metadata list wrapper
			$before_metadata_list = $args['before_metadata_list'];
			$before_metadata_list = str_replace('$id', $section_id, $before_metadata_list);
			$before_metadata_list = str_replace('$slug', $section_slug, $before_metadata_list);

			// Let theme authors tweak the metadata list wrapper
			$before_description = isset($args['before_description']) ? $args['before_description'] : '';
			$before_description = apply_filters( 'tainacan-get-metadata-section-as-html-before-metadata-list', $before_description, $metadata_section );
			$before_description = apply_filters( 'tainacan-get-metadata-section-as-html-before-metadata-list--id-' . $section_id, $before_description, $metadata_section );
			if ( is_numeric($section_index) && $section_index >= 0 ) {
				$before_description = apply_filters( 'tainacan-get-metadata-section-as-html-before-metadata-list--index-' . $section_index, $before_description, $metadata_section );	
			}

			// Renders the section metadata list wrapper
			$return .= $before_metadata_list . $before_description;

			// Renders the section metadata list, using get_tainacan_item_metadata_template
			if ($has_metadata_list) {
				$has_some_metadata_value = false;
				foreach( $metadata_section_metadata_list as $metadata_object) {
					$the_metadata_list = $this->get_tainacan_item_metadata_template( wp_parse_args($args['metadata_list_args'], [ 'metadata' => $metadata_object->get_id() ]), $collection_id );
					if (!$has_some_metadata_value && !empty($the_metadata_list))
						$has_some_metadata_value = true;

					$return .= $the_metadata_list;
				}

				// If no metadata value was found, this section may not be necessary
				if (!$has_some_metadata_value && $args['hide_empty'])
					return '';

			} else {
				$return .= $args['empty_metadata_list_message'];
			}
			// Gets the wrapper closer
			$after_metadata_list = $args['after_metadata_list'];

			// Let theme authors tweak the metadata list closer
			$after_description = isset($args['after_description']) ? $args['after_description'] : '';
			$after_description = apply_filters( 'tainacan-get-metadata-section-as-html-after-metadata-list', $after_description, $metadata_section );
			$after_description = apply_filters( 'tainacan-get-metadata-section-as-html-after-metadata-list--id-' . $section_id, $after_description, $metadata_section );
			if ( is_numeric($section_index) && $section_index >= 0 ) {
				$after_description = apply_filters( 'tainacan-get-metadata-section-as-html-after-metadata-list--index-' . $section_index, $after_description, $metadata_section );	
			}
			
			// Renders the section metadata list wrapper
			$return .= $after_description . $after_metadata_list;

			// Gets the wrapper closer
			$after = $args['after'];

			// Let theme authors tweak the wrapper closer
			if ( is_numeric($section_index) && $section_index >= 0 ) {
				$after = apply_filters( 'tainacan-get-metadata-section-as-html-after--index-' . $section_index, $after, $metadata_section );	
			}
			$after = apply_filters( 'tainacan-get-metadata-section-as-html-after--id-' . $section_id, $after, $metadata_section );
			$after = apply_filters( 'tainacan-get-metadata-section-as-html-after', $after, $metadata_section );
			
			// Closes the wrapper
			$return .= $after;
		}

		// Returns the html content created by the function
		return $return;
	}

	/**
	 * Returns a placeholder for the item gallery, to be
	 * used in the block editor.
	 *
	 * @param array $args {
		*     Optional. Array of arguments.
		*      @type string  $item_id						  The Item ID
		* 	   @type string	 $blockId 						  A unique identifier for the gallery, will be generated automatically if not provided,
		*	   @type bool    $isBlock						  An identifier if we're comming from a block renderer, to avois using functions not available outside of the gutenberg scope;
		* 	   @type array 	 $layoutElements 				  Array of elements present in the gallery. Possible values are 'main' and 'carousel'
		* 	   @type array 	 $mediaSources 					  Array of sources for the gallery. Possible values are 'document' and 'attachments'
		* 	   @type bool 	 $hideFileNameMain 				  Hides the Main slider file name
		* 	   @type bool 	 $hideFileCaptionMain 			  Hides the Main slider file caption
		* 	   @type bool 	 $hideFileDescriptionMain		  Hides the Main slider file description
		* 	   @type bool 	 $hideFileNameThumbnails 		  Hides the Thumbnails carousel file name
		* 	   @type bool 	 $hideFileCaptionThumbnails 	  Hides the Thumbnails carousel file caption
		* 	   @type bool 	 $hideFileDescriptionThumbnails   Hides the Thumbnails carousel file description
		* 	   @type bool 	 $hideFileNameLightbox 			  Hides the Lightbox file name
		* 	   @type bool 	 $hideFileCaptionLightbox 		  Hides the Lightbox file caption
		* 	   @type bool 	 $hideFileDescriptionLightbox	  Hides the Lightbox file description
		* 	   @type bool 	 $openLightboxOnClick 			  Enables the behaviour of opening a lightbox with zoom when clicking on the media item
		*	   @type bool	 $showDownloadButtonMain		  Displays a download button below the Main slider
		*	   @type bool	 $lightboxHasLightBackground      Show a light background instead of dark in the lightbox 
		*	   @type bool    $showArrowsAsSVG				  Decides if the swiper carousel arrows will be an SVG icon or font icon
		*	   @type string  $thumbnailsSize	 		      Media size for the thumbnail images. Defaults to 'tainacan-medium'
		*	   @type bool  	 $thumbsHaveFixedHeight			  If thumbs should have a fixed height and auto widht. Defaults to false.
		* @return string  The HTML div to be used for rendering the item galery component
	 */
	public function get_tainacan_item_gallery_template($args = []) {

		// Accepts both camelCase and snake_case
		// This is to make it easier to use in the block editor
		$args = $this->convert_params_to_camel_case($args);

		$defaults = array(
			'blockId' => 						uniqid(),
			'layoutElements' => 				array( 'main' => true, 'thumbnails' => true ),
			'isBlock' =>						false,
			'mediaSources' => 					array( 'document' => true, 'attachments' => true, 'metadata' => false),
			'hideFileNameMain' => 				true, 
			'hideFileCaptionMain' => 			false,
			'hideFileDescriptionMain' =>		true,
			'hideFileNameThumbnails' => 		true, 
			'hideFileCaptionThumbnails' => 		true,
			'hideFileDescriptionThumbnails' =>  true,
			'hideFileNameLightbox' =>	 		false, 
			'hideFileCaptionLightbox' => 		false,
			'hideFileDescriptionLightbox' =>	false,
			'openLightboxOnClick' => 			true,
			'showDownloadButtonMain' =>			true,
			'lightboxHasLightBackground' => 	false,
			'showArrowsAsSVG' =>				true,
			'thumbnailsSize' =>					'tainacan-medium',
			'thumbsHaveFixedHeight' =>			false
		);
		$args = wp_parse_args($args, $defaults);

		// Gets options from block attributes
		$block_id = $args['blockId'];
		$layout_elements = $args['layoutElements'];
		$media_sources = $args['mediaSources'];
		$hide_file_name_main = $args['hideFileNameMain'];
		$hide_file_caption_main = $args['hideFileCaptionMain'];
		$hide_file_description_main = $args['hideFileDescriptionMain'];
		$hide_file_name_thumbnails = $args['hideFileNameThumbnails'];
		$hide_file_caption_thumbnails = $args['hideFileCaptionThumbnails'];
		$hide_file_description_thumbnails = $args['hideFileDescriptionThumbnails'];
		$hide_file_name_lightbox = $args['hideFileNameLightbox'];
		$hide_file_caption_lightbox = $args['hideFileCaptionLightbox'];
		$hide_file_description_lightbox = $args['hideFileDescriptionLightbox'];
		$open_lightbox_on_click = $args['openLightboxOnClick'];
		$show_download_button_main = $args['showDownloadButtonMain'];
		$lightbox_has_light_background = $args['lightboxHasLightBackground'];
		$show_arrows_as_svg = $args['showArrowsAsSVG'];
		$thumbnails_size = $args['thumbnailsSize'];
		$thumbs_have_fixed_height = $args['thumbsHaveFixedHeight'];

		// Prefils arrays with proper values to avoid messsy IFs
		$layout_elements = array(
			'main' => (isset($layout_elements['main']) && ($layout_elements['main'] === true || $layout_elements['main'] == 'true')) ? true : false,
			'thumbnails' => (isset($layout_elements['thumbnails']) && ($layout_elements['thumbnails'] === true || $layout_elements['thumbnails'] == 'true')) ? true : false
		);
		
		$block_custom_css = '';
		
		// Text color. First we check for custom preset colors, then actual values
		$block_custom_css .= isset($args['textColor']) ? sprintf('--tainacan-media-metadata-color: var(--wp--preset--color--%s);', $args['textColor']) : '';
		$block_custom_css .= isset($args['style']['color']['text']) ? sprintf('--tainacan-media-metadata-color: %s;', $args['style']['color']['text']) : '';
		
		// Background color. First we check for custom preset colors, then actual values
		$block_custom_css .= isset($args['backgroundColor']) ? sprintf('--tainacan-media-background: var(--wp--preset--color--%s);', $args['backgroundColor']) : '';
		$block_custom_css .= isset($args['style']['color']['background']) ? sprintf('--tainacan-media-background: %s;', $args['style']['color']['background']) : '';

		// Link color, if enabled. Firts we check for custom preset colors, then actual values.
		$block_custom_css .= isset($args['linkColor']) ? sprintf('--swiper-theme-color: var(--wp--preset--color--%s);', $args['linkColor']) : '';
		if ( isset($args['style']['elements']['link']['color']['text']) ) {
			$link_color = $args['style']['elements']['link']['color']['text'];
			if ( strpos( $link_color, 'var:' ) !== false ) {
				$link_color = str_replace('|', '--', $link_color);
				$link_color = str_replace('var:', 'var(--wp--', $link_color) . ')';
			}
			$block_custom_css .= sprintf('--swiper-theme-color: %s;', $link_color);
		}
			
		// Other values are obtained directly from the attributes
		$block_custom_css .= (isset($args['arrowsSize']) && is_numeric($args['arrowsSize'])) ? sprintf('--swiper-navigation-size: %spx;', $args['arrowsSize']) : '';
		$block_custom_css .= (isset($args['mainSliderHeight']) && is_numeric($args['mainSliderHeight'])) ? sprintf('--tainacan-media-main-carousel-height: %svh;', $args['mainSliderHeight']) : '';
		$block_custom_css .= (isset($args['mainSliderWidth']) && is_numeric($args['mainSliderWidth'])) ? sprintf('--tainacan-media-main-carousel-width: %s%%;', $args['mainSliderWidth']) : '';
		$block_custom_css .= (isset($args['thumbnailsCarouselWidth']) && is_numeric($args['thumbnailsCarouselWidth'])) ? sprintf('--tainacan-media-thumbs-carousel-width: %s%%;', $args['thumbnailsCarouselWidth']) : '';
		$block_custom_css .= (isset($args['thumbnailsCarouselItemSize']) && is_numeric($args['thumbnailsCarouselItemSize'])) ? sprintf('--tainacan-media-thumbs-carousel-item-size: %spx;', $args['thumbnailsCarouselItemSize']) : '';

		// Checks if we're inside a block, otherwise we have to build this manually.
		if ( isset($args['isBlock']) && $args['isBlock'] ) {
			$wrapper_attributes = get_block_wrapper_attributes(
				array(
					'style' => $block_custom_css,
					'class' => 'tainacan-media-component'
				)
			);
		}  else {
			$wrapper_attributes = '';
			if ( !empty($block_custom_css) )
				$wrapper_attributes .= 'style="' . $block_custom_css . '" ';
			
			$wrapper_attributes .=	'class="tainacan-media-component"';
		}

		$placeholder_content = '';

		if ($layout_elements['main'])
			$placeholder_content .= '<div class="tainacan-gallery-main-placeholder wp-block-post-featured-image wp-block-post-featured-image"><div class="wp-block-post-featured-image__placeholder"><svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" preserveAspectRatio="none" class="components-placeholder__illustration" aria-hidden="true" focusable="false"><path vector-effect="non-scaling-stroke" d="M60 60 0 0"></path></svg></div></div>';
		
		if ($layout_elements['thumbnails'])
			$placeholder_content .= '<ul class="tainacan-gallery-thumbnails-placeholder">
				<li class="wp-block-post-featured-image wp-block-post-featured-image">
					<div class="wp-block-post-featured-image__placeholder"><svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" preserveAspectRatio="none" class="components-placeholder__illustration" aria-hidden="true" focusable="false"><path vector-effect="non-scaling-stroke" d="M60 60 0 0"></path></svg></div>
				</li>
				<li class="wp-block-post-featured-image wp-block-post-featured-image">
					<div class="wp-block-post-featured-image__placeholder"><svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" preserveAspectRatio="none" class="components-placeholder__illustration" aria-hidden="true" focusable="false"><path vector-effect="non-scaling-stroke" d="M60 60 0 0"></path></svg></div>
				</li>
				<li class="wp-block-post-featured-image wp-block-post-featured-image">
					<div class="wp-block-post-featured-image__placeholder"><svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" preserveAspectRatio="none" class="components-placeholder__illustration" aria-hidden="true" focusable="false"><path vector-effect="non-scaling-stroke" d="M60 60 0 0"></path></svg></div>
				</li>
				<li class="wp-block-post-featured-image wp-block-post-featured-image">
					<div class="wp-block-post-featured-image__placeholder"><svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" preserveAspectRatio="none" class="components-placeholder__illustration" aria-hidden="true" focusable="false"><path vector-effect="non-scaling-stroke" d="M60 60 0 0"></path></svg></div>
				</li>
				<li class="wp-block-post-featured-image wp-block-post-featured-image">
					<div class="wp-block-post-featured-image__placeholder"><svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" preserveAspectRatio="none" class="components-placeholder__illustration" aria-hidden="true" focusable="false"><path vector-effect="non-scaling-stroke" d="M60 60 0 0"></path></svg></div>
				</li>
			</ul>';

		return '<div ' . $wrapper_attributes . '>' . $placeholder_content . '</div>';
	}


	function get_taxonomies_query_args() {
		return array(
			'order' => get_query_var( 'order', apply_filters('tainacan-default-taxonomy-terms-order', 'ASC') ),
			'orderby' => get_query_var( 'orderby', apply_filters('tainacan-default-taxonomy-terms-orderby', 'name') ),
			'termspaged' => get_query_var( 'termspaged', 1 ),
			'perpage' => get_query_var( 'perpage', apply_filters('tainacan-default-taxonomy-terms-perpage', 12) ),
			'search' => get_query_var( 'search', '' ),
			'termsparent' => get_query_var( 'termsparent', '0' )
		);
	}

	/**
	 * Registers Tainacan oficial View Modes and their placeholders
	 */
	function register_tainacan_oficial_view_modes() {

		/**
		 * Registers the default placeholder template, which is used by Records and any View mode that does not defines it
		 */
		$this->default_placeholder_template = '<ul style="list-style: none;width: 100%; height: auto; column-width: 220px; gap: 15px;padding: 0;">' .
			array_reduce( range(0,5), function($container, $i) {
				$container .= '<li style="break-inside: avoid; width: calc(100% - 40px); height: auto; background-color: var(--tainacan-block-gray1, #f2f2f2); margin: 0 0 15px 0; padding: 20px;">
					<div style="width: 100%; height: 10px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 0 0 8px 0;"></div>
					<div style="width: 42px; height: 54px; background-color: var(--tainacan-block-gray2, #dbdbdb);float: right;margin-left: 10px;margin-bottom: 10px;"></div>
					<div style="width: 60%; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 14px 0;"></div>
					<div style="width: 50%; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 14px 0;"></div>
					<div style="width: 65%; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 14px 0;"></div>' .
					array_reduce( range(0,6), function($item, $m) {
						$should_appear = wp_rand(0,1);
						if ($should_appear)
							$item .= '<div style="width: ' . wp_rand(65,100). '%; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 14px 0;"></div>';
						return $item;	
					}) .
				'</li>';
				return $container;
			}) .
		'</ul>';
		
		$this->register_view_mode('table', [
			'label' => __('Table', 'tainacan'),
			'description' => __('The classic table display.', 'tainacan'),
			'dynamic_metadata' => true,
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewtable tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false,
			'placeholder_template' => '<ul style="list-style: none;width: 100%; height: auto; display: flex; flex-direction: column; overflow-x: auto;padding: 0;">' .
				array_reduce( range(0,9), function($container, $i) {
					$container .= '<li style="display: flex; align-items: center; flex-direction: row; flex-wrap: nowrap; gap: 15px; width: 100%; height: 40px; background-color: var(--tainacan-block-gray' . ($i % 2 == 0 ? 1 : 0) . ', #f2f2f2); padding: 2px 6px;">
						<div style="flex-shrink: 0; width: 32px; height: 32px; background-color: var(--tainacan-block-gray2, #dbdbdb);"></div>
						<div style="width: 180px; height: 10px; background-color: var(--tainacan-block-gray3, #a5a5a5);></div>
						<div style="width: 120px; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
						<div style="width: 70px; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
						<div style="width: 100px; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
						<div style="width: 90px; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
						<div style="width: 60px; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
						<div style="width: 70px; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
						<div style="width: 100px; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
						<div style="width: 90px; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
						<div style="width: 100px; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
					</li>';
					return $container;
				}) .
			'</ul>'
		]);
		$this->register_view_mode('cards', [
			'label' => __('Cards', 'tainacan'),
			'dynamic_metadata' => false,
			'description' => __('A cards view, displaying cropped thumbnails, title and description.', 'tainacan'),
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewcards tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false,
			'placeholder_template' => '<ul style="list-style: none;width: 100%; height: auto; display: grid;grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); gap: 15px;padding: 0;">' .
				array_reduce( range(0,5), function($container, $i) {
					$container .= '<li style="height: auto; background-color: var(--tainacan-block-gray1, #f2f2f2);padding: 20px;">
						<div style="width: 100%; height: 10px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 0 0 8px 0;"></div>
						<div style="width: 64px; height: 64px; background-color: var(--tainacan-block-gray2, #dbdbdb);float:left;margin-right:10px;"></div>
						<div style="margin-left: 74px;">
							<div style="width: ' . wp_rand(85,100) . '%; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 14px 0;"></div>
							<div style="width: ' . wp_rand(75,90) . '%; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 14px 0;"></div>
							<div style="width: ' . wp_rand(30,65) . '%; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 14px 0;"></div>
						</div>	
					</li>';
					return $container;
				}) .
			'</ul>'
		]);
		$this->register_view_mode('records', [
			'label' => __('Records', 'tainacan'),
			'dynamic_metadata' => true,
			'description' => __('A records view, similiar to cards, but flexible for metadata.', 'tainacan'),
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewrecords tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false,
			'placeholder_template' => $this->default_placeholder_template
		]);
		$this->register_view_mode('masonry', [
			'label' => __('Masonry', 'tainacan'),
			'dynamic_metadata' => false,
			'description' => __('A masonry view, similar to pinterest, which will display images without cropping.', 'tainacan'),
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewmasonry tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'placeholder_template' => '<ul style="list-style: none;width: 100%; height: auto; column-width: 120px; gap: 15px;padding: 0;">' .
				array_reduce( range(0,11), function($container, $i) {
					$container .= '<li style="break-inside: avoid; width: calc(100% - 20px); height: auto; background-color: var(--tainacan-block-gray1, #f2f2f2); margin: 0 0 15px 0; padding: 10px;">
						<div style="width: 100%;height: ' . ($i % 2 == 0 ? wp_rand(80, 120) : wp_rand(60, 100)) . 'px; background-color: var(--tainacan-block-gray2, #dbdbdb);margin-bottom: 10px;"></div>	
						<div style="width: 100%;height: 10px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
					</li>';
					return $container;
				}) .
			'</ul>'
		]);
		$this->register_view_mode('slideshow', [
			'label' => __('Slides', 'tainacan'),
			'dynamic_metadata' => false,
			'description' => __('A fullscreen slideshow view, that shows the item document instead of just thumbnails.', 'tainacan'),
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'show_pagination' => false,
			'full_screen' => true,
			'placeholder_template' => '<div style="background-color: var(--tainacan-block-gray0, #f0f0f0);display: flex; flex-direction: column; gap: 4px; padding: 4px">
					<div style="display: flex; align-items: center;flex-direction: column; height: calc(100% - 90px); justify-content: center; gap: 12px;padding: 4px;">
						<div style="width: 800px;max-width: 90%; height: 400px; background-color: var(--tainacan-block-gray2, #dbdbdb);"></div>
						<div style="width: 300px;max-width: 90%; height: 10px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 10px 0;"></div>
					</div>
					<ul style="list-style: none;width: 100%; height: 86px;display: flex;flex-direction:row;gap: 4px;margin:0px;padding: 4px;overflow-x: auto;">' .
						array_reduce( range(0,17), function($container, $i) {
							$container .= '<li style="width: 76px; height: 76px; flex-shrink: 0; background-color: var(--tainacan-block-gray2, #dbdbdb;">
								<div style="width: 70px;height:70px; background-color: var(--tainacan-block-gray2, #dbdbdb);margin-bottom: 10px;"></div>
							</li>';
							return $container;
						}) .
					'</ul>
				</div>'
		]);
		$this->register_view_mode('list', [
			'label' => __('List', 'tainacan'),
			'dynamic_metadata' => true,
			'description' => __('A list view, similiar to the records, but full width.', 'tainacan'),
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewlist tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false,
			'placeholder_template' => '<ul style="list-style: none;width: 100%; height: auto; display: flex;flex-direction:column; gap: 15px;padding: 0;">' .
				array_reduce( range(0,5), function($container, $i) {
					$container .= '<li style="height: auto; background-color: var(--tainacan-block-gray1, #f2f2f2);padding: 20px;">
						<div style="width: 100%; height: 10px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 0 0 8px 0;"></div>
						<div style="width: 64px; height:' . wp_rand(60,100) . 'px; background-color: var(--tainacan-block-gray2, #dbdbdb);float:left;margin-right:10px;"></div>
						<div style="margin-left: 74px; column-width: 200px;">' .
							array_reduce( range(0,11), function($item, $m) {
								$should_appear = wp_rand(0,1);
								if ($should_appear)
									$item .= '<div style="break-inside: avoid;width: ' . wp_rand(45,100). '%; height: 6px; background-color: var(--tainacan-block-gray3, #a5a5a5); margin: 14px 0;"></div>';
								return $item;	
							}) .
						'</div>	
					</li>';
					return $container;
				}) .
			'</ul>'
		]);

		$map_view_mode_placeholder = '';
		ob_start();
		?>
			<div style="display: flex; gap: 4px;">
				<ul style="float: inline-start;list-style: none;width: 180px; height: auto;display: flex;flex-direction:column;gap: 4px;margin:0px;padding: 0;">
					<?php echo wp_kses_post(array_reduce( range(0,5), function($container, $i) {
							$container .= '<li style="height: 40px; background-color: var(--tainacan-block-gray1, #f2f2f2);display: flex;flex-direction:row;align-items: center;padding: 3px 6px;gap: 6px">
								<div style="min-width: 32px; height: 32px; background-color: var(--tainacan-block-gray2, #dbdbdb);"></div>		
								<div style="width: ' . wp_rand(40, 80) . '%; height: 10px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
							</li>';
							return $container;
						}));
					?>
				</ul>
				<div style="width: calc(100% - 180px); height: auto;background-repeat: no-repeat;background-size: cover; background-color: var(--tainacan-block-gray1, #f2f2f2);background-image: url('data:image/svg+xml,%3Csvg%20width%3D%22803.301%22%20height%3D%22391.635%22%20viewBox%3D%220%200%20212.54%20103.62%22%20xml%3Aspace%3D%22preserve%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M-1.764%2048.512v19.154c1.643-3.092%2012.072%203.154%2010.543%204.4-2.274.13-10.2-1.377-6.518%202.47-4.975%202.865-3.496-.328-3.776-3.699-.125-.328-.173-.585-.25-.868v82.322h212.39V48.51H-1.764zm96.846.12c3.239%202.381%206.627%204.737%202.39%207.61-.123%203.148-.013%206.372-2.39%208.78-1.41%202.379-2.655%204.976-5.803%205.033-4.996-.803-4.05%206.176-8.377%205.357-6.435-3.166-2.866-13.901-9.489-15.79-7.714%203.195-1.378-9.275%203.455-8.514%206.733-.863%2013.475-1.655%2020.214-2.476m-32.926%201.892c3.351.21%2010.733-.612%204.808%203.871-7.285%204.64-2.894%208.74%202.653%2012.288%205.005%201.564%201.787%206.614-2.232%206-5.634%201.216-3.195-3.735-4.979-5.016-3.86.052.7%207.466-4.436%205.12-4.874-.83-5.39%206.603.628%206.05%204.48%206.915%204.413-.687%204.247-4.557%203.648.69%208.11.075%209.91%204.048%206.952%204.696-.63%207.087-4.817%209.589-.432%203.753-5.377%206.367-6.2%2010.142%201.538%205.38-3.581-2.222-7.154.038-2.67.478-4.12%205.98.138%205.671%204.84-2.911%202.436%206.369%207.09%205.704%205.958-2.69%2011.284.075%2014.644%202.59%203.614-.28%201.637%205.416%206.515%204.809%205.063%202.254%205.556%204.004%202.913%208.695-.234%203.194-3.913%204.846-5.614%207.384-3.384%204.52-9.925%207.976-9.675%2013.912-3.303%201.378%201.633%208.579-3.516%203.746-3.212-2.166-1.515-6.244-1.82-9.449-.684-5.575%205.617-13.963-1.204-16.566-2.566-4.201-2.454-9.471-2.277-13.005-4.47.502-5.255-4.622-10.158-4.957-3.732-3.626-8.605-3.759-11.169-8.23-1.54-2.41-3.953-4.564-4.951-7.131.614-3.08.368-6.336-2.6-8.125-3.426-.897-1.898-6.933-8.803-6.592-3.303-.098-6.136%202.724-9.246%203.82-3.904%202.261-3.947%201.71-.824-1.147%201.345-2.476-7.577-10.715-.62-13.204%203.54-2.395%207.91-.168%2011.866-.25%204.296.127%2011.682%201.935%2013.992.943-9.72-5.633%204.37-10.148%209.598-11.717%204.454-1.347%208.688-4.049%2013.294-4.473zm105.28%207.997c3.442-.211%2010.085%201.45%204.81%203.18-5.085%202.027.264%201.798%202.922%202.15l12.268%201.246c3.244-5.525%204.112.794%209.084.107%204.173%202.403%2011.066.24%2013.575%202.274%201.391%206.078-.485%209.82-6.983%208.917.577%204.614-5.54%2010.971-4.816%203.045%205.948-5.668-10.954-2.588-11.062%202.175%204.11-3.36%205.01%204.84%201.363%204.509-2.962%203.313-4.868%206.277-7.64%209.06-.915-6.945-4.9-.154-2.66%203.46-.09%203.552-7.653%203.665-8.056%206.873%204.34%203.952-3.2%205.42-4.218%202.404-.243%203.457%206.236%205.956.883%206.131%201.866%203.31%206.218%204.505%207.79%207.51-4.62.607-12.983-7.129-10.42-8.863.34-1.85-2.834-10.142-4.57-11.768-.971%203.62-5.74%205.006-5.438%208.75-6.681%201.08-3.625-12.508-12.141-9.987-3.004-.673-7.8-1.951-4.028%202.08%2010.37-1.547-2.863%2010.882-4.467%203.947-.707-3.348-7.323-12.563-5.17-4.36%201.583%203.895%204.176%209.357%209.223%207.656.658%201.726-4.62%206.84-5.866%209.642-.15%206.409-2.962%2014.724-9.85%2018.29l-.873-.072-1.166-.132v.001c-2.991-.089-2.415-4.738-3.872-6.787-3.377-3.939%201.386-8.048-2.084-11.556-.916-2.905-2.281-6.522-7.013-5.758-2.752.506-5.004-.321-6.753-2.497-2.791-1.61-.898-5.354-1.448-7.985%201.102-3.649%205.953-5.589%208.585-8.537%205.06-3.076%206.732%203.976%2011.149%202.59%202.733.683%2012.019-.296%208.762-2.806-2.107%205.425-5.946-6.487-4.25%201.957-2.54-.19-5.887-11.26-6.545-7.22%203.569%204.908-.306%205.332-2.602.75-4.135-2.31-9.814%2010.508-10.908%201.62-1.58-2.855%207.522.396%202.953-4.592%203.52-1.875%206.84-4.504%207.221-7.288%202.45%204.338%2013.631-3.1%2010.13-3.49-4.964%202.47-2.5-2.831-1.778-3.937-2.662-1.66-4.452%204.633-3.184%206.983-2.96%201.218-4.755-1.596-7.463-1.152%201.023-5.74%205.767-11.255%2011.87-11.6%203.072.937%2012.01.422%208.235%204.673-7.374-1.313.788%204.043%203.6-.984%202.81-2.97%2010.532.2%204.179-5.349%201.23-4.888%2014.848-7.176%205.76-2.917-5.767%202.158-2.659%205.274%201.585%206.69-.941-4.478%204.829-5.134%207.876-3.293%203.109-2.063%208-4.054%2012.266-5.53.317-.111.743-.18%201.235-.21M96.808%2070.07c2.75-.29%207.002%201.3%202.3%203.34-1.59%201.566-2.44%201.79-3.37-.477-1.69-1.838-.58-2.69%201.07-2.863m7.954%207.477c.227.004.543.08.971.245%204.089%202.577%202.672%207.226-.681%205.1l.235-.408.14-.242c.532-1.215-2.255-4.721-.665-4.695m-2.85%203.233c1.215-.065%203.695%202.36-.16%202.117-.683-1.543-.392-2.087.16-2.117m94.297%204.736c1.721.046%203.042%201.576-1.68%202.297l-.354-.043-.275-.238c.1-1.537%201.277-2.044%202.31-2.016m-66.763%201.2c-1.64%202.106-5.955-2.118-5.252%204.045l.225.021%201.116-.714c3.156-.564%209.618.31%203.912-3.352m9.244%201.057c-.196-.054-.513.389-1.032%201.632.438%201.26-.692%205.471%201.742%204.028-.61-.873-.12-5.5-.71-5.66m51.023.14c4.7.481%201.032%206.488-1.863%207.745-1.348.26-3.249%202.885-4.243%201.16.013-2.775%206.931-3.85%206.106-8.905m-45.983.034c-.848-.33-.386%204.161.804%201.15-.342-.741-.608-1.074-.804-1.15m-28.62%203.978c.5.184%201.143%201.324-.27%201.187-.277-1.062-.03-1.297.27-1.187M3.13%2097.235c1.365-.257%205.198%202.26.586%202.71l-.723-1.348c-.498-.882-.317-1.276.137-1.362m185.89.35c1.287.224%204.24%203.524%201.148%204.157-2.092-3.32-1.92-4.292-1.148-4.157M63.83%2099.233c.184-.398%202.735%202.403.858%201.712-.717-1.09-.919-1.579-.858-1.712m-55.066%201.183c3.11%201.207%206.283%202.622%209.074%201.357%202.408.905%203.908%206.157-.02%203.067-2.514-1.864-9.106.514-9.054-4.424m53.215%201.8c1.6.065%203.35.687%201.1%201.82l-1.29-.293c-2.856-1.098-1.409-1.59.19-1.526m4.634%201.869c1.163-.09%205.186%201.074%206.246%202.632-1.363.325-2.7-.64-3.99-.928-2.62-1.144-2.953-1.65-2.256-1.704m108.85%207.75c.286.056.534.255.715.636%202.679%201.666.062%207.232-2.755%205.52-3.822.034.04-6.554%202.04-6.157m4.422%202.958c1.276.065%202.188%202.773-1.726%203.695.063-2.821.96-3.734%201.726-3.695m5.842%201.23c.692.007%201.604.29%202.735.98%204.372-1.124%209.746%207.127%202.404%203.99l-1.816-.385c-5.213.525-6.325-4.616-3.323-4.585m-1.222%206.352c2.613%201.423%205.416%205.161%206.081.378%202.579%202.5%205.528%206.605%206.7%209.903-2.182%202.743-2.473%209.094-7.274%207.258-4.805-5.496-9.29-3.684-15.46-2.185%201.537-3.151-3.874-8.027%201.461-9.888zm-48.069%201.616c1.26-.083%201.886%201.021-.077%204.226-.787%201.494-.345%204.17-2.477%201.856-3.413-2.509.453-5.944%202.554-6.082m-108.29%202.545c1.497.247%204.66%203.669-.107%202.372-.91-1.94-.573-2.484.107-2.372m3.896%203.342c1.305-.114%204.702%201.97.433%201.923-1.187-1.365-1.026-1.871-.433-1.923m175.4%205.941c.744.047%203.773%204.482%201.845%207.23-1.395%201.334-2.445%204.431-4.818%203.485-1.774-3.385%202.518-5.057%204.054-7.489-1.286-2.362-1.42-3.247-1.081-3.226%22%20fill%3D%22%23dbdbdb%22%20style%3D%22-inkscape-stroke%3Anone%3Bpaint-order%3Astroke%20fill%20markers%22%20transform%3D%22translate(2.01%20-48.632)%22%2F%3E%3C%2Fsvg%3E');background-position: center;"></div>
			</div>
		<?php
		$map_view_mode_placeholder = ob_get_clean();

		$this->register_view_mode('map', [
			'label' => __('Map', 'tainacan'),
			'dynamic_metadata' => true,
			'description' => __('A map view, for displaying items that have geocoordinate metadata.', 'tainacan'),
			'icon' => '<span class="icon">
							<i>
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--tainacan-info-color, #505253)" width="1.25em" height="1.25em">
									<path d="M15,19L9,16.89V5L15,7.11M20.5,3C20.44,3 20.39,3 20.34,3L15,5.1L9,3L3.36,4.9C3.15,4.97 3,5.15 3,5.38V20.5A0.5,0.5 0 0,0 3.5,21C3.55,21 3.61,21 3.66,20.97L9,18.9L15,21L20.64,19.1C20.85,19 21,18.85 21,18.62V3.5A0.5,0.5 0 0,0 20.5,3Z" />
								</svg>
							</i>
						</span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false,
			'placeholder_template' => $map_view_mode_placeholder
		]);

		$this->register_view_mode('mosaic', [
			'label' => __('Mosaic', 'tainacan'),
			'dynamic_metadata' => false,
			'description' => __('A mosaic view, similar to Flickr and Google Photos, which will display images without cropping.', 'tainacan'),
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewmasonry tainacan-icon-rotate-90 tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'placeholder_template' => '<ul style="list-style: none;width: 100%; height: auto; display: flex; gap: 24px 0; flex-wrap: wrap;">' .
				array_reduce( range(0,11), function($container, $i) {
					$container .= '<li style="flex-grow: 1; max-width: 35%; width: ' . ($i % 2 == 0 ? wp_rand(100, 180) : wp_rand(90, 170)) . 'px; height: 120px ; background-color: var(--tainacan-block-gray1, #f2f2f2); margin: 0; padding: 5px;">
						<div style="width: 100%;height: 100%; background-color: var(--tainacan-block-gray2, #dbdbdb);margin-bottom: 10px;"></div>
						<div style="width: 100%;height: 10px; background-color: var(--tainacan-block-gray3, #a5a5a5);"></div>
					</li>';
					return $container;
				}) .
			'</ul>'
		]);
	}

	/**
	 * Converts snake_case keys to camelCase
	 */
	private function convert_params_to_camel_case($params) {
		$converted_params = [];
		foreach ($params as $key => $value) {
			$camel_case_key = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
			$converted_params[$camel_case_key] = $value;
		}
		return $converted_params;
	}
}
