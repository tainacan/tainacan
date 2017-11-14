<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tainacan_Filter extends Tainacan_Entity  {

    use Tainacan_Entity_Collection_Relation;

    function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Filters';

        if ( is_numeric( $which ) && $which > 0) {
            $post = get_post( $which );
            if ( $post instanceof WP_Post) {
                $this->WP_Post = get_post( $which );
            }

        } elseif ( $which instanceof WP_Post ) {
            $this->WP_Post = $which;
        } else {
            $this->WP_Post = new StdClass();
        }

    }

    // Getters
    function get_id() {
        return $this->get_mapped_property('ID');
    }

    function get_name() {
        return $this->get_mapped_property('name');
    }

    function get_order() {
        return $this->get_mapped_property('order');
    }


    function get_color() {
        return $this->get_mapped_property('color');
    }

    function get_metadata() {
        $id = $this->get_mapped_property('metadata');
        return new Tainacan_Metadata( $id );
    }

    function get_widget( $output = 'object' ){
        if( $output === 'object'){
            return unserialize( $this->get_mapped_property('option') );
        } else{
            return $this->get_mapped_property('widget');
        }
    }

    // Setters
    function set_name($value) {
        return $this->set_mapped_property('name', $value);
    }

    function set_order($value) {
        return $this->set_mapped_property('order', $value);
    }

    function set_description($value) {
        return $this->set_mapped_property('description', $value);
    }

    function set_color( $value ) {
        return $this->set_mapped_property('parent', $value);
    }

    /**
     * @param Tainacan_Metadata / int $value
     */
    function set_metadata( $value ){
        $id = ( $value instanceof Tainacan_Metadata ) ? $value->get_id() : $value;

        return $this->set_mapped_property('metadata', $id);
    }

    function set_widget($value){
        if( is_object( $value ) && is_subclass_of( $value, 'Tainacan_Filter_Type' ) && $this->get_metadata() ){
            $type = $this->get_metadata()->get_type();

            //if filter matches the metadata type
            if( in_array( $type->get_primitive_type(), $value->get_supported_types() ) ){
                $this->set_option( $value );
                return $this->set_mapped_property('widget', get_class( $value ) ) ;
            }

        }
        return null;
    }

    function set_option($value){
        return $this->set_mapped_property('option',  serialize($value) ) ;
    }
}