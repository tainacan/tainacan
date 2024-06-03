<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Numeric extends Filter_Type {

	function __construct(){
		$this->set_name( __('Numeric', 'tainacan') );
		$this->set_supported_types(['float']);
		$this->set_component('tainacan-filter-numeric');
		$this->set_form_component('tainacan-filter-form-numeric');
		$this->set_use_max_options(false);
		$this->set_default_options([
			'step' => 1,
			'comparators' => [ '=', '!=', '>', '>=', '<', '<=' ]
		]);
		$this->set_preview_template('
			<div>
				<div>
					<div class="numeric-filter-container">
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
									<a class="dropdown-item">&gt;&nbsp; '. __('Greater than', 'tainacan') .'</a>
									<a class="dropdown-item">≥&nbsp; '. __('Greater than or equal to', 'tainacan') .'</a>
									<a class="dropdown-item">&lt;&nbsp; '. __('Less than', 'tainacan') .'</a>
									<a class="dropdown-item">≤&nbsp;  '. __('Less than or equal to', 'tainacan') .'</a>
								</div>
							</div>
						</div>
					<div class="b-numberinput field is-grouped">
						<p class="control">
							<button type="button" class="button is-primary is-small">
								<span class="icon is-small">
									<i class="mdi mdi-minus"></i>
								</span>
							</button>
						</p>
						<div class="control is-small is-clearfix">
							<input type="number" step="0.01" class="input is-small" value="1.5">
						</div>
						<p class="control">
							<button type="button" class="button is-primary is-small">
								<span class="icon is-small">
									<i class="mdi mdi-plus"></i>
								</span>
							</button>
						</p>
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
			'step' => [
				'title' => __( 'Step', 'tainacan' ),
				'description' => __( 'The amount to be increased or decreased when clicking on the filter control buttons. This also defines whether the input accepts decimal numbers.', 'tainacan' ),
			],
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

		$errors = [];

		if ( empty($this->get_option('step')) )
			$errors[] = [
				'step' => __('"Step" value is required','tainacan')
			];

		if ( empty($this->get_option('comparators')) )
			$errors[] = [
				'comparators' => __('"Comparators" array is required', 'tainacan')
			];
		
		if ( count( $this->get_option('comparators') ) < 1 )
			$errors[] = [
				'comparators' => __('At least one comparator should be provided', 'tainacan')
			];

		return count($errors) ? $errors : true;
	}
}

class Numeric_Helper {
	use \Tainacan\Traits\Singleton_Instance;

	protected function init() {
		add_filter( 'tainacan-api-items-tainacan-filter-numeric-filter-arguments', [$this, 'format_filter_arguments']);
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
Numeric_Helper::get_instance();