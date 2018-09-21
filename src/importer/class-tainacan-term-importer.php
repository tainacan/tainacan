<?php

/** 
 *
 */

namespace Tainacan\Importer;
use \Tainacan\Entities;

class Term_Importer extends Importer {

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		$this->add_import_method('file');
		$this->remove_import_method('both');
		$this->remove_import_method('url');

		$this->set_default_options([
            'delimiter' => ','
		]);
	}
	
	public function options_form() {
		ob_start();
		?>
			<div class="field">
				<label class="label"><?php _e('CSV Delimiter', 'tainacan'); ?></label>
				<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							<i class="mdi mdi-help-circle-outline" ></i>
						</span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('CSV Delimiter', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The character used to separate each column in your CSV (e.g. , or ;)', 'tainacan'); ?></p>
						</div>
					</div> 
				</span>
				<div class="control is-clearfix">
					<input class="input" type="text" name="delimiter" value="<?php echo $this->get_option('delimiter'); ?>">
				</div>
			</div>

			<div class="field">
				<label class="label"><?php _e('Taxonomies:', 'tainacan'); ?></label>
				<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							<i class="mdi mdi-help-circle-outline" ></i>
						</span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Taxonomies', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The taxonomies where imported term will be added.', 'tainacan'); ?></p>
						</div>
					</div> 
				</span>
				<div class="control is-clearfix">
					<select class="input" type="text" name="taxonomies">
						<option>tax-1</option>
						<option>tax-2</option>
						<option>tax-3</option>
					</select>
					<?php 

						$request = new \WP_REST_Request(
							'GET', $this->namespace . '/taxonomies'
						);
						$response = $this->server->dispatch($request);
						echo $response;

					?>
				</div>
			</div>
			
		<?php
		return ob_get_clean();
	}

	public function process_item($index, $collection_definition) {
		$this->add_log('Proccessing item index ' . $index . ' in collection ' . $collection_definition['id'] );
	}
}