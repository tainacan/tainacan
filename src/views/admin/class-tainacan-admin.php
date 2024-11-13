<?php

namespace Tainacan;

class Admin extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	private $vue_component_page_slug = 'tainacan_admin';
	private $repository_links_slug = 'tainacan_admin'; // Same as vue_component_page_slug, because it is used in add_submenu_page() to create the root menu and the page has to exist
	private $collections_links_slug = 'tainacan_collection_links';

	public function init() {
		parent::init();
		add_action( 'wp_ajax_tainacan-sample-permalink', array( &$this, 'ajax_sample_permalink') );
	}

	function add_admin_menu() {

		// Tainacan Admin Vue component. 
		$tainacan_page_suffix = add_submenu_page(
			$this->tainacan_root_menu_slug,
			__( 'Repository', 'tainacan' ),
			'<span class="icon">' . $this->get_svg_icon( 'repository', 'var(--tainacan-blue4)', 20 ) . '</span><span class="menu-text">' .__( 'Repository', 'tainacan' ) . '</span>',
			'read',
			$this->repository_links_slug,
			array( &$this, 'render_page' ),
		);
		add_action( 'load-' . $tainacan_page_suffix, array( &$this, 'load_page' ) );
		
		// Inner links to the admin vue component
		add_submenu_page(
			$this->repository_links_slug,
			__('Metadata', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'metadata', 'var(--tainacan-blue4)', 18 ) . '</span><span class="menu-text">' . __('Metadata', 'tainacan') . '</span>',
			'read',
			$this->vue_component_page_slug . '#/metadata',
			array( &$this, 'render_page' )
		);
		add_submenu_page(
			$this->repository_links_slug,
			__('Filters', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'filters', 'var(--tainacan-blue4)', 18 ) . '</span><span class="menu-text">' . __('Filters', 'tainacan') . '</span>',
			'read',
			$this->vue_component_page_slug . '#/filters',
			array( &$this, 'render_page' )
		);
		add_submenu_page(
			$this->repository_links_slug,
			__('Taxonomies', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'taxonomies', 'var(--tainacan-blue4)', 18 ) . '</span><span class="menu-text">' . __('Taxonomies', 'tainacan') . '</span>',
			'read',
			$this->vue_component_page_slug . '#/taxonomies',
			array( &$this, 'render_page' )
		);
		add_submenu_page(
			$this->repository_links_slug,
			__('Activities', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'activities', 'var(--tainacan-blue4)', 18 ) . '</span><span class="menu-text">' . __('Activities', 'tainacan') . '</span>',
			'read',
			$this->vue_component_page_slug . '#/activities',
			array( &$this, 'render_page' )
		);
		add_submenu_page(
			$this->repository_links_slug,
			__('Capabilities', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'capability', 'var(--tainacan-blue4)', 18 ) . '</span><span class="menu-text">' . __('Capabilities', 'tainacan') . '</span>',
			'read',
			$this->vue_component_page_slug . '#/capabilities',
			array( &$this, 'render_page' )
		);
		add_submenu_page(
			$this->repository_links_slug,
			__('Importers', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'importers', 'var(--tainacan-blue4)', 18 ) . '</span><span class="menu-text">' . __('Importers', 'tainacan') . '</span>',
			'read',
			$this->vue_component_page_slug . '#/importers',
			array( &$this, 'render_page' )
		);
		add_submenu_page(
			$this->repository_links_slug,
			__('Exporters', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'export', 'var(--tainacan-blue4)', 18 ) . '</span><span class="menu-text">' . __('Exporters', 'tainacan') . '</span>',
			'read',
			$this->vue_component_page_slug . '#/exporters',
			array( &$this, 'render_page' )
		);
		add_submenu_page(
			$this->tainacan_root_menu_slug,
			__('Collections', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'collections', 'var(--tainacan-turquoise4)', 20 ) . '</span><span class="menu-text">' . __('Collections', 'tainacan') . '</span>',
			'read',
			$this->collections_links_slug,
			array( &$this, 'render_page' )
		);
		add_submenu_page(
			$this->collections_links_slug,
			__('Collections list', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'collection', 'var(--tainacan-turquoise4)', 18 ) . '</span><span class="menu-text">' . __('Collections list', 'tainacan') . '</span>',
			'read',
			$this->vue_component_page_slug . '#/collections',
			array( &$this, 'render_page' )
		);
		add_submenu_page(
			$this->collections_links_slug,
			__('Items', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'items', 'var(--tainacan-turquoise4)', 18 ) . '</span><span class="menu-text">' . __('Items', 'tainacan') . '</span>',
			'read',
			'tainacan_admin#/items',
			array( &$this, 'render_page' )
		);
	}
	
	function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-fonts', $TAINACAN_BASE_URL . '/assets/css/tainacanicons.css', [], TAINACAN_VERSION );
		wp_enqueue_script('underscore');
		wp_enqueue_style( 'tainacan-admin-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-admin.css', [], TAINACAN_VERSION );

		// $undesired_wp_styles = [
		// 	'admin-menu',
		// 	'admin-bar',
		// 	'code-editor',
		// 	'color-picker',
		// 	'customize-controls',
		// 	'customize-nav-menus',
		// 	'customize-widgets',
		// 	'dashboard',
		// 	'dashicons',
		// 	'deprecated-media',
		// 	'edit',
		// 	'wp-pointer',
		// 	'farbtastic',
		// 	'forms',
		// 	'common',
		// 	'install',
		// 	'wp-auth-check',
		// 	'site-icon',
		// 	'buttons',
		// 	'l10n',
		// 	'list-tables',
		// 	'login',
		// 	'media',
		// 	'nav-menus',
		// 	'revisions',
		// 	'themes',
		// 	'widgets',
		// 	'wp-admin'
		// ];

		// wp_dequeue_style( $undesired_wp_styles );
		// wp_deregister_style( $undesired_wp_styles );

	}

	function admin_enqueue_js() {
		global $TAINACAN_BASE_URL;
		global $TAINACAN_EXTRA_SCRIPTS;

		$deps = ['underscore', 'media-editor', 'media-views', 'customize-controls', 'wp-i18n', 'wp-hooks'];
		if ( !empty($TAINACAN_EXTRA_SCRIPTS) ) {
			foreach($TAINACAN_EXTRA_SCRIPTS as $dep) {
				$deps[] = $dep;
			}
		}

		wp_enqueue_script(
			'tainacan-pages-common-scripts',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_pages_common_scripts.js',
			$deps,
			TAINACAN_VERSION
		);
		$settings = $this->get_admin_js_localization_params();

		wp_localize_script( 'tainacan-pages-common-scripts', 'tainacan_plugin', $settings );
		wp_enqueue_media(
			 //[ 'post' => 131528 ]
		);
		wp_enqueue_script('underscore');
		wp_enqueue_script('jcrop');
		wp_enqueue_script( 'customize-controls' );

		do_action('tainacan-enqueue-admin-scripts');

	}

	function admin_body_class( $classes ) {

		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->vue_component_page_slug )
			$classes .= ' tainacan-pages-container tainacan-admin-page';
		
		return $classes;
	}

	public function render_page_content() {
		include('page.php');
	}

	/**
	 * Ajax request used in admin to create slug for entities based on the title
	 */
	function ajax_sample_permalink(){

		$id = sanitize_text_field($_POST['post_id']);
		$title = sanitize_text_field($_POST['new_title']);
		$name = sanitize_text_field($_POST['new_slug']);

		$post = get_post( $id );
		if ( ! $post )
			return array( '', '' );

		$ptype = get_post_type_object($post->post_type);

		// Hack: get_permalink() would return ugly permalink for drafts, so we will fake that our post is published.
		if ( in_array( $post->post_status, array( 'auto-draft', 'draft', 'pending', 'future' ) ) ) {
			$post->post_status = 'publish';
			$post->post_name = sanitize_title($post->post_name ? $post->post_name : $post->post_title, $post->ID);
		}

		// If the user wants to set a new name -- override the current one
		// Note: if empty name is supplied -- use the title instead, see #6072
		if ( !is_null($name) )
			$post->post_name = sanitize_title($name ? $name : $title, $post->ID);

		$post->post_name = wp_unique_post_slug($post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent);
		if ( $post->post_type === \Tainacan\Entities\Taxonomy::$post_type ) {
			$post_name = $post->post_name;
			$tax = \get_taxonomies(array('name' => $post_name));
			$suffix = 2;
			while ( !empty($tax) ) {
				$post_name = _truncate_post_slug( $post_name, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				$tax = \get_taxonomies(array('name' => $post_name));
				$suffix++;
			};
			$post->post_name = $post_name;
		}

		$post->filter = 'sample';

		$permalink = get_permalink($post, true);

		// Replace custom post_type Token with generic pagename token for ease of use.
		$permalink = str_replace("%$post->post_type%", '%pagename%', $permalink);

		// Handle page hierarchy
		if ( $ptype->hierarchical ) {
			$uri = get_page_uri($post);
			if ( $uri ) {
				$uri = untrailingslashit($uri);
				$uri = strrev( stristr( strrev( $uri ), '/' ) );
				$uri = untrailingslashit($uri);
			}

			/** This filter is documented in wp-admin/edit-tag-form.php */
			$uri = apply_filters( 'editable_slug', $uri, $post );
			if ( !empty($uri) )
				$uri .= '/';
			$permalink = str_replace('%pagename%', "{$uri}%pagename%", $permalink);
		}

		/** This filter is documented in wp-admin/edit-tag-form.php */
		$permalink = array( 'permalink' => $permalink, 'slug' => apply_filters( 'editable_slug', $post->post_name, $post ) );

		echo json_encode($permalink);

		wp_die();
	}
}

