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
			$options = $metadatum_type_object->get_options();
			$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

			if( !isset( $options['parent'] ) )
				return;

			if( isset( $options['before_children'] ) && is_array( $options['before_children'] ) ) {
				foreach ( $options['before_children'] as $child) {
					if( isset( $options['children'] ) && is_array( $options['children'] ) && in_array( $child,  $options['children']))
						continue;

					$metadatum = new \Tainacan\Entities\Metadatum( $child );
					$metadatum->set_parent(0);

					if( $metadatum->validate() ) {
						$Tainacan_Metadata->update( $field );
					}
				}
			}

			if( isset( $options['children'] ) && is_array( $options['children'] ) ) {
				foreach ( $options['children'] as $child) {
					$metadatum = new \Tainacan\Entities\Metadatum( $child );
					$metadatum->set_parent( $options['parent'] );
					if( $metadatum->validate() ) {
						$Tainacan_Metadata->update( $metadatum );
					}
				}
			}
		}
	}

	/**
	 * validate the children of the compound
	 * @param \Tainacan\Entities\Metadatum $metadatum
	 * @return array|bool
	 */
	public function validate_options( \Tainacan\Entities\Metadatum $metadatum ) {
		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
			return true;

		$metadatum_type_object = $metadatum->get_metadata_type_object();

		if( $metadatum_type_object instanceof \Tainacan\Metadata_Types\Compound ) {
			$options = $metadatum_type_object->get_options();

			// if parent is not set, it comes from tests
			if( !isset( $options['parent'] ) )
				return true;

			if( !isset( $options['children'] ) || empty( $options['children'] ) ) {
				return ['children' => __('Children is required','tainacan')];
			}

			foreach ($options['children'] as $child) {
				$metadatum = new \Tainacan\Entities\Metadatum( $child );
				$metadatum->set_parent( $options['parent'] );
				if( !$metadatum->validate() ) {
					return [ $metadatum->get_errors()[0] ];
				}
			}
		}
		return true;
	}

	/**
	 * Overrides and bring back all data for the children
	 * that were not set yet.
	 * @return array Metadata type options
	 */
	public function get_options() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$options = parent::get_options();
		$options['children_objects'] = [];

		if( isset( $options['children'] ) && !empty( $options['children'] ) ) {
			foreach ($options['children'] as $child) {
				$item = new \Tainacan\Entities\Metadatum( $child );
				$item_arr = $item->_toArray();
				$item_arr['metadata_type_object'] = $item->get_metadata_type_object()->_toArray();
				$item_arr['current_user_can_edit'] = $item->can_edit();
				ob_start();
				$item->get_metadata_type_object()->form();
				$form = ob_get_clean();
				$item_arr['edit_form'] = $form;
				$options['children_objects'][] = $item_arr;
			}
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
			
			$return .= '<div class="tainacan-compund-group">';
			
			foreach ( $value as $compound_element ) {
				
				$return .= '<div class="tainacan-compund-metadatum">';
				
				foreach ( $compound_element as $meta ) {
					if ( $meta instanceof Item_Metadata_Entity ) {
						$return .= '<h4>' . $meta->get_metadatum()->get_name() . "</h4>\n";
						$return .= '<p>' . $meta->get_value_as_html() . '</p>' . "\n\n";
					}
				}
				
				$return .= '</div>' . "\n\n";
				
			}
			
			$return .= '</div>' . "\n\n";
			
			
		} else {
			
			foreach ( $value as $meta ) {
				
				$return .= '<div class="tainacan-compund-metadatum">';
				
				if ( $meta instanceof Item_Metadata_Entity ) {
					$return .= '<h4>' . $meta->get_metadatum()->get_name() . "</h4>\n";
					$return .= '<p>' . $meta->get_value_as_html() . '</p>';
				}
				
				$return .= '</div>' . "\n\n";	
				
			}
			
		}
		
		return $return;
		
	}

}