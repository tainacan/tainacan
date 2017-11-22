<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Textarea extends Field_Type {

    function __construct(){
        parent::set_primitive_type('date');
    }

    /**
     * @param $metadata
     * @return string
     */

    public function render( $metadata ){
        return '<tainacan-date name="'.$metadata->get_name().'"></tainacan-date>';
    }
}