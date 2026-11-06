<?php

namespace Tainacan\API\EndPoints;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Tainacan\API\REST_Controller;
use Tainacan\Repositories;
use Tainacan\Entities;

/**
 * REST API controller for managing Tainacan background processes.
 *
 * Handles all REST API endpoints for background process operations including
 * process monitoring, status checking, and process management.
 *
 * @since 1.0.0
 */
class REST_Background_Processes_Controller extends REST_Controller {
    
    /**
	 * table
	 *
	 * @var String
	 * @access protected
	 */
    private $table;

    protected function get_schema() {
        return "TODO:get_schema";
    }

	/**
	 * REST_Background_Processes_Controller constructor.
	 * Define the namespace, rest base and instantiate your attributes.
	 */
	public function __construct(){
        global $wpdb;
        $this->rest_base = 'bg-processes';
        $this->table = $wpdb->prefix . 'tnc_bg_process';
		parent::__construct();
    }

	/**
	 * Register the BG Processes route and their endpoints
	 */
	public function register_routes(){
        register_rest_route($this->namespace, '/' . $this->rest_base , array(
	        array(
		        'methods'             => \WP_REST_Server::READABLE,
		        'callback'            => array($this, 'get_items'),
		        'permission_callback' => array($this, 'bg_processes_permissions_check'),
		        'args'                => [
                    'user_id' => [
                        'type'        => 'integer',
                        'description' => __( 'The ID of the owner of the background processes. Defaults to current user', 'tainacan' ),
                    ],
                    'all_users' => [
                        'type'        => 'boolean',
                        'description' => __( 'Whether to return processes from all users (if current user is admin).', 'tainacan' ),
                        'default'     => false,
                    ],
                    'status' => [
                        'type'        => 'string',
                        'description' => __( '"open" returns only processes currently running. "closed" returns only finished or aborted. "all" returns all.', 'tainacan' ),
                        'default'     => 'all',
                        'enum'        => array(
                            'open',
                            'closed',
                            'all'
                        )
                    ],
                    'perpage' => [
                        'type'        => 'integer',
                        'description' => __( 'Number of processes to return per page', 'tainacan' ),
                        'default'     => 10,
                    ],
                    'paged' => [
                        'type'        => 'integer',
                        'description' => __( 'Page to retrieve', 'tainacan' ),
                        'default'     => 1
                    ],
					'recent' => [
                        'type'        => 'boolean',
                        'description' => __( 'Returns only processes created or updated recently', 'tainacan' ),
                        'default' => false
                    ],
                ],
	        ),
        ));
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[0-9]+)', array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_item'),
                'permission_callback' => array($this, 'bg_processes_permissions_check'),
            ),

        ));
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[0-9]+)', array(
            array(
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'update_item'),
                'permission_callback' => array($this, 'bg_processes_permissions_check'),
                'args'                => [
                    'status' => [
                        'type'        => 'string',
                        'description' => __( '"open" or "closed" ', 'tainacan' ),
                        'enum'    	  => array(
                            'open',
                            'closed'
                        )
                    ]
                ],
            ),

        ));
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[0-9]+)', array(
            array(
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => array($this, 'delete_item'),
                'permission_callback' => array($this, 'bg_processes_permissions_check'),

            ),

        ));
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/file',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_file' ),
					'permission_callback' => array(
						$this,
						'bg_processes_permissions_check',
					),
					'args'                => array(
						'guid'       => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'description'       => __(
								'File identifier.',
								'tainacan'
							),
						),
						'process_id' => array(
							'type'        => 'integer',
							'required'    => false,
							'minimum'     => 1,
							'description' => __(
								'Background process ID associated with an exporter file.',
								'tainacan'
							),
						),
					),
				),
			)
		);
    }

	/**
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function  bg_processes_permissions_check($request) {
        return current_user_can('manage_tainacan');
	}

    public function get_items( $request ) {
        global $wpdb;

        $perpage = isset($request['perpage']) && is_numeric($request['perpage']) ? (int) $request['perpage'] : 10;
        if ($perpage < 1) {
            $perpage = 1;
        }
        if ($perpage > 100) {
            $perpage = 100;
        }
        $paged = isset($request['paged']) && is_numeric($request['paged']) ? (int) $request['paged'] : 1;
        if ($paged < 1) {
            $paged = 1;
        }

        $offset = ($paged - 1) * $perpage;

        $limit_q = $wpdb->prepare("LIMIT %d, %d", $offset, $perpage);

        $user_q = $wpdb->prepare("AND user_id = %d", get_current_user_id());
        $status_q = "";

        $date_range = '';

        if (current_user_can('edit_users')) {
            if (isset($request['user_id'])) {
                $user_q = $wpdb->prepare("AND user_id = %d", $request['user_id']);
            }

            if ( isset($request['all_users']) && $request['all_users'] ) {
                $user_q = "";
            }
        }

        if ( isset($request['status']) && $request['status'] != 'all' ) {
            if ( $request['status'] == 'open' ) {
                $status_q = "AND done = 0";
            }
            if ( $request['status'] == 'closed' ) {
                $status_q = "AND done = 1";
            }
        }

        if (isset($request['datequery'])) {
            $from = $request['datequery'][0]['after'];
            $to = $request['datequery'][0]['before'];
            $date_range = $wpdb->prepare("AND processed_last >= %s AND processed_last <= %s", $from, $to);
        }

        $process_type = '';
        if (isset($request['search'])) {
            $name = $request['search'];
            $search_term_like = '%' . $wpdb->esc_like($name) . '%';
            $process_type = $wpdb->prepare("AND name LIKE %s", $search_term_like);
        }

		$recent_q = '';
		if ( isset($request['recent']) && $request['recent'] !== false ) {
            $recent_q = "AND (processed_last >= NOW() - INTERVAL 10 MINUTE OR queued_on >= NOW() - INTERVAL 10 MINUTE)";
        }

        $base_query = "FROM $this->table WHERE 1=1 $status_q $user_q $recent_q $date_range $process_type ORDER BY priority DESC, queued_on DESC";

        $query = "SELECT * $base_query $limit_q";
        $count_query = "SELECT COUNT(ID) $base_query";

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Queries only contain the table name, fixed SQL fragments and fragments built with $wpdb->prepare().
        $result = $wpdb->get_results($query);
        $total_items = $wpdb->get_var($count_query);
        // phpcs:enable WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

        $response = [];

        foreach ($result as $r) {
            $response[] = $this->prepare_item_for_response($r, $request);
        }

        $rest_response = new \WP_REST_Response( $response, 200 );

        $max_pages = ceil($total_items / (int) $perpage);

		$rest_response->header('X-WP-Total', (int) $total_items);
        $rest_response->header('X-WP-TotalPages', (int) $max_pages);

        return $rest_response;
    }

    public function get_item( $request ) {
        global $wpdb;
        $id = $request['id'];

        $user_q = $wpdb->prepare("AND user_id = %d", get_current_user_id());
        $id_q = $wpdb->prepare("AND ID = %d", $id);

        if (current_user_can('edit_users')) {
            if ( isset($request['all_users']) && $request['all_users'] ) {
                $user_q = "";
            }
        }

        $query = "SELECT * FROM $this->table WHERE 1=1 $id_q $user_q LIMIT 1";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query only contains the table name and fragments built with $wpdb->prepare().
        $result = $wpdb->get_row($query);

        if ( ! $result ) {
            return new \WP_REST_Response([
                'error_message' => __('Process not found', 'tainacan'),
            ], 404);
        }

        $result = $this->prepare_item_for_response($result, $request);

        return new \WP_REST_Response( $result, 200 );
    }

	public function prepare_item_for_response( $item, $request ) {
		if ( ! is_object( $item ) ) {
			return $item;
		}

		$item = \Tainacan\Exporter_Files::get_instance()
			->prepare_process_for_response( $item );

		$key_log = $item->bg_uuid ?? $item->ID;
		$item->log = $this->get_log_url( $key_log, $item->action );
		$item->error_log = $this->get_log_url(
			$key_log,
			$item->action,
			'error'
		);

		$nonce = wp_create_nonce( 'wp_rest' );
		$item->output = $item->output ?? '';
		$item->output = str_replace(
			'&_wpnonce=[nonce]',
			'&_wpnonce=' . $nonce,
			$item->output
		);

		if (
			! empty( $item->exporter_files ) &&
			is_array( $item->exporter_files )
		) {
			foreach ( $item->exporter_files as &$file ) {
				if ( ! empty( $file['url'] ) ) {
					$file['url'] = str_replace(
						'&_wpnonce=[nonce]',
						'&_wpnonce=' . $nonce,
						$file['url']
					);
				}
			}
			unset( $file );
		}

		return $item;
	}

    public function update_item( $request ) {
        global $wpdb;
        $id = (int) $request['id'];
        $body = json_decode($request->get_body(), true);

        $allowed_statuses = [
            'open'            => [ 'done' => 0, 'status' => '' ],
            'closed'          => [ 'done' => 1, 'status' => 'cancelled' ],
            'waiting'         => [ 'done' => 1, 'status' => 'waiting' ],
            'running'         => [ 'done' => 1, 'status' => 'running' ],
            'paused'          => [ 'done' => 1, 'status' => 'paused' ],
            'cancelled'       => [ 'done' => 1, 'status' => 'cancelled' ],
            'errored'         => [ 'done' => 1, 'status' => 'errored' ],
            'finished'        => [ 'done' => 1, 'status' => 'finished' ],
            'finished-errors' => [ 'done' => 1, 'status' => 'finished-errors' ],
        ];

        if ( ! isset($body['status']) || ! isset($allowed_statuses[$body['status']]) ) {
            return new \WP_REST_Response([
                'error_message' => __('Status must be specified', 'tainacan' ),
                'session_id' => $id
            ], 400);
        }

        $where = [ 'ID' => $id ];
        $where_format = [ '%d' ];

        if ( ! current_user_can('edit_users') || ! isset($request['all_users']) || ! $request['all_users'] ) {
            $where['user_id'] = get_current_user_id();
            $where_format[] = '%d';
        }

        $id_q = $wpdb->prepare("AND ID = %d", $id);
        $user_q = '';
        if ( isset($where['user_id']) ) {
            $user_q = $wpdb->prepare("AND user_id = %d", $where['user_id']);
        }

        $query = "SELECT * FROM $this->table WHERE 1=1 $id_q $user_q LIMIT 1";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query only contains the table name and fragments built with $wpdb->prepare().
        $result = $wpdb->get_row($query);

        if ( ! $result ) {
            return new \WP_REST_Response([
                'error_message' => __('Process not found', 'tainacan'),
            ], 404);
        }

        if ( $body['status'] === 'closed' && $result->action === 'import' ) {
            $background_importer = new \Tainacan\Background_Importer();
            $background_importer->close( $result->ID, 'cancelled' );
        } else {
            $update_data = $allowed_statuses[$body['status']];
            if ( $update_data['status'] === '' ) {
                $update_data = [ 'done' => 0 ];
                $update_format = [ '%d' ];
            } else {
                $update_format = [ '%d', '%s' ];
            }

            $wpdb->update( $this->table, $update_data, $where, $update_format, $where_format );
        }

        $query = "SELECT * FROM $this->table WHERE 1=1 $id_q $user_q LIMIT 1";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $query only contains the table name and fragments built with $wpdb->prepare().
        $result = $wpdb->get_row($query);

        $result = $this->prepare_item_for_response($result, $request);

        return new \WP_REST_Response( $result, 200 );
    }

	public function delete_item( $request ) {
		global $wpdb;

		$id = absint( $request['id'] );

		if ( empty( $id ) ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'invalid_background_process',
					'message' => __(
						'Invalid background process.',
						'tainacan'
					),
					'data'    => array( 'status' => 400 ),
				),
				400
			);
		}

		$process = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE ID = %d LIMIT 1",
				$id
			)
		);

		if ( ! $process ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'background_process_not_found',
					'message' => __(
						'Background process not found.',
						'tainacan'
					),
					'data'    => array( 'status' => 404 ),
				),
				404
			);
		}

		if (
			(int) $process->user_id !== get_current_user_id() &&
			! current_user_can( 'edit_users' )
		) {
			return new \WP_REST_Response(
				array(
					'code'    => 'background_process_forbidden',
					'message' => __(
						'You are not allowed to delete this background process.',
						'tainacan'
					),
					'data'    => array( 'status' => 403 ),
				),
				403
			);
		}

		/*
		 * Exporter files must be removed before their process reference
		 * is deleted from the database.
		 */
		\Tainacan\Exporter_Files::get_instance()
			->delete_process_files( $process );

		$result = $wpdb->delete(
			$this->table,
			array( 'ID' => $id ),
			array( '%d' )
		);

		// TODO: delete log files.

		return new \WP_REST_Response( $result, 200 );
	}

    public function get_log_url($id, $action, $type = '') {
        $suffix = $type ? '-' . $type : '';

        $filename = 'bg-' . $action . '-' . $id . $suffix . '.log';

        $upload_url = wp_upload_dir();

        if (!file_exists( $upload_url['basedir'] . '/tainacan/' . $filename )) {
            return null;
        }
        $nonce = wp_create_nonce( 'wp_rest' );
        $logs_url = esc_url_raw( rest_url() ) . "tainacan/v2/bg-processes/file?guid=$filename&_wpnonce=$nonce";
        return $logs_url;
    }

	public function get_file( $request ) {
		if ( empty( $request['guid'] ) ) {
			return new \WP_REST_Response(
				array(
					'error_message' => __(
						'guid must be specified',
						'tainacan'
					),
				),
				400
			);
		}

		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'unauthorized',
					'message' => __( 'Unauthorized', 'tainacan' ),
					'data'    => array( 'status' => 403 ),
				),
				403
			);
		}

		$guid = $request['guid'];

		/*
		 * Reject traversal and absolute-path attempts before normalizing
		 * and resolving the requested file.
		 */
		if (
			strpos( $guid, '..' ) !== false ||
			preg_match( '#^([a-zA-Z]:)?[\\\\/]#', $guid )
		) {
			return new \WP_REST_Response(
				array(
					'code'    => 'unauthorized_file_path',
					'message' => __( 'Unauthorized file path', 'tainacan' ),
					'data'    => array( 'status' => 403 ),
				),
				403,
				array( 'content-type' => 'application/json; charset=utf-8' )
			);
		}

		$upload_dir   = wp_upload_dir();
		$tainacan_dir = realpath(
			trailingslashit( $upload_dir['basedir'] ) . 'tainacan'
		);

		if ( false === $tainacan_dir ) {
			return new \WP_REST_Response(
				array(
					'error_message' => __(
						'Base directory not found',
						'tainacan'
					),
				),
				404
			);
		}

		$guid           = sanitize_text_field(
			wp_unslash( $request['guid'] )
		);
		$normalized_guid = ltrim( wp_normalize_path( $guid ), '/' );
		$exporter_files = \Tainacan\Exporter_Files::get_instance();
        if ( strpos( $normalized_guid, 'exporter/' ) === 0 ) {
			if ( empty( $request['process_id'] ) ) {
				return new \WP_REST_Response(
					array(
						'code'    => 'missing_exporter_process',
						'message' => __(
							'The exporter process must be specified.',
							'tainacan'
						),
						'data'    => array( 'status' => 400 ),
					),
					400
				);
			}

			$file = $exporter_files->get_process_file(
				$request['process_id'],
				$normalized_guid
			);

			if ( is_wp_error( $file ) ) {
				return rest_convert_error_to_response( $file );
			}

			$path = $file['path'];
		} else {
			/*
			 * Log files keep their existing authentication behavior because
			 * they are not exporter output files.
			 */
			$path = $exporter_files->get_file_path( $normalized_guid );

			if ( is_wp_error( $path ) ) {
				return rest_convert_error_to_response( $path );
			}
		}

		$finfo     = @finfo_open( FILEINFO_MIME_TYPE );
		$mime_type = $finfo ? @finfo_file( $finfo, $path ) : null;
		$file_name = basename( $path );

		if ( $finfo ) {
			finfo_close( $finfo );
		}

		http_response_code( 200 );
		header( 'Content-Description: File Transfer' );
		header(
			'Content-Disposition: attachment; filename="'
			. $file_name
			. '"'
		);
		header(
			'Content-Type: '
			. ( $mime_type ?: 'application/octet-stream' )
		);
		header( 'Content-Length: ' . filesize( $path ) );

		if ( \ob_get_level() > 0 ) {
			\ob_clean();
		}

		\flush();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Stream the file to the response. WP_Filesystem::get_contents() would load the whole file into memory.
		\readfile( $path );
		exit;
	}
}
