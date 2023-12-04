<?php

namespace Tainacan\Exporter;
use Tainacan;
use Tainacan\Entities;

class CSV extends Exporter {
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
		
		$line = [];
		
		$line[] = $item->get_id();
		
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
		
		$line[] = $item->get_status();
		
		$line[] = $this->get_document_cell($item);
		
		$line[] = $this->get_attachments_cell($item);
		
		$line[] = $item->get_comment_status();

		$line[] = $item->get_author_name();
		
		$line[] = $item->get_creation_date();
		
		$line[] = $this->get_author_name_last_modification($item->get_id());

		$line[] = $item->get_modification_date();

		$line[] = get_permalink( $item->get_id() );
		
		$line_string = $this->str_putcsv($line, $this->get_option('delimiter'), $this->get_option('enclosure'));

		$this->append_to_file($this->collection_name, $line_string."\n");
	}

	function get_compound_metadata_cell($meta) {
		$enclosure = $this->get_option('enclosure');
		$delimiter = $this->get_option('delimiter');
		$multivalued_delimiter = $this->get_option('multivalued_delimiter');

		$metadata_type_options = $meta->get_metadatum()->get_metadata_type_options();
		$initial_values = [];
		foreach($metadata_type_options['children_order'] as $order) {
			$initial_values[$order['id']] = "";
		}
		$values = ($meta->get_metadatum()->is_multiple() ? $meta->get_value(): [$meta->get_value()]);
		$array_meta = [];
		foreach($values as $value) {
			$assoc_arr = array_reduce( $value, function ($result, $item) {
				$metadatum_id = $item->get_metadatum()->get_id();
				if ($item->get_metadatum()->get_metadata_type() == 'Tainacan\Metadata_Types\Relationship') {
					$result[$metadatum_id] = $item->get_value();
				} else {
					$result[$metadatum_id] = $item->get_value_as_string();
				}
				return $result;
			}, $initial_values);
			
			$array_meta[] = $this->str_putcsv($assoc_arr, $delimiter, $enclosure);
		}
		return implode($multivalued_delimiter, $array_meta);
	}

	function get_document_cell($item) {
		$type = $item->get_document_type();
		if ($type == 'attachment') $type = 'file';
		
		$document = $item->get_document();
		
		if ($type == 'file') {
			$url = wp_get_attachment_url($document);
			if ($url) $document = $url;
		}
		
		return $type . ':' . $document;
		
	}
	
	function get_attachments_cell($item) {
		$attachments = $item->get_attachments();
		
		$attachments_urls = array_map(function($a) {
			if (isset($a->guid)) return $a->guid;
		}, $attachments);
		
		return implode( $this->get_option('multivalued_delimiter'), $attachments_urls );
	}

	function get_author_name_last_modification($item_id) {
		$last_id = get_post_meta( $item_id, '_user_edit_lastr', true );
		if ( $last_id ) {
			$last_user = get_userdata( $last_id );
 			return apply_filters( 'tainacan-the-modified-author', $last_user->display_name );
		}
		return "";
	}

	private function get_description_title_meta($meta) {
		$meta_type =  explode('\\', $meta->get_metadata_type()) ;
		$meta_type = strtolower($meta_type[sizeof($meta_type)-1]);

		$meta_section_name = '';
		if ($this->get_option('add_section_name') == 'yes' && $current_collection = $this->get_current_collection_object()) {
			$meta_section_id = $meta->get_metadata_section_id();
			$collection_id = $current_collection->get_id();
			
			if($meta->is_repository_level()) {
				foreach($meta_section_id as $section_id ) {
					if($collection_id == get_post_meta($section_id, 'collection_id', true)) {
						$meta_section_name = '(' . get_the_title($section_id) . ')';
						continue;
					}
				}
			} else {
				if($meta_section_id != \Tainacan\Entities\Metadata_Section::$default_section_slug) {
					$meta_section_name = '(' . get_the_title($meta_section_id) . ')';
				}
			}
		}
		

		if($meta_type == 'compound') {
			$enclosure = $this->get_option('enclosure');
			$delimiter = $this->get_option('delimiter');
			$metadata_type_options = $meta->get_metadata_type_options();
			$desc_childrens = [];
			foreach($metadata_type_options['children_objects'] as $children) {
				$children_meta_type = explode('\\', $children['metadata_type']);
				$children_meta_type = strtolower($children_meta_type[sizeof($children_meta_type)-1]);
				$children_meta_type .=  ($children['collection_key'] === 'yes' ? '|collection_key_yes' : '');
				$desc_childrens[] = $children['name'] . '|' . $children_meta_type;
			}
			$meta_type .= "(" .  implode($delimiter, $desc_childrens)  . ")";
			$desc_title_meta = 
				$meta->get_name() .
				$meta_section_name .
				('|' . $meta_type) .
				($meta->is_multiple() ? '|multiple': '') .
				('|display_' . $meta->get_display());
		} else {
			$desc_title_meta = 
				$meta->get_name() .
				$meta_section_name .
				('|' . $meta_type) .
				($meta->is_multiple() ? '|multiple': '') .
				($meta->is_required() ? '|required': '') .
				('|display_' . $meta->get_display()) .
				($meta->is_collection_key() ? '|collection_key_yes' : '');
		}
		return $desc_title_meta;
	}

	public function output_header() {
		
		$mapper = $this->get_current_mapper();
		
		$line = ['special_item_id'];
		
		if ($mapper) {
			
			foreach ($mapper->metadata as $meta_slug => $meta) {
				$line[] = $meta_slug;
			}
			
		} else {
			if ( $collection = $this->get_current_collection_object() ) {
				
				$metadata = $collection->get_metadata();
				foreach ($metadata as $meta) {
					$desc_title_meta = $this->get_description_title_meta($meta);
					$line[] = $desc_title_meta;
				}
			}
		}
		
		$line[] = 'special_item_status';
		$line[] = 'special_document';
		$line[] = 'special_attachments';
		$line[] = 'special_comment_status';
		$line[] = 'author_name';
		$line[] = 'creation_date';
		$line[] = 'user_last_modified';
		$line[] = 'modification_date';
		$line[] = 'public_url';
		
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

	function str_putcsv($input, $delimiter = ',', $enclosure = '"') {
		// Open a memory "file" for read/write...
		$fp = fopen('php://temp', 'r+');
		
		fputcsv($fp, $input, $delimiter, $enclosure);
		rewind($fp);
		//Getting detailed stats to check filesize:
		$fstats = fstat($fp);
		$data = fread($fp, $fstats['size']);
		fclose($fp);
		return rtrim($data, "\n");
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
							<p><?php _e('The character that wraps the content of each cell in your CSV. (e.g. ")', 'tainacan'); ?></p>
						</div>
					</div>
			</span>
			<div class="control is-clearfix">
				<input class="input" type="text" name="enclosure" value="<?php echo esc_attr($this->get_option('enclosure')); ?>">
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
				<label class="checkbox">
					<input
						type="checkbox" 
						name="add_section_name" checked value="yes"
						>
					<?php _e('Yes', 'tainacan'); ?>
				</label>
			</div>
		</div>

      <?php
      return ob_get_clean();
  }
}