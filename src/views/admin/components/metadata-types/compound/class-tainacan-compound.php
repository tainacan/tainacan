<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;
use Tainacan\Entities\Item_Metadata_Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Compound extends Metadata_Type {

	function __construct() {
		// call metadatum type constructor
		parent::__construct();
		$this->set_name( __('Compound', 'tainacan') );
		$this->set_description( __('A compound metadatum can have groups of values of different types.', 'tainacan') );
		$this->set_sortable( false );
		$this->set_primitive_type('compound');
		$this->set_component('tainacan-compound');
		$this->set_form_component('tainacan-form-compound');
		$this->set_preview_template('
			<div class="child-metadata-inputs">
				<a class="collapse-all">
					' . __('Collapse all', 'tainacan') . '
					<span class="icon">
						<i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"></i>
					</span>
				</a>
				<div class="multiple-inputs">
					<div class="field">
						<span class="collapse-handle">
							<span class="icon">
								<i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"></i>
							</span>
							<label class="label has-tooltip">' . __('Text', 'tainacan') . '</label>
						</span>
						<div>
							<div class="control is-clearfix">
								<input type="text" placeholder="' . __('Type here...') . '" autocomplete="on" id="tainacan-text-texto-simples" class="input">
							</div>
						</div>
					</div>
					<div class="field is-last-input">
						<span class="collapse-handle">
							<span class="icon">
								<i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"></i>
							</span>
							<label class="label has-tooltip"> ' . __('Number', 'tainacan') . '</label>
						</span>
						<div>
							<div class="control is-clearfix">
								<input placeholder="3,1415" type="number" autocomplete="on" id="tainacan-numeric-numerico" step="0.01" class="input" lang="en">
							</div>
						</div>
					</div>
				</div>
			</div>
		');
		add_action( 'tainacan-insert-tainacan-metadatum', array( &$this, 'save_children' ), 10, 1 );
		add_action( 'tainacan-pre-delete-tainacan-metadatum', array( &$this, 'delete_children' ), 10, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_form_labels(){
		return [
			'children' => [
				'title' => __( 'Child Metadata', 'tainacan' ),
				'description' => __( 'The list of inner metadata that compose this compound metadata.', 'tainacan' ),
			]
		];
	}
	
	/**
	 * Gets print-ready version of the options list in html
	 *
	 * Checks if at least one option exists, otherwise return an empty string
	 * 
	 * @return string An html content with labels and values for the options or an empty string
	 */
	public function get_options_as_html() {
		return '';
	}

	/**
	 * save options and remove old children
	 * @param $options
	 */
	public function save_children( $metadatum ) {
		$metadatum_type_object = $metadatum->get_metadata_type_object();

		if( $metadatum_type_object instanceof \Tainacan\Metadata_Types\Compound ) {
			$options = $metadatum->get_metadata_type_options();

			if( isset( $options['parent'] ) ) {
				$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
				$childrens = $Tainacan_Metadata->fetch( ['parent' => $options['parent'] ], "OBJECT" );
				foreach ($childrens as $child) {
					if ($child->get_status() != $metadatum->get_status()) {
						$child->set_status( $metadatum->get_status() );
						if( $child->validate() ) {
							$Tainacan_Metadata->update( $child );
						}
					}
				}
			}

			if( isset( $options['parent'] ) )
				 return;
			
			$options['parent'] = $metadatum->get_ID();
			$metadatum->set_metadata_type_options($options);
			if( $metadatum->validate() ) {
				$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
				$Tainacan_Metadata->update( $metadatum );
			}
		}
	}

	public function delete_children ( $metadatum) {
		$metadatum_type_object = $metadatum->get_metadata_type_object();

		if( $metadatum_type_object instanceof \Tainacan\Metadata_Types\Compound ) {
			$options = parent::get_options();
			if( isset( $options['parent'] ) ) {
				$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
				$childrens = $Tainacan_Metadata->fetch( ['parent' => $options['parent'] ], "OBJECT" );
				foreach ($childrens as $child) {
					$Tainacan_Metadata->trash($child);
				}
			}
		}
	}

	/**
	 * Overrides and bring back all data for the children
	 * that were not set yet.
	 * @return array Metadata type options
	 */
	public function get_options() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$options = parent::get_options();
		$options['children_order'] = isset($options['children_order']) ? $options['children_order'] : [];
		$options['children_objects'] = [];
		$children_not_ordinate = [];

		if( isset( $options['parent'] ) ) {
			$childrens = $Tainacan_Metadata->fetch( ['parent' => $options['parent'] ], "OBJECT" );
			foreach ($childrens as $child) {
				$item_arr = $child->_toArray();
				$item_arr['metadata_type_object'] = $child->get_metadata_type_object()->_toArray();
			 	$item_arr['current_user_can_edit'] = $child->can_edit();
			 	ob_start();
			 	$child->get_metadata_type_object()->form();
			 	$form = ob_get_clean();
				$item_arr['edit_form'] = $form;
				
				$index = array_search( $item_arr['id'], array_column( $options['children_order'], 'id' ) );
				if ( $index !== false ) {
					$options['children_objects'][$index] = $item_arr;
				} else {
					$children_not_ordinate[] = $item_arr;
				}
			}
			ksort( $options['children_objects'] );
			$options['children_objects'] = array_merge($options['children_objects'], $children_not_ordinate);
		}
		return $options;
	}
	
		
	/**
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, each HTML representation of the value of each metadatum composing this metadata
	 */
	public function get_value_as_html(Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$separator = $item_metadata->get_multivalue_separator();
		$options = $item_metadata->get_metadatum()->get_metadata_type_options();
		$order = $options['children_order'];
		$return = '';

		if ( empty($value) )
			return $return;
		
		if ( $item_metadata->is_multiple() ) {
			$elements = [];
			foreach ( $value as $compound_element ) {
				if ( !empty($compound_element) ) {
					$metadata_value =  array_fill(0, count($compound_element), null);
					$metadata_value_not_ordinate = [];
					foreach ( $compound_element as $meta_id => $meta ) {
						$index = array_search( $meta_id, array_column( $order, 'id' ) );
						if ( $meta instanceof Item_Metadata_Entity && $meta->get_value_as_html() != '' ) {
							$html = $this->get_meta_html($meta);
							if ( $index !== false ) {
								$metadata_value[$index] = $html;
							} else {
								$metadata_value_not_ordinate[] = $html;
							}
						}
					}
					$elements[] = '<div class="tainacan-compound-metadatum">' . implode("\n", array_merge($metadata_value, $metadata_value_not_ordinate)) . "</div> \n" ;
				}
			}
			$return = implode($separator, $elements);
		} else {
			$metadata_value = array_fill(0, count($value), null);
			$metadata_value_not_ordinate = [];
			foreach ( $value as $meta_id => $meta ) {
				$index = array_search( $meta_id, array_column( $order, 'id' ) );
				if ( $meta instanceof Item_Metadata_Entity && $meta->get_value_as_html() != '' ) {
					$html = $this->get_meta_html($meta);
					if ( $index !== false ) {
						$metadata_value[intval($index)] = $html;
					} else {
						$metadata_value_not_ordinate[] = $html;
					}
				}
			}
			$return = implode("\n", array_merge($metadata_value, $metadata_value_not_ordinate));
		}
		
		return "<div class='tainacan-compound-group'> {$return} </div>";
	}

	// /**
	//  * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as a string
	//  * @param  Item_Metadata_Entity $item_metadata 
	//  * @return string The String representation of the value, containing one or multiple items names, linked to the item page
	//  */
	public function get_value_as_string(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$return = '';

		if ( empty($value) )
			return $return;

		if ( $item_metadata->is_multiple() ) {
			foreach ( $value as $compound_element ) {
				if ( !empty($compound_element) ) {
					foreach ( $compound_element as $meta_id => $meta ) {
						$return .= $meta->get_value_as_string() . " ";
					}
				}
				$return .= "\n";
			}
		} else {
			foreach ( $value as $meta_id => $meta ) {
				$return .= $meta->get_value_as_string() . " ";
			}
		}
		return $return;
	}

	private function get_meta_html(Item_Metadata_Entity $meta) {
		$html = '';

		if ($meta instanceof Item_Metadata_Entity && !empty($meta->get_value_as_html())) {
			ob_start();
			?>
				<div class="tainacan-metadatum metadata-type-<?php echo $meta->get_metadatum()->get_metadata_type_object()->get_slug(); ?> metadata-slug-<?php echo $meta->get_metadatum()->get_slug(); ?>">
					<?php
						$child_label = $meta->get_metadatum()->get_name();
						$child_label_before = apply_filters('tainacan-get-child-item-metadatum-as-html-before-label', '<h4 class="label child-metadatum-label">', $meta);
						$child_label_after = apply_filters('tainacan-get-child-item-metadatum-as-html-after-label', '</h4>', $meta);
						echo $child_label_before . esc_html($child_label) . $child_label_after;

						$child_value = $meta->get_value_as_html();
						$child_value_before = apply_filters('tainacan-get-child-item-metadatum-as-html-before-value', '<p class="child-metadatum-value">', $meta);
						$child_value_after = apply_filters('tainacan-get-child-item-metadatum-as-html-after-value', '</p>', $meta);
						echo $child_value_before . wp_kses_tainacan($child_value) . $child_value_after;
					?>
				</div>
			<?php
			$html = ob_get_contents();
			ob_end_clean();

		}

		return $html;
	}

}