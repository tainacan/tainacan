<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Ollama, used through its OpenAI-compatible endpoint.
 *
 * This is the fully local path: no key, no egress, and the reason a site with no
 * cloud budget can still index its own collection.
 *
 * @since 1.5.0
 */
class Ollama_Provider extends OpenAI_Compatible_Provider {

	/**
	 * Known model dimensions.
	 *
	 * @var array<string, int>
	 */
	const MODEL_DIMENSIONS = [
		'nomic-embed-text'    => 768,
		'mxbai-embed-large'   => 1024,
		'all-minilm'          => 384,
		'snowflake-arctic-embed' => 1024,
	];

	/**
	 * Provider id.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'ollama';
	}

	/**
	 * Label.
	 *
	 * @return string
	 */
	public function get_label() {
		return 'Ollama (local)';
	}

	/**
	 * Default endpoint. Overridable when Ollama runs in another container.
	 *
	 * @return string
	 */
	public function get_default_base_url() {
		return 'http://localhost:11434/v1';
	}

	/**
	 * A local server needs no key.
	 *
	 * @return bool
	 */
	public function requires_api_key() {
		return false;
	}

	/**
	 * Dimensions for a model.
	 *
	 * @param string $model Model name.
	 * @return int
	 */
	public function get_dimensions( $model ) {
		$model = preg_replace( '/:.*$/', '', (string) $model );

		return isset( self::MODEL_DIMENSIONS[ $model ] ) ? (int) self::MODEL_DIMENSIONS[ $model ] : 0;
	}

	/**
	 * Known models.
	 *
	 * @return array<string, int>
	 */
	public function get_known_models() {
		return self::MODEL_DIMENSIONS;
	}
}
