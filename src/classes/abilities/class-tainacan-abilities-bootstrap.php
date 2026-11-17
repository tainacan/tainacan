<?php

namespace Tainacan\Abilities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Wires Tainacan into the WordPress Abilities API.
 *
 * Two actions, in this order, and nothing registered anywhere else:
 *
 * 1. wp_abilities_api_categories_init -> categories
 * 2. wp_abilities_api_init            -> abilities
 *
 * Registering an ability outside `wp_abilities_api_init` returns null from
 * wp_register_ability() and raises _doing_it_wrong(), which is silent when
 * WP_DEBUG is off. That silence is the reason this class exists instead of
 * scattering wp_register_ability() calls through the codebase.
 *
 * The plugin header still declares "Requires at least: 6.5", while the Abilities
 * API is core from 6.9. The guard below keeps older core working: no hooks, no
 * notices, no fatal.
 *
 * @since 1.5.0
 */
class Abilities_Bootstrap {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Add the two ability hooks, if the Abilities API exists.
	 *
	 * @return void
	 */
	protected function init() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_categories' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
	}

	/**
	 * Categories hook callback.
	 *
	 * @return void
	 */
	public function register_categories() {
		Abilities_Categories::get_instance()->register();
	}

	/**
	 * Abilities hook callback.
	 *
	 * @return void
	 */
	public function register_abilities() {
		Abilities_Registry::get_instance()->register_all();
	}

	/**
	 * Whether this site can register abilities at all.
	 *
	 * Used by tests and by the admin surfaces that explain a missing feature
	 * instead of hiding it.
	 *
	 * @return bool
	 */
	public function is_supported() {
		return function_exists( 'wp_register_ability' )
			&& function_exists( 'wp_register_ability_category' );
	}
}
