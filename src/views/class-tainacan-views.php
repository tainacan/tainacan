<?php

namespace Tainacan;

class Views {
	use \Tainacan\Traits\Singleton_Instance;

	public $tainacan_root_menu_slug = 'tainacan-root-menu';
	private $other_links_slug = 'tainacan_other_links';

	private function init() {

		add_action( 'init', array( &$this, 'register_user_meta' ) );

		add_action( 'admin_init',array( $this, 'check_if_admin_menu_is_collapsed' ) );

		require_once('dashboard/class-tainacan-dashboard.php');
		new \Tainacan\Dashboard();

		require_once('admin/class-tainacan-admin.php');
		new \Tainacan\Admin();

		require_once('system-check/class-tainacan-system-check.php');
		new \Tainacan\System_Check();

		require_once('roles/class-tainacan-roles.php');
		new \Tainacan\Roles_Editor();

		require_once('reports/class-tainacan-reports.php');
		new \Tainacan\Reports();

		require_once('item-submission/class-tainacan-item-submission.php');
		new \Tainacan\Item_Submission();

		add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );

		add_action( 'after_setup_theme', array( &$this, 'load_theme_files') );
	}


	function load_theme_files() {
		add_action( 'wp_enqueue_scripts', array(&$this, 'add_theme_files') );
	}

	function add_theme_files() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-fonts', $TAINACAN_BASE_URL . '/assets/css/tainacanicons.css', [], TAINACAN_VERSION );
		wp_enqueue_script('underscore');
	}

	function add_admin_menu() {

		add_submenu_page(
			$this->tainacan_root_menu_slug,
			__('Other', 'tainacan'),
			__('Other', 'tainacan'),
			'read',
			$this->other_links_slug,
			'#'
		);
	}

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
			'admin_url'              	=> admin_url(),
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
			'tainacan_enable_core_metadata_on_advanced_search' => ( !defined('TAINACAN_DISABLE_CORE_METADATA_ON_ADVANCED_SEARCH') || false === TAINACAN_DISABLE_CORE_METADATA_ON_ADVANCED_SEARCH ),
			'tainacan_enable_relationship_metaquery' => ( defined('TAINACAN_ENABLE_RELATIONSHIP_METAQUERY') && true === TAINACAN_ENABLE_RELATIONSHIP_METAQUERY )
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

	public function the_admin_navigation_menu() {
		global $submenu;
		?>
		<ul>
			<li><a href="admin.php?page=tainacan_dashboard"><?php _e('Home', 'tainacan'); ?></a></li>
			<?php
				$tainacan_root_links = isset( $submenu[$this->tainacan_root_menu_slug] ) ? $submenu[$this->tainacan_root_menu_slug] : [];
				
				if ( count($tainacan_root_links) ) {
					foreach( $tainacan_root_links as $tainacan_root_link ) {
						?>
						<li>
							<strong>
								<?php if ( isset( $submenu[$tainacan_root_link[2]] ) ) : ?>
									<a onclick="console.log('implement open...')"><?php echo $tainacan_root_link[0]; ?></a>
								<?php else : ?>
									<a href="<?php echo $tainacan_root_link[1]; ?>"><?php echo $tainacan_root_link[0]; ?></a>
								<?php endif; ?>
							</strong>
						</li>
						
						<?php if ( isset( $submenu[$tainacan_root_link[2]] ) ) : ?>
							<ul>
							<?php foreach( $submenu[$tainacan_root_link[2]] as $link ) : ?>
								<li><a href="admin.php?page=<?php echo $link[2]; ?>"><?php echo $link[0]; ?></a></li>
							<?php endforeach; ?>
							</ul>
						<?php endif;
					}
				}
			?>
		</ul>
		<?php
	}

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

