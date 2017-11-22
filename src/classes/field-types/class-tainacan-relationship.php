<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Relationship extends Field_Type {

    function __construct(){
        parent::set_primitive_type('');
    }

    /**
     * @param $metadata
     * @return string
     */

    public function render( $metadata ){
        return '<tainacan-relationship name="'.$metadata->get_name().'"></tainacan-relationship>';
    }
}