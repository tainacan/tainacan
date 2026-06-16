<?php

namespace Tainacan\API\EndPoints;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\API\REST_Controller;
use Tainacan\OAIPMH\OAIPMH_Data_Provider;
use Tainacan\OAIPMH\OAIPMH_Xml_Generator;
use Tainacan\OAIPMH\OAIPMH_Token_Manager;

/**
 * REST controller exposing the repository as an OAI-PMH 2.0 data provider.
 *
 * Implements the six OAI-PMH verbs at /wp-json/tainacan/v2/oai. The response is
 * raw OAI-PMH XML, served through rest_pre_serve_request so harvesters receive
 * a valid document instead of a JSON envelope.
 *
 * @since 1.0.0
 */
class REST_Oaipmh_Controller extends REST_Controller {

	/**
	 * @var OAIPMH_Data_Provider
	 */
	private $data_provider;

	/**
	 * @var OAIPMH_Token_Manager
	 */
	private $token_manager;

	public function __construct() {
		$this->rest_base = 'oai';
		parent::__construct();
		add_filter( 'rest_pre_serve_request', array( $this, 'serve_xml_response' ), 10, 2 );
	}

	/**
	 * Lazily build the provider collaborators (after post types are registered).
	 */
	private function boot() {
		if ( null === $this->data_provider ) {
			$this->data_provider = new OAIPMH_Data_Provider();
			$this->token_manager = new OAIPMH_Token_Manager();
		}
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_verb' ),
					'permission_callback' => array( $this, 'get_verb_permissions_check' ),
					'args'                => $this->get_verb_params(),
				),
				'schema' => array( $this, 'get_schema' ),
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return bool|\WP_Error
	 */
	public function get_verb_permissions_check( $request ) {
		// OAI-PMH is, by protocol design, an unauthenticated public harvesting
		// interface: every exposed record is already public ('publish'). Access is
		// therefore intentionally open. Use the filter below to restrict it.
		return apply_filters( 'tainacan-oai-permission', true, $request );
	}

	/**
	 * Dispatch the requested OAI-PMH verb and return the XML response.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_verb( $request ) {
		$this->boot();

		$params   = $request->get_params();
		$verb     = isset( $params['verb'] ) ? $params['verb'] : '';
		$base_url = $this->data_provider->get_base_url();
		$xml      = new OAIPMH_Xml_Generator();

		$valid_verbs = array( 'Identify', 'ListMetadataFormats', 'ListSets', 'ListRecords', 'ListIdentifiers', 'GetRecord' );

		if ( '' === $verb ) {
			$xml->init( $base_url, '' )->add_error( 'badVerb', 'Missing verb argument.' );
			return $this->serve( $xml->output(), $verb, $params );
		}

		if ( ! in_array( $verb, $valid_verbs, true ) ) {
			$xml->init( $base_url, $verb )->add_error( 'badVerb', 'Illegal OAI verb.' );
			return $this->serve( $xml->output(), $verb, $params );
		}

		/**
		 * Allow a plugin to short-circuit the response, e.g. serve a cached
		 * document or reject the request (rate limiting). Returning a non-empty
		 * string is treated as the full XML response body.
		 *
		 * @param string|null $pre    Short-circuit XML, or null to dispatch normally.
		 * @param string      $verb   The requested verb.
		 * @param array       $params The request parameters.
		 */
		$pre = apply_filters( 'tainacan-oai-pre-dispatch', null, $verb, $params );
		if ( is_string( $pre ) && '' !== $pre ) {
			return $this->serve( $pre, $verb, $params, true );
		}

		$xml->init( $base_url, $verb, $params );

		switch ( $verb ) {
			case 'Identify':
				$xml->create_identify( $this->data_provider->get_identify() );
				break;
			case 'ListMetadataFormats':
				$this->handle_list_metadata_formats( $xml, $params );
				break;
			case 'ListSets':
				$this->handle_list_sets( $xml, $params );
				break;
			case 'ListRecords':
				$this->handle_list_records( $xml, $params, true );
				break;
			case 'ListIdentifiers':
				$this->handle_list_records( $xml, $params, false );
				break;
			case 'GetRecord':
				$this->handle_get_record( $xml, $params );
				break;
		}

		return $this->serve( $xml->output(), $verb, $params );
	}

	/**
	 * @param OAIPMH_Xml_Generator $xml
	 * @param array                $params
	 */
	private function handle_list_sets( $xml, $params ) {
		if ( $this->has_disallowed_list_sets_args( $params ) ) {
			$xml->add_error( 'badArgument', 'Invalid argument for ListSets.' );
			return;
		}

		$query = $this->parse_list_sets_params( $xml, $params );
		if ( false === $query ) {
			return;
		}

		$max_records = $this->get_max_records();

		$result = $this->data_provider->get_sets_page(
			array(
				'per'  => $max_records,
				'page' => $query['page'],
			)
		);

		if ( empty( $result['sets'] ) ) {
			if ( 1 === (int) $query['page'] ) {
				$xml->add_error( 'noSetHierarchy', 'This repository does not support sets.' );
			} else {
				$xml->add_error( 'badResumptionToken', 'Invalid or expired token.' );
			}
			return;
		}

		$total = $result['total'];
		$list  = $xml->start_list( 'ListSets' );

		foreach ( $result['sets'] as $set ) {
			$xml->add_set( $list, $set );
		}

		$delivered = $query['page'] * $max_records;
		if ( $delivered < $total ) {
			$next_query         = $query;
			$next_query['page'] = $query['page'] + 1;

			$token      = $this->token_manager->create( $next_query );
			$ttl        = (int) apply_filters( 'tainacan-oai-token-valid', DAY_IN_SECONDS );
			$expiration = gmdate( 'Y-m-d\TH:i:s\Z', time() + $ttl );
			$cursor     = ( $query['page'] - 1 ) * $max_records;

			$xml->add_resumption_token( $list, $token, $total, $cursor, $expiration );
		} elseif ( ! empty( $params['resumptionToken'] ) ) {
			$cursor = ( $query['page'] - 1 ) * $max_records;
			$xml->add_resumption_token( $list, '', $total, $cursor );
		}
	}

	/**
	 * ListSets accepts only verb and resumptionToken.
	 *
	 * @param array $params
	 * @return bool
	 */
	private function has_disallowed_list_sets_args( $params ) {
		$allowed = array( 'verb', 'resumptionToken' );
		foreach ( $params as $key => $value ) {
			if ( in_array( $key, $allowed, true ) ) {
				continue;
			}
			if ( is_string( $value ) && '' === $value ) {
				continue;
			}
			return true;
		}
		return false;
	}

	/**
	 * @param OAIPMH_Xml_Generator $xml
	 * @param array                $params
	 * @return array|false
	 */
	private function parse_list_sets_params( $xml, $params ) {
		if ( ! empty( $params['resumptionToken'] ) ) {
			if ( $this->has_exclusive_argument_violation( $params, array( 'metadataPrefix', 'from', 'until', 'set' ) ) ) {
				$xml->add_error( 'badArgument', 'The usage of resumptionToken as an argument allows no other arguments.' );
				return false;
			}

			$data = $this->token_manager->get( $params['resumptionToken'] );
			if ( ! $this->is_valid_token_for_verb( $data, 'ListSets' ) ) {
				$xml->add_error( 'badResumptionToken', 'Invalid or expired token.' );
				return false;
			}
			return $data;
		}

		return array(
			'verb' => 'ListSets',
			'page' => 1,
		);
	}

	/**
	 * @param array        $data
	 * @param string       $verb
	 * @return bool
	 */
	private function is_valid_token_for_verb( $data, $verb ) {
		return is_array( $data ) && isset( $data['verb'] ) && $verb === $data['verb'];
	}

	/**
	 * @param array $params
	 * @param array $exclusive_args
	 * @return bool
	 */
	private function has_exclusive_argument_violation( $params, $exclusive_args ) {
		foreach ( $exclusive_args as $arg ) {
			if ( isset( $params[ $arg ] ) && '' !== $params[ $arg ] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param OAIPMH_Xml_Generator $xml
	 * @param array                $params
	 */
	private function handle_list_metadata_formats( $xml, $params ) {
		if ( ! empty( $params['identifier'] ) && ! $this->data_provider->item_exists( $params['identifier'] ) ) {
			$xml->add_error( 'idDoesNotExist', 'The identifier does not exist.' );
			return;
		}
		$xml->create_metadata_formats();
	}

	/**
	 * @param OAIPMH_Xml_Generator $xml
	 * @param array                $params
	 * @param bool                 $include_metadata
	 */
	private function handle_list_records( $xml, $params, $include_metadata ) {
		$list_verb = $include_metadata ? 'ListRecords' : 'ListIdentifiers';
		$query     = $this->parse_list_params( $xml, $params, $list_verb );
		if ( false === $query ) {
			return;
		}

		$max_records = $this->get_max_records();

		$result = $this->data_provider->get_records(
			array(
				'per'   => $max_records,
				'page'  => $query['page'],
				'set'   => isset( $query['set'] ) ? $query['set'] : null,
				'from'  => isset( $query['from'] ) ? $query['from'] : null,
				'until' => isset( $query['until'] ) ? $query['until'] : null,
			)
		);

		if ( empty( $result['items'] ) ) {
			$xml->add_error( 'noRecordsMatch', 'No records match the request criteria.' );
			return;
		}

		$total     = $result['total'];
		$list_type = $include_metadata ? 'ListRecords' : 'ListIdentifiers';
		$list      = $xml->start_list( $list_type );

		foreach ( $result['items'] as $item ) {
			if ( $include_metadata ) {
				$xml->add_record( $list, $item, true );
			} else {
				$xml->add_header( $list, $item );
			}
		}

		$delivered = $query['page'] * $max_records;
		if ( $delivered < $total ) {
			$next_query         = $query;
			$next_query['page'] = $query['page'] + 1;

			$token      = $this->token_manager->create( $next_query );
			$ttl        = (int) apply_filters( 'tainacan-oai-token-valid', DAY_IN_SECONDS );
			$expiration = gmdate( 'Y-m-d\TH:i:s\Z', time() + $ttl );
			$cursor     = ( $query['page'] - 1 ) * $max_records;

			$xml->add_resumption_token( $list, $token, $total, $cursor, $expiration );
		} elseif ( ! empty( $params['resumptionToken'] ) ) {
			$cursor = ( $query['page'] - 1 ) * $max_records;
			$xml->add_resumption_token( $list, '', $total, $cursor );
		}
	}

	/**
	 * @param OAIPMH_Xml_Generator $xml
	 * @param array                $params
	 */
	private function handle_get_record( $xml, $params ) {
		if ( empty( $params['identifier'] ) ) {
			$xml->add_error( 'badArgument', 'Missing identifier argument.' );
			return;
		}
		if ( empty( $params['metadataPrefix'] ) ) {
			$xml->add_error( 'badArgument', 'Missing metadataPrefix argument.' );
			return;
		}
		if ( 'oai_dc' !== $params['metadataPrefix'] ) {
			$xml->add_error( 'cannotDisseminateFormat', 'Metadata format not supported.' );
			return;
		}

		$item = $this->data_provider->get_item( $params['identifier'] );
		if ( ! $item ) {
			$xml->add_error( 'idDoesNotExist', 'The identifier does not exist.' );
			return;
		}

		$list = $xml->start_list( 'GetRecord' );
		$xml->add_record( $list, $item, true );
	}

	/**
	 * Resolve the pagination query from the request or a resumptionToken.
	 *
	 * @param OAIPMH_Xml_Generator $xml
	 * @param array                $params
	 * @return array|false
	 */
	private function parse_list_params( $xml, $params, $expected_verb ) {
		if ( ! empty( $params['resumptionToken'] ) ) {
			if ( $this->has_exclusive_argument_violation( $params, array( 'metadataPrefix', 'from', 'until', 'set' ) ) ) {
				$xml->add_error( 'badArgument', 'The usage of resumptionToken as an argument allows no other arguments.' );
				return false;
			}

			$data = $this->token_manager->get( $params['resumptionToken'] );
			if ( ! $this->is_valid_token_for_verb( $data, $expected_verb ) ) {
				$xml->add_error( 'badResumptionToken', 'Invalid or expired token.' );
				return false;
			}
			return $data;
		}

		if ( empty( $params['metadataPrefix'] ) ) {
			$xml->add_error( 'badArgument', 'Missing metadataPrefix argument.' );
			return false;
		}
		if ( 'oai_dc' !== $params['metadataPrefix'] ) {
			$xml->add_error( 'cannotDisseminateFormat', 'Metadata format not supported.' );
			return false;
		}

		$query = array(
			'verb'           => $expected_verb,
			'page'           => 1,
			'metadataPrefix' => 'oai_dc',
		);

		// Treat null/'' as "no set filter"; everything else (including '0') must validate.
		if ( isset( $params['set'] ) && '' !== $params['set'] ) {
			if ( ! $this->data_provider->set_exists( $params['set'] ) ) {
				$xml->add_error( 'badArgument', 'Invalid set specification.' );
				return false;
			}
			$query['set'] = (int) $params['set'];
		}

		if ( ! empty( $params['from'] ) ) {
			$from = $this->parse_date( $params['from'], 'from' );
			if ( null === $from ) {
				$xml->add_error( 'badArgument', 'Invalid from date.' );
				return false;
			}
			$query['from'] = $from;
		}
		if ( ! empty( $params['until'] ) ) {
			$until = $this->parse_date( $params['until'], 'until' );
			if ( null === $until ) {
				$xml->add_error( 'badArgument', 'Invalid until date.' );
				return false;
			}
			$query['until'] = $until;
		}

		return $query;
	}

	/**
	 * Page size for paginated OAI list verbs.
	 *
	 * Defaults to the same value as the REST API and theme search
	 * (`tainacan_option_search_results_per_page`, via $TAINACAN_API_MAX_ITEMS_PER_PAGE).
	 *
	 * @return int
	 */
	private function get_max_records() {
		global $TAINACAN_API_MAX_ITEMS_PER_PAGE;

		$default = isset( $TAINACAN_API_MAX_ITEMS_PER_PAGE )
			? (int) $TAINACAN_API_MAX_ITEMS_PER_PAGE
			: max( 12, (int) get_option( 'tainacan_option_search_results_per_page', 96 ) );

		/**
		 * Filter the OAI-PMH page size for ListSets, ListRecords and ListIdentifiers.
		 *
		 * @param int $default Page size from Tainacan search settings.
		 */
		return max( 1, (int) apply_filters( 'tainacan-oai-maxrecords', $default ) );
	}

	/**
	 * Normalize an OAI date argument (YYYY-MM-DD or full UTC) to SQL form.
	 *
	 * @param string $date
	 * @param string $bound Either 'from' or 'until'.
	 * @return string|null
	 */
	private function parse_date( $date, $bound = 'from' ) {
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $date ) ) {
			return gmdate( 'Y-m-d H:i:s', strtotime( $date ) );
		}
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			if ( 'until' === $bound ) {
				return $date . ' 23:59:59';
			}
			return $date . ' 00:00:00';
		}
		return null;
	}

	/**
	 * Echo raw OAI-PMH XML instead of the REST JSON envelope.
	 *
	 * @param bool              $served Whether the request was already served.
	 * @param \WP_REST_Response $result REST response object.
	 * @return bool
	 */
	public function serve_xml_response( $served, $result ) {
		if ( $served || ! ( $result instanceof \WP_REST_Response ) ) {
			return $served;
		}
		$data = $result->get_data();
		if ( ! is_string( $data ) || '' === $data ) {
			return $served;
		}
		$headers = $result->get_headers();
		if ( isset( $headers['Content-Type'] ) && false === strpos( $headers['Content-Type'], 'text/xml' ) ) {
			return $served;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw OAI-PMH XML produced by DOMDocument (OAIPMH_Xml_Generator), already escaped element by element; esc_*() would corrupt the XML and break harvesters.
		echo $data;
		return true;
	}

	/**
	 * Serve an XML string as the raw response body.
	 *
	 * @param string $xml_string The OAI-PMH XML document.
	 * @param string $verb       The requested verb.
	 * @param array  $params     The request parameters.
	 * @param bool   $from_cache Whether the body came from a short-circuit (cache) filter.
	 * @return \WP_REST_Response
	 */
	private function serve( $xml_string, $verb = '', $params = array(), $from_cache = false ) {
		/**
		 * Fires after the OAI-PMH XML response is built, before it is sent.
		 * Lets a plugin cache the body or record observability data.
		 *
		 * @param string $xml_string The response body.
		 * @param string $verb       The requested verb.
		 * @param array  $params     The request parameters.
		 * @param bool   $from_cache Whether the body was produced by a short-circuit filter.
		 */
		do_action( 'tainacan-oai-response', $xml_string, $verb, $params, $from_cache );

		$response = new \WP_REST_Response();
		$response->set_headers( array( 'Content-Type' => 'text/xml; charset=utf-8' ) );
		$response->set_data( $xml_string );

		return $response;
	}

	public function get_verb_params() {
		return array(
			'verb'            => array(
				'description' => __( 'The OAI-PMH verb to execute.', 'tainacan' ),
				'type'        => 'string',
			),
			'resumptionToken' => array(
				'description' => __( 'The resumptionToken to continue a previous request.', 'tainacan' ),
				'type'        => 'string',
			),
			'metadataPrefix'  => array(
				'description' => __( 'The metadataPrefix to use in the request. Used when the verb is "ListRecords", "ListIdentifiers" and "GetRecord".', 'tainacan' ),
				'type'        => 'string',
			),
			'identifier'      => array(
				'description' => __( 'The record identifier. Used when the verb is "GetRecord".', 'tainacan' ),
				'type'        => 'string',
			),
			'set'             => array(
				'description' => __( 'The set (collection ID) to filter by.', 'tainacan' ),
				'type'        => 'string',
			),
			'from'            => array(
				'description' => __( 'Lower datestamp bound for selective harvesting (UTC).', 'tainacan' ),
				'type'        => 'string',
			),
			'until'           => array(
				'description' => __( 'Upper datestamp bound for selective harvesting (UTC).', 'tainacan' ),
				'type'        => 'string',
			),
		);
	}

	public function get_schema() {
		return array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'       => $this->rest_base,
			'type'        => 'string',
			'tags'        => array( $this->rest_base ),
			'description' => __( 'Result of the OAI-PMH Exposer for the provided verb.', 'tainacan' ),
		);
	}
}
