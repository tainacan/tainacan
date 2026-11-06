<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Contract every embedding provider implements.
 *
 * Two properties of this interface matter more than the methods:
 *
 * 1. A provider must be able to report its dimensions *before* anything is stored.
 *    `Space_Repository::create_space()` builds a `VECTOR(n)` column, so a wrong
 *    answer here produces a table that rejects every insert later.
 * 2. `embed()` must validate the returned vector length against the reported
 *    dimensions. MariaDB will not silently truncate a vector, but a provider that
 *    switches model under you will, and the failure would surface far from the
 *    cause.
 *
 * Providers are registered through `Provider_Registry` and can be added by a plugin
 * with the `tainacan_rag_register_embedding_providers` filter.
 *
 * @since 1.5.0
 */
abstract class Embedding_Provider {

	/**
	 * Stable provider id, used in space keys and option names.
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Human readable label.
	 *
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Modalities this provider can embed: text, image, or both.
	 *
	 * @return string[]
	 */
	abstract public function supported_modalities();

	/**
	 * Vector length a model produces, or 0 when the model is unknown.
	 *
	 * @param string $model Model name.
	 * @return int
	 */
	abstract public function get_dimensions( $model );

	/**
	 * Whether credentials or connection settings are present.
	 *
	 * @return bool
	 */
	abstract public function is_configured();

	/**
	 * Largest number of inputs a single request should carry.
	 *
	 * @return int
	 */
	abstract public function get_max_batch();

	/**
	 * Embed a batch of inputs.
	 *
	 * Returns an array with `vectors` (a list of float arrays), `model` and
	 * `dimensions`, or a WP_Error. Implementations must reject a batch whose vector
	 * length does not match get_dimensions().
	 *
	 * @param string[] $inputs   Inputs to embed.
	 * @param string   $model    Model name.
	 * @param string   $modality text or image.
	 * @return array|\WP_Error
	 */
	abstract public function embed( array $inputs, $model, $modality = 'text' );
}
