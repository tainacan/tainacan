<?php

namespace Tainacan;

class Item_Submission extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	public function init() {
		parent::init();
		add_action( 'admin_init', array( &$this, 'settings_init' ) );
	}

	public function add_admin_menu() {
		
		add_submenu_page(
			$this->tainacan_root_menu_slug,
			__('Other', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'viewminiature' ) . '</span><span class="menu-text">' .__( 'Other', 'tainacan' ) . '</span>',
			'read',
			$this->tainacan_other_links_slug,
			'#'
		);

		$tainacan_page_suffix = add_submenu_page(
			$this->tainacan_other_links_slug,
			__('Item Submission', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'upload' ) . '</span><span class="menu-text">' .__( 'Item submission', 'tainacan' ) . '</span>',
			'manage_options',
			'tainacan_item_submission',
			array( &$this, 'render_page' )
		);
		add_action( 'load-' . $tainacan_page_suffix, array( &$this, 'load_page' ) );
	}

	public function render_page_content() {
		require_once('page.php');
	}

	public function settings_init() {
		add_settings_section(
			'tainacan_item_submission_recaptcha_id', // ID
			'reCaptcha',                             // Title
			array( $this, 'print_section_info' ),    // Callback
			'tainacan_item_submission'               // Page
		);

		add_settings_field(
			'tnc_option_recaptch_site_key',                 // ID
			'Site Key',                                     // Title
			array( $this, 'tnc_option_recaptch_site_key' ), // Callback
			'tainacan_item_submission',                     // Page
			'tainacan_item_submission_recaptcha_id'         // Section
		);

		add_settings_field(
			'tnc_option_recaptch_secret_key',                 // ID
			'Secret Key',                                     // Title
			array( $this, 'tnc_option_recaptch_secret_key' ), // Callback
			'tainacan_item_submission',                       // Page
			'tainacan_item_submission_recaptcha_id'           // Section
		);

		register_setting(
			'tainacan_item_submission_recaptcha',
			'tnc_option_recaptch_site_key',
			'sanitize_text_field'
		);
	
		register_setting(
			'tainacan_item_submission_recaptcha',
			'tnc_option_recaptch_secret_key',
			'sanitize_text_field'
		);
	}

	public function print_section_info() {
		print _e('Enter your site settings below:', 'tainacan');
	}

	public function tnc_option_recaptch_site_key() {
		printf(
			'<input type="text" id="tnc_option_recaptch_site_key" name="tnc_option_recaptch_site_key" value="%s" />',
			esc_attr( get_option('tnc_option_recaptch_site_key') )
		);
	}

	public function tnc_option_recaptch_secret_key() {
		printf(
			'<input type="text" id="tnc_option_recaptch_secret_key" name="tnc_option_recaptch_secret_key" value="%s" />',
			esc_attr( get_option('tnc_option_recaptch_secret_key') )
		);
	}

}