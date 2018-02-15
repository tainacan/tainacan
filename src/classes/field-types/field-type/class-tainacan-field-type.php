<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
abstract class Field_Type  {

    private $primitive_type;
    public $options;
    public $errors;
    
    /**
     * Indicates wether this is a core Field Type or not
     *
     * Core field types are used by Title and description fields. These fields:
     * * Can only be used once, they belong to the repository and can not be deleted
     * * Its values are saved in th wp_post table, and not as post_meta 
     * 
     */
    public $core = false;
    
    /**
     * Used by core field types to indicate where it should be saved
     */
    public $related_mapped_prop = false;
    
    /**
     * The name of the web component used by this field type
     * @var string
     */
    public $component;
    
    abstract function render( $itemMetadata );

    public function __construct(){
        add_action('register_field_types', array(&$this, 'register_field_type'));
    }

    public function register_field_type(){
        global $Tainacan_Fields;
        $Tainacan_Fields->register_field_type( $this );
    }

    public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
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

    public function get_errors() {
        return $this->errors;
    }
    
    public function get_component() {
        return $this->component;
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