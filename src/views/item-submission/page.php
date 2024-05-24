<div class="wrap">
	<h1>
		<?php _e('Item submission', 'tainacan'); ?>
	</h1>
	<?php settings_errors(); ?>
	<form method="post" action="options.php">
		<?php
			settings_fields( 'tainacan_item_submission_recaptcha' );
			do_settings_sections( 'tainacan_item_submission' );
			submit_button();
		?>
	</form>
</div>