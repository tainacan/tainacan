<div class="wrap">
	<h1><?php _e('Tainacan System Check', 'tainacan'); ?></h1>
	
	<h2><?php _e('Versions', 'tainacan'); ?></h2>
	
	<table class="form-table">
		
		<tbody>
			<tr>
				<th scope="row">asd</th>
				<td>
					<?php $this->test_wordpress_version(); ?>
				</td>
			</tr>
		
			<tr>
				<th scope="row">Tagline</th>
				<td>
					<?php $this->test_php_version(); ?>
				</td>
			</tr>
			
		</tbody>
			
		</table>
		
	</div>