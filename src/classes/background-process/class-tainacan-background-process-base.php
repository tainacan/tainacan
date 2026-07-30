<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Abstract base class for background processes in Tainacan.
 *
 * Extends Async_Request to provide background processing capabilities
 * for long-running tasks like imports, exports, and bulk operations.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Background_Process_Base extends Async_Request {

	/**
	 * Action name for background processes.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $action = 'background_process';

	/**
	 * Start time of the current process.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $start_time = 0;

	/**
	 * Cron hook identifier for scheduling.
	 *
	 * @since 1.0.0
	 *
	 * @var mixed
	 */
	protected $cron_hook_identifier;

	/**
	 * Cron interval identifier for scheduling.
	 *
	 * @since 1.0.0
	 *
	 * @var mixed
	 */
	protected $cron_interval_identifier;

	/**
	 * cron_hook_check_identifier
	 *
	 * @var string
	 * @access protected
	 */
	protected $cron_hook_check_identifier;

	/**
	 * process_lock_in_time
	 *
	 * @var string
	 * @access protected
	 */
	protected $process_lock_in_time;

	/**
	 * Whether the current request is being dispatched via WP-Cron internally.
	 *
	 * When true, the nonce check in maybe_handle() is bypassed because cron
	 * dispatches are internal server-side requests that don't carry a nonce.
	 *
	 * @since 1.2.1
	 * @var bool
	 * @access protected
	 */
	protected $is_cron_dispatch = false;

	/**
	 * Maximum time (in seconds) a process can remain in 'running' status
	 * without updating its processed_last timestamp before being considered stale.
	 *
	 * @since 1.2.1
	 * @var int
	 * @access protected
	 */
	protected $stale_process_timeout = 900; // 15 minutes
	
	/**
	 * queue_lock_time
	 *
	 * @var string
	 * @access protected
	 */
	protected $queue_lock_time;
	
	/**
	 * cron_interval
	 *
	 * @var string
	 * @access protected
	 */
	protected $cron_interval = 5;

	/**
	 * Initiate new background process
	 */
	public function __construct() {
		parent::__construct();

		$this->cron_hook_identifier     = $this->identifier . '_cron';
		$this->cron_interval_identifier = $this->identifier . '_cron_interval';
		$this->cron_hook_check_identifier = $this->identifier . '_cron_check';

		add_action( $this->cron_hook_identifier, array( $this, 'handle_cron_healthcheck' ) );
		add_action( $this->cron_hook_check_identifier, array( $this, 'handle_cron_healthcheck_check' ) );
		add_filter( 'cron_schedules', array( $this, 'schedule_cron_healthcheck' ) );

		if ( ! wp_next_scheduled( $this->cron_hook_check_identifier ) ) {
			wp_schedule_event( time(), $this->cron_interval_identifier, $this->cron_hook_check_identifier );
		}
	}

	/**
	 * Dispatch
	 *
	 * @access public
	 * @return void
	 */
	public function dispatch() {
		// Schedule the cron healthcheck.
		$this->schedule_event();

		// Perform remote post.
		return parent::dispatch();
	}

	/**
	 * Push to queue
	 *
	 * @param mixed $data Data.
	 *
	 * @return $this
	 */
	public function push_to_queue( $data ) {
		$this->data[] = $data;

		return $this;
	}

	/**
	 * Save queue
	 *
	 * @return $this
	 */
	public function save() {
		$key = $this->generate_key();

		if ( ! empty( $this->data ) ) {
			update_site_option( $key, $this->data );
		}

		return $this;
	}

	/**
	 * Update queue
	 *
	 * @param string $key Key.
	 * @param array  $data Data.
	 *
	 * @return $this
	 */
	public function update( $key, $data ) {
		if ( ! empty( $data ) ) {
			update_site_option( $key, $data );
		}

		return $this;
	}

	/**
	 * Delete queue
	 *
	 * @param string $key Key.
	 *
	 * @return $this
	 */
	public function delete( $key ) {
		delete_site_option( $key );

		return $this;
	}

	/**
	 * Generate key
	 *
	 * Generates a unique key based on microtime. Queue items are
	 * given a unique key so that they can be merged upon save.
	 *
	 * @param int $length Length.
	 *
	 * @return string
	 */
	protected function generate_key( $length = 64 ) {
		$unique  = md5( microtime() . wp_rand() );
		$prepend = $this->identifier . '_batch_';

		return substr( $prepend . $unique, 0, $length );
	}

	/**
	 * Maybe process queue
	 *
	 * Checks whether data exists within the queue and that
	 * the process is not already running.
	 */
	public function maybe_handle() {
		// Don't lock up other requests while processing
		session_write_close();

		// Verify nonce to prevent unauthorized external triggering of background processes.
		// The nonce is generated in Async_Request::get_query_args() and sent with the dispatch request.
		// When dispatched via WP-Cron (which runs internally), the nonce check is bypassed
		// via the $this->is_cron_dispatch flag.
		if ( ! $this->is_cron_dispatch ) {
			$this->debug('checking nonce');
			check_ajax_referer( $this->identifier, 'nonce' );
			$this->debug('nonce ok!');
		}

		if ( $this->is_process_running() ) {
			$this->debug('process already running. To die...');
			// Background process already running.
			wp_die();
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			wp_die();
		}
		$this->handle();

		wp_die();
	}

	/**
	 * Is queue empty
	 *
	 * @return bool
	 */
	protected function is_queue_empty() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		}

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$count = $wpdb->get_var( $wpdb->prepare( "
		SELECT COUNT(*)
		FROM {$table}
		WHERE {$column} LIKE %s
	", $key ) );

		return ( $count > 0 ) ? false : true;
	}

	/**
	 * Is process running
	 *
	 * Check whether the current process is already running
	 * in a background process.
	 */
	protected function is_process_running() {
		$this->debug('Checking if process ' . $this->identifier . ' is running:');
		if ( get_site_transient( $this->identifier . '_process_lock' ) ) {
			// Process already running.
			$this->debug('process already running');
			return true;
		}
		$this->debug('process not already running');

		return false;
	}

	/**
	 * Lock process
	 *
	 * Lock the process so that multiple instances can't run simultaneously.
	 * Override if applicable, but the duration should be greater than that
	 * defined in the time_exceeded() method.
	 */
	protected function lock_process() {
		$this->debug('locking process: ' . $this->identifier);
		$this->start_time = time(); // Set start time of current process.
		$max_execution_time = ini_get('max_execution_time');
		$lock_duration = ( property_exists( $this, 'queue_lock_time' ) && !empty($this->queue_lock_time) ) ? $this->queue_lock_time : ( empty($max_execution_time) ? 60 : ($max_execution_time * 1.5) ); // 1 minute
		$lock_duration = apply_filters( $this->identifier . '_queue_lock_time', $lock_duration );
		$this->process_lock_in_time = microtime();
		$this->debug('locking duration: ' . $lock_duration);
		if(!$this->is_process_running())
			set_site_transient( $this->identifier . '_process_lock', $this->process_lock_in_time, $lock_duration );
	}

	/**
	 * Unlock process
	 *
	 * Unlock the process so that other instances can spawn.
	 *
	 * @return $this
	 */
	protected function unlock_process() {
		$this->debug('unlocking process: '. $this->identifier . '_process_lock');
		global $wpdb;
		$wpdb->query('START TRANSACTION');
		delete_site_transient( $this->identifier . '_process_lock' );
		$wpdb->query('COMMIT');

		return $this;
	}

	/**
	 * Get batch
	 *
	 * @return stdClass Return the first batch from the queue
	 */
	protected function get_batch() {
		global $wpdb;

		$table        = $wpdb->options;
		$column       = 'option_name';
		$key_column   = 'option_id';
		$value_column = 'option_value';

		if ( is_multisite() ) {
			$table        = $wpdb->sitemeta;
			$column       = 'meta_key';
			$key_column   = 'meta_id';
			$value_column = 'meta_value';
		}

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$query = $wpdb->get_row( $wpdb->prepare( "
		SELECT *
		FROM {$table}
		WHERE {$column} LIKE %s
		ORDER BY {$key_column} ASC
		LIMIT 1
	", $key ) );

		$batch       = new stdClass();
		$batch->key  = $query->$column;
		$batch->data = maybe_unserialize( $query->$value_column );

		return $batch;
	}

	/**
	 * Handle
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	protected function handle() {
		$this->lock_process();

		do {
			$batch = $this->get_batch();

			foreach ( $batch->data as $key => $value ) {
				$task = $this->task( $value );

				if ( false !== $task ) {
					$batch->data[ $key ] = $task;
				} else {
					unset( $batch->data[ $key ] );
				}

				if ( $this->time_exceeded() || $this->memory_exceeded() ) {
					// Batch limits reached.
					break;
				}
			}

			// Update or delete current batch.
			if ( ! empty( $batch->data ) ) {
				$this->update( $batch->key, $batch->data );
			} else {
				$this->delete( $batch->key );
			}
		} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

		$this->unlock_process();

		// Start next batch or complete process.
		if ( ! $this->is_queue_empty() ) {
			$this->dispatch();
		} else {
			$this->complete();
		}

		wp_die();
	}

	/**
	 * Memory exceeded
	 *
	 * Ensures the batch process never exceeds 90%
	 * of the maximum WordPress memory.
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.9; // 90% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;

		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		return apply_filters( $this->identifier . '_memory_exceeded', $return );
	}

	/**
	 * Get memory limit
	 *
	 * @return int
	 */
	protected function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			// Sensible default.
			$memory_limit = '128M';
		}

		if ( ! $memory_limit || -1 === intval( $memory_limit ) ) {
			// Unlimited, set to 32GB.
			$memory_limit = '32000M';
		}

		return intval( $memory_limit ) * 1024 * 1024;
	}

	/**
	 * Time exceeded.
	 *
	 * Ensures the batch never exceeds a sensible time limit.
	 * A timeout limit of 30s is common on shared hosting.
	 *
	 * @return bool
	 */
	protected function time_exceeded() {
		$finish = $this->start_time + apply_filters( $this->identifier . '_default_time_limit', 20 ); // 20 seconds
		$return = false;

		if ( time() >= $finish ) {
			$return = true;
		}

		return apply_filters( $this->identifier . '_time_exceeded', $return );
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		// Unschedule the cron healthcheck.
		$this->clear_scheduled_event();
	}

	/**
	 * Schedule cron healthcheck
	 *
	 * @access public
	 * @param mixed $schedules Schedules.
	 * @return mixed
	 */
	public function schedule_cron_healthcheck( $schedules ) {
		$interval = apply_filters( $this->identifier . '_cron_interval', 5 );

		if ( property_exists( $this, 'cron_interval' ) ) {
			$interval = apply_filters( $this->identifier . '_cron_interval', $this->cron_interval );
		}

		// Adds every 5 minutes to the existing schedules.
		$schedules[ $this->identifier . '_cron_interval' ] = array(
			'interval' => MINUTE_IN_SECONDS * $interval,
			/* translators: %d is the number of minutes */
			'display'  => sprintf( __( 'Every %d Minutes', 'tainacan' ), $interval ),
		);

		return $schedules;
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		$this->debug('running handle_cron_healthcheck');

		// Run the watchdog to detect and clean up stale processes before dispatching.
		$this->detect_and_cleanup_stale_processes();

		if ( $this->is_process_running() ) {
			// Background process already running.
			$this->debug('running handle_cron_healthcheck: process running');
			exit;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->debug('running handle_cron_healthcheck: queue empty');
			$this->clear_scheduled_event();
			exit;
		}

		$this->debug('running handle_cron_healthcheck: dispatching');
		// Mark that this dispatch originates from WP-Cron so maybe_handle() bypasses the nonce check.
		$this->is_cron_dispatch = true;
		$this->dispatch();

		exit;
	}

	/**
	 * Checks the healthcheck
	 * 
	 * If there is an open process, not running, and not scheduled. schedule it.
	 *
	 */
	public function handle_cron_healthcheck_check() {
		// Run the watchdog to detect and clean up stale processes.
		$this->detect_and_cleanup_stale_processes();

		if ( $this->is_process_running() ) {
			// Background process already running.
			exit;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			exit;
		}

		$this->debug('handle_cron_healthcheck_check scheduling event');
		$this->schedule_event();

	}

	/**
	 * Detect and clean up stale processes.
	 *
	 * A stale process is one whose status is 'running' in the database but
	 * whose processed_last timestamp has not been updated within the
	 * $stale_process_timeout window. This typically happens when a PHP
	 * process was killed by the server, hit a fatal error not caught by
	 * the shutdown handler, or the server crashed.
	 *
	 * This watchdog marks stale processes as 'errored' and releases the lock
	 * so that the cron healthcheck can re-dispatch queued work.
	 *
	 * @since 1.2.1
	 * @return void
	 */
	protected function detect_and_cleanup_stale_processes() {
		global $wpdb;

		// Only applies to the DB-backed Background_Process, which defines $this->table.
		if ( empty( $this->table ) ) {
			return;
		}

		$stale_timeout = apply_filters( $this->identifier . '_stale_process_timeout', $this->stale_process_timeout );
		$cutoff_date = gmdate( 'Y-m-d H:i:s', time() - $stale_timeout );

		// Find processes that are still marked as 'running' but haven't updated recently.
		$stale_processes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM {$this->table}
				 WHERE action = %s
				 AND status = 'running'
				 AND done = 0
				 AND processed_last IS NOT NULL
				 AND processed_last < %s",
				$this->action,
				$cutoff_date
			)
		);

		if ( empty( $stale_processes ) ) {
			return;
		}

		foreach ( $stale_processes as $stale ) {
			$this->debug( sprintf( 'Watchdog: marking stale process ID %d as errored', $stale->ID ) );
			$this->close( $stale->ID, 'errored' );
			$this->write_error_log( $stale->ID, [
				[
					'datetime' => gmdate( 'Y-m-d H:i:s' ),
					'message'  => __( 'Process marked as errored by watchdog: no heartbeat within the stale timeout window.', 'tainacan' ),
				],
			] );
		}

		// Release the process lock so new dispatches can proceed.
		$this->unlock_process();
	}

	/**
	 * Record a heartbeat for the current running process.
	 *
	 * Updates the processed_last timestamp in the database to indicate
	 * that the process is still alive. This is called during task execution
	 * and on shutdown to help the watchdog distinguish live processes from
	 * stale ones.
	 *
	 * @since 1.2.1
	 * @return void
	 */
	public function record_heartbeat() {
		global $wpdb;

		if ( empty( $this->table ) || empty( $this->ID ) ) {
			return;
		}

		$wpdb->update(
			$this->table,
			[ 'processed_last' => current_time( 'mysql' ) ],
			[ 'ID' => $this->ID ],
			[ '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Schedule event
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time(), $this->cron_interval_identifier, $this->cron_hook_identifier );
			$this->debug('cron event scheduled');
		}
	}

	/**
	 * Clear scheduled event
	 */
	protected function clear_scheduled_event() {
		$timestamp = wp_next_scheduled( $this->cron_hook_identifier );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $this->cron_hook_identifier );
			$this->debug('cron event unscheduled');
		}
	}

	/**
	 * Cancel Process
	 *
	 * Stop processing queue items, clear cronjob and delete batch.
	 *
	 */
	public function cancel_process() {
		if ( ! $this->is_queue_empty() ) {
			$batch = $this->get_batch();

			$this->delete( $batch->key );

			wp_clear_scheduled_hook( $this->cron_hook_identifier );
		}

	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	abstract protected function task( $item );

	/**
	 * desperate method to help debug bg processes
	 */
	public function debug($message) {
		if ( !defined('TAINACAN_DEBUG_BG_PROCESS') || true !== TAINACAN_DEBUG_BG_PROCESS || !is_string($message) ) {
			return;
		}

		$message = 'BG_PROCESS: ' . $message;
		error_log($message);
		
	}

}
