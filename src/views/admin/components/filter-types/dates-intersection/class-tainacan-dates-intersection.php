<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Dates_Intersection extends Filter_Type {

	function __construct(){
		$this->set_name( __('Dates Intersection', 'tainacan') );
		$this->set_supported_types(['date']);
		$this->set_component('tainacan-filter-dates-intersection');
		$this->set_form_component('tainacan-filter-form-dates-intersection');
		$this->set_default_options([
			'secondary_filter_metadatum_id' => '',
			'secondary_filter_metadatum_name' => '',
			'first_comparator' => '>=',
			'second_comparator' => '<=',
			'accept_date_interval' => 'no'
		]);
		$this->set_use_max_options(false);
		$this->set_preview_template('
			<div>
				<div class="datepicker control is-small">
					<div class="dropdown is-bottom-left is-mobile-modal">
						<div role="button" class="dropdown-trigger">
							<div class="control has-icons-left is-small is-clearfix">
								<input type="text" autocomplete="off" placeholder=" '. __('Select a date', 'tainacan') .'" class="input is-small">
								<span class="icon is-left is-small"><i class="mdi mdi-calendar-today"></i></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		');

	}

	/**
	 * @inheritdoc
	 */
	public function get_form_labels(){
		return [
			'secondary_filter_metadatum_id' => [
				'title' => __( 'Second date metadatum', 'tainacan' ),
				'description' => __( 'The other metadatum to which this filter will compare values to find if there is an intersection of dates.', 'tainacan' ),
			],
			'secondary_filter_metadatum_name' => [
				'title' => __( 'Second date metadatum', 'tainacan' ),
				'description' => __( 'Label of the other metadatum to which this filter will compare values to find if there is an intersection of dates.', 'tainacan' ),
			],
			'first_comparator' => [
				'title' => __( 'First comparator', 'tainacan' ),
				'description' => __( 'Comparator to be used for checking the first metadata value.', 'tainacan' ),
			],
			'second_comparator' => [
				'title' => __( 'Second comparator', 'tainacan' ),
				'description' => __( 'Comparator to be used for checking the second metadata value.', 'tainacan' ),
			],
			'accept_date_interval' => [
				'title' => __( 'Accept date interval', 'tainacan' ),
				'description' => __( 'If checked, the filter will accept date intervals as values.', 'tainacan' ),
			]
		];
	}

	/**
	 * @param \Tainacan\Entities\Filter $filter
	 * @return array|bool true if is validate or array if has error
	 */
	public function validate_options(\Tainacan\Entities\Filter $filter) {

		if ( !in_array($filter->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
			return true;

		$errors = [];

		if ( empty($this->get_option('secondary_filter_metadatum_id')) )
			$errors['secondary_filter_metadatum_id'] = __('The secondary date metadatum is required.','tainacan');

		if ( empty($this->get_option('first_comparator')) )
			$errors['first_comparator'] = __('The first comparator is required.','tainacan');
		
		if ( empty($this->get_option('second_comparator')) )
			$errors['second_comparator'] = __('The second comparator is required.','tainacan');

		if ( empty($this->get_option('accept_date_interval')) )
			$errors['accept_date_interval'] = __('The filter should define if it accepts date interval.','tainacan');

		return count($errors) > 0 ? $errors : true;
	}
	
}

class Dates_Intersection_Interval_Helper {
	use \Tainacan\Traits\Singleton_Instance;

	protected function init() {
		add_filter( 'tainacan-api-items-tainacan-filter-dates-intersection-filter-arguments', [$this, 'format_filter_arguments']);
	}

	function format_filter_arguments( $filter_arguments ) {
		if (
			!isset($filter_arguments['compare']) ||
			!isset($filter_arguments['label'])
		) {
			return $filter_arguments;
		}
		
		if (
			is_array($filter_arguments['label']) &&
			count($filter_arguments['label']) === 2
		) {
			$filter_arguments['label'] = $filter_arguments['label'][0] . ' - ' . $filter_arguments['label'][1];
		}
		if (
			isset( $filter_arguments['filter'] ) && 
			isset( $filter_arguments['filter']['metadatum'] ) &&
			isset( $filter_arguments['filter']['metadatum']['metadatum_name'] ) 
		) {
			$filter_arguments['filter']['name'] = $filter_arguments['filter']['metadatum']['metadatum_name'];
		}
		if (
			isset( $filter_arguments['filter'] ) && 
			isset( $filter_arguments['filter']['filter_type_options'] ) &&
			isset( $filter_arguments['filter']['filter_type_options']['secondary_filter_metadatum_name'] ) &&
			!empty( $filter_arguments['filter']['filter_type_options']['secondary_filter_metadatum_name'] )
		) {
			$filter_arguments['filter']['name'] = $filter_arguments['filter']['name'] . ' - ' . $filter_arguments['filter']['filter_type_options']['secondary_filter_metadatum_name'];
		}
		return $filter_arguments;
	}
}
Dates_Intersection_Interval_Helper::get_instance();