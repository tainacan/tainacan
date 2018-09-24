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
							<h5><?php _e('taxonomy', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The taxonomies where imported term will be added.', 'tainacan'); ?></p>
						</div>
					</div> 
				</span>
				<div class="control is-clearfix">
					<select class="input" type="text" name="taxonomies">
					<?php
						$Tainacan_Taxonomies  = \Tainacan\Repositories\Taxonomies::get_instance();
						$taxonomies  = $Tainacan_Taxonomies->fetch( [
							'status' => [
								'auto-draft',
								'draft',
								'publish',
								'private'
							]
						], 'OBJECT' );

						foreach( $taxonomies as $taxonomie) {
							?>
							<option value="<?php echo $taxonomie->get_db_identifier();?>"><?php echo $taxonomie->get_name() ?> </option>
							<?php
						}
					?>
					</select>
				</div>
			</div>
			
		<?php
		return ob_get_clean();
	}
	
	public function process_item($index, $collection_definition) {
		
		if (($handle = fopen($this->tmp_file, "r")) !== false) {
			$file = $handle;
        } else {
			$this->add_error_log(' Error reading the file ');
			return false;
		}

		$term_repo = \Tainacan\Repositories\Terms::get_instance();
		$parent = array();
		$position = 0;
		$last_term = 0;
		$auxId = 1;
		$taxonomy_id = $this->get_option('taxonomies');
		while (($values =  fgetcsv($file, 0, $this->get_option('delimiter'), '"')) !== FALSE) {
			if ($values[$position] == '') { // next degree
				$position++;
				array_push($parent, $last_term);
			}
			if ($position > 0 && $values[$position-1] != '') { // back degree
				$position--;
				array_pop($parent);
			}
			
			$term = new \Tainacan\Entities\Term();
			$term->set_name($values[$position]);
			$term->set_description($values[$position+1]);
			$term->set_taxonomy($taxonomy_id);
			
			if(end($parent))
				$term->set_parent(end($parent));
		
			if ($term->validate()) {
				$term_insert = $term_repo->insert($term);
				$last_term = $term_insert->get_id();
				$this->add_log('Added term: id=' . $last_term . ' name=' . $term->get_name() . ' id parent=' . $term->get_parent());
			} else {
				$validationErrors = $term->get_errors();
				$err_msg = "";
				foreach($validationErrors as $err) {
					$err_msg .= $err;
				}
				$this->add_error_log("err! = ". $err_msg);
				return false;
			}
		}
		return true;
	}
}