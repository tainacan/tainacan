<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Item_Metadata_Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Date extends Metadata_Type {

	private $format;
	public $output_date_format;

	function __construct() {
		// call metadatum type constructor
		parent::__construct();
		$this->set_primitive_type('date');
		$this->set_component('tainacan-date');
		$this->set_name( __('Date', 'tainacan') );
		$this->set_description( __('Exact date type, with day, month and year.', 'tainacan') );
		$this->set_preview_template('
			<div>
				<div class="control is-inline">
					<input type="text" placeholder="' . __('mm/dd/yyyy') . '" class="input"></input>
				</div>
			</div>
		');
		$this->output_date_format = get_option('date_format');
		$this->format = 'Y-m-d';
	}

	public function validate( Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();

		if (is_array($value)) {
			foreach ($value as $date_value) {
				if(!empty($date_value)) {
					$d = \DateTime::createFromFormat($this->format, $date_value);
					if (!$d || $d->format($this->format) !== $date_value) {
						$this->add_error($this->format_error_msg($date_value));
						return false;
					}
				}
			}
			return true;
		}

		$d = \DateTime::createFromFormat($this->format, $value);
		if (!$d || $d->format($this->format) !== $value) {
			$this->add_error($this->format_error_msg($value));
			return false;
		}
		return true;
	}
	
	/**
	 * Get the value as a HTML string with proper date format set in admin
	 * @return string
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		$return = '';
		if ( $item_metadata->is_multiple() ) {
			$total = sizeof($value);
			$count = 0;
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			foreach ( $value as $el ) {
				if( empty( $el ) ) 
					continue;
				$return .= $prefix;
				$return .= $this->format_date_value($el);
				$return .= $suffix;
				$count ++;
				if ($count < $total)
					$return .= $separator;
			}
		} else {
			$return = $this->format_date_value($value);
		}

		return $return;
	}

	private function format_date_value($value) {
		if (empty($value))
			return "";
		return mysql2date($this->output_date_format, ($value));
	}

	private function format_error_msg($value) {
		return sprintf(
			__('Invalid date format. Expected format is %s, got %s.', 'tainacan'),
			$this->format,
			$value
		);
	}

}