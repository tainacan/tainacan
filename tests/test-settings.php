<?php

namespace Tainacan\Tests;

class Settings extends TAINACAN_UnitTestCase {
	public function test_rich_text_editor_setting_is_disabled_by_default_and_can_be_enabled() {
		delete_option( 'tainacan_option_allow_rich_text_editor' );

		try {
			$settings = \Tainacan\Settings::get_instance();
			$this->assertFalse( $settings->get_admin_js_localization_params()['tainacan_allow_rich_text_editor'] );

			update_option( 'tainacan_option_allow_rich_text_editor', true );
			$this->assertTrue( $settings->get_admin_js_localization_params()['tainacan_allow_rich_text_editor'] );
		} finally {
			delete_option( 'tainacan_option_allow_rich_text_editor' );
		}
	}

	public function test_rich_text_editor_setting_is_registered_in_search_and_performance() {
		global $wp_settings_fields;

		\Tainacan\Settings::get_instance()->settings_init();
		$field = $wp_settings_fields['tainacan_settings']['tainacan_settings_search_and_performance']['tainacan_option_allow_rich_text_editor'];

		$this->assertSame( 'Rich text editor', $field['title'] );
		$this->assertSame( 'Allows the rich text editor in supported Tainacan text inputs. You can then enable it individually for Textarea and Core Description metadata.', $field['args']['description'] );
		$this->assertFalse( $field['args']['input_disabled'] );
		$this->assertFalse( $field['args']['default'] );
		$this->assertNull( $field['args']['forced_value'] );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_rich_text_editor_constant_overrides_the_administrator_setting() {
		define( 'TAINACAN_ALLOW_RICH_TEXT_EDITOR', false );

		try {
			update_option( 'tainacan_option_allow_rich_text_editor', true );

			$settings = \Tainacan\Settings::get_instance();
			$settings->settings_init();
			$field = $GLOBALS['wp_settings_fields']['tainacan_settings']['tainacan_settings_search_and_performance']['tainacan_option_allow_rich_text_editor'];

			$this->assertFalse( $settings->get_admin_js_localization_params()['tainacan_allow_rich_text_editor'] );
			$this->assertTrue( $field['args']['input_disabled'] );
			$this->assertFalse( $field['args']['forced_value'] );
		} finally {
			delete_option( 'tainacan_option_allow_rich_text_editor' );
		}
	}
}
