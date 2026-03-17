<?php

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

$tainacan_tools = $this->get_tools_for_page();

?>
<div class="wrap tainacan-page-container-content">
	<div class="tainacan-fixed-subheader">
		<h1 class="tainacan-page-title">
			<?php esc_html_e( 'Management Tools', 'tainacan' ); ?>
		</h1>
	</div>
	
	<p class="tainacan-tools-intro">
		<?php esc_html_e( 'Execute advanced operations affecting the database or the filesystem. Be careful when running these tools, you must know what you are doing!', 'tainacan' ); ?>
		<br>
		<?php echo wp_kses_post( __('They can also be invoked via <a href="https://wp-cli.org/" target="_blank">WP-CLI</a>: <code>wp tainacan ...</code>. For more information, see the <a href="https://tainacan.github.io/tainacan-wiki/#/tools" target="_blank">documentation</a>.', 'tainacan') ); ?>
	</p>

	<div id="tainacan-tools" class="tainacan-tools">
		<?php if ( empty( $tainacan_tools ) ) : ?>
			<p class="tainacan-tools-loading"><?php esc_html_e( 'No tools available.', 'tainacan' ); ?></p>
		<?php else : ?>
			<?php foreach ( $tainacan_tools as $tainacan_tool ) : ?>
				<?php $this->render_tool_card( $tainacan_tool ); ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
