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

	public function test_rich_text_editor_setting_is_registered_in_text_editing() {
		global $wp_settings_fields;

		\Tainacan\Settings::get_instance()->settings_init();
		$field = $wp_settings_fields['tainacan_settings']['tainacan_settings_text_editing']['tainacan_option_allow_rich_text_editor'];

		$this->assertSame( 'Rich text editor', $field['title'] );
		$this->assertSame( 'Allows the rich text editor in supported Tainacan text inputs. You can then enable it individually for Textarea and Core Description metadata. HTML saved in these fields is included in textual search, so markup can split phrases and change which results match.', $field['args']['description'] );
		$this->assertFalse( $field['args']['input_disabled'] );
		$this->assertFalse( $field['args']['default'] );
		$this->assertNull( $field['args']['forced_value'] );
	}
}
