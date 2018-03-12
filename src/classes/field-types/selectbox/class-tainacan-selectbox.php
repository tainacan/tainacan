<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Selectbox extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('string');
        $this->component = 'tainacan-selectbox';
        $this->form_component = 'tainacan-form-selectbox';
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        $options = $this->get_option('options');
        return '<tainacan-selectbox    
                                       options="' . $options . '"
                                       field_id ="'.$itemMetadata->get_field()->get_id().'" 
                                       item_id="'.$itemMetadata->get_item()->get_id().'"    
                                       value=\''.json_encode( $itemMetadata->get_value() ).'\'
                                       name="'.$itemMetadata->get_field()->get_name().'"></tainacan-selectbox>';
    }

    /**
     * @param \Tainacan\Entities\Field $field
     * @return array|bool true if is validate or array if has error
     */
    public function validate_options(\Tainacan\Entities\Field $field) {
        if ( !in_array($field->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        if ( empty($this->get_option('options')) ) {
            return [
                'options' => __('Options is required','tainacan')
            ];
        }

        return true;
    }
}