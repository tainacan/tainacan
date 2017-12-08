<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Text extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('string');
    }

    /**
     * @param $metadata
     * @return string
     */

    public function render( $metadata ){
        return '<tainacan-text name="'.$metadata->get_name().'"></tainacan-text>';
    }

    /**
     * generate the fields for this field type
     */
    public function form(){

    }
}