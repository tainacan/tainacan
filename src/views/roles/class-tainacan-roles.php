<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Roles_Editor extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	protected function get_page_slug() : string {
        return 'tainacan_roles';
    }

    public function add_admin_menu() {
		// Even if the navigation menu is to be hidden, this still is still displayed for admin users in order to fix
		// accidental removal of user roles.
		if ( !$this->has_admin_ui_option('hideNavigationRolesButton') || current_user_can('manage_options') ) {
			$roles_page_suffix = add_submenu_page(
				!$this->has_admin_ui_option('hideNavigationOtherMenu') ? $this->tainacan_other_links_slug : $this->tainacan_root_menu_slug,
				__('User Roles', 'tainacan'),
				'<span class="icon">' . $this->get_svg_icon( 'user' ) . '</span><span class="menu-text">' .__( 'User roles', 'tainacan' ) . '</span>',
				'tnc_rep_edit_users',
				$this->get_page_slug(),
				array( &$this, 'render_page' )
			);
			add_action( 'load-' . $roles_page_suffix, array( &$this, 'load_page' ) );
		}
    }

    function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-roles-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-roles.css', [], TAINACAN_VERSION );
	}

	/**
	 * Returns help tabs for the user roles page.
	 *
	 * @return array
	 */
	protected function get_help_tabs() {
		return array(
			array(
				'id'      => 'tainacan_roles_overview_help_tab',
				'title'   => __( 'User roles', 'tainacan' ),
				'content' => '<p>' . __( 'Use this page to review and adjust the Tainacan capabilities assigned to each WordPress user role.', 'tainacan' ) . '</p>',
			),
			array(
				'id'      => 'tainacan_roles_capabilities_help_tab',
				'title'   => __( 'Capabilities', 'tainacan' ),
				'content' => '<p>' . __( 'Capabilities control who can manage repository structures, collections, items, taxonomies, processes and other administrative resources. Review changes carefully before saving.', 'tainacan' ) . '</p>',
			),
		);
	}

	/**
	 * Returns help sidebar content for the user roles page.
	 *
	 * @return string
	 */
	protected function get_help_sidebar() {
		return
			'<p><strong>' . __( 'For more information:', 'tainacan' ) . '</strong></p>' .
			'<p><a href="' . esc_url( __( 'https://tainacan.github.io/tainacan-wiki/#/permissions', 'tainacan' ) ) . '" target="_blank" rel="noopener noreferrer">' . __( 'Permissions and roles', 'tainacan' ) . '</a></p>';
	}


	function admin_enqueue_js() {
		global $TAINACAN_BASE_URL;

		$this->register_pages_chunk_translations( 'roles' );

		wp_enqueue_script(
			'tainacan-pages-common-scripts',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_pages_common_scripts.js',
			['underscore', 'wp-i18n'],
			TAINACAN_VERSION,
			true
		);
		wp_set_script_translations('tainacan-pages-common-scripts', 'tainacan');

		$settings = $this->get_admin_js_localization_params();
		wp_localize_script( 'tainacan-pages-common-scripts', 'tainacan_user', $this->get_admin_js_user_data() );
		wp_localize_script( 'tainacan-pages-common-scripts', 'tainacan_plugin', $settings );
		wp_enqueue_script( 'underscore' );
		wp_enqueue_script( 'wp-i18n' );

		do_action('tainacan-enqueue-roles-scripts');
	}

	public function render_page_content() {
		require_once('page.php');
	}
}
