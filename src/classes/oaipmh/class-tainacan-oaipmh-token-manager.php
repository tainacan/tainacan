<?php

namespace Tainacan\OAIPMH;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * resumptionToken storage backed by WordPress transients.
 *
 * Tokens carry the pagination state of a ListRecords/ListIdentifiers harvest.
 * Transients give automatic expiration and need no custom table, removing the
 * filesystem tokens used by the legacy implementation.
 */
class OAIPMH_Token_Manager {

	const TRANSIENT_PREFIX = 'tnc_oai_token_';

	/**
	 * Persist a pagination state and return its opaque token.
	 *
	 * @param array $data
	 * @return string
	 */
	public function create( array $data ) {
		$token = wp_generate_password( 40, false, false );

		/**
		 * Lifetime, in seconds, of a resumptionToken. Defaults to 24 hours.
		 *
		 * @param int $seconds
		 */
		$ttl = (int) apply_filters( 'tainacan-oai-token-valid', DAY_IN_SECONDS );

		set_transient( self::TRANSIENT_PREFIX . $token, $data, $ttl );

		return $token;
	}

	/**
	 * Retrieve the pagination state for a token.
	 *
	 * @param string $token
	 * @return array|false
	 */
	public function get( $token ) {
		if ( ! is_string( $token ) || ! preg_match( '/^[A-Za-z0-9]{1,64}$/', $token ) ) {
			return false;
		}

		$data = get_transient( self::TRANSIENT_PREFIX . $token );

		return is_array( $data ) ? $data : false;
	}
}
