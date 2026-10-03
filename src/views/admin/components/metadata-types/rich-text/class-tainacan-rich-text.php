<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Rich HTML metadata edited with TinyMCE from its first value.
 */
class Rich_Text extends Textarea {

	/**
	 * Treat TinyMCE's empty paragraph as an empty metadata value.
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

	public function __construct() {
		parent::__construct();
		$this->set_component( 'tainacan-rich-text' );
		$this->set_form_component( 'tainacan-form-rich-text' );
		$this->set_name( __( 'Rich Text', 'tainacan' ) );
		$this->set_description( __( 'Formatted text with paragraphs, lists, and links', 'tainacan' ) );
		$this->set_preview_template( '<div class="content"><p><strong>' . esc_html__( 'Formatted text', 'tainacan' ) . '</strong></p></div>' );
	}

	/**
	 * Return the stored HTML without plain-text paragraph or link conversion.
	 */
	public function get_value_as_html( \Tainacan\Entities\Item_Metadata_Entity $item_metadata ) {
		$value = $item_metadata->get_value();
		if ( ! $item_metadata->is_multiple() ) {
			$return = wp_kses_post( (string) $value );
		} else {
			$values = is_array( $value ) ? $value : [];
			$values = array_map( 'wp_kses_post', $values );
			if ( $item_metadata->get_metadatum()->get_html_formatting() === 'list' ) {
				$return = count( $values ) === 1 ? reset( $values ) : '';
				if ( count( $values ) > 1 ) {
					$return = '<ul>';
					foreach ( $values as $entry ) {
						$return .= '<li>' . $entry . '</li>';
					}
					$return .= '</ul>';
				}
			} else {
				$parts = [];
				foreach ( $values as $entry ) {
					$parts[] = $item_metadata->get_multivalue_prefix() . $entry . $item_metadata->get_multivalue_suffix();
				}
				$return = implode( $item_metadata->get_multivalue_separator(), $parts );
			}
		}

		return apply_filters( 'tainacan-item-metadata-get-value-as-html--type-rich-text', $return, $item_metadata );
	}
}
