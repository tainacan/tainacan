<?php

namespace Tainacan\Abilities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Executes a Tainacan REST route on behalf of an ability.
 *
 * Design notes, because the alternative is tempting and wrong:
 *
 * - No HTTP loopback. Calling wp_remote_request() against the site's own URL
 *   fails inside containers, where the published WP_SITE_URL is unreachable from
 *   the container itself. That is the same root cause as Tainacan background
 *   processes stuck in `waiting`, and it would also lose the current user.
 * - No hand-invoked callbacks. rest_do_request() runs the real dispatch path, so
 *   route matching, argument validation and the controller's own
 *   permission_callback all apply exactly as they do for an HTTP client. A
 *   second code path would drift from the first.
 *
 * Abilities return data, not HTTP, so responses are normalized: a WP_REST_Response
 * becomes its data, and any status at or above 400 becomes a WP_Error so the
 * Abilities API reports a failure instead of returning an error body as success.
 *
 * @since 1.5.0
 */
class Ability_Bridge {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Placeholder pattern used in route templates, for example
	 * `/tainacan/v2/items/{item_id}`.
	 *
	 * @var string
	 */
	const ROUTE_PLACEHOLDER_PATTERN = '/\{([a-zA-Z0-9_]+)\}/';

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Run a route for an ability definition.
	 *
	 * @param array $definition Ability definition from Abilities_Registry.
	 * @param mixed $input      Validated ability input.
	 * @return array|\WP_Error|mixed Normalized response data, or WP_Error on failure.
	 */
	public function execute( array $definition, $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		$route_template = isset( $definition['route'] ) ? (string) $definition['route'] : '';
		$method         = isset( $definition['method'] ) ? strtoupper( (string) $definition['method'] ) : 'GET';

		if ( '' === $route_template ) {
			return new \WP_Error(
				'tainacan_ability_missing_route',
				__( 'This ability has no REST route configured.', 'tainacan' )
			);
		}

		$route_params = isset( $definition['route_params'] ) && is_array( $definition['route_params'] )
			? $definition['route_params']
			: [];

		$route = $this->build_route( $route_template, $route_params, $input );

		if ( is_wp_error( $route ) ) {
			return $route;
		}

		$params = $input;
		foreach ( $route_params as $key ) {
			unset( $params[ $key ] );
		}

		if ( isset( $definition['body_defaults'] ) && is_array( $definition['body_defaults'] ) ) {
			// Caller input must win over the ability's own defaults.
			$params = array_merge( $definition['body_defaults'], $params );
		}

		$request = new \WP_REST_Request( $method, $route );

		if ( 'GET' === $method ) {
			$request->set_query_params( $params );
		} else {
			/*
			 * Tainacan write controllers read json_decode( $request->get_body() ),
			 * not get_json_params(). Sending only body params leaves them with an
			 * empty payload, so a create would silently do nothing. Set both: the
			 * raw body is what the controller actually reads, and body params keep
			 * $request['key'] lookups working.
			 */
			$request->set_header( 'content-type', 'application/json' );
			$request->set_body( wp_json_encode( empty( $params ) ? new \stdClass() : $params ) );
			$request->set_body_params( $params );
		}

		return $this->normalize( rest_do_request( $request ), $route, $method );
	}

	/**
	 * Escape hatch: execute an arbitrary Tainacan REST route.
	 *
	 * This exists so the long tail of endpoints is reachable without writing a row
	 * for each of the 137 operations. It is deliberately narrow:
	 *
	 * - the route must start with the Tainacan namespace, so it cannot be used to
	 *   reach wp/v2, plugin routes or the abilities API itself
	 * - the method is restricted to the five verbs that make sense
	 * - the target route's own permission_callback runs inside rest_do_request() as
	 *   the current user, so this cannot escalate privilege. It is a convenience
	 *   door, not a master key.
	 *
	 * @param mixed $input Ability input with route, optional method and params.
	 * @return mixed
	 */
	public function escape_hatch( $input = [] ) {
		$input  = is_array( $input ) ? $input : [];
		$route  = isset( $input['route'] ) ? trim( (string) $input['route'] ) : '';
		$method = isset( $input['method'] ) ? strtoupper( (string) $input['method'] ) : 'GET';
		$params = isset( $input['params'] ) && is_array( $input['params'] ) ? $input['params'] : [];

		if ( '' === $route ) {
			return new \WP_Error(
				'tainacan_ability_missing_route',
				__( 'A route is required, for example tainacan/v2/reports/repository/summary.', 'tainacan' )
			);
		}

		$allowed_methods = [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ];

		if ( ! in_array( $method, $allowed_methods, true ) ) {
			return new \WP_Error(
				'tainacan_ability_invalid_method',
				__( 'method must be one of GET, POST, PUT, PATCH or DELETE.', 'tainacan' )
			);
		}

		$normalised = '/' . ltrim( $route, '/' );
		$prefix     = '/' . TAINACAN_REST_NAMESPACE . '/';

		if ( 0 !== strpos( $normalised, $prefix ) ) {
			return new \WP_Error(
				'tainacan_ability_route_not_allowed',
				sprintf(
					/* translators: %s: required route prefix */
					__( 'Only routes under %s can be reached through this ability. Use a dedicated ability for anything else.', 'tainacan' ),
					$prefix
				)
			);
		}

		return $this->execute(
			[
				'route'  => $normalised,
				'method' => $method,
			],
			$params
		);
	}

	/**
	 * Substitute `{placeholder}` tokens in a route template.
	 *
	 * @param string   $template     Route template.
	 * @param string[] $route_params Input keys used as route parameters.
	 * @param array    $input        Ability input.
	 * @return string|\WP_Error
	 */
	private function build_route( $template, array $route_params, array $input ) {
		if ( empty( $route_params ) ) {
			return $template;
		}

		$replacements = [];

		foreach ( $route_params as $key ) {
			if ( ! array_key_exists( $key, $input ) || '' === $input[ $key ] || null === $input[ $key ] ) {
				return new \WP_Error(
					'tainacan_ability_missing_param',
					sprintf(
						/* translators: %s: parameter name */
						__( 'The parameter %s is required by this ability.', 'tainacan' ),
						$key
					)
				);
			}

			$replacements[ '{' . $key . '}' ] = rawurlencode( (string) $input[ $key ] );
		}

		return strtr( $template, $replacements );
	}

	/**
	 * Convert a REST response into ability output.
	 *
	 * @param mixed  $response Result of rest_do_request().
	 * @param string $route    Route that was executed, for error context.
	 * @param string $method   HTTP method, for error context.
	 * @return mixed
	 */
	private function normalize( $response, $route, $method ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! $response instanceof \WP_REST_Response ) {
			return $response;
		}

		$status = (int) $response->get_status();
		$data   = $response->get_data();

		if ( $status < 400 ) {
			return $data;
		}

		return new \WP_Error(
			'tainacan_ability_request_failed',
			sprintf(
				/* translators: 1: HTTP method, 2: route, 3: HTTP status code */
				__( 'The %1$s request to %2$s failed with status %3$d.', 'tainacan' ),
				$method,
				$route,
				$status
			),
			[
				'status'   => $status,
				'route'    => $route,
				'method'   => $method,
				'response' => $data,
			]
		);
	}
}
