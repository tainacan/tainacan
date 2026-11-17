<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * A WordPress AI connector, used as an embedding provider.
 *
 * There is no separate vendor list in Tainacan. WordPress already knows which AI
 * connectors a site has and which of them are authenticated, and
 * `Settings → Connectors` is where that is configured. So one provider instance is
 * bound to one connector id, and the console's provider select is simply the connectors
 * that exist.
 *
 * Embedding goes through `WordPress\AI\generate_embeddings()`, which requires a model
 * **and** a provider: embeddings from different models are not comparable, so nothing
 * is chosen on the operator's behalf beyond the connector. When the AI plugin is too
 * old to expose embeddings, `tainacan_rag_wordpress_ai_embed` is the seam, and the
 * failure names it instead of guessing at an API.
 *
 * @since 1.5.0
 */
class WordPress_AI_Provider extends Embedding_Provider {

	/**
	 * Connector id, for example `google`.
	 *
	 * @var string
	 */
	private $connector;

	/**
	 * Connector label.
	 *
	 * @var string
	 */
	private $label;

	/**
	 * Bind the provider to one connector.
	 *
	 * @param string $connector Connector id. Empty means "no connector available".
	 * @param string $label     Connector label.
	 */
	public function __construct( $connector = '', $label = '' ) {
		$this->connector = (string) $connector;
		$this->label     = (string) $label;
	}

	/**
	 * Provider id. This is the connector id, so a space records which connector built
	 * its vectors.
	 *
	 * @return string
	 */
	public function get_id() {
		return '' !== $this->connector ? $this->connector : 'wordpress-ai';
	}

	/**
	 * Human readable label.
	 *
	 * @return string
	 */
	public function get_label() {
		if ( '' !== $this->label ) {
			return $this->label;
		}

		return __( 'WordPress AI connector', 'tainacan' );
	}

	/**
	 * Modalities offered. Text only: image embeddings depend on a model this layer
	 * cannot promise across connector versions.
	 *
	 * @return string[]
	 */
	public function supported_modalities() {
		return [ 'text' ];
	}

	/**
	 * Dimensions are not published by a connector, so the operator states them when
	 * creating a space and this reports 0.
	 *
	 * @param string $model Model name.
	 * @return int
	 */
	public function get_dimensions( $model ) {
		/**
		 * Filters the reported dimensions for a model.
		 *
		 * @param int    $dimensions Dimensions, 0 when unknown.
		 * @param string $model      Model name.
		 */
		return (int) apply_filters( 'tainacan_rag_wordpress_ai_dimensions', 0, (string) $model );
	}

	/**
	 * Embedding models this connector advertises, as id => label.
	 *
	 * Asked of the AI plugin's model registry rather than guessed: a connector that
	 * declares no embedding model returns an empty list, and the console renders that
	 * as "this connector reports none" instead of offering a dropdown nobody can
	 * choose from. The call is wrapped because the registry can throw while resolving
	 * a connector whose plugin is half-installed.
	 *
	 * @return array<string, string>
	 */
	public function available_models() {
		if (
			'' === $this->connector
			|| ! class_exists( '\WordPress\AiClient\AiClient' )
			|| ! class_exists( '\WordPress\AiClient\Providers\Models\DTO\ModelRequirements' )
			|| ! class_exists( '\WordPress\AiClient\Providers\Models\Enums\CapabilityEnum' )
		) {
			return [];
		}

		/**
		 * Filters the embedding models offered for a connector.
		 *
		 * @param array<string, string> $models    Model id => label.
		 * @param string                $connector Connector id.
		 */
		$filtered = apply_filters( 'tainacan_rag_connector_models', null, $this->connector );

		if ( is_array( $filtered ) ) {
			return $filtered;
		}

		try {
			$requirements = new \WordPress\AiClient\Providers\Models\DTO\ModelRequirements(
				[ \WordPress\AiClient\Providers\Models\Enums\CapabilityEnum::embeddingGeneration() ],
				[]
			);

			$found = \WordPress\AiClient\AiClient::defaultRegistry()->findProviderModelsMetadataForSupport(
				$this->connector,
				$requirements
			);
		} catch ( \Throwable $e ) {
			return [];
		}

		$models = [];

		foreach ( (array) $found as $model ) {
			$id = method_exists( $model, 'getId' ) ? (string) $model->getId() : '';

			if ( '' !== $id ) {
				$models[ $id ] = method_exists( $model, 'getName' ) && '' !== (string) $model->getName()
					? (string) $model->getName()
					: $id;
			}
		}

		return $models;
	}

	/**
	 * Whether the connector exists and is authenticated.
	 *
	 * @return bool
	 */
	public function is_configured() {
		if ( ! \Tainacan\Integrations\WordPress_AI::get_instance()->is_active() ) {
			return false;
		}

		if ( function_exists( 'WordPress\AI\has_ai_credentials' ) && ! \WordPress\AI\has_ai_credentials() ) {
			return false;
		}

		if ( '' !== $this->connector && function_exists( 'WordPress\AI\is_connector_configured' ) ) {
			return (bool) \WordPress\AI\is_connector_configured( $this->connector );
		}

		return true;
	}

	/**
	 * Largest number of inputs per request.
	 *
	 * @return int
	 */
	public function get_max_batch() {
		return 16;
	}

	/**
	 * Embed a batch through the connector.
	 *
	 * @param string[] $inputs   Inputs to embed.
	 * @param string   $model    Model name.
	 * @param string   $modality text or image.
	 * @return array|\WP_Error
	 */
	public function embed( array $inputs, $model, $modality = 'text' ) {
		$inputs = array_values( array_filter( array_map( 'strval', $inputs ), 'strlen' ) );

		if ( empty( $inputs ) ) {
			return new \WP_Error(
				'tainacan_rag_no_input',
				__( 'Nothing to embed.', 'tainacan' )
			);
		}

		if ( ! in_array( $modality, $this->supported_modalities(), true ) ) {
			return new \WP_Error(
				'tainacan_rag_unsupported_modality',
				sprintf(
					/* translators: %s: modality */
					__( 'This connector cannot embed %s.', 'tainacan' ),
					$modality
				)
			);
		}

		$model = trim( (string) $model );

		if ( '' === $model ) {
			return new \WP_Error(
				'tainacan_rag_missing_model',
				__( 'An embedding model is required: vectors from different models are not comparable.', 'tainacan' )
			);
		}

		/*
		 * The client is resolved before the connector is required to be authenticated:
		 * a site that wired `tainacan_rag_wordpress_ai_embed` has its own credentials
		 * and must not be blocked by a connector it never configured.
		 */
		$result = $this->generate( $inputs, $model );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( count( $result ) !== count( $inputs ) ) {
			return new \WP_Error(
				'tainacan_rag_vector_count_mismatch',
				__( 'The connector returned a different number of vectors than inputs.', 'tainacan' )
			);
		}

		$dimensions = count( $result[0] );
		$expected   = $this->get_dimensions( $model );

		if ( $expected > 0 && $dimensions !== $expected ) {
			return new \WP_Error(
				'tainacan_rag_dimension_mismatch',
				sprintf(
					/* translators: 1: model, 2: returned dimensions, 3: expected dimensions */
					__( '%1$s returned %2$d dimensions; this installation expects %3$d for that model.', 'tainacan' ),
					$model,
					$dimensions,
					$expected
				)
			);
		}

		return [
			'vectors'    => $result,
			'model'      => $model,
			'dimensions' => $dimensions,
		];
	}

	/**
	 * Ask the AI plugin for vectors, or a wired client.
	 *
	 * @param string[] $inputs Inputs.
	 * @param string   $model  Model name.
	 * @return array|\WP_Error List of float arrays.
	 */
	private function generate( array $inputs, $model ) {
		if (
			'' !== $this->connector
			&& function_exists( 'WordPress\AI\supports_embedding_generation' )
			&& function_exists( 'WordPress\AI\generate_embeddings' )
			&& \WordPress\AI\supports_embedding_generation()
		) {
			if ( ! $this->is_configured() ) {
				return new \WP_Error(
					'tainacan_rag_provider_not_configured',
					sprintf(
						/* translators: %s: connector id */
						__( 'The %s connector is not authenticated. Connect it under Settings → Connectors.', 'tainacan' ),
						$this->get_id()
					)
				);
			}

			$result = \WordPress\AI\generate_embeddings(
				$inputs,
				[
					'model'    => $model,
					'provider' => $this->connector,
				]
			);

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			if ( ! method_exists( $result, 'getEmbeddings' ) ) {
				return new \WP_Error(
					'tainacan_rag_unexpected_result',
					__( 'The AI plugin returned an embeddings result this version cannot read.', 'tainacan' )
				);
			}

			$vectors = [];

			foreach ( $result->getEmbeddings() as $embedding ) {
				if ( ! method_exists( $embedding, 'getValues' ) ) {
					return new \WP_Error(
						'tainacan_rag_unexpected_result',
						__( 'The AI plugin returned an embedding this version cannot read.', 'tainacan' )
					);
				}

				$vectors[] = array_map( 'floatval', (array) $embedding->getValues() );
			}

			return $vectors;
		}

		/**
		 * Filters the embeddings callable used when the AI plugin cannot embed.
		 *
		 * The callable receives the inputs, the model and the connector id, and must
		 * return a list of float arrays or a WP_Error.
		 *
		 * @param callable|null $embedder  Embeddings callable.
		 * @param string[]      $inputs    Inputs to embed.
		 * @param string        $model     Model name.
		 * @param string        $connector Connector id.
		 */
		$embedder = apply_filters( 'tainacan_rag_wordpress_ai_embed', null, $inputs, $model, $this->connector );

		if ( ! is_callable( $embedder ) ) {
			if ( ! $this->is_configured() ) {
				return new \WP_Error(
					'tainacan_rag_provider_not_configured',
					sprintf(
						/* translators: %s: connector id */
						__( 'The %s connector is not authenticated. Connect it under Settings → Connectors.', 'tainacan' ),
						$this->get_id()
					)
				);
			}

			return new \WP_Error(
				'tainacan_rag_wordpress_ai_unavailable',
				__( 'This WordPress AI version cannot generate embeddings. Update the AI plugin, or hook tainacan_rag_wordpress_ai_embed with a callable that returns vectors.', 'tainacan' )
			);
		}

		$vectors = call_user_func( $embedder, $inputs, $model, $this->connector );

		if ( is_wp_error( $vectors ) ) {
			return $vectors;
		}

		if ( ! is_array( $vectors ) ) {
			return new \WP_Error(
				'tainacan_rag_unexpected_result',
				__( 'The embeddings callable did not return a list of vectors.', 'tainacan' )
			);
		}

		return array_map(
			static function ( $vector ) {
				return array_map( 'floatval', (array) $vector );
			},
			array_values( $vectors )
		);
	}
}
