<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Date extends Filter_Type {

	function __construct(){
		$this->set_name( __('Date', 'tainacan') );
		$this->set_supported_types(['date']);
		$this->set_component('tainacan-filter-date');
		$this->set_form_component('tainacan-filter-form-date');
		$this->set_use_max_options(false);
		$this->set_default_options([
			'comparators' => [ '=', '!=', '>', '>=', '<', '<=' ]
		]);
		$this->set_preview_template('
			<div>
				<div>
					<div class="date-filter-container">
						<div class="dropdown is-active">
							<div role="button" class="dropdown-trigger">
								<button class="button is-white">
									<span class="icon is-small">
										<i>=</i>
									</span>
									<span class="icon">
										<i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"></i>
									</span>
								</button>
							</div>
							<div class="background" style="display: none;"></div>
							<div class="dropdown-menu" style="display: none;">
								<div role="list" class="dropdown-content">
									<a class="dropdown-item is-active">=&nbsp; ' . __('Equal', 'tainacan') .'</a>
									<a class="dropdown-item">≠&nbsp; '. __('Not equal', 'tainacan') .'</a>
									<a class="dropdown-item">&gt;&nbsp; '. __('After', 'tainacan') .'</a>
									<a class="dropdown-item">≥&nbsp; '. __('After (inclusive)', 'tainacan') .'</a>
									<a class="dropdown-item">&lt;&nbsp; '. __('Before', 'tainacan') .'</a>
									<a class="dropdown-item">≤&nbsp;  '. __('Before (inclusive)', 'tainacan') .'</a>
								</div>
							</div>
						</div>
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
				</div>
			</div>
		');
	}


	/**
	 * @inheritdoc
	 */
	public function get_form_labels(){
		return [
			'comparators' => [
				'title' => __( 'Enabled comparators', 'tainacan' ),
				'description' => __( 'A list of comparators to be available in the filter, such as equal, greater than, smaller than, etc.', 'tainacan' ),
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

		if ( empty( $this->get_option('comparators') ) )
			return [
				'comparators' => __('"Comparators" array is required', 'tainacan')
			];

		if ( count( $this->get_option('comparators') ) < 1 )
			return [
				'comparators' => __('At least one comparator should be provided', 'tainacan')
			];

		return true;
	}

}

class Date_Helper {
	use \Tainacan\Traits\Singleton_Instance;

	protected function init() {
		add_filter( 'tainacan-api-items-tainacan-filter-date-filter-arguments', [$this, 'format_filter_arguments']);
	}

	function format_filter_arguments( $filter_arguments ) {
		if (
			!isset($filter_arguments['compare']) ||
			!isset($filter_arguments['label'])
		) {
			return $filter_arguments;
		}

		if (count($filter_arguments['label']) === 1) {
			switch ($filter_arguments['compare']) {
				case '=':
					$filter_arguments['label'] = '&#61; ' . $filter_arguments['label'][0];
					break;
				case '!=':
					$filter_arguments['label'] = '&#8800; ' . $filter_arguments['label'][0];
					break;
				case '>':
					$filter_arguments['label'] = '&#62; ' . $filter_arguments['label'][0];
					break;
				case '>=':
					$filter_arguments['label'] = '&#8805; ' . $filter_arguments['label'][0];
					break;
				case '<':
					$filter_arguments['label'] = '&#60; ' . $filter_arguments['label'][0];
					break;
				case '<=':
					$filter_arguments['label'] = '&#8804; ' . $filter_arguments['label'][0];
				break;
				default:
					$filter_arguments['label'] = $filter_arguments['label'][0];
			}
		}

		return $filter_arguments;
	}
}
Date_Helper::get_instance();