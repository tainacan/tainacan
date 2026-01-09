<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

?>
<style>

	.tainacan-system-check {
		max-width: 1024px;
	}

	.tainacan-system-check .error {
		border-inline-start: 5px solid red;
		margin-inline-end: 10px;
	}
	.tainacan-system-check .warning {
		border-inline-start: 5px solid orange;
		margin-inline-end: 10px;
	}
	.tainacan-system-check .good {
		border-inline-start: 5px solid green;
		margin-inline-end: 10px;
	}
	.tainacan-system-check .impartial {
		border-inline-start: 5px solid gray;
		margin-inline-end: 10px;
	}


</style>

<div class="wrap tainacan-page-container-content">
	<div class="tainacan-fixed-subheader">
		<h1 class="tainacan-page-title">
			<?php esc_html_e('Tainacan System Check', 'tainacan'); ?>
		</h1>
	</div>
	
	<p><?php esc_html_e('This page checks your system against the Tainacan requirements. It helps you to find out whether your server is properly configured.', 'tainacan'); ?></p>
	
	<p><?php 
		printf(
			/* translators: %1$s is the link to the Health Check & Troubleshooting plugin, %2$s is the closing link tag */
			esc_html__('If you want to have a more complete diagnosis of your current WordPress instance, we recommend you to install the %1$sHealth Check & Troubleshooting%2$s plugin.', 'tainacan'),
			'<a href="https://wordpress.org/plugins/health-check/" target="_blank">',
			'</a>'
		);
	?></p>
	
	<table class="form-table tainacan-system-check">
		
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e('WordPress version', 'tainacan'); ?></th>
				<td>
					<?php $this->test_wordpress_version(); ?>
				</td>
			</tr>
		
			<tr>
				<th scope="row"><?php esc_html_e('PHP version', 'tainacan'); ?></th>
				<td>
					<?php $this->test_php_version(); ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e('Database version', 'tainacan'); ?></th>
				<td>
					<?php $this->test_sql_server(); ?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e('Tainacan version', 'tainacan'); ?></th>
				<td>
					<?php $this->get_tainacan_version(); ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e('PHP Modules', 'tainacan'); ?></th>
				<td>
					<?php $this->test_php_extensions(); ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e('PHP Maximum execution time', 'tainacan'); ?></th>
				<td>
					<?php $this->check_php_timeout(); ?>
					<p class="description">
						<?php esc_html_e('Some Tainacan features, such as import processes, may need a little extra time to run. It is a good idea to set the PHP maximum execution time to a bigger value than the default, although this is not mandatory. If you see a "Maximum execution time of XX seconds exceeded" in any error log, then you shoud definitely do it.', 'tainacan'); ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e('Permalinks Structure', 'tainacan'); ?></th>
				<td>
					<?php $this->check_permalink_settings(); ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e('Upload Folder', 'tainacan'); ?></th>
				<td>
					<?php $this->check_upload_permission(); ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e('Max Upload File size', 'tainacan'); ?></th>
				<td>
					<?php $this->check_max_upload_size(); ?>
					<p class="description">
						<?php esc_html_e('This is the maximum size of each individual upload to your site. You should increase it depending on your needs.', 'tainacan'); ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e('Protecting private uploads folders', 'tainacan'); ?></th>
				<td>
					<?php $this->check_protected_upload_folders(); ?>
					<p class="description">
						<?php esc_html_e('When files are attached to private items or collections, they are saved in special folders and the direct URL to them are never visible. However, it is recommended to block access to these folders in the server.', 'tainacan'); ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e('Cron', 'tainacan'); ?></th>
				<td>
					<?php echo wp_kses_post('It is strongly recommended that you configure a cron job in your server as described <a href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/">here</a>.', 'tainacan'); ?>
					<p class="description">
						<?php esc_html_e("We can't test whether there is a cronjob set or not, so ignore this if you already configured it.", 'tainacan'); ?>
					</p>
				</td>
			</tr>
			
		</tbody>
			
	</table>
	
</div>