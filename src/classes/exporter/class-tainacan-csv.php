<?php

namespace Tainacan\Exporter;
use Tainacan;
use Tainacan\Entities;

class CSV extends Exporter {
	use \Tainacan\Traits\Exporter_Handler_Cell;
	private $collection_name;

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		$this->set_accepted_mapping_methods('any'); // set all method to mapping
		$this->accept_no_mapping = true;
		if ($current_collection = $this->get_current_collection_object()) {
			$name = $current_collection->get_name();
			$this->collection_name = sanitize_title($name) . "_csv_export.csv";
		} else {
			$this->collection_name = "csv_export.csv";
		}

		//$this->set_accepted_mapping_methods('list', [ "dublin-core" ]); // set specific list of methods to mapping
		$this->set_default_options([
			'delimiter' => ',',
			'multivalued_delimiter' => '||',
			'enclosure' => '"'
		]);
	}
	
	public function filter_multivalue_separator($separator) {
		return $this->get_option('multivalued_delimiter');
	}
	
	public function filter_hierarchy_separator($separator) {
		return '>>';
	}

	public function process_item( $item, $metadata ) {
		$mapper = $this->get_current_mapper();
		$line = [];
		
		if(!$mapper) {
			$line[] = $item->get_id();
		}
		
		add_filter('tainacan-item-metadata-get-multivalue-separator', [$this, 'filter_multivalue_separator'], 20);
		add_filter('tainacan-terms-hierarchy-html-separator', [$this, 'filter_hierarchy_separator'], 20);
		
		foreach ($metadata as $meta_key => $meta) {
			// if (!$meta) means this metadata is not mapped in the collection so there is no value for it 
			// an empty value must be returned so the number of columns matches the header 
			if (!$meta || empty($meta->get_value()) ) {
				$line[] = '';
				continue;
			}

			if ($meta->get_metadatum()->get_metadata_type() == 'Tainacan\Metadata_Types\Relationship') {
				$rel = $meta->get_value();
				if (is_array($rel))
					$line[]	= implode( $this->get_option('multivalued_delimiter'), $rel );
				else 
					$line[] = $rel;
			} elseif ($meta->get_metadatum()->get_metadata_type() == 'Tainacan\Metadata_Types\Compound') {
				$line[] = $this->get_compound_metadata_cell($meta);
			} elseif ($meta->get_metadatum()->get_metadata_type() == 'Tainacan\Metadata_Types\Date' ) {
				$metadatum = $meta->get_metadatum();
				$date_value = 'ERROR ON FORMATING DATE';
				if (is_object($metadatum)) {
					$fto = $metadatum->get_metadata_type_object();
					if (is_object($fto)) {
						if ( method_exists($fto, 'get_value_as_html') ) {
							$fto->output_date_format = 'Y-m-d';
							$date_value = $fto->get_value_as_html($meta);
						}
					}
				} 
				$line[] = $date_value;
			} else {
				$line[] = $meta->get_value_as_string();
			}
		}
		
		remove_filter('tainacan-item-metadata-get-multivalue-separator', [$this, 'filter_multivalue_separator']);
		remove_filter('tainacan-terms-hierarchy-html-separator', [$this, 'filter_hierarchy_separator']);
		
		
		if(!$mapper) {
			$line[] = $item->get_status(); // special_item_status
			$line[] = $this->get_document_cell($item); // special_document
			$line[] = $this->get_thumbnail_cell($item); // special_thumbnail
			$line[] = $this->get_attachments_cell($item); // special_attachments
			$line[] = $item->get_comment_status(); // special_comment_status
			$line[] = $item->get_author_login(); // special_item_author
			$line[] = $item->get_slug(); // special_item_slug
			$line[] = $item->get_creation_date(); // creation_date
			$line[] = $this->get_author_name_last_modification($item->get_id()); // user_last_modified
			$line[] = $item->get_modification_date(); // modification_date
			$line[] = get_permalink( $item->get_id() ); // public_url
		}
		
		$line_string = $this->str_putcsv($line, $this->get_option('delimiter'), $this->get_option('enclosure'));

		$this->append_to_file($this->collection_name, $line_string."\n");
	}

	public function output_header() {
		
		$mapper = $this->get_current_mapper();
		
		$line = [];
		if ($mapper) {
			foreach ($mapper->metadata as $meta_slug => $meta) {
				$line[] = $meta['field'] ?? $meta_slug;
			}
		} else {
			$line = ['special_item_id'];
			if ( $collection = $this->get_current_collection_object() ) {
				$metadata = $collection->get_metadata();
				foreach ($metadata as $meta) {
					$desc_title_meta = $this->get_description_title_meta($meta);
					$line[] = $desc_title_meta;
				}
			}

			$line[] = 'special_item_status';
			$line[] = 'special_document';
			$line[] = 'special_thumbnail';
			$line[] = 'special_attachments';
			$line[] = 'special_comment_status';
			$line[] = 'special_item_author';
			$line[] = 'special_item_slug';
			$line[] = 'creation_date';
			$line[] = 'user_last_modified';
			$line[] = 'modification_date';
			$line[] = 'public_url';
		}
		
		$line_string = $this->str_putcsv($line, $this->get_option('delimiter'), $this->get_option('enclosure'));
		
		$this->append_to_file($this->collection_name, $line_string."\n");
		
	}
	
	public function output_footer() {
		return false;
	}

	private function get_collections_names() {
		$collections_names = [];
		foreach($this->collections as $col) {
			$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $col['id'], 'OBJECT' );
			$collections_names[] = $collection->get_name();
		}
		return $collections_names;
	}
	
	/** 
	* When exporter is finished, gets the final output 
	*/
	public function get_output() {
		$files = $this->get_output_files();
		
		if ( is_array($files) && isset($files[$this->collection_name])) {
			$file = $files[$this->collection_name];
			$current_user = wp_get_current_user();
			$author_name = $current_user->user_login;

			$message = __('Target collections:', 'tainacan');
			$message .= " <b>" . implode(", ", $this->get_collections_names() ) . "</b><br/>";
			$message .= __('Exported by:', 'tainacan');
			$message .= " <b> $author_name </b><br/>";
			$message .= __('Your CSV file is ready! Access it in the link below:', 'tainacan');
			$message .= '<br/><br/>';
			$message .= '<a target="_blank" href="' . $file['url'] . '">Download</a>';
			
			return $message;
			
		} else {
			$this->add_error_log('Output file not found! Maybe you need to correct the permissions of your upload folder');
		}
	}

	public function options_form() {
		ob_start();
		?>
		<div class="field">
			<label class="label"><?php _e('CSV Delimiter', 'tainacan'); ?></label>
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
			<div class="control is-clearfix">
				<input class="input" type="text" name="delimiter" maxlength="1" value="<?php echo esc_attr($this->get_option('delimiter')); ?>">
			</div>
		</div>

		<div class="field">
			<label class="label"><?php _e('Enclosure', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Enclosure', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The character that wraps the content of each cell in your CSV, if necessary (e.g. ")', 'tainacan'); ?></p>
						</div>
					</div>
			</span>
			<div class="control is-clearfix">
				<input class="input" type="text" name="enclosure" value="<?php echo esc_attr($this->get_option('enclosure')); ?>">
			</div>
		</div>

		<div class="field">
			<label class="label"><?php _e('Multivalued metadata delimiter', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Multivalued metadata delimiter', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The character used to separate each value inside a cell with multiple values (e.g. ||). Note that the target metadatum must accept multiple values.', 'tainacan'); ?></p>
						</div>
					</div>
			</span>
			<div class="control is-clearfix">
				<input class="input" type="text" name="multivalued_delimiter" value="<?php echo esc_attr($this->get_option('multivalued_delimiter')); ?>">
			</div>
		</div>

		<div class="field">
			<label class="label"><?php _e('Include metadata section name', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							<i class="tainacan-icon tainacan-icon-help" ></i>
						</span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Include metadata section name', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('Include metadatum section name after the metadatum name. Metadata inside the default section are not modified', 'tainacan'); ?></p>
						</div>
					</div> 
			</span>
			<div class="control is-clearfix">
				<label class="b-checkbox checkbox">
					<input
						type="checkbox" 
						name="add_section_name" checked value="yes"
						>
					<span class="check"></span>
					<span class="control-label"><?php _e('Yes', 'tainacan'); ?></span>
				</label>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}
}