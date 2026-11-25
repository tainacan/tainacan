<?php

namespace Tainacan\API\EndPoints;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Tainacan\API\REST_Controller;
use \Tainacan\GenericProcess\Management_Tools_Process;

/**
 * REST API controller for the Management Tools.
 *
 * Lists the commands available in the Management_Tools_Process and starts them
 * as background processes. The progress of a started command is followed through
 * the bg-processes endpoint, using the returned bg_process_id.
 *
 * GET  tainacan/v2/management-tools            Lists the commands and their params.
 * POST tainacan/v2/management-tools/{command}  Starts a command. Body: the command params.
 */
class REST_Management_Tools_Controller extends REST_Controller {

	public function __construct() {
		$this->rest_base = 'management-tools';
		parent::__construct();
	}

	protected function get_schema() {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'management-tools',
			'type'       => 'object',
			'properties' => [
				'id'          => [ 'type' => 'string', 'description' => __( 'The command id.', 'tainacan' ) ],
				'name'        => [ 'type' => 'string', 'description' => __( 'The command name.', 'tainacan' ) ],
				'description' => [ 'type' => 'string', 'description' => __( 'The command description.', 'tainacan' ) ],
				'params'      => [ 'type' => 'array', 'description' => __( 'The params accepted by the command.', 'tainacan' ) ],
			],
		];
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'management_tools_permissions_check' ],
			],
		] );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<command>[a-z0-9_]+)', [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'run_command' ],
				'permission_callback' => [ $this, 'management_tools_permissions_check' ],
				'args'                => [
					'command' => [
						'description' => __( 'The command id.', 'tainacan' ),
						'type'        => 'string',
						'required'    => true,
					],
				],
			],
		] );
	}

	/**
	 * Management tools run heavy operations on the whole repository, so they are
	 * restricted to site administrators.
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function management_tools_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$response = [];
		foreach ( Management_Tools_Process::get_commands() as $id => $command ) {
			$response[] = [
				'id'          => $id,
				'name'        => $command['name'],
				'description' => $command['description'],
				'params'      => $command['params'],
			];
		}
		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function run_command( $request ) {
		$args = $request->get_json_params();
		if ( ! is_array( $args ) ) {
			$args = $request->get_body_params();
		}

		$bg_process = Management_Tools_Process::queue( $request['command'], is_array( $args ) ? $args : [] );
		if ( is_wp_error( $bg_process ) ) {
			return $bg_process;
		}

		return new \WP_REST_Response( [
			'command'       => $request['command'],
			'bg_process_id' => $bg_process->get_id(),
		], 201 );
	}
}
