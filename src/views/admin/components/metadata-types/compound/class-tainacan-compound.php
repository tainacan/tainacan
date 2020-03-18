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
		$this->set_description( __('A compound metadata can have different types with groups of values.', 'tainacan') );
		$this->set_primitive_type('compound');
		$this->set_component('tainacan-compound');
		$this->set_form_component('tainacan-form-compound');
		$this->set_preview_template('
			<div>
				<div class="control is-clearfix">
						Compound
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
	 * save options and remove old children
	 * @param $options
	 */
	public function save_children( $metadatum ) {
		$metadatum_type_object = $metadatum->get_metadata_type_object();

		if( $metadatum_type_object instanceof \Tainacan\Metadata_Types\Compound ) {
			$options = $metadatum->get_metadata_type_options();
			
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
			 	$child = ob_get_clean();
				$item_arr['edit_form'] = $form;
				
				$index = array_search( $item_arr['id'], array_column( $options['children_order'], 'id' ) );
				if ( $index !== false ) {
					$options['children_objects'][$index] = $item_arr;
				} else {
					$children_not_ordinate['children_objects'][] = $item_arr;
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
		$return = '';
		
		if ( $item_metadata->is_multiple() ) {
			
			$return .= '<div class="tainacan-compound-group">';
			
			foreach ( $value as $compound_element ) {
				
				$return .= '<div class="tainacan-compound-metadatum">';
				
				foreach ( $compound_element as $meta ) {
					if ( $meta instanceof Item_Metadata_Entity ) {
						$return .= '<label class="label">' . $meta->get_metadatum()->get_name() . "</label>\n";
						$return .= '<p>' . $meta->get_value_as_html() . '</p>' . "\n\n";
					}
				}
				
				$return .= '</div>' . "\n\n";
				
			}
			
			$return .= '</div>' . "\n\n";
			
			
		} else {
			
			foreach ( $value as $meta ) {
				
				$return .= '<div class="tainacan-metadatum">';
				
				if ( $meta instanceof Item_Metadata_Entity ) {
					$return .= '<label class="label">' . $meta->get_metadatum()->get_name() . "</label>\n";
					$return .= '<p>' . $meta->get_value_as_html() . '</p>';
				}
				
				$return .= '</div>' . "\n\n";	
				
			}
			
		}
		
		return $return;
		
	}

}