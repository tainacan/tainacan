<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * OpenAI embeddings.
 *
 * @since 1.5.0
 */
class OpenAI_Provider extends OpenAI_Compatible_Provider {

	/**
	 * Known model dimensions.
	 *
	 * A model missing from this map reports 0, which means "unknown" rather than
	 * "unsupported": create_space() then relies on the environment's maximum, and
	 * embed() skips its length check. Adding a model here is what turns that check on.
	 *
	 * @var array<string, int>
	 */
	const MODEL_DIMENSIONS = [
		'text-embedding-3-small' => 1536,
		'text-embedding-3-large' => 3072,
		'text-embedding-ada-002' => 1536,
	];

	/**
	 * Provider id.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'openai';
	}

	/**
	 * Label.
	 *
	 * @return string
	 */
	public function get_label() {
		return 'OpenAI';
	}

	/**
	 * Default endpoint.
	 *
	 * @return string
	 */
	public function get_default_base_url() {
		return 'https://api.openai.com/v1';
	}

	/**
	 * Dimensions for a model.
	 *
	 * @param string $model Model name.
	 * @return int
	 */
	public function get_dimensions( $model ) {
		$model = (string) $model;

		return isset( self::MODEL_DIMENSIONS[ $model ] ) ? (int) self::MODEL_DIMENSIONS[ $model ] : 0;
	}

	/**
	 * Known models, for the settings screen.
	 *
	 * @return array<string, int>
	 */
	public function get_known_models() {
		return self::MODEL_DIMENSIONS;
	}
}
