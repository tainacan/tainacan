<?php

namespace Tainacan\GenericProcess;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Helpers\Logs_Wp_Posts_Migration;

/**
 * Background process that runs management commands (e.g. the legacy logs migration).
 *
 * The command to run and its args are given as process attributes. The first step
 * validates the command and its args; the following steps are the ones declared in
 * the command definition (see get_commands()), each one being a method of this class
 * that delegates the work to a helper, one batch per iteration.
 *
 * Usage:
 *
 *     $bg = Management_Tools_Process::queue( 'logs_migration', [ 'batch_size' => 100, 'purge' => true ] );
 *
 * To add a new command: add its definition to get_commands() and implement its step methods.
 */
class Management_Tools_Process extends Generic_Process {

	const SLUG = 'management_tools';

	/**
	 * Id of the command to run (a key of get_commands()).
	 * @var string|null
	 */
	private $command = null;

	/**
	 * Args given to the command, sanitized according to its params.
	 * @var array
	 */
	private $command_args = [];

	public function __construct( $attributes = array() ) {
		$this->array_attributes = array_merge( $this->array_attributes, [
			'command',
			'command_args',
		] );
		parent::__construct( $attributes );
		$this->init_objects();
	}

	/**
	 * Available commands, keyed by id. Each definition has:
	 * - name        (string)
	 * - description (string)
	 * - params      (array) Each entry: name, type (number|boolean|text), label, description,
	 *                       default, required, min, max (for numbers) and destructive (bool).
	 * - steps       (array) Each entry: name, progress_label, callback (method of this class)
	 *                       and, optionally, 'if' => param name: the step only runs when that
	 *                       param is truthy.
	 *
	 * @return array
	 */
	public static function get_commands() {
		return [
			'logs_migration' => [
				'name'        => __( 'Legacy logs migration', 'tainacan' ),
				'description' => __( 'Migrates the legacy activity logs, stored as posts in the wp_posts table, to the dedicated logs table. Already migrated logs are skipped, so it is safe to run it more than once. Optionally, it also deletes the legacy records that were already migrated.', 'tainacan' ),
				'params'      => [
					[
						'name'        => 'batch_size',
						'type'        => 'number',
						'label'       => __( 'Batch size', 'tainacan' ),
						'description' => __( 'Number of logs processed on each iteration.', 'tainacan' ),
						'default'     => 500,
						'min'         => 1,
						'max'         => 1000,
					],
					[
						'name'        => 'purge',
						'type'        => 'boolean',
						'label'       => __( 'Delete migrated legacy records from wp_posts', 'tainacan' ),
						'description' => __( 'Permanently deletes the legacy log records that were already migrated. It is strongly recommended to do a backup before.', 'tainacan' ),
						'default'     => false,
						'destructive' => true,
					],
				],
				'steps'       => [
					[
						'name'           => __( 'Migrate legacy logs', 'tainacan' ),
						'progress_label' => __( 'Migrating legacy logs to the logs table', 'tainacan' ),
						'callback'       => 'logs_migrate_step',
					],
					[
						'name'           => __( 'Purge legacy logs', 'tainacan' ),
						'progress_label' => __( 'Deleting migrated legacy logs from wp_posts', 'tainacan' ),
						'callback'       => 'logs_purge_step',
						'if'             => 'purge',
					],
				],
			],
		];
	}

	/**
	 * @param string $id
	 * @return array|null The command definition.
	 */
	public static function find_command( $id ) {
		$commands = self::get_commands();
		return isset( $commands[ $id ] ) ? $commands[ $id ] : null;
	}

	/**
	 * Validates the command and its args, and adds a new Management Tools process to the queue.
	 *
	 * @param string $command_id
	 * @param array  $args Raw args; they are sanitized according to the command params.
	 * @return \Tainacan\Background_Generic_Process|\WP_Error The queued background process.
	 */
	public static function queue( $command_id, array $args = [] ) {
		$command = self::find_command( $command_id );
		if ( ! $command ) {
			return new \WP_Error( 'tainacan_tool_command_not_found', __( 'Command not found.', 'tainacan' ), [ 'status' => 404 ] );
		}

		$args = self::sanitize_args( $command, $args );
		if ( is_wp_error( $args ) ) {
			return $args;
		}

		$handler = \Tainacan\Generic_Process_Handler::get_instance();
		$process = $handler->initialize_generic_process( self::SLUG );
		if ( ! $process instanceof self ) {
			return new \WP_Error( 'tainacan_tool_process_not_initialized', __( 'Unable to initialize the process.', 'tainacan' ), [ 'status' => 500 ] );
		}

		$process->set_command( $command_id );
		$process->set_command_args( $args );
		$process->init_objects();

		$bg_process = $handler->add_to_queue( $process, $command['name'] );
		if ( false === $bg_process ) {
			return new \WP_Error( 'tainacan_tool_process_not_queued', __( 'Unable to start the background process.', 'tainacan' ), [ 'status' => 500 ] );
		}
		return $bg_process;
	}

	/**
	 * Validates and sanitizes the args according to the command params.
	 * Params not declared are discarded.
	 *
	 * @param array $command The command definition.
	 * @param array $args    Raw args.
	 * @return array|\WP_Error
	 */
	public static function sanitize_args( array $command, array $args ) {
		$sanitized = [];
		$params    = isset( $command['params'] ) ? $command['params'] : [];

		foreach ( $params as $param ) {
			$name  = $param['name'];
			$type  = isset( $param['type'] ) ? $param['type'] : 'text';
			$label = isset( $param['label'] ) ? $param['label'] : $name;
			$value = array_key_exists( $name, $args ) ? $args[ $name ] : null;

			if ( is_null( $value ) || '' === $value ) {
				if ( ! empty( $param['required'] ) ) {
					/* translators: %s is the label of the required field */
					return new \WP_Error( 'tainacan_tool_param_required', sprintf( __( '%s must be provided.', 'tainacan' ), $label ), [ 'status' => 400 ] );
				}
				$sanitized[ $name ] = array_key_exists( 'default', $param ) ? $param['default'] : null;
				continue;
			}

			switch ( $type ) {
				case 'boolean':
					$sanitized[ $name ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
					break;

				case 'number':
					if ( ! is_numeric( $value ) ) {
						/* translators: %s is the label of the field */
						return new \WP_Error( 'tainacan_tool_param_invalid', sprintf( __( '%s must be a number.', 'tainacan' ), $label ), [ 'status' => 400 ] );
					}
					$value = (int) $value;
					if ( ( isset( $param['min'] ) && $value < $param['min'] ) || ( isset( $param['max'] ) && $value > $param['max'] ) ) {
						return new \WP_Error(
							'tainacan_tool_param_invalid',
							/* translators: 1: label of the field, 2: minimum value, 3: maximum value */
							sprintf( __( '%1$s must be between %2$s and %3$s.', 'tainacan' ), $label, isset( $param['min'] ) ? $param['min'] : '-', isset( $param['max'] ) ? $param['max'] : '-' ),
							[ 'status' => 400 ]
						);
					}
					$sanitized[ $name ] = $value;
					break;

				default:
					$sanitized[ $name ] = sanitize_text_field( (string) $value );
			}
		}

		return $sanitized;
	}

	/**
	 * Builds the steps: the "prepare" step followed by the command steps enabled by the args.
	 * Called on every iteration, since steps are not serialized between requests.
	 */
	public function init_objects() {
		$this->steps = [
			[
				'name'           => __( 'Prepare command', 'tainacan' ),
				'progress_label' => __( 'Preparing command', 'tainacan' ),
				'callback'       => 'prepare_command',
			],
		];

		$command = self::find_command( (string) $this->command );
		if ( ! $command ) {
			return;
		}

		foreach ( $command['steps'] as $step ) {
			if ( isset( $step['if'] ) && empty( $this->command_args[ $step['if'] ] ) ) {
				continue;
			}
			// Totals are counted on the first execution of each step (see start_step_count()).
			$step['total']  = (int) $this->get_transient( 'total_' . $step['callback'] );
			$this->steps[] = $step;
		}
	}

	public function set_command( $command ) {
		$this->command = $command ? (string) $command : null;
	}

	public function get_command() {
		return $this->command;
	}

	public function set_command_args( $args ) {
		$this->command_args = is_array( $args ) ? $args : [];
	}

	public function get_command_args() {
		return $this->command_args;
	}

	/* ---------------------------------------------------------------------
	 * Generic steps
	 * ------------------------------------------------------------------- */

	/**
	 * Step 1: validates the command and its args.
	 *
	 * @return false Always moves on to the next step (or aborts).
	 */
	public function prepare_command() {
		$command = self::find_command( (string) $this->command );
		if ( ! $command ) {
			/* translators: %s is the command id */
			$this->add_error_log( sprintf( __( 'Command "%s" not found.', 'tainacan' ), (string) $this->command ) );
			$this->abort();
			return false;
		}

		$args = self::sanitize_args( $command, $this->command_args );
		if ( is_wp_error( $args ) ) {
			$this->add_error_log( $args->get_error_message() );
			$this->abort();
			return false;
		}
		$this->command_args = $args;

		/* translators: %s is the command name */
		$this->add_log( sprintf( __( 'Command: %s', 'tainacan' ), $command['name'] ) );
		/* translators: %s is the list of args, as JSON */
		$this->add_log( sprintf( __( 'Arguments: %s', 'tainacan' ), wp_json_encode( $args ) ) );

		// Rebuild the steps with the sanitized args.
		$this->init_objects();
		return false;
	}

	/**
	 * Stores the total of records of the current step on its first execution,
	 * so the progress bar keeps a fixed reference while records are processed.
	 *
	 * @param callable $counter Returns the total of records to process.
	 */
	private function start_step_count( callable $counter ) {
		$step = $this->get_steps()[ $this->get_current_step() ];
		$key  = 'total_' . $step['callback'];
		if ( null !== $this->get_transient( $key ) ) {
			return;
		}
		$total = (int) call_user_func( $counter );
		$this->add_transient( $key, $total );
		$this->set_current_step_total( $total );
		/* translators: 1: step name, 2: number of records to process */
		$this->add_log( sprintf( __( '%1$s: %2$d record(s) to process.', 'tainacan' ), $step['name'], $total ) );
	}

	/**
	 * Accumulates the number of records processed by a step, used in the final output.
	 *
	 * @param string $key
	 * @param int    $count
	 * @return int The accumulated count of the current step.
	 */
	private function add_processed( $key, $count ) {
		$done      = $this->get_in_step_count() + (int) $count;
		$processed = (array) $this->get_transient( 'processed' );
		$processed[ $key ] = $done;
		$this->add_transient( 'processed', $processed );
		return $done;
	}

	/**
	 * Message stored as the background process output when it finishes.
	 * Required by Background_Generic_Process::task().
	 *
	 * @return string
	 */
	public function get_output() {
		$processed = (array) $this->get_transient( 'processed' );

		switch ( $this->command ) {
			case 'logs_migration':
				/* translators: %d is the number of logs migrated */
				$output = sprintf( __( 'Legacy logs migration finished. %d log(s) migrated.', 'tainacan' ), (int) ( $processed['migrated'] ?? 0 ) );
				if ( ! empty( $this->command_args['purge'] ) ) {
					/* translators: %d is the number of legacy records deleted */
					$output .= ' ' . sprintf( __( '%d legacy record(s) deleted from wp_posts.', 'tainacan' ), (int) ( $processed['deleted'] ?? 0 ) );
				}
				return $output;
		}

		$command = self::find_command( (string) $this->command );
		/* translators: %s is the name of the command */
		return $command ? sprintf( __( '%s finished.', 'tainacan' ), $command['name'] ) : __( 'Command not found.', 'tainacan' );
	}

	/* ---------------------------------------------------------------------
	 * Command: logs_migration (helper: Helpers\Logs_Wp_Posts_Migration)
	 * ------------------------------------------------------------------- */

	/**
	 * Migrates one batch of legacy logs.
	 *
	 * @return int|false
	 */
	public function logs_migrate_step() {
		$migration = new Logs_Wp_Posts_Migration();
		$this->start_step_count( [ $migration, 'count_pending' ] );

		$result = $migration->run_batch( $this->get_batch_size() );
		$done   = $this->add_processed( 'migrated', $result['migrated'] );

		if ( $result['migrated'] > 0 ) {
			/* translators: 1: number of logs migrated in this batch, 2: number of logs still pending */
			$this->add_log( sprintf( __( 'Batch migrated: %1$d. Pending: %2$d.', 'tainacan' ), $result['migrated'], $result['pending'] ) );
		}

		return $this->batch_result( $done, $result['migrated'], $result['pending'] );
	}

	/**
	 * Deletes one batch of already migrated legacy logs.
	 *
	 * @return int|false
	 */
	public function logs_purge_step() {
		$migration = new Logs_Wp_Posts_Migration();
		$this->start_step_count( [ $migration, 'count_purgeable' ] );

		$result = $migration->purge_batch( $this->get_batch_size() );
		$done   = $this->add_processed( 'deleted', $result['deleted'] );

		if ( $result['deleted'] > 0 ) {
			/* translators: 1: number of records deleted in this batch, 2: number of records still pending */
			$this->add_log( sprintf( __( 'Batch deleted: %1$d. Pending: %2$d.', 'tainacan' ), $result['deleted'], $result['pending'] ) );
		}

		return $this->batch_result( $done, $result['deleted'], $result['pending'] );
	}

	/* ---------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------- */

	/**
	 * @return int The 'batch_size' arg, or 500 when not given.
	 */
	private function get_batch_size() {
		return isset( $this->command_args['batch_size'] ) ? max( 1, absint( $this->command_args['batch_size'] ) ) : 500;
	}

	/**
	 * Return value of a batch step, following the Generic_Process convention.
	 * Stops (aborting) when a batch makes no progress, to avoid an infinite loop.
	 *
	 * @param int $done      Accumulated number of processed records in the step.
	 * @param int $processed Number of records processed in this batch.
	 * @param int $pending   Number of records still pending.
	 * @return int|false
	 */
	private function batch_result( $done, $processed, $pending ) {
		if ( $pending <= 0 ) {
			return false;
		}
		if ( $processed <= 0 ) {
			$this->add_error_log( __( 'No record could be processed in the last batch. Stopping to avoid an infinite loop.', 'tainacan' ) );
			$this->abort();
			return false;
		}
		return $done;
	}
}
