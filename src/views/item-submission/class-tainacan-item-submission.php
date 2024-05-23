<?php

namespace Tainacan;

class Item_Submission {

	private $other_links_slug = 'tainacan_other_links';

	public function __construct() {
        add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'settings_init' ) );
	}

	public function add_admin_menu() {
		add_submenu_page(
			$this->other_links_slug,
			__('Item Submission', 'tainacan'),
			__('Item Submission', 'tainacan'),
			'manage_options',
			'tainacan_item_submission',
			array( &$this, 'item_submission_page' )
		);
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

	public function item_submission_page() {
		\Tainacan\Views::get_instance()->the_admin_navigation_menu();
		$this->admin_page();
	}

	public function admin_page() {
		include('admin-page.php');
	}

}