<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Helpers;

/**
 * Class TainacanFieldType
 */
class Relationship extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('item');
        $this->component = 'tainacan-relationship';
        $this->form_component = 'tainacan-form-relationship';
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-relationship 
                            collection_id="' . $this->options['collection_id'] . '"
                            field_id ="'.$itemMetadata->get_field()->get_id().'" 
                            item_id="'.$itemMetadata->get_item()->get_id().'"    
                            value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                            name="'.$itemMetadata->get_field()->get_name().'"></tainacan-relationship>';
    }
    
    public function validate_options(\Tainacan\Entities\Field $field) {
        if ( !in_array($field->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        if (!empty($this->get_option('collection_id')) && !is_numeric($this->get_option('collection_id'))) {
            return [
                'collection_id' => __('Collection ID invalid','tainacan')
            ];
        } else if( empty($this->get_option('collection_id'))) {
            return [
                'collection_id' => __('Collection related is required','tainacan')
            ];
        }
        return true;
    }
}