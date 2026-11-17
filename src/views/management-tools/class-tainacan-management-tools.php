<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\GenericProcess\Management_Tools_Process;

/**
 * Management Tools admin page.
 *
 * Lists the commands available in the Management_Tools_Process, each one in a card
 * with a form generated from its params. Running a command starts a background
 * process, whose progress is displayed in the card (see tainacan-management-tools.js).
 */
class Management_Tools extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	protected function get_page_slug(): string {
		return 'tainacan_management_tools';
	}

	public function init() {
		parent::init();
	}

	public function add_admin_menu() {
		if ( $this->has_admin_ui_option( 'hideNavigationManagementToolsButton' ) ) {
			return;
		}

		$page_suffix = add_submenu_page(
			! $this->has_admin_ui_option( 'hideNavigationOtherMenu' ) ? $this->tainacan_other_links_slug : $this->tainacan_root_menu_slug,
			__( 'Management Tools', 'tainacan' ),
			'<span class="icon" aria-hidden="true">' . $this->get_svg_icon( 'processes' ) . '</span><span class="menu-text">' . __( 'Management Tools', 'tainacan' ) . '</span>',
			'manage_options',
			$this->get_page_slug(),
			[ &$this, 'render_page' ]
		);
		add_action( 'load-' . $page_suffix, [ &$this, 'load_page' ] );
	}

	public function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;
		wp_enqueue_style( 'tainacan-management-tools-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-management-tools.css', [], TAINACAN_VERSION );
	}

	public function admin_enqueue_js() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_script(
			'tainacan-management-tools',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_management_tools.js',
			[ 'tainacan-admin-navigation-menu' ],
			TAINACAN_VERSION,
			true
		);

		wp_localize_script( 'tainacan-management-tools', 'tainacan_management_tools', [
			'i18n' => [
				'run'                 => __( 'Run', 'tainacan' ),
				'starting'            => __( 'Starting…', 'tainacan' ),
				'running'             => __( 'Running…', 'tainacan' ),
				'confirm_destructive' => __( 'This operation will permanently delete data and cannot be undone. Do you want to continue?', 'tainacan' ),
				'request_failed'      => __( 'The request failed.', 'tainacan' ),
				'process_id'          => __( 'Process ID', 'tainacan' ),
				'see_log'             => __( 'See log', 'tainacan' ),
				'see_error_log'       => __( 'See error log', 'tainacan' ),
				'status'              => [
					'waiting'         => __( 'Waiting', 'tainacan' ),
					'running'         => __( 'Running', 'tainacan' ),
					'paused'          => __( 'Paused', 'tainacan' ),
					'finished'        => __( 'Finished', 'tainacan' ),
					'finished-errors' => __( 'Finished with errors', 'tainacan' ),
					'errored'         => __( 'Failed', 'tainacan' ),
					'cancelled'       => __( 'Cancelled', 'tainacan' ),
				],
			],
		] );
	}

	/**
	 * Returns help tabs for the management tools page.
	 *
	 * @return array
	 */
	protected function get_help_tabs() {
		return [
			[
				'id'      => 'tainacan_management_tools_overview_help_tab',
				'title'   => __( 'Management Tools', 'tainacan' ),
				'content' => '<p>' . __( 'This page runs maintenance operations that may affect the whole repository database. Each operation runs as a background process, so you can leave this page while it runs and follow it later in the Processes page.', 'tainacan' ) . '</p>',
			],
		];
	}

	public function render_page_content() {
		require_once __DIR__ . '/page.php';
	}

	/**
	 * @return array Commands available, keyed by id.
	 */
	public function get_commands() {
		return Management_Tools_Process::get_commands();
	}

	/**
	 * @return string URL of the Processes page in the Tainacan admin.
	 */
	public function get_processes_page_url() {
		return admin_url( 'admin.php?page=tainacan_admin#/processes' );
	}

	/**
	 * Outputs the card of a command: title, description, form and status area.
	 *
	 * @param string $id      The command id.
	 * @param array  $command The command definition.
	 */
	public function render_command_card( $id, array $command ) {
		$form_id = 'tainacan-management-tool-' . $id;
		?>
		<section class="tainacan-management-tool" data-command="<?php echo esc_attr( $id ); ?>" aria-labelledby="<?php echo esc_attr( $form_id . '-title' ); ?>">
			<div class="tainacan-management-tool__main">
				<h2 class="tainacan-management-tool__title" id="<?php echo esc_attr( $form_id . '-title' ); ?>">
					<?php echo esc_html( $command['name'] ); ?>
				</h2>
				<?php if ( ! empty( $command['description'] ) ) : ?>
					<p class="tainacan-management-tool__description"><?php echo esc_html( $command['description'] ); ?></p>
				<?php endif; ?>

				<form class="tainacan-management-tool__form" id="<?php echo esc_attr( $form_id ); ?>" novalidate>
					<?php
					foreach ( $command['params'] as $param ) {
						$this->render_param_field( $id, $param );
					}
					?>
					<div class="tainacan-management-tool__actions">
						<button type="submit" class="button button-primary tainacan-management-tool__run">
							<?php esc_html_e( 'Run', 'tainacan' ); ?>
						</button>
					</div>
				</form>
			</div>

			<div class="tainacan-management-tool__status" aria-live="polite" hidden>
				<div class="tainacan-management-tool__status-header">
					<span class="tainacan-management-tool__status-badge"></span>
					<span class="tainacan-management-tool__status-id"></span>
				</div>
				<p class="tainacan-management-tool__status-label"></p>
				<div class="tainacan-management-tool__progress" hidden>
					<progress max="100" value="0"></progress>
					<span class="tainacan-management-tool__progress-value"></span>
				</div>
				<div class="tainacan-management-tool__status-output"></div>
				<p class="tainacan-management-tool__status-links"></p>
			</div>
		</section>
		<?php
	}

	/**
	 * Outputs a form field for a command param.
	 *
	 * @param string $command_id
	 * @param array  $param The param definition (see Management_Tools_Process::get_commands()).
	 */
	private function render_param_field( $command_id, array $param ) {
		$name        = $param['name'];
		$type        = isset( $param['type'] ) ? $param['type'] : 'text';
		$label       = isset( $param['label'] ) ? $param['label'] : $name;
		$description = isset( $param['description'] ) ? $param['description'] : '';
		$default     = isset( $param['default'] ) ? $param['default'] : '';
		$required    = ! empty( $param['required'] );
		$destructive = ! empty( $param['destructive'] );
		$input_id    = 'tainacan-management-tool-' . $command_id . '-' . $name;
		$desc_id     = $input_id . '-description';
		?>
		<div class="tainacan-management-tool__field<?php echo $destructive ? ' tainacan-management-tool__field--destructive' : ''; ?>">
			<?php if ( 'boolean' === $type ) : ?>
				<label class="tainacan-management-tool__checkbox" for="<?php echo esc_attr( $input_id ); ?>">
					<input
						type="checkbox"
						id="<?php echo esc_attr( $input_id ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						value="1"
						<?php checked( (bool) $default ); ?>
						<?php echo $destructive ? 'data-destructive="1"' : ''; ?>
						<?php echo $description ? 'aria-describedby="' . esc_attr( $desc_id ) . '"' : ''; ?>
					>
					<span><?php echo esc_html( $label ); ?></span>
				</label>
			<?php else : ?>
				<label class="tainacan-management-tool__label" for="<?php echo esc_attr( $input_id ); ?>">
					<?php echo esc_html( $label ); ?><?php echo $required ? ' <span aria-hidden="true">*</span>' : ''; ?>
				</label>
				<input
					type="<?php echo 'number' === $type ? 'number' : 'text'; ?>"
					id="<?php echo esc_attr( $input_id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( (string) $default ); ?>"
					<?php echo isset( $param['min'] ) ? 'min="' . esc_attr( $param['min'] ) . '"' : ''; ?>
					<?php echo isset( $param['max'] ) ? 'max="' . esc_attr( $param['max'] ) . '"' : ''; ?>
					<?php echo $required ? 'required' : ''; ?>
					<?php echo $description ? 'aria-describedby="' . esc_attr( $desc_id ) . '"' : ''; ?>
				>
			<?php endif; ?>
			<?php if ( $description ) : ?>
				<p class="description" id="<?php echo esc_attr( $desc_id ); ?>"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
