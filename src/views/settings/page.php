<div class="wrap tainacan-page-container-content">
	<div class="tainacan-fixed-subheader">
		<h1 class="wp-heading-inline">
			<?php _e('Settings', 'tainacan'); ?>
		</h1>
	</div>
	<?php settings_errors(); ?>
	<form method="post" action="options.php" class="tainacan-settings">
		<?php
			settings_fields( 'tainacan_item_submission_recaptcha' );
			do_settings_sections( 'tainacan_settings' );
		?>
		<footer class="form-footer">
			<?php submit_button( __( 'Save Changes', 'tainacan' ), 'primary', 'submit', true ); ?>
		</footer>
	</form>
</div>