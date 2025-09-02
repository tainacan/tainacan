<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Abstract base class for asynchronous requests.
 *
 * Provides the foundation for handling asynchronous HTTP requests
 * in WordPress, typically used for background processing tasks.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Async_Request {

	/**
	 * Request prefix for identifying the async request.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $prefix = 'wp';

	/**
	 * Action name for the async request.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $action = 'async_request';

	/**
	 * Unique identifier for this request.
	 *
	 * @since 1.0.0
	 *
	 * @var mixed
	 */
	protected $identifier;

	/**
	 * Data to be sent with the async request.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Constructor for the Async_Request class.
	 *
	 * Initializes the async request with a unique identifier.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->identifier = $this->prefix . '_' . $this->action;

		add_action( 'wp_ajax_' . $this->identifier, array( $this, 'maybe_handle' ) );
		add_action( 'wp_ajax_nopriv_' . $this->identifier, array( $this, 'maybe_handle' ) );
	}

	/**
	 * Set data used during the request
	 *
	 * @param array $data Data.
	 *
	 * @return $this
	 */
	public function data( $data ) {
		$this->data = $data;

		return $this;
	}

	/**
	 * Dispatch the async request
	 *
	 * @return array|WP_Error
	 */
	public function dispatch() {
		$url  = add_query_arg( $this->get_query_args(), $this->get_query_url() );
		$args = $this->get_post_args();

		return wp_remote_post( esc_url_raw( $url ), $args );
	}

	/**
	 * Get query args
	 *
	 * @return array
	 */
	protected function get_query_args() {
		if ( property_exists( $this, 'query_args' ) ) {
			return $this->query_args;
		}

		return array(
			'action' => $this->identifier,
			'nonce'  => wp_create_nonce( $this->identifier ),
		);
	}

	/**
	 * Get query URL
	 *
	 * @return string
	 */
	protected function get_query_url() {
		if ( property_exists( $this, 'query_url' ) ) {
			return $this->query_url;
		}

		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * Get post args
	 *
	 * @return array
	 */
	protected function get_post_args() {
		if ( property_exists( $this, 'post_args' ) ) {
			return $this->post_args;
		}

		return array(
			'timeout'   => 10.00,
			'blocking'  => false,
			'body'      => $this->data,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		);
	}

	/**
	 * Maybe handle
	 *
	 * Check for correct nonce and pass to handler.
	 */
	public function maybe_handle() {
		// Don't lock up other requests while processing
		session_write_close();

		check_ajax_referer( $this->identifier, 'nonce' );

		$this->handle();

		wp_die();
	}

	/**
	 * Handle
	 *
	 * Override this method to perform any actions required
	 * during the async request.
	 */
	abstract protected function handle();

}
