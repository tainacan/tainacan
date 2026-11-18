<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$tainacan_management_tools_commands = $this->get_commands();

?>
<div class="wrap tainacan-page-container-content">
	<div class="tainacan-fixed-subheader">
		<h1 class="tainacan-page-title">
			<?php esc_html_e( 'Management Tools', 'tainacan' ); ?>
		</h1>
	</div>

	<p class="tainacan-management-tools__intro">
		<?php esc_html_e( 'Run maintenance operations that may affect the whole repository database. Be careful and, before running operations that delete data, do a backup.', 'tainacan' ); ?>
		<br>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %s is the URL of the Processes page */
				__( 'Each operation runs as a background process: you can leave this page and follow it in the <a href="%s">Processes page</a>.', 'tainacan' ),
				esc_url( $this->get_processes_page_url() )
			)
		);
		?>
	</p>

	<div class="tainacan-management-tools">
		<?php if ( empty( $tainacan_management_tools_commands ) ) : ?>
			<p><?php esc_html_e( 'No tools available.', 'tainacan' ); ?></p>
		<?php else : ?>
			<?php foreach ( $tainacan_management_tools_commands as $tainacan_command_id => $tainacan_command ) : ?>
				<?php $this->render_command_card( $tainacan_command_id, $tainacan_command ); ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
