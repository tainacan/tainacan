<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Numeric_List_Interval extends Filter_Type {

	function __construct(){
		$this->set_name( __('Numeric Interval List', 'tainacan') );
		$this->set_supported_types(['float']);
		$this->set_component('tainacan-filter-numeric-list-interval');
		$this->set_form_component('tainacan-filter-form-numeric-list-interval');
		$this->set_use_max_options(false);
		$this->set_default_options([
			'intervals' => [],
			'showIntervalOnTag' => false
		]);
		$this->set_preview_template('
			<div class="collapse show">
				<div class="dropdown is-active">
					<div role="button" class="dropdown-trigger">
						<button class="button is-white">
							List
							<span class="icon">
								<i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"></i>
							</span>
						</button>
					</div>
					<div class="background"></div>
					<div class="dropdown-menu">
						<div role="list" class="dropdown-content">
							<a class="dropdown-item is-active">Top 10</a>
							<a class="dropdown-item">Top 20</a>
							<a class="dropdown-item">Top 30</a>
						</div>
					</div>
				</div>
			</div>
		');
	}

	public function get_form_labels() {
		return [
			'intervals' => [
				'title' => __('Predefined intervals','tainacan'),
				'description' => __('A list of predefined intervals that the filter will offer on a select box.','tainacan')
			],
			'showIntervalOnTag' => [
				'title' => __('Interval on tags', 'tainacan'),
				'description' => __('Whether the applied interval values should appear on filter tags.')
			]
		];
	}
}

class Numeric_List_Interval_Helper {
	use \Tainacan\Traits\Singleton_Instance;

	protected function init() {
		add_filter( 'tainacan-api-items-tainacan-filter-numeric-list-interval-filter-arguments', [$this, 'format_filter_arguments']);
	}

	function format_filter_arguments( $filter_arguments ) {
		if (
			!isset($filter_arguments['filter']) ||
			!isset($filter_arguments['filter']['filter_type_options']) ||
			!isset($filter_arguments['filter']['filter_type_options']['intervals'])
		) {
			return $filter_arguments;
		}

		$intervals = $filter_arguments['filter']['filter_type_options']['intervals'];
		foreach($intervals as $interval) {
			if (
				$interval['from'] == $filter_arguments['value'][0] &&
				$interval['to'] == $filter_arguments['value'][1]
			) {
				$filter_arguments['label'] = $interval['label'];

				if ( isset($filter_arguments['filter']['filter_type_options']['showIntervalOnTag']) && $filter_arguments['filter']['filter_type_options']['showIntervalOnTag'] )
					$filter_arguments['label'] .= ' (' . $filter_arguments['value'][0] . '-' . $filter_arguments['value'][1] . ')';

				break;
			}
		}
		return $filter_arguments;
	}
}
Numeric_List_Interval_Helper::get_instance();
