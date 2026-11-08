<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Renders, on the Tainacan dashboard, a notice warning that the active theme
 * does not provide Tainacan templates, with shortcuts to fix it.
 *
 * Regular admin notices are removed from Tainacan pages (see Pages::remove_admin_notices),
 * so this one is printed through the 'tainacan-dashboard-before-cards' action.
 */
class Dashboard_Theme_Notice {
	use Traits\Singleton_Instance;

	const NONCE_ACTION = 'tainacan_dashboard_theme_notice_nonce';
	const USER_META_KEY = 'tainacan_theme_support_notice_dismissed';
	const ADMIN_UI_OPTION = 'hideDashboardThemeSupportNotice';

	protected function init() {
		add_action( 'tainacan-dashboard-before-cards', array( $this, 'render' ) );
		add_action( 'wp_ajax_tainacan_dismiss_theme_support_notice', array( $this, 'ajax_dismiss' ) );
	}

	/**
	 * Whether the current user may see (and dismiss) the notice.
	 *
	 * @return bool
	 */
	public function user_can_act(): bool {
		return current_user_can( 'switch_themes' ) || current_user_can( 'install_themes' );
	}

	/**
	 * @return bool
	 */
	public function should_display(): bool {
		if ( Dashboard::get_instance()->has_admin_ui_option( self::ADMIN_UI_OPTION ) ) {
			return false;
		}

		if ( ! $this->user_can_act() ) {
			return false;
		}

		if ( Theme_Support::get_instance()->is_supported() ) {
			return false;
		}

		return get_user_meta( get_current_user_id(), self::USER_META_KEY, true ) !== get_stylesheet();
	}

	/**
	 * Marks the notice as dismissed for the current user and the active stylesheet.
	 *
	 * Switching to another theme shows the notice again.
	 */
	public function dismiss() {
		update_user_meta( get_current_user_id(), self::USER_META_KEY, get_stylesheet() );
	}

	/**
	 * AJAX handler for the dismiss button.
	 */
	public function ajax_dismiss() {
		check_ajax_referer( self::NONCE_ACTION, '_nonce' );

		if ( ! $this->user_can_act() ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'tainacan' ) ), 403 );
		}

		$this->dismiss();
		wp_send_json_success();
	}

	/**
	 * Prints the notice markup.
	 */
	public function render() {
		if ( ! $this->should_display() ) {
			return;
		}

		$theme_support = Theme_Support::get_instance();
		$actions = $theme_support->get_missing_support_actions();
		?>
		<div class="notice notice-warning inline tainacan-theme-support-notice" id="tainacan-theme-support-notice">
			<h2 class="tainacan-theme-support-notice__title"><?php esc_html_e( 'Your theme does not include Tainacan templates', 'tainacan' ); ?></h2>
			<p><?php esc_html_e( 'Collection, term and repository item pages will look like a regular blog list, without faceted search and filters. Use the Tainacan Interface theme, a block theme such as Tainá, or Blocksy with the Tainacan Support for Blocksy plugin.', 'tainacan' ); ?></p>
			<p class="tainacan-theme-support-notice__actions">
				<?php if ( in_array( 'install_theme', $actions, true ) ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( self_admin_url( 'theme-install.php?theme=tainacan-interface' ) ); ?>"><?php esc_html_e( 'Install Tainacan Interface', 'tainacan' ); ?></a>
				<?php endif; ?>
				<?php if ( in_array( 'activate_theme', $actions, true ) ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'themes.php?action=activate&stylesheet=tainacan-interface&template=tainacan-interface' ), 'switch-theme_tainacan-interface' ) ); ?>"><?php esc_html_e( 'Activate Tainacan Interface', 'tainacan' ); ?></a>
				<?php endif; ?>
				<?php if ( in_array( 'install_blocksy_plugin', $actions, true ) ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( self_admin_url( 'plugin-install.php?s=tainacan-blocksy&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Install Tainacan Support for Blocksy', 'tainacan' ); ?></a>
				<?php endif; ?>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=tainacan_settings' ) ); ?>"><?php esc_html_e( 'Use basic templates instead', 'tainacan' ); ?></a>
				<?php endif; ?>
				<a class="button" target="_blank" rel="noopener noreferrer" href="<?php
					/* translators: URL of the theming documentation page. Replace with the page for this language if available. */
					echo esc_url( __( 'https://tainacan.github.io/tainacan-wiki/#/dev/theming', 'tainacan' ) );
				?>"><?php esc_html_e( 'Learn more', 'tainacan' ); ?></a>
				<button type="button" class="button-link tainacan-theme-support-notice__dismiss" data-nonce="<?php echo esc_attr( wp_create_nonce( self::NONCE_ACTION ) ); ?>"><?php esc_html_e( 'Dismiss', 'tainacan' ); ?></button>
			</p>
		</div>
		<?php
	}
}
