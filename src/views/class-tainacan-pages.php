<?php

namespace Tainacan;

/**
 * Pages is an abstract base class for all Tainacan admin pages.
 */
abstract class Pages {

	/**
	 * $tainacan_root_menu_slug is the root menu slug for Tainacan admin pages.
	 * @var string
	 */
	public $tainacan_root_menu_slug = 'tainacan-root-menu';
	
	/**
	 * $tainacan_other_links_slug is menu slug for the "Others" menu collapse
	 * @var string
	 */
	public $other_links_slug = 'tainacan_other_links';
	
	/**
	 * Class construtor, never called directly but used to invoke the init method.
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
		add_action( 'admin_init',array( $this, 'check_if_admin_menu_is_collapsed' ) );
		add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
	}
	
	/**
	 * load_page is called from the 'load-$page_suffix' action and should trigger the load of page's assets.
	 * 
	 * In a child class, when registering a page with add_submenu_page, the $page_suffix is returned and can be 
	 * used to load the page's assets. This garantees that the body class and the assets are only loaded when the
	 * user visits the respective page.
	 *
	 * @return void
	 */
	function load_page() {
		add_filter( 'admin_body_class', array( &$this, 'admin_body_class' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_css' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_js' ), 90 );
	}
	
	/**
	 * add_admin_menu is called from the 'admin_menu' action and should register whiever menu links the page has.
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
		return $classes;
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

		$cur_user  = wp_get_current_user();
		$user_caps = array();
		$prefs     = array();
		$user_data = array();
		if ( $cur_user instanceof \WP_User ) {
			$tainacan_caps = \tainacan_roles()->get_repository_caps_slugs();
			foreach ($tainacan_caps as $tcap) {
				$user_caps[$tcap] = current_user_can( $tcap );
			}
			$prefs = get_user_meta( $cur_user->ID, 'tainacan_prefs', true );

			if ( $cur_user->data && isset($cur_user->data->user_email) && isset($cur_user->data->display_name) ) {
				$user_data = array(
					'ID' => $cur_user->ID,
					'email' => $cur_user->data->user_email,
					'display_name' => $cur_user->data->display_name
				);
			}
		}

		$settings = [
			'tainacan_api_url'         	=> esc_url_raw( rest_url() ) . 'tainacan/v2',
			'wp_api_url'            	=> esc_url_raw( rest_url() ) . 'wp/v2/',
			'wp_ajax_url'            	=> admin_url( 'admin-ajax.php' ),
			'nonce'                  	=> is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : false,
			'classes'                	=> array(),
			'i18n'                   	=> $tainacan_admin_i18n,
			'user_caps'              	=> $user_caps,
			'user_prefs'             	=> $prefs,
			'user_data'					=> $user_data,
			'base_url'               	=> $TAINACAN_BASE_URL,
			'plugin_dir_url'			=> plugin_dir_url( __DIR__ ),
			'admin_url'              	=> admin_url('admin.php'),
			'theme_items_list_url' 		=> esc_url_raw( get_site_url() ) . '/' . \Tainacan\Theme_Helper::get_instance()->get_items_list_slug(),
			'theme_collection_list_url' => get_post_type_archive_link( 'tainacan-collection' ),
			'theme_taxonomy_list_url' 	=> get_post_type_archive_link( 'tainacan-taxonomy' ),
			'custom_header_support'  	=> get_theme_support('custom-header'),
			'registered_view_modes'  	=> \Tainacan\Theme_Helper::get_instance()->get_registered_view_modes(),
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
		if (isset($wp_post_types['attachment'])) {
			unset($wp_post_types['attachment']);
		}

		$wp_post_types = array_map(function($i) {
			return [
				'slug' => $i->name,
				'label' => $i->label
			];
		}, $wp_post_types);

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
		wp_insert_term( 'e', 'category', array( 'alias_of' => 'a' ) );
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
	 * Tipically this function is to be passed as a callback to the add_submenu_page function, in each child class.
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
		
		?>
		<div id="tainacan-page-container">
			<?php 
				$this->render_navigation_menu();
				$this->render_page_content();
			?>
		</div>
		<?php
	}
	
	/**
	 * render_navigation_menu creates the aside navigation menu for the Tainacan admin pages.
	 * 
	 * Internally, it loops through the submenu global variable to render the menu items. All submenu registered with
	 * the $tainacan_root_menu_slug are considered root links and are rendered as strong tags. They may or may not contain
	 * links and submenu items of their slug are rendered as ul/li tags.
	 *
	 * @return void
	 */
	public function render_navigation_menu() {
		global $TAINACAN_BASE_URL;
		global $submenu;

		wp_enqueue_script(
			'tainacan-admin-navigation-menu',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_admin_navigation_menu.js',
			[ 'wp-hooks', 'wp-i18n' ],
			TAINACAN_VERSION
		);
		$current_screen = get_current_screen();
		$current_page_slug = $current_screen->id ? $current_screen->id : 'toplevel_page_tainacan_dashboard';
		$current_page_parent = $current_screen->parent_file;
		
		?>
		<aside id="tainacan-navigation-menu">
			<nav>
				<header>
					<h1>
						<a href="admin.php?page=tainacan_dashboard">
							<img 
								alt="<?php _e('Tainacan', 'tainacan'); ?>" 
								width="140" 
								src="<?php echo plugin_dir_url( __DIR__ ) . '/assets/images/tainacan_logo_header.svg'; ?>" />
						</a>
					</h1>
				</header>
				<ul id="tainacan-root-menu">
					<li>
						<a href="admin.php?page=tainacan_dashboard" <?php echo $current_page_slug === 'toplevel_page_tainacan_dashboard' ? 'aria-current="page"' : ''; ?>>
							<?php _e('Home', 'tainacan'); ?>
						</a>
					</li>
					<?php
						$tainacan_root_links = isset( $submenu[$this->tainacan_root_menu_slug] ) ? $submenu[$this->tainacan_root_menu_slug] : [];
						
						if ( count($tainacan_root_links) ) {
							foreach( $tainacan_root_links as $tainacan_root_link ) {
								if (  !current_user_can( $tainacan_root_link[1] ) ) 
									continue;
								
								if ( isset( $submenu[$tainacan_root_link[2]] ) ) : ?>

									<li class="menu-item-has-children <?php echo $current_page_parent === $tainacan_root_link[2] ? 'is-open' : ''; ?>">
										<button type="button" aria-expanded="<?php echo $current_page_parent === $tainacan_root_link[2] ?>"><strong><?php echo $tainacan_root_link[0]; ?></strong></button>
										
										<?php if ( count( $submenu[$tainacan_root_link[2]] ) ) : ?>
											<ul id="<?php echo $tainacan_root_link[2]; ?>">
												<?php foreach( $submenu[$tainacan_root_link[2]] as $link ) : 
													if ( !current_user_can( $link[1] ) ) continue;
												?>
													<li>
														<a href="admin.php?page=<?php echo $link[2]; ?>" <?php echo $current_page_slug === 'admin_page_' . $link[2] ? 'aria-current="page"' : ''; ?>><?php echo $link[0]; ?></a>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</li>

								<?php else : ?>
									<li>
										<a href="<?php echo $tainacan_root_link[1]; ?>"  <?php echo $current_page_slug === 'admin_page_' . $tainacan_root_link[1] ? 'aria-current="page"' : ''; ?>><strong><?php echo $tainacan_root_link[0]; ?></strong></a>
									</li>
								<?php endif; 
							}
						}
					?>
				</ul>
			</nav>
		</aside>
		<?php
	}
    
    /**
     * check_if_admin_menu_is_collapsed is a routine to automatically collapse WordPress admin sidemenu when a
	 * Tainacan page is entered. It stores the current state of the menu and restores it when the user leaves the
	 * Tainacan context using the 'tainacan-set-mfold' setting.
     *
     * @return void
     */
    public function check_if_admin_menu_is_collapsed() {
        if ( str_contains( $_SERVER['REQUEST_URI'], 'page=tainacan' ) ) {
			$menu_folding_option = get_user_setting( 'mfold', 'o' );
			if ( 'o' === $menu_folding_option ) {
				set_user_setting( 'mfold', 'f');
				set_user_setting( 'tainacan-set-mfold', 'yes' );
			} else {
				set_user_setting( 'tainacan-set-mfold', 'no' );
			}
        } else {
			$menu_folding_option = get_user_setting( 'mfold', 'o' );
			if ( 'f' === $menu_folding_option && 'yes' === get_user_setting( 'tainacan-set-mfold' ) )
				set_user_setting( 'mfold', 'o');
		}
    }

}

