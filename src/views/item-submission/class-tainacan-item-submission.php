<?php

namespace Tainacan;

class Item_Submission {

	public function __construct()
	{
		$this->page_init();
	}

	public function page_init()
	{
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
	}

	public function print_section_info()
	{
		print _e('Enter your site settings below:', 'tainacan');
	}

	public function tnc_option_recaptch_site_key()
	{
		printf(
			'<input type="text" id="tnc_option_recaptch_site_key" name="tnc_option_recaptch_site_key" value="%s" />',
			esc_attr( get_option('tnc_option_recaptch_site_key') )
		);
	}

	public function tnc_option_recaptch_secret_key()
	{
		printf(
			'<input type="text" id="tnc_option_recaptch_secret_key" name="tnc_option_recaptch_secret_key" value="%s" />',
			esc_attr( get_option('tnc_option_recaptch_secret_key') )
		);
	}

	public function admin_page()
	{
		include('admin-page.php');
	}

}