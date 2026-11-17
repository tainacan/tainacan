<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * The embedding providers this site offers.
 *
 * Providers are plain objects rather than singletons so a plugin can register
 * several instances of the same class pointed at different gateways. Anything the
 * filter returns that is not an Embedding_Provider is dropped instead of trusted,
 * because the cost of a bad filter entry is a fatal in an unrelated request.
 *
 * The list this exposes deliberately excludes API keys. Credentials are read by the
 * provider itself and never travel through an ability result.
 *
 * @since 1.5.0
 */
class Provider_Registry {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Registered providers, keyed by id.
	 *
	 * @var array<string, Embedding_Provider>
	 */
	private $providers = [];

	/**
	 * Build the default list and let plugins extend it.
	 *
	 * The list *is* the WordPress AI connectors: one provider instance per connector
	 * the site has installed, labelled with the connector's own name. Credentials and
	 * vendor choice stay in `Settings → Connectors`, so nothing here asks for a key and
	 * no vendor name is hard-coded. When the AI plugin is absent, one placeholder
	 * provider is registered so the console can say what is missing instead of showing
	 * an empty select.
	 *
	 * OpenAI and Ollama remain available through the filter for a site that wants a
	 * direct key, but they are not offered by default: two places to configure one
	 * provider means two places to rotate a secret.
	 *
	 * @return void
	 */
	protected function init() {
		$providers = [];

		foreach ( $this->connectors() as $connector_id => $connector ) {
			$label = isset( $connector['name'] ) && '' !== $connector['name']
				? (string) $connector['name']
				: (string) $connector_id;

			$providers[ (string) $connector_id ] = new WordPress_AI_Provider( (string) $connector_id, $label );
		}

		if ( empty( $providers ) ) {
			$providers['wordpress-ai'] = new WordPress_AI_Provider();
		}

		/**
		 * Filters the registered embedding providers.
		 *
		 * @param array<string, Embedding_Provider> $providers Providers keyed by id.
		 */
		$filtered = apply_filters( 'tainacan_rag_register_embedding_providers', $providers );

		if ( ! is_array( $filtered ) ) {
			$filtered = $providers;
		}

		foreach ( $filtered as $id => $provider ) {
			if ( $provider instanceof Embedding_Provider ) {
				$this->providers[ (string) $id ] = $provider;
			}
		}
	}

	/**
	 * One provider by id.
	 *
	 * @param string $id Provider id.
	 * @return Embedding_Provider|null
	 */
	public function get_provider( $id ) {
		$id = (string) $id;

		if ( isset( $this->providers[ $id ] ) ) {
			return $this->providers[ $id ];
		}

		/*
		 * A space created before the connector list settled, or by a plugin that
		 * registered a connector late, still has to resolve. Bind it on demand instead
		 * of reporting a provider that does not exist.
		 */
		$connectors = $this->connectors();

		if ( isset( $connectors[ $id ] ) ) {
			$label = isset( $connectors[ $id ]['name'] ) && '' !== $connectors[ $id ]['name']
				? (string) $connectors[ $id ]['name']
				: $id;

			return new WordPress_AI_Provider( $id, $label );
		}

		return null;
	}

	/**
	 * The AI connectors this site has, as reported by the WordPress AI plugin.
	 *
	 * Empty when the plugin is not there, which is what makes the placeholder provider
	 * and the "connect one first" message possible.
	 *
	 * @return array<string, array>
	 */
	protected function connectors() {
		if ( ! function_exists( 'WordPress\AI\get_ai_connectors' ) ) {
			return [];
		}

		$connectors = \WordPress\AI\get_ai_connectors();

		return is_array( $connectors ) ? $connectors : [];
	}

	/**
	 * All providers.
	 *
	 * @return array<string, Embedding_Provider>
	 */
	public function get_providers() {
		return $this->providers;
	}

	/**
	 * Provider summary for an ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array
	 */
	public function for_ability_list( $input = [] ) {
		$summary = [];

		foreach ( $this->providers as $id => $provider ) {
			$summary[] = [
				'id'                 => $id,
				'label'              => $provider->get_label(),
				'modalities'         => $provider->supported_modalities(),
				'configured'         => $provider->is_configured(),
				'known_models'       => method_exists( $provider, 'get_known_models' ) ? $provider->get_known_models() : [],
				'max_batch'          => $provider->get_max_batch(),
				'requires_api_key'   => $provider instanceof OpenAI_Compatible_Provider ? (bool) $provider->requires_api_key() : true,
				'base_url'           => $provider instanceof OpenAI_Compatible_Provider ? $provider->get_base_url() : '',
			];
		}

		return [ 'providers' => $summary ];
	}
}
