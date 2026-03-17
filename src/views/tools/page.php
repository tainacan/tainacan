<?php

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

$tools = $this->get_tools_for_page();

?>
<div class="wrap tainacan-page-container-content">
	<div class="tainacan-fixed-subheader">
		<h1 class="tainacan-page-title">
			<?php esc_html_e( 'Management Tools', 'tainacan' ); ?>
		</h1>
	</div>

	<div id="tainacan-tools-running-notice" class="tainacan-tools-notice tainacan-tools-notice--running" role="alert" aria-live="polite" style="display: none;"></div>

	<p class="tainacan-tools-intro">
		<?php esc_html_e( 'Run CLI-backed operations from this screen. Results appear below each tool. Advanced users can run these via WP-CLI: wp tainacan ...', 'tainacan' ); ?>
	</p>

	<div id="tainacan-tools-cards" class="tainacan-tools-cards">
		<?php if ( empty( $tools ) ) : ?>
			<p class="tainacan-tools-loading"><?php esc_html_e( 'No tools available.', 'tainacan' ); ?></p>
		<?php else : ?>
			<?php foreach ( $tools as $tool ) : ?>
				<?php $this->render_tool_card( $tool ); ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
