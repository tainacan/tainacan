<?php 

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

?>
<div class="wrap tainacan-page-container-content">
	<div class="tainacan-fixed-subheader">
		<h1 class="tainacan-page-title">
			<?php esc_html_e('Settings', 'tainacan'); ?>
		</h1>
		<?php settings_errors(); ?>
	</div>
	<div class="tainacan-settings-layout">
		<form method="post" action="options.php" class="tainacan-settings">
		<?php
			settings_fields( 'tainacan_settings' );
			do_settings_sections( 'tainacan_settings' );
		?>
		<footer class="form-footer">
			<?php submit_button( __( 'Save Changes', 'tainacan' ), 'primary', 'submit', true ); ?>
		</footer>
		</form>
		<nav id="tainacan-settings-toc" class="tainacan-settings-toc" hidden aria-labelledby="tainacan-settings-toc-label">
			<h2 id="tainacan-settings-toc-label" class="tainacan-settings-toc__label">
				<?php esc_html_e( 'Sections', 'tainacan' ); ?>
			</h2>
			<ol id="tainacan-settings-toc-list" class="tainacan-settings-toc__list"></ol>
		</nav>
	</div>
</div>