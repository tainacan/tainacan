<?php

namespace Tainacan\Tests;

/**
 * @group theme-support
 */
class Test_Theme_Support extends TAINACAN_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		$this->reset_state();
	}

	public function tearDown(): void {
		$this->reset_state();
		parent::tearDown();
	}

	private function reset_state() {
		remove_theme_support( 'tainacan' );
		remove_all_filters( 'tainacan-theme-support' );
		delete_option( 'tainacan_option_override_collection_items_archive_template' );
		\Tainacan\Theme_Support::get_instance()->flush_cache();
	}

	public function test_default_test_theme_is_not_supported() {
		$this->assertFalse( \Tainacan\Theme_Support::get_instance()->is_supported() );
	}

	public function test_theme_support_declaration_is_detected() {
		$theme_support = \Tainacan\Theme_Support::get_instance();
		$this->assertFalse( $theme_support->is_supported() );

		add_theme_support( 'tainacan' );
		$theme_support->flush_cache();
		$this->assertTrue( $theme_support->is_supported() );

		remove_theme_support( 'tainacan' );
		$theme_support->flush_cache();
		$this->assertFalse( $theme_support->is_supported() );
	}

	public function test_result_is_cached_until_flushed() {
		$theme_support = \Tainacan\Theme_Support::get_instance();
		$this->assertFalse( $theme_support->is_supported() );

		add_theme_support( 'tainacan' );
		$this->assertFalse( $theme_support->is_supported(), 'Cached value should be returned until the cache is flushed' );

		$theme_support->flush_cache();
		$this->assertTrue( $theme_support->is_supported() );
	}

	public function test_filter_overrides_detection() {
		$theme_support = \Tainacan\Theme_Support::get_instance();
		$this->assertFalse( $theme_support->is_supported() );

		add_filter( 'tainacan-theme-support', '__return_true' );
		$this->assertTrue( $theme_support->is_supported() );
	}

	public function test_collection_archive_override_option_counts_as_support() {
		$theme_support = \Tainacan\Theme_Support::get_instance();
		$this->assertFalse( $theme_support->is_supported() );

		update_option( 'tainacan_option_override_collection_items_archive_template', true );
		// Saving the option fires the hook that flushes the cache.
		$this->assertTrue( $theme_support->is_supported() );

		delete_option( 'tainacan_option_override_collection_items_archive_template' );
		$this->assertFalse( $theme_support->is_supported() );
	}

	public function test_notice_is_hidden_for_subscriber() {
		$subscriber = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		$this->assertFalse( \Tainacan\Dashboard_Theme_Notice::get_instance()->should_display() );
	}

	public function test_notice_is_displayed_for_admin_without_theme_support() {
		$notice = \Tainacan\Dashboard_Theme_Notice::get_instance();
		$this->assertTrue( $notice->should_display() );

		add_theme_support( 'tainacan' );
		\Tainacan\Theme_Support::get_instance()->flush_cache();
		$this->assertFalse( $notice->should_display() );
	}

	public function test_notice_is_hidden_after_dismiss_for_current_stylesheet() {
		$notice = \Tainacan\Dashboard_Theme_Notice::get_instance();
		$this->assertTrue( $notice->should_display() );

		$notice->dismiss();
		$this->assertSame( get_stylesheet(), get_user_meta( get_current_user_id(), \Tainacan\Dashboard_Theme_Notice::USER_META_KEY, true ) );
		$this->assertFalse( $notice->should_display() );

		// Dismissal is bound to the stylesheet: another theme shows the notice again.
		update_user_meta( get_current_user_id(), \Tainacan\Dashboard_Theme_Notice::USER_META_KEY, 'some-other-theme' );
		$this->assertTrue( $notice->should_display() );
	}

	public function test_notice_markup_is_rendered_with_actions() {
		$notice = \Tainacan\Dashboard_Theme_Notice::get_instance();

		ob_start();
		$notice->render();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'tainacan-theme-support-notice', $html );
		$this->assertStringContainsString( 'Your theme does not include Tainacan templates', $html );
		$this->assertStringContainsString( 'tainacan_settings', $html );
		$this->assertStringContainsString( 'tainacan-theme-support-notice__dismiss', $html );

		$notice->dismiss();
		ob_start();
		$notice->render();
		$this->assertSame( '', ob_get_clean() );
	}

	public function test_user_can_act_is_false_for_subscriber_and_true_for_admin() {
		$notice = \Tainacan\Dashboard_Theme_Notice::get_instance();
		$this->assertTrue( $notice->user_can_act() );

		$subscriber = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );
		$this->assertFalse( $notice->user_can_act() );
	}

	public function test_missing_support_actions_for_admin() {
		$theme_support = \Tainacan\Theme_Support::get_instance();
		$actions = $theme_support->get_missing_support_actions();

		$has_install = in_array( 'install_theme', $actions, true );
		$has_activate = in_array( 'activate_theme', $actions, true );
		$this->assertTrue( $has_install xor $has_activate, 'Exactly one of install_theme/activate_theme must be offered' );
		$this->assertSame( $has_activate, $theme_support->is_tainacan_interface_installed() );
		$this->assertNotContains( 'install_blocksy_plugin', $actions );
		$this->assertCount( 1, $actions );
	}

	public function test_blocksy_requires_support_plugin() {
		if ( ! wp_get_theme( 'blocksy' )->exists() ) {
			$this->markTestSkipped( 'Blocksy theme is not installed in the test environment.' );
		}

		$original_stylesheet = get_stylesheet();
		$theme_support = \Tainacan\Theme_Support::get_instance();

		try {
			switch_theme( 'blocksy' );
			$this->assertTrue( $theme_support->is_blocksy_template() );
			$this->assertFalse( $theme_support->is_blocksy_support_plugin_active() );
			$this->assertFalse( $theme_support->is_supported() );
			$this->assertContains( 'install_blocksy_plugin', $theme_support->get_missing_support_actions() );

			update_option( 'active_plugins', array( \Tainacan\Theme_Support::BLOCKSY_SUPPORT_PLUGIN ) );
			$theme_support->flush_cache();
			$this->assertTrue( $theme_support->is_blocksy_support_plugin_active() );
			$this->assertTrue( $theme_support->is_supported() );
			$this->assertNotContains( 'install_blocksy_plugin', $theme_support->get_missing_support_actions() );
		} finally {
			delete_option( 'active_plugins' );
			switch_theme( $original_stylesheet );
		}
	}
}
