<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Pauses a provider and model after repeated failures.
 *
 * Row-level backoff already exists in the queue, but it cannot help when the
 * provider itself is down or the account is out of credit: every row would retry,
 * fail, and back off on its own schedule, producing a slow trickle of doomed
 * requests. This pauses the provider+model pair as a unit so one run stops and the
 * operator gets a reason.
 *
 * State lives in one option because it is small, per site, and needs to survive
 * across requests. It is deliberately not in the queue table: a pause is about the
 * provider, not about any particular item.
 *
 * @since 1.5.0
 */
class Provider_Circuit_Breaker {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Option holding health state.
	 *
	 * @var string
	 */
	const OPTION = 'tainacan_rag_provider_health';

	/**
	 * Consecutive failures before a pair is paused.
	 *
	 * @var int
	 */
	const THRESHOLD = 3;

	/**
	 * First pause in seconds; doubles per pause, capped.
	 *
	 * @var int
	 */
	const PAUSE_BASE = 60;

	/**
	 * Maximum pause in seconds.
	 *
	 * @var int
	 */
	const PAUSE_MAX = 3600;

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Key for a provider+model pair.
	 *
	 * @param string $provider Provider id.
	 * @param string $model    Model name.
	 * @return string
	 */
	public function key( $provider, $model ) {
		return $provider . '|' . $model;
	}

	/**
	 * Read the whole state map.
	 *
	 * @return array
	 */
	public function get_state() {
		$state = get_option( self::OPTION, [] );

		return is_array( $state ) ? $state : [];
	}

	/**
	 * Record a failure, pausing the pair once the threshold is reached.
	 *
	 * @param string $provider Provider id.
	 * @param string $model    Model name.
	 * @param string $error    Failure message.
	 * @return array Updated entry.
	 */
	public function record_failure( $provider, $model, $error = '' ) {
		$state = $this->get_state();
		$key   = $this->key( $provider, $model );

		$entry = isset( $state[ $key ] ) && is_array( $state[ $key ] )
			? $state[ $key ]
			: [ 'consecutive' => 0, 'pauses' => 0 ];

		$entry['consecutive'] = (int) $entry['consecutive'] + 1;
		$entry['error']       = substr( (string) $error, 0, 500 );

		if ( $entry['consecutive'] >= self::THRESHOLD ) {
			$pauses        = (int) ( isset( $entry['pauses'] ) ? $entry['pauses'] : 0 ) + 1;
			$seconds       = min( self::PAUSE_MAX, self::PAUSE_BASE * (int) pow( 2, min( 6, $pauses - 1 ) ) );

			$entry['pauses']        = $pauses;
			$entry['paused_until']  = time() + $seconds;
			$entry['paused_seconds'] = $seconds;
		}

		$state[ $key ] = $entry;

		update_option( self::OPTION, $state, false );

		return $entry;
	}

	/**
	 * Record a success, clearing the pair's state.
	 *
	 * @param string $provider Provider id.
	 * @param string $model    Model name.
	 * @return void
	 */
	public function record_success( $provider, $model ) {
		$state = $this->get_state();
		$key   = $this->key( $provider, $model );

		if ( isset( $state[ $key ] ) ) {
			unset( $state[ $key ] );
			update_option( self::OPTION, $state, false );
		}
	}

	/**
	 * Whether a pair is currently paused.
	 *
	 * @param string $provider Provider id.
	 * @param string $model    Model name.
	 * @return array|false Entry with seconds_remaining, or false.
	 */
	public function is_paused( $provider, $model ) {
		$state = $this->get_state();
		$key   = $this->key( $provider, $model );

		if ( empty( $state[ $key ]['paused_until'] ) ) {
			return false;
		}

		$remaining = (int) $state[ $key ]['paused_until'] - time();

		if ( $remaining <= 0 ) {
			// The pause expired: clear it so the next run gets a clean attempt.
			unset( $state[ $key ] );
			update_option( self::OPTION, $state, false );

			return false;
		}

		return [
			'provider'          => $provider,
			'model'             => $model,
			'consecutive'       => (int) $state[ $key ]['consecutive'],
			'seconds_remaining' => $remaining,
			'error'             => isset( $state[ $key ]['error'] ) ? $state[ $key ]['error'] : '',
		];
	}

	/**
	 * Clear every pause. Used by the settings screen and by an operator retry.
	 *
	 * @return void
	 */
	public function clear() {
		delete_option( self::OPTION );
	}
}
