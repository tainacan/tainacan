<?php

namespace Tainacan\Exporter;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan;
use Tainacan\Entities;
use Tainacan\Exporter\Exporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class XLSX_Exporter extends Exporter {
	use \Tainacan\Traits\Exporter_Handler_Cell;

	private $collection_name;
	private $spreadsheet;
	private $filePath;
	private $tempFilePath;

	public function __construct($attributes = array()) {
		parent::__construct($attributes);

		$this->set_accepted_mapping_methods('any'); // set all method to mapping
		$this->accept_no_mapping = true;

		if ($current_collection = $this->get_current_collection_object()) {
			$name = $current_collection->get_name();
			$this->collection_name = sanitize_title($name) . "_xlsx_export.xlsx";
		} else {
			$this->collection_name = "xlsx_export.xlsx";
		}
		
		$this->tempFilePath = "";

		$this->spreadsheet = null;

		$this->set_default_options([
			'delimiter' => ',',
			'multivalued_delimiter' => '||',
			'enclosure' => '"'
		]);
	}

	public function initialize_writer() {
		if (!array_key_exists($this->collection_name, $this->get_output_files())) {
			$this->add_new_file($this->collection_name);
		}
		
		$file_info = $this->get_output_files()[$this->collection_name];
		$this->filePath = $file_info['filename'];
		
		if (file_exists($this->filePath)) {
			$reader = IOFactory::createReader('Xlsx');
			$this->spreadsheet = $reader->load($this->filePath);
		} else {
			$this->spreadsheet = new Spreadsheet();
		}
	}

	public function finalize_writer() {
		if ($this->spreadsheet) {
			$writer = new Xlsx($this->spreadsheet);
			$savePath = $this->tempFilePath ?: $this->filePath;
			$writer->save($savePath);

			$this->spreadsheet->disconnectWorksheets();
			unset($this->spreadsheet);
			$this->spreadsheet = null;

			if ($this->tempFilePath) {
				if (file_exists($this->filePath)) {
					unlink($this->filePath);
				}
				rename($this->tempFilePath, $this->filePath);
			}
		}
	}

	public function process_collections() {

		$this->initialize_writer();
		
		$current_collection = $this->get_current_collection();
		$collections = $this->get_collections();
		$collection_definition = isset($collections[$current_collection]) ? $collections[$current_collection] : false;
		$current_collection_item = $this->get_current_collection_item();

		if ( !$collection_definition || !is_array($collection_definition) || !isset($collection_definition['id']) ) {
			parent::add_error_log('Collection misconfigured');
			return false;
		}
		$length_items = parent::get_step_length_items();
		parent::add_log("Processing batch with $length_items items, starting from item $current_collection_item");

		$this->process_header($current_collection_item, $collection_definition);
		$init = microtime(true);
		$processed_items = $this->get_items($current_collection_item, $collection_definition);
		foreach ($processed_items as $processed_item) {
			$this->process_item( $processed_item['item'], $processed_item['metadata'] );
		}
		$final = microtime(true);
		$total = ($final - $init);
		/* translators: %f is the time in seconds */
		$time_log = sprintf( __('Processed in %f seconds', 'tainacan'), $total );
		$this->add_log($time_log);

		$this->process_footer($current_collection_item, $collection_definition);

		$this->finalize_writer();        

		return parent::next_item();
	}

	private function process_header($current_collection_item, $collection_definition) {
		if ($current_collection_item == 0) {
			$this->output_header();
		}
	}

	private function process_footer($current_collection_item, $collection_definition) {
		if ($current_collection_item > $collection_definition['total_items']) {
			parent::output_footer();
		}
	}

	private function get_items($index, $collection_definition) {
		$collection_id = $collection_definition['id'];
		$tainacan_items = \Tainacan\Repositories\Items::get_instance();
		$per_page = parent::get_step_length_items();
		$page = intdiv($index, $per_page) + 1;
		$filters = [
			'posts_per_page' => $per_page,
			'paged'   => $page,
			'order'   => 'DESC',
			'orderby' => 'ID',
			'post_status' => ['publish','private','pending', 'draft']
		];

		parent::add_log("Retrieving $per_page items on page index: $page , item index: $index, in collection " . $collection_definition['id'] );
		$items = $tainacan_items->fetch($filters, $collection_id, 'WP_Query');

		if ( !isset($collection_definition['total_items']) ) {
			$collection_definition['total_items'] = $items->found_posts;
			parent::update_current_collection($collection_definition);
		}
		
		$data = [];
		while ($items->have_posts()) {
			$items->the_post();
			$item = new Entities\Item($items->post);
			
			if ($item instanceof \Tainacan\Entities\Item ) {
				$data[] = [
					'metadata' =>$this->map_item_metadata($item),
					'item' => $item
				];
			} else {
				parent::add_error_log( __('Error processing item', 'tainacan') );
			}
		}
		wp_reset_postdata();
		$dataLen = count($data);
		parent::add_log("Retrieved data size: $dataLen");
		return $data;
	}

	private function map_item_metadata(\Tainacan\Entities\Item $item) {
		
		$mapper = $this->get_current_mapper();
		$metadata = $item->get_metadata();
		if (!$mapper) {
			return $metadata;
		}
		$pre = [];
		foreach ($metadata as $item_metadata) {
			$metadatum = $item_metadata->get_metadatum();
			$meta_mappings = $metadatum->get_exposer_mapping();
			if ( array_key_exists($this->get_mapping_selected(), $meta_mappings) ) {
				
				$pre[ $meta_mappings[$this->get_mapping_selected()] ] = $item_metadata;
			}
		}
		
		// reorder
		$return = [];
		foreach ( $mapper->metadata as $meta_slug => $meta ) {
			if ( array_key_exists($meta_slug, $pre) ) {
				$return[$meta_slug] = $pre[$meta_slug];
			} else {
				$return[$meta_slug] = null;
			}
		}
		
		return $return;
		
	}

	public function process_item($item, $metadata) {
		
		$mapper = $this->get_current_mapper();
		$line = [];
		
		if(!$mapper) {
			$line[] = $item->get_id();
		}

		add_filter('tainacan-item-metadata-get-multivalue-separator', [$this, 'filter_multivalue_separator'], 20);
		add_filter('tainacan-terms-hierarchy-html-separator', [$this, 'filter_hierarchy_separator'], 20);
		
		foreach ($metadata as $meta_key => $meta) {
			if (!$meta || empty($meta->get_value())) {
				$line[] = '';
				continue;
			}
	
			if ($meta->get_metadatum()->get_metadata_type() == 'Tainacan\Metadata_Types\Relationship') {
				$rel = $meta->get_value();
				$line[] = is_array($rel) ? implode($this->get_option('multivalued_delimiter'), $rel) : $rel;
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

		$this->addRowToSheet($line); 
	}
	
	public function addRowToSheet(array $rowData, $sheetIndex = 0) {
		if (!$this->spreadsheet) {
			throw new \Exception('Spreadsheet not initialized. Call initializeWriter() before.');
		}

		$sheet = $this->spreadsheet->getSheet($sheetIndex);
		if (!$sheet) {
			$sheet = new Worksheet($this->spreadsheet, 'Sheet' . ($sheetIndex + 1));
			$this->spreadsheet->addSheet($sheet, $sheetIndex);
		}

		$lastRow = $sheet->getHighestRow() + 1;
		$col = 1;
		foreach ($rowData as $value) {
			$cellCoordinate = Coordinate::stringFromColumnIndex($col) . $lastRow;
			$sheet->setCellValue($cellCoordinate, $value);
			$col++;
		}
	}

	public function get_file_path() {
		return $this->filePath;
	}

	public function get_file_name() {
		return $this->collection_name;
	}

	public function get_content_type() {
		return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	}

	public function filter_multivalue_separator($separator) {
		return $this->get_option('multivalued_delimiter');
	}

	public function filter_hierarchy_separator($separator) {
		return '>>';
	}

	private function get_collections_names() {
		$collections_names = [];
		foreach ($this->collections as $col) {
			$collection = \Tainacan\Repositories\Collections::get_instance()->fetch((int) $col['id'], 'OBJECT');
			$collections_names[] = $collection->get_name();
		}
		return $collections_names;
	}

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
			$message .= __('Your XLSX file is ready! Access it in the link below:', 'tainacan');
			$message .= '<br/><br/>';
			$message .= '<a target="_blank" href="' . $file['url'] . '">Download</a>';
			
			return $message;
			
		} else {
			$this->add_error_log('Output file not found! Maybe you need to correct the permissions of your upload folder');
		}
	}

	public function output_header() {
		$mapper = $this->get_current_mapper();
		$headerRowContents = [];

		if ($mapper) {
			foreach ($mapper->metadata as $meta_slug => $meta) {
				$headerRowContents[] = $meta['field'] ?? $meta_slug;
			}
		} else {
			$headerRowContents = ['special_item_id'];

			$collection = $this->get_current_collection_object();
			if ($collection) {
				$metadata = $collection->get_metadata();
				foreach ($metadata as $meta) {
					$desc_title_meta = $this->get_description_title_meta($meta);
					$headerRowContents[] = $desc_title_meta;
				}
			}

			$headerRowContents[] = 'special_item_status';
			$headerRowContents[] = 'special_document';
			$headerRowContents[] = 'special_thumbnail';
			$headerRowContents[] = 'special_attachments';
			$headerRowContents[] = 'special_comment_status';
			$headerRowContents[] = 'special_item_author';
			$headerRowContents[] = 'special_item_slug';
			$headerRowContents[] = 'creation_date';
			$headerRowContents[] = 'user_last_modified';
			$headerRowContents[] = 'modification_date';
			$headerRowContents[] = 'public_url';
		}

		$sheet = $this->spreadsheet->getActiveSheet();

		$row = 1;
		$col = 1;
		foreach ($headerRowContents as $value) {
			$cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
			$sheet->setCellValue($cellCoordinate, $value);
			$col++;
		}
	}

	public function options_form() {
		ob_start();
		?>

		<div class="field">
			<label class="label"><?php esc_html_e('Multivalued metadata delimiter', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php esc_html_e('Multivalued metadata delimiter', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php esc_html_e('The character used to separate each value inside a cell with multiple values (e.g. ||). Note that the target metadatum must accept multiple values.', 'tainacan'); ?></p>
						</div>
					</div>
			</span>
			<div class="control is-clearfix">
				<input class="input" type="text" name="multivalued_delimiter" value="<?php echo esc_attr($this->get_option('multivalued_delimiter')); ?>">
			</div>
		</div>

		<div class="field">
			<label class="label"><?php esc_html_e('Compound children delimiter', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php esc_html_e('Compound children delimiter', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php esc_html_e('The character used to separate each child of compound metadata (e.g. , or ;)', 'tainacan'); ?></p>
						</div>
					</div>
			</span>
			<div class="control is-clearfix">
				<input class="input" type="text" name="delimiter" maxlength="1" value="<?php echo esc_attr($this->get_option('delimiter')); ?>">
			</div>
		</div>

		<div class="field">
			<label class="label"><?php esc_html_e('Enclosure for compound children', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php esc_html_e('Enclosure', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php esc_html_e('The character that wraps the content of each compound child value in the cell if necessary (e.g. ")', 'tainacan'); ?></p>
						</div>
					</div>
			</span>
			<div class="control is-clearfix">
				<input class="input" type="text" name="enclosure" value="<?php echo esc_attr($this->get_option('enclosure')); ?>">
			</div>
		</div>

		<div class="field">
			<label class="label"><?php esc_html_e('Include metadata section name', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button">
						<span class="icon is-small">
							<i class="tainacan-icon tainacan-icon-help" ></i>
						</span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php esc_html_e('Include metadata section name', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php esc_html_e('Include metadatum section name after the metadatum name. Metadata inside the default section are not modified', 'tainacan'); ?></p>
						</div>
					</div> 
			</span>
			<div class="control is-clearfix">
				<label class="checkbox">
					<input
						type="checkbox" 
						name="add_section_name" checked value="yes"
						>
					<?php esc_html_e('Yes', 'tainacan'); ?>
				</label>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}
}
