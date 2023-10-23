<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Textarea extends Metadata_Type {
	use \Tainacan\Traits\Formatter_Text;

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('long_string');
		$this->set_component('tainacan-textarea');
		$this->set_form_component('tainacan-form-textarea');
		$this->set_name( __('Textarea', 'tainacan') );
		$this->set_description( __('A textarea with multiple lines', 'tainacan') );
		$this->set_preview_template('
			<div>
				<div class="control is-clearfix">
					<textarea rows="3" placeholder="' . __('Type some long text here...') . '" class="input"></textarea> 
				</div>
			</div>
		');
	
	}

	/**
	 * @inheritdoc
	 */
	public function get_form_labels(){
		return [
			'maxlength' => [
				'title' => __( 'Maximum of characters', 'tainacan' ),
				'description' => __( 'Limits the character input to a maximum value an displays a counter.', 'tainacan' ),
			]
		];
	}
	
	public function get_multivalue_prefix() {
		return '<p>';
	}
	
	public function get_multivalue_suffix() {
		return '</p>';
	}

	/**
	 * Get the value as a HTML string with links and breakline tag.
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
				$return .= $prefix;
				$return .= nl2br($this->make_clickable_links($el));
				$return .= $suffix;
				$count ++;
				if ($count < $total)
					$return .= $separator;
			}
		} else {
			$return = nl2br($this->make_clickable_links($value));
		}
		return force_balance_tags($return);
	}
}