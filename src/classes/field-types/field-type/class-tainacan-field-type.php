<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
abstract class Field_Type  {

    private $primitive_type;
    public $options;

    abstract function render( $itemMetadata );

    public function __construct(){
        add_action('register_field_types', array(&$this, 'register_field_type'));
    }

    public function register_field_type(){
        global $Tainacan_Metadatas;
        $Tainacan_Metadatas->register_field_type( $this );
    }

    public function validate($value) {
        return true;
    }
    
    public function get_validation_errors() {
        return [];
    }

    public function get_primitive_type(){
        return $this->primitive_type;
    }

    public function set_primitive_type($primitive_type){
        $this->primitive_type = $primitive_type;
    }

    /**
     * @param $options
     */
    public function set_options( $options ){
        $this->options = ( is_array( $options ) ) ? $options : unserialize( $options );
    }

    /**
     * generate the fields for this field type
     */
    public function form(){

    }

}