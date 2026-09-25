<?php

namespace Tainacan\Tests;

/**
 * Tests the datepickers years range settings.
 */
class SettingsDatepickers extends TAINACAN_UnitTestCase {

	/**
	 * Years before must be zero or negative, falling back to the default when not numeric.
	 */
	public function test_datepickers_year_min_sanitization() {
		$settings = \Tainacan\Settings::get_instance();

		$this->assertSame( -500, $settings->sanitize_datepickers_year_min( '-500' ) );
		$this->assertSame( 0, $settings->sanitize_datepickers_year_min( '0' ) );
		$this->assertSame( 0, $settings->sanitize_datepickers_year_min( '30' ) );
		$this->assertSame( \Tainacan\Settings::DATEPICKERS_YEAR_MIN_DEFAULT, $settings->sanitize_datepickers_year_min( 'abc' ) );
		$this->assertSame( \Tainacan\Settings::DATEPICKERS_YEAR_MIN_DEFAULT, $settings->sanitize_datepickers_year_min( '' ) );
	}

	/**
	 * Years after must be zero or positive, falling back to the default when not numeric.
	 */
	public function test_datepickers_year_max_sanitization() {
		$settings = \Tainacan\Settings::get_instance();

		$this->assertSame( 100, $settings->sanitize_datepickers_year_max( '100' ) );
		$this->assertSame( 0, $settings->sanitize_datepickers_year_max( '0' ) );
		$this->assertSame( 0, $settings->sanitize_datepickers_year_max( '-30' ) );
		$this->assertSame( \Tainacan\Settings::DATEPICKERS_YEAR_MAX_DEFAULT, $settings->sanitize_datepickers_year_max( 'abc' ) );
		$this->assertSame( \Tainacan\Settings::DATEPICKERS_YEAR_MAX_DEFAULT, $settings->sanitize_datepickers_year_max( '' ) );
	}

	/**
	 * The saved years range must reach the tainacan_plugin JS object as integers.
	 */
	public function test_datepickers_years_range_is_passed_to_js() {
		$admin = \Tainacan\Admin::get_instance();

		try {
			delete_option( 'tainacan_option_datepickers_year_min' );
			delete_option( 'tainacan_option_datepickers_year_max' );

			$params = $admin->get_admin_js_localization_params();
			$this->assertSame( \Tainacan\Settings::DATEPICKERS_YEAR_MIN_DEFAULT, $params['datepickers_year_min'] );
			$this->assertSame( \Tainacan\Settings::DATEPICKERS_YEAR_MAX_DEFAULT, $params['datepickers_year_max'] );

			update_option( 'tainacan_option_datepickers_year_min', -1000 );
			update_option( 'tainacan_option_datepickers_year_max', 10 );

			$params = $admin->get_admin_js_localization_params();
			$this->assertSame( -1000, $params['datepickers_year_min'] );
			$this->assertSame( 10, $params['datepickers_year_max'] );
		} finally {
			delete_option( 'tainacan_option_datepickers_year_min' );
			delete_option( 'tainacan_option_datepickers_year_max' );
		}
	}
}
