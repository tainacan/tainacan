<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * The semantic search console.
 *
 * A read-mostly screen: it shows what the index contains, what it covers and what is
 * failing, and it offers the operator actions that the findings of earlier sprints
 * said were missing. Every action here is a thin form over the callback that already
 * backs an ability, so the screen cannot drift from the agent-facing surface: there
 * is one implementation of "retry the queue" and this page presses it.
 *
 * Rendering is server-side on purpose. The plugin mount is read-only, the panels are
 * tables, and a Vue page would mean a webpack entry, a Sass entry and a rebuild for
 * every copy change. Nothing here needs a build step, and the Tainacan page container
 * styles are already enqueued by Pages.
 *
 * @since 1.5.0
 */
class Rag_Console extends Pages {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Nonce action for every form on the page.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'tainacan_rag_console_action';

	/**
	 * Notice query argument.
	 *
	 * @var string
	 */
	const NOTICE_ARG = 'tainacan_rag_notice';

	/**
	 * The report gathered for this request.
	 *
	 * @var array
	 */
	public $report = [];

	/**
	 * Fields the preview submitted, echoed back into the form.
	 *
	 * @var array
	 */
	public $preview = [
		'query'         => '',
		'collection_id' => 0,
		'ran'           => false,
		'result'        => null,
	];

	/**
	 * Page slug.
	 *
	 * @return string
	 */
	protected function get_page_slug() : string {
		return 'tainacan_rag';
	}

	/**
	 * Register the menu entry and the action handler.
	 *
	 * @return void
	 */
	public function add_admin_menu() {

		if ( ! $this->has_admin_ui_option( 'hideNavigationSemanticSearchButton' ) ) {
			$page_suffix = add_submenu_page(
				! $this->has_admin_ui_option( 'hideNavigationOtherMenu' ) ? $this->tainacan_other_links_slug : $this->tainacan_root_menu_slug,
				__( 'Semantic search', 'tainacan' ),
				'<span class="icon" aria-hidden="true">' . $this->get_svg_icon( 'search' ) . '</span><span class="menu-text">' . __( 'Semantic search', 'tainacan' ) . '</span>',
				'manage_options',
				$this->get_page_slug(),
				array( &$this, 'render_page' )
			);

			add_action( 'load-' . $page_suffix, array( &$this, 'load_page' ) );
			/*
			 * Actions run on the load hook, before any output, so the redirect after a
			 * write is a real redirect and a refresh cannot repeat the write.
			 */
			add_action( 'load-' . $page_suffix, array( &$this, 'handle_actions' ) );
		}
	}

	/**
	 * Gather everything the page renders.
	 *
	 * @return void
	 */
	public function render_page_content() {
		$this->report                 = \Tainacan\Rag\Status::get_instance()->report();
		$this->preview                = $this->run_preview();
		$this->preview['collections'] = $this->preview_collections();

		$connector                    = $this->selected_connector();
		$this->preview['connector']   = $connector;
		$this->preview['models']      = $this->connector_models( $connector );

		require_once __DIR__ . '/page.php';
	}

	/**
	 * The connector the create form is showing models for.
	 *
	 * Read from the query string so the model list can be rendered server-side: pick a
	 * connector, the page reloads, the model select is populated from that connector.
	 *
	 * @return string
	 */
	public function selected_connector() {
		$providers = \Tainacan\Rag\Provider_Registry::get_instance()->get_providers();
		$requested = isset( $_GET['rag_connector'] ) ? sanitize_key( wp_unslash( $_GET['rag_connector'] ) ) : '';

		if ( '' !== $requested && isset( $providers[ $requested ] ) ) {
			return $requested;
		}

		$ids = array_keys( $providers );

		return $ids ? (string) $ids[0] : '';
	}

	/**
	 * Embedding models a connector advertises, as id => label.
	 *
	 * @param string $connector_id Connector id.
	 * @return array<string, string>
	 */
	public function connector_models( $connector_id ) {
		$provider = \Tainacan\Rag\Provider_Registry::get_instance()->get_provider( (string) $connector_id );

		if ( $provider instanceof \Tainacan\Rag\WordPress_AI_Provider ) {
			return $provider->available_models();
		}

		return [];
	}

	/**
	 * Collections the preview can be scoped to, as id => name.
	 *
	 * The page is `manage_options` only, so every collection is listed, matching what
	 * the collections admin screen shows. A number field would have made the operator
	 * look an id up in another tab and type it from memory.
	 *
	 * @return array<int, string>
	 */
	public function preview_collections() {
		$collections = \Tainacan\Repositories\Collections::get_instance()->fetch(
			[
				'posts_per_page' => -1,
				'post_status'    => [ 'publish', 'private', 'draft', 'pending' ],
			],
			'OBJECT'
		);

		$options = [];

		if ( is_array( $collections ) ) {
			foreach ( $collections as $collection ) {
				if ( $collection instanceof \Tainacan\Entities\Collection ) {
					$options[ (int) $collection->get_id() ] = (string) $collection->get_name();
				}
			}
		}

		return $options;
	}

	/**
	 * Run the preview form, if it was submitted.
	 *
	 * @return array
	 */
	private function run_preview() {
		$raw = isset( $_GET['rag_query'] ) ? sanitize_text_field( wp_unslash( $_GET['rag_query'] ) ) : '';

		$preview = [
			'query'         => $raw,
			'collection_id' => isset( $_GET['rag_collection'] ) ? (int) $_GET['rag_collection'] : 0,
			'ran'           => false,
			'result'        => null,
		];

		if ( '' === $raw ) {
			return $preview;
		}

		$args = [ 'limit' => 10 ];

		if ( $preview['collection_id'] > 0 ) {
			$args['collection_ids'] = [ $preview['collection_id'] ];
		}

		$preview['ran']    = true;
		$preview['result'] = \Tainacan\Rag\Semantic_Search::get_instance()->search( $raw, $args );

		return $preview;
	}

	/**
	 * Handle a submitted action, then redirect.
	 *
	 * @return void
	 */
	public function handle_actions() {
		if ( empty( $_POST['tainacan_rag_action'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to change semantic search settings.', 'tainacan' ) );
		}

		check_admin_referer( self::NONCE_ACTION );

		$action = sanitize_key( wp_unslash( $_POST['tainacan_rag_action'] ) );
		$notice = $this->dispatch( $action );

		wp_safe_redirect(
			add_query_arg(
				self::NOTICE_ARG,
				rawurlencode( $notice ),
				remove_query_arg( array( self::NOTICE_ARG, 'rag_query', 'rag_collection' ) )
			)
		);
		exit;
	}

	/**
	 * Run one action and describe the outcome in a notice.
	 *
	 * @param string $action Action key.
	 * @return string
	 */
	private function dispatch( $action ) {
		$status = \Tainacan\Rag\Status::get_instance();

		switch ( $action ) {
			case 'retry_queue':
				$result = $status->for_ability_retry_queue( [ 'limit' => isset( $_POST['limit'] ) ? (int) $_POST['limit'] : 50 ] );
				return 'queue-retried:' . (int) $result['requeued'];

			case 'run_queue':
				$result = \Tainacan\Rag\Indexer::get_instance()->for_ability_run(
					[ 'limit' => isset( $_POST['limit'] ) ? (int) $_POST['limit'] : 5 ]
				);

				if ( is_wp_error( $result ) ) {
					return 'error:' . $result->get_error_code();
				}

				return 'queue-run:' . (int) $result['processed'];

			case 'create_space':
				$result = \Tainacan\Rag\Space_Repository::get_instance()->for_ability_create(
					[
						'provider'   => isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '',
						'model'      => isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : '',
						'modality'   => isset( $_POST['modality'] ) ? sanitize_key( wp_unslash( $_POST['modality'] ) ) : 'text',
						'dimensions' => isset( $_POST['dimensions'] ) ? (int) $_POST['dimensions'] : 0,
					]
				);

				if ( is_wp_error( $result ) ) {
					return 'error:' . $result->get_error_code();
				}

				return 'space-created:' . (int) $result['id'];

			case 'activate_space':
				$result = \Tainacan\Rag\Space_Repository::get_instance()->for_ability_activate(
					[ 'space_id' => isset( $_POST['space_id'] ) ? (int) $_POST['space_id'] : 0 ]
				);

				return is_wp_error( $result ) ? 'error:' . $result->get_error_code() : 'space-activated:' . (int) $result['id'];

			case 'delete_space':
				$result = \Tainacan\Rag\Space_Repository::get_instance()->for_ability_delete(
					[ 'space_id' => isset( $_POST['space_id'] ) ? (int) $_POST['space_id'] : 0 ]
				);

				return is_wp_error( $result ) ? 'error:' . $result->get_error_code() : 'space-deleted';

			case 'backfill_start':
				$result = \Tainacan\Rag\Backfill::get_instance()->for_ability_start(
					[
						'collection_id' => isset( $_POST['collection_id'] ) ? (int) $_POST['collection_id'] : 0,
						'space_id'      => isset( $_POST['space_id'] ) ? (int) $_POST['space_id'] : 0,
					]
				);

				if ( is_wp_error( $result ) ) {
					return 'error:' . $result->get_error_code();
				}

				return 'backfill-started:' . (int) $result['total'];

			case 'backfill_step':
				$result = \Tainacan\Rag\Backfill::get_instance()->for_ability_step(
					[
						'collection_id' => isset( $_POST['collection_id'] ) ? (int) $_POST['collection_id'] : 0,
						'space_id'      => isset( $_POST['space_id'] ) ? (int) $_POST['space_id'] : 0,
						'batch'         => 50,
					]
				);

				if ( is_wp_error( $result ) ) {
					return 'error:' . $result->get_error_code();
				}

				return 'backfill-stepped:' . (int) $result['enqueued'];

			case 'clear_breaker':
				\Tainacan\Rag\Provider_Circuit_Breaker::get_instance()->clear();

				return 'breaker-cleared';
		}

		return 'unknown-action';
	}

	/**
	 * Human readable notice text.
	 *
	 * @param string $notice Raw notice from the query string.
	 * @return string
	 */
	public static function notice_text( $notice ) {
		$notice = sanitize_text_field( (string) $notice );

		if ( '' === $notice ) {
			return '';
		}

		$parts  = explode( ':', $notice, 2 );
		$key    = $parts[0];
		$value  = isset( $parts[1] ) ? (int) $parts[1] : 0;

		switch ( $key ) {
			case 'queue-retried':
				/* translators: %d: number of rows */
				return sprintf( __( '%d failed row(s) put back in the queue.', 'tainacan' ), $value );

			case 'queue-run':
				/* translators: %d: number of items processed */
				return sprintf( __( '%d item(s) processed.', 'tainacan' ), $value );

			case 'space-created':
				/* translators: %d: space id */
				return sprintf( __( 'Space %d created. Activate it to start indexing.', 'tainacan' ), $value );

			case 'space-activated':
				/* translators: %d: space id */
				return sprintf( __( 'Space %d is now the active space for its modality.', 'tainacan' ), $value );

			case 'space-deleted':
				return __( 'The space and its table were removed.', 'tainacan' );

			case 'backfill-started':
				/* translators: %d: number of items */
				return sprintf( __( 'Backfill started for %d published item(s).', 'tainacan' ), $value );

			case 'backfill-stepped':
				/* translators: %d: number of items */
				return sprintf( __( '%d item(s) queued by this backfill step.', 'tainacan' ), $value );

			case 'breaker-cleared':
				return __( 'Provider pauses cleared. The next queue run will try again.', 'tainacan' );

			case 'error':
				return sprintf(
					/* translators: %s: error code */
					__( 'The action failed: %s', 'tainacan' ),
					isset( $parts[1] ) ? $parts[1] : __( 'unknown error', 'tainacan' )
				);
		}

		return __( 'Unknown action.', 'tainacan' );
	}

	/**
	 * Format a byte count for display.
	 *
	 * @param int|null $bytes Bytes.
	 * @return string
	 */
	public static function format_bytes( $bytes ) {
		if ( null === $bytes || '' === $bytes ) {
			return __( 'unknown', 'tainacan' );
		}

		$bytes = (float) $bytes;

		if ( $bytes < 1024 ) {
			/* translators: %d: bytes */
			return sprintf( __( '%d B', 'tainacan' ), $bytes );
		}

		$units = [ 'KB', 'MB', 'GB', 'TB' ];
		$index = -1;

		do {
			$bytes /= 1024;
			$index++;
		} while ( $bytes >= 1024 && $index < count( $units ) - 1 );

		return sprintf( '%.1f %s', $bytes, $units[ $index ] );
	}
}
