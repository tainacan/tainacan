<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Detects whether the active theme provides Tainacan templates (items archive,
 * item single, faceted search) or whether the site owner opted in to the
 * fallback templates shipped by the plugin.
 *
 * Detection is cached in a transient for 12 hours and invalidated whenever a
 * theme is switched, a plugin is (de)activated or updated, or the collection
 * items archive override option changes.
 */
class Theme_Support {
	use Traits\Singleton_Instance;

	const TRANSIENT_KEY = 'tainacan_theme_support';

	/**
	 * Theme slugs (template or stylesheet) known to ship Tainacan templates.
	 */
	const SUPPORTED_THEMES = array( 'tainacan-interface', 'taina' );

	/**
	 * Plugin basename of "Tainacan Support for Blocksy" (WordPress.org slug
	 * `tainacan-blocksy`, version 0.5.1): its main file is `functions.php`, so the
	 * plugin can also be shipped as a Blocksy child theme.
	 */
	const BLOCKSY_SUPPORT_PLUGIN = 'tainacan-blocksy/functions.php';

	/**
	 * Constant defined by "Tainacan Support for Blocksy" when it is loaded,
	 * whether as a plugin or as a child theme.
	 */
	const BLOCKSY_SUPPORT_CONSTANT = 'TAINACAN_BLOCKSY_VERSION';

	protected function init() {
		add_action( 'switch_theme', array( $this, 'flush_cache' ) );
		add_action( 'activated_plugin', array( $this, 'flush_cache' ) );
		add_action( 'deactivated_plugin', array( $this, 'flush_cache' ) );
		add_action( 'upgrader_process_complete', array( $this, 'flush_cache' ) );
		add_action( 'update_option_tainacan_option_override_collection_items_archive_template', array( $this, 'flush_cache' ) );
		add_action( 'add_option_tainacan_option_override_collection_items_archive_template', array( $this, 'flush_cache' ) );
		add_action( 'delete_option_tainacan_option_override_collection_items_archive_template', array( $this, 'flush_cache' ) );
	}

	/**
	 * Whether the current theme (or the plugin fallback templates) supports
	 * Tainacan item listings.
	 *
	 * @return bool
	 */
	public function is_supported(): bool {
		$stylesheet = get_stylesheet();
		$cached = get_transient( self::TRANSIENT_KEY );

		if ( is_array( $cached ) && isset( $cached['stylesheet'], $cached['supported'] ) && $cached['stylesheet'] === $stylesheet ) {
			$supported = (bool) $cached['supported'];
		} else {
			$supported = $this->detect();

			// Themes declare add_theme_support() on after_setup_theme; caching
			// before that would record a false negative for the next 12 hours.
			if ( did_action( 'after_setup_theme' ) ) {
				set_transient(
					self::TRANSIENT_KEY,
					array( 'stylesheet' => $stylesheet, 'supported' => $supported ),
					12 * HOUR_IN_SECONDS
				);
			}
		}

		/**
		 * Filters whether the active theme supports Tainacan templates.
		 *
		 * @param bool      $supported Detected support.
		 * @param \WP_Theme $theme     The active theme.
		 */
		return (bool) apply_filters( 'tainacan-theme-support', $supported, wp_get_theme() );
	}

	/**
	 * Removes the cached detection result.
	 */
	public function flush_cache() {
		delete_transient( self::TRANSIENT_KEY );
	}

	/**
	 * Runs the actual detection, without cache.
	 *
	 * @return bool
	 */
	private function detect(): bool {
		if ( current_theme_supports( 'tainacan' ) ) {
			return true;
		}

		if ( in_array( get_template(), self::SUPPORTED_THEMES, true ) || in_array( get_stylesheet(), self::SUPPORTED_THEMES, true ) ) {
			return true;
		}

		if ( '' !== locate_template( array( 'tainacan/archive-items.php', 'tainacan/single-items.php' ) ) ) {
			return true;
		}

		if ( $this->is_blocksy_template() && $this->is_blocksy_support_plugin_active() ) {
			return true;
		}

		// The site owner chose the basic templates shipped by the plugin.
		if ( $this->is_collection_items_archive_override_enabled() ) {
			return true;
		}

		return false;
	}

	/**
	 * Same rule Theme_Helper::init() uses to decide whether the collection
	 * items archive fallback template is enabled: the constant wins over the option.
	 *
	 * @return bool
	 */
	public function is_collection_items_archive_override_enabled(): bool {
		return (bool) (
			defined( 'TAINACAN_ENABLE_COLLECTION_THE_CONTENT_FILTER' )
				? ( true === TAINACAN_ENABLE_COLLECTION_THE_CONTENT_FILTER )
				: get_option( 'tainacan_option_override_collection_items_archive_template', false )
		);
	}

	/**
	 * @return bool
	 */
	public function is_blocksy_template(): bool {
		return 'blocksy' === get_template();
	}

	/**
	 * Checks the plugin's own constant first, then the active plugin lists
	 * directly, so no wp-admin file has to be loaded on the front end.
	 *
	 * @return bool
	 */
	public function is_blocksy_support_plugin_active(): bool {
		if ( defined( self::BLOCKSY_SUPPORT_CONSTANT ) ) {
			return true;
		}

		if ( in_array( self::BLOCKSY_SUPPORT_PLUGIN, (array) get_option( 'active_plugins', array() ), true ) ) {
			return true;
		}

		if ( is_multisite() ) {
			$sitewide = (array) get_site_option( 'active_sitewide_plugins', array() );
			return isset( $sitewide[ self::BLOCKSY_SUPPORT_PLUGIN ] );
		}

		return false;
	}

	/**
	 * @return bool Whether Tainacan Interface is installed and may be activated on this site.
	 */
	public function is_tainacan_interface_installed(): bool {
		$theme = wp_get_theme( 'tainacan-interface' );
		return $theme->exists() && $theme->is_allowed();
	}

	/**
	 * Lists the actions the current user can take to obtain theme support.
	 *
	 * @return string[] Any of 'install_theme', 'activate_theme', 'install_blocksy_plugin'.
	 */
	public function get_missing_support_actions(): array {
		$actions = array();

		if ( $this->is_tainacan_interface_installed() ) {
			if ( current_user_can( 'switch_themes' ) ) {
				$actions[] = 'activate_theme';
			}
		} elseif ( current_user_can( 'install_themes' ) ) {
			$actions[] = 'install_theme';
		}

		if ( $this->is_blocksy_template() && ! $this->is_blocksy_support_plugin_active() && current_user_can( 'install_plugins' ) ) {
			$actions[] = 'install_blocksy_plugin';
		}

		return $actions;
	}
}
