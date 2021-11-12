<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Date_Interval extends Filter_Type {

	function __construct(){
		$this->set_name( __('Date Interval', 'tainacan') );
		$this->set_supported_types(['date']);
		$this->set_component('tainacan-filter-date-interval');
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
				<p class="is-size-7 has-text-centered is-marginless">until</p> 
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
}

class Date_Interval_Interval_Helper {
	use \Tainacan\Traits\Singleton_Instance;

	protected function init() {
		add_filter( 'tainacan-api-items-tainacan-filter-date-interval-filter-arguments', [$this, 'format_filter_arguments']);
	}

	function format_filter_arguments( $filter_arguments ) {
		if (
			!isset($filter_arguments['compare']) ||
			!isset($filter_arguments['label'])
		) {
			return $filter_arguments;
		}

		if (
			strtoupper($filter_arguments['compare']) === 'BETWEEN' &&
			is_array($filter_arguments['label']) &&
			count($filter_arguments['label']) === 2
		) {
			$filter_arguments['label'] = $filter_arguments['label'][0] . ' - ' . $filter_arguments['label'][1];
		}

		return $filter_arguments;
	}
}
Date_Interval_Interval_Helper::get_instance();