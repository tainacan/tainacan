<?php

namespace Tainacan\Integrations;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Integration with the WordPress AI plugin (https://github.com/WordPress/ai).
 *
 * @package Tainacan
 * @since 1.1.0
 */
class WordPress_AI {
	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Plugin basename when {@see is_plugin_active()} is already available (e.g. admin).
	 *
	 * @var string
	 */
	const PLUGIN_BASENAME = 'ai/ai.php';

	/**
	 * Reserved for future WordPress AI integration hooks.
	 */
	protected function init() {
	}

	/**
	 * Whether the WordPress AI plugin is active.
	 *
	 * When the plugin is enabled, WordPress loads ai/ai.php, which defines WPAI_VERSION
	 * and instantiates Main in the same request (see WordPress/ai ai.php).
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		if ( defined( 'WPAI_VERSION' ) ) {
			return true;
		}

		// Non-standard partial load only; false = do not autoload Main.php from disk.
		if ( class_exists( \WordPress\AI\Main::class, false ) ) {
			return true;
		}

		if ( function_exists( 'is_plugin_active' ) ) {
			return is_plugin_active( self::PLUGIN_BASENAME );
		}

		return false;
	}

	/**
	 * Whether alt-text generation should be offered in the item editor.
	 *
	 * @return bool
	 */
	public function is_alt_text_generation_available(): bool {
		$available = $this->is_active() && $this->is_alt_text_generation_usable();

		/**
		 * Filters whether AI alt-text generation is available in Tainacan admin.
		 *
		 * @param bool $available Whether alt-text generation is available.
		 */
		return (bool) apply_filters( 'tainacan_ai_alt_text_generation_available', $available );
	}

	/**
	 * Whether the alt-text ability can run (feature, credentials, vision model).
	 *
	 * @return bool
	 */
	private function is_alt_text_generation_usable(): bool {
		if ( ! function_exists( 'wp_has_ability' ) || ! wp_has_ability( 'ai/alt-text-generation' ) ) {
			return false;
		}

		if ( function_exists( 'WordPress\AI\has_ai_credentials' ) && ! \WordPress\AI\has_ai_credentials() ) {
			return false;
		}
		
		return $this->has_vision_model_available();
	}

	/**
	 * Whether any active AI connector exposes a vision-capable model.
	 *
	 * Uses the same requirements as the WordPress AI REST Models_Controller for capability "vision".
	 *
	 * @return bool
	 */
	private function has_vision_model_available(): bool {
		if ( ! class_exists( \WordPress\AiClient\AiClient::class ) || ! function_exists( 'WordPress\AI\get_ai_connectors' ) ) {
			return false;
		}

		$connectors = \WordPress\AI\get_ai_connectors();
		if ( empty( $connectors ) ) {
			return false;
		}
		
		try {
			// Same requirements as WordPress\AI\REST\Models_Controller for capability "vision".
			// ModalityEnum lives under Messages\Enums, not Providers\Models\Enums.
			$requirements = new \WordPress\AiClient\Providers\Models\DTO\ModelRequirements(
				array( \WordPress\AiClient\Providers\Models\Enums\CapabilityEnum::textGeneration() ),
				array(
					new \WordPress\AiClient\Providers\Models\DTO\RequiredOption(
						\WordPress\AiClient\Providers\Models\Enums\OptionEnum::inputModalities(),
						array(
							\WordPress\AiClient\Messages\Enums\ModalityEnum::text(),
							\WordPress\AiClient\Messages\Enums\ModalityEnum::image(),
						)
					),
				)
			);

			$registry = \WordPress\AiClient\AiClient::defaultRegistry();

			foreach ( array_keys( $connectors ) as $connector_id ) {
				try {
					$models = $registry->findProviderModelsMetadataForSupport( $connector_id, $requirements );
					if ( ! empty( $models ) ) {
						return true;
					}
				} catch ( \Throwable $e ) {
					continue;
				}
			}
		} catch ( \Throwable $e ) {
			return false;
		}

		return false;
	}
}
