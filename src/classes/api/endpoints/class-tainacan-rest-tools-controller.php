<?php

namespace Tainacan\API\EndPoints;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\API\REST_Controller;
use Tainacan\Tools\Output_Collector;
use Tainacan\Tools\Tools_Registry;

/**
 * REST API controller for Management Tools (run CLI-backed operations from the UI).
 *
 * GET tools – list registered tools.
 * GET tools/status – running indicator (transient/option).
 * POST tools/{id}/run – run a tool and return logs.
 *
 * @since 1.0.0
 */
class REST_Tools_Controller extends REST_Controller {

	const TRANSIENT_RUNNING = 'tainacan_tool_running';
	const TRANSIENT_TTL     = 3600; // 1 hour

	/**
	 * @var string
	 */
	protected $rest_base = 'tools';

	/**
	 * @return string
	 */
	protected function get_schema() {
		return 'TODO:get_schema';
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'tools_permissions_check' ],
			],
		] );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/status', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_status' ],
				'permission_callback' => [ $this, 'tools_permissions_check' ],
			],
		] );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[a-z0-9_-]+)/run', [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'run_tool' ],
				'permission_callback' => [ $this, 'tools_permissions_check' ],
				'args'                => [
					'id' => [
						'type'              => 'string',
						'required'          => true,
						'pattern'           => '[a-z0-9_-]+',
						'sanitize_callback' => 'sanitize_key',
					],
				],
			],
		] );
	}

	/**
	 * Permission check: manage_options.
	 *
	 * @param \WP_REST_Request $request
	 * @return bool|\WP_Error
	 */
	public function tools_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to run management tools.', 'tainacan' ), [ 'status' => 403 ] );
		}
		return true;
	}

	/**
	 * Get registered tools (for cards and forms).
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_items( $request ) {
		$tools = Tools_Registry::get_all_tools();
		$items = [];
		foreach ( $tools as $tool ) {
			if ( ! $tool->is_exposed_to_rest() ) {
				continue;
			}
			$items[] = [
				'id'          => $tool->get_id(),
				'name'        => $tool->get_name(),
				'description' => $tool->get_description(),
				'params'      => $tool->get_params(),
				'destructive' => $tool->is_destructive(),
			];
		}
		return new \WP_REST_Response( $items, 200 );
	}

	/**
	 * Get running status from transient.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_status( $request ) {
		$data = get_transient( self::TRANSIENT_RUNNING );
		if ( $data && is_array( $data ) && ! empty( $data['tool_id'] ) ) {
			return new \WP_REST_Response( [
				'running'    => true,
				'tool_id'    => $data['tool_id'],
				'tool_name'  => isset( $data['tool_name'] ) ? $data['tool_name'] : $data['tool_id'],
				'started_at' => isset( $data['started_at'] ) ? $data['started_at'] : '',
			], 200 );
		}
		return new \WP_REST_Response( [ 'running' => false ], 200 );
	}

	/**
	 * Run a tool by id. Sets transient at start, clears on finish, returns logs.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function run_tool( $request ) {
		$id = $request->get_param( 'id' );
		$tool = Tools_Registry::get_tool( $id );
		if ( ! $tool || ! $tool->is_exposed_to_rest() ) {
			return new \WP_Error( 'invalid_tool', __( 'Tool not found or not runnable.', 'tainacan' ), [ 'status' => 404 ] );
		}

		$body = $request->get_json_params();
		if ( ! is_array( $body ) ) {
			$body = [];
		}
		$args = $this->normalize_tool_params( $tool->get_params(), $body );

		// Mark as running.
		set_transient( self::TRANSIENT_RUNNING, [
			'tool_id'    => $id,
			'tool_name'  => $tool->get_name(),
			'started_at' => gmdate( 'Y-m-d H:i:s' ),
		], self::TRANSIENT_TTL );

		$output = new Run_Output_Collector();
		try {
			$tool->run( $args, $output );
		} catch ( \Exception $e ) {
			$output->error( $e->getMessage() );
		} finally {
			delete_transient( self::TRANSIENT_RUNNING );
		}

		$logs = $output->get_logs();
		$has_error = false;
		foreach ( $logs as $entry ) {
			if ( isset( $entry['level'] ) && $entry['level'] === 'error' ) {
				$has_error = true;
				break;
			}
		}

		$response_data = [
			'logs'    => $logs,
			'success' => ! $has_error,
		];
		$table = $output->get_table();
		if ( $table !== null ) {
			$response_data['table'] = $table;
		}

		return new \WP_REST_Response( $response_data, $has_error ? 400 : 200 );
	}

	/**
	 * Normalize request body to args for the tool. Uses tool param definitions (e.g. from get_params()).
	 *
	 * @param array $params Param definitions (name, type, required, default, description).
	 * @param array $body   Request body.
	 * @return array<string, mixed>
	 */
	private function normalize_tool_params( $params, $body ) {
		$assoc_args = [];
		if ( empty( $params ) || ! is_array( $params ) ) {
			return $assoc_args;
		}
		foreach ( $params as $param ) {
			$name = isset( $param['name'] ) ? $param['name'] : null;
			if ( ! $name ) {
				continue;
			}
			$key = str_replace( '-', '_', $name );
			if ( array_key_exists( $name, $body ) ) {
				$assoc_args[ $key ] = $body[ $name ];
			} elseif ( array_key_exists( $key, $body ) ) {
				$assoc_args[ $key ] = $body[ $key ];
			} elseif ( isset( $param['default'] ) ) {
				$assoc_args[ $key ] = $param['default'];
			}
		}
		// Cast boolean params from definitions.
		foreach ( $params as $param ) {
			$name = isset( $param['name'] ) ? $param['name'] : null;
			if ( ! $name || ! array_key_exists( $name, $assoc_args ) ) {
				continue;
			}
			$type = isset( $param['type'] ) ? strtolower( (string) $param['type'] ) : '';
			if ( $type === 'boolean' ) {
				$assoc_args[ $name ] = (bool) $assoc_args[ $name ];
			}
		}
		return $assoc_args;
	}
}

/**
 * Collects tool output into an array for the REST run response. Only used by REST_Tools_Controller.
 *
 * @since 1.0.0
 */
class Run_Output_Collector implements Output_Collector {

	/** @var array<int, array{datetime: string, message: string, level: string}> */
	private $logs = [];

	/** @var int */
	private $progress_total = 0;

	/** @var int */
	private $progress_done = 0;

	/** @var string */
	private $progress_label = '';

	/** @var array{rows: array, columns: array}|null Last table output, if any. */
	private $table = null;

	/** Log progress every N ticks to avoid flooding the response. */
	private const PROGRESS_LOG_INTERVAL = 50;

	public function log( $message, $level = 'info' ) {
		$this->logs[] = [
			'datetime' => gmdate( 'Y-m-d H:i:s' ),
			'message'  => $message,
			'level'    => $level,
		];
	}

	public function success( $message ) {
		$this->log( $message, 'success' );
	}

	public function warning( $message ) {
		$this->log( $message, 'warning' );
	}

	public function error( $message, $exit = false ) {
		$this->log( $message, 'error' );
	}

	public function start_progress( $total, $label ) {
		$this->progress_total = (int) $total;
		$this->progress_done  = 0;
		$this->progress_label = $label;
		if ( $this->progress_total > 0 ) {
			$this->log( $label . ' (0/' . $this->progress_total . ')', 'info' );
		}
	}

	public function tick_progress( $increment = 1 ) {
		$this->progress_done += (int) $increment;
		if ( $this->progress_total > 0 && $this->progress_done % self::PROGRESS_LOG_INTERVAL === 0 ) {
			$this->log( sprintf( __( 'Processed %d / %d', 'tainacan' ), $this->progress_done, $this->progress_total ), 'info' );
		}
	}

	public function finish_progress() {
		if ( $this->progress_total > 0 ) {
			$this->log( sprintf( __( 'Processed %d / %d', 'tainacan' ), $this->progress_done, $this->progress_total ), 'info' );
		}
		$this->progress_total = 0;
		$this->progress_done  = 0;
		$this->progress_label = '';
	}

	public function output_table( array $rows, array $columns ) {
		$this->table = [ 'rows' => $rows, 'columns' => $columns ];
		$this->log( sprintf( __( 'Displaying %d row(s).', 'tainacan' ), count( $rows ) ), 'info' );
	}

	/** @return array<int, array{datetime: string, message: string, level: string}> */
	public function get_logs() {
		return $this->logs;
	}

	/** @return array{rows: array, columns: array}|null */
	public function get_table() {
		return $this->table;
	}
}
