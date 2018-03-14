<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Radio extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        $this->set_primitive_type('');
        $this->set_component('tainacan-radio');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        $options = $this->get_option('options');
        return '<tainacan-radio
                                options="' . $options . '"  
                                field_id ="'.$itemMetadata->get_field()->get_id().'" 
                                item_id="'.$itemMetadata->get_item()->get_id().'"    
                                value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                                options="'.$options.'" name="'.$itemMetadata->get_field()->get_name().'"></tainacan-radio>';
    }

    /**
     * generate the fields for this field type
     */
    public function form(){
        ?>
        <tr>
            <td>
                <label><?php echo __('Options','tainacan'); ?></label><br/>
                <small><?php echo __('Insert the options, separate by lines for the field value','tainacan'); ?></small>
            </td>
            <td>
                <textarea name="field_type_options[options]"><?php echo $this->get_option('options'); ?></textarea>
            </td>
        </tr>
        <?php
    }
}