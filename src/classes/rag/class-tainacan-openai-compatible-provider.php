<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Shared implementation for providers that speak the OpenAI embeddings shape.
 *
 * One base class covers OpenAI itself and every self-hosted gateway that copies
 * its `/v1/embeddings` contract, including Ollama. That is deliberate: the
 * requirement is that an operator can point at any provider without a code change,
 * and the OpenAI wire format is the closest thing to a lingua franca.
 *
 * Secrets live in options, are read here, and are never returned by any ability.
 *
 * @since 1.5.0
 */
abstract class OpenAI_Compatible_Provider extends Embedding_Provider {

	/**
	 * Default request timeout in seconds.
	 *
	 * @var int
	 */
	const TIMEOUT = 60;

	/**
	 * Option name holding the API key for this provider.
	 *
	 * @return string
	 */
	public function api_key_option() {
		return 'tainacan_rag_provider_' . $this->get_id() . '_api_key';
	}

	/**
	 * Option name holding the base URL for this provider.
	 *
	 * @return string
	 */
	public function base_url_option() {
		return 'tainacan_rag_provider_' . $this->get_id() . '_base_url';
	}

	/**
	 * Default base URL, before the trailing slash.
	 *
	 * @return string
	 */
	abstract public function get_default_base_url();

	/**
	 * Whether this provider needs a key at all.
	 *
	 * @return bool
	 */
	public function requires_api_key() {
		return true;
	}

	/**
	 * Configured base URL.
	 *
	 * @return string
	 */
	public function get_base_url() {
		$configured = get_option( $this->base_url_option(), '' );

		$url = is_string( $configured ) && '' !== $configured
			? $configured
			: $this->get_default_base_url();

		return untrailingslashit( (string) $url );
	}

	/**
	 * Configured API key, or an empty string.
	 *
	 * Intentionally not exposed by any ability.
	 *
	 * @return string
	 */
	public function get_api_key() {
		$key = get_option( $this->api_key_option(), '' );

		return is_string( $key ) ? $key : '';
	}

	/**
	 * Whether the provider can be called.
	 *
	 * @return bool
	 */
	public function is_configured() {
		if ( '' === $this->get_base_url() ) {
			return false;
		}

		if ( ! $this->requires_api_key() ) {
			return true;
		}

		return '' !== $this->get_api_key();
	}

	/**
	 * Supported modalities. Text by default; multimodal providers override this.
	 *
	 * @return string[]
	 */
	public function supported_modalities() {
		return [ 'text' ];
	}

	/**
	 * Batch size.
	 *
	 * @return int
	 */
	public function get_max_batch() {
		return 32;
	}

	/**
	 * Build the request payload. Pure, so the shape is testable without a network.
	 *
	 * @param string[] $inputs Inputs.
	 * @param string   $model  Model name.
	 * @return array
	 */
	public function build_payload( array $inputs, $model ) {
		return [
			'model' => (string) $model,
			'input' => array_values( $inputs ),
		];
	}

	/**
	 * Build request headers. Pure, and never leaks the key into a return value
	 * outside this method.
	 *
	 * @return array
	 */
	public function build_headers() {
		$headers = [
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		];

		if ( '' !== $this->get_api_key() ) {
			$headers['Authorization'] = 'Bearer ' . $this->get_api_key();
		}

		return $headers;
	}

	/**
	 * Embed a batch of inputs.
	 *
	 * @param string[] $inputs   Inputs.
	 * @param string   $model    Model name.
	 * @param string   $modality Modality.
	 * @return array|\WP_Error
	 */
	public function embed( array $inputs, $model, $modality = 'text' ) {
		$inputs = array_values( array_filter( array_map( 'strval', $inputs ), 'strlen' ) );
		$model  = trim( (string) $model );

		if ( empty( $inputs ) ) {
			return new \WP_Error(
				'tainacan_rag_no_input',
				__( 'There is nothing to embed.', 'tainacan' )
			);
		}

		if ( '' === $model ) {
			return new \WP_Error(
				'tainacan_rag_missing_model',
				__( 'A model is required.', 'tainacan' )
			);
		}

		if ( ! in_array( $modality, $this->supported_modalities(), true ) ) {
			return new \WP_Error(
				'tainacan_rag_unsupported_modality',
				sprintf(
					/* translators: 1: provider label, 2: modality */
					__( '%1$s cannot embed %2$s inputs.', 'tainacan' ),
					$this->get_label(),
					$modality
				)
			);
		}

		if ( ! $this->is_configured() ) {
			return new \WP_Error(
				'tainacan_rag_provider_not_configured',
				sprintf(
					/* translators: %s: provider label */
					__( '%s is not configured yet. Add its credentials in the semantic search settings.', 'tainacan' ),
					$this->get_label()
				)
			);
		}

		$response = wp_remote_post(
			$this->get_base_url() . '/embeddings',
			[
				'timeout' => self::TIMEOUT,
				'headers' => $this->build_headers(),
				'body'    => wp_json_encode( $this->build_payload( $inputs, $model ) ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = wp_remote_retrieve_body( $response );
		$data   = json_decode( $body, true );

		if ( 200 !== $status ) {
			return new \WP_Error(
				'tainacan_rag_provider_error',
				sprintf(
					/* translators: 1: provider label, 2: HTTP status, 3: response body */
					__( '%1$s returned HTTP %2$d: %3$s', 'tainacan' ),
					$this->get_label(),
					$status,
					is_array( $data ) && isset( $data['error']['message'] ) ? $data['error']['message'] : substr( (string) $body, 0, 300 )
				),
				[ 'status' => $status ]
			);
		}

		if ( ! is_array( $data ) || empty( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return new \WP_Error(
				'tainacan_rag_invalid_response',
				__( 'The provider returned a response without embeddings.', 'tainacan' )
			);
		}

		$vectors = [];

		foreach ( $data['data'] as $index => $entry ) {
			if ( ! isset( $entry['embedding'] ) || ! is_array( $entry['embedding'] ) ) {
				return new \WP_Error(
					'tainacan_rag_invalid_response',
					__( 'The provider returned an entry without an embedding.', 'tainacan' )
				);
			}

			$vectors[ $index ] = array_map( 'floatval', $entry['embedding'] );
		}

		$dimensions = isset( $vectors[0] ) ? count( $vectors[0] ) : 0;
		$expected   = (int) $this->get_dimensions( $model );

		/*
		 * A mismatch here means the space table was built for a different length and
		 * every insert would fail at the database. Failing now names the real cause.
		 */
		if ( $expected > 0 && $dimensions !== $expected ) {
			return new \WP_Error(
				'tainacan_rag_dimension_mismatch',
				sprintf(
					/* translators: 1: model, 2: returned dimensions, 3: expected dimensions */
					__( 'Model %1$s returned %2$d dimensions but %3$d were expected for its space.', 'tainacan' ),
					$model,
					$dimensions,
					$expected
				),
				[
					'dimensions' => $dimensions,
					'expected'   => $expected,
				]
			);
		}

		return [
			'vectors'    => $vectors,
			'model'      => $model,
			'dimensions' => $dimensions,
		];
	}
}
