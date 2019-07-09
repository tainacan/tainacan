<?php

namespace Tainacan\Exporter;
use Tainacan;
use Tainacan\Entities;

class CSV extends Exporter {

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		$this->set_accepted_mapping_methods('any'); // set all method to mapping
		$this->accept_no_mapping = true;
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

	public function process_item( $item, $metadata ) {
		
		$line = [];
		
		$line[] = $item->get_id();
		
		add_filter('tainacan-item-metadata-get-multivalue-separator', [$this, 'filter_multivalue_separator']);
		
		foreach ($metadata as $meta_key => $meta) {
			
			// if (!$meta) means this metadata is not mapped in the collection so there is no value for it 
			// an empty value must be returned so the number of columns matches the header 
			if (!$meta || empty($meta->get_value()) ) {
				$line[] = '';
				continue;
			}
			
			$line[] = $meta->get_value_as_string();
			
		}
		
		remove_filter('tainacan-item-metadata-get-multivalue-separator', [$this, 'filter_multivalue_separator']);
		
		
		$line[] = $item->get_status();
		
		$line[] = $this->get_document_cell($item);
		
		$line[] = $this->get_attachments_cell($item);
		
		$line[] = $item->get_comment_status();
		
		$line_string = $this->str_putcsv($line, $this->get_option('delimiter'), $this->get_option('enclosure'));
		
		
		$this->append_to_file('csvexporter.csv', $line_string."\n");
		
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
			if (isset($a['url'])) return $a['url'];
		}, $attachments);
		
		return implode( $this->get_option('multivalued_delimiter'), $attachments_urls );
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
					$line[] = $meta->get_name();
				}
				
				
			}
		}
		
		$line[] = 'special_item_status';
		$line[] = 'special_document';
		$line[] = 'special_attachments';
		$line[] = 'special_comment_status';
		
		$line_string = $this->str_putcsv($line, $this->get_option('delimiter'), $this->get_option('enclosure'));
		
		$this->append_to_file('csvexporter.csv', $line_string."\n");
		
	}
	
	public function output_footer() {
		return false;
	}
	
	/** 
	* When exporter is finished, gets the final output 
	*/
	public function get_output() {
		$files = $this->get_output_files();
		
		if ( is_array($files) && isset($files['csvexporter.csv'])) {
			$file = $files['csvexporter.csv'];
			
			$message = __('Your CSV file is ready! Access it in the link below:', 'tainacan');
			$message .= '<br/><br/>';
			$message .= '<a href="' . $file['url'] . '">Download</a>';
			
			return $message;
			
		} else {
			$this->add_error_log('Output file not found! Maybe you need to correct the permissions of your upload folder');
		}
	}

	function str_putcsv($item, $delimiter = ',', $enclosure = '"') {
		// Open a memory "file" for read/write...
		$fp = fopen('php://temp', 'r+');
		
		fputcsv($fp, $item, $delimiter, $enclosure);
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
				<input class="input" type="text" name="delimiter" maxlength="1" value="<?php echo $this->get_option('delimiter'); ?>">
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
				<input class="input" type="text" name="multivalued_delimiter" value="<?php echo $this->get_option('multivalued_delimiter'); ?>">
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
		
		
	   
	   <?php 
	   
	   
	   return ob_get_clean();

    }
}