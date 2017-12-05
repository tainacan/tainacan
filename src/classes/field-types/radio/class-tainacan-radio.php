<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Radio extends Field_Type {

    public $options;

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('');
    }

    /**
     * @param $metadata
     * @return string
     */

    public function render( $metadata ){
        return '<tainacan-radio name="'.$metadata->get_name().'"></tainacan-radio>';
    }

    /**
     * generate the fields for this field type
     */
    public function form(){
        ?>
        <tr>
            <td>
                <label><?php echo __('Options','tainacan'); ?></label><br/>
                <small><?php echo __('Insert the options, separate by ";" for the metadata value','tainacan'); ?></small>
            </td>
            <td>
                <textarea name="tnc_metadata_options"><?php echo ( $this->options ) ? $this->options : ''; ?></textarea>
            </td>
        </tr>
        <?php
    }
}