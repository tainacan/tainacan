<?php

namespace Tainacan\Abilities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Registers the WordPress Abilities API categories used by Tainacan.
 *
 * Categories must be registered on the `wp_abilities_api_categories_init`
 * action, which fires before `wp_abilities_api_init`. An ability that
 * references an unregistered category is silently dropped: wp_register_ability()
 * returns null and only fires _doing_it_wrong(), which is invisible when
 * WP_DEBUG is off. Keeping every slug in one list is what makes that ordering
 * auditable and testable.
 *
 * @since 1.5.0
 */
class Abilities_Categories {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Category definitions, slug => label and description.
	 *
	 * Slugs must match [a-z0-9]+(?:-[a-z0-9]+)*.
	 *
	 * @var array<string, array{label: string, description: string}>
	 */
	const CATEGORIES = [
		'tainacan' => [
			'label'       => 'Tainacan',
			'description' => 'Abilities provided by the Tainacan repository platform.',
		],
		'tainacan-content' => [
			'label'       => 'Tainacan content',
			'description' => 'Collections, items and their metadata values.',
		],
		'tainacan-structure' => [
			'label'       => 'Tainacan structure',
			'description' => 'Metadata definitions, sections, taxonomies, terms, filters and facets.',
		],
		'tainacan-media' => [
			'label'       => 'Tainacan media',
			'description' => 'Attachments and item documents.',
		],
		'tainacan-search' => [
			'label'       => 'Tainacan search',
			'description' => 'Search, reports and read-only introspection.',
		],
		'tainacan-admin' => [
			'label'       => 'Tainacan administration',
			'description' => 'Roles, capabilities and site configuration.',
		],
		'tainacan-assist' => [
			'label'       => 'Tainacan assisted authoring',
			'description' => 'Single-shot proposals from an external model. These abilities never write.',
		],
	];

	/**
	 * Nothing to hook here; the bootstrap owns the action registration.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Register every category.
	 *
	 * Must run on `wp_abilities_api_categories_init`.
	 *
	 * @return void
	 */
	public function register() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		foreach ( self::CATEGORIES as $slug => $args ) {
			wp_register_ability_category( $slug, $args );
		}
	}

	/**
	 * Registered category slugs, as declared by this class.
	 *
	 * @return string[]
	 */
	public function get_slugs() {
		return array_keys( self::CATEGORIES );
	}
}
