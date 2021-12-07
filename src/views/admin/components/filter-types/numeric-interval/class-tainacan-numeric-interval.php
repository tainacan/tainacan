<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Numeric_Interval extends Filter_Type {

	function __construct(){
		$this->set_name( __('Numeric Interval', 'tainacan') );
		$this->set_supported_types(['float']);
		$this->set_component('tainacan-filter-numeric-interval');
		$this->set_form_component('tainacan-filter-form-numeric-interval');
		$this->set_use_max_options(false);
		$this->set_default_options([
			'step' => 1
		]);
		$this->set_preview_template('
			<div>
				<div class="b-numberinput field is-grouped">
					<p class="control">
						<button type="button" class="button is-primary is-small">
							<span class="icon is-small">
								<i class="mdi mdi-minus"></i>
							</span>
						</button>
					</p>
					<div class="control is-small is-clearfix">
						<input type="number" step="0.01" class="input is-small" value="6">
					</div>
					<p class="control">
						<button type="button" class="button is-primary is-small">
							<span class="icon is-small">
								<i class="mdi mdi-plus"></i>
							</span>
						</button>
					</p>
				</div>
				<p class="is-size-7 has-text-centered is-marginless">until</p> 
				<div class="b-numberinput field is-grouped">
					<p class="control">
						<button type="button" class="button is-primary is-small">
							<span class="icon is-small">
								<i class="mdi mdi-minus"></i>
							</span>
						</button>
					</p>
					<div class="control is-small is-clearfix">
						<input type="number" step="0.01" class="input is-small" value="10">
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
		');
	}
	
	public function get_form_labels(){
		return [
			'step' => [
				'title' => __( 'Step', 'tainacan' ),
				'description' => __( 'The amount to be increased or decreased when clicking on the filter control buttons. This also defines whether the input accepts decimal numbers.', 'tainacan' ),
			],
			'custom' => ['title' => __('Custom interval','tainacan')],
		];
	}
}

class Numeric_Interval_Interval_Helper {
	use \Tainacan\Traits\Singleton_Instance;

	protected function init() {
		add_filter( 'tainacan-api-items-tainacan-filter-numeric-interval-filter-arguments', [$this, 'format_filter_arguments']);
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
Numeric_Interval_Interval_Helper::get_instance();