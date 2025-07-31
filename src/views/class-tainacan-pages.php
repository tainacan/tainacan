<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Pages is an abstract base class for all Tainacan admin pages.
 */
abstract class Pages {
	use \Tainacan\Traits\SVG_Icon;
	use \Tainacan\Traits\Admin_UI_Options;

	/**
	 * This method must be implemented, providing a page_slug (page's ID or Slug), used to identify the page in the admin menu.
	 */
	abstract protected function get_page_slug(): string;

	/**
	 * $tainacan_root_menu_slug is the root menu slug for all Tainacan admin pages.
	 * @var string
	 */
	public $tainacan_root_menu_slug = 'tainacan-root-menu';
	
	/**
	 * $tainacan_other_links_slug is the menu slug for the "Others" menu collapse
	 * @var string
	 */
	public $tainacan_other_links_slug = 'tainacan_other_links';
	
	/**
	 * Class constructor, never called directly but used to invoke the init method.
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->init();
	}
	
	/**
	 * init adds WordPress basic admin hooks
	 * 
	 * Registers user meta, check admin menu collapse state and add links to the admin menu.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( &$this, 'register_user_meta' ) );
		add_action( 'admin_menu', array( &$this, 'admin_init_ui_options' ), 0 );
		add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
		add_action( 'admin_head', array( &$this, 'remove_admin_notices' ) );
	}
	
	/**
	 * load_page is called from the 'load-$page_suffix' action and should trigger the load of page's assets.
	 * 
	 * In a child class, when registering a page with add_submenu_page, the $page_suffix is returned and can be 
	 * used to load the page's assets. This guarantees that the body class and the assets are only loaded when the
	 * user visits the respective page.
	 *
	 * @return void
	 */
	function load_page() {
		add_filter( 'admin_body_class', array( &$this, 'admin_body_class' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_fonts' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_css' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_js' ), 90 );
		add_filter( 'screen_settings', array( &$this, 'admin_add_screen_options' ), 10, 2 );
	}
	
	/**
	 * add_admin_menu is called from the 'admin_menu' action and should register whatever menu links the page has.
	 * 
	 * If a page or group of pages is to be listed in the root level of the Tainacan admin, the parent should be
	 * $this->tainacan_root_menu_slug. A child slug then should be defined so that child submenus are added to it
	 * (see class-tainacan-admin for an example). If the page is to be listed in the "Others" menu collapse, the 
	 * parent should be $this->tainacan_other_links_slug.
	 *
	 * @return void
	 */
	function add_admin_menu() {}
	
	/**
	 * admin_enqueue_css is called from the 'admin_enqueue_scripts' action only when the page is loaded and should
	 * enqueue the page's CSS using wp_enqueue_script()
	 *
	 * @return void
	 */
	function admin_enqueue_css() {}

	/**
	 * admin_enqueue_fonts is called from the 'admin_enqueue_scripts' action only when the page is loaded and should
	 * enqueue the page's fonts using wp_enqueue_script(). This is a default implementation that enqueues the Roboto
	 * as most pages will use this typography
	 */
	function admin_enqueue_fonts() {
		wp_enqueue_style( 'roboto-fonts', 'https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i', [] );
	}
	
	/**
	 * admin_enqueue_js is called from the 'admin_enqueue_scripts' action only when the page is loaded and should
	 * enqueue the page's JS using wp_enqueue_script()
	 *
	 * @return void
	 */
	function admin_enqueue_js() {}
	
	/**
	 * admin_body_class is called from the 'admin_body_class' filter and should add the page's class to the body. 
	 * 
	 * By default, it adds the class 'tainacan-pages-container', responsible to style the default container for
	 * the Tainacan admin pages. If overriden, this class should possibly be added to the returned string if
	 * a sidemenu is desired.
	 *
	 * @param  mixed $classes
	 * @return void
	 */
	function admin_body_class( $classes ) {
		$classes .= ' tainacan-pages-container';

		$current_user  = wp_get_current_user();

		if ( $current_user instanceof \WP_User ) {
			
			$user_prefs_as_string = get_user_meta( $current_user->ID, 'tainacan_prefs', true );
			$user_prefs = json_decode( $user_prefs_as_string, true );

			if ( $this->has_admin_ui_option('forceFullscreenAdminMode') || ( isset($user_prefs['is_fullscreen']) && ( $user_prefs['is_fullscreen'] == 'true' || $user_prefs['is_fullscreen'] == true ) ) )
				$classes .= ' tainacan-pages-container--fullscreen';

			if ( isset($user_prefs['admin_style']) )
				$classes .= ' tainacan-pages-container--admin-' . $user_prefs['tainacan-admin-style'] . '-style';
		}
		if ( 
			$this->has_admin_ui_option( 'hideNavigationSidebar' ) &&
			$this->has_admin_ui_option( 'hideBreadcrumbs' ) &&
			$this->has_admin_ui_option( 'forceFullscreenAdminMode') &&
			$this->has_admin_ui_option( 'hideWordPressShorcutButton' )
		)
			$classes .= ' tainacan-pages-container--iframe-mode';

		if ( $this->has_admin_ui_option( 'hideBreadcrumbs' ) )
			$classes .= ' tainacan-pages-container--hide-breadcrumbs';

		if ( $this->has_admin_ui_option( 'hideWordPressShorcutButton' ) )
			$classes .= ' tainacan-pages-container--hide-wordpress-shortcut';

		if ( $this->has_admin_ui_option( 'hideSiteShorcutButton' ) )
			$classes .= ' tainacan-pages-container--hide-site-shortcut';

		$screen = get_current_screen();
    	if ( $screen ) {

        	// Get all help tabs for the current screen
        	$help_tabs = $screen->get_help_tabs();
        
        	// Check if any help tabs exist
        	if ( !empty($help_tabs) )
				$classes .= ' tainacan-pages-container--has-help-tabs';
		}

		if ( !$this->has_admin_ui_option('forceFullscreenAdminMode') && !$this->has_admin_ui_option('hideFullscreenTogglerButton') )
			$classes .= ' tainacan-pages-container--has-screen-options';

		return $classes;
	}

	/**
	 * get_admin_js_user_data is used to build the user data object that is passed to the JS global object 'tainacan_user'.
	 * The navigation script, which is enqueued to all admin pages, uses this object to check user capabilities and tweak
	 * user prefs.
	 */
	function get_admin_js_user_data() {

		$current_user  = wp_get_current_user();
		$user_caps = array();
		$prefs     = array();
		$user_data = array();

		if ( $current_user instanceof \WP_User ) {
			$tainacan_caps = \tainacan_roles()->get_repository_caps_slugs();

			foreach ($tainacan_caps as $tcap) {
				$user_caps[$tcap] = current_user_can( $tcap );
			}
			$prefs = get_user_meta( $current_user->ID, 'tainacan_prefs', true );

			if ( $current_user->data && isset($current_user->data->user_email) && isset($current_user->data->display_name) ) {
				$user_data = array(
					'ID' => $current_user->ID,
					'email' => $current_user->data->user_email,
					'display_name' => $current_user->data->display_name
				);
			}
		}

		return array(
			'caps' => $user_caps,
			'prefs' => $prefs,
			'data' => $user_data,
			'nonce'                  	=> is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : false,
			'tainacan_api_url'         	=> esc_url_raw( rest_url() ) . 'tainacan/v2',
			'wp_api_url'            	=> esc_url_raw( rest_url() ) . 'wp/v2/',
			'wp_ajax_url'            	=> admin_url( 'admin-ajax.php' ),
		);
	}
	
	/**
	 * get_admin_js_localization_params is used to build the JS tainacan_plugin global object that serves as a 
	 * bridge between PHP and JS. Not every page needs it but they can call it to add their own data to the object.
	 *
	 * @return void
	 */
	function get_admin_js_localization_params() {
		global $TAINACAN_BASE_URL, $TAINACAN_API_MAX_ITEMS_PER_PAGE;

		$Tainacan_Collections 		= \Tainacan\Repositories\Collections::get_instance();
		$Tainacan_Metadata    		= \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Metadata_Sections = \Tainacan\Repositories\Metadata_Sections::get_instance();
		$Tainacan_Filters     		= \Tainacan\Repositories\Filters::get_instance();
		$Tainacan_Items       		= \Tainacan\Repositories\Items::get_instance();
		$Tainacan_Taxonomies  		= \Tainacan\Repositories\Taxonomies::get_instance();

		$tainacan_admin_i18n = require( 'tainacan-i18n.php' );

		$entities_labels = [
			'collections' 		=> $Tainacan_Collections->get_cpt_labels(),
			'metadata'      	=> $Tainacan_Metadata->get_cpt_labels(),
			'metadata-sections' => $Tainacan_Metadata_Sections->get_cpt_labels(),
			'filters'     		=> $Tainacan_Filters->get_cpt_labels(),
			'items'       		=> $Tainacan_Items->get_cpt_labels(),
			'taxonomies'  		=> $Tainacan_Taxonomies->get_cpt_labels(),
		];

		$tainacan_admin_i18n['entities_labels'] = $entities_labels;

		$settings = [
			'tainacan_api_url'         	=> esc_url_raw( rest_url() ) . 'tainacan/v2',
			'wp_api_url'            	=> esc_url_raw( rest_url() ) . 'wp/v2/',
			'wp_ajax_url'            	=> admin_url( 'admin-ajax.php' ),
			'classes'                	=> array(),
			'i18n'                   	=> $tainacan_admin_i18n,
			'base_url'               	=> $TAINACAN_BASE_URL,
			'plugin_dir_url'			=> plugin_dir_url( __DIR__ ),
			'admin_url'              	=> admin_url('admin.php'),
			'theme_items_list_url' 		=> esc_url_raw( get_site_url() ) . '/' . \Tainacan\Theme_Helper::get_instance()->get_items_list_slug(),
			'theme_collection_list_url' => get_post_type_archive_link( 'tainacan-collection' ),
			'theme_taxonomy_list_url' 	=> get_post_type_archive_link( 'tainacan-taxonomy' ),
			'custom_header_support'  	=> get_theme_support('custom-header'),
			'registered_view_modes'  	=> \Tainacan\Theme_Helper::get_instance()->get_registered_view_modes(),
			'enabled_view_modes'  		=> \Tainacan\Theme_Helper::get_instance()->get_enabled_view_modes(),
			'default_view_mode'		  	=> \Tainacan\Theme_Helper::get_instance()->get_default_view_mode(),
			'default_order' => \Tainacan\Theme_Helper::get_instance()->get_default_order(),
			'default_orderby' => \Tainacan\Theme_Helper::get_instance()->get_default_orderby(),
			'admin_ui_options'       	=> $this->get_available_admin_ui_options(),
			'exposer_mapper_param'   	=> \Tainacan\Mappers_Handler::MAPPER_PARAM,
			'exposer_type_param'     	=> \Tainacan\Exposers_Handler::TYPE_PARAM,
			'repository_name'	 		=> get_bloginfo('name'),
			'api_max_items_per_page'    => $TAINACAN_API_MAX_ITEMS_PER_PAGE,
			'wp_elasticpress'    		=> \Tainacan\Elastic_Press::get_instance()->is_active(),
			'item_submission_captcha_site_key' => get_option("tnc_option_recaptch_site_key"),
			'tainacan_enable_core_metadata_on_advanced_search' => (
				!defined('TAINACAN_DISABLE_CORE_METADATA_ON_ADVANCED_SEARCH') || 
				false === TAINACAN_DISABLE_CORE_METADATA_ON_ADVANCED_SEARCH
			),
			'tainacan_enable_relationship_metaquery' => ( 
				defined('TAINACAN_ENABLE_RELATIONSHIP_METAQUERY') &&
				true === TAINACAN_ENABLE_RELATIONSHIP_METAQUERY
			),
			'has_permalinks_structure' => get_option('permalink_structure') !== ''
		];
		
		$maps = [
			'collections' 		=> $Tainacan_Collections->get_map(),
			'metadata'    		=> $Tainacan_Metadata->get_map(),
			'metadata-sections' => $Tainacan_Metadata_Sections->get_map(),
			'filters'     		=> $Tainacan_Filters->get_map(),
			'items'       		=> $Tainacan_Items->get_map(),
			'taxonomies'  		=> $Tainacan_Taxonomies->get_map(),
		];

		$metadata_types = $Tainacan_Metadata->fetch_metadata_types();

		foreach( $maps as $type => $map ){
			foreach ( $map as $metadatum => $details){
				$settings['i18n']['helpers_label'][$type][$metadatum] = [ 'title' => $details['title'], 'description' => $details['description'] ];
			}
		}
		foreach ( $metadata_types as $index => $metadata_type){
			$class = new $metadata_type;
			$settings['i18n']['helpers_label'][$class->get_component()] = $class->get_form_labels();
		}

		$filter_types = $Tainacan_Filters->fetch_filter_types();
		
		foreach ( $filter_types as $index => $filter_type){
			$class = new $filter_type;
			$settings['i18n']['helpers_label'][$class->get_component()] = $class->get_form_labels();
		}

		$settings['form_hooks'] = Admin_Hooks::get_instance()->get_registered_hooks();

		$wp_post_types = get_post_types(['show_ui' => true], 'objects');
		if ( isset($wp_post_types['attachment']) ) {
			unset($wp_post_types['attachment']);
		}
		if ( isset($wp_post_types['wp_navigation']) ) {
			unset($wp_post_types['wp_navigation']);
		}

		$wp_post_types = array_map(function($i) {
			return [
				'slug' => $i->name,
				'label' => $i->label
			];
		}, $wp_post_types);

		$wp_post_types['tainacan-collection'] = [
			'slug' => 'tainacan-collection',
			'label' => $Tainacan_Collections->get_cpt_labels()['name']
		];

		$settings['wp_post_types'] = $wp_post_types;

		// Key-valued array with extra options to be passed to every request in the admin (goes the header)
		$admin_request_options = [];
		$admin_request_options = apply_filters('tainacan-admin-extra-request-options', $admin_request_options);
		$settings['admin_request_options'] = $admin_request_options;

		return $settings;

	}
	
	/**
	 * register_user_meta is called from the 'init' action and defines the tainacan_prefs object as a user meta.
	 * 
	 * The tainacan_prefs holds several user defined options such as perpage and orderby preferences.
	 *
	 * @return void
	 */
	function register_user_meta() {
		$args = array(
			//'sanitize_callback' => array(&$this, 'santize_user_tainacan_prefs'),
			//'auth_callback' => 'authorize_my_meta_key',
			'type'         => 'string',
			'description'  => 'Tainacan admin user preferences',
			'single'       => true,
			'show_in_rest' => true,
		);
		register_meta( 'user', 'tainacan_prefs', $args );
	}
	
	/**
	 * render_page_content defines the inner content of the page. It is called from the render_page method, aside the menu.
	 *
	 * @return void
	 */
	abstract public function render_page_content();
	
	/**
	 * render_page creates the canvas/container where the admin pages will be rendered, adding the navigation menu aside.
	 * 
	 * Usually this function is to be passed as a callback to the add_submenu_page function, in each child class.
	 *
	 * @return void
	 */
	public function render_page() {
		global $TAINACAN_BASE_URL;
		wp_enqueue_style( 
			'tainacan-page-container',
			$TAINACAN_BASE_URL . '/assets/css/tainacan-pages.css',
			[],
			TAINACAN_VERSION
		);
		
		wp_enqueue_script(
			'tainacan-admin-navigation-menu',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_admin_navigation_menu.js',
			[ 'wp-hooks', 'wp-i18n' ],
			TAINACAN_VERSION
		);
		wp_localize_script( 'tainacan-admin-navigation-menu', 'tainacan_user', $this->get_admin_js_user_data() );
		
		?>
		<div id="tainacan-page-container">
			<?php 
				if ( !$this->has_admin_ui_option('hideNavigationSidebar') )
					$this->render_navigation_menu();
			?>
			<main id="tainacan-page-container--inner">
				<?php
					if ( !$this->has_admin_ui_option('hideNavigationSidebar') )
						$this->render_navigation_menu_toggler_buttons();

					if ( !$this->has_admin_ui_option('hideBreadcrumbs') )
						$this->render_breadcrumbs();

					$this->render_page_content();
				?>
			</main>
		</div>

		<?php
	}
	
	/**
	 * render_navigation_menu creates the aside navigation menu for the Tainacan admin pages.
	 * 
	 * Internally, it loops through the submenu global variable to render the menu items.
	 * All submenu registered with the $tainacan_root_menu_slug are considered root links.
	 * They may or may not contain links and submenu items of their slug are rendered as ul/li tags.
	 *
	 * @return void
	 */
	public function render_navigation_menu() {
		global $submenu;

		$current_screen = get_current_screen();
		$current_page_slug = $current_screen->id ? $current_screen->id : 'toplevel_page_tainacan_dashboard';
		$current_page_parent = $current_screen->parent_file;

		$is_navigation_sidebar_collapsed = false;
		$current_user  = wp_get_current_user();

		if ( $current_user instanceof \WP_User ) {
			
			$user_prefs_as_string = get_user_meta( $current_user->ID, 'tainacan_prefs', true );
			$user_prefs = json_decode( $user_prefs_as_string, true );
			
			if ( isset($user_prefs['is_navigation_sidebar_collapsed']) && ( $user_prefs['is_navigation_sidebar_collapsed'] == 'true' || $user_prefs['is_navigation_sidebar_collapsed'] == true ) )
				$is_navigation_sidebar_collapsed = true;
		}

		?>
		<aside id="tainacan-navigation-menu" <?php echo ( $is_navigation_sidebar_collapsed ? 'class="is-collapsed"' : '') ?>>	
			<nav>
				<header>
					<?php if ( !$this->has_admin_ui_option( 'hideWordPressShorcutButton' ) || !$this->has_admin_ui_option( 'hideSiteShorcutButton' ) ) : ?>
						<a
								id="tainacan-wordpress-shortcut"
								title="<?php _e('Return to WordPress Admin', 'tainacan'); ?>"
								href="<?php echo admin_url(); ?>">
							<span class="icon"><?php echo $this->get_svg_icon( 'wordpress' ); ?></span>
						</a>

						<a
								id="tainacan-site-shortcut"
								title="<?php _e('Visit the site', 'tainacan'); ?>"
								href="<?php echo site_url(); ?>"
								target="_blank">
							<span class="menu-text"><?php echo get_bloginfo( 'name' ); ?> </span><span class="icon">↗</span>
						</a>
					<?php endif; ?>
					<h1>
						<a href="admin.php?page=tainacan_dashboard">
							<img
								id="tainacan-menu-logo-full" 
								alt="<?php _e('Tainacan', 'tainacan'); ?>" 
								width="140" 
								src="<?php echo plugin_dir_url( __DIR__ ) . '/assets/images/tainacan_logo_header.svg'; ?>" />
							<img 
								id="tainacan-menu-logo-icon"
								alt="<?php _e('Tainacan', 'tainacan'); ?>" 
								width="28" 
								src="<?php echo plugin_dir_url( __DIR__ ) . '/assets/images/tainacan_logo_icon.svg'; ?>" />
						</a>
					</h1>
					<span class="plugin-version">
						<?php echo TAINACAN_VERSION; ?>
					</span>
				</header>
				<ul id="tainacan-root-menu">
					<?php if ( !$this->has_admin_ui_option('hideNavigationHomeButton') ) : ?>
						<li>
							<a href="admin.php?page=tainacan_dashboard" <?php echo $current_page_slug === 'toplevel_page_tainacan_dashboard' ? 'aria-current="page"' : ''; ?>>
								<span class="icon"><?php echo $this->get_svg_icon( 'home' ); ?></span>
								<span class="menu-text"><?php _e('Home', 'tainacan'); ?></span>
							</a>
						</li>
					<?php endif; ?>
					<?php
						$tainacan_root_links = isset( $submenu[$this->tainacan_root_menu_slug] ) ? $submenu[$this->tainacan_root_menu_slug] : [];
						
						if ( count($tainacan_root_links) ) {
							foreach( $tainacan_root_links as $tainacan_root_link ) {

								if (  !current_user_can( $tainacan_root_link[1] ) ) 
									continue;
								
								if ( isset( $submenu[$tainacan_root_link[2]] ) ) : ?>

									<li class="menu-item-has-children <?php echo $current_page_parent === $tainacan_root_link[2] ? 'is-open' : ''; ?>">
										<div class="menu-item-backdrop"></div>
										<button type="button" aria-expanded="<?php echo $current_page_parent === $tainacan_root_link[2] ?>"><?php echo $tainacan_root_link[0]; ?></button>
										
										<?php if ( count( $submenu[$tainacan_root_link[2]] ) ) : ?>
											<ul id="<?php echo $tainacan_root_link[2]; ?>">
												<?php foreach( $submenu[$tainacan_root_link[2]] as $link ) : 
													if ( !current_user_can( $link[1] ) ) continue;
												?>
													<li>
														<a href="<?php echo add_query_arg( 'page', $link[2] ); ?>" <?php echo $current_page_slug === 'admin_page_' . $link[2] ? 'aria-current="page"' : ''; ?>><?php echo $link[0]; ?></a>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</li>

								<?php elseif ( isset( $tainacan_root_link[2] ) && $tainacan_root_link[2] !== $this->tainacan_other_links_slug ) : ?>
									<li>
										<a href="<?php echo add_query_arg( 'page', $tainacan_root_link[2] ); ?>"  <?php echo $current_page_slug === 'admin_page_' . $tainacan_root_link[2] ? 'aria-current="page"' : ''; ?>><?php echo $tainacan_root_link[0]; ?></a>
									</li>
								<?php endif; 
							}
						}
					?>
				</ul>
			</nav>
		</aside>
		<div id="tainacan-navigation-menu-backdrop"></div>
		<?php
	}

	/**
	 * render_breadcrumbs creates the breadrcumbs for the Tainacan admin pages.
	 *
	 * @return void
	 */
	public function render_breadcrumbs() {
		global $submenu;
		
		$current_screen = get_current_screen(); 
		$tainacan_root_links = isset( $submenu[$this->tainacan_root_menu_slug] ) ? $submenu[$this->tainacan_root_menu_slug] : [];
		$tainacan_root_link_submenu = null;
		
		$breadcrumbs = [];
		
		// Inserts the menu root level link
		if ( count($tainacan_root_links) ) {
			foreach( $tainacan_root_links as $tainacan_root_link ) {

				if (
					(
						$current_screen->parent_base === $this->tainacan_root_menu_slug &&
						isset($tainacan_root_link[2]) &&
						( $current_screen->base === 'admin_page_' . $tainacan_root_link[2] )
					) || 
					(
						$current_screen->parent_base !== $this->tainacan_root_menu_slug &&
						isset($tainacan_root_link[2]) &&
						( $current_screen->parent_base === $tainacan_root_link[2] )
					)
				) {
					$breadcrumbs[] = array(
						'label' => wp_strip_all_tags( $tainacan_root_link[0], true )
					);
					$tainacan_root_link_submenu = $tainacan_root_link[2];
					break;
				}
			}
		}

		// Inserts the current page link, except for the Vue Admin component, which is a SPA.
		// Its breadcrumbs are handled by the tainacan-admin-navigation-menu.js script.
		if ( !is_null($tainacan_root_link_submenu) && isset( $submenu[$tainacan_root_link_submenu] ) ) {
			foreach( $submenu[$tainacan_root_link_submenu] as $submenu_item ) {
				if ( isset($submenu_item[2]) && $current_screen->base === 'admin_page_' . $submenu_item[2] ) {
					$breadcrumbs[] = array(
						'label' => wp_strip_all_tags( $submenu_item[0], true )
					);
					break;
				}
			}
		}

		/**
		 * Allows external plugins to add breadcrumbs to the Tainacan admin pages.
		 */
		$breadcrumbs = apply_filters( 'tainacan_admin_breadcrumbs', $breadcrumbs );

		if ( count($breadcrumbs) ) {
			?>
			<div id="tainacan-breadcrumbs">
				<nav>
					<ul id="tainacan-breadcrumbs-list">
						<?php foreach( $breadcrumbs as $breadcrumb ) : ?>
							<?php if ( isset( $breadcrumb['url'] ) ) : ?>
								<li><a href="<?php echo $breadcrumb['url']; ?>"><?php echo $breadcrumb['label']; ?></a></li>
							<?php else : ?>
								<li><?php echo $breadcrumb['label']; ?></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</nav>
			</div>
			<?php
		}
	}

	/**
	 * Renders buttons for minimizing and collapsing the menu.
	 */
	function render_navigation_menu_toggler_buttons() {

		$is_menu_toggled = false;
		$is_menu_collapsed = false;

		$current_user  = wp_get_current_user();
		
		if ( $current_user instanceof \WP_User ) {
			
			$user_prefs_as_string = get_user_meta( $current_user->ID, 'tainacan_prefs', true );
			$user_prefs = json_decode( $user_prefs_as_string, true );

			if ( isset($user_prefs['menu_toggled']) ) 
				$is_menu_toggled = $user_prefs['menu_toggled'];

			if ( isset($user_prefs['menu_collapsed']) )
				$is_menu_collapsed = $user_prefs['menu_collapsed'];

		}	
		
		if ( !$this->has_admin_ui_option('hideNavigationSidebar') ) : ?>
			<button
					id="tainacan-menu-toggler"
					class="tainacan-ui-tweak-button"
					aria-label="<?php _e('Toggle menu', 'tainacan'); ?>"
					aria-pressed="<?php echo $is_menu_toggled ? 'true' : 'false'; ?>"
					title="<?php _e('Toggle menu', 'tainacan'); ?>">
				<span class="icon"><?php echo $this->get_svg_icon( 'menu' ); ?></span>
			</button>
			<?php if ( !$this->has_admin_ui_option('hideMenuCollapserButton') ) : ?>
				<button
						id="tainacan-menu-collapser"
						class="tainacan-ui-tweak-button"
						aria-label=""
						aria-pressed="<?php echo $is_menu_collapsed ? 'true' : 'false'; ?>"
						title="<?php _e('Toggle menu', 'tainacan'); ?>">
					<span class="icon"><?php echo $this->get_svg_icon( 'arrowleft' ); ?></span>
					<span class="menu-text"><?php _e('Collapse menu', 'tainacan'); ?></span>
				</button>
			<?php endif; ?>
		<?php endif;

	}

	/**
	 * remove_admin_notices removes all admin notices from the admin_notices and all_admin_notices hooks.
	 */
	public function remove_admin_notices() {
		$current_screen = get_current_screen();
		if ($current_screen && strpos($current_screen->id, 'tainacan') !== false ) {
			remove_all_actions('admin_notices');
        	remove_all_actions('all_admin_notices');
		}
	}

	/**
	 * admin_init_ui_options is a filter that sets the admin UI options for the current user,
	 * based on his/her role.
	 */
	function admin_init_ui_options() {
		
		if ( !is_admin() && !$this->get_page_slug() || !isset($_GET['page']) || ( isset($_GET['page']) && $_GET['page'] !== $this->get_page_slug() ) )
			return;
		
		/**
		 * Presets the current admin_ui_options with user role options stored on the database
		 */
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$roles = ( array ) $user->roles;
			$per_role_admin_ui_options = get_option('tainacan_admin_ui_options', []);
	
			foreach( $roles as $role ) {
				if ( isset($per_role_admin_ui_options[$role]) ) {
					foreach( $per_role_admin_ui_options[$role] as $option => $value ) {
						if ( isset($option) ) {
							self::$admin_ui_options[$option] = $value;
						}
					}
				}
			}
		}
		
		// Gets filtered options, offering a chance to define them via URL parameters
		self::$admin_ui_options = apply_filters('tainacan-admin-ui-options', array_merge( self::$admin_ui_options, $_GET ));

		/** 
		 * Preset common 'modes', which group certain admin options
		 */

		// For all these special modes, we want to render a shell-like, contained version inside the iframe, where navigation is hidden 
		if (
			( isset($_GET[ 'itemEditionMode' ]) && $_GET[ 'itemEditionMode' ] ) ||
			( isset($_GET[ 'itemCreationMode' ]) && $_GET[ 'itemCreationMode' ] ) ||
			( isset($_GET[ 'itemsSingleSelectionMode' ]) && $_GET[ 'itemsSingleSelectionMode' ] ) ||
			( isset($_GET[ 'itemsMultipleSelectionMode' ]) && $_GET[ 'itemsMultipleSelectionMode' ] ) ||
			( isset($_GET[ 'itemsSearchSelectionMode' ]) && $_GET[ 'itemsSearchSelectionMode' ] ) ||
			( isset($_GET[ 'mobileAppMode' ]) && $_GET[ 'mobileAppMode' ] )
		) {
			self::$admin_ui_options['hideNavigationSidebar'] = true;
			self::$admin_ui_options['hideBreadcrumbs'] = true;
			self::$admin_ui_options['forceFullscreenAdminMode'] = true;
			self::$admin_ui_options['hideWordPressShorcutButton'] = true;
			self::$admin_ui_options['hideExternalEntityLinks'] = true;
			self::$admin_ui_options['hideItemsListMultipleSelection'] = true;
			self::$admin_ui_options['hideItemsListBulkActionsButton'] = true;
			self::$admin_ui_options['hideItemsListActionAreas'] = true;
			self::$admin_ui_options['hideItemsListPageTitle'] = true;
			self::$admin_ui_options['hideItemsListCreationDropdown'] = true;
			self::$admin_ui_options['hideItemsListExposersButton'] = true;
			self::$admin_ui_options['hideItemsListStatusTabs'] = true;
			self::$admin_ui_options['hideItemsListFilterCreationButton'] = true;
			self::$admin_ui_options['hideItemsListContextMenuOpenItemOption'] = true;
			self::$admin_ui_options['hideItemsListContextMenuOpenItemOnNewTabOption'] = true;
			self::$admin_ui_options['hideItemsListContextMenuEditItemOption'] = true;
			self::$admin_ui_options['hideItemsListContextMenuCopyItemOption'] = true;
			self::$admin_ui_options['hideItemsListContextMenuDeleteItemOption'] = true;
		}

		// When selecting a search-like query, we want to hide the context menu and the selection area
		if (isset($_GET['itemsSearchSelectionMode']) && $_GET['itemsSearchSelectionMode']) {
			self::$admin_ui_options['hideItemsListContextMenu'] = true;
			self::$admin_ui_options['hideItemsListSelection'] = true;
		}

		// In mobile app mode, we change a portion of the item edtion page
		if ( isset($_GET[ 'mobileAppMode' ]) && $_GET[ 'mobileAppMode' ] ) {
			self::$admin_ui_options['hideItemEditionPageTitle'] = true;
			self::$admin_ui_options['hideBulkEditionPageTitle'] = true;
			self::$admin_ui_options['hideItemEditionCollapses'] = true;
			self::$admin_ui_options['hideItemEditionMetadataTypes'] = true;
			self::$admin_ui_options['itemEditionDocumentInsideTabs'] = true;
			self::$admin_ui_options['itemEditionAttachmentsInsideTabs'] = true;
			self::$admin_ui_options['itemEditionPublicationSectionInsideTabs'] = true;
		}
	}

	/**
	 * Hooks into WordPress Admin Screen Options to add option
	 * to insert fullscreen mode toggle button.
	 */
	function admin_add_screen_options($current, $screen) {

		if ( !is_admin() && !$this->get_page_slug() || !isset($_GET['page']) || ( isset($_GET['page']) && $_GET['page'] !== $this->get_page_slug() ) )
			return $current;

		$is_fullscreen = false;

		$current_user  = wp_get_current_user();
		
		if ( $current_user instanceof \WP_User ) {
			
			$user_prefs_as_string = get_user_meta( $current_user->ID, 'tainacan_prefs', true );
			$user_prefs = json_decode( $user_prefs_as_string, true );

			if ( isset($user_prefs['is_fullscreen']) && ( $user_prefs['is_fullscreen'] == 'true' || $user_prefs['is_fullscreen'] == true ) )
				$is_fullscreen = $user_prefs['is_fullscreen'];

		}	

		ob_start();

		if ( !$this->has_admin_ui_option('forceFullscreenAdminMode') && !$this->has_admin_ui_option('hideFullscreenTogglerButton') ) {
			?>
			<div class="metabox-prefs custom-options-wrap">
				<h5><?php _e('Integration with WordPress Admin UI', 'tainacan'); ?></h5>
				<label for="tainacan-fullscreen-toggler">
					<fieldset>
						<legend class="screen-reader-text"><?php _e('Fullscreen mode options', 'tainacan'); ?></legend>
						<label>
							<input type="radio" name="tainacan-fullscreen-state" value="1" <?php checked($is_fullscreen, true); ?>>
							<?php _e('Hide WordPress navigation', 'tainacan'); ?>
						</label>
						<label>
							<input type="radio" name="tainacan-fullscreen-state" value="0" <?php checked(!$is_fullscreen, true); ?>>
							<?php _e('Show WorPress navigation', 'tainacan'); ?>
						</label>
					</fieldset>
				</label>
				<p class="submit">
					<button type="button" class="button-primary" id="tainacan-fullscreen-toggler">
						<?php _e('Apply', 'tainacan'); ?>
					</button>
				</p>
			</div>
			<?php
		}

		$extra_screen_options_html = ob_get_clean();

		return $current . $extra_screen_options_html;
	}

}

