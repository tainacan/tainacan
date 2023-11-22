<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Numeric extends Metadata_Type {

	function __construct(){
		// call metadatum type constructor
		parent::__construct();
		$this->set_name( __('Numeric', 'tainacan') );
		$this->set_primitive_type('float');
		$this->set_component('tainacan-numeric');
		$this->set_form_component('tainacan-form-numeric');
		$this->set_description( __('A numeric value, integer or float', 'tainacan') );
		$this->set_preview_template('
			<div>
				<div class="control is-clearfix">
					<input type="number" placeholder="3,1415" class="input"> 
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
				'description' => __( 'The amount to be increased or decreased when clicking on the metadatum control buttons. This also defines whether the input accepts decimal numbers.', 'tainacan' ),
			],
			'min' => [
				'title' => __( 'Minimum', 'tainacan' ),
				'description' => __( 'The minimum value that the input will accept.', 'tainacan' ),
			],
			'max' => [
				'title' => __( 'Maximum', 'tainacan' ),
				'description' => __( 'The maximum value that the input will accept.', 'tainacan' ),
			]
		];
	}

	public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$value = is_array($value) ? $value : [$value];

		foreach ($value as $numeric_value) {
			if( !empty($numeric_value) && !is_numeric($numeric_value) ) {
				$this->add_error( sprintf(__('The value (%s) is not a valid number', 'tainacan'), $numeric_value ) );
				return false;
			}
		}
		return true;

		
	}
}