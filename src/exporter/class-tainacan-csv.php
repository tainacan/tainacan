<?php

namespace Tainacan\Exporter;
use Tainacan;
use Tainacan\Entities;

class CSV extends Exporter {

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		$this->set_accepted_mapping_methods('any'); // set all method to mapping
		//$this->set_accepted_mapping_methods('list', [ "dublin-core" => "Tainacan\\Exposers\\Mappers\\Dublin_Core" ]); // set specific list of methods to mapping
		//todo create list only slug
	}

	public function process_item( $processed_item ) {
		
		
		$mapper = $this->get_current_mapper();
		error_log(json_encode($mapper));
		$line = [];
		
		foreach ($processed_item as $meta_key => $meta) {
			
			if (!$meta) {
				$line[] = '';
				continue;
			}
			
			$line[] = $meta->get_value_as_string();
			
		}
		
		$line_string = $this->str_putcsv($line, ',', '"');
		
		
		$this->append_to_file('exporter', $line_string."\n");
		
		
	}

	public function output_header() {
		
		$mapper = $this->get_current_mapper();
		
		$line = [];
		
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
		
		$line_string = $this->str_putcsv($line, ',', '"');
		
		$this->append_to_file('exporter', $line_string."\n");
		
	}
	
	public function output_footer() {
		return false;
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
			<label class="label"><?php _e('Multivalued metadata delimiter', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="mdi mdi-help-circle-outline" ></i>
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
							 <i class="mdi mdi-help-circle-outline" ></i>
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
				<input class="input" type="text" name="enclosure" value="<?php echo $this->get_option('enclosure'); ?>">
			</div>
		</div>
		
		<div class="field">
			<label class="label"><?php _e('File Encoding', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="mdi mdi-help-circle-outline" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('File Encoding', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The encoding of the CSV file.', 'tainacan'); ?></p>
						</div>
					</div> 
			</span>
			<div class="control is-clearfix">
				<div class="select">
					<select name="encode">
						<option value="utf8" <?php selected($this->get_option('encode'), 'utf8'); ?> >UTF-8</option>
						<option value="iso88591" <?php selected($this->get_option('encode'), 'iso88591'); ?> >ISO-88591</option>
					</select>
				</div>
			</div>
		</div>
	   
	   <?php 
	   
	   
	   return ob_get_clean();

    }
}