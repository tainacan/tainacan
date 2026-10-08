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
		$this->set_default_options([
			'use_rich_text_editor' => 'no'
		]);
		$this->set_preview_template('
			<div>
				<div class="control is-clearfix">
					<textarea rows="3" placeholder="' . __('Type some long text here...', 'tainacan') . '" class="input"></textarea> 
				</div>
			</div>
		');
	
	}

	/**
	 * @inheritdoc
	 */
	public function allows_links() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function get_form_labels(){
		return [
			'maxlength' => [
				'title' => __( 'Maximum of characters', 'tainacan' ),
				'description' => __( 'Limits the character input to a maximum value an displays a counter.', 'tainacan' ),
			],
			'use_rich_text_editor' => [
				'title' => __( 'Use rich text editor', 'tainacan' ),
				'description' => __( 'Replaces the textarea with the rich text editor when it is enabled for Tainacan.', 'tainacan' ),
			]
		];
	}

	/**
	 * Treat an editor value with no visible content as empty.
	 */
	public static function normalize_value( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ __CLASS__, 'normalize_value' ], $value );
		}
		if ( ! is_string( $value ) || $value === '' ) {
			return $value;
		}
		$text = html_entity_decode( wp_strip_all_tags( $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$visible_text = preg_replace( '/[\s\x{00A0}]+/u', '', $text );
		if ( $visible_text === '' && ! preg_match( '/<(?:img|video|audio|iframe|embed|object|svg|hr)\b/i', $value ) ) {
			return '';
		}
		return $value;
	}
	
	public function get_multivalue_prefix() {
		return '<div class="metadata-value">';
	}
	
	public function get_multivalue_suffix() {
		return '</div>';
	}

	/**
	 * Render stored text as HTML with links and paragraphs.
	 * @return string
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$return = '';

		if ( $item_metadata->is_multiple() ) {
			$html_formatting = $item_metadata->get_metadatum()->get_html_formatting();
			if ( $html_formatting === 'list' ) {
				$total = count( $value );
				if ( $total === 1 ) {
					$return .= wpautop($this->make_clickable_links( reset( $value ) ));
				} elseif ( $total > 1 ) {
					$return .= '<ul>';
					foreach ( $value as $el ) {
						$return .= '<li>' . wpautop($this->make_clickable_links($el)) . '</li>';
					}
					$return .= '</ul>';
				}
			} else {
				$total = sizeof($value);
				$count = 0;
				$prefix = $item_metadata->get_multivalue_prefix();
				$suffix = $item_metadata->get_multivalue_suffix();
				$separator = $item_metadata->get_multivalue_separator();
				foreach ( $value as $el ) {
					$return .= $prefix;
					$return .= wpautop($this->make_clickable_links($el));
					$return .= $suffix;
					$count++;
					if ( $count < $total ) {
						$return .= $separator;
					}
				}
			}
		} else {
			$return = wpautop($this->make_clickable_links($value));
		}

		return 
			/**
			 * Filter the HTML representation of the value of a textarea metadatum
			 * 
			 * @param string $return The HTML representation of the value
			 * @param \Tainacan\Entities\Item_Metadata_Entity $item_metadata The Item_Metadata_Entity object
			 * 
			 * @return string The HTML representation of the item metadatum value
			 */
			apply_filters( 'tainacan-item-metadata-get-value-as-html--type-textarea', force_balance_tags($return), $item_metadata );
	}
}