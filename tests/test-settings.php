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

	public function test_input_fields_section_groups_editor_and_datepicker_controls() {
		global $wp_settings_fields, $wp_settings_sections;

		\Tainacan\Settings::get_instance()->settings_init();
		$sections = $wp_settings_sections['tainacan_settings'];
		$this->assertArrayHasKey( 'tainacan_settings_input_fields', $sections );
		$this->assertArrayNotHasKey( 'tainacan_settings_text_editing', $sections );
		$this->assertArrayNotHasKey( 'tainacan_settings_datepickers', $sections );
		$this->assertSame( 'Input fields', $sections['tainacan_settings_input_fields']['title'] );
		$this->assertSame( 'input_fields_section_description', $sections['tainacan_settings_input_fields']['callback'][1] );

		$fields = $wp_settings_fields['tainacan_settings']['tainacan_settings_input_fields'];
		$this->assertSame( array(
			'tainacan_option_allow_rich_text_editor',
			'tainacan_option_datepickers_year_min',
			'tainacan_option_datepickers_year_max',
		), array_keys( $fields ) );
		$this->assertSame( 'Datepicker years before', $fields['tainacan_option_datepickers_year_min']['title'] );
		$this->assertSame( 'Datepicker years after', $fields['tainacan_option_datepickers_year_max']['title'] );

		$field = $fields['tainacan_option_allow_rich_text_editor'];

		$this->assertSame( 'Rich text editor', $field['title'] );
		$this->assertSame( 'Allows the rich text editor in supported Tainacan text inputs. You can then enable it individually for Textarea and Core Description metadata. HTML saved in these fields is included in textual search, so markup can split phrases and change which results match.', $field['args']['description'] );
		$this->assertFalse( $field['args']['input_disabled'] );
		$this->assertFalse( $field['args']['default'] );
		$this->assertNull( $field['args']['forced_value'] );
	}

	public function test_input_fields_section_renders_together_with_existing_saved_values() {
		\Tainacan\Settings::get_instance()->settings_init();
		update_option( 'tainacan_option_allow_rich_text_editor', true );
		update_option( 'tainacan_option_datepickers_year_min', -500 );
		update_option( 'tainacan_option_datepickers_year_max', 100 );

		ob_start();
		do_settings_sections( 'tainacan_settings' );
		$html = ob_get_clean();

		preg_match_all( '/<h2\b[^>]*>(.*?)<\/h2>/s', $html, $headings );
		$this->assertSame( 1, count( array_keys( $headings[1], 'Input fields', true ) ) );
		$this->assertNotContains( 'Text editing', $headings[1] );
		$this->assertNotContains( 'Date selectors', $headings[1] );
		preg_match( '/<h2\b[^>]*>Input fields<\/h2>(.*?)(?=<h2\b|$)/s', $html, $section );
		$input_fields_html = $section[1];
		$this->assertStringContainsString( 'rich text editor', $input_fields_html );
		$this->assertStringContainsString( 'datepickers', $input_fields_html );
		$this->assertStringContainsString( 'Datepicker years before', $input_fields_html );
		$this->assertStringContainsString( 'Datepicker years after', $input_fields_html );
		$this->assertMatchesRegularExpression( '/id="tainacan_option_allow_rich_text_editor"[^>]*checked/', $input_fields_html );
		$this->assertMatchesRegularExpression( '/id="tainacan_option_datepickers_year_min"[^>]*value="-500"/', $input_fields_html );
		$this->assertMatchesRegularExpression( '/id="tainacan_option_datepickers_year_max"[^>]*value="100"/', $input_fields_html );
	}
}
