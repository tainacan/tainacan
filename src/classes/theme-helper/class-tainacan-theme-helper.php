<?php

namespace Tainacan;
use Tainacan\Entities;


class Theme_Helper {

	private static $instance = null;
	
	public $visiting_collection_cover = false;

	/**
	 * Stores view modes available to be used by the theme
	 */
	private $registered_view_modes = [];

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {

		if ( !defined('TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER') || true !== TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER ) {
			add_filter( 'the_content', [$this, 'the_content_filter_item'] );
		}

		if ( !defined('TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER') || true !== TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER ) {
			add_filter( 'the_content', [$this, 'the_content_filter_taxonomy'] );
		}

		// Replace collections permalink to post type archive if cover not enabled
		add_filter('post_type_link', array($this, 'permalink_filter'), 10, 3);

		// Replace single query to the page content set as cover for the colllection
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
		add_shortcode( 'tainacan-dynamic-items-list', array($this, 'get_tainacan_dynamic_items_list'));
		add_shortcode( 'tainacan-related-items-carousel', array($this, 'get_tainacan_related_items_carousel'));

		add_action( 'generate_rewrite_rules', array( &$this, 'rewrite_rules' ), 10, 1 );
		add_filter( 'query_vars', array( &$this, 'rewrite_rules_query_vars' ) );
		add_filter( 'template_include', array( &$this, 'rewrite_rule_template_include' ) );
		add_action( 'pre_get_posts', array($this, 'archive_repository_pre_get_posts'));
		// TODO: fix the WP Title 
		// add_filter( 'wp_title', array($this, 'archive_repository_wp_title'), 10, 3);
		
		add_action( 'wp_head', array($this, 'add_social_meta'), 5 );
		
		$this->register_view_mode('table', [
			'label' => __('Table', 'tainacan'),
			'description' => 'The classic table display.',
			'dynamic_metadata' => true,
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewtable tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false
		]);
		$this->register_view_mode('cards', [
			'label' => __('Cards', 'tainacan'),
			'dynamic_metadata' => false,
			'description' => 'A cards view, displaying cropped thumbnails, title and description.',
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewcards tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false
		]);
		$this->register_view_mode('records', [
			'label' => __('Records', 'tainacan'),
			'dynamic_metadata' => true,
			'description' => 'A records view, similiar to cards, but flexible for metadata.',
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewrecords tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false
		]);
		$this->register_view_mode('masonry', [
			'label' => __('Masonry', 'tainacan'),
			'dynamic_metadata' => false,
			'description' => 'A masonry view, similar to pinterest, which will display images without cropping.',
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewmasonry tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true
		]);
		$this->register_view_mode('slideshow', [
			'label' => __('Slides', 'tainacan'),
			'dynamic_metadata' => false,
			'description' => 'A fullscreen slideshow view, that shows the item document instead of just thumbnails.',
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'show_pagination' => false,
			'full_screen' => true
		]);
		$this->register_view_mode('list', [
			'label' => __('List', 'tainacan'),
			'dynamic_metadata' => true,
			'description' => 'A list view, similiar to the records, but full width.',
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewlist tainacan-icon-1-25em"></i></span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false
		]);
		$this->register_view_mode('map', [
			'label' => __('Map', 'tainacan'),
			'dynamic_metadata' => true,
			'description' => 'A map view, for displaying items that have geocoordinate metadata.',
			'icon' => '<span class="icon">
							<i>
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--tainacan-info-color, #555758)" width="1.25em" height="1.25em">
									<path d="M15,19L9,16.89V5L15,7.11M20.5,3C20.44,3 20.39,3 20.34,3L15,5.1L9,3L3.36,4.9C3.15,4.97 3,5.15 3,5.38V20.5A0.5,0.5 0 0,0 3.5,21C3.55,21 3.61,21 3.66,20.97L9,18.9L15,21L20.64,19.1C20.85,19 21,18.85 21,18.62V3.5A0.5,0.5 0 0,0 20.5,3Z" />
								</svg>
							</i>
						</span>',
			'type' => 'component',
			'implements_skeleton' => true,
			'requires_thumbnail' => false
		]);
	}
	
	public function is_post_an_item(\WP_Post $post) {
		$post_type = $post->post_type;
		$prefix = substr( $post_type, 0, strlen( Entities\Collection::$db_identifier_prefix ) );
		return $prefix == Entities\Collection::$db_identifier_prefix;
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
			
			$collections_post_types = \Tainacan\Repositories\Repository::get_collections_db_identifiers();
			$current_post_type = get_post_type();
			
			if (in_array($current_post_type, $collections_post_types)) {
				$title = sprintf( __( 'Collection: %s' ), post_type_archive_title( '', false ) );
			}
		} elseif (is_archive()) {
			if (get_query_var('tainacan_repository_archive') == 1) {
				$title = __( 'All items in repository', 'tainacan' );
			}
		}
		
		return $title;
	}

	/**
	 * Filters the post content to create Tainacan default 
	 * item single, including its metadata sections and the
	 * item media gallery.
	 * 
	 * @return string content tweaked to the item features
	 */
	public function the_content_filter_item($content) {
		
		if (!is_single())
			return $content;

		$post = get_queried_object();
		
		// Is it a collection Item 
		if ( !$this->is_post_an_item($post) )
			return $content;
	
		$item = new Entities\Item($post);

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
	 * Filters the post content to create Tainacan default 
	 * taxonomy single, which works as a "terms archive"
	 * 
	 * @return string content tweaked to show the taxonomy terms list
	 */
	public function the_content_filter_taxonomy($content) {
		
		if ( !is_single() )
			return $content;

		$post = get_queried_object();
		
		// Is it a taxonomy-post-type post?
		if ( !$this->is_post_a_tainacan_taxonomy_postype($post) )
			return $content;
		
		$content .= tainacan_get_taxonomies_orderby();
		$content .= tainacan_get_taxonomies_search();
		$taxonomy_terms_list = tainacan_get_single_taxonomy_content($post);
		$content .= $taxonomy_terms_list['content'];
		$content .= tainacan_get_taxonomies_pagination($taxonomy_terms_list['total_terms']);

		return $content;
		
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
			if ( !isset($_GET['orderby']) )
				$wp_query->set('orderby', 'name');
			
			if ( !isset($_GET['order']) )
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
	
	/**
	 * Allows themes to create a tainacan/single-items.php file which will
	 * be used to represent all items single page.
	 */
	function item_template_hierarchy($templates) {
		
		if ( !is_single() )
			return $templates;

		$collections_post_types = \Tainacan\Repositories\Repository::get_collections_db_identifiers();
		$current_post_type = get_post_type();
		
		if ( in_array($current_post_type, $collections_post_types) ) {
			
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

		$collections_post_types = \Tainacan\Repositories\Repository::get_collections_db_identifiers();
		$current_post_type = get_post_type();
		
		if ( in_array($current_post_type, $collections_post_types) ) {
			
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
		if ( $this->is_post_a_tainacan_taxonomy_postype($post) ) {
			
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
		$props = ' ';

		// Passes arguments to custom props
		if ($args) {
			foreach ($args as $key => $value) {
				if ($value == true || $value == 'true') {
					$props .= str_replace('_', '-', $key) . '="' . $value . '" ';
				}
			}
		}

		wp_enqueue_media();

		$allowed_html = [
			'div' => [
				'id' => true,
				'data-module' => true,
				'collection-id' => true,
				'hide-file-modal-button' => true,
				'hide-text-modal-button' => true,
				'hide-link-modal-button' => true,
				'hide-thumbnail-section' => true,
				'hide-attachments-section' => true,
				'show-allow-comments-section' => true,
				'hide-collapses' => true,
				'hide-help-buttons' => true,
				'hide-metadata-types' => true,
				'help-info-bellow-label' => true,
				'document-section-label' => true,
				'thumbnail-section-label' => true,
				'attachments-section-label' => true,
				'metadata-section-label' => true,
				'sent-form-heading' => true,
				'sent-form-message' => true,
				'item-link-button-label' => true,
				'show-item-link-button' => true,
				'show-terms-agreement-checkbox' => true,
				'terms-agreement-message' => true,
				'enabled-metadata' => true,
			]
		];

		return wp_kses("<div data-module='item-submission-form' id='tainacan-item-submission-form' $props ></div>", $allowed_html);
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
		 *     @type bool	$hide_displayed_metadata_dropdown			Hides the "Displayed metadata" dropdown even if the current view modes allows it	
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
	 * @return string  The HTML div to be used for rendering the items list vue component
	 */
	public function search_shortcode($args) {
		return $this->get_tainacan_items_list($args, true);
	}
	public function get_tainacan_items_list($args, $force_enqueue = false) {
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
		$default_order = 'ASC';
		if ( isset($args['default_order']) ) {
			$default_order = $args['default_order'];
			unset($args['default_order']);
		}
		
		$default_orderby = 'date';
		if ( isset($args['default_orderby']) ) {
			$default_orderby = $args['default_orderby'];
			unset($args['default_orderby']);
		}

		// If in a collection page
		$collection = tainacan_get_collection($args);
		if ($collection) {
			$props .= "collection-id='" . $collection->get_id() . "' ";
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
			$props .= "term-id='" . $term->term_id . "' ";
			$props .= "taxonomy='" . $term->taxonomy . "' ";
		}
		
		$props .= "default-view-mode='" . $default_view_mode . "' ";
		$props .= "enabled-view-modes='" . implode(',', $enabled_view_modes) . "' ";
		$props .= "default-order='" . $default_order . "' ";
		$props .= "default-orderby='" . (is_array($default_orderby) ? json_encode($default_orderby) : $default_orderby) . "' ";

		// Passes arguments to custom props
		foreach ($args as $key => $value) {
			if ($value == true || $value == 'true') {
				$props .= str_replace("_", "-", $key) . "='" . $value . "' ";
			}
		}

		$allowed_html = [
			'div' => [
				'id' => true,
				'data-module' => true,
				'collection-id' => true,
				'term-id' => true,
				'taxonomy' => true,
				'default-view-mode' => true,
				'is-forced-view-mode' => true,
				'enabled-view-modes' => true,
				'default-order' => true,
				'default-orderby' => true,
				'hide-filters' => true,
				'hide-hide-filters-button' => true,
				'hide-search' => true,
				'hide-advanced-search' => true,
				'hide-displayed-metadata-button' => true,
				'hide-sorting-area' => true,
				'hide-items-thumbnail' => true,
				'hide-sort-by-button' => true,
				'hide-exposers-button' => true,
				'hide-items-per-page-button' => true,
				'hide-go-to-page-button' => true,
				'hide-pagination-area' => true,
				'default-items-per-page' => true,
				'show-filters-button-inside-search-control' => true,
				'start-with-filters-hidden' => true,
				'filters-as-modal' => true,
				'show-inline-view-mode-options' => true,
				'show-fullscreen-with-view-modes' => true
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
			
			return locate_template($templates, false);
			
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
	 * 		@type string 		$label				 Label, visible to users. Default to $slug
	 * 		@type string		$description		 Description, visible only to editors in the admin. Default none.
	 * 		@type string		$type 				 Type. Accepted values are 'template' or 'component'. Default 'template'
	 * 		@type string		$template			 Full path  to the template file to be used. Required if $type is set to template.
	 * 												 Default: theme-path/tainacan/view-mode-{$slug}.php
	 * 		@type string		$component			 Component tag name. The web component js must be included and must accept two props:
	 * 												 	* items - the list of items to be rendered
	 * 												 	* displayed-metadata - list of metadata to be displayed
	 * 												 Default view-mode-{$slug}
	 * 		@type string		$thumbnail			 Full URL to an thumbnail that represents the view mode. Displayed in admin.
	 * 		@type string		$icon 				 HTML that outputs an icon that represents the view mode. Displayed in front end.
	 * 		@type bool			$show_pagination	 Wether to display or not pagination controls. Default true.
	 * 		@type bool			$full_screen		 Wether the view mode will display full screen or not. Default false.
	 * 		@type bool			$dynamic_metadata	 Wether to display or not (and use or not) the "displayed metadata" selector. Default false.
	 * 		@type bool			$implements_skeleton Wether the view mode has its own strategy for disaplying loading state.
	 * 		@type string		$skeleton_template	 If the view mode is a template, this is the html of its loading state.
	 * 		@type bool			$required_thumbnail	 Wether the view mode considers essential that the item thumbnail is available, even if it is a placeholder.
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

		if (!$this->is_post_an_item($post))
			return null;

		$item = new \Tainacan\Entities\Item($post);

		return $item;

	}

	/**
	 * Adds meta tags to the header to improve social sharing 
	 */
	public function add_social_meta() {

		if ( is_single() || is_tax() || is_archive() ) {

			$logo = get_template_directory_uri() . '/assets/images/social-logo.png';
			$excerpt = get_bloginfo( 'description' );
			$url_src = esc_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
			global $wp;
				
			if ( is_post_type_archive() ) {
				$collection_id = tainacan_get_collection_id();
				if ($collection_id) {
					$title = tainacan_get_the_collection_name();
					$img_info = ( has_post_thumbnail( tainacan_get_collection_id() ) ) ? wp_get_attachment_image_src( get_post_thumbnail_id( tainacan_get_collection_id() ), 'full' ) : $logo;
					$url_src = home_url( $wp->request );
					$excerpt = strip_tags(tainacan_get_the_collection_description());
				} elseif ( is_post_type_archive('tainacan-collection') ) {
					$title = __('Collections', 'tainacan');
				} elseif ( is_post_type_archive('tainacan-taxonomy') ) {
					$title = __('Taxonomies', 'tainacan');
				} else {
					$title = get_the_archive_title();
				}
			} elseif ( is_singular() ) {
				global $post;

				if ( !is_object($post) ) { return; }

				$title = get_the_title();
				$img_info = ( has_post_thumbnail( $post->ID ) ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ) : $logo;
				$url_src = get_permalink();
				$content = wp_trim_words( $post->post_content, 28, '[...]' );
				if ( $content ) {
					$excerpt = strip_tags( $content );
					$excerpt = str_replace( '', "'", $excerpt );
				} 
			} elseif ( is_tax() ) {
				$term = get_queried_object();
				$tainacan_term = tainacan_get_term();
				
				$title = $term->name;
				$excerpt = strip_tags($term->description);
				
				$url_src = get_term_link($term->term_id, $term->taxonomy);

				if ($tainacan_term) {
					$_term = new \Tainacan\Entities\Term( $tainacan_term );
					$img_id = $_term->get_header_image_id();
					if ($img_id) {
						$img_info = wp_get_attachment_image_src( $img_id, 'full' );
					}
				}
				
			} else {
				
				if ( is_day() ) :
					$title =  sprintf( __( 'Daily Archives: %s', 'tainacan-interface' ), get_the_date() );
				elseif ( is_month() ) :
					$title =  sprintf( __( 'Monthly Archives: %s', 'tainacan-interface' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'tainacan-interface' ) ) );
				elseif ( is_year() ) :
					$title =  sprintf( __( 'Yearly Archives: %s', 'tainacan-interface' ), get_the_date( _x( 'Y', 'yearly archives date format', 'tainacan-interface' ) ) );
				elseif ( is_author() ) :
					$title = get_the_author();
				else :
					$title = get_the_archive_title();
				endif;
				
			}

			$image = array(
			   'url' => ( ! empty( $img_info[0] ) && $img_info[1] >= 200 && $img_info[2] >= 200 ) ? $img_info[0] : $logo,
			   'width' => ( ! empty( $img_info[1] ) && $img_info[1] >= 200 && $img_info[2] >= 200 ) ? $img_info[1] : 200,
			   'height' => ( ! empty( $img_info[2] ) && $img_info[1] >= 200 && $img_info[2] >= 200 ) ? $img_info[2] : 200,
			);

			?>
			<meta property="og:type" content="article"/>
			<meta property="og:title" content="<?php echo esc_attr($title); ?>"/>
			<meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo()); ?>"/>
			<meta property="og:description" content="<?php echo esc_html($excerpt); ?>"/>
			<meta property="og:url" content="<?php echo esc_url($url_src); ?>"/>
			<meta property="og:image" content="<?php echo esc_url($image['url']); ?>"/>
			<meta property="og:image:width" content="<?php echo esc_attr($image['width']); ?>"/>
			<meta property="og:image:height" content="<?php echo esc_attr($image['height']); ?>"/>


		<?php } else { return; } // End if().
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
		$args = $_GET;
		
		// Defines where are we getting items from
		$entity = [];
		if (isset($args['source_list']) && $args['source_list'] == 'collection' && $collection_id = tainacan_get_collection_id()) {
			$entity = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_id);
		}
		unset($args['source_list']);

		if (isset($args['pos'])) {

			// Sets Page based on position
			$args['perpage'] = '1';
			$current_position = (int)$args['pos'] + 1;
			unset($args['pos']);
			$args = (new \Tainacan\API\EndPoints\REST_Items_Controller())->process_request_filters($args);

			// Fetch Previous Item
			if($current_position > 1) {
				$args['paged'] = $current_position - 1;
				$items = \Tainacan\Repositories\Items::get_instance()->fetch($args, $entity, 'WP_Query');
				
				if ($items->have_posts()) {
					$items->the_post();
					$item = new Entities\Item($items->post);

					if (!empty($item)  && $item instanceof \Tainacan\Entities\Item) {
						$adjacent_items['previous'] = [
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
			'show_collection_header' => false,
			'show_collection_label' => false,
			'collection_background_color' => '#454647',
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
		
		// Builds parameters to the html div rendered by Vue
		foreach ($args as $key => $value) {
			if (is_bool($value))
				$value = $value ? 'true' : 'false';
			// Changes from PHP '_' notation to HTML '-' notation
			$key_attr = str_replace('_', '-', $key);
			$props .= sprintf("%s='%s' ", $key_attr, esc_attr($value));
		}
		
		return "<div data-module='carousel-items-list' id='tainacan-items-carousel-shortcode_" . uniqid() . "' $props ></div>";
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
	 * @return string  The HTML div to be used for rendering the items carousel vue component
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
			'collection_background_color' => '#454647',
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
			$props .= sprintf("%s='%s' ", $key_attr, esc_attr($value));
		}
		
		return "<div data-module='dynamic-items-list' id='tainacan-dynamic-items-list-shortcode_" . uniqid(). "' $props ></div>";
	} 

	/**
	 * Returns a group of related items list
	 * For each metatada, the collection name, the metadata name and a button linking
	 * the items list filtered is presented
	 *
	 * @param array $args {
		 *     Optional. Array of arguments.
		 *     @type string  $item_id							The Item ID
		 *     @type string  $items_list_layout					The type of list to be rendered. Accepts 'grid', 'list', 'mosaic' and 'carousel'. 
		 * 	   @type string  $order								Sorting direction to the related items query. Either 'desc' or 'asc'. 
		 * 	   @type string  $orderby							Sortby metadata. By now we're accepting only 'title' and 'date'.
		 *     @type string  $class_name						Extra class to add to the wrapper, besides the default wp-block-tainacan-carousel-related-items
		 *     @type string  $collection_heading_class_name		Extra class to add to the collection name wrapper. Defaults to ''
		 * 	   @type string  $collection_heading_tag			Tag to be used as wrapper of the collection name. Defaults to h2
		 *     @type string  $metadata_label_class_name			Extra class to add to the metadata label wrapper. Defaults to ''
		 * 	   @type string  $metadata_label_tag				Tag to be used as wrapper of the metadata label. Defaults to p
		 *     @type array   $carousel_args						Array of arguments to be passed to the get_tainacan_items_carousel function if $items_list_layout == carousel
		 *     @type array   $dynamic_items_args				Array of arguments to be passed to the get_tainacan_dynamic_items function if $items_list_layout != carousel
		 * @return string  The HTML div to be used for rendering the related items vue component
	 */
	public function get_tainacan_related_items_list($args = []) {
		$defaults = array(
			'class_name' => '',
			'collection_heading_class_name' => '',
			'collection_heading_tag' => 'h2', 
			'metadata_label_class_name' => '',
			'metadata_label_tag' => 'p',
			'carousel_args' => [],
			'dynamic_items_args' => []
		);
		$args = wp_parse_args($args, $defaults);
		
		// Gets the current Item
		$item = isset($args['item_id']) ? $this->tainacan_get_item($args['item_id']) : $this->tainacan_get_item();
		if (!$item)
			return;
		
		// Then fetches related ones
		$related_items_query_args = [];

		if ( isset($args['orderby']) )
			$related_items_query_args['orderby'] = $args['orderby'];

		if ( isset($args['order']) )
			$related_items_query_args['order'] = $args['order'];

		if ( isset($args['max_items_number']) )
			$related_items_query_args['posts_per_page'] = $args['max_items_number'];
		
		$related_items = $item->get_related_items($related_items_query_args);

		if (!count($related_items))
			return;

		// Always pass the default class. We force passing the wp-block-tainacan-carousel-related-items because themes might have used it to style before the other layouts exist;
		$output = '<div data-module="related-items-list" class="' .  esc_attr($args['class_name']) . ' wp-block-tainacan-carousel-related-items wp-block-tainacan-related-items' . '">';
		
		foreach($related_items as $collection_id => $related_group) {
			
			if ( isset($related_group['items']) && isset($related_group['total_items']) && $related_group['total_items'] ) {
				// Adds a heading with the collection name
				$collection_heading = '';
				if ( isset($related_group['collection_name']) ) {
					$collection_heading = wp_kses_post('<' . $args['collection_heading_tag'] . ' class="' . $args['collection_heading_class_name'] . '">' . $related_group['collection_name'] . '</' . $args['collection_heading_tag'] . '>');
				}
				
				// Adds a paragraph with the metadata name
				$metadata_label = '';
				if ( isset($related_group['metadata_name']) ) {
					$metadata_label = wp_kses_post('<' . $args['metadata_label_tag'] . ' class="' . $args['metadata_label_class_name'] . '">' . $related_group['metadata_name'] . '</' . $args['metadata_label_tag'] . '>');
				}

				// Sets the carousel, from the items carousel template tag.
				$items_list_div = '';
				if ( isset($related_group['collection_id']) ) {

					$block_args = (isset($args['items_list_layout']) && $args['items_list_layout'] !== 'carousel' )
						? $args['dynamic_items_args']
						: $args['carousel_args'];

					$no_crop_images_to_square = isset($block_args['crop_images_to_square']) && !$block_args['crop_images_to_square'];
					$image_size = isset($block_args['image_size']) 
						? $block_args['image_size']
						: ($no_crop_images_to_square ? 'tainacan-medium-full' : 'tainacan-medium');

					// Remove attribute description to avoid poluting HTML
					$related_group['items'] = array_map(
						function($el) use ($image_size) {
							unset($el['description']);
							return $el;
						}, $related_group['items']
					);

					if ( isset($args['items_list_layout']) && $args['items_list_layout'] !== 'carousel' ) {
						$items_list_args = wp_parse_args([
							'collection_id' => $related_group['collection_id'],
							'load_strategy' => 'parent',
							'selected_items' => json_encode($related_group['items']),
							'layout' => $args['items_list_layout'],
							'image_size' => $image_size
						], $block_args);

						$items_list_div = $this->get_tainacan_dynamic_items_list($items_list_args);
					} else {
						$items_list_args = wp_parse_args([
							'collection_id' => $related_group['collection_id'],
							'load_strategy' => 'parent',
							'selected_items' => json_encode($related_group['items']),
							'image_size' => $image_size
						], $block_args);

						$items_list_div = $this->get_tainacan_items_carousel($items_list_args);
					}
				}
				
				$output .= '<div class="wp-block-group">
					<div class="wp-block-group__inner-container">' .
						/**
						 * Note to code reviewers: This lines doesn't need to be escaped.
						 * Functions get_tainacan_items_carousel() and get_tainacan_dynamic_items_list used here escape the return value.
						 */
						$collection_heading .
						$metadata_label .
						$items_list_div .
							( 
							$related_group['total_items'] > 1 ?
								'<div class="wp-block-buttons">
									<div class="wp-block-button">
										<a class="wp-block-button__link" href="' . esc_url( get_permalink( $related_group['collection_id'] ) ) . '?metaquery[0][key]=' . esc_attr($related_group['metadata_id']) . '&metaquery[0][value][0]=' . esc_attr($item->get_ID()) . '&metaquery[0][compare]=IN">
											' . sprintf( __('View all %s related items', 'tainacan'), $related_group['total_items'] ) . '
										</a>
									</div>
								</div>'
							: ''
							)
						. '<div style="height:30px" aria-hidden="true" class="wp-block-spacer">
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
		$args = wp_parse_args($args, [ 'items_list_layout' => 'carousel' ]);
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
		* @return string  The HTML div to be used for rendering the item galery component
	 */
	public function get_tainacan_item_gallery($args = []) {

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
			'showArrowsAsSVG' =>				true
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
					$document_description = ($attachment instanceof WP_Post) ? $attachment->post_content : '';
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
						'media_content' => get_the_post_thumbnail($item_id, 'tainacan-medium'),
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
					$media_items_thumbnails[] = 
						tainacan_get_the_media_component_slide(array(
							'media_content' => wp_get_attachment_image( $attachment->ID, 'tainacan-medium', false ),
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

		return tainacan_get_the_media_component(
			'tainacan-item-gallery-block_id-' . $block_id,
			$layout_elements['thumbnails'] ? $media_items_thumbnails : null,
			$layout_elements['main'] ? $media_items_main : null,
			array(
				'wrapper_attributes' => $wrapper_attributes,
				'class_main_div' => '',
				'class_thumbs_div' => '',
				'swiper_main_options' => $layout_elements['main'] ? array(
					'navigation' => array(
						'nextEl' => sprintf('.swiper-navigation-next_tainacan-item-gallery-block_id-%s-main', $block_id),
						'prevEl' => sprintf('.swiper-navigation-prev_tainacan-item-gallery-block_id-%s-main', $block_id),
						'preloadImages' => false,
						'lazy' => true
					)
				) : '',
				'swiper_thumbs_options' => ( $layout_elements['thumbnails'] && !$layout_elements['main'] ) ? array(
					'navigation' => array(
						'nextEl' => sprintf('.swiper-navigation-next_tainacan-item-gallery-block_id-%s-thumbs', $block_id),
						'prevEl' => sprintf('.swiper-navigation-prev_tainacan-item-gallery-block_id-%s-thumbs', $block_id),
						'preloadImages' => false,
						'lazy' => true
					)
				) : '',
				'swiper_arrows_as_svg' => $show_arrows_as_svg,
				'disable_lightbox' => !$open_lightbox_on_click,
				'hide_media_name' => $hide_file_name_lightbox,
				'hide_media_caption' => $hide_file_caption_lightbox,
				'hide_media_description' => $hide_file_description_lightbox,
				'lightbox_has_light_background' => $lightbox_has_light_background
			)
		);
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
		 *     @type bool        $hide_empty                Wether to hide or not metadata the item has no value to
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
			$before = apply_filters( 'tainacan-get-item-metadatum-as-html-before', $before, $item_metadatum );
			$before = apply_filters( 'tainacan-get-item-metadatum-as-html-before--type-' . $metadatum->get_metadata_type(), $before, $item_metadatum );
			$before = apply_filters( 'tainacan-get-item-metadatum-as-html-before--id-' . $metadatum->get_id(), $before, $item_metadatum );

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
			$after = apply_filters( 'tainacan-get-item-metadatum-as-html-after--id-' . $metadatum->get_id(), $after, $item_metadatum );
			$after = apply_filters( 'tainacan-get-item-metadatum-as-html-after--type-' . $metadatum->get_metadata_type(), $after, $item_metadatum );
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
		* @return string  The HTML div to be used for rendering the item galery component
	 */
	public function get_tainacan_item_gallery_template($args = []) {

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
			'showArrowsAsSVG' =>				true
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
}
