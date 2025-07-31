<?php

namespace Tainacan\Importer;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Tainacan\Entities;

/**
 * @author: MediaLab-UFG(Vinicius Nunes).
 * Term Importer
 *
 * Class to import files CSV with terms
 *
 */
class Term_Importer extends Importer {

	protected $steps = [
		[
			'name' => 'Create Taxonomy',
			'progress_label' => 'Creating taxonomy',
			'callback' => 'create_taxonomy'
		],
		[
			'name' => 'Import Terms',
			'progress_label' => 'Creating terms',
			'callback' => 'create_terms'
		]
	];

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		$this->add_import_method('file');
		$this->remove_import_method('url');

		$this->set_default_options([
			'delimiter' => ',',
			'new_taxonomy' => ''
		]);
	}

	public function options_form() {
		ob_start();
		?>
			<div class="columns">
				<div class="column">
					<div class="field">
						<label class="label" style="position: relative">
							<?php _e('CSV Delimiter', 'tainacan'); ?>
							<span class="help-wrapper">
								<a class="help-button has-text-secondary">
									<span class="icon is-small">
										<i class="tainacan-icon tainacan-icon-help" ></i>
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
						</label>
						<div class="control is-clearfix">
							<input class="input" type="text" name="delimiter" value="<?php echo esc_attr($this->get_option('delimiter')); ?>">
						</div>
					</div>
				</div>

				<div class="column">
					<div class="field import_term_csv_taxonomies">
						<label class="label" style="position: relative">
							<?php _e('Target taxonomy:', 'tainacan'); ?>
							<span class="help-wrapper">
								<a class="help-button has-text-secondary">
									<span class="icon is-small">
										<i class="tainacan-icon tainacan-icon-help" ></i>
									</span>
								</a>
								<div class="help-tooltip">
									<div class="help-tooltip-header">
										<h5><?php _e('Existing Taxonomy', 'tainacan'); ?></h5>
									</div>
									<div class="help-tooltip-body">
										<p><?php _e('Specify the taxonomy you want to import the terms to.', 'tainacan'); ?></p>
										<p><?php _e('Select an existing taxonomy or create a new one on the fly.', 'tainacan'); ?></p>
									</div>
								</div>
							</span>
						</label>
						<style>
							.import_term_csv_taxonomies:has(select.select_taxonomy option:checked[value=""]) input.new_taxonomy {
								display: block;
							}
							.import_term_csv_taxonomies:has(select.select_taxonomy option:checked:not([value=""])) input.new_taxonomy {
								display: none;
							}
						</style>
						<div class="control is-clearfix">
							<div class="select is-fullwidth">
								<select name="select_taxonomy" class="select_taxonomy">
									<option value="" selected><?php _e('Create a new taxonomy', 'tainacan'); ?></option>
								<?php
									$Tainacan_Taxonomies  = \Tainacan\Repositories\Taxonomies::get_instance();
									$taxonomies  = $Tainacan_Taxonomies->fetch( ['nopaging' => true], 'OBJECT' );
									foreach( $taxonomies as $taxonomy) {
										?>
										<option value="<?php echo esc_attr($taxonomy->get_db_identifier());?>"><?php echo esc_attr($taxonomy->get_name()) ?> </option>
										<?php
									}
								?>
								</select>

							</div>

							<input class="input new_taxonomy" type="text" name="new_taxonomy" value="<?php echo esc_attr($this->get_option('new_taxonomy')); ?>" placeholder="<?php _e('New taxonomy name', 'tainacan'); ?>" >

						</div>
					</div>
				</div>

			</div>
		<?php
		return ob_get_clean();
	}

	public function process_item($index, $collection_definition) {
	 	return true;
	}

	public function create_terms( ) {

		if (($handle = fopen($this->tmp_file, "r")) !== false) {
			$file = $handle;
			$this->set_current_step_total( filesize($this->tmp_file) );
		} else {
			$this->add_error_log(' Error reading the file ');
			return false;
		}

		$parent = $this->get_transient('parent');
		if ($parent == null) $parent = array();
		$position 	= $this->get_transient('position')     == null ? 0: $this->get_transient('position');
		$last_term 	= $this->get_transient('last_term')    == null ? 0: $this->get_transient('last_term');
		$id_taxonomy= $this->get_transient('new_taxonomy');

		$taxonomy = \tainacan_taxonomies()->fetch( (int) $id_taxonomy );
		if ( $taxonomy instanceof Entities\Taxonomy && ! $taxonomy->can_edit() ) {
			$this->add_error_log("You don't have permission to add terms to this taxonomy");
			$this->abort();
			return false;
		}

		$position_file = $this->get_in_step_count();
		fseek($file, $position_file);
		if (($values =  fgetcsv($file, 0, $this->get_option('delimiter'), '"')) !== FALSE) {
			$position_file = ftell($file);
			if ($values[$position] == '') { // next degree
				$position++;
				array_push($parent, $last_term);
			}
			while( $position > 0 && !($values[$position] != '' && $values[$position-1] == '' )) {  // back degree
				$position--;
				array_pop($parent);
			}
			if ($position == 0 && $values[$position] == '') {
				$this->add_error_log("incorrect formatted csv");
				$this->abort();
				return false;
			}

			$term = new \Tainacan\Entities\Term();
			$term->set_name($values[$position]);
			$term->set_description($values[$position+1]);
			$term->set_taxonomy($id_taxonomy);

			$term_repo = \Tainacan\Repositories\Terms::get_instance();
			if(end($parent))
				$term->set_parent(end($parent));

			if ($term->validate()) {
				$term_insert = $term_repo->insert($term);
				$last_term = $term_insert->get_id();
				$this->add_log('Added term: id=' . $last_term . ' name=' . $term->get_name() . ' id parent=' . $term->get_parent());
			} else {
				$validationErrors = $term->get_errors();
				$err_msg = json_encode($validationErrors);
				$this->add_error_log("erro=>$err_msg");
				$this->abort();
				return false;
			}
			$this->add_transient('parent', $parent);
			$this->add_transient('last_term', $last_term);
			$this->add_transient('position', $position);
			return $position_file;
		} else {
			return true;
		}
	}

	public function create_taxonomy() {
		if ( $this->get_option('select_taxonomy') != '' ) {
			$this->add_transient('new_taxonomy',  $this->get_option('select_taxonomy'));
			return false;
		}

		if ( $this->get_option('select_taxonomy') == '' && $this->get_option('new_taxonomy') == '' ) {
			$this->abort();
			$this->add_error_log('No taxonomy selected');
			return false;
		}

		$tax1 = new Entities\Taxonomy();
		$tax1->set_name($this->get_option('new_taxonomy'));
		$tax1->set_allow_insert('yes');
		$tax1->set_status('publish');

		if ( ! $tax1->get_capabilities()->edit_posts ) {
			$this->add_error_log('Error creating taxonomy. Permission denied');
			$this->abort();
		} elseif ($tax1->validate()) {
			$tax_repo = \Tainacan\Repositories\Taxonomies::get_instance();
			$tax1 = $tax_repo->insert($tax1);
			$name = $tax1->get_name();
			$this->add_transient('new_taxonomy', $tax1->get_db_identifier());
			$this->add_log("taxonomy $name Created.");
			return true;
		} else {
			$this->add_error_log('Error creating taxonomy');
			$this->add_error_log($tax1->get_errors());
			$this->abort();
		}
		return false;
	}

	/**
	* Called when the process is finished. returns the final message to the user with a
	* short description of what happened. May contain HTML code and links
	*
	* @return string
	*/
	public function get_output() {
		$taxonomy = \Tainacan\Repositories\Taxonomies::get_instance()->fetch_by_db_identifier( $this->get_transient('new_taxonomy') );
		if ( !empty($taxonomy) ) {
			$message = __('target taxonomy:', 'tainacan');
			$message .= " <b>" . $taxonomy->get_name() . "</b><br/>";
		}
		return $message;
	}

}
